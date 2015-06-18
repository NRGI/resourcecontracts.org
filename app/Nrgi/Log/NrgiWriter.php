<?php namespace App\Nrgi\Log;

use App\Nrgi\Repositories\ActivityLog\ActivityLogService;
use Illuminate\Log\Writer;

/**
 * NRGI logger class
 *
 * Class NrgiWriter
 * @package App\Nrgi\Log
 */
class NrgiWriter extends Writer
{
    /**
     * @var ActivityLogService
     */
    protected $activityLog;

    /**
     * @param       $message
     * @param array $params
     * @param null  $contractId
     * @return bool
     */
    public function activity($message, $params = array(), $contractId = null)
    {
        $activityLogService = app('App\Nrgi\Services\ActivityLog\ActivityLogService');
        return $activityLogService->save($message, $params, $contractId);
    }
}
