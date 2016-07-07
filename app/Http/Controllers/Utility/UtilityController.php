<?php namespace App\Http\Controllers\Utility;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Nrgi\Services\Contract\ContractService;
use Illuminate\Http\Request;

class UtilityController extends Controller
{

    /**
     * @var ContractService
     */
    protected $contractService;

    /**
     * @param ContractService $contractService
     */
    public function __construct(ContractService $contractService)
    {
        $this->contractService = $contractService;
    }

    /**
     * Display Rename form
     *
     * @param Request $request
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $filters   = $request->only('category', 'country');
        $confirm   = false;
        $contracts = [];

        if (!empty($filters['country']) || !empty($filters['category'])) {
            $contracts = $this->contractService->getContractRenameList($filters);
            $confirm   = true;
        }

        return view('utility.index', compact('confirm', 'contracts'));
    }

    /**
     * Update Contract Name
     *
     * @param Request $request
     *
     * @return \Illuminate\View\View
     */
    public function save(Request $request)
    {
        $contracts = $request->input('contracts');
        $contracts = json_decode($contracts);

        if (empty($contracts)) {
            return redirect()->route('utility.index')->with('error', 'Contracts not found');
        }

        $this->contractService->renameContracts($contracts);

        return redirect()->route('utility.index')->with('success', 'Contracts renamed successfully');
    }
}
