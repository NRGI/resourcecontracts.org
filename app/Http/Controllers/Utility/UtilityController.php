<?php namespace App\Http\Controllers\Utility;

use App\Http\Controllers\Controller;
use App\Nrgi\Services\Contract\ContractService;
use App\Nrgi\Services\Contract\CountryService;
use Illuminate\Auth\Guard;
use Illuminate\Http\Request;


/**
 * Class UtilityController
 * @package App\Http\Controllers\Utility
 */
class UtilityController extends Controller
{

    /**
     * @var ContractService
     */
    protected $contractService;

    /**
     * @var CountryService
     */
    protected $countryService;

    /**
     * @param ContractService $contractService
     * @param CountryService  $countryService
     */
    public function __construct(ContractService $contractService, CountryService $countryService)
    {
        $this->contractService = $contractService;
        $this->countryService  = $countryService;
    }

    /**
     * Display Rename form
     *
     * @param Request $request
     *
     * @param Guard   $auth
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request, Guard $auth)
    {   if(!($auth->user()->isAdmin())) {
            return back()->withErrors(trans('contract.permission_denied'));
        }
        $filters   = $request->only('category', 'country');
        $country   =  $this->countryService->all();
        $confirm   = false;
        $contracts = [];

        if (!empty($filters['country']) || !empty($filters['category'])) {
            $contracts = $this->contractService->getContractRenameList($filters);
            $confirm   = true;
        }

        return view('utility.index', compact('confirm', 'contracts','country'));
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
            return redirect()->route('utility.index')->with('error', trans('contract.contract_not_found'));
        }

        $this->contractService->renameContracts($contracts);

        return redirect()->route('utility.index')->with('success', trans('contract.rename_success_message'));
    }
    /**
     * Bulk text download
     */
    public function bulkTextDownload()
    {

        $this->contractService->bulkTextDownload();
    }
}
