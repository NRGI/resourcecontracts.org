<?php namespace App\Nrgi\Services\Queue;

use App\Nrgi\Services\ElasticSearch\ElasticSearchService;

/**
 * Queue for deleting contract in Elastic Search
 * Class DeleteToElasticSearchQueue
 * @package App\Nrgi\Services\Queue
 */
class DeleteToElasticSearchQueue
{
    /**
     * @var ElasticSearchService
     */
    public $elasticSearch;

    /**
     * @param ElasticSearchService $elasticSearchService
     */
    public function __construct(ElasticSearchService $elasticSearch)
    {
        $this->elasticSearch = $elasticSearch;
    }

    /**
     * @param $job
     * @param $data
     */
    public function fire($job, $data)
    {
        $this->elasticSearch->delete($data['contract_id']);
        $job->delete();
    }
}
