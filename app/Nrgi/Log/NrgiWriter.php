<?php 
namespace App\Nrgi\Log;

use Illuminate\Log\Logger;

/**
 * NRGI logger class
 *
 * Class NrgiWriter
 * @package App\Nrgi\Log
 */
class NrgiWriter extends Logger
{
    /**
     * @param       $message
     * @param array $params
     * @param null  $contractId
     * @return bool
     */
    public function activity($message, $params = array(), $contractId = null, $user_id = null)
    {
        $activityLogService = app('App\Nrgi\Services\ActivityLog\ActivityLogService');

        return $activityLogService->save($message, $params, $contractId, $user_id);
    }

    /**
     * @param       $message
     * @param array $params
     * @param null  $contractId
     * @return bool
     */
    public function mTurkActivity($message, $params = array(), $contractId = null, $page_no= null)
    {
        $activity = app('App\Nrgi\Mturk\Services\ActivityService');
        return $activity->save($message, $params, $contractId, $page_no);
    }

}
