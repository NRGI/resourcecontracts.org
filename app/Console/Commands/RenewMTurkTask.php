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
        $expired_pages = $task->getExpired();

        $pages = $this->setPriority($expired_pages);

        $availableBalance = (int) $task->getMturkBalance();

        if (!$this->isSufficientBalance($availableBalance)) {
            return false;
        }

        foreach ($pages as $key => $page) {

            $page = $task->updateAssignment($page);

            if ($page->status == Task::COMPLETED || $page->assignments['assignment']['status'] == 'Approved' ||
                $page->assignments['assignment']['status'] == 'Rejected') {
                continue;
            }

            if (!$this->isSufficientBalance($availableBalance)) {
                break;
            }

            $contractId = $page->contract_id;
            $hitId      = $page->hit_id;
            $pageNumber = $page->page_no;
            $pageId     = $page->id;

            if ($task->resetHIT($contractId, $pageId)) {
                $availableBalance = $availableBalance - (config('mturk.defaults.production.Reward.Amount') * 1.20);
                $this->info(
                    sprintf('Contract ID : %s with HIT: %s, Page no: %s updated', $contractId, $hitId, $pageNumber)
                );
            } else {
                $this->error(
                    sprintf('Contract ID : %s with HIT: %s, Page no: %s failed', $contractId, $hitId, $pageNumber)
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

        foreach ($count as $id => $v) {
            $cons = $contracts[$id];
            foreach ($cons as $page) {
                $pages[] = $page;
            }
        }

        return $pages;
    }
}
