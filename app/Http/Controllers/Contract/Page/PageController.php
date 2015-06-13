<?php namespace App\Http\Controllers\Contract\Page;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Nrgi\Services\Contract\ContractService;
use App\Nrgi\Services\Contract\Pages\PagesService;
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
     * @var PagesService
     */
    protected $pages;

    /**
     * @param ContractService $contract
     * @param PagesService    $pages
     */
    public function __construct(ContractService $contract, PagesService $pages)
    {
        $this->middleware('auth');
        $this->contract = $contract;
        $this->pages    = $pages;
    }

    /**
     * Display Contact Pages
     *
     * @return Response
     */
    public function index(Request $request, $contractId)
    {
        $page = $request->input('page', '1');
        $action = $request->input('action', '');
        $canEdit = $action=="edit"?'true':'false';
        $canAnnotate = $action=="annotate"?'true':'false';
        $contract = $this->contract->findWithPages($contractId);
        $pages = $contract->pages;

        return view('contract.page.index', compact('contract', 'pages', 'page', 'canEdit', 'canAnnotate'));
    }

    /**
     * Save Page text
     * @param         $id
     * @param Request $request
     * @return int
     */
    public function store($id, Request $request)
    {
        if ($this->pages->saveText($id, $request->input('page'), $request->input('text'))) {
            return response()->json(['result' => 'success', 'message' => 'saved']);
        }

        return response()->json(['result' => 'fail', 'message' => 'Something went wrong.']);
    }

    /**
     * Get Page Text
     * @param         $contractID
     * @param Request $request
     * @return mixed
     */
    public function getText($contractID, Request $request)
    {
        $page = $this->pages->getText($contractID, $request->input('page'));

        return response()->json(['result' => 'success', 'message' => $page->text]);
    }
}
