<?php namespace App\Nrgi\Services\Queue;

use App\Nrgi\Services\ElasticSearch\ElasticSearchService;

class DeleteElementQueue
{
    /**
     * @var ElasticSearchService
     */
    public $elastic;

    /**
     * @param ElasticSearchService $elastic
     */
    public function __construct(ElasticSearchService $elastic)
    {
        $this->elastic = $elastic;
    }

    public function fire($job,$data)
    {
        $this->elastic->deleteElement($data['contract_id'],$data['type']);
        $job->delete();
    }
}