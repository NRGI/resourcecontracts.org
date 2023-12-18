<?php namespace App\Nrgi\Services\Contract\Page;

use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Mail\MailQueue;
use App\Nrgi\Services\Contract\ContractService;
use Aws\Credentials\Credentials;
use Aws\S3\S3Client;
use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Factory as Storage;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Filesystem\Filesystem;
use Psr\Log\LoggerInterface as Log;

/**
 * Use for processing pages
 * Class ProcessService
 *
 * @package App\Nrgi\Services\Contract\Page
 */
class ProcessService
{
    /**
     * @var storage
     */
    protected $storage;
    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var ContractService
     */
    protected $contract;

    /**
     * @var PageService
     */
    protected $page;

    /**
     * @var Log
     */
    protected $logger;
    /**
     * @var MailQueue
     */
    protected $mailer;

    protected $contract_id;
    /**
     * @var Queue
     */
    protected $queue;

    /**
     * @param Filesystem      $fileSystem
     * @param ContractService $contract
     * @param PageService     $page
     * @param Storage         $storage
     * @param Queue           $queue
     * @param Log             $logger
     * @param MailQueue       $mailer
     */
    public function __construct(
        Filesystem $fileSystem,
        ContractService $contract,
        PageService $page,
        Storage $storage,
        Queue $queue,
        Log $logger,
        MailQueue $mailer
    ) {
        $this->fileSystem = $fileSystem;
        $this->contract   = $contract;
        $this->page       = $page;
        $this->storage    = $storage;
        $this->logger     = $logger;
        $this->mailer     = $mailer;
        $this->queue      = $queue;
    }

    /**
     * @param $contractId
     *
     * @return bool
     */
    public function execute($contractId)
    {
        $this->contract_id = $contractId;
        $contract          = $this->contract->find($contractId);
        $startTime         = Carbon::now();
        try {

            $ocr_lang = $this->getOCRLang($contract->metadata->language);
            $this->processStatus(Contract::PROCESSING_RUNNING);
            $this->logger->info("Processing Contract", ['contractId' => $contractId]);

            list($writeFolderPath, $readFilePath) = $this->setup($contract);

            if ($this->process($writeFolderPath, $readFilePath, $ocr_lang)) {
                $pages = $this->page->buildPages($writeFolderPath);
                $this->page->savePages($contractId, $pages);
                $this->mailer->send(
                    $contract->created_user->email,
                    "{$contract->title} processing contract completed.",
                    'emails.process_success',
                    [
                        'contract_title'      => $contract->title,
                        'contract_id'         => $contract->id,
                        'contract_detail_url' => route('contract.show', ["contract" => $contract->id]),
                        'start_time'          => $startTime->toDayDateTimeString(),
                        'end_time'            => Carbon::now()->toDayDateTimeString(),
                    ]
                );
                $this->contract->moveS3File(
                    $contract->file,
                    sprintf('%s/%s', $contract->id, $contract->getS3PdfName())
                );

                $this->updateContractPdfStructure($contract, $writeFolderPath);
                $this->uploadPdfsToS3($contract->id);
                $this->deleteContractFolder($contract->id);
                $this->fileSystem->delete($readFilePath);

                $this->contract->updateFileName($contract);
                $this->contract->updateWordFile($contract->id);

                $contract->text_status = Contract::STATUS_DRAFT;
                $contract->save();

                $contract = $this->contract->find($contract->id);

                if ($contract->metadata_status == Contract::STATUS_PUBLISHED) {
                    $this->queue->push(
                        'App\Nrgi\Services\Queue\PostToElasticSearchQueue',
                        ['contract_id' => $contractId, 'type' => 'Metadata'],
                        'elastic_search'
                    );
                }

                $this->processStatus(Contract::PROCESSING_COMPLETE);
                $this->logger->info("Processing contract completed.", ['contractId' => $contractId]);

                return true;
            }
        } catch (\Exception $e) {
            $this->processStatus(Contract::PROCESSING_FAILED);
            $this->mailer->send(
                $contract->created_user->email,
                "{$contract->title} processing error.",
                'emails.process_error',
                [
                    'contract_title'      => $contract->title,
                    'contract_id'         => $contract->id,
                    'contract_detail_url' => route('contract.show', ["contract" => $contract->id]),
                    'start_time'          => $startTime->toDayDateTimeString(),
                    'error'               => $e->getMessage(),
                ]
            );
            $this->logger->error("error processing contract.{$e->getMessage()}", ['contractId' => $contractId]);

            return false;
        }

        return false;
    }

