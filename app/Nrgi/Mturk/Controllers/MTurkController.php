<?php namespace App\Nrgi\Mturk\Controllers;

use App\Http\Controllers\Controller;
use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Mturk\Services\ActivityService;
use App\Nrgi\Mturk\Services\MTurkService;
use App\Nrgi\Mturk\Services\TaskService;
use App\Nrgi\Services\Contract\ContractService;
use App\Nrgi\Services\User\UserService;
use Illuminate\Database\DatabaseManager;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;

/**
 * Class MturkController
 * @package App\Nrgi\Mturk\Controllers
 */
class MTurkController extends Controller
{
    /**
     * @var TaskService
     */
    protected $task;
    /**
     * @var ContractService
     */
    protected $contract;
    /**
     * @var ActivityService
     */
    protected $activity;
    /**
     * @var MTurkService
     */
    private $mturk;
    /**
     * @var DatabaseManager
     */
    private $db;

    /**
     * @param TaskService     $task
     * @param ContractService $contract
     * @param ActivityService $activity
     * @param MTurkService    $mturk
     * @param DatabaseManager $db
     */
    public function __construct(
        TaskService $task,
        ContractService $contract,
        ActivityService $activity,
        MTurkService $mturk,
        DatabaseManager $db
    ) {
        $this->middleware('auth', ['except' => 'publicPage']);
        $this->task     = $task;
        $this->contract = $contract;
        $this->activity = $activity;
        $this->mturk    = $mturk;
        $this->db    = $db;
    }

    /**
     * Display all the contracts sent for MTurk
     *
     * @param Request $request
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $filter = [
            'status'   => $request->get('status', Contract::MTURK_SENT),
            'category' => $request->get('category', 'all'),
        ];

        $contracts = $this->task->getContracts($filter);

        return view('mturk.index', compact('contracts'));
    }

    /**
     * Display all the tasks for a specific contract
     *
     * @param Request $request
     * @param         $contract_id
     *
     * @return string
     */
    public function tasksList(Request $request, $contract_id)
    {
        $status   = $request->get('status', null);
        $approved = $request->get('approved', null);
        $contract = $this->contract->findWithTasks($contract_id, $status, $approved);
        if (!$contract) {
            return abort(404);
        }

        $contractAll = $this->contract->findWithTasks($contract_id);

        $contract->tasks = $this->task->appendAssignment($contract->tasks);
        $total_pages     = $contractAll->tasks->count();
        $total_hit       = $this->task->getTotalHits($contract_id);
        $status          = $this->task->getTotalByStatus($contract_id);

        return view('mturk.tasks', compact('contract', 'total_pages', 'total_hit', 'status'));
    }

    /**
     * Create tasks
     *
     * @param $id
     *
     * @return Redirect
     */
    public function createTasks($id)
    {
        if ($this->task->create($id)) {
            return redirect()->back()->withSuccess(trans('mturk.action.sent_to_mturk'));
        }

        return redirect()->back()->withError(trans('mturk.action.sent_fail_to_mturk'));
    }

    /**
     * Task Detail
     *
     * @param $contract_id
     * @param $task_id
     *
     * @return \Illuminate\View\View
     */
    public function taskDetail($contract_id, $task_id)
    {
        $contract = $this->contract->findWithTasks($contract_id);
        $task     = $this->task->get($contract_id, $task_id);

        if (!$contract || !$task) {
            return abort(404);
        }

        $feedback = ($task->status == '1') ? $this->mturk->getAns($task) : '';

        return view('mturk.detail', compact('contract', 'task', 'feedback'));
    }

    /**
     * Approve Assignment
     *
     * @param $contract_id
     * @param $task_id
     *
     * @return Redirect
     */
    public function approve($contract_id, $task_id)
    {
        $status = $this->task->approveTask($contract_id, $task_id);
        $result = is_bool($status) ? $status : $status['result'];

        if ($result) {
            $message = isset($status['message']) ? $status['message'] : trans('mturk.action.approve');

            return redirect()->back()->withSuccess($message);
        }

        $error_msg = isset($status['message']) ? $status['message'] : trans('mturk.action.approve_fail');

        return redirect()->back()->withError($error_msg);
    }

    /**
     * Approve All Assignments
     *
     * @param $contract_id
     *
     * @return Redirect
     */
    public function approveAll($contract_id)
    {
        if ($this->task->approveAllTasks($contract_id)) {
            return redirect()->back()->withSuccess(trans('mturk.action.approve'));
        }

        return redirect()->back()->withError(trans('mturk.action.approve_fail'));
    }

