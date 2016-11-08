<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Nrgi\Services\User\UserService;
use Illuminate\Auth\Guard as Auth;
use Illuminate\Http\Request;

/**
 * Class ProfilesController
 * @package App\Http\Controllers\User
 */
class ProfilesController extends Controller
{

    /**
     * @var UserService
     */
    private $user;
    /**
     * @var Auth
     */
    private $auth;


    /**
     * @param UserService $user
     * @param Auth        $auth
     */
    public function __construct(UserService $user, Auth $auth)
    {
        $this->middleware('auth');
        $this->user = $user;
        $this->auth = $auth;
    }

    /**
     * Loads the profile page
     * @return \Illuminate\View\View
     */
    public function profile()
    {
        $userDetails = $this->auth->user();

        return view('user.profile', compact('userDetails'));
    }

    /**
     * Loads the edit profile page
     * @return \Illuminate\View\View
     */
    public function editProfile()
    {
        $userDetails = $this->auth->user();

        return view('user.editProfile', compact('userDetails'));

    }


    /**
     * write brief description
     * @param UpdateProfileRequest $request
     * @return mixed
     */
    public function updateUser(UpdateProfileRequest $request)
    {

        $user_detail = $request->only('name', 'organization', 'password');

        if ($this->user->update($this->auth->id(), $user_detail, null)) {
            return redirect()->route('user.profile')->withSuccess(trans('user.update_success'));
        }

        return redirect()->route('user.list')->withError(trans('user.update_fail'));
    }

}

