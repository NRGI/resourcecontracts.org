<?php namespace App\Nrgi\Mturk\Repositories;

use App\Nrgi\Mturk\Entities\Task;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface TaskRepositoryInterface
 * @package App\Nrgi\Mturk\Repositories
 */
interface TaskRepositoryInterface
{
    /**
     * Create Tasks in MTurk
     *
     * @param $tasks
     * @return bool
     */
    public function createTasks($tasks);

    /**
     * Get All Task by Contract ID
     * @param $contract_id
     *
     * @return Collection
     */
    public function getAll($contract_id);

    /**
     * Update Task
     *
     * @param $contract_id
     * @param $page_no
     * @param $update
     * @return mixed
     */
    public function update($contract_id, $page_no, $update);

    /**
     * Get Task detail
     *
     * @param $contract_id
     * @param $task_id
     * @return task
     */
    public function getTask($contract_id, $task_id);

    /**
     * Get Total Hits
     *
     * @param $contact_id
     * @return int
     */
    public function getTotalHits($contact_id);

    /**
     * Get Total by status
     *
     * @param $contract_id
     * @return array
     */
    public function getTotalByStatus($contract_id);

    /**
     * Get All Task by Contract ID
     * @param $contract_id
     *
     * @return Collection
     */
    public function getApprovalPendingTask($contract_id);

    /**
     * Get All Expired Tasks
     *
     * @return Collection
     */
    public function getExpired();

    /**
     * Get all Tasks
     *
     * @param $filter
     * @param $null
     * @return Collection
     */
    public function allTasks($filter, $perPage);

}
