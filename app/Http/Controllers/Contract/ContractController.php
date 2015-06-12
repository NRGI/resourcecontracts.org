<?php namespace App\Http\Controllers\Contract;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Requests\Contract\ContractRequest;
use App\Nrgi\Services\Contract\AnnotationService;
use App\Nrgi\Services\Contract\ContractService;
use App\Nrgi\Services\Contract\CountryService;
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

    public function __construct(ContractService $contract, CountryService $countries)
    {
        $this->middleware('auth');
        $this->contract  = $contract;
        $this->countries = $countries;
    }

    /**
     * Display a listing of the Contracts.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $filters = $request->only('resource','year','country');
        $contracts = $this->contract->getAll($filters);

        return view('contract.index', compact('contracts'));
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
    public function show($id, AnnotationService $annotation)
    {
        $contract    = $this->contract->find($id);
        $status      = $this->contract->getStatus($id);
        $annotations = $annotation->getAllByContractId($id);
        $file        = $this->contract->getS3FileURL($contract->file);

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


}
