<?php namespace App\Nrgi\Mturk\Repositories\Activity;

use App\Nrgi\Entities\ActivityLog\ActivityLog;
use App\Nrgi\Mturk\Entities\Activity;
use Illuminate\Auth\Guard;

/**
 * Class ActivityRepository
 * @package App\Nrgi\Mturk\Repositories\Activity
 */
class ActivityRepository implements ActivityRepositoryInterface
{
    /**
     * @var Activity
     */
    protected $activity;

    /**
     * @var Guard
     */
    protected $auth;
    /**
     * @var ActivityLog
     */
    public $activityLog;

    /**
     * @param Activity    $activity
     * @param Guard       $auth
     * @param ActivityLog $activityLog
     */
    public function __construct(Activity $activity, Guard $auth, ActivityLog $activityLog)
    {
        $this->activity    = $activity;
        $this->auth        = $auth;
        $this->activityLog = $activityLog;
    }

    /**
     * Save activity
     *
     * @param $activity
     *
     * @return bool
     */
    public function save($activity)
    {
        $user_id = $this->auth->id();

        if (empty($user_id)) {
            $user_id = 1;
        }

        $activity['user_id'] = $user_id;

        return ($this->activity->create($activity) ? true : false);
    }

    /**
     * @param $limit
     *
     * @return Activity
     */
    public function paginate($filter, $limit)
    {
        extract($filter);
        $query = $this->activity->with('user', 'contract')->orderby('id', 'desc');

        if ($contract != '' && $contract != 'all') {
            $query->where('contract_id', $contract);
        }

        if ($user != '' && $user != 'all') {
            $query->where('user_id', $user);
        }

        return $query->paginate($limit);
    }

    /**
     * Count Activity by user
     *
     * @param $user_id
     *
     * @return int
     */
    public function countByUser($user_id)
    {
        return $this->activity->where('user_id', $user_id)->count();
    }

    /**
     * Get full info on latest published event for the given contract.
     *
     * @param $id
     * @param $element
     *
     * @return activityLog
     */
    public function getLatestPublicationEvent($id, $element)
    {
        $query = $this->activityLog->select('*')->with('user')
                                   ->where('contract_id', $id)
                                   ->whereRaw("message_params->>'new_status' = 'published'")
                                   ->whereRaw(sprintf("message_params->>'type' = '%s'", $element))
                                   ->orderBy("created_at", "desc");

        $result = $query->first();

        return $result;

    }

    /**
     * @param $id
     * @param $element
     * @return mixed|void
     */
    public function getFirstPublicationEvent($id, $element)
    {
        $query = $this->activityLog->select('*')->with('user')
            ->where('contract_id', $id)
            ->whereRaw("message_params->>'new_status' = 'published'")
            ->whereRaw(sprintf("message_params->>'type' = '%s'", $element))
            ->orderBy("created_at", "asc");

        return $query->first();
    }

    /**
     * write brief description
     *
     * @param $id
     * @param $element
     *
     * @return activityLog
     */
    public function getElementState($id, $element)
    {
        $query = $this->activityLog->select('*')
                                   ->where('contract_id', $id)
                                   ->whereRaw(
                                       "(message_params->>'new_status' = 'published' or message_params->>'new_status' = 'unpublished' )"
                                   )
                                   ->whereRaw(sprintf("message_params->>'type' = '%s'", $element))
                                   ->orderBy("created_at", "desc");


        $result = $query->first();

        return $result;
    }

    /**
     * Returns published date for contracts
     *
     * @param bool $recent
     *
     * @return mixed
     */
    public function getPublishedContracts($recent = false)
    {
        $where_raw = "message_params->>'new_status'='published'";

        if ($recent) {
            $where_raw .= " and created_at > CURRENT_DATE - INTERVAL '3 months'";
        }

        return $this->activityLog->selectRaw("contract_id, min(created_at) as published_at")
                                 ->whereRaw($where_raw)
                                 ->groupBy("contract_id")
                                 ->get();
    }
}
