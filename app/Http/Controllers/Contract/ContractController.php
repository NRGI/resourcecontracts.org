<?php namespace App\Http\Controllers\Contract;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Requests\Contract\ContractRequest;
use App\Nrgi\Entities\Contract\Comment\Comment;
use App\Nrgi\Entities\Contract\Contract;
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
     * @param ContractService       $contract
     * @param ContractFilterService $contractFilter
     * @param CountryService        $countries
     * @param CommentService        $comment
     */
    public function __construct(
        ContractService $contract,
        ContractFilterService $contractFilter,
        CountryService $countries,
        CommentService $comment
    ) {
        $this->middleware('auth');
        $this->contract       = $contract;
        $this->countries      = $countries;
        $this->contractFilter = $contractFilter;
        $this->comment        = $comment;
    }

    /**
     * Display a listing of the Contracts.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $filters   = $request->only('resource', 'year', 'country');
        $contracts = $this->contractFilter->getAll($filters);
        $years     = $this->contractFilter->getUniqueYears();
        $countries = $this->contractFilter->getUniqueCountries();
        return view('contract.index', compact('contracts', 'years', 'countries'));
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
            return redirect()->route('contract.index')->withSuccess('Contract successfully uploaded.');
        }

        return redirect()->route('contract.index')->withError('Contract could not be saved.');
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
        $status      = $this->contract->getStatus($id);
        $annotations = $contract->annotations;
        $file        = $this->contract->getS3FileURL($contract->file);

        if ($contract->metadata_status == Contract::STATUS_REJECTED) {
            $contract->metadata_comment = $this->comment->getLatest($contract->id, Comment::TYPE_METADATA);
        }

        if ($contract->text_status == Contract::STATUS_REJECTED) {
            $contract->text_comment = $this->comment->getLatest($contract->id, Comment::TYPE_TEXT);
        }

        return view('contract.show', compact('contract', 'status', 'annotations', 'file'));
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
            return redirect()->route('contract.index')->withSuccess('Contract successfully updated.');
        }

        return redirect()->route('contract.index')->withError('Contract could not be updated.');
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
            return redirect()->route('contract.index')->withSuccess('Contract successfully deleted.');
        }

        return redirect()->route('contract.index')->withSuccess('Contract could not be deleted.');
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
                ['result' => 'success', 'type' => $contract->getTextType(), 'message' => 'Changes Saved.']
            );
        }

        return response()->json(['result' => 'error', 'message' => 'Could not be updated.']);
    }

    /**
     * Update contract status
     * @param         $contract_id
     * @param Request $request
     * @param Guard   $auth
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

        if ($auth->user()->hasRole('superadmin') || $auth->user()->can(sprintf('%s-metadata', $permission[$status]))) {
            if ($this->contract->updateStatus($contract_id, $status, $request->input('type'))) {
                return back()->withSuccess('Contract status successfully updated.');
            }

            return back()->withError('Invalid status');
        }

        return back()->withError('Permission denied.');
    }

    /**
     * Save Contract Comment
     * @param         $contract_id
     * @param Request $request
     * @param Guard   $auth
     * @return mixed
     */
    public function contractComment($contract_id, Request $request, Guard $auth)
    {
        if ($auth->user()->hasRole('superadmin') || $auth->user()->can('reject-metadata')) {
            if ($this->contract->updateStatusWithComment(
                $contract_id,
                Contract::STATUS_REJECTED,
                $request->input('message'),
                $request->input('type')
            )
            ) {
                return back()->withSuccess('Contract status successfully updated.');
            }

            return back()->withError('Invalid status');
        }

        return back()->withError('Permission denied.');
    }
}
