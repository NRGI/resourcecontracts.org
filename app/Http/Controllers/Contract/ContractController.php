<?php namespace App\Http\Controllers\Contract;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Requests\Contract\ContractRequest;
use App\Nrgi\Entities\Contract\Annotation;
use App\Nrgi\Entities\Contract\Comment\Comment;
use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Services\Contract\AnnotationService;
use App\Nrgi\Services\Contract\Comment\CommentService;
use App\Nrgi\Services\Contract\ContractFilterService;
use App\Nrgi\Services\Contract\ContractService;
use App\Nrgi\Services\Contract\CountryService;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Class ContractController
 * @package App\Http\Controllers\Contract
 */
class ContractController extends Controller
{
    /**
     * @var ContractService
     */
    protected $contract;
    /**
     * @var CountryService
     */
    protected $countries;
    /**
     * @var ContractFilterService
     */
    protected $contractFilter;
    /**
     * @var CommentService
     */
    protected $comment;
    /**
     * @var AnnotationService
     */
    protected $annotation;

    /**
     * @param ContractService $contract
     * @param ContractFilterService $contractFilter
     * @param CountryService $countries
     * @param CommentService $comment
     * @param AnnotationService $annotation
     */
    public function __construct(
        ContractService $contract,
        ContractFilterService $contractFilter,
        CountryService $countries,
        CommentService $comment,
        AnnotationService $annotation
    ) {
        $this->middleware('auth');
        $this->contract       = $contract;
        $this->countries      = $countries;
        $this->contractFilter = $contractFilter;
        $this->comment        = $comment;
        $this->annotation     = $annotation;
    }

    /**
     * Display a listing of the Contracts.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $filters   = $request->only('resource', 'year', 'country', 'category', 'resource');
        $contracts = $this->contractFilter->getAll($filters);
        $years     = $this->contractFilter->getUniqueYears();
        $countries = $this->contractFilter->getUniqueCountries();
        $resources = $this->contractFilter->getUniqueResources();

        return view('contract.index', compact('contracts', 'years', 'countries', 'resources'));
    }

    /**
     * Display contract create form.
     *
     * @return Response
     */
    public function create()
    {
        $country = $this->countries->all();

        return view('contract.create', compact('country'));
    }

    /**
     * Store a newly created contract.
     *
     * @param ContractRequest $request
     * @return Response
     */
    public function store(ContractRequest $request)
    {
        if ($this->contract->saveContract($request->all())) {
            return redirect()->route('contract.index')->withSuccess(trans('contract.save_success'));
        }

        return redirect()->route('contract.index')->withError(trans('contract.save_fail'));
    }

    /**
     * Display specified contract
     *
     * @return Response
     */
    public function show($id)
    {
        $contract = $this->contract->findWithAnnotations($id);
        if (!$contract) {
            abort('404');
        }
        $status                       = $this->contract->getStatus($id);
        $annotationStatus             = $this->annotation->getStatus($id);
        $annotations                  = $contract->annotations;
        $contract->metadata_comment   = $this->comment->getLatest($contract->id, Comment::TYPE_METADATA);
        $contract->text_comment       = $this->comment->getLatest($contract->id, Comment::TYPE_TEXT);
        $contract->annotation_comment = $this->comment->getLatest($contract->id, Comment::TYPE_ANNOTATION);

        return view('contract.show', compact('contract', 'status', 'annotations', 'annotationStatus'));
    }

    /**
     * Display contract edit form.
     *
     * @return Response
     */
    public function edit($id)
    {
        $contract = $this->contract->find($id);
        $country  = $this->countries->all();

        return view('contract.edit', compact('contract', 'country'));
    }

    /**
     * Update contract Detail
     *
     * @param ContractRequest $request
     * @param                 $contractID
     * @return Response
     */
    public function update(ContractRequest $request, $contractID)
    {
        if ($this->contract->updateContract($contractID, $request->all())) {
            return redirect()->route('contract.index')->withSuccess(trans('contract.update_success'));
        }

        return redirect()->route('contract.index')->withError(trans('contract.update_fail'));
    }

    /**
     * Remove the specified contract
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        if ($this->contract->deleteContract($id)) {
            return redirect()->route('contract.index')->withSuccess(trans('contract.delete_success'));
        }

        return redirect()->route('contract.index')->withSuccess(trans('contract.delete_fail'));
    }

    /**
     * Save output
     *
     * @param         $id
     * @param Request $request
     * @return Response
     */
    public function saveOutputType($id, Request $request)
    {
        $type     = $request->input('text_type');
        $contract = $this->contract->saveTextType($id, $type);

        if ($contract) {
            return response()->json(
                ['result' => 'success', 'type' => $contract->getTextType(), 'message' => trans('contract.saved')]
            );
        }

        return response()->json(['result' => 'error', 'message' => trans('contract.not_updated')]);
    }

    /**
     * Update contract status
     * @param         $contract_id
     * @param Request $request
     * @param Guard $auth
     * @return Response
     */
    public function updateStatus($contract_id, Request $request, Guard $auth)
    {
        $status     = trim(strtolower($request->input('state')));
        $permission = [
            'completed' => 'complete',
            'rejected'  => 'reject',
            'published' => 'publish'
        ];

        if (!($auth->user()->can(sprintf('%s-metadata', $permission[$status])))) {
            return back()->withError(trans('contract.permission_denied'));
        }

        if ($this->contract->updateStatus($contract_id, $status, $request->input('type'))) {
            return back()->withSuccess(trans('contract.status_update'));
        }

        return back()->withError(trans('contract.invalid_status'));
    }

    /**
     * Save Contract Comment
     * @param         $contract_id
     * @param Request $request
     * @param Guard $auth
     * @return mixed
     */
    public function contractComment($contract_id, Request $request, Guard $auth)
    {
        $status = $request->get('status');

        if (!$auth->user()->can(sprintf('%s-%s', config('nrgi.permission')[$status], $request->get('type')))) {
            return back()->withError('Permission denied.');
        }

        if ($this->contract->updateStatusWithComment(
            $contract_id,
            $status,
            $request->input('message'),
            $request->input('type')
        )
        ) {
            return back()->withSuccess(trans('contract.status_update'));
        }

        return back()->withError(trans('contract.invalid_status'));
    }

    /**
     * Get Metadata by contract ID
     *
     * @param $contract_id
     * @return Contract
     */
    public function getMetadata($contract_id)
    {
        if ($contract = $this->contract->find($contract_id)) {
            return response()->json($contract->metadata);
        }

        return abort(404);
    }

}
