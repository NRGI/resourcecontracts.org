<?php namespace App\Http\Controllers\Contract\Page;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Nrgi\Entities\Contract\Annotation;
use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Entities\Contract\Pages\Pages;
use App\Nrgi\Services\Contract\ContractService;
use App\Nrgi\Services\Contract\AnnotationService;
use App\Nrgi\Services\Contract\Pages\PagesService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * Class PageController
 * @package App\Http\Controllers\Contract\Page
 */
class PageController extends Controller
{
    /**
     * @var AnnotationService
     */
    protected $annotation;
    /**
     * @var ContractService
     */
    protected $contract;
    /**
     * @var PagesService
     */
    protected $pages;

    /**
     * @param ContractService   $contract
     * @param PagesService      $pages
     * @param AnnotationService $annotation
     */
    public function __construct(ContractService $contract, PagesService $pages, AnnotationService $annotation)
    {
        $this->middleware('auth');
        $this->contract   = $contract;
        $this->pages      = $pages;
        $this->annotation = $annotation;
    }

    /**
     * Display Contact Pages
     *
     * @return Response
     */
    public function index(Request $request, $contractId)
    {
        try {
            $page        = $this->pages->getText($contractId, $request->input('page', '1'));
            $action      = $request->input('action', '');
            $canEdit     = $action == "edit" ? 'true' : 'false';
            $canAnnotate = $action == "annotate" ? 'true' : 'false';
            $contract    = $this->contract->findWithPages($contractId);
            $pages       = $contract->pages;
        } catch (\Exception $e) {

            return abort(404);
        }

        return view('contract.page.index', compact('contract', 'pages', 'page', 'canEdit', 'canAnnotate'));
    }

    public function compare(Request $request, $contractId1, $contractId2)
    {
        $page        = $this->pages->getText($contractId1, $request->input('page', '1'));
        $action      = $request->input('action', '');
        $canEdit     = $action == "edit" ? 'true' : 'false';
        $canAnnotate = $action == "annotate" ? 'true' : 'false';
        $contract    = $this->contract->findWithPages($contractId1);
        $pages       = $contract->pages;

        $contract1Meta           = $this->contract->findWithPages($contractId1);
        $contract1AnnotationsObj = $this->annotation->getContractPagesWithAnnotations($contractId1);
        $contract1Annotations    = [];
        foreach ($contract1AnnotationsObj->annotations as $annotation) {
            $tags = [];
            foreach ($annotation->annotation->tags as $tag) {
                $tags[] = $tag;
            }
            $contract1Annotations[] = [
                'page'  => $annotation->document_page_no,
                'quote' => $annotation->annotation->quote,
                'text'  => $annotation->annotation->text,
                'tags'  => $tags
            ];
        }

        $contract2Meta           = $this->contract->findWithPages($contractId2);
        $contract2AnnotationsObj = $this->annotation->getContractPagesWithAnnotations($contractId2);
        $contract2Annotations    = [];
        foreach ($contract2AnnotationsObj->annotations as $annotation) {
            $tags = [];
            foreach ($annotation->annotation->tags as $tag) {
                $tags[] = $tag;
            }
            $contract2Annotations[] = [
                'page'  => $annotation->document_page_no,
                'quote' => $annotation->annotation->quote,
                'text'  => $annotation->annotation->text,
                'tags'  => $tags
            ];
        }

        $contract1 = [
            'metadata'    => $contract1Meta,
            'pages'       => $contract1Meta->pages,
            'annotations' => $contract1Annotations
        ];
        $contract2 = [
            'metadata'    => $contract2Meta,
            'pages'       => $contract2Meta->pages,
            'annotations' => $contract2Annotations
        ];

        return view(
            'contract.page.compare',
            compact('contract', 'contract1', 'contract2', 'pages', 'page', 'canEdit', 'canAnnotate')
        );
    }

