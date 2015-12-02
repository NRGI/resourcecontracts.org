<?php namespace App\Console\Commands;

use App\Nrgi\Mturk\Services\TaskService;
use App\Nrgi\Services\Contract\ContractService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class CreateMTurkTasks
 * @package App\Console\Commands
 */
class CreateMTurkTasks extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'nrgi:createtasks';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create MTurk Tasks.';

	/**
	 * Create a new command instance.
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @param ContractService $contract
	 * @param TaskService     $task
	 * @return mixed
	 */
	public function fire(ContractService $contract,TaskService $task)
	{
		$contract_id = $this->input->getArgument('id');

		$contract = $contract->findWithPages($contract_id);

		if($task->sendToMTurk($contract)) {
			$this->info(sprintf('Contract sent to MTurk id : %s',  $contract->id));
			return true;
		}
		$this->info(sprintf('Contract sent to MTurk id : %s',  $contract->id));
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			['id', InputArgument::REQUIRED, 'Contract ID.'],
		];
	}

}
