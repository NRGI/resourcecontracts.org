<?php
namespace App\Nrgi\Services\Queue;

use App\Nrgi\Services\ElasticSearch\ElasticSearchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Queue for posting to Elastic Search
 */
class PostToElasticSearchQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $contractId;
    private string $type;

    /**
     * @param int $contractId
     * @param string $type
     */
    public function __construct(int $contractId, string $type)
    {
        $this->contractId = $contractId;
        $this->type = $type;
    }

    /**
     * Execute the job
     *
     * @param ElasticSearchService $elasticSearch
     */
    public function handle(ElasticSearchService $elasticSearch): void
    {
        $elasticSearch->post($this->contractId, $this->type);
    }
}
