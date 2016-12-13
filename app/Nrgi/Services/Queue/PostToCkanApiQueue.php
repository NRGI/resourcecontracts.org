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
     * @var client
     */

    /**
     * @param $job
     * @param $dataToCkan
     */
    public function fire($job, $dataToCkan)
    {
        $this->ckanApi->callCkanApiPost($dataToCkan);
        $job->delete();
    }
}
