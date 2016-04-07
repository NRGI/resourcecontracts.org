<?php namespace App\Nrgi\Repositories\ActivityLog;

use App\Nrgi\Entities\ActivityLog\ActivityLog;
use App\Nrgi\Mturk\Entities\Activity;
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
     * @var Activity
     */
    protected $mTurkActivities;

    /**
     * @param ActivityLog $activityLog
     * @param Activity    $mTurkActivities
     * @param Guard       $auth
     * @internal param Activity $mTurkActivities
     */
    public function __construct(ActivityLog $activityLog, Activity $mTurkActivities, Guard $auth)
    {
        $this->activityLog     = $activityLog;
        $this->auth            = $auth;
        $this->mTurkActivities = $mTurkActivities;
    }

    /**
     * @param        $activityLog
     * @param string $user_id
     * @return bool
     */
    public function save($activityLog, $user_id = null)
    {
        if (is_null($user_id)) {
            $user_id = $this->auth->id();
        }

        if (empty($user_id)) {
            $user_id = 1;
        }

        $activityLog['user_id'] = $user_id;

        return ($this->activityLog->create($activityLog) ? true : false);
    }

    /**
     * @param $limit
     * @return ActivityLog
     */
    public function paginate($filter, $limit)
    {
        extract($filter);
        $query = $this->activityLog->with('user')->orderby('id', 'desc');

        if ($contract != '' && $contract != 'all') {
            $query->where('contract_id', $contract);
        }

        if ($user != '' && $user != 'all') {
            $query->where('user_id', $user);
        }

        return $query->paginate($limit);
    }

    /**
     * Get Mturk Log
     *
     * @param $contract_id
     * @param $log
     * @return ActivityLog
     */
    public function mturk($contract_id, $log)
    {
        return $this->mTurkActivities->with('user')->where('contract_id', $contract_id)->where('message', 'mturk.log.' . $log)->first();
    }

    /**
     * Count Activities by User
     *
     * @param $user_id
     * @return int
     */
    public function countByUser($user_id)
    {
        return $this->activityLog->where('user_id', $user_id)->count();
    }
}
