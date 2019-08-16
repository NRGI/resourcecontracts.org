<?php namespace App\Nrgi\Mturk\Services;

use App\Nrgi\Mail\MailQueue;
use App\Nrgi\Services\Contract\ContractService;
use Illuminate\Contracts\Logging\Log;

/**
 * Class MTurkNotificationService
 * @package App\Nrgi\Mturk\Services
 */
class MTurkNotificationService
{
    /**
     * @var TaskService
     */
    protected $task;
    /**
     * @var ContractService
     */
    protected $contract;
    /**
     * @var Log
     */
    protected $logger;
    /**
     * @var MailQueue
     */
    protected $mailer;
    /**
     * @var MTurkService
     */
    protected $mturk;

    /**
     * @param ContractService $contract
     * @param TaskService     $task
     * @param MTurkService    $mturk
     * @param Log             $logger
     * @param MailQueue       $mailer
     *
     * @internal param MTurkService $turk
     */
    public function __construct(
        ContractService $contract,
        TaskService $task,
        MTurkService $mturk,
        Log $logger,
        MailQueue $mailer
    ) {
        $this->contract = $contract;
        $this->logger   = $logger;
        $this->mailer   = $mailer;
        $this->task     = $task;
        $this->mturk    = $mturk;
    }

    /**
     * Display all the tasks for a specific contract
     *
     * @param $contract_id
     *
     * @return string
     */
    public function process($contract_id)
    {
        $contract             = $this->contract->findWithTasks($contract_id);
        $contract->tasks      = $this->task->appendAssignment($contract->tasks);
        $tasks                = $this->task->getTotalByStatus($contract_id);
        $tasks['total_pages'] = $contract->tasks->count();

        if ($tasks['total_pending_approval'] > 0) {
            $recipients = $this->getRecipients($contract);
            $this->mailer->send(
                $recipients,
                sprintf("Mturk assignments for your action for [%s]", $contract->title),
                'mturk.email.notify',
                [
                    'task'     => $tasks,
                    'contract' => ['id' => $contract->id, 'title' => $contract->title],
                ]
            );
        }

    }

    /**
     * gets recipients for mturk notification
     *
     * @param $contract
     *
     * @return array
     */
    public function getRecipients($contract)
    {
        $type         = $contract->metadata->category;
        $resourceType = isset($type[0]) ? $type[0] : "";
        $recipients   = $this->mailer->getMTurkNotifyEmails($resourceType);

        return $recipients;
    }

    /**
     * Check minimum balance
     *
     */
    public function checkBalance()
    {
        $minimum_balance = config('mturk.minimumBalance');
        $balance         = $this->mturk->getBalance();
        
        if ($balance < $minimum_balance) {
            $this->mailer->send(
                $this->mailer->getNotifyEmails(),
                "MTurk balance is getting low.",
                'mturk.email.balance',
                [
                    'balance' => $balance,
                ]
            );
        }
    }

}
