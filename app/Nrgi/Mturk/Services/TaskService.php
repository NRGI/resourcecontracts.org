<?php namespace App\Nrgi\Mturk\Services;

use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Mturk\Entities\Task;
use App\Nrgi\Mturk\Repositories\TaskRepositoryInterface;
use App\Nrgi\Services\ActivityLog\ActivityLogService;
use App\Nrgi\Services\Contract\ContractService;
use App\Nrgi\Services\Contract\Page\PageService;
use Exception;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class TaskService
 * @package App\Nrgi\Mturk\Services
 */
class TaskService
{
    /**
     * @var TaskRepositoryInterface
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
     * @var MTurkService
     */
    protected $turk;
    /**
     * @var PageService
     */
    protected $page;
    /**
     * @var String
     */
    protected $task_url;
    /**
     * @var Queue
     */
    protected $queue;
    /**
     * @var int
     */
    protected $perPage = 50;
    /**
     * @var ActivityLogService
     */
    protected $logService;

    /**
     * @param TaskRepositoryInterface $task
     * @param ContractService         $contract
     * @param Log                     $logger
     * @param MTurkService            $turk
     * @param PageService             $page
     * @param Queue                   $queue
     * @param ActivityLogService      $logService
     */
    public function __construct(
        TaskRepositoryInterface $task,
        ContractService $contract,
        Log $logger,
        MTurkService $turk,
        PageService $page,
        Queue $queue,
        ActivityLogService $logService
    ) {
        $this->task       = $task;
        $this->contract   = $contract;
        $this->logger     = $logger;
        $this->turk       = $turk;
        $this->page       = $page;
        $this->queue      = $queue;
        $this->logService = $logService;
        $this->task_url   = $this->getMTurkPageUrl();
    }

    /**
     * Get Contracts having MTurk Tasks
     *
     * @param array $filter
     *
     * @return Collection|null
     */
    public function getContracts(array $filter = [])
    {
        $status = isset($filter['status']) ? $filter['status'] : null;

        if (!is_null($status) && !in_array($status, [Contract::MTURK_SENT, Contract::MTURK_COMPLETE])) {
            $filter['status'] = Contract::MTURK_SENT;
        }

        if ($status == Contract::MTURK_SENT) {
            $this->perPage = null;
        }

        $contracts = $this->contract->getMTurkContracts($filter, $this->perPage);

        if (!is_null($contracts)) {
            foreach ($contracts as &$contract) {
                $contract->total_hits   = $this->getTotalHits($contract->id);
                $contract->count_status = $this->getTotalByStatus($contract->id);
                $info                   = $this->getMTurkInfo($contract->id);

                $contract->mturk_created_at = $info['created_at'];
                $contract->mturk_created_by = $info['created_by'];

                $contract->mturk_sent_at = $info['sent_at'];
                $contract->mturk_sent_by = $info['sent_by'];

            }
        }

        return $contracts;
    }

    /**
     * Get MTurk Contract List
     *
     * @return array
     */
    public function getContractsList()
    {
        $contracts = $this->contract->getMTurkContracts();
        $list      = [];

        foreach ($contracts as $contract) {
            $list[$contract->id] = $contract->title;
        }

        return $list;
    }

    /**
     * Create new task
     *
     * @param $contract_id
     *
     * @return bool
     */
    public function create($contract_id)
    {
        $contract = $this->contract->findWithPages($contract_id);

        try {
            $this->task->createTasks($contract->pages);
            $this->logger->info('Tasks added in database', ['Contract_id' => $contract_id]);
            $this->logger->mTurkActivity('mturk.log.create', ['contract' => $contract->title], $contract->id);
        } catch (Exception $e) {
            $this->logger->error('Create Task:'.$e->getMessage(), ['Contract_id' => $contract_id]);

            return false;
        }

        try {
            $contract->mturk_status = Contract::MTURK_SENT;
            $contract->save();
        } catch (Exception $e) {
            $this->logger->error('Update Task Status:'.$e->getMessage(), ['Contract_id' => $contract->id]);

            return false;
        }

        $this->queue->push('App\Nrgi\Mturk\Services\Queue\MTurkQueue', ['contract_id' => $contract->id], 'mturk');

        return true;
    }

    /**
     * Mechanical turk process
     *
     * @param $contract_id
     *
     * @return bool
     */
    public function mTurkProcess($contract_id)
    {
        $contract = $this->contract->findWithPages($contract_id);

        if ($this->sendToMTurk($contract)) {
            return true;
        }

        return false;
    }

