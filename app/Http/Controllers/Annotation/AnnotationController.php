<?php namespace app\Http\Controllers\Annotation;

use App\Http\Controllers\Controller;
use App\Nrgi\Services\Contract\AnnotationService;
use App\Nrgi\Services\Contract\ContractService;
use Illuminate\Auth\Guard;
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
        $this->contract   = $contract;
        $this->annotation = $annotation;
        $this->middleware('auth');
    }

    /**
     * annotations for Contract
     *
     * @param Request $request
     * @param String  $contractId
     * @return Response
     */
    public function index(Request $request, $contractId)
    {
        $page     = $request->input('page', '1');
        $contract = $this->contract->findWithPages($contractId);
        $pages    = $contract->pages;

        return view('annotations.create', compact('contract', 'pages', 'page'));
    }

    /**
     * @param Request $request
     * @param         $contractId
     */
    public function show(Request $request, $contractId)
    {
        try {
            $page     = $request->input('page', '1');
            $contract = $this->annotation->getContractPagesWithAnnotations($contractId);
            $status   = $this->annotation->getStatus($contractId);
            $pages    = $contract->pages;
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }

        return view('annotations.show', compact('contract', 'pages', 'page', 'status'));
    }

    /**
     * @param Guard   $auth
     * @param Request $request
     * @param         $contractId
     * @return Response
     */
    public function updateStatus(Guard $auth, Request $request, $contractId)
    {
        $status = trim(strtolower($request->input('state')));
        if (!$auth->user()->can(sprintf('%s-annotation', config('nrgi.permission')[$status]))) {
            return back()->withError('Permission denied.');
        }
        if ($this->annotation->updateStatus($status, $contractId)) {
            return back()->withSuccess(trans('annotation.status_updated_success'));
        }

        return back()->withError(trans('annotation.invalid_status'));
    }

    /**
     * @param Guard   $auth
     * @param Request $request
     * @param         $contractId
     * @return Response
     */
    public function comment(Guard $auth, Request $request, $contractId)
    {
        if (!$auth->user()->can('reject-annotation')) {
            return back()->withError('Permission denied.');
        }

        if ($this->annotation->comment($contractId, $request->input('message'))) {
            return back()->withSuccess(trans('annotation.comment_created_successfully'));
        }

        return back()->withError(trans('annotation.invalid_status'));

    }
}
