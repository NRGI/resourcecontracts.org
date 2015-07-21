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
}
