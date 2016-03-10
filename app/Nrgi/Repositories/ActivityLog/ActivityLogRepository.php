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
     * @param        $activityLog
     * @param string $user_id
     * @return bool
     */
    public function save($activityLog, $user_id = null)
    {
        $activityLog['user_id'] = is_null($user_id) ? $this->auth->id() : $user_id;

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
       return $this->activityLog->with('user')->where('contract_id', $contract_id)->where('message', 'mturk.log.'.$log)->first();
    }
}
