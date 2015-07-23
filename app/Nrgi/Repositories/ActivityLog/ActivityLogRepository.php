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
}
