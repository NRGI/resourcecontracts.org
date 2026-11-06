<?php namespace App\Nrgi\Services\Queue;

use App\Nrgi\Services\ElasticSearch\ElasticSearchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Queue for deleting contract in Elastic Search
 */
class DeleteToElasticSearchQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $contractId;

    /**
     * @param int $contractId
     */
    public function __construct(int $contractId)
    {
        $this->contractId = $contractId;
    }

    /**
     * Execute the job
     *
     * @param ElasticSearchService $elasticSearch
     */
    public function handle(ElasticSearchService $elasticSearch): void
    {
        $elasticSearch->delete($this->contractId);
    }
}
