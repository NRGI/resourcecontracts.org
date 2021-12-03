<?php namespace App\Http\Controllers\Contract;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contract\ContractRequest;
use App\Nrgi\Entities\Contract\Comment\Comment;
use App\Nrgi\Mturk\Services\ActivityService;
use App\Nrgi\Services\Contract\Annotation\AnnotationService;
use App\Nrgi\Services\Contract\Comment\CommentService;
use App\Nrgi\Services\Contract\ContractFilterService;
use App\Nrgi\Services\Contract\ContractService;
use App\Nrgi\Services\Contract\CountryService;
use App\Nrgi\Services\Contract\Discussion\DiscussionService;
use App\Nrgi\Services\Download\DownloadService;
use App\Nrgi\Services\Language\LanguageService;
use App\Nrgi\Services\CodeList\CodeListService;
use GuzzleHttp\Client;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Contracts\Logging\Log;

/**
 * Class ContractController
 * @property DownloadService downloadService
 * @package App\Http\Controllers\Contract
 */
class ContractController extends Controller
{
    /**
     * @var ActivityService
     */
    protected $activity;
    /**
     * @var ContractService
     */
    protected $contract;
    /**
     * @var CountryService
     */
    protected $countries;
    /**
     * @var ContractFilterService
     */
    protected $contractFilter;
    /**
     * @var CommentService
     */
    protected $comment;
    /**
     * @var Log
     */
    protected $logger;
    /**
     * @var AnnotationService
     */
    protected $annotation;
    /**
     * @var CodeListService
     */
    protected $codeList;
    /**
     * @var DatabaseManager
     */
    private $db;
    /**
     * @var Client
     */
    private $http;

    /**
     * @param ContractService       $contract
     * @param ContractFilterService $contractFilter
     * @param CountryService        $countries
     * @param CommentService        $comment
     * @param AnnotationService     $annotation
     * @param DownloadService       $downloadService
     * @param ActivityService       $activity
     * @param CodeListService       $codeList
     * @param DatabaseManager       $db
     * @param Client                $http
     * @param Log                   $logger
     */
    public function __construct(
        ContractService $contract,
        ContractFilterService $contractFilter,
        CountryService $countries,
        CommentService $comment,
        AnnotationService $annotation,
        DownloadService $downloadService,
        ActivityService $activity,
        CodeListService $codeList,
        DatabaseManager $db,
        Log $logger,
        Client $http
    ) {
        $this->middleware('auth');
        $this->contract        = $contract;
        $this->countries       = $countries;
        $this->contractFilter  = $contractFilter;
        $this->comment         = $comment;
        $this->annotation      = $annotation;
        $this->downloadService = $downloadService;
        $this->activity        = $activity;
        $this->codeList        = $codeList;
        $this->db              = $db;
        $this->http            = $http;
        $this->logger          = $logger;
    }

    /**
     * Display a listing of the Contracts.
     *
     * @param Request $request
     * @param LanguageService $lang
     *
     * @return \Illuminate\Foundation\Application|\Illuminate\View\View
     */
    public function index(Request $request, LanguageService $lang)
    {
         $filters        = $request->only(
             'resource',
             'year',
             'country',
             'category',
             'resource',
             'document_type',
             'type_of_contract',
             'company_name',
             'language',
             'type',
             'word',
             'issue',
             'status',
             'download',
             'disclosure',
             'q'
         );
         $contracts      = $this->contractFilter->getAll($filters);
         $years          = $this->contractFilter->getUniqueYears();
         $countries      = $this->contractFilter->getUniqueCountries();
         $resources      = $this->contractFilter->getUniqueResources();
         $download_files = $this->contract->getDownloadTextFiles();
         $resourceList   = $this->codeList->getCodeList('resources',$lang->getSiteLang());
         $locale           = $lang->defaultLang();
         $companyNamesListRaw = $this->contract->getCompanyNames();
         foreach($companyNamesListRaw as $company){
             $companyNamesList[$company] = $company;
         }
         $contractTypeList = $this->codeList->getCodeList('contract_types',$lang->getSiteLang());
         $documentTypeList = $this->codeList->getCodeList('document_types',$lang->getSiteLang());

         return view('contract.index', compact('contracts', 'years', 'countries', 'resources', 'download_files', 'resourceList','companyNamesList', 'contractTypeList','documentTypeList', 'locale' ));
    }

