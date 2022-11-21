<?php namespace App\Http\Controllers\Contract\Import;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Requests\Contract\ImportRequest;
use App\Nrgi\Services\Contract\ContractService;
use App\Nrgi\Services\Contract\ImportService;
use Illuminate\Http\Request;
use Throwable;
use App\Nrgi\Services\Microsoft\MicrosoftService;

/**
 * Class ImportController
 * @package App\Http\Controllers\Contract\Import
 */
class ImportController extends Controller
{
    /**
     * @var ContractService
     */
    protected $contract;
    /**
     * @var ImportService
     */
    protected $contractImport;

    protected $microsoftService;

    /**
     * @param ContractService $contract
     * @param ImportService   $contractImport
     */
    public function __construct(ContractService $contract, ImportService $contractImport, MicrosoftService $microsoftService)
    {
        $this->middleware('auth');
        $this->contract       = $contract;
        $this->contractImport = $contractImport;
        $this->microsoftService = $microsoftService;
    }

    /**
     * Import Contracts
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        if($request->has('code')) {
            $this->microsoftService->getAccessToken($request->get('code'));
        }
        $jobs = $this->contractImport->getImportJobByUser();
        $is_one_drive_authenticated = $this->microsoftService->hasAuthenticatedToken();
        $one_drive_auth_link = $this->microsoftService->getAuthLink();
        
        return view('contract.import.index', compact('jobs', 'one_drive_auth_link', 'is_one_drive_authenticated'));
    }

    /**
     * Import Contracts
     *
     * @param ImportRequest $request
     * @return \Illuminate\View\View
     */
    public function importPost(ImportRequest $request)
    {
        try {
            if ($key = $this->contractImport->import($request)) {
                return redirect()->route('contract.import.confirm', ['key' => $key]);
            }
            $this->contractImport->deleteImportFolder($key);

            return redirect()->route('contract.import')->withError(trans('contract.import.fail'));
        } catch (Throwable $e) {
            return redirect()->route('contract.import')->withError($e->getMessage());
        }
    }

    /**
     * Import Confirm Page
     *
     * @param $import_key
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function confirm($import_key)
    {
        if ($json = $this->contractImport->getJsonData($import_key, false)) {

            if ($json->step == 2) {
                return redirect()->route('contract.import.status', ['key' => $import_key]);
            }

            $import_json = route('contract.import.notify', $import_key);
            $contracts   = $json->contracts;

            return view('contract.import.confirm', compact('contracts', 'import_key', 'import_json'));
        }

        return redirect()->route('contract.import');
    }

    /**
     * Import Contracts Post
     *
     * @param                       $key
     * @param Request               $request
     * @return \Illuminate\View\View
     */
    public function confirmPost($key, Request $request)
    {
        if ($this->contractImport->saveContracts($key, $request->input('id'))) {
            return redirect()->route('contract.import.status', ['key' => $key]);
        }

        return redirect()->route('contract.import')->withError(trans('contract.import.fail'));
    }

    /**
     * Import Status
     *
     * @param $import_key
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function status($import_key)
    {
        if ($json = $this->contractImport->getJsonData($import_key, false)) {

            if ($json->step == 1) {
                return redirect()->route('contract.import.confirm', ['key' => $import_key]);
            }

            $import_json = route('contract.import.notify', ['key' => $import_key]);
            $contracts   = $json->contracts;

            return view('contract.import.status', compact('contracts', 'import_key', 'import_json'));
        }

        return redirect()->route('contract.import');
    }

    /**
     * Delete Folder
     *
     * @param $key
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($key)
    {
        $this->contractImport->deleteImportFolder($key);

        return redirect()->route('contract.import');
    }

    /**
     * Notify url for import
     *
     * @param $key
     * @return string
     */
    public function notify($key)
    {
        $json = storage_path(sprintf('%s/%s/%s.json', ImportService::UPLOAD_FOLDER, $key, $key));

        if (file_exists($json)) {
            return file_get_contents($json);
        }

        abort(404, 'File not found');
    }
}
