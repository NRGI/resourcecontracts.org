<?php namespace App\Nrgi\Mturk\Services\Queue;

use App\Nrgi\Mturk\Services\TaskService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class MTurkQueue
 * @package App\Nrgi\Mturk\Services\Queue
 */
class MTurkQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private array $data;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the queue job.
     *
     * @param TaskService $task
     */
    public function handle(TaskService $task): void
    {
        $task->mTurkProcess($this->data);
    }
}
