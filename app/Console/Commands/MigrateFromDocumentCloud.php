<?php namespace App\Console\Commands;

use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Services\Contract\MigrationService;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Excel;
use Symfony\Component\Console\Input\InputOption;
use Illuminate\Filesystem\Filesystem;

/**
 * reads json files insert to db
 *
 * Class MigrateFromDocumentCloud
 * @package App\Console\Commands
 */
class MigrateFromDocumentCloud extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'nrgi:documentcloud';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reads json files insert to db.';
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
     * Create a new command instance.
     *
     * @param MigrationService $migration
     * @param Filesystem       $fileSystem
     * @param Excel            $excel
     * @internal param MigrationService $migrate
     */
    public function __construct(MigrationService $migration, Filesystem $fileSystem, Excel $excel)
    {
        parent::__construct();
        $this->migration  = $migration;
        $this->fileSystem = $fileSystem;
        $this->excel      = $excel;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        if ($this->input->getOption('update')) {
            $this->updateFromXl();

            return "";
        }
        $this->readFromJson();
    }

    /**
     * update from excel file
     */
    public function updateFromXl()
    {
        $contracts = $this->extractRecords($this->getFile());
        foreach ($contracts as $contractXlData) {
            $query    = Contract::select('*');
            $contract = $query->whereRaw(
                sprintf("contracts.metadata->>'documentcloud_url'='%s'", $contractXlData['m_documentcloud_url'])
            )->first();
            if ($contract) {
                $contract->metadata = $this->migration->buildContractMetadata($contractXlData, $contract);
                $contract->save();
                $this->info(sprintf('Success - %s - %s', "done", $contractXlData['m_contract_name']));
            } else {
                $this->info(sprintf('Failed - %s - %s', "contract not found", $contractXlData['m_contract_name']));
            }

            $this->info(sprintf('Success - %s', $contractXlData['m_contract_name']));
        }
        $this->info("Done!");
    }

    /**
     * Read and extract records from file
     *
     * @param $file
     * @return array
     */
    protected function extractRecords($file)
    {
        return $this->excel->load($file)->all()->toArray();
    }


    /**
     * Get File
     *
     * @param string $key
     * @param string $fileName
     * @return string
     */
    public function getFile()
    {
        return public_path() . "/dc_contracts.csv";
    }

    /**
     * Document cloud contract from json file
     */
    public function readFromJson()
    {
        $files = $this->fileSystem->files($this->getDir());
        $data  = [];

        if (count($files) < 1) {
            $this->error('Json file not found');

            return;
        }

        foreach ($files as $file) {
            $this->migration->setData($file);
            $contract = $this->migration->run();
            $data[]   = $contract;
            if (!is_null($contract)) {

                $con = $this->migration->uploadPdfToS3AndCreateContracts($contract);

                $this->info(sprintf('Success - %s - %s', $file, $contract->metadata->contract_name));

                if (!empty($contract->annotations)) {
                    $this->migration->saveAnnotations($con, $contract->annotations);
                }

                $this->moveFile($file);

                continue;
            }

            $this->error(sprintf('Failed - %s', $file));
        }

        $this->info('done');
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
     * @param $file
     */
    protected function moveFile($file)
    {
        $done = $this->getDir('done');

        if (!$this->fileSystem->isDirectory($done)) {
            $this->fileSystem->makeDirectory($done);
        }

        $done = $this->getDir('done/' . basename($file));
        $this->fileSystem->move($file, $done);
    }

    /**
     * @param string $dir
     * @return string
     */
    protected function getDir($dir = '')
    {
        return public_path('api-data' . '/' . $dir);
    }

}
