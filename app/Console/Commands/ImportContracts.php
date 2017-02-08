<?php namespace App\Console\Commands;

use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Repositories\Contract\ContractRepositoryInterface;
use App\Nrgi\Services\Contract\ContractService;
use App\Nrgi\Services\Contract\CountryService;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Contracts\Filesystem\Factory as Storage;

/**
 * Class ImportContracts
 * @package App\Console\Commands
 */
class ImportContracts extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'nrgi:importContracts';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import contracts for Tunisia CKAN spreadsheet data.';
    /**
     * @var array
     */
    protected $fields = [
        "project_identifier",
        "contract_name",
        "source_url",
        "government_entity",
        "country",
        "language",
        "resource",
        "is_supporting_document",
        "contract_type_1",
        "contract_type_2",
        "signature_date",
        "document_type",
        "company_name_1",
        "company_address_1",
        "jurisdiction_of_incorporation_1",
        "registration_agency_1",
        "company_number_1",
        "corporate_grouping_1",
        "participation_share_1",
        "open_corporates_link_1",
        "incorporation_date_1",
        "operator_1",
        "company_name_2",
        "company_address_2",
        "jurisdiction_of_incorporation_2",
        "registration_agency_2",
        "company_number_2",
        "corporate_grouping_2",
        "participation_share_2",
        "open_corporates_link_2",
        "incorporation_date_2",
        "operator_2",
        "company_name_3",
        "company_address_3",
        "jurisdiction_of_incorporation_3",
        "registration_agency_3",
        "company_number_3",
        "corporate_grouping_3",
        "participation_share_3",
        "open_corporates_link_3",
        "incorporation_date_3",
        "operator_3",
        "company_name_4",
        "company_address_4",
        "jurisdiction_of_incorporation_4",
        "registration_agency_4",
        "company_number_4",
        "corporate_grouping_4",
        "participation_share_4",
        "open_corporates_link_4",
        "incorporation_date_4",
        "operator_4",
        "company_name_5",
        "company_address_5",
        "jurisdiction_of_incorporation_5",
        "registration_agency_5",
        "company_number_5",
        "corporate_grouping_5",
        "participation_share_5",
        "open_corporates_link_5",
        "incorporation_date_5",
        "operator_5",
        "project_title",
        "license_name",
        "license_identifier",
        "disclosure_mode",
        "retrieval_date",
    ];

    /**
     * @var int
     */
    protected $skip = 10;
    /**
     * @var int
     */
    protected $limit = 44;
    /**
     * @var CountryService
     */
    protected $country;
    /**
     * @var Log
     */
    protected $log;
    /**
     * @var ContractRepositoryInterface
     */
    protected $contract;
    /**
     * @var Filesystem
     */
    protected $filesystem;
    /**
     * @var Storage
     */
    protected $storage;
    /**
     * @var Queue
     */
    protected $queue;
    /**
     * @var bool
     */
    protected $test = false;
    /**
     * @var string
     */
    protected $externalPath = 'https://s3-us-west-2.amazonaws.com/resourcecontracts-dev/pdf2/';
    /**
     * @var ContractService
     */
    protected $contractService;

    /**
     * Create a new command instance.
     *
     * @param ContractRepositoryInterface $contract
     * @param ContractService             $contractService
     * @param CountryService              $country
     * @param Storage                     $storage
     * @param Filesystem                  $filesystem
     * @param Log                         $log
     * @param Queue                       $queue
     */
    public function __construct(
        ContractRepositoryInterface $contract,
        ContractService $contractService,
        CountryService $country,
        Storage $storage,
        Filesystem $filesystem,
        Log $log,
        Queue $queue
    ) {
        parent::__construct();
        $this->country         = $country;
        $this->log             = $log;
        $this->contract        = $contract;
        $this->filesystem      = $filesystem;
        $this->storage         = $storage;
        $this->queue           = $queue;
        $this->contractService = $contractService;
    }

    /**
     * Execute the console command.
     *
     */
    public function fire()
    {
        $json       = $this->getJson();
        $i          = 1;
        $importFile = [];
        foreach ($json as $data) {

            if (!$this->isImportable($data)) {
                continue;
            }

            $isContractImported = Contract::whereRaw("metadata->>'ckan' = '1'")
                                          ->whereRaw("metadata->>'contract_name' = ?", [$data['contract_name']])
                                          ->first();
            if ($isContractImported) {
                $this->error($isContractImported->id.' : Contract already Imported : '.$data['contract_name']);
                continue;
            }

            $contract = $this->extractData($data);

            if ($this->test) {
                $file = md5(microtime()).'.pdf';
            } else {
                $file = $this->downloadPdf(rawurlencode($data['source_url']));
            }

            if (empty($file)) {
                continue;
            }

            if ($this->test) {
                $fileHash = md5(microtime());
            } else {
                $fileHash = getFileHash($this->tempPath($file));
            }

            if ($con = $this->contract->getContractByFileHash($fileHash)) {
                $this->error(
                    'File already exist'.
                    ' contract_id: '.$con->id.
                    ' file: '.$con->file.
                    ' CKAN file: '.$contract['metadata']['source_url']
                );
                $this->deleteFile($file);
                continue;
            }

            try {
                if (!$this->test) {
                    $this->storage->disk('s3')->put(
                        $file,
                        fopen($this->tempPath($file), 'r+')
                    );
                    $this->info('File uploaded to s3 : '.$file);
                }

            } catch (Exception $e) {
                $this->error(sprintf('File could not be uploaded : %s', $e->getMessage()));

                continue;
            }

            $contract['metadata']['file_size'] = ($this->test) ? '234234' : filesize($this->tempPath($file));

            $contract_data = [
                'file'     => $file,
                'filehash' => $fileHash,
                'user_id'  => $contract['user_id'],
                'metadata' => $contract['metadata'],
            ];

            $importFile[] = $contract_data;

            try {
                $con = $this->contract->save($contract_data);
                $this->updateTransMetadata($con);
                $this->log->activity('contract.log.save', ['contract' => $con->id], $con->id, $con->user_id);

                if ($con->metadata->is_supporting_document) {
                    $this->handleAssociatedDocument($con, $data);
                }

                if ($con) {
                    $this->queue->push('App\Nrgi\Services\Queue\ProcessDocumentQueue', ['contract_id' => $con->id]);
                }
                $this->info($i++.'- Contract successfully created : '.$con->title);
            } catch (Exception $e) {
                $this->error($e->getMessage());
                if ($this->storage->disk('s3')->exists($file)) {
                    $this->storage->disk('s3')->delete($file);
                }
            }

            $this->deleteFile($file);
        }

        file_put_contents(public_path('import.html'), json_encode($importFile));
        $this->info('file generated');
    }

    /**
     * Get Contract Json
     *
     * @return array
     */
    function getJson()
    {
        $spreadsheet_url  = 'https://docs.google.com/spreadsheets/u/1/d/1EGUDJvvK7rJglreBpeixM_OiM1m40GPb2Y_Hrs5fjco/pub?gid=65475573&single=true&output=csv';
        $spreadsheet_data = [];
        $i                = 0;
        if (($handle = fopen($spreadsheet_url, "r")) !== false) {
            while (($data = fgetcsv($handle, 10000, ",")) !== false) {
                $i++;

                if ($i == 1) {
                    continue;
                }

                if ($this->isSkippable($i)) {
                    continue;
                }

                if ($this->isLimitExceeded($i)) {
                    break;
                }

                $d = [];
                foreach ($data as $k => $v) {
                    $d[$this->fields[$k]] = $v;
                }

                $spreadsheet_data[] = $d;
            }
            fclose($handle);
        }

        return $spreadsheet_data;
    }

    /**
     * Determine limit exceeded
     *
     * @param $i
     *
     * @return bool
     */
    public function isLimitExceeded($i)
    {
        if ($this->limit < 1) {
            return false;
        }

        return ($i > ($this->limit + $this->skip + 1));
    }

    /**
     * Skip data
     *
     * @param $i
     *
     * @return bool
     */
    public function isSkippable($i)
    {
        if ($this->skip < 1) {
            return false;
        }

        return ($i <= ($this->skip + 1));
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
        if ($date == '') {
            return '';
        }

        $time = Carbon::createFromFormat('d/m/Y', $date);

        return $time->format($format);
    }

    /**
     * Download a Pdf File
     *
     * @param $pdf
     *
     * @return null|string
     */
    protected function downloadPdf($pdf)
    {
        list($fileName, $pdf) = $this->getFilePath($pdf);
        $temp_path = $this->tempPath($fileName);
        try {
            copy($pdf, $temp_path);

            return $fileName;
        } catch (\Exception $e) {
            $this->error('PDF Download Error: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Extract Contract Data
     *
     * @param $results
     *
     * @return array
     */
    protected function extractData($results)
    {
        $results             = trimArray($results);
        $contract            = config('metadata.schema');
        $company_template    = $contract['metadata']['company'][0];
        $contract['user_id'] = 1;

        $contract['metadata']['contract_name'] = $results['contract_name'];
        $companies                             = [];

        for ($i = 1; $i <= 5; $i++) {
            $company_default                                  = $company_template;
            $company_default['name']                          = $results['company_name_'.$i];
            $company_default['participation_share']           = $results['participation_share_'.$i];
            $company_default['jurisdiction_of_incorporation'] = $this->getJOI(
                $results['jurisdiction_of_incorporation_'.$i]
            );
            $company_default['registration_agency']           = $results['registration_agency_'.$i];
            $company_default['company_founding_date']         = $this->dateFormat($results['incorporation_date_'.$i]);
            $company_default['company_address']               = $results['company_address_'.$i];
            $company_default['company_number']                = $results['company_number_'.$i];
            $company_default['parent_company']                = $results['corporate_grouping_'.$i];
            $company_default['open_corporate_id']             = $results['open_corporates_link_'.$i];
            $company_default['operator']                      = $results['operator_'.$i];
            $data_filled                                      = false;
            foreach ($company_default as $v) {
                if ($v != '') {
                    $data_filled = true;
                }
            }
            $company_default['operator'] = ($company_default['operator'] == '') ? 1 : $company_default['operator'];
            if ($data_filled) {
                $companies[] = $company_default;
            }
        }
        $contract['metadata']['resource']                            = [$this->getResource($results['resource'])];
        $contract['metadata']['project_title']                       = $results['project_title'];
        $contract['metadata']['project_identifier']                  = $results['project_identifier'];
        $contract['metadata']['company']                             = $companies;
        $contract['metadata']['disclosure_mode']                     = 'Government';
        $contract['metadata']['type_of_contract']                    = array_filter(
            [$results['contract_type_1'], $results['contract_type_2']]
        );
        $contract['metadata']['document_type']                       = $results['document_type'];
        $contract['metadata']['date_retrieval']                      = $this->dateFormat($results['retrieval_date']);
        $contract['metadata']['concession'][0]['license_name']       = $results["license_name"];
        $contract['metadata']['concession'][0]['license_identifier'] = $results["license_identifier"];
        $contract['metadata']['government_entity'][0]['entity']      = $results["government_entity"];
        $contract['metadata']['government_entity'][0]['identifier']  = '';
        $contract['metadata']['country']                             = $this->getCountry($results['country']);
        $contract['metadata']['signature_date']                      = $this->dateFormat($results['signature_date']);
        $contract['metadata']['signature_year']                      = $this->dateFormat(
            $results['signature_date'],
            'Y'
        );
        $contract['metadata']['language']                            = $this->getLanguage(
            strtolower($results['language'])
        );
        $contract['metadata']['category']                            = ['rc'];
        $contract['metadata']['show_pdf_text']                       = Contract::SHOW_PDF_TEXT;
        $contract['metadata']['open_contracting_id']                 = $this->contract->generateOCID();
        $contract['metadata']['ckan']                                = 1;
        $contract['metadata']['is_supporting_document']              = $this->getSupportingDocumentType(
            $results["is_supporting_document"]
        );
        $contract['metadata']['pages_missing']                       = -1;
        $contract['metadata']['annexes_missing']                     = -1;
        $contract['metadata']['is_contract_signed']                  = 1;

        return trimArray($contract);
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
     * Get Language code
     *
     * @param $lang
     *
     * @return string
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
     * Get Supporting Document type
     *
     * @param $is_supporting_document
     *
     * @return int
     */
    protected function getSupportingDocumentType($is_supporting_document)
    {
        if (strtolower($is_supporting_document) == 'main') {
            return 0;
        }

        return 1;
    }

    /**
     * Get Temp Path
     *
     * @param $pdf_name
     *
     * @return string
     */
    protected function tempPath($pdf_name = '')
    {
        $path = storage_path('ckan');

        if (!is_dir($path)) {
            mkdir($path);
        }

        return sprintf('%s/%s', $path, $pdf_name);
    }

    /**
     * Delete pdf file.
     *
     * @param $file
     *
     * @return bool
     */
    protected function deleteFile($file)
    {
        return $this->filesystem->delete($this->tempPath($file));
    }

    /**
     * Handle Associated Document
     *
     * @param $contract
     * @param $data
     */
    protected function handleAssociatedDocument(Contract $contract, $data)
    {
        if ($data['is_supporting_document'] == 'Associated') {
            if (is_numeric($data['contract_name'])) {
                $parent = Contract::find($data['contract_name']);
            } else {
                $parent = Contract::whereRaw("metadata->>'ckan' = '1'")
                                  ->whereRaw("metadata->>'contract_name' = '".$data['contract_name']."'")
                                  ->first();
            }

            if ($parent) {
                $contract->syncSupportingContracts($parent->id);
                $this->contract->updateOCID($contract);
            }
        }
    }

    /**
     * Determine data can import or not
     *
     * @param $data
     *
     * @return bool
     */
    protected function isImportable($data)
    {
        if ($data['contract_name'] == '' && $data['is_supporting_document'] == 'Associated') {
            return false;
        }

        return true;
    }

    /**
     * Get File Path
     *
     * @param $pdf
     *
     * @return array
     */
    protected function getFilePath($pdf)
    {
        if ($this->externalPath != '') {
            $pdf      = explode('/', $pdf);
            $fileName = end($pdf);

            return [$fileName, $this->externalPath.$fileName];
        }

        return [md5(microtime()).'.pdf', $pdf];
    }

    /**
     * Get Jurisdiction of incorporation.
     *
     * @param $joi
     *
     * @return string
     * @throws Exception
     */
    protected function getJOI($joi)
    {
        if ($joi == '') {
            return "";
        }

        $countries = trans('codelist/country', [], null, 'fr');

        foreach ($countries as $code => $name) {
            if (strcasecmp($joi, $name) == 0) {
                return $code;
            }
        }

        throw new Exception($joi.' do not match with codelist');
    }

    /**
     * Update Metadata
     *
     * @param $contract
     */
    protected function updateTransMetadata($contract)
    {
        foreach (config('lang.translation') as $lang) {

            if ($lang['code'] == 'en') {
                continue;
            }

            $transData = [
                'contract_name'        => $this->contractService->refineContract(
                    $contract->metadata,
                    $contract->id,
                    $lang['code']
                ),
                'company'              => $contract->metadata->company,
                'project_title'        => $contract->metadata->project_title,
                'project_identifier'   => $contract->metadata->project_identifier,
                'concession'           => $contract->metadata->concession,
                'disclosure_mode_text' => $contract->metadata->disclosure_mode_text,
                'contract_note'        => '',
                'trans'                => $lang['code'],
            ];
            $this->contractService->updateContractTrans($contract->id, $transData);
        }
    }

    /**
     * Get Resource name in English
     *
     * @param $resourceInFr
     *
     * @return string
     * @throws Exception
     */
    protected function getResource($resourceInFr)
    {
        $resources = trans('codelist/resource', [], null, 'fr');
        foreach ($resources as $key => $resource) {
            if (strtolower($resource) == strtolower($resourceInFr)) {
                return $key;
            }
        }

        throw new Exception('Resource with given name '.$resourceInFr.' not found in codelist');
    }

}
