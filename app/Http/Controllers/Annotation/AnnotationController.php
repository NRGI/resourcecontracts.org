<?php namespace app\Http\Controllers\Annotation;

use App\Http\Controllers\Controller;
use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Services\Contract\AnnotationService;
use Illuminate\Http\Request;

/**
 * Class AnnotationController
 * @package app\Http\Controllers
 */
class AnnotationController extends Controller
{
    /**
     * @var AnnotationService
     */
    protected $annotation;

    /**
     * @var Contract
     */
    protected $contract;

    /**S
     * Constructor
     * Create a new ContractAnnotationController instance.
     */
    public function __construct(AnnotationService $annotation, Contract $contract)
    {
        $this->contract = $contract;
        $this->annotation = $annotation;
        $this->middleware('auth');
    }

    /**
     * list of annotaions of Contract
     * @param String $contractId
     * @return Response
     */
    public function index($contractId)
    {
        $annotations = $this->annotation->getAllByContractId($contractId);

        return view('annotations.list', compact('annotations'));
    }

    /**
     * @param         $contractId
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function create($contractId ,Request $request)
    {
        $page = $request->input('page', '1');
        $contract = Contract::with('pages')->find($contractId);
        $pages = $contract->pages;

        return view('annotations.create', compact('contract', 'pages', 'page'));
    }
}