    /**
     * Send Tasks to MTurk
     *
     * @param $contract
     *
     * @return bool
     * @throws Exception
     */
    public function sendToMTurk($contract)
    {
        foreach ($contract->pages as $key => $page) {
            $title       = sprintf(
                "Transcription of Contract '%s' - Pg: %s Lang: %s",
                str_limit($contract->title, 70),
                $page->page_no,
                $contract->metadata->language
            );
            $url         = $this->getMTurkUrl($page->pdf_url, $contract->metadata->language);
            $description = config('mturk.defaults.production.Description');

            try {
                $ret = $this->turk->createHIT($title, $description, $url);
            } catch (MTurkException $e) {
                $this->logger->error(
                    'createHIT: '.$e->getMessage(),
                    [
                        'Contract id' => $contract->id,
                        'Page'        => $page->page_no,
                        'Errors'      => $e->getErrors(),
                    ]
                );
                continue;
            } catch (Exception $e) {
                $this->logger->error(
                    'createHIT: '.$e->getMessage(),
                    ['Contract_id' => $contract->id, 'Page' => $page->page_no]
                );
                continue;
            }

            $update = ['hit_id' => $ret->hit_id, 'hit_type_id' => $ret->hit_type_id];

            if ($ret) {
                $this->task->update($page->contract_id, $page->page_no, $update);
                $this->logger->info(
                    'createHIT:'.sprintf('HIT created for page no.%s', $page->page_no),
                    ['Contract_id' => $contract->id, 'hit_id' => $ret->hit_id]
                );
                continue;
            }

            $this->logger->error(
                'Error while sending to MTurk',
                ['Contract_id' => $page->contract_id, 'Page No.' => $page->page_no]
            );
        }

        return true;
    }

    /**
     * Update assignment to tasks collection
     *
     * @param $tasks
     *
     * @return mixed
     */
    public function appendAssignment($tasks)
    {
        foreach ($tasks as &$task) {
            $task = $this->updateAssignment($task);
        }

        return $tasks;
    }

    /**
     * Save Assignment
     *
     * @param Task $task
     *
     * @return Task
     */
    public function updateAssignment(Task $task)
    {
        try {
            if (empty($task->assignments)) {
                $assignment = $this->turk->assignment($task->hit_id);
                if (!is_null($assignment) && $assignment['NumResults'] > 0) {
                    $task->status = Task::COMPLETED;
                    $this->logger->mTurkActivity('mturk.log.submitted', null, $task->contract_id, $task->page_no);

                    $updatedAssignment = $this->getFormattedAssignment($assignment);
                    $task->assignments = $updatedAssignment;

                    if ($updatedAssignment['assignment']['status'] == 'Approved') {
                        $task->approved = Task::APPROVED;
                        $this->logger->mTurkActivity('mturk.log.approve', null, $task->contract_id, $task->page_no);
                    }

                    $this->logger->info(
                        sprintf('Update Assignment for page no.%s', $task->page_no),
                        ['task' => $task->toArray()]
                    );
                    $task->save();
                }
            }

        } catch (MTurkException $e) {
            $errors = $e->getErrors();

            if ($errors['Error']['Code'] == 'AWS.MechanicalTurk.HITDoesNotExist') {
                $task->hit_id      = null;
                $task->hit_type_id = null;
                $task->save();
            }

            $this->logger->error(
                'Assignment update failed. '.$e->getMessage(),
                [
                    'Contract id' => $task->contract_id,
                    'Task'        => $task->id,
                    'Page no'     => $task->page_no,
                    'Errors'      => $errors,
                ]
            );
        } catch (Exception $e) {
            $this->logger->error('Assignment update failed. '.$e->getMessage());
        }

        return $task;
    }

