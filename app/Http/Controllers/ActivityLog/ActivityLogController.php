<?php namespace app\Http\Controllers\ActivityLog;

use App\Http\Controllers\Controller;
use App\Nrgi\Services\ActivityLog\ActivityLogService;
use App\Nrgi\Services\Contract\ContractService;
use App\Nrgi\Services\Contract\CountryService;
use App\Nrgi\Services\User\UserService;
use Illuminate\Http\Request;
use Illuminate\Auth\Guard;

/**
 * Class ActivityController
 * @property CountryService country
 * @property Guard          auth
 * @package app\Http\Controllers\ActivityLog
 */
class ActivityLogController extends Controller
{
    /**
     * @var ActivityLogService
     */
    protected $activity;

    /**
     * @param ActivityLogService $activity
     * @param CountryService     $country
     * @param Guard              $auth
     */
    public function __construct(
        ActivityLogService $activity,
        CountryService $country,
        Guard $auth
    ) {
        $this->middleware('auth');
        $this->activity = $activity;
        $this->country  = $country;
        $this->auth     = $auth;
    }

    /**
     * @param Request         $request
     * @param UserService     $userService
     * @param ContractService $contract
     *
     * @return \Illuminate\View\View
     */
    public function index(
        Request $request,
        UserService $userService,
        ContractService $contract
    ) {
        $user         = $this->auth->user();
        $filter       = $request->only('contract', 'user', 'category', 'country', 'status');
        $activityLogs = $this->activity->getAll($filter);
        $users        = (!$user->isCountryUser()) ? $userService->getList() : $userService->getAllUsersList();
        $contracts    = $contract->getList();
        $categories   = trans('category');
        $countries    = (!$user->isCountryUser()) ? $this->country->all() : null;
        $status       = trans('status');

        return view(
            'activitylog.index',
            compact(
                'activityLogs',
                'users',
                'contracts',
                'categories',
                'countries',
                'status'
            )
        );
    }
}
