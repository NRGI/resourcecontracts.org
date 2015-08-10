<?php namespace App\Console\Commands;

use App\Nrgi\Services\Contract\MigrationService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
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
     * Create a new command instance.
     *
     * @param MigrationService $migration
     * @param Filesystem       $fileSystem
     * @internal param MigrationService $migrate
     */
    public function __construct(MigrationService $migration, Filesystem $fileSystem)
    {
        parent::__construct();
        $this->migration  = $migration;
        $this->fileSystem = $fileSystem;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $filePath = public_path('api-data');
        $files    = $this->fileSystem->allFiles($filePath);

        foreach ($files as $file) {
            $this->migration->setData($file);
            $contract = $this->migration->run();

            if (!is_null($contract)) {
                $this->migration->uploadPdfToS3AndCreateContracts($contract);
                $this->info(sprintf('Success - %s - %s', $file, $contract->metadata->contract_name));
                continue;
            }
            $this->error(sprintf('Failed - %s', $file));
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
            ['file', null, InputOption::VALUE_OPTIONAL, 'path of file.', null],
        ];
    }

}
