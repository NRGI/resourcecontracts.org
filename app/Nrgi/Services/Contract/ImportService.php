<?php namespace App\Nrgi\Services\Contract;

use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Repositories\Contract\ContractRepositoryInterface;
use App\Nrgi\Repositories\CodeList\ContractType\ContractTypeRepositoryInterface;
use App\Nrgi\Repositories\CodeList\DocumentType\DocumentTypeRepositoryInterface;
use App\Nrgi\Repositories\CodeList\Resource\ResourceRepositoryInterface;
use Exception;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel;
use Aws\Credentials\Credentials;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Aws\S3\MultipartUploader;
use Aws\Exception\MultipartUploadException;
use Illuminate\Contracts\Filesystem\Factory as Storage;
use Throwable;

/**
 * Class ImportService
 * @package App\Nrgi\Services\Contract
 */
class ImportService
{
    const UPLOAD_FOLDER = 'app';
    const PIPELINE = 0;
    const PROCESSING = 1;
    const COMPLETED = 2;
    const FAILED = 3;

    const CREATE_PENDING = 0;
    const CREATE_PROCESSING = 1;
    const CREATE_COMPLETED = 2;
    const CREATE_FAILED = 3;

    protected $separator = '||';

    /**
     * @var Excel
     */
    protected $excel;
    /**
     * @var Filesystem
     */
    protected $filesystem;
    /**
     * @var Guard
     */
    protected $auth;
    /**
     * @var Log
     */
    protected $logger;
    /**
     * @var Queue
     */
    protected $queue;
    /**
     * @var ContractRepositoryInterface
     */
    protected $contract;
    /**
     * @var Storage
     */
    protected $storage;
    /**
     * @var CountryService
     */
    protected $country;
     /**
     * @var ContractTypeRepositoryInterface
     */
    protected $contractType;
    /**
     * @var DocumentTypeRepositoryInterface
     */
    protected $documentType;
     /**
     * @var ResourceRepositoryInterface
     */
    protected $resource;

    protected $contractService;

    protected $ocid;

    protected $json_ocid = [];

    /*
     * Valid Keys
     */

    protected $keys = [
        "document_url",
        "contract_name",
        "language",
        "country_code",
        "resource",
        "government_entity",
        "contract_type",
        "signature_date",
        "document_type",
        "company_name",
        "participation_share",
        "jurisdiction_of_incorporation",
        "registration_agency",
        "incorporation_date",
        "company_address",
        "company_number",
        "corporate_grouping",
        "open_corporate_link",
        "operator",
        "project_title",
        "project_identifier",
        "license_name",
        "license_identifier",
        "disclosure_mode",
        "retrieval_date",
        "category",
        "source_url",
        "matrix_page",
        "deal_number",

    ];

    /**
     * @param ContractRepositoryInterface $contract
     * @param Excel $excel
     * @param Storage $storage
     * @param Filesystem $filesystem
     * @param Guard $auth
     * @param Log $logger
     * @param Queue $queue
     * @param CountryService $country
     * @param ContractService $contractService
     */
    public function __construct(
        ContractRepositoryInterface $contract,
        Excel $excel,
        Storage $storage,
        Filesystem $filesystem,
        Guard $auth,
        Log $logger,
        Queue $queue,
        CountryService $country,
        ContractService $contractService,
        ContractTypeRepositoryInterface $contractType,
        DocumentTypeRepositoryInterface $documentType,
        ResourceRepositoryInterface $resource
    )
    {
        $this->excel           = $excel;
        $this->filesystem      = $filesystem;
        $this->auth            = $auth;
        $this->logger          = $logger;
        $this->queue           = $queue;
        $this->contract        = $contract;
        $this->storage         = $storage;
        $this->country         = $country;
        $this->contractService = $contractService;
        $this->contractType    = $contractType;
        $this->documentType    = $documentType;
        $this->resource        = $resource;
    }  

