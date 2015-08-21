<?php namespace App\Console\Commands;

use App\Nrgi\Services\Contract\EthiopianMigrationService;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Excel;
use Symfony\Component\Console\Input\InputOption;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Contracts\Logging\Log;

/**
 * reads Excel files insert to db
 *
 * Class MigrateEthiopianContracts
 * @package App\Console\Commands
 */
class MigrateEthiopianContracts extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'nrgi:migrate-ethiopian';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reads excel  files insert to db.';
    /**
     * @var MigrationService
     */
    public $migration;
    /**
     * @var Filesystem
     */
    protected $fileSystem;
    /**
     * @var Excel
     */
    protected $excel;
    /**
     * @var Log
     */
    protected $logger;

    /**
     * Create a new command instance.
     *
     * @param MigrationService $migration
     * @param Filesystem       $fileSystem
     * @param Excel            $excel
     * @internal param MigrationService $migrate
     */
    public function __construct(EthiopianMigrationService $migration, Filesystem $fileSystem, Excel $excel, Log $logger)
    {
        parent::__construct();
        $this->migration  = $migration;
        $this->fileSystem = $fileSystem;
        $this->excel      = $excel;
        $this->logger     = $logger;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $this->readFromExcel();
    }

    /**
     * Read and extract records from file
     *
     * @param $file
     * @return array
     */
    protected function extractRecords($fileType, $file)
    {
        $columns = $this->setConfig($file);

        if ($fileType == "xlsm") {
            $columns = $this->setXlsmConfig($file);
        }

        return $this->excel->load(
            $file
        )->get($columns)->toArray();
    }

    /**
     * Ethopian contract from excel file
     */
    public function readFromExcel()
    {
        $contractDir = $this->fileSystem->directories($this->getDir());
        foreach ($contractDir as $dir) {
            $this->readFiles($dir);
        }
    }

    /**
     * @param $dir
     * @return array|void
     * @internal param $data
     * @internal param $file
     */
    public function readFiles($dir)
    {
        $files        = $this->fileSystem->files($dir);
        $data         = [];
        $contractName = basename($dir);
        if (count($files) < 1) {
            $this->error('file not found');

            return;
        }
        $this->info("reading {$contractName}");
        $this->migration->setContractName($contractName);
        $filetype = "xlsx";
        if (count($files) == 3) {
            $filetype = "xlsm";
        }
        $this->migration->setFileName($contractName . '.' . $filetype);
        $this->migration->setFileType($filetype);
        foreach ($files as $file) {
            $type = basename($file, ".csv");
            if ($type != "picklists") {
                $data[$type] = $this->extractRecords($filetype, $file);
            }
        }
        $this->fileSystem->deleteDirectory($dir);
        $this->migration->setData($data);
        $this->info("done reading {$contractName}");

        \File::put($this->getJsonDir($contractName), json_encode($this->migration->run()));
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['update', null, InputOption::VALUE_NONE, 'Force the operation to run.', null],
        ];
    }

    /**
     * @return string
     */
    protected function getDir()
    {
        return public_path('ethiopian-contracts/data/converted');
    }

    /**
     * @return string
     */
    protected function getJsonDir($file)
    {
        return public_path("ethiopian-contracts/json/{$file}.json");
    }

    /**
     * @param $file
     * @return array
     */
    protected function setConfig($file)
    {
        $type    = basename($file, ".csv");
        $columns = ['category', 'terms'];
        config()->set('excel.import.startRow', 1);
        if ($type == "Categories") {
            config()->set('excel.import.startRow', 2);
            $columns = ['francais', 'english', 'details', 'articlereference', 'page_permalink'];

            return $columns;
        }

        return $columns;
    }

    /**
     * @param $file
     * @return array
     */
    protected function setXlsmConfig($file)
    {
        $type    = basename($file, ".csv");
        $columns = ['category', 'terms'];
        config()->set('excel.import.startRow', 1);
        if ($type == "Categories") {
            config()->set('excel.import.startRow', 4);
            $columns = [
                'francais',
                'english',
                'details_value',
                'articlereference',
                'page_permalink_page_page_top_middle_bottom'
            ];

            return $columns;
        }

        return $columns;
    }

}
