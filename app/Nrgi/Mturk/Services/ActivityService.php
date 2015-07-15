<?php namespace App\Nrgi\Mturk\Services;

use App\Nrgi\Mturk\Repositories\Activity\ActivityRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class ActivityService
 * @package App\Nrgi\Services\ActivityLog
 */
class ActivityService
{
    /**
     * @var ActivityRepositoryInterface
     */
    protected $activity;

    /**
     * @param ActivityRepositoryInterface $activity
     */
    public function __construct(ActivityRepositoryInterface $activity)
    {
        $this->activity = $activity;
    }

    /**
     * Save activity
     *
     * @param       $message
     * @param array $params
     * @param null  $contract_id
     * @return bool
     */
    public function save($message, $params = array(), $contract_id = null, $page_no = null)
    {
        $activity            = [];
        $activity['message'] = $message;

        if (!empty($params)) {
            $activity['message_params'] = $params;
        }

        if (!is_null($contract_id)) {
            $activity['contract_id'] = $contract_id;
        }

        if (!is_null($page_no)) {
            $activity['page_no'] = $page_no;
        }

        return $this->activity->save($activity);
    }

    /**
     * Activities pagination
     *
     * @param int $perPage
     * @return Collection
     */
    public function getAll($filter, $perPage = 25)
    {
        return $this->activity->paginate($filter, $perPage);
    }
}
