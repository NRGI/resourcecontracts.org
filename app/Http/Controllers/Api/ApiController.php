<?php namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

/**
 * Class ApiController
 * @package App\Http\Controllers\Api
 */
class ApiController extends Controller
{
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var Guard
     */
    protected $auth;

    /**
     * ApiController constructor.
     *
     * @param Request $request
     * @param Guard   $auth
     */
    public function __construct(Request $request, Guard $auth)
    {
        $this->request = $request;
        $this->auth    = $auth;
    }

    /**
     * Login API
     *
     * @param username
     * @param password
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function login()
    {
        $credentials = [
            'email'    => $this->request->input('username'),
            'password' => $this->request->input('password'),
        ];

        $response = [
            'status'  => 'failed',
            'message' => 'Invalid username or password.',
        ];

        if ($this->auth->attempt($credentials, false, true)) {
            if ($this->auth->user()->status == 'false') {
                $response = [
                    'status'  => 'failed',
                    'message' => 'Account has not been activated',
                ];
            } else {
                $response = [
                    'status'  => 'success',
                    'message' => $this->auth->user()->toArray(),
                ];
            }
        }

        return response()->json($response);
    }
}