    /**
     * Import CSV File
     *
     * @param Request $request
     *
     * @return bool|string
     * @throws Exception
     */
    public function import(Request $request)
    {
        $import_key = md5(microtime());
        
        try {
            $originalFileName = $request->file('file')->getClientOriginalName();
            $fileName         = $originalFileName;
            $request->file('file')->move($this->getFilePath($import_key), $fileName);
        } catch (Throwable $d) {
            $this->logger->error('File could not be uploaded');
            throw new Exception('File could not be uploaded.');
        }

        try {
            $excelData = $this->extractRecords($this->getFilePath($import_key, $fileName));
            $contracts = $this->filterContracts($excelData[1]);
        } catch (Exception $e) {
            $this->logger->error('Import Error :' . $e->getMessage());
            $this->deleteImportFolder($import_key);
            throw new Exception('Could not extract data from file.');
        }

        if (empty($contracts)) {
            $this->deleteImportFolder($import_key);
            throw new Exception('Could not found any contract to import.');
        }

        if (!$this->isValidFormat($contracts)) {
            $this->deleteImportFolder($import_key);
            throw new Exception('File is not in valid format. Please check sample csv file for the valid format.');
        }

        $this->exportToJson($import_key, $contracts);

        $this->queue->push(
            'App\Nrgi\Services\Queue\ContractDownloadQueue',
            ['import_key' => $import_key],
            'contract_download'
        );

        return $import_key;
    }

    /**
     * Read and extract records from file
     *
     * @param $file
     *
     * @return array
     */
    protected function extractRecords($file)
    {
        return $this->excel->load($file)->all()->toArray();
    }

    /**
     * Save contracts array to json file
     *
     * @param $import_key
     * @param $results
     */
    protected function exportToJson($import_key, $results)
    {
        $data = [];

        foreach ($results as $key => $result) {
            $contract       = $this->getFormattedJsonData($result);
            $contract['id'] = $key + 1;
            $data[]         = $contract;
        }

        $this->updateJson($import_key, $data);
    }