    /**
     * Display contract create form.
     *
     * @param Request         $request
     * @param LanguageService $lang
     *
     * @return Response
     */
    public function create(Request $request, LanguageService $lang)
    {
        $country          = $this->countries->all();
        $contracts        = $this->contract->parentContracts();
        $contract         = !is_null($request->get('parent')) ? $this->contract->find($request->get('parent')) : [];
        $companyName      = $this->contract->getCompanyNames();
        $locale           = $lang->defaultLang();
        $resourceList     = $this->codeList->getCodeList('resources',$lang->getSiteLang());
        $contractTypeList = $this->codeList->getCodeList('contract_types',$lang->getSiteLang());
        $documentTypeList = $this->codeList->getCodeList('document_types',$lang->getSiteLang());

        return view('contract.create', compact('country', 'contracts', 'contract', 'companyName', 'locale', 'resourceList', 'contractTypeList', 'documentTypeList'));
    }

    /**
     * Store a newly created contract.
     *
     * @param ContractRequest $request
     *
     * @return Response
     */
    public function store(ContractRequest $request)
    {
        if ($contract = $this->contract->saveContract($request->all())) {
            return redirect()->route('contract.show', ['id' => $contract->id])->with(
                'success',
                trans('contract.save_success')
            );
        }

        return redirect()->route('contract.index')->withError(trans('contract.save_fail'));
    }

    /**
     * Display specified contract
     *
     * @param                   $id
     * @param DiscussionService $discussion
     * @param LanguageService   $lang
     * @param Request           $request
     *
     * @return Response
     */
    public function show($id, DiscussionService $discussion, LanguageService $lang, Request $request)
    {

         $contract = $this->contract->findWithAnnotations($id, $withRelation = true);

         if (!$contract) {
             abort('404');
         }

         $associatedContracts          = $this->contract->getAssociatedContracts($contract);
         $status                       = $this->contract->getStatus($id);
         $annotations                  = $contract->annotations;
         $contract->metadata_comment   = $this->comment->getLatest($contract->id, Comment::TYPE_METADATA);
         $contract->text_comment       = $this->comment->getLatest($contract->id, Comment::TYPE_TEXT);
         $contract->annotation_comment = $this->comment->getLatest($contract->id, Comment::TYPE_ANNOTATION);
         $publishedInformation         = $this->contract->getPublishedInformation($id);
         $discussions                  = $discussion->getCount($id);
         $discussion_status            = $discussion->getResolved($id);
         $elementState                 = $this->activity->getElementState($id);
         $annotationStatus             = $this->annotation->getStatus($id);
         $annotationStatus             = $annotationStatus == '' ? $elementState['annotation'] : $annotationStatus;
         $locale                       = $request->route()->getParameter('lang', $lang->defaultLang());
         $resourceList                 = $this->codeList->getCodeList('resources',$lang->getSiteLang());
         $contractTypeList             = $this->codeList->getCodeList('contract_types',$lang->getSiteLang());
         $documentTypeList             = $this->codeList->getCodeList('document_types',$lang->getSiteLang());

         if (!is_null($locale)) {
             if (!$lang->isValidTranslationLang($locale)) {
                 abort(404);
             }

             if ($locale != $lang->defaultLang()) {
                 $contract->setLang($locale);
             }

         }

         return view(
             'contract.show',
             compact(
                 'contract',
                 'status',
                 'annotations',
                 'annotationStatus',
                 'associatedContracts',
                 'discussions',
                 'discussion_status',
                 'publishedInformation',
                 'elementState',
                 'locale',
                 'resourceList',
                 'contractTypeList',
                 'documentTypeList'
             )
         );
    }