    /**
     * Approve Task
     *
     * @param $contract_id
     * @param $task_id
     *
     * @return bool
     */
    public function approveTask($contract_id, $task_id)
    {
        try {
            $task = $this->task->getTask($contract_id, $task_id);
        } catch (Exception $e) {
            $this->logger->error(
                'Task does not exit:'.$e->getMessage(),
                ['Contract id' => $contract_id, 'Task' => $task_id]
            );

            return false;
        }

        if ($task->assignments->assignment->status == 'Submitted') {
            try {
                $response = $this->turk->approve($task->assignments->assignment->assignment_id);
            } catch (MTurkException $e) {
                $error = $e->getErrors()['Error'];
                if (isset($error['Code']) && $error['Code'] == 'AWS.MechanicalTurk.InvalidAssignmentState') {
                    foreach ($error['Data'] as $d) {
                        if ($d['Key'] == 'CurrentState' && $d['Value'] == 'Approved') {
                            $this->updateApproveTask($task);

                            return ['result' => true, 'message' => trans('mturk.action.already_approved')];
                        }
                    }
                }

                $this->logger->error(
                    'Approve Task failed:'.$e->getMessage(),
                    [
                        'Contract id' => $contract_id,
                        'Task'        => $task->toArray(),
                        'Errors'      => $e->getErrors(),
                    ]
                );

                return false;
            } catch (Exception $e) {
                $this->logger->error(
                    'Approve Task failed:'.$e->getMessage(),
                    ['Contract id' => $contract_id, 'Task' => $task->toArray()]
                );

                return false;
            }

            if ($response['http_code'] == 200) {
                return $this->updateApproveTask($task);
            }
        }

        return false;
    }

    /**
     * Reject Task
     *
     * @param $contract_id
     * @param $task_id
     * @param $message
     *
     * @return bool
     */
    public function rejectTask($contract_id, $task_id, $message)
    {
        try {
            $task = $this->task->getTask($contract_id, $task_id);
        } catch (Exception $e) {
            $this->logger->error(
                'Task does not exist '.$e->getMessage(),
                ['Contract id' => $contract_id, 'Task' => $task_id]
            );

            return false;
        }

        if ($task->assignments->assignment->status == 'Submitted') {
            try {
                $response = $this->turk->reject($task->assignments->assignment->assignment_id, $message);
            } catch (MTurkException $e) {
                $error = $e->getErrors()['Error'];
                if (isset($error['Code']) && $error['Code'] == 'AWS.MechanicalTurk.InvalidAssignmentState') {
                    foreach ($error['Data'] as $d) {
                        if ($d['Key'] == 'CurrentState' && $d['Value'] == 'Approved') {
                            $this->updateApproveTask($task);

                            $reset = \Form::open(
                                [
                                    'url'    => route(
                                        'mturk.task.reset',
                                        ['contract_id' => $task->contract_id, 'task_id' => $task->id]
                                    ),
                                    'method' => 'post',
                                    'style'  => 'display: inline',
                                ]
                            );
                            $reset .= \Form::button('Click here', ['type' => 'submit', 'class' => 'btn btn-primary']);
                            $reset .= \Form::close();

                            return [
                                'result'  => true,
                                'message' => trans('mturk.action.already_approved_and_reset', ['reset' => $reset]),
                            ];
                        }
                    }
                }

                $this->logger->error(
                    'Reject Task failed:'.$e->getMessage(),
                    [
                        'Contract id' => $contract_id,
                        'Task'        => $task->toArray(),
                        'Errors'      => $e->getErrors(),
                    ]
                );

                return false;
            } catch (Exception $e) {
                $this->logger->error(
                    'Reject Task failed:'.$e->getMessage(),
                    ['Contract id' => $contract_id, 'Task' => $task->toArray()]
                );

                return false;
            }

            if ($response['http_code'] == 200) {
                $assignments                     = $task->assignments;
                $assignments->assignment->status = 'Rejected';
                $task->assignments               = $assignments;
                $task->approved                  = Task::REJECTED;
                $this->logger->info(
                    sprintf('Assignment rejected for page no.%s', $task->page_no),
                    ['Task' => $task->toArray()]
                );
                $this->logger->mTurkActivity('mturk.log.reject', null, $contract_id, $task->page_no);

                return $task->save();
            }
        }

        return false;

    }

    /**
     * Get Task Detail
     *
     * @param $contract_id
     * @param $task_id
     *
     * @return Task|null
     */
    public function get($contract_id, $task_id)
    {
        try {
            $task = $this->task->getTask($contract_id, $task_id);

            return $this->updateAssignment($task);
        } catch (Exception $e) {
            $this->logger->info('Get Task:'.$e->getMessage(), ['Task' => $task_id]);

            return null;
        }
    }