    /**
     * Format the csv data to Contract array
     *
     * @param $results
     *
     * @return array
     */
    protected function getFormattedJsonData(array $results)
    {
        $results                               = trimArray($results);
        $contract                              = config('metadata.schema');
        $company_template                      = $contract['metadata']['company'][0];
        $contract['metadata']['contract_name'] = $results['contract_name'];

        $contract['metadata']['is_supporting_document']     = $results['main_associated'];
        $contract['metadata']['parent_open_contracting_id'] = $results['main_document_ocid_if_already_published'];

        if($contract['metadata']['is_supporting_document'] == '0'){
            //for main document
            $contract['metadata']['parent_open_contracting_id'] = '';
        }

        $contract['metadata']['is_contract_signed']         = $results['contract_signed'];
        $contract['metadata']['document_type']              = $results['document_type'];

        $company_name_arr                  = explode($this->separator, $results['company_name']);
        $participation_share_arr           = explode($this->separator, $results['participation_share']);
        $jurisdiction_of_incorporation_arr = explode($this->separator, $results['jurisdiction_of_incorporation']);
        $registration_agency_arr           = explode($this->separator, $results['registration_agency']);
        $incorporation_date_arr            = explode($this->separator, $results['incorporation_date']);
        $company_address_arr               = explode($this->separator, $results['company_address']);
        $company_number_arr                = explode($this->separator, $results['company_number']);
        $corporate_grouping_arr            = explode($this->separator, $results['corporate_grouping']);
        $open_corporate_id_arr             = explode($this->separator, $results['open_corporate_link']);
        $operator_arr                      = explode($this->separator, $results['operator']);

        $companies = [];
        foreach ($company_name_arr as $key => $company) {
            $company_default                                  = $company_template;
            $company_default['name']                          = $company;
            $company_default['participation_share']           = isset($participation_share_arr[$key]) ? $this->getParticipation_share($participation_share_arr[$key]) : '';
            $company_default['jurisdiction_of_incorporation'] = isset($jurisdiction_of_incorporation_arr[$key]) ? $jurisdiction_of_incorporation_arr[$key] : '';
            $company_default['registration_agency']           = isset($registration_agency_arr[$key]) ? $registration_agency_arr[$key] : '';
            $company_default['company_founding_date']         = isset($incorporation_date_arr[$key]) ? $this->dateFormat($incorporation_date_arr[$key]) : '';
            $company_default['company_address']               = isset($company_address_arr[$key]) ? $company_address_arr[$key] : '';
            $company_default['company_number']                = isset($company_number_arr[$key]) ? $company_number_arr[$key] : '';
            $company_default['parent_company']                = isset($corporate_grouping_arr[$key]) ? $corporate_grouping_arr[$key] : '';
            $company_default['open_corporate_id']             = isset($open_corporate_id_arr[$key]) ? $open_corporate_id_arr[$key] : '';
            $company_default['operator']                      = isset($operator_arr[$key]) ? $this->getOperatorValue($operator_arr[$key]) : '';
            $companies[]                                      = $company_default;
        }

        $contract['document_url']     = $results['document_url'];
        $pdfArray = explode("/", $results['document_url']);

        if(in_array("drive.google.com", $pdfArray)){
            //converts the shareable google drive link to downloadable link 
            $newPdf = $this->convertToDownloadableUrl($pdfArray, $results['document_url']);
            $contract['document_url'] = $newPdf;
        } else if(in_array("www.dropbox.com", $pdfArray)){
            //converts the shareable google drive link to downloadable link 
            $newPdf = $this->convertToDownloadableUrlDropbox($pdfArray, $results['document_url']);
            $contract['document_url'] = $newPdf;
        }

        $contract['download_status']  = static::PIPELINE;
        $contract['download_remarks'] = '';
        $contract['create_status']    = '';
        $contract['create_remarks']   = '';

        $contract['user_id']                        = $this->auth->id();
        $contract['metadata']['resource']           = array_filter(explode($this->separator, $results['resource']));
        $contract['metadata']['project_title']      = $results['project_title'];
        $contract['metadata']['project_identifier'] = $results['project_identifier'];

        $contract['metadata']['company']          = $companies;
        $contract['metadata']['disclosure_mode']  = $results['disclosure_mode'];
        $contract['metadata']['type_of_contract'] = array_filter(explode($this->separator, $results['contract_type']));
        $contract['metadata']['date_retrieval']   = $this->dateFormat($results['retrieval_date'], 'Y-m-d');

        $license_name       = explode($this->separator, $results["license_name"]);
        $license_identifier = explode($this->separator, $results["license_identifier"]);
        $count              = (count($license_name) > count($license_identifier)) ? count($license_name) : count($license_identifier);

        for ($i = 0; $i < $count; $i++) {
            $contract['metadata']['concession'][$i]['license_name']       = isset($license_name[$i]) ? $license_name[$i] : '';
            $contract['metadata']['concession'][$i]['license_identifier'] = isset($license_identifier[$i]) ? $license_identifier[$i] : '';
        }
        $countryCode                            = $this->getCountry($results['country_code']);
        $contract['metadata']['country']        = $countryCode;

        $contract['metadata']['signature_year'] = $results['signature_year'];

        if($results['signature_date'] == ""){
            $contract['metadata']['signature_date'] = "";
        }else{
            $contract['metadata']['signature_date'] = $this->dateFormat($results['signature_date'], 'Y-m-d');
            $contract['metadata']['signature_year'] = $this->dateFormat($results['signature_date'], 'Y');
        }
        $contract['metadata']['language']       = $this->getLanguage(strtolower($results['language']));
        $contract['metadata']['category']       = [strtolower($results['category'])];
        $contract['metadata']['show_pdf_text']  = Contract::SHOW_PDF_TEXT;

        $government_entity = explode($this->separator, $results["government_entity"]);
        $count             = count($government_entity);

        for ($i = 0; $i < $count; $i++) {
            $govEntity                                                   = isset($government_entity[$i]) ? $government_entity[$i] : '';
            $contract['metadata']['government_entity'][$i]['entity']     = $govEntity;
            $contract['metadata']['government_entity'][$i]['identifier'] = $this->getGovernmentIdentifier($govEntity, $countryCode);
        }

        if (empty($contract['metadata']['contract_name'])) {
            $contract['metadata']['contract_name'] = $this->contractService->getContractName($contract['metadata']);
        }
        $contract['metadata']['open_contracting_id'] = $this->contract->generateOCID();

        if($contract['metadata']['is_supporting_document'] == '0'){
            //save ocid of main contract 
            $this->ocid = $contract['metadata']['open_contracting_id'];
        }

        if(empty($contract['metadata']['parent_open_contracting_id']) && $contract['metadata']['is_supporting_document'] == '1'){
            //stores parent ocid for associated document
            $contract['metadata']['parent_open_contracting_id'] = $this->ocid;
        }
         //for rc and olc addition of source url
         $contract['metadata']['source_url']=isset($results['source_url']) ? $results['source_url'] : ''; 
        
         //for addition of matrix_page and deal_number for olc
        
         if(strcasecmp($results['category'],'olc')==0)
         {
         $contract['metadata']['matrix_page']=isset($results['matrix_page']) ? $results['matrix_page'] : ''; 
         $contract['metadata']['deal_number']=isset($results['deal_number']) ? $results['deal_number'] : ''; 
            
         
         }

        return trimArray($contract);
    }

