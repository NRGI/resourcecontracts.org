<?php
namespace App\Nrgi\Services\Contract;

use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Repositories\Contract\ContractRepositoryInterface;
use Exception;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Contracts\Filesystem\Factory as Storage;
use App\Nrgi\Entities\Contract\Annotation;

/**
 * Class migration
 * @package App\Nrgi\Services\Contract
 */
class MigrationService
{
    const UPLOAD_FOLDER = 'data/temp';
    /**
     * @var string
     */
    protected $raw_data;
    /**
     * @var array
     */
    protected $annotation_title = [];
    /**
     * @var
     */
    protected $country;
    /**
     * @var Log
     */
    protected $logger;
    /**
     * @var Filesystem
     */
    protected $filesystem;
    /**
     * @var Storage
     */
    protected $storage;
    /**
     * @var ContractService
     */
    protected $contract;
    /**
     * @var Queue
     */
    protected $queue;

    /**
     * @param CountryService              $country
     * @param ContractRepositoryInterface $contract
     * @param Queue                       $queue
     * @param Log                         $logger
     * @param Filesystem                  $filesystem
     * @param Storage                     $storage
     */
    public function __construct(
        CountryService $country,
        ContractRepositoryInterface $contract,
        Queue $queue,
        Log $logger,
        Filesystem $filesystem,
        Storage $storage
    ) {
        $this->country    = $country;
        $this->logger     = $logger;
        $this->filesystem = $filesystem;
        $this->storage    = $storage;
        $this->contract   = $contract;
        $this->queue      = $queue;
    }

    /**
     * @param null $file
     */
    public function setData($file = null)
    {
        $this->raw_data = file_get_contents($file);
    }

    /**
     * @return object
     */
    public function data()
    {
        return json_decode($this->raw_data)->document;
    }

    /**
     * Run Migration
     *
     * @return array
     */
    public function run()
    {
        $default_keys = array_keys(array_merge($this->contractMapping(), $this->annotationTitleMapping()));
        $default      = [];

        foreach ($default_keys as $key => $value) {
            $default[$value] = '';
        }

        $available = array_merge(
            $this->filterData($this->data(), $this->contractMapping()),
            $this->extractMetadata($this->data()->annotations, $this->annotationTitleMapping())
        );

        $contract              = array_merge($default, $available);
        $contract              = $this->refinery($contract);
        $contract              = json_decode(json_encode($contract));
        $contract->annotations = $this->refineAnnotation($this->data()->annotations);

        return $contract;

        if ($contract['pdf_url'] != '') {
            if ($pdf = $this->downloadPdf($contract['pdf_url'])) {
                if (!$this->isPdfExists($pdf)) {
                    $contract['file']      = $pdf;
                    $contract              = $this->getContractArray($contract);
                    $contract              = json_decode(json_encode($contract));
                    $contract->annotations = $this->refineAnnotation($this->data()->annotations);

                    return $contract;
                }
            }
        }

        return null;
    }

    /**
     * @return array
     */
    protected function contractMapping()
    {
        return [
            'language'              => 'language',
            'contract_name'         => 'title',
            'created_datetime'      => 'created_at',
            'last_updated_datetime' => 'updated_at',
            'pdf_url'               => ['resources', 'pdf'],
            'signature_date_main'   => ['data', 'Signature Date'],
            'signature_year_main'   => ['data', 'Signature Year'],
            'resources_main'        => ['data', 'Resource'],
            'country'               => ['data', 'Countries'],
            'documentcloud_url'     => 'canonical_url'
        ];
    }

    /**
     * @return array
     */
    protected function annotationTitleMapping()
    {
        return [
            'company'                 => [
                'Local company name',
                'Name and/or composition of executing company created or anticipated',
                'Name and/or composition of the company created or anticipated',
                'Name of company executing the document',
                'Name of company executing the document and composition of the shareholders',
                'Name of contracting company',
                'Signatories, company',
                'Other - [Parent company guarantee]',
            ],
            'contract_identifier'     => 'Legal Enterprise Identifier',
            'project_title'           => ['Project title', 'Project Title'],
            'signature_date'          => 'Date of contract signature',
            'signature_year'          => 'Year of contract signature',
            'type_of_contract'        => [
                'Type of document / right (Concession, Lease, Production Sharing Agreement, Service Agreement, etc.)',
                'Type of document / right (Concession, Lease, Production Sharing contract, Service contract, etc.)'
            ],
            'resources'               => [
                'Type of mining title associated with the contract',
                'Type of resources',
                'Type of resources (mineral type, crude oil, gas, etc.)',
                'Type of resources (mineral type, crude oil, gas, timber, etc.) OR specific crops planned (ex: food crops, oil palm, etc.)',
                'Type of resources (mineral type, crude oil, gas, timber, etc.) OR specific crops planned (ex: food crops, oil palm, etc.)'
            ],
            'license_concession_name' => 'Name and/or number of field, block or deposit',
            'government_entities'     => [
                'State agency, National Company, Ministry',
                'State agency, national company, ministry executing the document'
            ]
        ];
    }

