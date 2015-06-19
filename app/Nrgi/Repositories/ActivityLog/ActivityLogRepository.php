<?php namespace App\Nrgi\Repositories\ActivityLog;

use App\Nrgi\Entities\ActivityLog\ActivityLog;
use Illuminate\Auth\Guard;

/**
 * Class ActivityLogRepository
 * @package App\Nrgi\Repositories\ActivityLog
 */
class ActivityLogRepository implements ActivityLogRepositoryInterface
{
    /**
     * @var ActivityLog
     */
    protected $activityLog;

    /**
     * @var Guard
     */
    protected $auth;

    /**
     * @param ActivityLog $activityLog
     */
    public function __construct(ActivityLog $activityLog, Guard $auth)
    {
        $this->activityLog = $activityLog;
        $this->auth        = $auth;
    }

    /**
     * @param $activityLog
     * @return bool
     */
    public function save($activityLog)
    {
        $activityLog['user_id'] = $this->auth->user()->id;

        return ($this->activityLog->create($activityLog) ? true : false);
    }
}
