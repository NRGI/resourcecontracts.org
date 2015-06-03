<?php namespace app\Http\Controllers;

/**
 * Class ContractAnnotationController
 * @package app\Http\Controllers
 */
class ContractAnnotationController extends Controller
{
    /**
     * Constructor
     * Create a new ContractAnnotationController instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a Demo page for annotatorjs.
     * @return Response
     */
    public function index()
    {
        return view('annotator.index');
    }
}
