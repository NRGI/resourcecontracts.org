<?php namespace App\Nrgi\Mturk\Repositories\Activity;

use App\Nrgi\Mturk\Entities\Activity;

interface ActivityRepositoryInterface
{
    /**
     * Save activity
     *
     * @param $activity
     * @return bool
     */
    public function save($activity);

    /**
     * Activities pagination
     *
     * @param $filter
     * @param $limit
     * @return Activity
     */
    public function paginate($filter, $limit);

    /**
     * Count Activity by user
     *
     * @param $user_id
     * @return int
     */
    public function countByUser($user_id);

    /**
     * Get the first row where status is published
     * @param $id
     * @param $element
     * @return activityLog
     */
    public function getPublishedInfo($id, $element);

    /**
     * write brief description
     * @param $id
     * @param $element
     * @return activityLog
     */
    public function getElementState($id, $element);
}
