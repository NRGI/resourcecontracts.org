<?php namespace app\Http\Controllers\Annotation;

use App\Http\Controllers\Controller;
use App\Nrgi\Services\Contract\AnnotationService;
use App\Nrgi\Services\Contract\ContractService;
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
    public function __construct(AnnotationService $annotation, ContractService $contract)
    {
        $this->contract = $contract;
        $this->annotation = $annotation;
        $this->middleware('auth');
    }

    /**
     * annotations for Contract
     *
     * @param Request $request
     * @param String $contractId
     * @return Response
     */
    public function index(Request $request, $contractId)
    {
        $page = $request->input('page', '1');
        $contract = $this->contract->findWithPages($contractId);
        $pages = $contract->pages;

        return view('annotations.create', compact('contract', 'pages', 'page'));
    }
}
