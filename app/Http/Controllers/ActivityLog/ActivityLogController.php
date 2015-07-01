<?php namespace app\Http\Controllers\ActivityLog;

use App\Http\Controllers\Controller;
use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Entities\User\User;
use App\Nrgi\Services\ActivityLog\ActivityLogService;
use App\Nrgi\Services\Contract\ContractService;
use App\Nrgi\Services\User\UserService;
use Illuminate\Auth\Guard;
use Illuminate\Http\Request;

/**
 * Class ActivityController
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
     */
    public function __construct(ActivityLogService $activity)
    {
        $this->middleware('auth');
        $this->activity = $activity;
    }

    /**
     * @param Request         $request
     * @param UserService     $user
     * @param ContractService $contract
     * @return \Illuminate\View\View
     */
    public function index(Request $request, UserService $user, ContractService $contract)
    {
        $filter       = $request->only('contract', 'user');
        $activityLogs = $this->activity->getAll($filter);
        $users        = $user->getList();
        $contracts    = $contract->getList();

        return view('activitylog.index', compact('activityLogs', 'users', 'contracts'));
    }
}