    /**
     * Display contract edit form.
     *
     * @param                   $id
     * @param DiscussionService $discussion
     *
     * @param LanguageService   $lang
     * @param Request           $request
     *
     * @return Response
     */
    public function edit($id, DiscussionService $discussion, LanguageService $lang, Request $request)
    {
        $contract           = $this->contract->find($id);
        $country            = $this->countries->all();
        $supportingDocument = $this->contract->getSupportingDocuments($id);
        $contracts          = $this->contract->getList($id);
        $resourceList       = $this->codeList->getCodeList('resources',$lang->getSiteLang());
        $contractTypeList   = $this->codeList->getCodeList('contract_types',$lang->getSiteLang());
        $documentTypeList   = $this->codeList->getCodeList('document_types',$lang->getSiteLang());

        $discussions       = $discussion->getCount($id);
        $discussion_status = $discussion->getResolved($id);
        $companyName       = $this->contract->getCompanyNames();
        $view              = 'contract.edit';
        $locale            = $request->route()->getParameter('lang');

        if (!is_null($locale)) {
            if (!$lang->isValidTranslationLang($locale) || $locale == $lang->defaultLang()) {
                abort(404);
            }
            $contract->setLang($locale);
            $view = 'contract.edit_trans';
        }

        return view(
            $view,
            compact(
                'contract',
                'country',
                'supportingDocument',
                'contracts',
                'discussions',
                'discussion_status',
                'companyName',
                'locale',
                'resourceList',
                'contractTypeList',
                'documentTypeList'
            )
        );
    }

    /**
     * Update contract Detail
     *
     * @param ContractRequest $request
     * @param                 $contractID
     *
     * @return Response
     */
    public function update(ContractRequest $request, $contractID)
    {
        if ($this->contract->updateContract($contractID, $request->all())) {
            return redirect()->route('contract.show', $contractID)->withSuccess(trans('contract.update_success'));
        }

        return redirect()->route('contract.show', $contractID)->withError(trans('contract.update_fail'));
    }

    /**
     * Remove the specified contract
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        if ($this->contract->deleteContract($id)) {
            return redirect()->route('contract.index')->withSuccess(trans('contract.delete_success'));
        }

        return redirect()->route('contract.index')->withSuccess(trans('contract.delete_fail'));
    }

    /**
     * Save output
     *
     * @param         $id
     * @param Request $request
     *
     * @return Response
     */
    public function saveOutputType($id, Request $request)
    {
        $type     = $request->input('text_type');
        $contract = $this->contract->saveTextType($id, $type);

        if ($contract) {
            return response()->json(
                ['result' => 'success', 'type' => $contract->getTextType(), 'message' => trans('contract.saved')]
            );
        }

        return response()->json(['result' => 'error', 'message' => trans('contract.not_updated')]);
    }

    /**
     * Update contract status
     *
     * @param         $contract_id
     * @param Request $request
     * @param Guard   $auth
     *
     * @return Response
     */
    public function updateStatus($contract_id, Request $request, Guard $auth)
    {
        $status     = trim(strtolower($request->input('state')));
        $permission = [
            'completed' => 'complete',
            'rejected'  => 'reject',
            'published' => 'publish',
        ];

        if (!($auth->user()->can(sprintf('%s-metadata', $permission[$status])))) {
            return back()->withError(trans('contract.permission_denied'));
        }

        if ($this->contract->updateStatus($contract_id, $status, $request->input('type'))) {
            return back()->withSuccess(trans('contract.status_update'));
        }

        return back()->withError(trans('contract.invalid_status'));
    }