    /**
     * Download Pdfs
     *
     * @param $import_key
     */
    public function download($import_key)
    {
        $contracts = $this->getJsonData($import_key);

        if (is_array($contracts)) {
            foreach ($contracts as $contract) {
                $this->updateContractJsonByID($import_key, $contract->id, ['download_status' => static::PROCESSING]);
                if($contract->metadata->is_supporting_document == 0){
                    array_push($this->json_ocid, $contract->metadata->open_contracting_id);
                }
                list($status, $message) = $this->validateContract($contract);

                if (!$status) {
                    $this->updateContractJsonByID($import_key, $contract->id, ['download_remarks' => $message, 'download_status' => static::FAILED]);
                    continue;
                }

                $file = $this->downloadPdf($import_key, $contract->document_url);

                if (is_null($file)) {
                    $this->updateContractJsonByID($import_key, $contract->id, ['download_remarks' => trans('File could not be downloaded'), 'download_status' => static::FAILED]);
                    continue;
                }

                $fileHash = getFileHash($this->getFilePath($import_key, $file));

                if ($con = $this->contract->getContractByFileHash($fileHash)) {
                    $title = sprintf('<a href="%s" target="_blank">%s</a>', route('contract.show', $con->id), str_limit($con->title, 25));
                    $this->updateContractJsonByID(
                        $import_key,
                        $contract->id,
                        ['download_remarks' => trans('contract.import.exist', ['link' => $title]), 'file' => $file, 'download_status' => static::FAILED]
                    );
                    $this->deleteFile($import_key, $file);
                    continue;
                }

                $filePath = $this->getFilePath($import_key, $file);

                $this->updateContractJsonByID(
                    $import_key,
                    $contract->id,
                    ['file' => $file, 'filehash' => $fileHash, 'file_size' => filesize($filePath), 'download_status' => static::COMPLETED]
                );

            }
        }
    }

    /**
     * Update Contract Json file
     *
     * @param       $key
     * @param       $id
     * @param array $updateData
     * @param int $step
     *
     * @return bool
     */
    protected function updateContractJsonByID($key, $id, array $updateData, $step = 1)
    {
        $contracts = $this->getJsonData($key);

        foreach ($contracts as &$contract) {

            if ($contract->id == $id) {

                foreach ($updateData as $ckey => $value) {

                    if (property_exists($contract, $ckey)) {
                        $contract->$ckey = $value;
                    }

                    if (property_exists($contract->metadata, $ckey)) {
                        $contract->metadata->$ckey = $value;
                    }
                }

                break;
            }

        }

        return $this->updateJson($key, $contracts, $step);
    }

    /**
     * Download a Pdf File
     *
     * @param $key
     * @param $pdf
     *
     * @return null|string
     */
    protected function downloadPdf($key, $pdf)
    {
        $pdf_name  = sha1(str_random()) . '.pdf';
        $temp_path = $this->getFilePath($key, $pdf_name);

        try {
            copy($pdf, $temp_path);

            return $pdf_name;
        } catch (\Exception $e) {
            $this->logger->error('PDF Download Error:' . $e->getMessage());

            return null;
        }
    }