    /**
     * @param string $title
     * @return int|null|string
     */
    protected function getKeyIfValid($title = '')
    {
        foreach ($this->annotationTitleMapping() as $key => $value) {

            if (is_array($value)) {

                foreach ($value as $k => $v) {
                    $v = trim($v);

                    if ($this->isStringMatch($title, $v)) {
                        return $key;
                    }
                }

            } else {
                $value = trim($value);

                if ($this->isStringMatch($title, $value)) {
                    return $key;
                }
            }

        }

        return null;
    }


    /**
     * check if two string match
     *
     * @param $string1
     * @param $string2
     * @return bool
     */
    protected function isStringMatch($string1, $string2)
    {
        if (strcasecmp($string1, $string2) == 0) {
            return true;
        }

        return false;
    }

    /**
     * @param $data
     * @param $grab
     * @return array
     */
    protected function filterData($data, $grab)
    {
        $return = [];
        foreach ($grab as $key => $map) {
            if (is_string($map) && property_exists($data, $map)) {
                $return[$key] = $data->$map;
                continue;
            }

            if (is_array($map)) {
                list($parent, $child) = $map;
                if (property_exists($data, $parent) && property_exists($data->$parent, $child)) {
                    $return[$key] = $data->$parent->$child;
                }
            }
        }

        return $return;
    }


    /**
     * @param $annotations
     * @return array
     */
    protected function extractMetadata($annotations)
    {
        $return = [];

        foreach ($annotations as $key => $value) {
            $title = $this->getAnnotationTitle($value->title);

            if (!empty($title)) {
                $this->annotation_title[] = $title;

                if ($key = $this->getKeyIfValid($title)) {
                    $return[$key][] = $value->content;
                }
            }
        }

        return $return;
    }

    /**
     * @return array
     */
    public function getTitles()
    {
        return $this->annotation_title;
    }

    /**
     * Get Contract Array
     *
     * @param $data
     * @return array
     */
    protected function getContractArray($data)
    {
        $contract = config('metadata.schema');

        $company_template = $contract['metadata']['company'][0];

        $contract['user_id']               = 1;
        $contract['file']                  = $data['file'];
        $contract['filehash']              = getFileHash($this->getMigrationFile($data['file']));
        $contract['metadata']['file_size'] = filesize($this->getMigrationFile($data['file']));
        $contract['created_datetime']      = $data['created_datetime'];
        $contract['last_updated_datetime'] = $data['last_updated_datetime'];

        $contract['metadata']['language']       = $data['language'];
        $contract['metadata']['signature_date'] = $data['signature_date'];
        $contract['metadata']['signature_year'] = $data['signature_year'];

        $contract['metadata']['contract_name']                 = $data['contract_name'];
        $contract['metadata']['resource']                      = $data['resources'];
        $contract['metadata']['country']                       = $data['country'];
        $contract['metadata']['contract_identifier']           = $data['contract_identifier'];
        $contract['metadata']['project_title']                 = $data['project_title'];
        $contract['metadata']['type_of_contract']              = $data['type_of_contract'];
        $contract['metadata']['concession'][0]['license_name'] = $data['license_concession_name'];
        $contract['metadata']['documentcloud_url']             = $data['documentcloud_url'];
        $contract['metadata']['category']                      = ['rc'];

        $company_arr = array_map('trim', $data['company_name']);

        $companies = [];
        if (empty($company_arr)) {
            $companies[] = $company_template;
        } else {
            foreach ($company_arr as $company) {
                $company_template['name'] = $company;
                $companies[]              = $company_template;
            }

        }
        $contract['metadata']['company'] = $companies;

        return $contract;
    }