    /**
     * @param $contract
     * @param $writeFolderPath
     *
     * @return mixed
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function updateContractPdfStructure($contract, $writeFolderPath)
    {
        $content                 = $this->fileSystem->get(sprintf('%s/stats.json', $writeFolderPath));
        $data                    = json_decode($content);
        $contract->pdf_structure = strtolower($data->status);

        return $contract->save();
    }

    /**
     * Process document
     *
     * @param $lang
     * @param $writeFolderPath
     * @param $readFilePath
     *
     * @return bool|null
     */
    public function process($writeFolderPath, $readFilePath, $lang)
    {
        try {
            $this->processStatus(Contract::PROCESSING_RUNNING);
            $this->logger->info("Python script running");
            $this->processContractDocument($writeFolderPath, $readFilePath, $lang);
            $this->logger->info("Python script completed");

            return true;
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('error.%s', $e->getMessage()),
                [
                    'write_folder_path' => $writeFolderPath,
                    'read_file_path'    => $readFilePath,
                ]
            );

            return false;
        }
    }

    /**
     * @param $writeFolderPath
     * @param $readFilePath
     *
     * @param $lang
     *
     * @return bool
     */
    public function processContractDocument($writeFolderPath, $readFilePath, $lang)
    {
        set_time_limit(0);
        $commandPath = config('nrgi.pdf_process_path');
        $abbyyOcrUrl = env('ABBYY_OCR_URL');
        $command = sprintf(
            'ABBYY_OCR_URL=%s python %s/run.py -i %s -o %s -l %s',
            escapeshellarg($abbyyOcrUrl),
            escapeshellarg($commandPath),
            escapeshellarg($readFilePath),
            escapeshellarg($writeFolderPath),
            escapeshellarg($lang)
        );
        $this->logger->info("Executing python command", ['command' => $command]);

        try {
            exec($command, $output);

            return true;
        } catch (\Exception $e) {
            $this->logger->error("error while executing command : {$e->getMessage()}", ['command' => $command]);

            return false;
        }
    }

    /**
     * @param $contractId
     *
     * @return bool
     */
    public function checkIfProcessed($contractId)
    {
        return file_exists($this->getContractDirectory($contractId));
    }

    /**
     * @param $directory
     * @param $path
     *
     * @throws \Exception
     */
    public function addDirectory($directory, $path)
    {
        if (!$this->fileSystem->makeDirectory($path.'/'.$directory, 0777, true)) {
            $this->logger->error(sprintf("error while creating director.%s/%s", $path, $directory));
            throw new \Exception(sprintf('could not make directory.%s/%s'), $path, $directory);
        }
    }

    /**
     * @param $contract
     *
     * @return array
     * @throws \Exception
     */
    public function setup($contract)
    {
        $this->logger->info('Download started...', ['file' => $contract->file]);
        $pdfFile = '';
        try {
            if ($this->storage->disk('s3')->exists($contract->file)) {
                $pdfFile = $this->storage->disk('s3')->get($contract->file);
            } else {
                $pdfFile = $this->storage->disk('s3')->get($contract->id.'/'.$contract->file);
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['contract id' => $contract->id, 'file' => $contract->file]);
        }
        $this->storage->disk('local')->put($contract->file, $pdfFile);
        $this->logger->info('Download completed...', ['file' => $pdfFile]);

        if (!$this->fileSystem->isDirectory($this->getContractDirectory($contract->id))) {
            $this->addDirectory($contract->id, $this->getWriteDirectory());
        }

        $writeFolderPath = $this->getContractDirectory($contract->id);
        $readFilePath    = sprintf('%s/app/%s', storage_path(), $contract->file);

        return [$writeFolderPath, $readFilePath];
    }

    /**
     * Update process status
     *
     * @param $status
     *
     * @return bool
     * @throws \Exception
     */
    public function processStatus($status)
    {
        $contract                     = $this->contract->find($this->contract_id);
        $contract->pdf_process_status = $status;

        return $contract->save();
    }

    /**
     * @param $contractId
     *
     * @return string
     */
    public function getContractDirectory($contractId)
    {
        return sprintf('%s/%s', $this->getWriteDirectory(), $contractId);
    }

    /**
     * provides write folder path
     *
     * @return string
     */
    public function getWriteDirectory()
    {
        $publicPath = public_path();

        return sprintf('%s/%s', $publicPath, 'data');
    }

    /**
     * Upload Pdfs to S3
     *
     * @param $id
     *
     * @return void
     */
    public function uploadPdfsToS3($id)
    {
        $credentials = new Credentials(env('AWS_KEY'), env('AWS_SECRET'));
        $client = new S3Client(
            [
                'version'=> '2006-03-01',
                'region' => env('AWS_REGION'),
                'credentials' => $credentials
            ]
        );

        $client->uploadDirectory(sprintf('%s/%s/pages/', $this->getWriteDirectory(), $id), env('AWS_BUCKET'), $id);
        $this->logger->info(sprintf("Pdf uploaded to S3 {%s}", env('AWS_BUCKET')));
    }

    /**
     * Delete contract Folder
     *
     * @param $id
     */
    protected function deleteContractFolder($id)
    {
        $this->fileSystem->deleteDirectory(sprintf('%s/%s', $this->getWriteDirectory(), $id));
    }

    /**
     * Get OCR Language
     *
     * @param $code
     *
     * @return string
     */
    private function getOCRLang($code)
    {
        $available_lang = [
            'fr' => 'french',
            'es' => 'spanish',
            'en' => 'english',
            "pt" => "portuguese",
            "ar" => "arabic",
        ];

        return isset($available_lang[$code]) ? $available_lang[$code] : 'english';
    }

}
