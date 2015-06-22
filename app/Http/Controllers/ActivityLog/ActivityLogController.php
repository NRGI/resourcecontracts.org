<?php namespace app\Http\Controllers\ActivityLog;

use App\Http\Controllers\Controller;
use App\Nrgi\Services\ActivityLog\ActivityLogService;
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
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $activityLogs = $this->activity->getAll();

        return view('activitylog.index', compact('activityLogs'));
    }
}
