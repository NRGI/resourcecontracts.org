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
        $expired_pages = $task->getExpired();

        $pages = $this->setPriority($expired_pages);

        $current_balance = $task->getMturkBalance();
        $available_balance =  $current_balance['Amount'];

        foreach ($pages as $key => $page)
        {
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
                $page_id =  $page->id;
            if ($task->resetHIT($contract_id, $page_id)) {
                    $current_balance = $available_balance - (config('mturk.defaults.production.Reward.Amount') * 1.20);
                    $this->info(sprintf('Contract ID : %s with HIT: %s, Page no: %s updated', $contract_id, $hit_id, $page_no));
                } else {
                    $this->error(sprintf('Contract ID : %s with HIT: %s, Page no: %s failed', $contract_id, $hit_id, $page_no));
                }



        }

        $this->info('Process Completed');
    }

    /**
     * Set Priority for pages
     *
     * @param $expired_pages
     * @return array
     */
    private function setPriority ($expired_pages)
    {
        $contracts=[];
        foreach($expired_pages  as $page)
        {
            $contracts[$page->contract_id][] = $page;
        }

        $count = [];
        foreach($contracts as $id => $contract)
        {
            $count[$id] =  count($contract);
        }

        asort($count);

        $pages = [];

        foreach($count as $id => $v)
        {
            $cons = $contracts[$id];
            foreach($cons as $page)
            {
                $pages[] = $page;
            }
        }
        return $pages;
    }
}
