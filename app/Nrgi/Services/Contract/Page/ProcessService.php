<?php namespace App\Nrgi\Services\Contract\Page;

use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Mail\MailQueue;
use App\Nrgi\Services\Contract\ContractService;
use Aws\S3\S3Client;
use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Factory as Storage;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Contracts\Logging\Log;
use Symfony\Component\Process\Process;

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
     * @param Filesystem      $fileSystem
     * @param ContractService $contract
     * @param PageService     $page
     * @param Storage         $storage
     * @param Log             $logger
     * @param MailQueue       $mailer
     */
    public function __construct(
        Filesystem $fileSystem,
        ContractService $contract,
        PageService $page,
        Storage $storage,
        Log $logger,
        MailQueue $mailer
    ) {
        $this->fileSystem = $fileSystem;
        $this->contract   = $contract;
        $this->page       = $page;
        $this->storage    = $storage;
        $this->logger     = $logger;
        $this->mailer     = $mailer;
    }

    /**
     * @param $contractId
     * @return bool
     */
    public function execute($contractId)
    {
        $this->contract_id = $contractId;
        $contract          = $this->contract->find($contractId);
        $startTime         = Carbon::now();
        try {
            $this->logger->info("processing Contract", ['contractId' => $contractId]);
            list($writeFolderPath, $readFilePath) = $this->setup($contract);

            if ($this->process($writeFolderPath, $readFilePath)) {
                $pages = $this->page->buildPages($writeFolderPath);
                $this->page->savePages($contractId, $pages);
                $this->updateContractPdfStructure($contract, $writeFolderPath);
                $contract->text_status = Contract::STATUS_DRAFT;
                $contract->save();
                $this->logger->info("processing contract completed.", ['contractId' => $contractId]);
                $this->mailer->send(
                    [
                        'email' => $contract->created_user->email,
                        'name'  => $contract->created_user->name
                    ],
                    "{$contract->title} processing contract completed.",
                    'emails.process_success',
                    [
                        'contract_title'      => $contract->title,
                        'contract_id'         => $contract->id,
                        'contract_detail_url' => route('contract.show', $contract->id),
                        'start_time'          => $startTime->toDayDateTimeString(),
                        'end_time'            => Carbon::now()->toDayDateTimeString()
                    ]
                );
                $this->movePdfToUpload($contract->id, $contract->file);
                $this->uploadPdfsToS3($contract->id);
                $this->deleteContractFolder($contract->id);

                return true;
            }
        } catch (\Exception $e) {
            $this->processStatus(Contract::PROCESSING_FAILED);
            $this->mailer->send(
                [
                    'email' => $contract->created_user->email,
                    'name'  => $contract->created_user->name
                ],
                "{$contract->title} processing error.",
                'emails.process_error',
                [
                    'contract_title'      => $contract->title,
                    'contract_id'         => $contract->id,
                    'contract_detail_url' => route('contract.show', $contract->id),
                    'start_time'          => $startTime->toDayDateTimeString(),
                    'error'               => $e->getMessage()
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
     * @param $writeFolderPath
     * @param $readFilePath
     * @return bool|null
     */
    public function process($writeFolderPath, $readFilePath)
    {
        try {
            $this->processStatus(Contract::PROCESSING_RUNNING);
            $this->logger->info("processing contract running");
            $this->processContractDocument($writeFolderPath, $readFilePath);
            $this->processStatus(Contract::PROCESSING_COMPLETE);
            $this->logger->info("processing contract completed");

            return true;
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('error.%s', $e->getMessage()),
                [
                    'write_folder_path' => $writeFolderPath,
                    'read_file_path'    => $readFilePath
                ]
            );

            return false;
        }
    }

    /**
     * @param $writeFolderPath
     * @param $readFilePath
     * @return bool
     */
    public function processContractDocument($writeFolderPath, $readFilePath)
    {
        set_time_limit(0);
        $commandPath = config('nrgi.pdf_process_path');
        $command     = sprintf('python %s/run.py -i %s -o %s', $commandPath, $readFilePath, $writeFolderPath);
        $this->logger->info("processing command", ['command' => $command]);
        $process = new Process($command);
        $process->setTimeout(360 * 10);
        $process->start();
        while ($process->isRunning()) {
            echo $process->getIncrementalOutput();
        }
        if (!$process->isSuccessful()) {
            //todo remove folder
            $this->logger->error("error while executing command.{$process->getErrorOutput()}", ['command' => $command]);
            throw new \RuntimeException($process->getErrorOutput());
        }

        return true;
    }

    /**
     * @param $contractId
     * @return bool
     */
    public function checkIfProcessed($contractId)
    {
        return file_exists($this->getContractDirectory($contractId));
    }

    /**
     * @param $directory
     * @param $path
     * @throws \Exception
     */
    public function addDirectory($directory, $path)
    {
        if (!$this->fileSystem->makeDirectory($path . '/' . $directory, 0777, true)) {
            $this->logger->error(sprintf("error while creating director.%s/%s", $path, $directory));
            throw new \Exception(sprintf('could not make directory.%s/%s'), $path, $directory);
        }
    }

    /**
     * @param $contract
     * @return array
     * @throws \Exception
     */
    public function setup($contract)
    {
        $this->logger->info('Download started...', ['file' => $contract->file]);
        $pdfFile = '';

        try {
            $pdfFile = $this->storage->disk('s3')->get($contract->file);
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
     * @return void
     */
    public function uploadPdfsToS3($id)
    {
        $client = S3Client::factory(
            [
                'key'    => env('AWS_KEY'),
                'secret' => env('AWS_SECRET'),
                'region' => env('AWS_REGION'),
            ]
        );

        $client->uploadDirectory(sprintf('%s/%s/pages/', $this->getWriteDirectory(), $id), env('AWS_BUCKET'), $id);
        $this->logger->info(sprintf("Pdf uploaded to S3 {%s}", env('AWS_BUCKET')));
    }

    /**
     * Move pdf to folder
     *
     * @param $id
     * @param $file
     */
    protected function movePdfToUpload($id, $file)
    {
        $this->storage->disk('s3')->move($file, sprintf('%s/%s', $id, $file));
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

}
