<?php
namespace App\Nrgi\Services\Queue;

use App\Nrgi\Services\CKAN\CKANService;
use GuzzleHttp\Client;
/**
 * Queue for posting to  CKAN API
 * @package App\Nrgi\Services\Queue
 */
class DeleteFromCkanApiQueue
{

    public function __construct(CKANService $ckanApi)
    {
        $this->ckanApi = $ckanApi;
    }

    /**
     * @var client
     */

    /**
     * @param $job
     * @param $dataToCkan
     */
    public function fire($job, $dataToCkan)
    {
        $this->ckanApi->callCkanApiDelete($dataToCkan);
        $job->delete();
    }
}
