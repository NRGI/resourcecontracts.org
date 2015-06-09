<?php namespace App\Http\Controllers\Contract\Page;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Nrgi\Services\Contract\ContractService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Class PageController
 * @package App\Http\Controllers\Contract\Page
 */
class PageController extends Controller
{
    /**
     * @var ContractService
     */
    protected $contract;

    /**
     * @param ContractService $contract
     */
    function __construct(ContractService $contract)
    {
        $this->contract = $contract;
    }

    /**
     * Display Contact Pages
     *
     * @return Response
     */
    public function index($id)
    {
        $contract = $this->contract->find($id);

        return view('contract.page.index', compact('contract'));
    }

    /**
     * Save Page text
     * @param         $id
     * @param Request $request
     * @return int
     */
    public function store($id, Request $request)
    {
        $this->contract->savePageText($id, $request->input('page'), $request->input('text'));

        return 1;
    }
}
