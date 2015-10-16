<?php namespace App\Http\Controllers\Contract\Import;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Requests\Contract\ImportRequest;
use App\Nrgi\Services\Contract\ContractService;
use App\Nrgi\Services\Contract\ImportService;
use Illuminate\Http\Request;

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

    /**
     * @param ContractService $contract
     * @param ImportService   $contractImport
     */
    public function __construct(ContractService $contract, ImportService $contractImport)
    {
        $this->middleware('auth');
        $this->contract       = $contract;
        $this->contractImport = $contractImport;
    }

    /**
     * Import Contracts
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $jobs = $this->contractImport->getImportJobByUser();

        return view('contract.import.index', compact('jobs'));
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
                return redirect()->route('contract.import.confirm', $key);
            }
            $this->contractImport->deleteImportFolder($key);

            return redirect()->route('contract.import')->withError(trans('contract.import.fail'));
        } catch (\Exception $e) {
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
                return redirect()->route('contract.import.status', $import_key);
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
            return redirect()->route('contract.import.status', $key);
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
                return redirect()->route('contract.import.confirm', $import_key);
            }

            $import_json = route('contract.import.notify', $import_key);
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
