<?php namespace App\Nrgi\Services\Queue;

use App\Nrgi\Services\Contract\Page\ProcessService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class ProcessDocumentQueue
 */
class ProcessDocumentQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $contractId;

    /**
     * Constructor
     * @param int $contractId
     */
    public function __construct(int $contractId)
    {
        $this->contractId = $contractId;
    }

    /**
     * Execute the job
     *
     * @param ProcessService $process
     */
    public function handle(ProcessService $process): void
    {
        $process->execute($this->contractId);
    }
}
