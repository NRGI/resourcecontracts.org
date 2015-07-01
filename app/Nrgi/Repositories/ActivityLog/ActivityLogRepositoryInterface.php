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
     * @return bool
     */
    public function save($activityLog);

    /**
     * @param $filter
     * @param $limit
     * @return ActivityLog
     */
    public function paginate($filter, $limit);
}
