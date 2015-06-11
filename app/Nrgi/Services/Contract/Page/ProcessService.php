<?php namespace App\Nrgi\Services\Contract\Page;

use App\Nrgi\Services\Contract\ContractService;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use App\Nrgi\Services\Contract\Page\PageService;
use Illuminate\Contracts\Filesystem\Factory as Storage;

/**
 * Use for processing pages
 * Class ProcessService
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

    protected $page ;

    /**
     * @param Filesystem      $fileSystem
     * @param ContractService $contract
     * @param PageService     $page
     */
    function __construct(Filesystem $fileSystem,
                         ContractService $contract ,
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

            try {
                if (!$this->checkIfProcessed($contract)) {

                    list($writeFolderPath, $readFilePath) = $this->setup($contract);
                    if ($this->process($writeFolderPath, $readFilePath)) {
                        //insert to database by contract id
                        $pages = $this->page->buildPages($writeFolderPath);
                        $this->page->savePages($contractId, $pages);
                        $this->renameTxtFiles($writeFolderPath);
                        $this->renamePdfFiles($writeFolderPath);
                        return true;
                    }
                }
            } catch (\Exception $e) {
                return false;
            }
        } catch (ModelNotFoundException $e) {
            return false;
        }

        return false;
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
            $this->processContractDocument($writeFolderPath, $readFilePath, 'text');
            $this->processContractDocument($writeFolderPath, $readFilePath, 'pages');
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
    public function processContractDocument($writeFolderPath, $readFilePath, $type)
    {
        $writeFolderPath = $writeFolderPath.'/'.$type;
        $command = sprintf('docsplit %s %s --pages all -o %s', $type, $readFilePath, $writeFolderPath);
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
     * @param $file
     * @return int
     */
    public function getPageNo($file)
    {
        $fileName = pathinfo($file, PATHINFO_FILENAME);
        $output    = explode("_", $fileName);

        return (int) $output[count($output) - 1];
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
     * @param $directory
     * @return bool
     */
    public function renameTxtFiles($directory)
    {
        $filePath = $directory.'/text';
        $files = $this->fileSystem->files($filePath);

        foreach ($files as $file) {
            $pageNo = $this->getPageNo($file);
            if (!$this->fileSystem->move($file, $filePath.'/'.$pageNo.'.txt')) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $directory
     * @return bool
     */
    public function renamePdfFiles($directory)
    {
        $filePath = $directory.'/pages';
        $files    = $this->fileSystem->files($filePath);

        foreach ($files as $file) {
            if ($this->fileSystem->extension($file) == 'pdf') {
                $pageNo = $this->getPageNo($file);

                if (!$this->fileSystem->move($file, $filePath.'/'.$pageNo.'.pdf')) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param $contract
     * @return array
     */
    public function setup($contract)
    {

        $publicPath = public_path();
        //$this->file->makeDirectory($publicPath.'/data', 0775, true, true);
        $pdfFile = $this->storage->disk('s3')->get($contract->file);
        $this->storage->disk('local')->put($contract->file, $pdfFile);
        //mkdir folder with contract id in data folder
        $writeFolderPath = sprintf('%s/%s', $publicPath, 'data');
        $this->addDirectory($contract->id, $writeFolderPath);
        $writeFolderPath = $writeFolderPath.'/'.$contract->id;
        //get temporarary file from local storage
        $readFilePath = storage_path().'/app/'.$contract->file;
        return array($writeFolderPath, $readFilePath);
    }

    /**
     * @param $newFileName
     * @param $status
     */
    public function processStatus($newFileName, $status)
    {
        $newFileContent = $status.PHP_EOL;
        $newFileName = $newFileName.'/status.txt';

        if (file_put_contents($newFileName, $newFileContent) != false) {
            return  "File created (" . basename($newFileName) . ")";
        } else {
            return  "Cannot create file (" . basename($newFileName) . ")";
        }

    }
}