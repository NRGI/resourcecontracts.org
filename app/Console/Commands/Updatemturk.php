<?php namespace App\Console\Commands;

use App\Nrgi\Mturk\Entities\Task;
use App\Nrgi\Mturk\Services\TaskService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class Updatemturk extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'nrgi:updatemturk';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Update Mturk Tasks';

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
	 * @param Task        $task
	 * @param TaskService $taskService
	 * @return mixed
	 */
	public function fire(Task $task, TaskService $taskService)
	{
		$contract_id = $this->input->getArgument('id');
		$contract_tasks = $task->pending()->where('contract_id',$contract_id)->get();

		foreach($contract_tasks as $page)
		{
			$contract_id = $page->contract_id;
			$hit_id      = $page->hit_id;
			$page_no     = $page->page_no;

			if ($taskService->resetHIT($contract_id, $page->id)) {
				$this->info(sprintf('Contract ID : %s with HIT: %s, Page no: %s updated', $contract_id, $hit_id, $page_no));
			} else {
				$this->error(sprintf('Contract ID : %s with HIT: %s, Page no: %s failed', $contract_id, $hit_id, $page_no));
			}
		}

		$this->info('Process Completed');
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