    /**
     * Reject Assignment
     *
     * @param         $contract_id
     * @param         $task_id
     * @param Request $request
     *
     * @return Redirect
     */
    public function reject($contract_id, $task_id, Request $request)
    {
        $message = $request->input('message');

        if ($message == '') {
            return redirect()->back()->withError(trans('mturk.action.reject_reason'));
        }

        $status = $this->task->rejectTask($contract_id, $task_id, $message);
        $result = is_bool($status) ? $status : $status['result'];

        if ($result) {
            $message = isset($status['message']) ? $status['message'] : trans('mturk.action.reject');

            return redirect()->back()->withSuccess($message);
        }
        $error_msg = isset($status['message']) ? $status['message'] : trans('mturk.action.reject_fail');

        return redirect()->back()->withError($error_msg);
    }

    /**
     * Reset HIT
     *
     * @param $contract_id
     * @param $task_id
     *
     * @return Redirect
     */
    public function resetHit($contract_id, $task_id)
    {
        if (!$this->task->isBalanceToCreateHIT()) {
            return redirect()->back()->withError(trans('mturk.action.reset_balance_low'));
        }

        $resetStatus = $this->task->resetHIT($contract_id, $task_id);

        if (is_array($resetStatus)) {
            return redirect()->back()->withError($resetStatus['message']);
        }

        if ($resetStatus === true) {
            return redirect()->back()->withSuccess(trans('mturk.action.reset'));
        }

        return redirect()->back()->withError(trans('mturk.action.reset_fail'));
    }

    /**
     * Sent text to RC
     *
     * @param $contract_id
     *
     * @return Redirect
     */
    public function sendToRC($contract_id)
    {
        if ($this->task->copyTextToRC($contract_id)) {
            return redirect()->back()->withSuccess(trans('mturk.action.sent_to_rc'));
        }

        return redirect()->back()->withError(trans('mturk.action.sent_fail_to_rc'));
    }

    /**
     * @param Request     $request
     * @param UserService $user
     *
     * @return View
     */
    public function activity(Request $request, UserService $user)
    {
        $filter     = $request->only('contract', 'user');
        $activities = $this->activity->getAll($filter);
        $users      = $user->getList();
        $contracts  = $this->task->getContractsList();

        return view('mturk.activity', compact('activities', 'users', 'contracts'));
    }

    /**
     * Display all tasks
     *
     * @param Request $request
     *
     * @return View
     */
    public function allTasks(Request $request)
    {
        $filter       = [
            'status'   => $request->get('status', null),
            'approved' => $request->get('approved', null),
            'hitid'    => $request->get('hitid', null),
        ];
        $tasks        = $this->task->allTasks($filter);
        $show_options = is_null($filter['hitid']) ? true : false;

        return view('mturk.allTasks', compact('tasks', 'show_options'));
    }

    /**
     * Task SubmitPage
     *
     * @param Request $request
     *
     * @return View
     */
    public function publicPage(Request $request)
    {
        $assignmentId = $request->get('assignmentId');
        $workerId     = $request->get('workerId');
        $langCode     = strtolower($request->get('lang', 'en'));
        $pdf          = $request->get('pdf');

        return view('mturk.public', compact('assignmentId', 'workerId', 'langCode', 'pdf'));
    }

    /**
     * Resets the hit. Temporary function. Remove after user
     *
     * @return mixed
     */
    public function resetHitCmd()
    {
        if (auth()->user()->isAdmin()) {
            $this->db->beginTransaction();

            try {
                $backup_tasks = $this->task->resetHitCommand();

                file_put_contents('hit_bk.json', json_encode($backup_tasks), FILE_APPEND);
                $this->db->commit();
                return redirect()->route('contract.index')->withSuccess('HIT reset successfully');
            } catch (\Exception $e) {
                $this->db->rollBack();
                file_put_contents('hit_reset_error.log', $e->getMessage(), FILE_APPEND);

                return redirect()->route('contract.index')->withSuccess('HIT reset error');
            }
        }

        return redirect()->route('contract.index')->withSuccess('Access denied');
    }

    /**
     * Restores the hits. Temporary function. Remove after user
     *
     * @return mixed
     */
    public function restoreHitCmd()
    {
        if (auth()->user()->isAdmin()) {
            $this->db->beginTransaction();
            try {
                $data = json_decode(file_get_contents('hit_bk.json'), true);
                $this->task->restoreHitCommand($data);
                $this->db->commit();
                unlink('hit_bk.json');

                return redirect()->route('contract.index')->withSuccess('HIT restored successfully');
            } catch (\Exception $e) {
                $this->db->rollBack();
                file_put_contents('hit_restore_error.log', $e->getMessage(), FILE_APPEND);

                return redirect()->route('contract.index')->withSuccess('HIT restored error');
            }
        }

        return redirect()->route('contract.index')->withSuccess('Access denied');
    }

}