    /**
     * Reset HIT
     *
     * @param $contract_id
     * @param $task_id
     *
     * @return bool
     */
    public function resetHIT($contract_id, $task_id)
    {
        $contract = $this->contract->find($contract_id);

        try {
            $task = $this->task->getTask($contract_id, $task_id);
            $task = $this->updateAssignment($task);

        } catch (Exception $e) {
            $this->logger->error(
                'Task does not exit'.$e->getMessage(),
                ['Contract id' => $contract_id, 'Task' => $task_id]
            );

            return false;
        }

        if ($task->hit_id != '') {
            try {
                if (!$this->turk->removeHIT($task)) {
                    return [
                        'result'  => false,
                        'message' => trans('HIT is in Reviewable state so can not be reset.'),
                    ];
                }
                $this->logger->info(
                    'HIT successfully deleted',
                    ['Contract id' => $contract_id, 'hit id' => $task->hit_id, 'Task' => $task_id]
                );
            } catch (MTurkException $e) {
                if ($e->getErrors()['Error']['Code'] != 'AWS.MechanicalTurk.HITDoesNotExist') {
                    return [
                        'result'  => false,
                        'message' => $e->getErrors()['Error']['Message'],
                    ];
                }

                $this->logger->error(
                    'HIT delete failed MTurk Error. '.$e->getMessage(),
                    [
                        'Contract id' => $contract_id,
                        'hit id'      => $task->hit_id,
                        'Task'        => $task_id,
                        'Errors'      => $e->getErrors(),
                    ]
                );
            } catch (Exception $e) {
                $this->logger->error(
                    'HIT delete failed. '.$e->getMessage(),
                    ['Contract id' => $contract_id, 'hit id' => $task->hit_id, 'Task' => $task_id]
                );

                return false;
            }
        }

        $title       = sprintf(
            "Transcription of Contract '%s' - Pg: %s Lang: %s",
            str_limit($contract->title, 70),
            $task->page_no,
            $contract->metadata->language
        );
        $url         = $this->getMTurkUrl($task->pdf_url, $contract->metadata->language);
        $description = config('mturk.defaults.production.Description');

        try {
            $ret = $this->turk->createHIT($title, $description, $url);
        } catch (MTurkException $e) {
            $this->logger->error(
                'HIT create failed. '.$e->getMessage(),
                [
                    'Contract id' => $contract_id,
                    'Task'        => $task_id,
                    'Page no'     => $task->page_no,
                    'Errors'      => $e->getErrors(),
                ]
            );

            return ['result' => false, 'message' => $e->getErrors()];
        } catch (Exception $e) {
            $this->logger->error(
                'HIT create failed. '.$e->getMessage(),
                ['Contract id' => $contract_id, 'Task' => $task_id, 'Page no' => $task->page_no]
            );

            return false;
        }

        $update = [
            'hit_id'      => $ret->hit_id,
            'assignments' => null,
            'status'      => 0,
            'approved'    => 0,
            'hit_type_id' => $ret->hit_type_id,
            'created_at'  => date('Y-m-d H:i:s'),
        ];

        if ($ret) {
            $this->task->update($task->contract_id, $task->page_no, $update);
            $this->logger->info('HIT successfully reset', ['Contract id' => $contract_id, 'Task' => $task->toArray()]);
            $this->logger->mTurkActivity('mturk.log.reset', null, $task->contract_id, $task->page_no);

            return true;
        }

        $this->logger->error('Error in MTurk', ['Contract id' => $contract_id, 'Task' => $task->toArray()]);

        return false;
    }

    /**
     * Get Total Hits
     *
     * @param $contract_id
     *
     * @return mixed
     */
    public function getTotalHits($contract_id)
    {
        return $this->task->getTotalHits($contract_id);
    }

    /**
     * Get total By status
     *
     * @param $contract_id
     *
     * @return array
     */
    public function getTotalByStatus($contract_id)
    {
        return $this->task->getTotalByStatus($contract_id);
    }

    /**
     * Text send to RC
     *
     * @param $contract_id
     *
     * @return bool
     */
    public function copyTextToRC($contract_id)
    {
        $tasks = $this->task->getAll($contract_id);

        foreach ($tasks as $task) {
            $text     = $this->turk->getAns($task->hit_id);
            $text     = is_string($text) ? $text : '';
            $pdf_text = nl2br($text);
            $this->page->saveText($contract_id, $task->page_no, $pdf_text, false);
        }

        $this->contract->updateWordFile($contract_id);
        $contract               = $this->contract->find($contract_id);
        $contract->mturk_status = Contract::MTURK_COMPLETE;
        $contract->textType     = Contract::ACCEPTABLE;

        $this->logger->info('Contract text updated from MTurk', ['Contract id' => $contract_id]);
        $this->logger->activity('mturk.log.sent_to_rc', null, $contract_id);
        $this->logger->mTurkActivity('mturk.log.sent_to_rc', null, $contract_id);


        return $contract->save();
    }

