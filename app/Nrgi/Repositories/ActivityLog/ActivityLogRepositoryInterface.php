<?php namespace App\Nrgi\Repositories\ActivityLog;

use App\Nrgi\Entities\ActivityLog\ActivityLog;

/**
 * Class ActivityLogRepository
 * @package App\Nrgi\Repositories\ActivityLog
 */
interface ActivityLogRepositoryInterface
{
    /**
     * @param $activityLog
     * @param $user_id
     * @return bool
     */
    public function save($activityLog, $user_id = null);

    /**
     * @param $filter
     * @param $limit
     * @return ActivityLog
     */
    public function paginate($filter, $limit);
}
