<?php namespace App\Nrgi\Services\ActivityLog;

use App\Nrgi\Entities\ActivityLog\ActivityLog;
use App\Nrgi\Repositories\ActivityLog\ActivityLogRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class ActivityLogService
 * @package App\Nrgi\Repositories\ActivityLog
 */
class ActivityLogService
{
    /**
     * @var ActivityLogRepositoryInterface
     */
    protected $activityLog;

    /**
     * @param ActivityLogRepositoryInterface $activityLog
     */
    public function __construct(ActivityLogRepositoryInterface $activityLog)
    {
        $this->activityLog = $activityLog;
    }

    /**
     * @param       $message
     * @param array $params
     * @param null  $contractId
     * @return bool
     */
    public function save($message, $params = [], $contractId = null, $user_id = null)
    {
        $activity            = [];
        $activity['message'] = $message;
        if (!empty($params)) {
            $activity['message_params'] = $params;
        }
        if (!is_null($contractId)) {
            $activity['contract_id'] = $contractId;
        }

        return $this->activityLog->save($activity, $user_id);
    }

    /**
     * @param int $perPage
     * @return Collection
     */
    public function getAll($filter, $perPage = 25)
    {
        return $this->activityLog->paginate($filter, $perPage);
    }

    /**
     * Get MTurk Log
     *
     * @param $log
     * @return ActivityLog
     */
    public function mturk($contract_id, $log)
    {
        $log = $this->activityLog->mturk($contract_id, $log);
        if (empty($log)) {
            $log = ActivityLog::with('user')->where('message', 'mturk.log.create')->where('contract_id', $contract_id)->first();
        }
        return $log;
    }
}
