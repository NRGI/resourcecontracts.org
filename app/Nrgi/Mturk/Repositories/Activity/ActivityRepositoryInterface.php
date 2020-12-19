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
     * Get full info on latest published event for the given contract.
     * @param $id
     * @param $element
     * @return activityLog
     */
    public function getLatestPublicationEvent($id, $element);

    /**
     * write brief description
     * @param $id
     * @param $element
     * @return activityLog
     */
    public function getElementState($id, $element);

    /**
     * Returns details on the first time the element was published.
     *
     * @param $id
     * @param $element
     * @return mixed
     */
    public function getFirstPublicationEvent($id, $element);
}
