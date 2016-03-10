<?php namespace App\Console\Commands;

use App\Nrgi\Mturk\Entities\Task;
use App\Nrgi\Mturk\Services\TaskService;
use Illuminate\Console\Command;

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
    protected $description = 'Renew MTurk Task after 10 days';

    /**
     * Create a new command instance.
     */
    public function __construct ()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param TaskService $task
     */
    public function fire (TaskService $task)
    {
        $pages = $task->getExpired();

        $current_balance = $task->getMturkBalance();
        dd($current_balance);
        foreach ($pages as $key => $page) {

            $page = $task->updateAssignment($page);

            if ($page->status == Task::COMPLETED || $page->assignments['assignment']['status'] == 'Approved') {
                continue;
            }

            if ($current_balance <= 0.50) {
                continue;
            }

            $contract_id = $page->contract_id;
            $hit_id      = $page->hit_id;
            $page_no     = $page->page_no;
            if ($task->resetHIT($contract_id, $page->id)) {
                $current_balance = $current_balance - (config('mturk.defaults.production.Reward.Amount') * 1.20);
                $this->info(sprintf('Contract ID : %s with HIT: %s, Page no: %s updated', $contract_id, $hit_id, $page_no));
            } else {
                $this->error(sprintf('Contract ID : %s with HIT: %s, Page no: %s failed', $contract_id, $hit_id, $page_no));
            }
        }

        $this->info('Process Completed');
    }
}
