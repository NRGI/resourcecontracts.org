<?php namespace App\Nrgi\Mturk\Services\Queue;

use App\Nrgi\Mturk\Services\TaskService;

/**
 * Class MTurkQueue
 * @package App\Nrgi\Mturk\Services\Queue
 */
class MTurkQueue
{
    /**
     * @var TaskService
     */
    protected $task;

    /**
     * @param TaskService $task
     */
    public function __construct(TaskService $task)
    {
        $this->task = $task;
    }

    /**
     * Execute the queue
     *
     * @param $job
     * @param $data
     */
    public function fire($job, $data)
    {
        $this->task->mTurkProcess($data['contract_id']);
        $job->delete();
    }
}
