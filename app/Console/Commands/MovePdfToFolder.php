<?php namespace App\Console\Commands;

use App\Nrgi\Services\Contract\ContractService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class MovePdfToFolder extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'nrgi:movepdf';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move main pdf to contract folder.';
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
        $this->movePdFToFolder($contract_id);
    }

    /**
     * Move pdf file
     *
     * @param null $contract_id
     * @return bool
     */
    public function movePdFToFolder($contract_id = null)
    {
        if (is_null($contract_id)) {
            $contracts = $this->contract->getProcessCompleted();

            foreach ($contracts as $contract) {
                $file   = $contract->file;
                $moveTo = sprintf('%s/%s', $contract->id, $contract->file);
                if ($this->contract->moveS3File($file, $moveTo)) {
                    $this->info(sprintf('Contract %s : completed.', $contract_id));
                    continue;
                }

                $this->info(sprintf('Contract %s : failed.', $contract_id));
            }

            return true;
        }

        $contract = $this->contract->find($contract_id);
        $file     = $contract->file;
        $moveTo   = sprintf('%s/%s', $contract->id, $contract->file);

        if ($this->contract->moveS3File($file, $moveTo)) {
            $this->info(sprintf('Contract %s : completed.', $contract_id));

            return true;
        }

        $this->info(sprintf('Contract %s : failed.', $contract_id));

        return true;
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
