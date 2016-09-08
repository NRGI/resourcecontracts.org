<?php namespace App\Http\Controllers\ExternalApi;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExternalApiRequest;
use App\Nrgi\Entities\ExternalApi\ExternalApi;
use App\Nrgi\Services\Importer\ApiService;
use Illuminate\Http\Request;

/**
 * Class ExternalApiController
 * @package App\Http\Controllers\ExternalApi
 */
class ExternalApiController extends Controller
{
    /**
     * @var ExternalApi
     */
    protected $eApi;
    /**
     * @var ApiService
     */
    protected $importer;

    /**
     * ExternalApiController constructor.
     *
     * @param ExternalApi $eApi
     * @param ApiService  $importer
     */
    public function __construct(ExternalApi $eApi, ApiService $importer)
    {
        $this->middleware('auth');
        $this->eApi     = $eApi;
        $this->importer = $importer;
    }

    /**
     * List all Url
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $apis = $this->eApi->all();

        return view('externalapi.index', compact('apis'));
    }

    /**
     * Store api url in database
     *
     * @param ExternalApiRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ExternalApiRequest $request)
    {
        $create = $this->eApi->create($request->only('site', 'url'));
        if ($create) {
            return redirect()->back()->with('success', 'API url successfully added.');
        }

        return redirect()->back()->with('error', 'API url could not be added.');
    }

    /**
     * Delete Ap url
     *
     * @param $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $destroy = $this->eApi->destroy($id);

        if ($destroy) {
            return redirect()->back()->with('success', 'API url successfully deleted.');
        }

        return redirect()->back()->with('error', 'API url could not be deleted.');
    }

    /**
     * Remove index contracts
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function remove(Request $request)
    {
        $id = $request->only('id');

        if ($this->importer->remove($id)) {
            return redirect()->back()->with('success', trans('ea.remove_success'));
        }

        return redirect()->back()->with('error', trans('ea.remove_fail'));
    }

    /**
     * Index all contracts from source.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function indexAll(Request $request)
    {
        $id = $request->only('id');

        if ($this->importer->updateIndex($id, true)) {
            return redirect()->back()->with('success', trans('ea.update_success'));
        }

        return redirect()->back()->with('error', trans('ea.update_fail'));
    }

    /**
     * Update Index.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $id = $request->only('id');

        if ($this->importer->updateIndex($id)) {
            return redirect()->back()->with('success', trans('ea.update_success'));
        }

        return redirect()->back()->with('error', trans('ea.update_fail'));
    }

}