    /**
     * Save Page text
     * @param         $id
     * @param Request $request
     * @return int
     */
    public function store($id, Request $request, ContractService $contract)
    {
        if ($this->pages->saveText($id, $request->input('page'), $request->input('text'))) {
            $contract              = $contract->find($id);
            $contract->text_status = Contract::STATUS_DRAFT;
            $contract->save();

            return response()->json(['result' => 'success', 'message' => trans('contract.page.save')]);
        }

        return response()->json(['result' => 'fail', 'message' => trans('contract.page.save_fail')]);
    }

    /**
     * Get Page Text
     * @param         $contractID
     * @param Request $request
     * @return mixed
     */
    public function getText($contractID, Request $request)
    {
        $page_no = $request->input('page');
        $page    = $this->pages->getText($contractID, $page_no);

        return response()->json(
            [
                'result'  => 'success',
                'id'      => $page->id,
                'pdf'     => $page->pdf_url,
                'message' => $page->text
            ]
        );
    }

    public function getAllText($contractID)
    {
        $pages = $this->pages->getAllText($contractID);

        return response()->json(
            [
                'result' => $pages
            ]
        );
    }

    /**
     * Display Contact Pages
     *
     * @return Response
     */
    public function annotate(Request $request, $contractId)
    {
        try {
            $page        = $this->pages->getText($contractId, $request->input('page', '1'));
            $action      = $request->input('action', '');
            $canEdit     = $action == "edit" ? 'true' : 'false';
            $canAnnotate = $action == "annotate" ? 'true' : 'false';
            $contract    = $this->contract->findWithPages($contractId);
            $pages       = $contract->pages;
        } catch (\Exception $e) {

            return abort(404);
        }

        return view('contract.page.annotate', compact('contract', 'pages', 'page', 'canEdit', 'canAnnotate'));
    }

    public function review(Request $request, $contractId)
    {
        try {
            $page     = $this->pages->getText($contractId, $request->input('page', '1'));
            $action   = $request->input('action', '');
            $canEdit  = $action == "edit" ? 'true' : 'false';
            $contract = $this->contract->findWithPages($contractId);
            $pages    = $contract->pages;
        } catch (\Exception $e) {

            return abort(404);
        }

        return view('contract.page.review', compact('contract', 'pages', 'page', 'canEdit', 'canAnnotate'));
    }

    public function annotatenew(Request $request, $contractId)
    {

        try {
            $back        = \Request::server('HTTP_REFERER');
            $page        = $this->pages->getText($contractId, $request->input('page', '1'));
            $action      = $request->input('action', '');
            $canEdit     = $action == "edit" ? 'true' : 'false';
            $canAnnotate = $action == "annotate" ? 'true' : 'false';
            $contract    = $this->contract->findWithPages($contractId);
            $pages       = $contract->pages;
        } catch (\Exception $e) {

            return abort(404);
        }

        return view('contract.page.annotatenew', compact('contract', 'pages', 'page', 'canEdit', 'canAnnotate', 'back'));
    }

    public function reviewnew(Request $request, $contractId)
    {
        try {
            $back     = \Request::server('HTTP_REFERER');
            $page     = $this->pages->getText($contractId, $request->input('page', '1'));
            $contract = $this->contract->findWithPages($contractId);
            $pages    = $contract->pages;
        } catch (\Exception $e) {

            return abort(404);
        }

        return view('contract.page.reviewnew', compact('contract', 'pages', 'page', 'back'));
    }

    /**
     * Full text search
     * @param         $contract_id
     * @param Request $request
     * @return array
     */
    public function search($contract_id, Request $request)
    {
        return response()->json(
            [
                'results' => $this->pages->fullTextSearch($contract_id, $request->input('q'))
            ]
        );
    }


    public function rewriteAnnotate($id)
    {
        $contract = Contract::with('annotations')->where('id', $id)->first();

        return view('contract.page.rewrite_annotate_final', compact('contract'));
    }

    /**
     * write brief description
     * @param         $id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postAnnotation($id, Request $request)
    {
        $data = [
            'text'        => $request->input('text'),
            'user_id'     => Auth::id(),
            'contract_id' => $id,
            'category'    => $request->input('category'),
            'annotation'  => json_encode($request->input('annotation')),
        ];

        Annotation::create($data);

        return redirect()->route('contract.annotate.new', $id);
    }

}