    /**
     * Is balance sufficient for new HIT
     *
     * @return bool
     */
    public function isBalanceToCreateHIT()
    {
        $availableBalance = (int) $this->getMturkBalance();

        return $availableBalance > $this->costPerHIT();
    }

    /**
     * Cost per HIT
     *
     * @return string
     */
    public function costPerHIT()
    {
        return (config('mturk.defaults.production.Reward.Amount') * 1.20);
    }

    /**
     * Get Approval Pending tasks
     *
     * @param $contract_id
     *
     * @return mixed
     */
    public function getApprovalPendingTask($contract_id)
    {
        return $this->task->getApprovalPendingTask($contract_id);
    }

    /**
     * Get Expired Tasks
     *
     * @return Collection
     */
    public function getExpired()
    {
        return $this->task->getExpired();
    }

    /**
     * Get MTurk Information
     *
     * @param $id
     *
     * @return array
     */
    public function getMTurkInfo($id)
    {
        $create = $this->logService->mturk($id, 'create');
        $sent   = $this->logService->mturk($id, 'sent_to_rc');

        return [
            'created_at' => isset($create->created_at) ? $create->created_at : '',
            'created_by' => isset($create->user->name) ? $create->user->name : '',
            'sent_at'    => isset($sent->created_at) ? $sent->created_at : '',
            'sent_by'    => isset($sent->user->name) ? $sent->user->name : '',
        ];
    }

    /**
     * Get all Tasks
     *
     * @param $filter
     *
     * @return Collection
     */
    public function allTasks($filter)
    {
        return $this->task->allTasks($filter, 50);
    }

    /**
     * Approve all assignment
     *
     * @param $contract_id
     *
     * @return bool
     */
    public function approveAllTasks($contract_id)
    {
        try {
            $contracts = $this->contract->findWithTasks($contract_id, Task::COMPLETED, Task::APPROVAL_PENDING);
            $tasks     = $contracts->tasks;
        } catch (Exception $e) {
            $this->logger->error('Tasks not found for approval : '.$e->getMessage(), ['Contract id' => $contract_id]);

            return false;
        }

        $status = true;
        foreach ($tasks as $task) {
            if (!$this->approveTask($task->contract_id, $task->id)) {
                $status = false;
            }
        }

        return $status;
    }

    /**
     * Get MTurk Url
     *
     * @param $pdf
     * @param $lang
     *
     * @return string
     */
    public function getMTurkUrl($pdf, $lang)
    {
        return sprintf('%s?pdf=%s&amp;lang=%s', $this->task_url, $pdf, $lang);
    }

    /**
     * Get Mturk Balance
     *
     * @return object
     */
    public function getMturkBalance()
    {
        try {
            return $this->turk->getBalance();
        } catch (Exception $e) {
            $this->logger->error('Get Mturk Balance : '.$e->getMessage());

            return 0;
        }
    }

    /**
     * Get Formatted Assignments
     *
     * @param $assignment
     *
     * @return array
     */
    protected function getFormattedAssignment($assignment)
    {
        $task_assignment = $assignment['Assignments'][0];
        $data             = [];

        $answerObj = json_decode(json_encode(new \SimpleXMLElement($task_assignment['Answer']), true));
        $answer    = '';

        foreach ($answerObj->Answer as $key => $ans) {
            if ($key == 'feedback') {
                $answer = $ans;
                break;
            }
        }
        $data['assignment'] = [
            'assignment_id' => $task_assignment['AssignmentId'],
            'worker_id'     => $task_assignment['WorkerId'],
            'accept_time'   => $task_assignment['AcceptTime'],
            'submit_time'   => $task_assignment['SubmitTime'],
            'status'        => $task_assignment['AssignmentStatus'],
            'answer'        => $answer,
        ];

        $data['total'] = $assignment['NumResults'];

        return $data;
    }

    /**
     * Get MTurk Page Url
     *
     * @return string
     */
    protected function getMTurkPageUrl()
    {
        return env('MTURK_TASK_URL');
    }

    /**
     * Update Approve Task
     *
     * @param $task
     *
     * @return mixed
     */
    private function updateApproveTask($task)
    {
        $assignments                     = $task->assignments;
        $assignments->assignment->status = 'Approved';
        $task->assignments               = $assignments;
        $task->approved                  = Task::APPROVED;

        $this->logger->info(
            sprintf('Assignment approved for page no. %s', $task->page_no),
            ['Task' => $task->toArray()]
        );
        $this->logger->mTurkActivity('mturk.log.approve', null, $task->contract_id, $task->page_no);

        return $task->save();
    }
}
