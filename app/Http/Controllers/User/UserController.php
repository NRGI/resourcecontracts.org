<?php namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserRequest;
use App\Nrgi\Services\Contract\CountryService;
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
     * @var CountryService
     */
    protected $countries;

    /**
     * @param UserService    $user
     * @param CountryService $countries
     * @param Guard          $auth
     *
     * @internal param CountryService $country
     */
    public function __construct(UserService $user, CountryService $countries, Guard $auth)
    {
        $this->middleware('auth');
        $this->user = $user;
        $this->auth = $auth;

        if ($this->auth->user() && !$this->auth->user()->hasRole(['superadmin', 'admin', 'country-admin'])) {
            return redirect('/home')->withError(trans('contract.permission_denied'))->send();
        }

        $this->countries = $countries;
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
        $roles       = $this->user->getAllRoles();
        $country     = $this->countries->all();
        $permissions = $this->user->getPermissionsList();

        return view('user.create', compact('roles', 'country', 'permissions'));
    }

    /**
     * Register user
     *
     * @param UserRequest $request
     *
     * @return \Illuminate\Routing\Redirector
     */
    public function store(UserRequest $request)
    {
        $user_detail = $request->only('name', 'email', 'password', 'organization', 'status', 'country');
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
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $roles   = $this->user->getAllRoles();
        $country = $this->countries->all();
        $user    = $this->user->find($id);

        return view('user.edit', compact('roles', 'user', 'country'));
    }

    /**
     * Update User
     *
     * @param UserRequest $request
     * @param             $id
     *
     * @return \Illuminate\Routing\Redirector
     */
    public function update(UserRequest $request, $id)
    {
        $user_detail = $request->only('name', 'email', 'password', 'organization', 'status', 'country');
        $role        = $request->input('role');

        if ($this->user->update($id, $user_detail, $role)) {
            return redirect()->route('user.list')->withSuccess(trans('user.update_success'));
        }

        return redirect()->route('user.list')->withError(trans('user.update_fail'));
    }

    /**
     * Delete User
     *
     * @param $id
     *
     * @return \Illuminate\Routing\Redirector
     */
    public function delete($id)
    {
        if ($this->user->hasNoActivity($id)) {

            if ($this->user->delete($id)) {
                return redirect()->route('user.list')->withSuccess(trans('user.delete_success'));
            }

            return redirect()->route('user.list')->withError(trans('user.delete_fail'));
        }

        $user = $this->user->find($id);

        return view('user.delete_confirm', compact('user'));
    }

    /**
     * Deactivate User
     *
     * @return \Illuminate\Routing\Redirector
     */
    public function deactivate($id)
    {
        if ($this->user->update($id, ['status' => 'false'], null)) {
            return redirect()->route('user.list')->withSuccess(trans('user.deactivate_success'));
        }

        return redirect()->route('user.list')->withError(trans('user.deactivate_fail'));
    }

}