    /**
     * Get Contract Array
     *
     * @param $data
     * @param $contract
     * @return array
     */
    public function buildContractMetadata($data, $contract)
    {
        $contractSchema = config('metadata.schema');
        $metadata       = json_decode(json_encode($contract->metadata), true);
        // $metadata['language']       = $data['n_language'];
        // $metadata['signature_date'] = $data['n_signature_date'];
        // $metadata['signature_year'] = $data['n_signature_year'];

        // $metadata['contract_name']                 = $data['m_contract_name'];
        // $metadata['resource']                      = array_map('trim', explode(",", $data['n_resources']));
        // $metadata['country']                       = $this->getCountryArray($data['n_country']);
        // $metadata['project_title']                 = $data['n_project_title'];
        $metadata['type_of_contract'] = $this->removeAfterDash($data['n_type_of_contract']);
        // $metadata['concession'][0]['license_name'] = $data['n_license_concession_name'];
        // $metadata['government_entity']             = $data['n_government_entities'];

        $company_arr               = array_map('trim', explode(",", $data['n_company']));
        $company_jurisdictions_arr = array_map('trim', explode(",", $data['n_company_jurisdictions']));
        $corporate_group_arr       = array_map('trim', explode(",", $data['n_corporate_group']));
        $company_identifier_arr    = array_map('trim', explode(",", $data['n_company_identifier']));
        $opencorporates_id_arr     = array_map('trim', explode(",", $data['n_opencorporates_id']));
        $companies                 = [];
        $company_template          = $contractSchema['metadata']['company'][0];
        if (empty($company_arr)) {
            $companies[] = $company_template;
        } else {
            foreach ($company_arr as $key => $company) {
                $company_template['name']                  = $company;
                $company_template['company_jurisdictions'] = (array_key_exists(
                    $key,
                    $company_jurisdictions_arr

                )) ? $company_jurisdictions_arr[$key] : "";
                $company_template['corporate_group']       = (array_key_exists(
                    $key,
                    $corporate_group_arr

                )) ? $corporate_group_arr[$key] : "";
                $company_template['company_identifier']    = (array_key_exists(
                    $key,
                    $company_identifier_arr

                )) ? $company_identifier_arr[$key] : "";
                $company_template['opencorporates_id']     = (array_key_exists(
                    $key,
                    $opencorporates_id_arr

                )) ? $opencorporates_id_arr[$key] : "";
                $companies[]                               = $company_template;

            }

        }

        //$metadata['company'] = $companies;

        return $metadata;
    }

    public function cleanString($string)
    {
        return str_replace(["\n", "\r"], '', $string);
    }

    /**
     * Check if pdf file exists
     *
     * @param $file
     * @return bool
     */
    protected function isPdfExists($file)
    {
        $file     = $this->getMigrationFile($file);
        $fileHash = getFileHash($file);

        if ($con = $this->contract->getContractByFileHash($fileHash)) {
            $this->filesystem->delete($file);

            return true;
        }

        return false;
    }


    /**
     * Upload Pdf to s3 and create contracts
     *
     * @return null|Contract
     */
    public function uploadPdfToS3AndCreateContracts($contract)
    {
        try {
            $this->storage->disk('s3')->put(
                $contract->file,
                $this->filesystem->get($this->getMigrationFile($contract->file))
            );
        } catch (Exception $e) {
            $this->logger->error(sprintf('File could not be uploaded : %s', $e->getMessage()));

            return false;
        }

        $data = [
            'file'     => $contract->file,
            'filehash' => $contract->filehash,
            'user_id'  => $contract->user_id,
            'metadata' => $contract->metadata,
        ];

        try {
            $con = $this->contract->save($data);

            $this->logger->activity('contract.log.save', ['contract' => $con->id], $con->id, $con->user_id);

            if ($con) {
                $this->queue->push('App\Nrgi\Services\Queue\ProcessDocumentQueue', ['contract_id' => $con->id]);
            }

            $this->logger->info(
                'Contract successfully created.',
                ['Contract Title' => $con->title]
            );
            $this->filesystem->delete($this->getMigrationFile($contract->file));

            return $con;
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            if ($this->storage->disk('s3')->exists($contract->file)) {
                $this->storage->disk('s3')->delete($contract->file);
            }
            $this->filesystem->delete($this->getMigrationFile($contract->file));

            return null;
        }

    }

    /**
     * Get Migration File
     *
     * @param string $fileName
     * @return string
     */
    public function getMigrationFile($fileName = '')
    {
        return sprintf('%s/%s', public_path(static::UPLOAD_FOLDER), $fileName);
    }