    /**
     * Save Contract Comment
     *
     * @param         $contract_id
     * @param Request $request
     * @param Guard   $auth
     *
     * @return Response
     */
    public function contractComment($contract_id, Request $request, Guard $auth)
    {
        $status = $request->get('status');

        if (!$auth->user()->can(sprintf('%s-%s', config('nrgi.permission')[$status], $request->get('type')))) {
            return back()->withError('Permission denied.');
        }

        if ($this->contract->updateStatusWithComment(
            $contract_id,
            $status,
            $request->input('message'),
            $request->input('type')
        )
        ) {
            return back()->withSuccess(trans('contract.status_update'));
        }

        return back()->withError(trans('contract.invalid_status'));
    }

    /**
     * Get Metadata by contract ID
     *
     * @param $contract_id
     *
     * @return Response
     */
    public function getMetadata($contract_id)
    {
        if ($contract = $this->contract->find($contract_id)) {
            return response()->json($contract->metadata);
        }

        return abort(404);
    }

    /**
     * Download Word File
     *
     * @param $contract_id
     */
    public function download($contract_id)
    {
        $contract = $this->contract->find($contract_id);

        if (empty($contract)) {
            abort(404);
        }

        $text = $this->contract->getTextFromS3($contract->id, $contract->file);

        if (empty($text)) {
            abort(404);
        }

        $filename = sprintf('%s-%s', $contract->id, str_limit(str_slug($contract->title), 70));

        header("Content-type: application/vnd.ms-wordx");
        header("Content-Disposition: attachment;Filename=$filename.doc");

        $html = "<html>";
        $html .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">";
        $html .= "<body>";
        $html .= $text;
        $html .= "</body>";
        $html .= "</html>";
        echo $html;
        exit;
    }

    /**
     * Save Contract Comment
     *
     * @param       $contract_id
     * @param Guard $auth
     *
     * @return Response
     * @internal param Request $request
     */
    public function publish($contract_id, Guard $auth)
    {
        if ($auth->user()->isCountryResearch()) {
            return back()->withError(trans('contract.permission_denied'));
        }
        $status = "published";
        $types  = ["metadata", "text"];
        foreach ($types as $type) {
            if (!$this->contract->updateStatus($contract_id, $status, $type)
            ) {
                return back()->withError(trans('contract.invalid_status'));
            }
        }

        $this->annotation->updateStatus("published", '', $contract_id);

        return back()->withSuccess(trans('contract.status_update'));
    }

    /**
     * Unpublish Contract
     *
     * @param         $contract_id
     * @param Guard   $auth
     *
     * @param Request $request
     *
     * @return Response
     */
    public function unpublish($contract_id, Guard $auth, Request $request)
    {
        $elementStatus = $request->all();
        if ($auth->user()->isCountryResearch()) {
            return back()->withError(trans('contract.permission_denied'));
        }

        if ($this->contract->unPublishContract($contract_id, $elementStatus)) {

            return back()->withSuccess(trans('contract.unpublish.success'));
        }

        return back()->withError(trans('contract.unpublish.fail'));
    }

    /**
     * Display contract type selection form.
     *
     * @return Response
     */
    public function contractType()
    {
        $parentContracts = $this->contract->parentContracts();

        return view('contract.type_selection', compact('parentContracts'));
    }

    /**
     * Get name for contract if ajax request exists
     *
     * @param Request $request
     *
     * @return object
     */
    public function getContractName(Request $request)
    {
        if ($request->ajax()) {
            return $this->contract->getContractName($request->all());
        } else {
            return abort(404);
        }
    }

