<?php namespace App\Console\Commands;

use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Entities\Contract\Pages\Pages;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Filesystem\Filesystem as File;
use Illuminate\Contracts\Filesystem\Factory as Storage;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Process\Process;

/**
 * Class ProcessDocument
 * @package app\Console\Commands
 */
class ProcessDocument extends Command
{
    /**
     * @var Contract
     */
    protected $contract;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var Storage
     */
    protected $storage;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'process:document';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process Document by contract Identifier.';

    /**
     * Create a new command instance.
     *
     */
    public function __construct(Contract $contract, Storage $storage, File $file)
    {
        $this->storage = $storage;
        $this->contract = $contract;
        $this->file = $file;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $contractId = $this->input->getArgument('contract_id');
        $this->info("<info>Processing Contract File</info>");
        try {
            $contract = $this->contract->findOrFail($contractId);
            try {
                if (!$this->checkIfProcessed($contract)) {
                    list($writeFolderPath, $readFilePath) = $this->setup($contract);
                    if ($this->process($writeFolderPath, $readFilePath)) {
                        //insert to database by contract id
                        $this->save($contract, $writeFolderPath);
                        $this->renameTxtFiles($writeFolderPath);
                        $this->renamePdfFiles($writeFolderPath);

                        $this->info("<info>File Processing Done.</info>");
                    } else {
                        $this->error('Error Processing File');
                    }
                }
            } catch (\Exception $e) {
                $this->error('Error while processing file! Cause->'.$e);
            }
        } catch (ModelNotFoundException $e) {
            $this->error('Contract Does Not Exists!');
        }
    }

    /**
     * @param $contract
     * @return array
     */
    public function setup($contract)
    {
        $publicPath = public_path();
        //get file from s3
        $this->file->makeDirectory($publicPath.'/data', 0775, true, true);
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
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['contract_id', InputArgument::REQUIRED, 'Contract to be processed.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['example', null, InputOption::VALUE_OPTIONAL, 'An  option.', null],
        ];
    }

    /**
     * @param $contract
     * @param $writeFolderPath
     * @param $readFilePath
     * @return bool
     */
    public function process($writeFolderPath, $readFilePath)
    {
        $this->file->put($writeFolderPath.'/status.txt', '0'.PHP_EOL);
        try {
            $this->processContractDocument($writeFolderPath, $readFilePath, 'text');
            $this->processContractDocument($writeFolderPath, $readFilePath, 'pages');
        } catch (\Exception $e) {
            return false;
        }

        $this->file->put($writeFolderPath.'/status.txt', '1'.PHP_EOL);
        //todo delete temporary file from local storage

        return true;
    }

    /**
     * @param $directory
     * @param $path
     */
    public function addDirectory($directory, $path)
    {
        $this->file->makeDirectory($path.'/'.$directory, 0755, true);
    }

    /**
     * @param $contract
     * @return bool
     */
    public function checkIfProcessed($contract)
    {
        $publicPath = public_path();
        $writeFolderPath = sprintf('%s/%s', $publicPath, 'data');
        $path = $writeFolderPath.'/'.$contract->id;

        return file_exists($path);
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
        $process = new Process(sprintf('docsplit %s %s --pages all -o %s', $type, $readFilePath, $writeFolderPath));
        $process->run();
        //executes after the command finishes
        if (!$process->isSuccessful()) {
            //todo remove folder
            throw new \RuntimeException($process->getErrorOutput());
        }

        return true;
    }

    /**
     * @param $contract
     * @param $directory
     * return bool// pages serivce /
     */
    public function save($contract, $directory)
    {
        //refactor to another service method
        $files = $this->file->files($directory.'/text');

        foreach ($files as $file) {
            $content = $this->file->get($file);
            $pageNo = $this->getPageNo($file);
            $page = new Pages();
            $page->page_no = $pageNo;
            $page->text = $content;
            $pages[] = $page;
        }

        return $contract->pages()->saveMany($pages);
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
     * @return bool
     */
    public function renameTxtFiles($directory)
    {
        $filePath = $directory.'/text';
        $files = $this->file->files($filePath);

        foreach ($files as $file) {
            $pageNo = $this->getPageNo($file);
            if (!$this->file->move($file, $filePath.'/'.$pageNo.'.txt')) {
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
        $files    = $this->file->files($filePath);

        foreach ($files as $file) {
            if ($this->file->extension($file) == 'pdf') {
                $pageNo = $this->getPageNo($file);

                if (!$this->file->move($file, $filePath.'/'.$pageNo.'.pdf')) {
                    return false;
                }
            }
        }

        return true;
    }
}