    /**
     * Download a Pdf File
     *
     * @param $pdf
     * @return null|string
     */
    protected function downloadPdf($pdf)
    {
        $pdf_name  = sha1(str_random()) . '.pdf';
        $temp_path = $this->getMigrationFile($pdf_name);

        try {
            copy($pdf, $temp_path);

            return $pdf_name;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            return null;
        }
    }

    /**
     * @param $contract
     * @param $annotations
     */
    public function saveAnnotations($contract, $annotations)
    {
        $annotationData = [];
        foreach ($annotations as $annotationArr) {
            $annotation                     = [];
            $annotation['contract_id']      = $contract->id;
            $annotation['annotation']       = json_encode($annotationArr['annotation']);
            $annotation['url']              = "";
            $annotation['user_id']          = 1;
            $annotation['document_page_no'] = $annotationArr['document_page_no'];
            $annotation['created_at']       = date('Y-m-d H:i:s');
            $annotation['updated_at']       = date('Y-m-d H:i:s');
            $annotationData[]               = $annotation;
        }


        Annotation::insert($annotationData);
    }

    /**
     * Convert annotation rectangle point from document cloud to annotorious plugin
     *
     * @param $data
     * @return array
     */
    public function convertPoint($data)
    {
        $rcheight = 845;
        $rcwidth  = 575;
        $dcheight = 1000;
        $dcwidth  = 700;
        $y        = $data[0] * $rcheight / $dcheight;
        $x        = $data[3] * $rcwidth / $dcwidth;
        $width    = $data[1] * $rcwidth / $dcwidth;
        $height   = $data[2] * $rcheight / $dcheight;
        list($x, $y) = $this->transform($x, $y);
        list($width, $height) = $this->transform($width, $height);

        return ["x" => $x, "width" => ($width - $x), "y" => $y, "height" => ($height - $y)];
    }

    /**
     * @param $x
     * @param $y
     * @return array
     */
    public function transform($x, $y)
    {
        $imageHeight = 842;
        $imageWidth  = 575;

        return [$x / $imageWidth, $y / $imageHeight];
    }

    /**
     * @param $data
     * @param $annotation
     */
    public function buildAnnotation($annotation)
    {
        $data['url']              = "";
        $data['text']             = $annotation->content;
        $position                 = explode(',', $annotation->location->image);
        $data['shapes']           = [
            [
                "type"     => "rect",
                "geometry" => $this->convertPoint($position)
            ]
        ];
        $category                 = $this->refineAnnotationCategory($annotation->title);
        $data['category']         = ($category == '') ? 'General Information' : $category;
        $data['tags']             = ($category == '') ? [$this->getAnnotationTitle($annotation->title)] : [];
        $data['page']             = $annotation->page;
        $data['document_page_no'] = $annotation->page;
        $data['id']               = "";

        return $data;
    }


    protected function refinery($contract)
    {
        $signature_date = $this->refineSignatureDate($contract['signature_date_main']);
        $signature_year = $this->refineSignatureYear($contract['signature_year_main'], $signature_date);

        $contract['converted'] = [
            'language'                => $this->refineLanguage($contract['language']),
            'contract_name'           => trim(trim($contract['contract_name']), '"'),
            'created_datetime'        => date('Y-m-d H:i:s', strtotime($contract['created_datetime'])),
            'last_updated_datetime'   => date('Y-m-d H:i:s', strtotime($contract['last_updated_datetime'])),
            'pdf_url'                 => $contract['pdf_url'],
            'documentcloud_url'       => $contract['documentcloud_url'],
            'project_title'           => $this->removeAfterDash($contract['project_title']),
            'government_entities'     => $this->removeAfterDash($contract['government_entities']),
            'contract_identifier'     => $this->removeAfterDash($contract['contract_identifier']),
            'signature_date'          => $signature_date,
            'signature_year'          => $signature_year,
            'company_name'            => $this->refineCompanyName($contract['company']),
            'resources'               => $this->refineResources($contract['resources_main'], $contract['resources']),
            'country'                 => $this->refineCountry($contract['country']),
            'type_of_contract'        => $this->refineToc($contract['type_of_contract']),
            'license_concession_name' => $this->removeAfterDash($contract['license_concession_name']),
        ];

        return $contract['converted'];
    }


