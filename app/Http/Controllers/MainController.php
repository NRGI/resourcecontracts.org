<?php namespace App\Http\Controllers;

use Illuminate\Http\Response;

/**
 * Class MainController
 * @package App\Http\Controllers
 */
class MainController extends Controller
{
    /**
     * Create a new controller instance.
     *
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Welcome Page.
     *
     * @return Response
     */
    public function index()
    {
        return view('home');
    }
}
