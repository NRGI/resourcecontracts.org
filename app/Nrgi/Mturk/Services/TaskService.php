<?php namespace App\Nrgi\Mturk\Services;

use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Mturk\Entities\Task;
use App\Nrgi\Mturk\Repositories\TaskRepositoryInterface;
use App\Nrgi\Mturk\Repositories\MturkTaskItem\MturkTaskItemRepositoryInterface;
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
     * @var MturkTaskItemRepositoryInterface
     */
    protected $taskItem;
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
     * @var String
     */
    protected $bucket_url;
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
     * @param MturkTaskItemRepositoryInterface $taskItem
     * @param ContractService         $contract
     * @param Log                     $logger
     * @param MTurkService            $turk
     * @param PageService             $page
     * @param Queue                   $queue
     * @param ActivityLogService      $logService
     */
    public function __construct(
        TaskRepositoryInterface $task,
        MturkTaskItemRepositoryInterface $taskItem,
        ContractService $contract,
        Log $logger,
        MTurkService $turk,
        PageService $page,
        Queue $queue,
        ActivityLogService $logService
    ) {
        $this->task       = $task;
        $this->taskItem   = $taskItem;
        $this->contract   = $contract;
        $this->logger     = $logger;
        $this->turk       = $turk;
        $this->page       = $page;
        $this->queue      = $queue;
        $this->logService = $logService;
        $this->task_url   = $this->getMTurkPageUrl();
        $this->bucket_url = $this->getBucketUrl();
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

    public function getLogArray($arr) 
    {
        return array_map(function($el) { unset($el['text']); return $el;}, array_merge(array(), $arr));
    }

    /**
     * Create new task
     *
     * @param $contract_id
     *
     * @return bool
     */
    public function create($contract_id, $description, $per_task_items_count = 5)
    {
        $contract = $this->contract->findWithPages($contract_id);

        try {
            $contract_pages = $contract->pages->toArray();
            $this->logger->info('Contract pages'.json_encode($this->getLogArray($contract_pages)));
            usort($contract_pages, function($a, $b) {return $this->compareAscendingSort($a, $b, 'page_no');});
            $this->logger->info('Contract pages sort'.json_encode($this->getLogArray($contract_pages)));

            $this->task->createTasks($contract_pages, $per_task_items_count);
            $this->logger->info('Tasks added in database', ['Contract_id' => $contract_id]);
            $this->logger->mTurkActivity('mturk.log.create', ['contract' => $contract->title], $contract->id);
        } catch (Exception $e) {
            $this->logger->error('Create Task:'.$e->getMessage(), ['Contract_id' => $contract_id]);

            return false;
        }

        try {
            //TODO: REVERT MTURK STATUS SAVE
            $contract->mturk_status = Contract::MTURK_SENT;
            $contract->save();
        } catch (Exception $e) {
            $this->logger->error('Update Task Status:'.$e->getMessage(), ['Contract_id' => $contract->id]);

            return false;
        }

        $this->queue->push('App\Nrgi\Mturk\Services\Queue\MTurkQueue', ['contract_id' => $contract->id, 'hit_description' => $description, 'per_task_items_count' => $per_task_items_count ], 'mturk');

        return true;
    }

    /**
     * Mechanical turk process
     *
     * @param $contract_id
     *
     * @return bool
     */
    public function mTurkProcess($data)
    {
        $contract_id=$data['contract_id'];
        $hit_description = $data['hit_description'];
        $per_task_items_count = $data['per_task_items_count'];
        $contract = $this->contract->findWithPages($contract_id);

        if ($this->sendToMTurk($contract, $hit_description, $per_task_items_count )) {
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
    public function sendToMTurk($contract, $hit_description=null, $per_task_items_count = 5)
    {
        $contract_pages = $contract->pages->toArray();
        usort($contract_pages, function($a, $b) {return $this->compareAscendingSort($a, $b, 'page_no');});
        $chunked_contract_pages = array_chunk($contract_pages, $per_task_items_count);
        foreach ($chunked_contract_pages as $key => $pages) {
            $all_pages = $this->getAllPages($pages);
            $all_pages_str = join(',', $all_pages);
            $title       = $this->getMTurkTaskTitle($contract, $all_pages);
            $url         = $this->getMTurkUrl($contract->id, $all_pages, $contract->metadata->language);
            $description = !is_null($hit_description) && strlen(trim($hit_description)) > 0? $hit_description: config('mturk.defaults.production.Description');
            try {
                $ret = $this->turk->createHIT($title, $description, $url, $per_task_items_count);
            } catch (MTurkException $e) {
                $this->logger->error(
                    'createHIT: '.$e->getMessage(),
                    [
                        'Contract id' => $contract->id,
                        'Pages'        => $all_pages_str,
                        'Errors'      => $e->getErrors(),
                    ]
                );
                continue;
            } catch (Exception $e) {
                $this->logger->error(
                    'createHIT: '.$e->getMessage(),
                    ['Contract_id' => $contract->id, 'Pages' => $all_pages_str]
                );
                continue;
            }

            if ($ret) {
                $update = ['hit_id' => $ret->hit_id, 'hit_type_id' => $ret->hit_type_id, 'hit_description'=>$ret->description];
                $this->task->update($contract->id, $all_pages, $update);
                $this->logger->info(
                    'createHIT:'.sprintf('HIT created for page no.%s', $all_pages_str),
                    ['Contract_id' => $contract->id, 'hit_id' => $ret->hit_id]
                );
                continue;
            }

            $this->logger->error(
                'Error while sending to MTurk',
                ['Contract_id' => $contract->id, 'Page Nos.' => $all_pages_str]
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
     * Get Sorted Array in ascending order based on number/integer items with this function
     *
     * @param $array
     * @param $itemKey
     *
     * @return mixed
     */

    public function compareAscendingSort ($el1, $el2, $item_key) {
        $item1 = $el1[$item_key];
        $item2 = $el2[$item_key];
        $this->logger->info('Task service:sortfunc'.json_encode($item1).json_encode($item_key));
        $this->logger->info('Task service:sortfunc2'.json_encode($item2).json_encode($item_key));
        if(!isset($item1) || !isset($item2)) {
            $this->logger->info('Returning 0'.json_encode($item1).json_encode($item2));
            return 0;
        }
        if($item1 == $item2) {
            $this->logger->info('Returning 00'.json_encode($item1).json_encode($item2));
            return 0;
        }
        $this->logger->info('Returning final'.json_encode($item1).json_encode($item2).json_encode($item1 < $item2 ? -1 : 1));
        return $item1 < $item2 ? -1 : 1;
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
       $all_pages =  $this->getAllPages($task->taskItems->toArray());
       $all_pages_str = join(',', $all_pages);
        try {
            if (empty($task->assignments)) {
                $assignment = $this->turk->assignment($task->hit_id);
                if (!is_null($assignment) && $assignment['NumResults'] > 0) {
                    $task->status = Task::COMPLETED;
                    $this->logger->mTurkActivity('mturk.log.submitted', null, $task->contract_id, $all_pages_str);

                    $updatedAssignment = $this->getFormattedAssignment($assignment);
                    if ($updatedAssignment['assignment']['status'] == 'Approved') {
                        $task->approved = Task::APPROVED;
                        $this->logger->mTurkActivity('mturk.log.approve', null, $task->contract_id, $all_pages_str);
                    }
                    $this->updateMTurkTaskItems($task->id, $updatedAssignment['assignment']['answer']);
                    unset($updatedAssignment['assignment']['answer']);
                    $task->assignments = $updatedAssignment;
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
                    'Page nos'     => $all_pages_str,
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
     * @return array|bool|mixed
     */
    public function updateMTurkTaskItems($task_id, $answerObj) {
        try {
            foreach($answerObj as $page_no => $ans) {
                $this->logger->info(
                    sprintf('Update Assignment Task Item for page no.%s', $page_no),
                    ['Answer' => $ans]
                );
               try {
                   $update = [
                       'answer' => json_encode($ans)
                   ];
                $this->taskItem->update($task_id, $page_no, $update);
               }
               catch(Exception $e)  {
                $this->logger->error('Assignment task items update failed. TaskId'.$task_id.'Page Number'.$page_no.$e->getMessage());
               }
            }
        }
        catch(Exception $e) {
            $this->logger->error('Assignment task items update failed. '.$e->getMessage());
        }
    }

    /**
     * Approve Task
     *
     * @param $contract_id
     * @param $task_id
     *
     * @return array|bool|mixed
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

            if ($response['http_code'] == 400) {
                if (isset($response['response']) && isset($response['response']['TurkErrorCode'])) {
                    if ($response['response']['TurkErrorCode'] == 'AWS.MechanicalTurk.InvalidAssignmentState') {
                        $assignment = $this->turk->getAssignment($task->assignments->assignment->assignment_id);

                        if ($assignment['http_code'] == 200
                            && isset($assignment['response'])
                            && isset($assignment['response']['Assignment'])
                            && isset($assignment['response']['Assignment']['AssignmentStatus'])) {

                            if ($assignment['response']['Assignment']['AssignmentStatus'] == 'Approved') {
                                $task->is_auto_approved = true;
                                $this->updateApproveTask($task);

                                return ['result' => true, 'message' => trans('mturk.action.has_already_approved')];
                            } elseif ($assignment['response']['Assignment']['AssignmentStatus'] == 'Rejected') {
                                return [
                                    'result'  => true,
                                    'message' => trans('mturk.action.hit_rejected_cannot_be_approved'),
                                ];
                            }
                        }
                    } elseif ($response['response']['TurkErrorCode'] == 'AWS.MechanicalTurk.AssignmentDoesNotExist') {
                        return ['result' => false, 'message' => trans('mturk.action.assignment_does_not_exists')];
                    } elseif ($response['response']['TurkErrorCode'] == 'AWS.MechanicalTurk.HITDoesNotExist') {
                        return ['result' => false, 'message' => trans('mturk.action.hit_does_not_exists')];
                    }

                    return ['result' => false, 'message' => $response['response']['TurkErrorCode']];
                }

                return false;
            }

        }

        return false;
    }

    /**
     * Update reject status for task in DB
     *
     * @param $task
     * @param $contract_id
     *
     * @return mixed
     */
    public function rejectTaskInDb($task, $contract_id)
    {
        $all_pages_str = join(',', $this->getAllPages($task->taskItems->toArray()));
        $assignments                     = $task->assignments;
        $assignments->assignment->status = 'Rejected';
        $task->assignments               = $assignments;
        $task->approved                  = Task::REJECTED;
        $this->logger->info(
            sprintf('Assignment rejected for page no.%s', $all_pages_str),
            ['Task' => $task->toArray()]
        );
        $this->logger->mTurkActivity('mturk.log.reject', null, $contract_id, $all_pages_str);

        return $task->save();
    }

    /**
     * Reject Task
     *
     * @param $contract_id
     * @param $task_id
     * @param $message
     *
     * @return array|bool
     */
    public function rejectTask($contract_id, $task_id, $message, $hit_description=null)
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
                $reject_status = $this->rejectTaskInDb($task, $contract_id);
                 $reject_result = is_bool($reject_status) ? $reject_status : $reject_status['result'];
                 return !$reject_result ? $reject_result : $this->processHitAutoReset($contract_id, $task_id,$hit_description,'mturk.action.reject_hit_auto_reset','mturk.reject');
            }

            if ($response['http_code'] == 400) {
                if (isset($response['response']) && isset($response['response']['TurkErrorCode'])) {
                    if ($response['response']['TurkErrorCode'] == 'AWS.MechanicalTurk.InvalidAssignmentState') {
                        $assignment = $this->turk->getAssignment($task->assignments->assignment->assignment_id);

                        if ($assignment['http_code'] == 200
                            && isset($assignment['response'])
                            && isset($assignment['response']['Assignment'])
                            && isset($assignment['response']['Assignment']['AssignmentStatus'])) {

                            if ($assignment['response']['Assignment']['AssignmentStatus'] == 'Approved') {
                                return [ 'result'  => true, 'message' => trans('mturk.action.hit_approved_cannot_be_rejected')];
                            } elseif ($assignment['response']['Assignment']['AssignmentStatus'] == 'Rejected') {
                                $this->rejectTaskInDb($task, $contract_id);
                                return $this->processHitAutoReset($contract_id, $task_id,$hit_description,'mturk.action.reject_hit_auto_reset','mturk.action.has_already_rejected');
                            }
                        }
                        elseif($assignment['http_code'] == 400 ) {
                            if(isset($assignment['response']) && isset($assignment['response']['TurkErrorCode'])) {
                                if ($assignment['response']['TurkErrorCode'] == 'AWS.MechanicalTurk.HITDoesNotExist') {
                                    $this->logger->warning(
                                        'HIT does not exist '.json_encode($assignment['response']),
                                        [
                                            'Contract id' => $contract_id,
                                            'hit id'      => $task->hit_id,
                                            'Task'        => $task_id,
                                            'Errors'      => $assignment['response']['Message'],
                                        ]
                                    );
                                    return $this->processHitAutoCreation($contract_id, $task_id, $hit_description,'mturk.action.hit_auto_reset','mturk.action.hit_does_not_exists');
                                    
                                }
                            }
                        }
                        elseif($assignment['http_code'] == 400 ) {
                            if(isset($assignment['response']) && isset($assignment['response']['TurkErrorCode'])) {
                                if ($assignment['response']['TurkErrorCode'] == 'AWS.MechanicalTurk.HITDoesNotExist') {
                                    $this->logger->warning(
                                        'HIT does not exist '.json_encode($assignment['response']),
                                        [
                                            'Contract id' => $contract_id,
                                            'hit id'      => $task->hit_id,
                                            'Task'        => $task_id,
                                            'Errors'      => $assignment['response']['Message'],
                                        ]
                                    );
                                    return $this->processHitAutoCreation($contract_id, $task_id, $hit_description,'mturk.action.hit_auto_reset','mturk.action.assignment_does_not_exists');
                                    
                                }
                            }
                        }
                    } elseif ($response['response']['TurkErrorCode'] == 'AWS.MechanicalTurk.AssignmentDoesNotExist') {
                        $this->logger->warning(
                            'Assignments does not exist '.json_encode($response['response']),
                            [
                                'Contract id' => $contract_id,
                                'hit id'      => $task->hit_id,
                                'Task'        => $task_id,
                                'Errors'      => $response['response']['Message'],
                            ]
                        );
                        return $this->processHitAutoCreation($contract_id, $task_id, $hit_description,'mturk.action.hit_auto_reset','mturk.action.assignment_does_not_exists');
                    } elseif ($response['response']['TurkErrorCode'] == 'AWS.MechanicalTurk.HITDoesNotExist') {
                        return $this->processHitAutoCreation($contract_id, $task_id,$hit_description,'mturk.action.hit_auto_reset','mturk.action.hit_does_not_exists');
                    }
                    return ['result' => false, 'message' => $response['response']['TurkErrorCode']];
                }

                return false;
            }
        }

        return false;

    }

    /**
     * Auto resetting hit
     *
     * @param $contract_id
     * @param $task_id
     * @param $hit_description
     * @param $sucess_message
     * @param $fallbackMessage
     *
     * @return array|bool
     */

    public function processHitAutoReset($contract_id, $task_id, $hit_description,$success_message, $fallbackMessage) {
        try {
            $newHit = $this->resetHIT($contract_id, $task_id, $hit_description);
            $result = is_bool($newHit) ? $newHit : $newHit['result'];
            $resetMessage = is_bool($newHit) ? null : $newHit['message'];
            if($result) {
                return [ "result" => true, "message" => trans($success_message) ];
            } else {
                return [ "result "=> false, "message" => $resetMessage != null ? $resetMessage : trans($fallbackMessage) ];
            }
        } catch (Exception $e) {
            $this->logger->error(
                'HIT auto creation failed. '.$e->getMessage(),
                ['Contract id' => $contract_id, 'Task' => $task_id,]
            );
            return false;
        }
        return false;
    }

    /**
     * Auto creating new hit
     *
     * @param $contract_id
     * @param $task_id
     * @param $hit_description
     * @param $success_message
     * @param $fallbackMessage
     *
     * @return array|bool
     */

    public function processHitAutoCreation($contract_id, $task_id, $hit_description,$success_message, $fallbackMessage) {
        try {
            $newHit = $this->createNewHit($contract_id, $task_id, $hit_description);
            $result = is_bool($newHit) ? $newHit : $newHit['result'];
            $resetMessage = is_bool($newHit) ? null : $newHit['message'];
            if($result) {
                return [ "result" => true, "message" => trans($success_message) ];
            } else {
                return [ "result "=> false, "message" => $resetMessage != null ? $resetMessage : trans($fallbackMessage) ];
            }
        } catch (Exception $e) {
            $this->logger->error(
                'HIT auto creation failed. '.$e->getMessage(),
                ['Contract id' => $contract_id, 'Task' => $task_id,]
            );
            return false;
        }
        return false;
    }

    /**
     * Create new hit
     *
     * @param $contract_id
     * @param $task_id
     *
     * @return array|bool
     */

    public function createNewHit($contract_id, $task_id, $hit_description)
    {
       try {
        $contract = $this->contract->find($contract_id);
        $task = $this->task->getTask($contract_id, $task_id);
       } catch (Exception $e) {
        $this->logger->error(
            'Task or contract does not exist '.$e->getMessage(),
            ['Contract id' => $contract_id, 'Task' => $task_id]
        );

        return false;
       }
    $all_pages = $this->getAllPages($task->taskItems->toArray());
    $title       = $this->getMTurkTaskTitle($contract, $all_pages);
    $all_pages_str = join(',', $all_pages );
    $url         = $this->getMTurkUrl($contract_id,  $all_pages, $contract->metadata->language);
    $description = !is_null($hit_description) && strlen(trim($hit_description)) > 0 ? $hit_description: config('mturk.defaults.production.Description');

    try {
        $ret = $this->turk->createHIT($title, $description, $url);
    } catch (MTurkException $e) {
        $this->logger->error(
            'HIT create failed. '.$e->getMessage(),
            [
                'Contract id' => $contract_id,
                'Task'        => $task_id,
                'Page no'     => $all_pages_str,
                'Errors'      => $e->getErrors(),
            ]
        );

        return ['result' => false, 'message' => $e->getErrors()];
    } catch (Exception $e) {
        $this->logger->error(
            'HIT create failed. '.$e->getMessage(),
            ['Contract id' => $contract_id, 'Task' => $task_id, 'Page no' => $all_pages_str]
        );

        return false;
    }

    if ($ret) {
        $update = [
            'hit_id'      => $ret->hit_id,
            'assignments' => null,
            'status'      => 0,
            'approved'    => 0,
            'hit_description'=>$description,
            'hit_type_id' => $ret->hit_type_id,
            'created_at'  => date('Y-m-d H:i:s'),
        ];

        $task_items_update = [
            "answer" => null
        ];
        $this->task->updateWithId($task->contract_id, $task->id, $update);
        $this->taskItem->updateAllTaskItems($task->id, $task_items_update);
        $this->logger->info('HIT successfully reset', ['Contract id' => $contract_id, 'Task' => $task->toArray()]);
        $this->logger->mTurkActivity('mturk.log.reset', null, $task->contract_id, $all_pages_str);

        return true;
    }

    $this->logger->error('Error in MTurk', ['Contract id' => $contract_id, 'Task' => $task->toArray()]);

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

    public function getAllPages($arr) 
    {
        return array_map(function($el) {
            return $el['page_no'];
        }, $arr);
    }

    public function getMTurkTaskTitle($contract, $all_pages) 
    {
        $hasPages = count($all_pages) > 0;
        $startPage = $hasPages ? $all_pages[0] : '';
        $endPage = $hasPages ? $all_pages[count($all_pages) -1] : '';
       return sprintf(
            "Transcription of Contract '%s' - Pg: %s - %s Lang: %s",
            str_limit($contract->title, 70),
            $startPage,
            $endPage,
            $contract->metadata->language
       );
    }

    /**
     * Reset HIT
     *
     * @param $contract_id
     * @param $task_id
     * @param $hit_description
     * @param $approved_hit
     *
     * @return bool
     */
    public function resetHIT($contract_id, $task_id, $hit_description = '', $approved_hit = false)
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

        if ($task->hit_id != '' && $task->hit_id != null) {
            try {
                $response=$this->turk->removeHIT($task);
                if($response['http_code']==400) {
                    if(isset($response['response']) && isset($response['response']['TurkErrorCode'])) {
                        if ($response['response']['TurkErrorCode'] != 'AWS.MechanicalTurk.HITDoesNotExist') {
                            return [
                                'result'  => false,
                                'message' => $response['response']['Message'],
                            ];
                        }
                    }
                    $this->logger->warning(
                        'HIT delete failed MTurk Error. '.json_encode($response['response']),
                        [
                            'Contract id' => $contract_id,
                            'hit id'      => $task->hit_id,
                            'Task'        => $task_id,
                            'Errors'      => $response['response']['Message'],
                        ]
                    );
                }
                else {
                    $removedHit = $response['response'];

                    if ($removedHit && $removedHit['HIT']['HITStatus'] != "Disposed" && !$approved_hit) {
                        return [
                            'result'  => false,
                            'message' => trans('HIT is in Reviewable state so can not be reset.'),
                        ];
                    }

                    $this->logger->info(
                        'HIT successfully deleted',
                        ['Contract id' => $contract_id, 'hit id' => $task->hit_id, 'Task' => $task_id]
                    );
                }
               
            } catch (MTurkException $e) {
                if ($e->getErrors()['Error']['Code'] != 'AWS.MechanicalTurk.HITDoesNotExist') {
                    return [
                        'result'  => false,
                        'message' => $e->getErrors()['Error']['Message'],
                    ];
                }

                $this->logger->error(
                    'HIT delete failed MTurk Error. '.json_encode($e).$e->getMessage(),
                    [
                        'Contract id' => $contract_id,
                        'hit id'      => $task->hit_id,
                        'Task'        => $task_id,
                        'Errors'      => $e->getErrors(),
                    ]
                );
            } catch (Exception $e) {
                $this->logger->error(
                    'HIT delete failed. '.json_encode($e).$e->getMessage(),
                    ['Contract id' => $contract_id, 'hit id' => $task->hit_id, 'Task' => $task_id]
                );

                return false;
            }
        }

        $all_pages = $this->getAllPages($task->taskItems->toArray());
        $title       = $this->getMTurkTaskTitle($contract, $all_pages);
        $all_pages_str = join(',', $all_pages );
        $url         = $this->getMTurkUrl($contract_id,  $all_pages, $contract->metadata->language);
        $description = !is_null($hit_description) && strlen(trim($hit_description)) > 0? $hit_description: config('mturk.defaults.production.Description');

        try {
            $ret = $this->turk->createHIT($title, $description, $url, count($task->taskItems));
        } catch (MTurkException $e) {
            $this->logger->error(
                'HIT create failed. '.$e->getMessage(),
                [
                    'Contract id' => $contract_id,
                    'Task'        => $task_id,
                    'Page no'     => $all_pages_str,
                    'Errors'      => $e->getErrors(),
                ]
            );

            return ['result' => false, 'message' => $e->getErrors()];
        } catch (Exception $e) {         
            $this->logger->error(
                'HIT create failed. '.$e->getMessage(),
                ['Contract id' => $contract_id, 'Task' => $task_id, 'Page nos' => $all_pages_str]
            );

            return false;
        }

        if ($ret) {
            $update = [
                'hit_id'      => $ret->hit_id,
                'assignments' => null,
                'status'      => 0,
                'approved'    => 0,
                'hit_type_id' => $ret->hit_type_id,
                'hit_description' =>$description,
                'created_at'  => date('Y-m-d H:i:s'),
                'is_auto_approved' => false,
            ];
            $task_items_update = [
                "answer" => null
            ];
            $this->task->updateWithId($task->contract_id, $task->id, $update);
            $this->taskItem->updateAllTaskItems($task->id, $task_items_update);

            if($approved_hit){
                $contract->mturk_status = Contract::MTURK_SENT;
                $contract->textType     = Contract::NEEDS_FULL_TRANSCRIPTION;
                $contract->save();
            }

            $this->logger->info('HIT successfully reset', ['Contract id' => $contract_id, 'Task' => $task->toArray()]);
            $this->logger->mTurkActivity('mturk.log.reset', null, $task->contract_id, $all_pages_str);

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
            $textArray     = $this->turk->getAns($task);
            $taskItems = $task->taskItems->toArray();
            foreach($taskItems as $key => $taskItem) 
            {
                $text     = is_string($textArray[$taskItem->page_no]) ? $textArray[$taskItem->page_no] : '';
                $pdf_text = nl2br($text);
                $this->page->saveText($contract_id, $taskItem->page_no, $pdf_text, false);
            }
        }

        $this->contract->updateWordFile($contract_id);
        $contract               = $this->contract->find($contract_id);
        $text_status            = $contract->text_status;
        $contract->text_status  = Contract::STATUS_PUBLISHED;
        $contract->mturk_status = Contract::MTURK_COMPLETE;
        $contract->textType     = Contract::ACCEPTABLE;
        $is_updated             = $contract->save();

        if($is_updated){
            $this->queue->push(
                'App\Nrgi\Services\Queue\PostToElasticSearchQueue',
                ['contract_id' => $contract->id, 'type' => 'text'],
                'elastic_search'
            );

            $this->logger->activity(
                'contract.log.status',
                ['type' => 'text', 'old_status' => $text_status, 'new_status' => $contract->text_status],
                $contract->id
            );
            $this->logger->info(
                "Contract status updated",
                [
                    'Contract id' => $contract->id,
                    'Status type' => 'text',
                    'Old status'  => $text_status,
                    'New Status'  => $contract->text_status,
                ]
            );        
        }

        $this->logger->info('Contract text updated from MTurk', ['Contract id' => $contract_id]);
        $this->logger->activity('mturk.log.sent_to_rc', null, $contract_id);
        $this->logger->mTurkActivity('mturk.log.sent_to_rc', null, $contract_id);
        return $is_updated;
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
    public function getMTurkUrl($contract_id, $all_pages, $lang)
    {
        $startPage = isset($all_pages) && count($all_pages) > 0 ? min($all_pages) : 0;
        $endPage = isset($all_pages) && count($all_pages) > 0 ? max($all_pages) : 0;
        return sprintf('%s?bucket=%s&amp;contractId=%s&amp;startPage=%s&amp;endPage=%s&amp;lang=%s', $this->task_url, $this->bucket_url, $contract_id, $startPage, $endPage, $lang);
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
        $data            = [];

        $answerObj = json_decode(json_encode(new \SimpleXMLElement($task_assignment['Answer']), true));
        $answer    = array();
        $this->logger->info('ANSWER OF HIT IS'.json_encode($answerObj));
        foreach ($answerObj->Answer as $k => $ans) {
            $key = $ans->QuestionIdentifier;
            $text = $ans->FreeText;
            $this->logger->info('ANSWER OF HIT IN LOOP IS'.json_encode($key).'ANSWER'.json_encode($ans).'SUB STR'.json_encode(substr($key, 0, strlen('feedback'))));
            if (substr($key, 0, strlen('feedback')) == 'feedback') {
                $values = explode('_', $key);
                $this->logger->info('ANSWER OF HIT IN LOOP IS COUNT'.json_encode($values).'COUNT'.json_encode(count($values)));
                if(count($values) > 1) {
                    $page_no = $values[1];
                    $answer[$page_no] = $text;
                }
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
        $this->logger->info('FINAL ANSWER OF HIT IS'.json_encode($answer));

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
     * Get Bucket Url
     *
     * @return string
     */
    protected function getBucketUrl() 
    {
        $s3Url = env('AWS_S3_BUCKET_URL');
        return isset($s3Url) ? $s3Url : "https://".env('AWS_BUCKET').".s3.amazonaws.com";
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
        $all_pages_str = join(',', $this->getAllPages($task->taskItems->toArray()));
        $this->logger->info(
            sprintf('Assignment approved for page nos. %s', $all_pages_str),
            ['Task' => $task->toArray()]
        );
        $this->logger->mTurkActivity('mturk.log.approve', null, $task->contract_id, $all_pages_str);

        return $task->save();
    }

    /**
     * Resets the hit. Temporary function. Remove after user
     *
     * @return array
     */
    public function resetHitCommand()
    {
        $map_ids     = [
            '3SV8KD29L4G4QF224QWS9KI9Z84ZKA' => 3881,
            '36U4VBVNQO19RKLNON6HT4P8SIJUR9' => 3880,
            '3XT3KXP24ZMBWAS32IE5Z6A10I7I6E' => 3870,
            '3J6BHNX0U9GA9QOJ12LYEXB0NCLKN1' => 3869,
            '3B623HUYJ4ENU2EN095HNMCFFBZS82' => 3868,
            '3BFNCI9LYKEFA7OP0PCA1E88DR0735' => 3868,
            '3VI0PC2ZAY8YBBN21000JGJ7ABAXOK' => 3866,
            '3HO4MYYR12CG51N3WZ3JI9YCWOQ6UU' => 3865,
            '3S4TINXCC0BRY8K1W48IUJSFRMIBOD' => 3854,
            '3A9LA2FRWS2OJU1FXN5AZ7M6X9QHXD' => 3854,
            '338GLSUI43ZW9HOA8NBNXET13RASF6' => 3848,
            '35ZRNT9RUIMMVDGOHBTCC0U33C2O3W' => 3845,
            '3MG8450X2OYOF758BV2SO9PTQUIUPR' => 3845,
            '30U1YOGZGAKZBXAEHHGX9EQGREUSDV' => 3836,
            '3NOEP8XAU4QGWBZ3G0DF8GOXKCZPXE' => 3831,
            '33W1NHWFYH93TYSPYZAKAB55HFIZTQ' => 3831,
            '3T6SSHJUZFYRPUN44JNUW906AL0IIV' => 3829,
            '3UAU495MIIG6U7T7WVPDZ9K3FBROUG' => 3829,
            '3R6RZGK0XF0I10M9788GXMKO7SZYVQ' => 3828,
            '3SNR5F7R92HF9PLI80X3BU2EISTIEM' => 3827,
            '3511RHPADV268UYTF9EG2HTPGCHLR4' => 3826,
            '3VMHWJRYHV445YA92XHAWMATI6CFX1' => 3825,
            '33J5JKFMK6MPGPT4WOLG15P3HZOQ3H' => 3825,
            '3BS6ERDL93VUOZCHA4DU89UO95HD6A' => 3821,
            '3IVKZBIBJ0XGNDFG3DZN0Z5EISRHS4' => 3821,
            '38G0E1M85MT1KR24X7BRU1EBDFWVUL' => 3820,
            '3UAU495MIIG6U7T7WVPDZ9K3GPMUOA' => 3818,
            '362E9TQF2HEDT3H9EVNRBXNQV88IG3' => 3817,
            '3W0XM68YZPJ7VJHUWFN0HQYXKZC1KI' => 3815,
            '3WRBLBQ2GRW2M80TA5YL5TNU8IA0GG' => 3793,
            '3SBX2M1TKDBAYLC8W2QZBAH9S524Q7' => 3692,
            '374UMBUHN5DQL5HF6LQCZD4K5IVTCN' => 3692,
            '3AFT28WXLFQ1LGY72E0ZG6WSL26IOC' => 2885,
            '3Q2T3FD0ONWYVAVC4VEZKW5Y7KU3MI' => 2885,
            '35NNO802AVKJ3VYV1Z0M1HWNVS1INX' => 2885,
            '37VE3DA4YU5H6RYESRDSAAVEEQ8BH8' => 2885,
            '3TTPFEFXCT8B0FHJW0WKZU9M0PX6HI' => 2885,
            '3J9UN9O9J3GCDAQUIBJO26FA1P00JO' => 2885,
            '37SOB9Z0SSLEPSDR4JDKKJQ97F43LG' => 2885,
        ];
        $update_data = [
            'hit_id'      => null,
            'hit_type_id' => null,
            'status'      => '0',
            'approved'    => '0',
            'assignments' => null,
        ];
        $backup_data = [];

        foreach ($map_ids as $hit_id => $contract_id) {
            $backup_data[] = $this->task->getMturkTask($contract_id, $hit_id);
            $this->task->resetHitCmd($contract_id, $hit_id, $update_data);
        }

        return $backup_data;
    }

    /**
     * Restores the hits. Temporary function. Remove after user
     *
     * @param $data
     */
    public function restoreHitCommand($data)
    {
        foreach ($data as $datum) {
            $update_data = [
                'hit_id'      => $datum['hit_id'],
                'hit_type_id' => $datum['hit_type_id'],
                'status'      => $datum['status'],
                'approved'    => $datum['approved'],
                'assignments' => json_encode($datum['assignments']),
            ];
            $this->task->restoreHitId($datum['id'], $update_data);
        }
    }
}
