<?php namespace App\Nrgi\Mturk\Services;

use App\Nrgi\Mail\MailQueue;
use App\Nrgi\Mturk\Services\TaskService;
use App\Nrgi\Services\Contract\ContractService;
use Exception;
use Illuminate\Contracts\Logging\Log;

/**
 * Class MTurkNotificationService
 * @package App\Nrgi\Mturk\Services
 */
class MTurkNotificationService
{
    /**
     * @var Task
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
     * @param ContractService $contract
     * @param Task            $task
     * @param Log             $logger
     * @param MailQueue       $mailer
     * @internal param MTurkService $turk
     */
    public function __construct(
        ContractService $contract,
        TaskService $task,
        Log $logger,
        MailQueue $mailer
    ) {
        $this->contract = $contract;
        $this->logger   = $logger;
        $this->mailer   = $mailer;
        $this->task     = $task;
    }


    /**
     * Display all the tasks for a specific contract
     * @param $contract_id
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
            $this->mailer->sendMultiple(
                $recipients,
                sprintf("Mturk assignments for your action for [%s]", $contract->title),
                'mturk.email.notify',
                [
                    'task' => $tasks,
                    'contract' => ['id' => $contract->id, 'title' => $contract->title],
                ]
            );
        }

    }

    /**
     * gets recipients for mturk notification
     *
     * @param $contract
     * @return array
     */
    public function getRecipients($contract)
    {
        $recipients   = [];
        $uploader     = $contract->created_user->email;
        $type         = $contract->metadata->category;
        $resourceType = isset($type[0]) ? $type[0] : "";
        if (env('MTURK_NOTIFY_' . strtoupper($resourceType))) {
            $recipients = explode(',', env('MTURK_NOTIFY_' . strtoupper($resourceType)));
        }
        array_push($recipients, $uploader);
        
        return $recipients;
    }
}