    /**
     * Changes the given url into downloadable url if it is google drive link
     * If variabel $newPdf is returned empty it means the drive link doesnot satisfy any of the conditions 
     * The shareable link needs to be viewed
     *
     * @param $pdfArray
     * @param $docUrl
     * @return string
     */
    protected function convertToDownloadableUrl($pdfArray, $docUrl)
    {
        $newPdf = '';
        if(in_array("d", $pdfArray)){
            $array_key = array_search("d", $pdfArray);
            $doc_id = $pdfArray[$array_key + 1];
            $newPdf = sprintf("https://drive.google.com/uc?id=%s&export=download",$doc_id);
        }else{
            $parsed_url = parse_url($docUrl);
            if(isset($parsed_url['query'])){
                parse_str($parsed_url['query'], $params);
                if(isset($params['id'])){
                    $doc_id = $params['id'];
                    $newPdf = sprintf("https://drive.google.com/uc?id=%s&export=download",$doc_id);
                }
            }
        }
        return $newPdf;
    }

      protected function convertToDownloadableUrlDropbox($pdfArray, $docUrl) {
        $newPdf = '';
        if(strpos($docUrl, 'www.dropbox.com')!== false){
            $newPdf = str_replace('www.dropbox.com', 'dl.dropboxusercontent.com', $docUrl);
        }
        return $newPdf;
    }

    /**
     * Save all Contracts
     *
     * @param $key
     * @param $ids
     *
     * @return bool
     */
    public function saveContracts($key, $ids)
    {
        if (empty($ids)) {
            return false;
        }

        $all_data  = $this->getJsonData($key);
        $contracts = [];
        foreach ($all_data as $data) {
            if (in_array($data->id, $ids) && $data->download_status == static::COMPLETED) {
                $data->create_status = static::CREATE_PENDING;
                $contracts[]         = $data;
            }
        }

        $this->updateJson($key, $contracts, 2);

        $this->queue->push(
            'App\Nrgi\Services\Queue\UploadBulkPdf',
            ['key' => $key],
            'contract_bulk_create'
        );

        return true;
    }

    /**
     * Upload Pdf to s3 and create contracts
     *
     * @param $key
     */
    public function uploadPdfToS3AndCreateContracts($key)
    {
        $contracts = $this->getJsonData($key);

        foreach ($contracts as $contract) {
            try {
                $contract->metadata->contract_name = $this->checkContractNameAvailability($key, $contract);

                $this->updateContractJsonByID($key, $contract->id, ['create_status' => static::CREATE_PROCESSING], 2);

                // $this->storage->disk('s3')->put(
                //     $contract->file,
                //     $this->filesystem->get($this->getFilePath($key, $contract->file))
                // );
                $newFileName=$contract->file;
                $file_path=$this->getFilePath($key, $contract->file);
                $credentials = new Credentials(env('AWS_KEY'), env('AWS_SECRET'));
                $client = new S3Client(
                    [
                        'version'=> '2006-03-01',
                        'region' => env('AWS_REGION'),
                        'credentials' => $credentials
                    ]
                );
                $uploader = new MultipartUploader($client, $file_path, [
                    'bucket' => env('AWS_BUCKET'),
                    'key' => $newFileName,
                    'before_upload' => function(\Aws\Command $command) {
                       gc_collect_cycles();
                    }
                 ]);
                 $data=$uploader->upload();

            } catch (Exception $e) {
                $this->updateContractJsonByID($key, $contract->id, ['create_status' => static::CREATE_FAILED], 2);
                $this->logger->error(sprintf('File could not be uploaded : %s', $e->getMessage()));

                continue;
            }

            $data = [
                'file'     => $contract->file,
                'filehash' => $contract->filehash,
                'user_id'  => $contract->user_id,
                'metadata' => $contract->metadata,
            ];

            try {
                $con               = $this->contract->save($data);
                $openContractingId = $contract->metadata->parent_open_contracting_id;

                if (!empty($openContractingId)) {
                    $supportContract = $this->contract->findContractByOpenContractingId($openContractingId);
                    $con->syncSupportingContracts($supportContract->id);
                }

                $this->logger->activity('contract.log.save', ['contract' => $con->id], $con->id, $con->user_id);
                $this->updateContractJsonByID($key, $contract->id, ['create_status' => static::CREATE_COMPLETED], 2);

                if ($con) {
                    $this->queue->push('App\Nrgi\Services\Queue\ProcessDocumentQueue', ['contract_id' => $con->id]);
                }

                $this->logger->info(
                    'Contract successfully created.',
                    ['Contract Title' => $con->title]
                );
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
                if ($this->storage->disk('s3')->exists($contract->file)) {
                    $this->storage->disk('s3')->delete($contract->file);
                }
                $this->updateContractJsonByID($key, $contract->id, ['create_remarks' => trans('contract.save_fail'), 'create_status' => static::CREATE_FAILED], 2);
            }
            $this->deleteFile($key, $contract->file);
        }
    }

