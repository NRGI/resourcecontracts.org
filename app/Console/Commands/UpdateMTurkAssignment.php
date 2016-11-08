<?php namespace App\Console\Commands;

use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Mturk\Services\TaskService;
use App\Nrgi\Services\Contract\ContractService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class UpdateMTurkAssignment extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'nrgi:updatemturktasks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update MTurk Assignments.';
    /**
     * @var ContractService
     */
    protected $contract;
    /**
     * @var TaskService
     */
    protected $task;

    /**
     * Create a new command instance.
     *
     * @param ContractService $contract
     * @param TaskService     $task
     */
    public function __construct(ContractService $contract, TaskService $task)
    {
        parent::__construct();
        $this->contract = $contract;
        $this->task     = $task;
    }

    /**
     * Execute the console command.
     *
     */
    public function fire()
    {
        $contract_id = $this->input->getOption('id');
        if (!is_null($contract_id)) {
            $contracts = Contract::with('tasks')->where('id', $contract_id)->where('mturk_status', Contract::MTURK_SENT)->get();
        } else {
            $contracts = $this->contract->getMTurkContracts(['status' => Contract::MTURK_SENT]);
        }

        foreach ($contracts as $contract) {
            foreach ($contract->tasks as $page) {
                $this->task->updateAssignment($page);
            }
        }
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['id', null, InputOption::VALUE_OPTIONAL, 'Contract ID.', null],
        ];
    }
}
