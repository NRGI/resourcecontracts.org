<?php namespace App\Console\Commands;

use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Services\Contract\ContractService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Filesystem\Filesystem as File;
use Illuminate\Contracts\Filesystem\Factory as Storage;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use App\Nrgi\Services\Contract\Page\ProcessService;

/**
 * Command to process documents
 *
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
     * @var ProcessService
     */
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
    public function __construct(ContractService $contract, Storage $storage, File $file, ProcessService $process)
    {
        parent::__construct();

        $this->storage  = $storage;
        $this->contract = $contract;
        $this->file     = $file;
        $this->process  = $process;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->info('processing contract document');
        $contractId = $this->input->getArgument('contract_id');
        try {
            $contract = $this->contract->find($contractId);
            if ($this->input->getOption('force')) {
                $contract->pages()->delete();
            }

            if ($this->process->execute($contractId)) {
                $this->info('processing completed.');
            } else {
                $this->error('Error processing contract document.check log for detail');
            }

        } catch (ModelNotFoundException $exception) {
            $this->error('could cot find contract.' . $exception->getMessage());
        } catch (\Exception $exception) {
            $this->error('processing contract document.' . $exception->getMessage());
        }
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
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run.', null],
        ];
    }
}
