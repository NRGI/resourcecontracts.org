<?php namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserRequest;
use App\Nrgi\Entities\User\Role\Role;
use App\Nrgi\Services\User\UserService;
use Illuminate\Auth\Guard;

/**
 * Class UserController
 * @package App\Http\Controllers\Contract
 */
class UserController extends Controller
{
    /**
     * @var UserService
     */
    protected $user;
    /**
     * @var Guard
     */
    protected $auth;

    /**
     * @param UserService $user
     * @param Guard       $auth
     */
    public function __construct(UserService $user, Guard $auth)
    {
        $this->middleware('auth');
        $this->user = $user;
        $this->auth = $auth;

        if (!$this->auth->user()->hasRole('superadmin')) {
            return redirect('/home')->withError(trans('contract.permission_denied'))->send();
        }

    }

    /**
     * List of users
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $users = $this->user->all();

        return view('user.list', compact('users'));
    }

    /**
     * Create new user
     *
     * @return string
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $roles = $this->user->getAllRoles();

        return view('user.create', compact('roles'));
    }

    /**
     * Register user
     *
     * @param UserRequest $request
     * @return \Illuminate\Routing\Redirector
     */
    public function store(UserRequest $request)
    {
        $user_detail = $request->only('name', 'email', 'password', 'organization', 'status');
        $role        = $request->input('role');

        if ($this->user->create($user_detail, $role)) {
            return redirect()->route('user.list')->withSuccess(trans('user.create_success'));
        }

        return redirect()->route('user.list')->withSuccess(trans('user.create_fail'));
    }

    /**
     * Edit user view
     *
     * @param $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $roles = Role::lists('name', 'id');
        $user  = $this->user->find($id);

        return view('user.edit', compact('roles', 'user'));
    }

    /**
     * Update User
     *
     * @param UserRequest $request
     * @param             $id
     * @return \Illuminate\Routing\Redirector
     */
    public function update(UserRequest $request, $id)
    {
        $user_detail = $request->only('name', 'email', 'password', 'organization', 'status');
        $role        = $request->input('role');

        if ($this->user->update($id, $user_detail, $role)) {
            return redirect()->route('user.list')->withSuccess(trans('user.update_success'));
        }

        return redirect()->route('user.list')->withSuccess(trans('user.update_fail'));
    }

    /**
     * Delete User
     *
     * @param $id
     * @return \Illuminate\Routing\Redirector
     */
    public function delete($id)
    {
        if ($this->user->delete($id)) {
            return redirect()->route('user.list')->withSuccess(trans('user.delete_success'));
        }

        return redirect()->route('user.list')->withError(trans('user.delete_fail'));
    }

}
