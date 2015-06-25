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
     */
    public function create()
    {
        $roles = $this->user->getAllRoles();

        return view('user.create', compact('roles'));
    }

    /**
     * Register user
     * @param UserRequest $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(UserRequest $request)
    {
        $user_detail = $request->only('name', 'email', 'password', 'organization', 'status');
        $role        = $request->input('role');

        if ($this->user->create($user_detail, $role)) {
            return redirect()->route('user.list')->withSuccess('User successfully created.');
        }

        return redirect()->route('user.create')->withSuccess('User could not be created.');
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
     * @param $id
     */
    public function update(UserRequest $request, $id)
    {
        $user_detail = $request->only('name', 'email', 'password', 'organization', 'status');
        $role        = $request->input('role');

        if ($this->user->update($id, $user_detail, $role)) {
            return redirect()->route('user.list')->withSuccess('User successfully updated.');
        }

        return redirect()->route('user.create')->withSuccess('User could not be updated.');
    }

    /**
     * Delete User
     *
     * @param $id
     */
    public function delete($id)
    {
        if ($this->user->delete($id)) {
            return redirect()->route('user.list')->withSuccess('User successfully deleted.');
        }

        return redirect()->route('user.create')->withSuccess('User could not be deleted.');
    }

}