    protected function refineSignatureDate($signature_date)
    {
        $signature_date = trim($signature_date, '"');
        $signature_date = trim($signature_date);
        $signature_date = str_replace('.', '/', $signature_date);

        if ($signature_date != '') {

            if (strlen($signature_date) == 4) {
                return $signature_date;
            }

            if (strlen($signature_date) == 7) {
                $signature_date = date_create_from_format('m/Y', $signature_date);

                return date_format($signature_date, 'Y-m');
            }

            try {
                $signature_date = date_create_from_format('d/m/Y', $signature_date);
                $signature_date = date_format($signature_date, 'Y-m-d');

            } catch (Exception $e) {
                dd($signature_date);
            }

        }

        return $signature_date;
    }

    protected function refineSignatureYear($signature_year_main, $signature_date)
    {
        $signature_year = trim($signature_year_main, '"');
        $signature_year = trim($signature_year);

        if (is_numeric($signature_year) && strlen($signature_year) == 4) {
            return $signature_year;
        }

        if ($signature_date != '') {
            return date('Y', strtotime($signature_date));
        }
    }

    function refineCompanyName($company)
    {
        $return = [];

        if (is_string($company)) {
            $company = [$company];
        }

        foreach ($company as $name) {


            if ($name != '') {
                $name = $this->removeAfterDash($name);
                $name = trim($name, '"');
                $name = explode('"', $name);
                $name = $name[0];
                $name = explode('(', $name);
                $name = $name[0];

                $name = explode(',', $name);
                $name = trim($name[0]);

                if ($this->ignoreText($name)) {
                    continue;
                }

                $return[] = trim($name);
            }
        }


        return array_unique($return);
    }

    /**
     * Remove string after dash
     *
     * @param $string
     * @return string
     */
    protected function removeAfterDash($string)
    {
        if (is_array($string)) {
            $string = array_unique($string);
            if (count($string) > 1) {
                dd($string);
            }
            $string = $string[0];
        }

        $arr    = explode('--', $string);
        $string = $arr[0];
        $string = trim($string, '"');
        $string = trim($string);

        return $string;
    }

    protected function refineLanguage($language)
    {
        return ('eng' == $language) ? 'EN' : $language;
    }

    protected function refineResources($resource_main, $resource)
    {
        $resource = is_array($resource) ? $resource : [$resource];
        $res_arr  = explode(',', $resource_main);
        $res_arr  = array_map('trim', $res_arr);
        foreach ($resource as $val) {
            if ($val != '') {
                $res_arr[] = $val;
            }
        }
        $res_arr   = $res_arr + $resource;
        $valid_res = [];

        foreach ($res_arr as $resource) {
            if ($res = $this->extractResource($resource)) {
                if (is_array($res)) {
                    foreach ($res as $r) {
                        $valid_res[] = $r;
                    }
                } else {
                    $valid_res[] = $res;
                }
            }

        }

        return array_unique($valid_res);
    }

    protected function refineCountry($country)
    {
        return $this->country->getCountryByName($country);
    }

    protected function extractResource($resource)
    {
        $resource_list = trans('codelist/resource');
        $resource      = $this->removeAfterDash($resource);
        $match         = [];
        $wildCard      = [
            'Aluminium'             => 'Alumine',
            'Base metals'           => 'Les metaux de base',
            'Bauxite'               => 'Bauxite',
            'Coal'                  => 'Charbon',
            'Cobalt'                => 'Cobalt',
            'Copper'                => 'Cuivre',
            'Crude oil'             => 'Huile brute',
            'Diamonds'              => 'Diamants',
            'Gas'                   => 'Gaz',
            'Gold'                  => 'Or',
            'Iron ore'              => 'Fer',
            'Lapis lazuli'          => 'Lapis-lazuli',
            'Lead'                  => 'Plomb',
            'Lithium'               => 'Lithium',
            'Manganese'             => 'Manganèse',
            'Molybdenum'            => 'Molybdène',
            'Nickel'                => 'Nickel',
            'Phosphate rock'        => 'Rroche phosphatée',
            'Platinum Group Metals' => 'Métaux du groupe du platine',
            'Precious stones'       => 'Pierres précieuses',
            'Quartz'                => 'Quartz',
            'Semi-precious stones'  => 'Pierres semi précieuses',
            'Silver'                => 'Argent',
            'Titanium'              => 'Titane',
            'Uranium'               => 'Uranium',
            'Zinc'                  => 'Zinc',
            'Citrus'                => 'Citrus',
            'Food crops'            => 'Les cultures vivrières',
            'Hydrocarbons'          => 'Hydrocarbures',
            'Palm oil'              => 'Huile de palme',
            'Rubber'                => 'Caoutchouc',
            'Soy'                   => 'Soja',
            'Sugar'                 => 'Sucre',
            'Timber'                => 'Bois d\'œuvre'
        ];


        if ($resource == '') {
            return '';
        }

        foreach ($resource_list as $key => $val) {

            if ($this->isStringMatch($resource, $val)) {
                return $val;
            }

            if (stripos($resource, $val) !== false) {
                $match[] = $val;
            }

            if (stripos($val, $resource) !== false) {
                $match[] = $val;
            }
        }

        foreach ($wildCard as $key => $val) {
            if (stripos($val, $resource) !== false) {
                $match[] = $key;
            }
        }

        $match = array_unique($match);

        if (count($match) > 0) {
            return $match;
        }

        return '';
    }

