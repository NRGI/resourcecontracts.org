<?php namespace App\Nrgi\Mturk\Repositories;

use App\Nrgi\Mturk\Entities\MturkTaskItem;
use Illuminate\Database\Eloquent\Collection;

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
class MturkTaskItemRepository implements MturkTaskItemRepositoryInterface
{
    /**
     * @var MturkTaskItem
     */
    protected $taskItem;

    /**
     * @param MturkTaskItem $taskItem
     */
    public function __construct(MturkTaskItem $taskItem)
    {
        $this->taskItem = $taskItem;
    }

    /**
     * Create Mturk task items
     *
     * @param array $task_items
     *
     * @return bool
     */
    public function createMturkTasksItems($task_items)
    {
        $tasks_items_collection = $task_items->toArray();

        $task_items = [];
        foreach ($tasks_items_collection as $key => $value) {
            $task_items[] = array_only($value, ['task_id', 'page_no', 'pdf_url']) + [
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
        }

        return $this->taskItem->insert($task_items);
    }

    /**
     * Update Task Item
     *
     * @param $task_id
     * @param $page_no
     * @param $update
     *
     * @return mixed
     */
    public function update($task_id, $page_no, $update)
    {
        return $this->taskItem->where('task_id', $task_id)
                          ->where('page_no', $page_no)
                          ->update($update);
    }

     /**
     * Update Task Item
     *
     * @param $task_id
     * @param $update
     *
     * @return mixed
     */
    public function updateAllTaskItems($task_id, $update)
    {
        return $this->taskItem->where('task_id', $task_id)->update($update);
    }

    /**
     * Get All Task Items by Task ID
     *
     * @param $contract_id
     *
     * @return Collection
     */
    public function getAll($task_id)
    {
        return $this->task->where('task_id', $task_id)->get();
    }

    /**
     * Get Task Item detail
     *
     * @param $task_id
     * @param $task_item_id
     *
     * @return task
     */
    public function getMturkTaskItem($task_id, $task_item_id)
    {
        return $this->task->where('task_id', $task_id)->where('id', $task_item_id)->first();
    }

    /**
     * Get Total Hits
     *
     * @param $contact_id
     *
     * @return int
     */
    public function getTotalMturkTaskItems($task_id)
    {
        return $this->task->where('task_id', $task_id)->count();
    }
}
