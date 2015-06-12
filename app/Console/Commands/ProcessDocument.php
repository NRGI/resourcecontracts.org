<?php namespace App\Console\Commands;

use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Entities\Contract\Pages\Pages;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Filesystem\Filesystem as File;
use Illuminate\Contracts\Filesystem\Factory as Storage;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use App\Nrgi\Services\Contract\Page\ProcessService;

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

    protected $process;

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
    public function __construct(Contract $contract, Storage $storage, File $file ,ProcessService $process)
    {
        $this->storage = $storage;
        $this->contract = $contract;
        $this->file = $file;
        $this->process = $process;
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
        $this->process->execute($contractId);

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

}
