<?php namespace app\Http\Controllers\Annotation;

use App\Http\Controllers\Controller;
use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Services\Contract\AnnotationService;

/**
 * Class ContractAnnotationController
 * @package app\Http\Controllers
 */
class AnnotationController extends Controller
{
    protected $annotation;
    protected $contract;
    /**
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
}
