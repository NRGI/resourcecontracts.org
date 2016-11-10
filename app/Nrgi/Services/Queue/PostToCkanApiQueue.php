<?php
namespace App\Nrgi\Services\Queue;

use App\Nrgi\Services\CKAN\CKANService;
/**
 * Queue for posting to  CKAN API
 * @package App\Nrgi\Services\Queue
 */
class PostToCkanApiQueue
{
    /**
     * @var CKANService
     */
    private $ckanApi;

    /**
     * @param CKANService $ckanApi
     */
    public function __construct(CKANService $ckanApi)
    {
        $this->ckanApi = $ckanApi;
    }

    /**
     * @param $job
     * @param $dataToCkan
     */
    public function fire($job, $dataToCkan)
    {
        //data ok till now
        $this->ckanApi->callCkanApi($dataToCkan);
        $job->delete();
    }
}
