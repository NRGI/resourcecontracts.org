<?php
namespace App\Nrgi\Services\Queue;

use App\Nrgi\Services\ElasticSearch\ElasticSearchService;

/**
 * Queue for posting to  Elastic Search
 * Class PostToElasticSearch
 * @package App\Nrgi\Services\Queue
 */
class PostToElasticSearchQueue
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
        $this->elasticSearch->post($data['contract_id'], $data['type']);
        $job->delete();
    }
}
