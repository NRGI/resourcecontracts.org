<?php namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use App\Nrgi\Services\CodeList\CodeListService;

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
     * @var CodeListService
     */
    protected $codelist;

    /**
     * ApiController constructor.
     *
     * @param Request $request
     * @param Guard   $auth
     */
    public function __construct(Request $request, Guard $auth, CodeListService $codelist)
    {
        $this->request  = $request;
        $this->auth     = $auth;
        $this->codelist = $codelist;
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
                    'status'   => 'failed',
                    'message'  => 'Account has not been activated',
                    'is_admin' => false,
                ];
            } else {
                $response = [
                    'status'   => 'success',
                    'message'  => $this->auth->user()->toArray(),
                    'is_admin' => $this->auth->user()->isAdmin(),
                ];
            }
        }

        return response()->json($response);
    }

    /**
     * CodeList API
     *
     * @param $lang
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getCodeList($lang)
    {
        if($lang){
            $codelist['resources']      = $this->codelist->getCodeList('resources',$lang);
            $codelist['document_types'] = $this->codelist->getCodeList('document_types',$lang);
            $codelist['contract_types'] = $this->codelist->getCodeList('contract_types',$lang);
    
            $response = [
                'status'   => 'success',
                'message'  => $codelist
            ];
        } else {
            $response = [
                'status'   => 'failed',
                'message'  => 'Language is required'
            ];
        }

        return response()->json($response);
    }
}
