<?php namespace App\Nrgi\Mturk\Repositories;

use App\Nrgi\Mturk\Entities\Task;
use App\Nrgi\Mturk\Entities\MturkTaskItem;
use Illuminate\Database\Eloquent\Collection;
use Psr\Log\LoggerInterface as Log;

/**
 * Class TaskRepository
 *
 * @method void where()
 * @method void whereRaw()
 * @method void select()
 * @method void orderByRaw()
 * @method void count()
 * @method void whereIn()
 * @method void selectRaw()
 *
 * @package App\Nrgi\Mturk\Repositories
 */
class TaskRepository implements TaskRepositoryInterface
{
    /**
     * @var Task
     */
    protected $task;

        /**
     * @var MturkTaskItem
     */
    protected $taskItem;
    /**
     * @var Log
     */
    protected $logger;

    /**
     * @param Task $task
     * @param MturkTaskItem  $taskItem
     * @param Log  $logger
     */
    public function __construct(Task $task, MturkTaskItem $taskItem, Log $logger)
    {
        $this->task = $task;
        $this->taskItem = $taskItem;
        $this->logger = $logger;
    }

    public function getLogArray($arr) 
    {
        return array_map(function($el) { unset($el['text']); return $el;}, array_merge(array(), $arr));
    }

    /**
     * Create Tasks in MTurk
     *
     * @param array $tasks
     *
     * @return bool
     */
    public function createTasks($tasks_collection, $task_items_per_task)
    {
        $this->logger->info('TaskRepo:createTasks'.json_encode($this->getLogArray($tasks_collection)));
        $per_task_count = isset($task_items_per_task) && !is_nan($task_items_per_task) && $task_items_per_task > 0 ? $task_items_per_task : 5;
        $chunked_tasks = array_chunk($this->getLogArray($tasks_collection), $per_task_count);
        $this->logger->info('createTasks:'.json_encode($chunked_tasks));
        $task_items = [];
        foreach ($chunked_tasks as $k => $task_item_group) {
            if (count($task_item_group) < 1) {
                continue;
            }
            //TODO:REMOVE page_no and pdf_url
            $task = array_only($task_item_group[0], ['contract_id', 'page_no', 'pdf_url']) + [
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            $this->logger->info('createTasks:task'.json_encode($task));
            $this->logger->info('createTasks:task_item_group'.json_encode($task_item_group[0]));
            $created_task_id = $this->task->insertGetId($task);
            $this->logger->info('createTasks:created_task'.json_encode($created_task_id));
            foreach ($task_item_group as $eleKey => $task_item_val) {
                $task_items[] = array_only($task_item_val, ['page_no', 'pdf_url']) + [
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'task_id' => $created_task_id,
                ];
            }
        }
        $this->logger->info('createTasks:task_items'.json_encode($this->getLogArray($task_items)));
        return $this->taskItem->insert($task_items);
    }

    /**
     * Update Task
     *
     * @param $contract_id
     * @param $page_nos_arr
     * @param $update
     *
     * @return mixed
     */
    public function update($contract_id, $page_nos, $update)
    {
        $task = $this->task->where('contract_id', $contract_id)->whereHas('taskItems',
         function ($q) use($page_nos) {
           return $q->whereIn('page_no', $page_nos);})
           ->update($update);
    }

     /**
     * Update Task
     *
     * @param $contract_id
     * @param $task_id
     * @param $update
     *
     * @return mixed
     */
    public function updateWithId($contract_id, $task_id, $update)
    {
        $task = $this->task->where('contract_id', $contract_id)->where('id', $task_id)->update($update);
    }

    /**
     * Get All Task by Contract ID
     *
     * @param $contract_id
     *
     * @return Collection
     */
    public function getAll($contract_id)
    {
        return $this->task->where('contract_id', $contract_id)->with('taskItems')->get();
    }

    /**
     * Get Task detail
     *
     * @param $contract_id
     * @param $task_id
     *
     * @return task
     */
    public function getTask($contract_id, $task_id)
    {
        return $this->task->where('contract_id', $contract_id)->where('id', $task_id)->with('taskItems')->first();
    }

    /**
     * Get Total Hits
     *
     * @param $contract_id
     *
     * @return int
     */
    public function getTotalHits($contract_id)
    {
        return $this->task->where('contract_id', $contract_id)
                          ->where('hit_id', '!=', '')
                          ->where('hit_type_id', '!=', '')
                          ->count();
    }

    /**
     * Get Total by status
     *
     * @param $contract_id
     *
     * @return array
     */
    public function getTotalByStatus($contract_id)
    {
        return [
            'total_completed'        => $this->task->completed()->where('contract_id', $contract_id)->count(),
            'total_approved'         => $this->task->approve()->where('contract_id', $contract_id)->count(),
            'total_rejected'         => $this->task->rejected()->where('contract_id', $contract_id)->count(),
            'total_pending_approval' => $this->task->completed()
                                                   ->approvalPending()
                                                   ->where('contract_id', $contract_id)
                                                   ->count(),
        ];
    }

    /**
     * Get All Approval pending Task by Contract ID
     *
     * @param $contract_id
     *
     * @return Collection
     */
    public function getApprovalPendingTask($contract_id)
    {
        return $this->task->completed()->approvalPending()->where('contract_id', $contract_id)->get();
    }

    /**
     * Get All Expired Tasks
     *
     * @return Collection
     */
    public function getExpired()
    {
        return $this->task->whereRaw(
            "status='0' AND (hit_id is null OR date(now()) >= date(created_at + interval '".config(
                'mturk.hitRenewDay'
            )."' day))"
        )->with('taskItems')->get();
    }

    /**
     * Get all Tasks
     *
     * @param      $filter
     * @param null $perPage
     *
     * @return Collection
     */
    public function allTasks($filter, $perPage = null)
    {
        $status   = $filter['status'];
        $approved = $filter['approved'];
        $hitid    = $filter['hitid'];

        $query = $this->task->join('contracts', 'mturk_tasks.contract_id', '=', 'contracts.id')
                            ->select('contracts.*', 'mturk_tasks.*')
                            ->orderBy('mturk_tasks.created_at', 'DESC');

        if (!is_null($status)) {
            $query->where('mturk_tasks.status', $status);
        }

        if (!is_null($approved)) {
            $query->where('mturk_tasks.approved', $approved);
        }

        if (!is_null($hitid)) {
            $query->where('mturk_tasks.hit_id', $hitid);
        }


        if (!is_null($perPage)) {
            return $query->paginate($perPage);
        }

        return $query->get();
    }


    /**
     * Returns mturk tasks which need to be reset. Temporary function. Remove after user
     *
     * @param $contract_id
     * @param $hit_id
     *
     * @return mixed
     */
    public function getMturkTask($contract_id, $hit_id)
    {
        return $this->task->whereRaw("contract_id=$contract_id and hit_id='$hit_id'")->get()->first()->toArray();
    }

    /**
     * Resets the hits. Temporary function. Remove after user
     *
     * @param $contract_id
     * @param $hit_id
     * @param $update
     *
     * @return mixed
     */
    public function resetHitCmd($contract_id, $hit_id, $update)
    {
        return $this->task->where('hit_id', $hit_id)
                          ->where('contract_id', $contract_id)
                          ->update($update);
    }

    /**
     * Restores the reset hits. Temporary function. Remove after user
     *
     * @param $id
     * @param $update
     *
     * @return mixed
     */
    public function restoreHitId($id, $update)
    {
        return $this->task->where('id', $id)
                          ->update($update);
    }
}
