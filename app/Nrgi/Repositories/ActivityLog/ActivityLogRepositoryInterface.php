<?php namespace App\Nrgi\Repositories\ActivityLog;

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
     * @param $limit
     * @return activity log model
     */
    public function paginate($limit);
}
