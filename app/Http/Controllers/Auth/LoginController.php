<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    /**
     * The user has been authenticated.
     *
     * Deactivated accounts (status = false) are logged out again immediately.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Nrgi\Entities\User\User  $user
     * @return \Illuminate\Http\RedirectResponse|null
     */
    protected function authenticated(Request $request, $user)
    {
        if ($user->status === false || $user->status === 'false' || $user->status === 0) {
            $this->guard()->logout();
            $request->session()->invalidate();

            return redirect('/login')
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'Account has not been activated']);
        }

        return null;
    }
}
