<?php namespace App\Console\Commands;

use App\Nrgi\Services\Importer\ImportManager;
use Illuminate\Console\Command;

/**
 * Class ApiImporter
 * @package App\Console\Commands
 */
class ApiImporter extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'nrgi:apiImporter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull contracts from API and index in elasticSearch.';
    /**
     * @var ApiService
     */
    protected $importer;

    /**
     * Create a new command instance.
     *
     * @param ImportManager $importer
     */
    public function __construct(ImportManager $importer)
    {
        parent::__construct();
        $this->importer = $importer;
    }

    /**
     * Execute the console command.
     *
     */
    public function fire()
    {
        $this->importer->run();
    }
}
