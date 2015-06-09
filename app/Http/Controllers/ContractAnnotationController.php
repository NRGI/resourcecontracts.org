<?php namespace app\Http\Controllers;

use App\Nrgi\Entities\Contract\Contract;

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
        $contract = Contract::with('pages')->find(4);
        return view('annotator.index',compact('contract'));
    }
}
