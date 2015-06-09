<?php namespace App\Http\Controllers\Contract;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Requests\Contract\ContractRequest;
use App\Nrgi\Services\Contract\ContractService;
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

    public function __construct(ContractService $contract)
    {
        $this->middleware('auth');
        $this->contract = $contract;
    }

    /**
     * Display a listing of the Contracts.
     *
     * @return Response
     */
    public function index()
    {
        $contracts = $this->contract->getAll();

        return view('contract.index', compact('contracts'));
    }

    /**
     * Display contract create form.
     *
     * @return Response
     */
    public function create()
    {
        return view('contract.create');
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
        $contract = $this->contract->find($id);
        $status   = $this->contract->getStatus($id);

        return view('contract.show', compact('contract', 'status'));
    }

    /**
     * Display contract edit form.
     *
     * @return Response
     */
    public function edit($id)
    {
        $contract = $this->contract->find($id);

        return view('contract.edit', compact('contract'));
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
}
