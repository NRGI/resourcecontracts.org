<?php namespace App\Nrgi\Mturk\Repositories\MturkTaskItem;

use App\Nrgi\Mturk\Entities\MTurkTaskItem;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface MturkTaskItemRepositoryInterface
 * @package App\Nrgi\Mturk\Repositories
 */
interface MturkTaskItemRepositoryInterface
{
    /**
     * Create MTurk Task Items
     *
     * @param $tasksItems
     * @return bool
     */
    public function createMturkTasksItems($taskItems);

    /**
     * Get All Task Items by Task ID
     * @param $task_id
     *
     * @return Collection
     */
    public function getAll($task_id);

    /**
     * Update Task Item
     *
     * @param $task_id
     * @param $page_no
     * @param $update
     * @return mixed
     */
    public function update($task_id, $page_no, $update);

    /**
     * Get Task Item detail
     *
     * @param $task_id
     * @param $task_item_id
     * @return task
     */
    public function getMturkTaskItem($task_id, $task_item_id);

    /**
     * Get Total Task Items for Task
     *
     * @param $contact_id
     * @return int
     */
    public function getTotalMturkTaskItems($task_id);

}
