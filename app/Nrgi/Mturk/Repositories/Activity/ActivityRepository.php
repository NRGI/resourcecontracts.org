<?php namespace App\Nrgi\Mturk\Repositories\Activity;

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
     * @param Activity $activity
     * @param Guard    $auth
     */
    public function __construct(Activity $activity, Guard $auth)
    {
        $this->activity = $activity;
        $this->auth     = $auth;
    }

    /**
     * Save activity
     *
     * @param $activity
     * @return bool
     */
    public function save($activity)
    {
        $activity['user_id'] = null;
        $user                = $this->auth->user();

        if (isset($user->id)) {
            $activity['user_id'] = $user->id;
        }

        return ($this->activity->create($activity) ? true : false);
    }

    /**
     * @param $limit
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
}
