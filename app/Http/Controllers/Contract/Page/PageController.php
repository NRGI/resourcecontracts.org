<?php namespace App\Http\Controllers\Contract\Page;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Services\Contract\ContractService;
use Illuminate\Filesystem\Filesystem;
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
    public function index($id, Filesystem $filesystem)
    {
        $contract    = $this->contract->find($id);
        $files       = $filesystem->files(ContractService::UPLOAD_FOLDER . '/' . $id . '/pages');
        $page_number = count($files);

        return view('contract.page.index', compact('contract', 'page_number'));
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
