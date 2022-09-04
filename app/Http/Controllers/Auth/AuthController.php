<?php namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Nrgi\Entities\User\User;
use Auth;
use Validator;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Http\Request;

class AuthController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers;


    /**
     * Create a new authentication controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth', ['only' => 'getRegister']);
        $this->middleware('guest', ['except' => 'getLogout']);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function postLogin(Request $request)
    {
        $this->validate(
            $request,
            [
                'email'    => 'required|email',
                'password' => 'required',
            ]
        );

        $credentials = $request->all('email', 'password');

        if (Auth::attempt($credentials, $request->has('remember'))) {
            if (Auth::user()->status == 'false') {
                Auth::logout();

                return redirect($this->loginPath())
                    ->withInput($request->all('email', 'remember'))
                    ->withErrors(
                        [
                            'email' => 'Account has not been activated',
                        ]
                    );
            }

            return redirect()->intended($this->redirectPath());
        }

        return redirect($this->loginPath())
            ->withInput($request->all('email', 'remember'))
            ->withErrors(
                [
                    'email' => $this->getFailedLoginMessage(),
                ]
            );
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    public function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }
}
