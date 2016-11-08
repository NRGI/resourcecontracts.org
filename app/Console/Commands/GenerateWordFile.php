<?php namespace App\Console\Commands;

use App\Nrgi\Services\Contract\ContractService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class GenerateWordFile
 * @package App\Console\Commands
 */
class GenerateWordFile extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'nrgi:moveword';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and copy word file to S3.';
    /**
     * @var ContractService
     */
    protected $contract;

    /**
     * Create a new command instance.
     * @param ContractService $contract
     */
    public function __construct(ContractService $contract)
    {
        parent::__construct();
        $this->contract = $contract;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $contract_id = $this->input->getOption('id');
        $this->generateWordFile($contract_id);
    }

    /**
     * Generate word file from console command
     *
     * @param $contract_id
     * @return bool
     */
    protected function generateWordFile($contract_id)
    {
        if (is_null($contract_id)) {
            $contracts = $this->contract->getProcessCompleted();
            if (!is_null($contracts)) {
                foreach ($contracts as $contract) {
                    $this->generateWordFile($contract->id);
                }

                return true;
            }
            $this->info('Contract not found');

            return false;
        }

        if ($this->contract->updateWordFile($contract_id)) {
            $this->info(sprintf('Contract %s : completed.', $contract_id));

            return true;
        } else {
            $this->info(sprintf('Contract %s : failed.', $contract_id));

            return false;
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
            ['id', null, InputOption::VALUE_OPTIONAL, 'ID of the contract', null],
        ];
    }

}
