<?php namespace App\Console\Commands;

use App\Nrgi\Mturk\Entities\Task;
use App\Nrgi\Mturk\Services\TaskService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Class RenewMTurkTask
 * @package App\Console\Commands
 */
class RenewMTurkTask extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'nrgi:renewmturktask';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Renew MTurk Task after 20 days';

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
     * @param TaskService $task
     *
     * @return bool
     */
    public function fire(TaskService $task)
    {
        $expiredMturkTasks = $task->getExpired();

        $mturkTasks = $this->setPriority($expiredMturkTasks);

        $availableBalance = (int) $task->getMturkBalance();

        if (!$this->isSufficientBalance($availableBalance)) {
            return false;
        }

        foreach ($mturkTasks as $key => $mturkTask) {

            $mturkTask = $task->updateAssignment($mturkTask);
            $allTaskItems = $mturkTask->taskItems->toArray();
            if ($mturkTask->status == Task::COMPLETED || $mturkTask->assignments['assignment']['status'] == 'Approved' ||
                $mturkTask->assignments['assignment']['status'] == 'Rejected' || count($allTaskItems) < 1) {
                continue;
            }

            if (!$this->isSufficientBalance($availableBalance)) {
                break;
            }

            $contractId = $mturkTask->contract_id;
            $hitId      = $mturkTask->hit_id;
            $all_pages_str =  join(',', $task->getAllPages($allTaskItems));
            $pageId     = $mturkTask->id;
            if ($task->resetHIT($contractId, $pageId, $mturkTask->hit_description)) {
                $availableBalance = $availableBalance - (config('mturk.defaults.production.Reward.Amount') * 1.20);
                $this->info(
                    sprintf('Contract ID : %s with HIT: %s, Page nos: %s updated', $contractId, $hitId, $all_pages_str)
                );
            } else {
                $this->error(
                    sprintf('Contract ID : %s with HIT: %s, Page nos: %s failed', $contractId, $hitId, $all_pages_str)
                );
            }
        }

        $this->info('Process Completed');

        $file = storage_path().'/logs/scheduler.log';
        Log::useFiles($file);
        Log::info("Renew Mturk command successfully executed");
    }

    /**
     * Check for sufficient Balance
     *
     * @param $balance
     *
     * @return bool
     */
    protected function isSufficientBalance($balance)
    {
        return ($balance >= 0.50);
    }

    /**
     * @param $expiredPages
     *
     * @return array
     */
    private function setPriority($expiredMturkTasks)
    {
        $contracts = [];
        foreach ($expiredMturkTasks as $mturkTask) {
            $contracts[$mturkTask->contract_id][] = $mturkTask;
        }
        $count = [];

        foreach ($contracts as $id => $contract) {
            $count[$id] = 0;
            foreach($contract as $key =>$mturkTask) {
                $taskItemsCount = count($mturkTask->taskItems);
                $count[$id] = $count[$id]  + $taskItemsCount > 0 ? $taskItemsCount : 1;
            }

        }
        asort($count);
        $mturkTasks = [];

        foreach ($count as $id => $v) {
            $cons = $contracts[$id];
            foreach ($cons as $mturkTask) {
                $mturkTasks[] = $mturkTask;
            }
        }

        return $mturkTasks;
    }
}
