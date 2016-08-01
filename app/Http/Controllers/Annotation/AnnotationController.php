<?php namespace App\Http\Controllers\Annotation;

use App\Http\Controllers\Controller;
use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Services\Contract\Annotation\AnnotationService;
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

    /**
     * @param AnnotationService $annotation
     * @param ContractService   $contract
     */
    public function __construct(AnnotationService $annotation, ContractService $contract)
    {
        $this->middleware('auth');
        $this->contract   = $contract;
        $this->annotation = $annotation;
    }

    /**
     * Show Annotations
     *
     * @param Request $request
     * @param         $contractId
     * @return \Illuminate\View\View
     */
    public function show(Request $request, $contractId)
    {
        try {
            $page     = $request->input('page', '1');
            $contract = $this->annotation->getContractPagesWithAnnotations($contractId);
            $status   = $this->annotation->getStatus($contractId);
            $pages    = $contract->pages;
        } catch (\Exception $e) {
            return redirect()->back()->withError($e->getMessage());
        }

        return view('annotations.show', compact('contract', 'pages', 'page', 'status'));
    }

    /**
     * Update Annotation Status
     *
     * @param Guard   $auth
     * @param Request $request
     * @param         $contractId
     */
    public function updateStatus(Guard $auth, Request $request, $contractId)
    {
        $status = trim(strtolower($request->input('status')));
        if (!$auth->user()->can(sprintf('%s-annotation', config('nrgi.permission')[$status]))) {
            return redirect()->back()->withError('Permission denied.');
        }

        if ($this->annotation->comment($contractId, $request->input('message'), $request->input('status'))) {
            return redirect()->back()->withSuccess(trans('annotation.comment_created_successfully'));
        }

        return redirect()->back()->withError(trans('annotation.invalid_status'));
    }
}
