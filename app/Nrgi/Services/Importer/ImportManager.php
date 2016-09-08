<?php namespace App\Nrgi\Services\Importer;

use App\Nrgi\Entities\ExternalApi\ExternalApi;
use Illuminate\Contracts\Logging\Log;

/**
 * Class ImportManager
 * @package App\Nrgi\Services\Importer
 */
class ImportManager
{
    /**
     * @var ApiService
     */
    protected $importer;
    /**
     * @var ExternalApi
     */
    protected $api;
    /**
     * @var Log
     */
    private $log;

    /**
     * ImportManager constructor.
     *
     * @param ApiService  $importer
     * @param ExternalApi $api
     * @param Log         $log
     */
    public function __construct(ApiService $importer, ExternalApi $api, Log $log)
    {
        $this->importer = $importer;
        $this->api      = $api;
        $this->log      = $log;
    }

    /**
     * Index Contract data.
     */
    public function run()
    {
        $apis = $this->api->all();

        foreach ($apis as $api) {
            $this->importer->run($api->url, $api->last_index_date, $api->slug);
            $api = $api->updateIndexDate();
            $this->log->info($api->site.' Api - '.$api->url.' updated at '.$api->last_index_date);
        }
    }
}