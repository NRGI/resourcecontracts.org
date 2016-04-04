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

        foreach ($pages as $key => $page) {

            $expiredPages = $task->getExpired();

            $pages = $this->setPriority($expiredPages);

            $currentBalance   = $task->getMturkBalance();
            $availableBalance = $currentBalance['Amount'];

            foreach ($pages as $key => $page) {

                $page = $task->updateAssignment($page);

                if ($page->status == Task::COMPLETED || $page->assignments['assignment']['status'] == 'Approved') {
                    continue;
                }

                if ($availableBalance <= 0.50) {
                    continue;
                }

                $contractId = $page->contract_id;
                $hitId      = $page->hit_id;
                $pageNumber = $page->page_no;
                $pageId     = $page->id;

                if ($task->resetHIT($contractId, $pageId)) {
                    $availableBalance = $availableBalance - (config('mturk.defaults.production.Reward.Amount') * 1.20);
                    $this->info(sprintf('Contract ID : %s with HIT: %s, Page no: %s updated', $contractId, $hitId, $pageNumber));
                } else {
                    $this->error(sprintf('Contract ID : %s with HIT: %s, Page no: %s failed', $contractId, $hitId, $pageNumber));
                }


            }

            $this->info('Process Completed');
        }
    }

    /**
     * @param $expiredPages
     * @return array
     */
    private function setPriority($expiredPages)
    {
        $contracts = [];
        foreach ($expiredPages as $page) {
            $contracts[$page->contract_id][] = $page;
        }
        $count = [];

        foreach ($contracts as $id => $contract) {
            $count[$id] = count($contract);

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