    /**
     * Checks if contract name is already present in the database. 
     * Appends number if contract name is already present in database.
     *
     * @param $key
     * @param $contract
     * @return void
     */
    public function checkContractNameAvailability($key, $contract)
    {
        $checkName = $this->contract->checkMetaDataContractName($contract->metadata->contract_name);

        $name = $contract->metadata->contract_name;

        if($checkName){
            $count = 1;

            do{
                $newName = $name . '-' . $count;
                $check = $this->contract->checkMetaDataContractName($newName);
                
                if($check){
                    $count++;
                }else{
                    $this->updateContractJsonByID($key, $contract->id, ['contract_name' => $newName], 2);  
                }
            }while($check);

            return $newName;
        }else{
            return $name;
        }
    }


    /**
     * Get Contract Json
     *
     * @param      $key
     * @param bool $contractOnly
     *
     * @return object|null
     */
    public function getJsonData($key, $contractOnly = true)
    {
        try {
            $filename = $key . '.json';
            $data     = $this->filesystem->get($this->getFilePath($key, $filename));
            $data     = json_decode($data);
            if ($contractOnly) {
                return $data->contracts;
            }

            return $data;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            return null;
        }
    }

    /**
     * Update Contract Json
     *
     * @param     $key
     * @param     $contracts
     * @param int $step
     *
     * @return bool
     */
    protected function updateJson($key, $contracts, $step = 1)
    {
        $data_string = json_encode(['step' => $step, 'contracts' => $contracts]);
        $fileName    = $key . '.json';
        $filePath    = $this->getFilePath($key, $fileName);

        return $this->filesystem->put($filePath, $data_string);
    }

    /**
     * Get File
     *
     * @param string $key
     * @param string $fileName
     *
     * @return string
     */
    public function getFilePath($key = '', $fileName = '')
    {
        if ($key == '' && $fileName == '') {
            return storage_path(static::UPLOAD_FOLDER);
        }

        if ($key != '' && $fileName != '') {
            return sprintf('%s/%s/%s', storage_path(static::UPLOAD_FOLDER), $key, $fileName);
        }

        if ($key != '' && $fileName == '') {
            return sprintf('%s/%s', storage_path(static::UPLOAD_FOLDER), $key);
        }
    }

    /**
     * Delete File from temp folder
     *
     * @param $key
     * @param $file
     *
     * @return bool
     */
    protected function deleteFile($key, $file)
    {
        return $this->filesystem->delete($this->getFilePath($key, $file));
    }

    /**
     * Get Import Job by User
     *
     * @return array
     */
    public function getImportJobByUser()
    {
        $dirs     = $this->filesystem->directories($this->getFilePath());
        $user_key = [];

        if (empty($dirs)) {
            return $user_key;
        }

        foreach ($dirs as $dir) {

            $key  = last(explode('/', $dir));
            $json = $this->getJsonData($key, false);

            if (empty($json)) {
                continue;
            }

            $contracts = $json->contracts;

            if (isset($contracts[0]->user_id) && $contracts[0]->user_id == $this->auth->id()) {
                $user_key[] = [
                    'step'         => $json->step,
                    'key'          => $key,
                    'is_completed' => $this->isImportCompleted($key),
                    'file'         => $this->getOriginalFileName($key)
                ];
            }

        }

        return $user_key;
    }

