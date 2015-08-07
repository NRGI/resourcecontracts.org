<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

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
    protected $description = 'reads json files insert to db.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        //
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