    /**
     * Extract Type of Contract
     *
     * @param $toc
     * @return string
     */
    protected function refineToc($toc)
    {
        $toc_string = $this->removeAfterDash($toc);

        if ($toc_string == '') {
            return '';
        }

        $toc_list = trans('codelist/contract_type');

        foreach ($toc_list as $key => $val) {

            if ($this->isStringMatch($val, $toc_string)) {
                return $toc_string;
            }

        }

        $wildCard = ['Production or Profit Sharing Agreement' => ['sharing', 'production']];

        foreach ($wildCard as $key => $val) {
            if (is_array($val)) {
                foreach ($val as $v) {
                    if (stripos($toc_string, $v) !== false) {
                        return $key;
                    }
                }
            } else {
                if (stripos($toc_string, $val) !== false) {
                    return $key;
                }
            }
        }

        return '';
    }


    public function cscExport(array &$array)
    {
        $filename = "data_export_" . date("Y-m-d") . ".csv";

        // disable caching
        $now = gmdate("D, d M Y H:i:s");
        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
        header("Last-Modified: {$now} GMT");

        // force download
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");

        // disposition / encoding on response body
        header("Content-Disposition: attachment;filename={$filename}");
        header("Content-Transfer-Encoding: binary");


        if (count($array) == 0) {
            return null;
        }
        ob_start();
        $df = fopen("php://output", 'w');
        fputcsv($df, array_keys(reset($array)));
        foreach ($array as $row) {
            fputcsv($df, $row);
        }
        fclose($df);

        return ob_get_clean();
    }

    public function ignoreText($name)
    {
        $words = [' the ', ' an ', ' will ', ' is '];

        foreach ($words as $word) {
            if (stripos($name, $word) !== false) {
                return true;
            }
        }
    }

    public function refineAnnotation($annotations)
    {
        $annotationArr = [];

        foreach ($annotations as $annotationObj) {
            $annotation['annotation'] = $this->buildAnnotation($annotationObj);;
            $annotation['document_page_no'] = $annotationObj->page;
            $annotationArr[]                = $annotation;
        }

        return $annotationArr;
    }


    public function refineAnnotationCategory($title)
    {
        $title    = $this->getAnnotationTitle($title);
        $mappings = config('annotation_mapping');
        $title    = trim(trim($title), ',');
        foreach ($mappings as $key => $map) {
            $key = trim(trim($key), ',');
            $from  = [' / ', '  ', '/ ', ' /', ' - ', '- ', ' -'];
            $to    = ['/', ' ', '/', '/', '-', '-', '-'];
            $title = str_replace($from, $to, $title);
            $key   = str_replace($from, $to, $key);

            if ($this->isStringMatch($title, $key) !== false) {
                //echo $map;
                //echo PHP_EOL . '-----------------------------------' . PHP_EOL;
                return $map;
            }
        }

        if ($title != '') {
            echo $title;
            echo PHP_EOL . '-----------------------------------' . PHP_EOL;
        }

        return '';
    }


    function getAnnotationTitle($title)
    {
        $title = explode('//', $title);

        if (isset($title[1])) {
            return $title[1];
        }

        return '';
    }

    function getCountryArray($countryData)
    {
        $countryData = explode("name:", $countryData);
        $countryCode = explode(":", $countryData[0])[1];
        $countryCode = trim($countryCode);
        $countryCode = trim($countryCode, ',');

        $countryName = $countryData[1];

        return [
            'code' => trim($countryCode),
            "name" => trim($countryName)
        ];
    }

}
