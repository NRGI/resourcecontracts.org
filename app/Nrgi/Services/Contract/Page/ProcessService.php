<?php namespace App\Nrgi\Services\Contract\Page;

use App\Nrgi\Services\Contract\ContractService;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Illuminate\Contracts\Filesystem\Factory as Storage;
use Psr\Log\LoggerInterface;

/**
 * Use for processing pages
 * Class ProcessService
 *
 * @package App\Nrgi\Services\Contract\Page
 */
class ProcessService
{
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
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Filesystem      $fileSystem
     * @param ContractService $contract
     * @param PageService     $page
     * @param Storage         $storage
     * @param LoggerInterface $logger
     */
    public function __construct(Filesystem $fileSystem,
                                ContractService $contract,
                                PageService $page,
                                Storage $storage,
                                LoggerInterface $logger
    ) {
        $this->fileSystem = $fileSystem;
        $this->contract = $contract;
        $this->page = $page;
        $this->storage = $storage;
        $this->logger = $logger;
    }

    /**
     * @param $contractId
     * @return bool
     */
    public function execute($contractId)
    {
        try {
            $contract = $this->contract->find($contractId);
            $this->logger->info("processing Contract", ['contractId' => $contractId]);
            list($writeFolderPath, $readFilePath) = $this->setup($contract);

            if ($this->process($writeFolderPath, $readFilePath)) {
                $pages = $this->page->buildPages($writeFolderPath);
                $this->page->savePages($contractId, $pages);
                $this->updateContractPdfStructure($contract, $writeFolderPath);
                $this->logger->info("processing contract completed.",['contractId' => $contractId]);
                return true;
            }
        } catch (\Exception $e) {
            $this->logger->error("error processing contract.{$e->getMessage()}",['contractId'=>$contractId]);
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
        $content = $this->fileSystem->get(sprintf('%s/stats.json', $writeFolderPath));
        $data = json_decode($content);
        $contract->pdf_structure = ($data->structured ? "structured" : "scanned");

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
            $this->processStatus($writeFolderPath, 0);
            $this->processContractDocument($writeFolderPath, $readFilePath);
            $this->processStatus($writeFolderPath, 1);
            $this->logger->info("processing contract completed");

            return true;
        } catch (\Exception $e) {
            $this->logger->error('error'.$e->getMessage(),
                                 ['write_folder_path'=>$writeFolderPath,
                                  'read_file_path'=>$readFilePath
                                 ]);
            
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
        $commandPath = config('nrgi.pdf_process_path');
        $command = sprintf('python %s/run.py -i %s -o %s', $commandPath, $readFilePath, $writeFolderPath);
        $this->logger->info("processing command",['command'=>$command]);
        $process = new Process($command);
        $process->run();
        //executes after the command finishes
        if (!$process->isSuccessful()) {
            //todo remove folder
            $this->logger->error("error while executing command.$process->getErrorOutput()",['command'=>$command]);
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
        $publicPath = public_path();
        $writeFolderPath = sprintf('%s/%s', $publicPath, 'data');
        $path = $writeFolderPath.'/'.$contractId;

        return file_exists($path);
    }

    /**
     * @param $directory
     * @param $path
     * @throws \Exception
     */
    public function addDirectory($directory, $path)
    {
        if(!$this->fileSystem->makeDirectory($path.'/'.$directory, 0777, true)) {
            $this->loger->error("error while creating director.".$path.'/'.$directory);
            throw new \Exception('could not make directory.'.$path.'/'.$directory);
        }
    }

    /**
     * @param $contract
     * @return array
     * @throws \Exception
     */
    public function setup($contract)
    {
        $publicPath = public_path();
        $pdfFile = $this->storage->disk('s3')->get($contract->file);
        $this->storage->disk('local')->put($contract->file, $pdfFile);
        $writeFolderPath = sprintf('%s/%s', $publicPath, 'data');
        if ( ! $this->fileSystem->isDirectory($writeFolderPath.'/'.$contract->id))
        {
            $this->addDirectory($contract->id, $writeFolderPath);
        }

        $writeFolderPath = $writeFolderPath.'/'.$contract->id;
        $readFilePath = storage_path().'/app/'.$contract->file;

        return array($writeFolderPath, $readFilePath);
    }

    /**
     * @param $directory
     * @param $status
     * @throws \Exception
     */
    public function processStatus($directory, $status)
    {
        $fileContent = $status.PHP_EOL;
        $filePath = $directory.'/status.txt';
        $this->logger->info("writing to {$filePath}", ['status'=>$status]);
        if(!$this->fileSystem->put($filePath, $fileContent)) {
            $this->logger->error("could not create status file in directory {$directory}");
            throw new \Exception("could not create status file.");
        }

    }
}
