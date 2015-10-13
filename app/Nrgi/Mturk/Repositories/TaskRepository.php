<?php namespace App\Nrgi\Mturk\Repositories;

use App\Nrgi\Mturk\Entities\Task;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class TaskRepository
 * @package App\Nrgi\Mturk\Repositories
 */
class TaskRepository implements TaskRepositoryInterface
{
    /**
     * @var Task
     */
    protected $task;

    /**
     * @param Task $task
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * Create Tasks in MTurk
     *
     * @param array $tasks
     * @return bool
     */
    public function createTasks($tasks)
    {
        $tasks_collection = $tasks->toArray();

        $tasks = [];
        foreach ($tasks_collection as $key => $value) {
            $tasks[] = array_only($value, ['contract_id', 'page_no', 'pdf_url']) + [
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
        }

        return $this->task->insert($tasks);
    }

    /**
     * Update Task
     *
     * @param $contract_id
     * @param $page_no
     * @param $update
     * @return mixed
     */
    public function update($contract_id, $page_no, $update)
    {
        return $this->task->where('contract_id', $contract_id)
                          ->where('page_no', $page_no)
                          ->update($update);
    }

    /**
     * Get All Task by Contract ID
     * @param $contract_id
     *
     * @return Collection
     */
    public function getAll($contract_id)
    {
        return $this->task->where('contract_id', $contract_id)->get();
    }

    /**
     * Get Task detail
     *
     * @param $contract_id
     * @param $task_id
     * @return task
     */
    public function getTask($contract_id, $task_id)
    {
        return $this->task->where('contract_id', $contract_id)->where('id', $task_id)->first();
    }

    /**
     * Get Total Hits
     *
     * @param $contact_id
     * @return int
     */
    function getTotalHits($contact_id)
    {
        return $this->task->where('contract_id', $contact_id)
                          ->where('hit_id', '!=', '')
                          ->where('hit_type_id', '!=', '')
                          ->count();
    }

    /**
     * Get Total by status
     *
     * @param $contract_id
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
        return $this->task->expired()->pending()->get();
    }
}
