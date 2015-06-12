<?php namespace App\Nrgi\Services\Contract\Page;

use App\Nrgi\Services\Contract\ContractService;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Illuminate\Contracts\Filesystem\Factory as Storage;

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
     * @param Filesystem      $fileSystem
     * @param ContractService $contract
     * @param PageService     $page
     */
    public function __construct(Filesystem $fileSystem,
                         ContractService $contract,
                         PageService $page, Storage $storage)
    {
        $this->fileSystem = $fileSystem;
        $this->contract = $contract;
        $this->page = $page;
        $this->storage = $storage;
    }

    /**
     * @param $contractId
     * @return bool
     */
    public function execute($contractId)
    {
        try {
            $contract = $this->contract->find($contractId);
            
            if (!$this->checkIfProcessed($contract)) {
                list($writeFolderPath, $readFilePath) = $this->setup($contract);

                if ($this->process($writeFolderPath, $readFilePath)) {
                    $pages = $this->page->buildPages($writeFolderPath);
                    $this->page->savePages($contractId, $pages);
                    $this->updateContractPdfStructure($contract, $writeFolderPath);

                    return true;
                }
            }
        } catch (\Exception $e) {
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
        //return $this->contract->updatePdfStructure($contract->id, ($data->structured ? "structured" : "scanned"));
    }

    /**
     * @param $contract
     * @param $writeFolderPath
     * @param $readFilePath
     * @return bool
     */
    public function process($writeFolderPath, $readFilePath)
    {
        $this->processStatus($writeFolderPath, 0);
        try {
            $this->processContractDocument($writeFolderPath, $readFilePath);
        } catch (\Exception $e) {
            return false;
        }

        $this->processStatus($writeFolderPath, 1);
        //todo delete temporary file from local storage

        return true;
    }

    /**
     * @param $writeFolderPath
     * @param $readFilePath
     * @param $type
     * @return bool
     */
    public function processContractDocument($writeFolderPath, $readFilePath)
    {
        $commandPath = config('nrgi.pdf_process_path');
        $command = sprintf('python %s/run.py -i %s -o %s', $commandPath, $readFilePath, $writeFolderPath);
        $process = new Process($command);
        $process->run();
        //executes after the command finishes
        if (!$process->isSuccessful()) {
            //todo remove folder
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
     */
    public function addDirectory($directory, $path)
    {
        $this->fileSystem->makeDirectory($path.'/'.$directory, 0777, true, true);
    }

    /**
     * @param $contract
     * @return array
     */
    public function setup($contract)
    {
        $publicPath = public_path();
        $pdfFile = $this->storage->disk('s3')->get($contract->file);
        $this->storage->disk('local')->put($contract->file, $pdfFile);
        $writeFolderPath = sprintf('%s/%s', $publicPath, 'data');
        $this->addDirectory($contract->id, $writeFolderPath);
        $writeFolderPath = $writeFolderPath.'/'.$contract->id;
        $readFilePath = storage_path().'/app/'.$contract->file;

        return array($writeFolderPath, $readFilePath);
    }

    /**
     * @param $directory
     * @param $status
     * @return int
     */
    public function processStatus($directory, $status)
    {
        $fileContent = $status.PHP_EOL;
        $filePath = $directory.'/status.txt';

        return $this->fileSystem->put($filePath, $fileContent);
    }
}
