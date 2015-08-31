<?php namespace App\Console\Commands;

use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Services\Contract\ContractFilterService;
use App\Nrgi\Services\Contract\EthiopianMigrationService;
use App\Nrgi\Services\Contract\MigrationService;
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
    protected $migration;
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
     * @var MigrationService
     */
    protected $mService;
    /**
     * @var ContractFilterService
     */
    protected $contract;

    /**
     * Create a new command instance.
     *
     * @param EthiopianMigrationService|MigrationService $migration
     * @param Filesystem                                 $fileSystem
     * @param Excel                                      $excel
     * @param Log                                        $logger
     * @param MigrationService                           $mService
     * @param ContractService                            $contract
     * @internal param MigrationService $migrate
     */
    public function __construct(
        EthiopianMigrationService $migration,
        Filesystem $fileSystem,
        Excel $excel,
        Log $logger,
        MigrationService $mService,
        ContractFilterService $contract
    ) {
        parent::__construct();
        $this->migration  = $migration;
        $this->fileSystem = $fileSystem;
        $this->excel      = $excel;
        $this->logger     = $logger;
        $this->mService   = $mService;
        $this->contract   = $contract;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        if ($this->input->getOption('rebuild')) {
            $data = $this->extractCsvRecords($this->getCsv());
            $this->processExcel($data);
            $this->readFromExcel();
        }
        if ($this->input->getOption('annotation')) {
            $this->updateOlcAnnotation();
        } else {
            $this->readFromJson();
        };
    }

    /**
     *
     */
    public function updateOlcAnnotation()
    {
        $files = $this->fileSystem->files($this->getConvertedJsonDir());

        foreach ($files as $file) {
            $contractJson = json_decode(file_get_contents($file), 1);

            $name     = urldecode(pathinfo($contractJson['contract_name'], PATHINFO_FILENAME));
            $query    = Contract::select('*');
            $contract = $query->whereRaw(
                sprintf("contracts.metadata->>'contract_name'='%s'", $name)
            )->first();
            if (!is_null($contract)) {
                $annotations = $this->migration->refineAnnotation($contractJson['annotations']);
                $this->migration->saveAnnotations($contract->id, $annotations);
                $this->info(sprintf('Success - %s - %s', $file, $contract->title));
                continue;
            }

            $this->error(sprintf('Failed - %s', $file));
        }

        $this->info('done');
        //list of olc contract;
        //delete contract
        //insert updated annotations
        //remove
    }

    public function readFromJson()
    {
        $files = $this->fileSystem->files($this->getConvertedJsonDir());

        if (count($files) < 1) {
            $this->error('Json file not found');

            return;
        }

        foreach ($files as $file) {
            $contract = json_decode(file_get_contents($file), 1);

            if (!is_null($contract)) {
                $this->info(sprintf('Reading - %s', $file));
                $contractArray = $this->migration->setupContract($contract);
                $contractObj   = json_decode(json_encode($contractArray), false);
                $con           = $this->mService->uploadPdfToS3AndCreateContracts($contractObj->data);
                if ($con) {
                    $this->migration->saveAnnotations($con->id, $contractArray['annotations']);
                    $this->info(sprintf('Success - %s - %s', $file, $contractObj->data->metadata->contract_name));
                }
                continue;
            }

            $this->error(sprintf('Failed - %s', $file));
        }

        $this->info('done');
    }

    public function processExcel($data)
    {
        foreach ($data as $contract) {
            if (!is_null($contract['metadata'])) {
                $this->info("downloading {$contract['m_link_template']}");
                $this->migration->setPdfUrl($contract['m_link_template']);
                $contractDir = $this->migration->downloadExcel($contract['m_link_template']);

                \File::put($this->migration->getConvertedDir($contractDir) . "/data.json", json_encode($contract));
                //$this->readFiles($this->migration->getConvertedDir($contractDir));
                $this->info("done!");
            }
        }
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
     * Read and extract records from file
     *
     * @param $file
     * @return array
     */
    protected function extractCsvRecords($file)
    {
        return $this->excel->load(
            $file
        )->all()->toArray();
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
        try {

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
            if (count($files) == 4) {
                $filetype = "xlsm";
            }
            $excelData = json_decode(file_get_contents($dir . "/data.json"), 1);

            $this->migration->setPdfUrl($excelData['m_pdf_url']);
            $this->migration->setFileName($contractName);
            $this->migration->setFileType($filetype);
            foreach ($files as $file) {
                $type = basename($file, ".csv");
                if ($type != "picklists") {
                    $data[$type] = $this->extractRecords($filetype, $file);
                }
            }
            $this->fileSystem->deleteDirectory($dir);
            $this->migration->setData($data);
            $this->info("done {$contractName}");

            \File::put($this->getJsonDir($contractName), json_encode($this->migration->run()));

        } catch (\Exception  $e) {
            $this->error($e->getMessage());
        }
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
            ['annotation', null, InputOption::VALUE_NONE, 'updates annotations.', null],
            ['rebuild', null, InputOption::VALUE_NONE, 'updates annotations.', null],
        ];
    }

    /**
     * @return string
     */
    protected function getConvertedJsonDir()
    {
        return public_path('ethiopian-contracts/json');
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
     * @return string
     */
    protected function getCsv()
    {
        return public_path("OLC_data_migration.csv");
    }

    /**
     * @param $file
     * @return array
     */
    protected function setConfig($file)
    {
        $type = basename($file, ".csv");

        $columns = ['category', 'terms'];
        config()->set('excel.import.startRow', 1);
        if ($type == "Categories") {
            config()->set('excel.import.startRow', 2);
            if (preg_match('/Heng%20Yue_Cambodia.xlsx/', $file)
                || preg_match('/Company_Sierra%20Leone/', $file)
                || preg_match('/Holdings_Sierra%20Leone.xlsx/', $file)
                || preg_match('/Nile%20Trading%20%26%20Development_South%20Sudan/', $file)
            ) {
                config()->set('excel.import.startRow', 3);
            }
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
