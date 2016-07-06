<?php namespace App\Http\Controllers\Utility;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Nrgi\Services\Contract\ContractService;
use Illuminate\Http\Request;

class UtilityController extends Controller {


	/**
	 * @var ContractService
     */
	protected $contractService;

	/**
	 * write brief description
	 * @param ContractService $contractService
     */
	public function __construct(ContractService $contractService)
	{
		$this->contractService = $contractService;
	}

	/**
	 * get cntracts from provided filters
	 *
	 * @param Request $request
	 * @return \Illuminate\View\View
     */
	public function index(Request $request)
	{
		$filters = $request->only('category','country');
		$renameContracts = $this->contractService->renameContract($filters);

		return view('utility.index',compact('renameContracts'));
	}

	public function save(Request $request)
	{
		$contracts = $request->only('con');
		$this->contractService->findAndUpdateContract($contracts);

		return view('utility.index');


	}

}