    /**
     * Updates the published_at field in elastic search
     *
     * @return mixed
     */
    public function updatePublishedAtIndex()
    {
        if (auth()->user()->isAdmin()) {
            try {
                $uri = 'contract/published_at/update';
                $url = sprintf('%s%s', rtrim(env('ELASTIC_SEARCH_URL')), $uri);
                $recent_contracts = json_encode($this->activity->getPublishedContracts(true));
                $response = $this->http->post($url, [
                    'body' => [
                        'recent_contracts' => $recent_contracts
                    ]
                ]);

                return redirect()->route('contract.index')->withSuccess('Elastic updated successfully');
            } catch (\Exception $e) {
                file_put_contents('published_at_error.log', $e->getMessage(), FILE_APPEND);

                return redirect()->route('contract.index')->withSuccess('Elastic update error');
            }
        }

        return redirect()->route('contract.index')->withSuccess('Access denied');
    }

    /**
     * Updates annotation category name "Community consultation " to
     * "Community consultation" in elastic search
     * @return mixed
     */
    public function updateAnnotationCategory()
    {
        if (auth()->user()->isAdmin()) {
            try {
                $uri = 'contract/annotation_category/community_consultation/update';
                $url = sprintf('%s%s', rtrim(env('ELASTIC_SEARCH_URL')), $uri);
                $annotations = $this->annotation->getAllByAnnotation('community-consultation');
                $response = $this->http->post($url, [
                    'body' => [
                        'annotations' => $annotations
                    ]
                ]);

                return redirect()->route('contract.index')->withSuccess('Elastic updated successfully');
            } catch (\Exception $e) {
                file_put_contents('annotation_category-community_consultation_error.log', $e->getMessage());

                return redirect()->route('contract.index')->withSuccess('Elastic update error');
            }
        }

        return redirect()->route('contract.index')->withSuccess('Access denied');
    }

    /**
     * Request elastic server to update annotation cluster
     *
     * @return mixed
     */
    public function updateAnnotationCluster()
    {
        if (auth()->user()->isAdmin()) {
            try {
                $uri = 'contract/cluster/update';
                $url = sprintf('%s%s', rtrim(env('ELASTIC_SEARCH_URL')), $uri);
                $response = $this->http->post($url);

                return redirect()->route('contract.index')->withSuccess('Elastic updated successfully');
            } catch (\Exception $e) {
                file_put_contents('cluster_update_error.log', $e->getMessage(), FILE_APPEND);

                return redirect()->route('contract.index')->withSuccess('Elastic update error');
            }
        }

        return redirect()->route('contract.index')->withSuccess('Access denied');
    }

    /**
     * Request elastic server to restore annotation cluster
     *
     * @param $key
     *
     * @return mixed
     */
    public function restoreAnnotationCluster($key)
    {
        if (auth()->user()->isAdmin()) {
            try {
                $uri = 'contract/cluster/restore';
                $url = sprintf('%s%s', rtrim(env('ELASTIC_SEARCH_URL')), $uri);
                $response = $this->http->post($url, [
                    'body' => ['key' => $key]
                ]);

                return redirect()->route('contract.index')->withSuccess('Elastic restored successfully');
            } catch (\Exception $e) {
                file_put_contents('cluster_restore_error.log', $e->getMessage(), FILE_APPEND);

                return redirect()->route('contract.index')->withSuccess('Elastic restored error');
            }
        }

        return redirect()->route('contract.index')->withSuccess('Access denied');
    }

