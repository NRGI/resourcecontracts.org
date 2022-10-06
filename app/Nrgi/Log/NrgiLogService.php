<?php
 
namespace App\Nrgi\Log;
 
use Monolog\Logger;
use App\Nrgi\Services\ActivityLog\ActivityLogService;
use App\Nrgi\Mturk\Services\ActivityService;
use Psr\Log\LoggerInterface as Log;
 
class NrgiLogService
{
    /**
     * @var ActivityLogService
     */
    protected $activityLogService;
    /**
     * @var ActivityService
     */
    protected $activityService;

    /**
     * @var Log
     */
    protected $log;

    /**
     * @param ActivityLogService     $activityLogService
     * @param ActivityService $activityService
     * @param Log $log
     */
    public function __construct(
        ActivityLogService $activityLogService,
        ActivityService $activityService,
        Log $log
    ) {
        $this->activityLogService = $activityLogService;
        $this->activityService = $activityService;
        $this->log = $log;
    }
       /**
     * @param       $message
     * @param array $params
     * @param null  $contractId
     * @param null  $user_id
     * @return bool
     */
    public function activity($message, $params = array(), $contractId = null, $user_id = null)
    {
        $this->log->info('activity'.$message.json_encode($params).$user_id);
        return $this->activityLogService->save($message, $params, $contractId, $user_id);
    }

    /**
     * @param       $message
     * @param array $params
     * @param null  $contractId
     * @param null  $pages
     * @return bool
     */
    public function mTurkActivity($message, $params = array(), $contractId = null, $pages= null)
    {
        $this->log->info('mturkActivity'.$message.json_encode($params).$contractId);
        return $this->activityService->save($message, $params, $contractId, $pages);
    }
}