<?php namespace App\Console\Commands;

use App\Nrgi\Mturk\Entities\Task;
use App\Nrgi\Mturk\Services\TaskService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Support\Facades\Log;

class ResetMTurkTasks extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'nrgi:resetmturk';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Reset MTurk Tasks';

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
		$contract_tasks = $task->pending()->where('contract_id',$contract_id)->with('taskItems')->get();

		foreach($contract_tasks as $mturk_task)
		{
			$contract_id = $mturk_task->contract_id;
			$hit_id      = $mturk_task->hit_id;
			$all_pages_str     = join(',', $task->getAllPages($mturk_task->taskItems->toArray()));

			if ($taskService->resetHIT($contract_id, $mturk_task->id,'')) {
				$this->info(sprintf('Contract ID : %s with HIT: %s, Page no: %s updated', $contract_id, $hit_id, $all_pages_str));
			} else {
				$this->error(sprintf('Contract ID : %s with HIT: %s, Page no: %s failed', $contract_id, $hit_id, $all_pages_str));
			}
		}

		$this->info('Process Completed');

		$file = storage_path().'/logs/scheduler.log';
		Log::useFiles($file);
		Log::info("Reset MTurk command successfully executed.");
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