    /**
     * Get Original Imported FileName
     *
     * @param $key
     *
     * @return string|null
     */
    protected function getOriginalFileName($key)
    {
        $files = $this->filesystem->allFiles($this->getFilePath($key));

        foreach ($files as $file) {
            if (!in_array(pathinfo($file, PATHINFO_EXTENSION), ['pdf', 'json'])) {
                return ['name' => $file->getRelativePathname(), 'created_at' => filemtime($file)];
            }
        }

        return null;
    }

    /**
     * Get Language code
     *
     * @param $language
     *
     * @return mixed
     */
    protected function getLanguage($lang)
    {
        $languages = trans('codelist/language')['major'] + trans('codelist/language')['minor'];

        foreach ($languages as $code => $name) {
            if ($lang == $code || strtolower($name) == $lang) {
                return $code;
            }
        }

        return '';
    }

    /**
     * Get Country code and name
     *
     * @param $code
     *
     * @return array
     */
    protected function getCountry($code)
    {
        $country = $this->country->getInfoByCode(strtoupper($code), 'en');

        return is_array($country) ? $country : ['code' => '', 'name' => ''];
    }

    /**
     * Delete Import Folder
     *
     * @param $key
     *
     * @return bool
     */
    public function deleteImportFolder($key)
    {
        return $this->filesystem->deleteDirectory($this->getFilePath($key));
    }