    /**
     * Renders pages for master docs
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getPaginatedMasterIndex()
    {
        if (auth()->user()->isAdmin()) {
            try {
                $uri = 'contract/supporting-doc/update';
                $url = sprintf('%s%s', rtrim(env('ELASTIC_SEARCH_URL')), $uri);
                $request_payload = ['get_master_pages' => true];
                $response = $this->http->post($url, [
                    'body' => $request_payload
                ]);
                $resp_json = $response->json();

                echo 'The total pages are: ' . $resp_json['result'];

                die();

            } catch (\Exception $e) {
                file_put_contents('supporting_doc_error.log', $e->getMessage(), FILE_APPEND);

                return redirect()->route('contract.index')->withErrors(
                    'Elastic update error. View supporting_doc_error.log file'
                );
            }
        }

        return redirect()->route('contract.index')->withSuccess('Access denied');
    }


    /**
     * Updates master docs page wise
     *
     * @param $page
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateMasterIndex($page)
    {
        if (auth()->user()->isAdmin()) {
            try {
                $uri = 'contract/supporting-doc/update';
                $url = sprintf('%s%s', rtrim(env('ELASTIC_SEARCH_URL')), $uri);
                $request_payload = ['add_to_master' => $page];
                $response = $this->http->post($url, [
                    'body' => $request_payload
                ]);
                $resp_json = $response->json();

                if (!$resp_json['status']) {
                    file_put_contents('supporting_doc_error.log', $resp_json['result'], FILE_APPEND);

                    return redirect()->route('contract.index')->withError(
                        'Elastic update error. View supporting_doc_error.log file'
                    );
                }

                return redirect()->route('contract.index')->withSuccess('Elastic updated successfully');
            } catch (\Exception $e) {
                file_put_contents('supporting_doc_error.log', $e->getMessage(), FILE_APPEND);

                return redirect()->route('contract.index')->withErrors(
                    'Elastic update error. View supporting_doc_error.log file'
                );
            }
        }

        return redirect()->route('contract.index')->withSuccess('Access denied');
    }

    /**
     * Updates parent child relation in master doc
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateParentChildDocIndex()
    {
        if (auth()->user()->isAdmin()) {
            try {
                $uri = 'contract/supporting-doc/update';
                $url = sprintf('%s%s', rtrim(env('ELASTIC_SEARCH_URL')), $uri);
                $parent_child_contracts = json_encode($this->contract->getParentChild());
                $request_payload = [
                    'parent_child_contracts' => $parent_child_contracts,
                ];
                $response = $this->http->post($url, [
                    'body' => $request_payload
                ]);
                $resp_json = $response->json();

                if (!$resp_json['status']) {
                    file_put_contents('supporting_doc_error.log', $resp_json['result'], FILE_APPEND);

                    return redirect()->route('contract.index')->withError(
                        'Elastic update error. View supporting_doc_error.log file'
                    );
                }

                return redirect()->route('contract.index')->withSuccess('Elastic updated successfully');
            } catch (\Exception $e) {
                file_put_contents('supporting_doc_error.log', $e->getMessage(), FILE_APPEND);

                return redirect()->route('contract.index')->withErrors(
                    'Elastic update error. View supporting_doc_error.log file'
                );
            }
        }

        return redirect()->route('contract.index')->withSuccess('Access denied');
    }

    /**
     * Updates child parent relation master doc
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateChildParentDocIndex()
    {
        if (auth()->user()->isAdmin()) {
            try {
                $uri                    = 'contract/supporting-doc/update';
                $url                    = sprintf('%s%s', rtrim(env('ELASTIC_SEARCH_URL')), $uri);
                $child_parent_contracts = json_encode($this->contract->getChildParent());
                $request_payload        = [
                    'child_parent_contracts' => $child_parent_contracts
                ];
                $response                = $this->http->post($url, [
                    'body' => $request_payload
                ]);
                $resp_json              = $response->json();

                if (!$resp_json['status']) {
                    file_put_contents('supporting_doc_error.log', $resp_json['result'], FILE_APPEND);

                    return redirect()->route('contract.index')->withError(
                        'Elastic update error. View supporting_doc_error.log file'
                    );
                }

                return redirect()->route('contract.index')->withSuccess('Elastic updated successfully');
            } catch (\Exception $e) {
                file_put_contents('supporting_doc_error.log', $e->getMessage(), FILE_APPEND);

                return redirect()->route('contract.index')->withErrors(
                    'Elastic update error. View supporting_doc_error.log file'
                );
            }
        }

        return redirect()->route('contract.index')->withSuccess('Access denied');
    }
}
