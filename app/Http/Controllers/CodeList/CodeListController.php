<?php 
namespace App\Http\Controllers\CodeList;

use App\Http\Controllers\Controller;
use Illuminate\Database\DatabaseManager;
use App\Nrgi\Services\CodeList\CodeListService;
use App\Http\Requests\CodeList\CodeListRequest;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

/**
 * Class CodeListController
 */
class CodeListController extends Controller
{
    /**
     * @var CodeListService
     */
    protected $codelist;
    /**
     * @var DatabaseManager
     */
    protected $db;
    /**
     * @var Guard
     */
    protected $auth;

    /**
     * CodeListController constructor
     *
     * @param CodeListService $codelist
     * @param DatabaseManager $db
     * @param Guard $auth
     */
    public function __construct(CodeListService $codelist, DatabaseManager $db, Guard $auth)
    {
        $this->middleware('auth');
        $this->codelist = $codelist;
        $this->db       = $db;
        $this->auth = $auth;

        if ($this->auth->user() && !$this->auth->user()->hasRole(['superadmin-editor'])) {

            return redirect('/home')->withError(trans('contract.permission_denied'))->send();
        }
    }

    /**
     * Renders codelist listing page
     *
     * @param Request $request
     * @param string $type
     * 
     * @return \Illuminate\View\View
     */
    public function index(Request $request, $type="contract_types")
    {
        $data = $this->codelist->all($type);

        return view('codelist.list', compact('data','type'));
    }

    /**
     * Renders create form
     *
     * @param $type
     * 
     * @return \Illuminate\View\View
     */
    public function create($type)
    {
        return view('codelist.create', compact('type'));
    }

    /**
     * Store document type, resource or contract type
     *
     * @param CodeListRequest $request
     * 
     * @return \Illuminate\Routing\Redirector
     */
    public function store(CodeListRequest $request)
    {
        $type = $request['type'];

        if($this->codelist->store($request->all('en' ,'ar' ,'fr', 'type'))) {

            return redirect()->route('codelist.list', ['type' => $type])->withSuccess(trans('codelist.'.$type.'_create_success'));
        }

        return redirect()->route('codelist.list', ['type' => $type])->withSuccess(trans('codelist.'.$type.'_create_fail'));
    }

    /**
     * Renders edit form
     *
     * @param $type
     * @param $id
     * 
     * @return \Illuminate\View\View
     */
    public function edit($type, $id)
    {
        $data = $this->codelist->find($type, $id);

        return view('codelist.edit', compact('data', 'type'));
    }

    /**
     * Update document type, resource or contract type
     *
     * @param CodeListRequest $request
     * @param $id
     * 
     * @return \Illuminate\Routing\Redirector
     */
    public function update(CodeListRequest $request, $id)
    {
        $type = $request['type'];

        if($this->codelist->update($id,$request->all('en' ,'ar' ,'fr', 'type'))){

            return redirect()->route('codelist.list',['type' => $type])->withSuccess(trans('codelist.'.$type.'_update_success'));
        }

        return redirect()->route('codelist.list',['type' => $type])->withSuccess(trans('codelist.'.$type.'_update_fail'));
    }

    /**
     * Delete User
     *
     * @param $id
     * 
     * @return \Illuminate\Routing\Redirector
     */
    public function delete($type, $id) 
    {
        if ($this->codelist->isNotUsed($type, $id)) {

            if ($this->codelist->delete($type, $id)) {

                return redirect()->route('codelist.list',['type' => $type])->withSuccess(trans('codelist.'.$type.'_delete_success'));
            }

            return redirect()->route('codelist.list', ['type' => $type])->withError(trans('codelist.'.$type.'_delete_fail'));

        }

        return redirect()->route('codelist.list', ['type' => $type])->withError(trans('codelist.'.$type.'_in_use'));
    }
}