    /**
     * check whether Import Completed or not
     *
     * @param $key
     *
     * @return bool
     */
    public function isImportCompleted($key)
    {
        $contracts = $this->getJsonData($key);

        foreach ($contracts as $contract) {
            if ($contract->create_status < static::COMPLETED) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get Formatted date
     *
     * @param        $date
     * @param string $format
     *
     * @return string
     */
    public function dateFormat($date, $format = 'Y-m-d')
    {
        $time = strtotime($date);

        if ($time != '') {
            return date($format, $time);
        }

        return '';
    }

    /**
     * Check for valid format
     *
     * @param $contracts
     *
     * @return bool
     */
    protected function isValidFormat($contracts)
    {
        $titles = array_keys($contracts[0]);
        $diff   = array_diff($this->keys, $titles);

        if (count($diff) > 0) {
            return false;
        }

        return true;
    }

    /**
     * Validate Contract
     *
     * @param $contract
     *
     * @return array
     */
    protected function validateContract($contract)
    {
        $required = [
            "category",
            "contract_name",
            "signature_year",
            "language",
            "country",
            "document_type",
            "resource",
            "government_entity",
            "company",
            
        ];
        $message  = '';

        foreach ($required as $key) {
            if (empty($contract->metadata->$key)) {
                $message .= '<p>' . ucwords(str_replace('_', ' ', $key)) . ' is required.</p>';
            }
        }
        $countryCode = $contract->metadata->country->code;
        $countries   = trans('codelist/country');

        if (empty($contract->document_url)) {
            $message .= '<p>Document file url is required.</p>';
        }

        if ($countryCode == '' && !array_key_exists($countryCode, $countries)) {
            $message .= '<p>Country Code is invalid.</p>';
        }
        $companies           = $contract->metadata->company;
        $government_entities = $contract->metadata->government_entity;

        foreach ($government_entities as $government_entity) {
            if (empty($government_entity->entity)) {
                $message .= '<p>Government Entity is invalid.</p>';
                break;
            }
        }

        foreach ($companies as $company) {
            if (
                !empty($company->jurisdiction_of_incorporation) &&
                !array_key_exists($company->jurisdiction_of_incorporation, $countries
                )
            ) {
                $message .= '<p>Jurisdiction of Incorporation is invalid.</p>';
                break;
            }
        }

        foreach ($companies as $company) {
            if (empty($company->name)) {
                $message .= '<p>Company name is invalid.</p>';
                break;
            }
        }

        $languages = trans('codelist/language');
        $languages = array_merge($languages['major'], $languages['minor']);

        if (!empty($contract->metadata->language) && !array_key_exists($contract->metadata->language, $languages)) {
            $message .= '<p>Language is invalid.</p>';
        }
        $resources     = $this->resource->getResources('en');
        $metaResources = !empty($contract->metadata->resource) ? $contract->metadata->resource : [];

        foreach ($metaResources as $metaResource) {
            if (!array_key_exists($metaResource, $resources)) {
                $message .= '<p>Resource is invalid.</p>';
                break;
            }
        }
        $meta_contract_types = !empty($contract->metadata->type_of_contract) ? $contract->metadata->type_of_contract : [];
        $contract_types      = $this->contractType->getContractTypes('en');

        foreach ($meta_contract_types as $meta_contract_type) {
            if (!empty($meta_contract_type) &&
                !array_key_exists($meta_contract_type, $contract_types
                )
            ) {
                $message .= '<p>Contract Type is Invalid.</p>';
                break;
            }
        }
        $document_type  = $contract->metadata->document_type;
        $document_types = $this->documentType->getDocumentTypes('en');

        if (!empty($document_type) && !array_key_exists($document_type, $document_types)) {
            $message .= "<p>Document Type is invalid.</p>";
        }
        $category = strtolower($contract->metadata->category[0]);

        if ($category != 'rc' && $category != 'olc') {
            $message .= "<p>Category is empty or invalid ['rc' or 'olc'].</p>";
        }

        if ($contract->metadata->is_supporting_document) {
            $open_contracting_id = $contract->metadata->parent_open_contracting_id;

            if (!empty($open_contracting_id) && !$this->contract->findContractByOpenContractingId($open_contracting_id) && !in_array($open_contracting_id, $this->json_ocid)) {
                $message .= "<p>Open contracting id is invalid.</p>";
            }
        }

        if (!empty($contract->metadata->signature_date) && !$this->validateDate($contract->metadata->signature_date)) {
            $message .= "<p>Signature date is invalid.</p>";
        }

        if (!empty($contract->metadata->date_retrieval) && !$this->validateDate($contract->metadata->date_retrieval)) {
            $message .= "<p>Retrieval date is invalid.</p>";
        }
       if(!empty($contract->metadata->source_url)&& !($this->validateUrl($contract->metadata->source_url)))
       {
           
        $message .= "<p>Source URL is invalid.</p>";

       }

        return [($message == ''), $message];
    }

    //validate Url
    protected function validateUrl($url)
    {
        if (preg_match("/\b(?:(?:https?):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $url)) {
            return true;
          }
           return false;
    }

    /**
     * Validate date format
     *
     * @param $date
     * @param string $format
     *
     * @return bool
     */
    protected function validateDate($date, $format = 'Y-m-d')
    {
        $d = \DateTime::createFromFormat($format, $date);

        return $d && $d->format($format) === $date;
    }


    /**
     * GET Operator Value
     *
     * @param $key
     *
     * @return int
     */
    protected function getOperatorValue($key)
    {
        $key = trim(strtolower($key));

        if ($key == 'yes') {
            return 1;
        }

        if ($key == 'no') {
            return 0;
        }

        return -1;
    }

    /**
     * Get Valid participation share
     *
     * @param $share
     *
     * @return string
     */
    protected function getParticipation_share($share)
    {
        if ($share <= 1) {
            return $share;
        }

        return "";
    }

    /**
     * Get government identifier by government entity
     *
     * @param $govEntity
     * @param $country
     *
     * @return string
     */
    private function getGovernmentIdentifier($govEntity, $country)
    {
        $countryCode            = $country['code'];
        $govEntities            = config('governmentEntities');
        $identifier             = '';
        $govEntitiesCountryWise = isset($govEntities->{$countryCode}) ? $govEntities->{$countryCode} : [];

        foreach ($govEntitiesCountryWise as $row) {
            if ($row->entity == $govEntity) {
                $identifier = isset($row->identifier) ? $row->identifier : '';
                break;
            }
        }

        return $identifier;
    }

    /**
     * Removes empty contracts
     *
     * @param $contracts
     *
     * @return mixed
     */
    private function filterContracts($contracts)
    {
        foreach ($contracts as $key => $row) {
            $is_empty = true;
            foreach ($row as $field) {
                if (isset($field)) {
                    $is_empty = false;
                    break;
                }
            }
            if ($is_empty) {
                unset($contracts[$key]);
            }
        }

        return $contracts;
    }
}
