<?php
namespace App\Nrgi\Services\Contract;

use App\Nrgi\Repositories\Contract\ContractRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use App\Nrgi\Entities\Contract\Annotation;

/**
 * Class EthiopianMigrationService
 * @package App\Nrgi\Services\Contract
 */
class EthiopianMigrationService
{
    const UPLOAD_FOLDER = 'data/temp';

    protected $raw_data;

    protected $contract_name;

    protected $file_type;

    protected $file_name;

    protected $pdf_url;
    protected $excel_metadata;

    /**
     * @var Log
     */
    protected $logger;
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var ContractService
     */
    protected $contract;
    /**
     * @var CountryService
     */
    protected $country;

    /**
     * @param CountryService              $country
     * @param ContractRepositoryInterface $contract
     * @param Log                         $logger
     * @param Filesystem                  $filesystem
     */
    public function __construct(
        ContractRepositoryInterface $contract,
        Log $logger,
        Filesystem $filesystem,
        CountryService $country
    ) {
        $this->logger     = $logger;
        $this->filesystem = $filesystem;
        $this->contract   = $contract;

        $this->country = $country;
    }


    /**
     * @param $data
     * @return mixed
     */
    public function setData($data)
    {
        return $this->raw_data = $data;
    }

    /**
     * @param $data
     * @return mixed
     */
    public function setExcelMetadata($data)
    {
        return $this->excel_metadata = $data;
    }

    /**
     * @param $fileName
     * @return mixed
     */
    public function setFileName($fileName)
    {
        $this->file_name = $fileName;

        return $this;
    }

    /**
     * @param $fileName
     * @return mixed
     */
    public function setPdfUrl($pdf_url)
    {
        $this->pdf_url = $pdf_url;

        return $this;
    }


    /**
     * @param $contract_name
     * @return object
     */
    public function setContractName($contract_name)
    {
        return $this->contract_name = $contract_name;
    }

    /**
     * @param $fileType
     * @return $this
     */
    public function setFileType($fileType)
    {
        $this->file_type = $fileType;

        return $this;
    }

    /**
     * Run Migration
     *
     * @return array
     */
    public function run()
    {
        $metadataFromAnnotations  = $this->getMetaDataFromAnnotation();
        $metadata['annotation']   = $this->extractMetadataFromAnnotation($metadataFromAnnotations);
        $metadata['metadata']     = $this->filterData($this->getMetaDataFromMetadata(), $this->metadataMapping());
        $metadata['metadata_new'] = $this->excel_metadata;

        $metadata ['contract_name'] = $this->contract_name;
        $metadata['file_name']      = $this->file_name;
        $metadata['pdf_url']        = $this->pdf_url;
        $metadata['annotations']    = $this->getAnnotations();

        return $metadata;
    }


    /**
     * Run Migration
     *
     * @return array
     */
    public function setupContract($contract)
    {
        if ($contract['pdf_url'] != '') {
            if ($pdf = $this->downloadPdf($contract['pdf_url'] . "?dl=1")) {
                if (!$this->isPdfExists($pdf)) {
                    $contract['file']        = $pdf;
                    $contract['data']        = $this->getContractArray($contract);
                    $contract['annotations'] = $this->refineAnnotation($contract['annotations']);

                    return $contract;
                }
            }
        }

        return null;
    }

    /**
     * @param $annotations
     * @return array
     */
    public function getAnnotationByPagePosition($annotations)
    {
        $annotationArray = [];
        foreach ($annotations as $annotationObj) {

            if (!is_null($annotationObj['page'])) {
                $annotationPages = $this->getPages($annotationObj['page']);
                foreach ($annotationPages as $pages) {
                    list($page, $position) = $this->getAnnotationPagePosition($pages);
                    $annotation['position']   = $position;
                    $annotation['page_no']    = $page;
                    $annotation['annotation'] = $annotationObj;

                    $annotationArray[] = $annotation;
                }
            }
        }

        $groupByPage = $this->groupAnnotation($annotationArray, 'page_no');
        foreach ($groupByPage as $page => $value) {
            $groupByPosition                 = $this->groupAnnotation($value, 'position');
            $groupByPage[$page]['positions'] = $groupByPosition;
        }

        return $groupByPage;
    }

    /**
     * @param $annotations
     * @return array
     */
    public function refineAnnotation($annotations)
    {
        dd($annotations);
        $annotationArray  = [];
        $annotationByPage = $this->getAnnotationByPagePosition($annotations);
        foreach ($annotationByPage as $page => $annotationObj) {
            foreach ($annotationObj['positions'] as $position => $annotations) {
                foreach ($annotations as $key => $annotationObj1) {
                    $annotation1['position']        = $position;
                    $annotation1['page_no']         = $page;
                    $annotation1['text']            = $annotationObj1['annotation']['text'];
                    $annotation1['category']        = $annotationObj1['annotation']['category'];
                    $annotation['annotation']       = $this->buildAnnotation($annotation1, $key);
                    $annotation['document_page_no'] = $page;
                    $annotationArray[]              = $annotation;
                }
            }

        }

        return $annotationArray;
    }

//    public function refineAnnotation($annotations)
//    {
//        $annotationArray = [];
//        $this->getAnnotationByPagePosition($annotations);
//        foreach ($annotations as $annotationObj) {
//
//            if (!is_null($annotationObj['page'])) {
//                $annotationPages = $this->getPages($annotationObj['page']);
//                //todo annotation group by page and position
//                foreach ($annotationPages as $pages) {
//                    list($page, $position) = $this->getAnnotationPagePosition($pages);
//                    $annotationObj['position']      = $position;
//                    $annotationObj['page_no']       = $page;
//                    $annotation['annotation']       = $this->buildAnnotation($annotationObj);
//                    $annotation['document_page_no'] = $page;
//
//                    $annotationArray[] = $annotation;
//                }
//            }
//        }
//
//        //$annotations     = $this->groupAnnotation($annotations);
//
//        return $annotationArray;
//    }

    /**
     * @param $annotations
     * return array
     */
    public function groupAnnotation($annotations, $key)
    {
        $return = array();
        foreach ($annotations as $val) {
            $return[$val[$key]][] = $val;
        }

        //dd($return);
        return $return;
    }

    /**
     * @param $contract_id
     * @param $annotations
     * @internal param $contract
     */
    public function saveAnnotations($contract_id, $annotations)
    {
        $annotationData = [];
        foreach ($annotations as $annotationArray) {

            $annotation                     = [];
            $annotation['contract_id']      = $contract_id;
            $annotation['annotation']       = json_encode($annotationArray['annotation']);
            $annotation['url']              = "";
            $annotation['user_id']          = 1;
            $annotation['document_page_no'] = $annotationArray['document_page_no'];
            $annotation['created_at']       = date('Y-m-d H:i:s');
            $annotation['updated_at']       = date('Y-m-d H:i:s');
            $annotationData[]               = $annotation;
        }

        Annotation::insert($annotationData);
    }

    /**
     * @param $pages
     * @return array
     */
    public function getPages($pages)
    {
        //$pages = "13 (middle, bottom), 17 (middle)";
        preg_match_all('/(\d+) \((.*?)\)/', $pages, $out);

        return $out[0];
    }

    /**
     * @param     $annotation
     * @param int $factor
     * @return
     * @internal param $data
     */
    public function buildAnnotation($annotation, $factor = 0)
    {
        $data['url']              = "";
        $data['text']             = $annotation['text'];
        $data['shapes']           = [
            [
                "type"     => "rect",
                "geometry" => $this->getPosition($annotation['position'], $factor)
            ]
        ];
        $category                 = $this->refineAnnotationCategory($annotation['category']);
        $data['category']         = ($category == '') ? 'i-general-information' : str_slug($category);
        $data['tags']             = ($category == '') ? [$annotation['category']] : [];
        $data['page']             = $annotation['page_no'];
        $data['document_page_no'] = $annotation['page_no'];
        $data['position']         = $annotation['position'];
        $data['id']               = "";

        return $data;
    }

    /**
     * Convert annotation rectangle point from document cloud to annotorious plugin
     *
     * @param $position
     * @param $factor
     * @return array
     * @internal param $data
     */
    public function getPosition($position, $factor)
    {
        $positionMapping = array(
            "top"       => array(
                'x'      => 0.018425460636516001 + ($factor * .1),
                'width'  => 0.065427135678391997,
                'y'      => 0.074408117249153999,
                'height' => 0.023821871476887998,
            ),
            "topmiddle" => array(
                'x'      => 0.03182579564489900 + ($factor * .1),
                'width'  => 0.065427135678391997,
                'y'      => 0.51747463359090901,
                'height' => 0.022694475760991999,
            ),
            "middle"    => array(
                'x'      => 0.031825795644890999 + ($factor * .1),
                'width'  => 0.065427135678391997,
                'y'      => 0.51747463359639001,
                'height' => 0.022694475760991999,
            ),
            "bottom"    => array(
                'x'      => 0.023450586264657 + ($factor * .1),
                'width'  => 0.065427135678391997,
                'y'      => 0.89740698985344003,
                'height' => 0.027204058624576999,
            ),
            "topbottom" => array(
                'x'      => 0.023450586264657 + ($factor * .1),
                'width'  => 0.065427135678391997,
                'y'      => 0.89740698985344003,
                'height' => 0.027204058624576999,
            ),
        );

        return $positionMapping[$position];
    }

    /**
     * @param $title
     * @return string
     */
    public function refineAnnotationCategory($title)
    {
        $mappings = config('annotation_mapping');
        $title    = trim(trim($title), ',');
        foreach ($mappings as $key => $map) {
            $key = trim(trim($key), ',');

            if ($this->isStringMatch($title, $key) !== false) {
                return $map;
            }
        }

        return '';
    }


    /**
     * Get Contract Array
     *
     * @param $data
     * @return array
     */
    protected function getContractArray($data)
    {
        $contract                  = config('metadata.schema');
        $company_template          = $contract['metadata']['company'][0];

        $contract['user_id']                                   = 1;
        $contract['file']                                      = $data['file'];
        $contract['filehash']                                  = getFileHash($this->getMigrationPdfFile($data['file']));
        $contract['metadata']['file_size']                     = filesize($this->getMigrationPdfFile($data['file']));
        $contract['metadata']['contract_name']                 = $data['metadata_new']['m_contract_name'];
        $contract['metadata']['language']                      = "EN";
        $contract['metadata']['signature_date']                = $data['metadata_new']['n_signature_date']; //$this->getMetadataByKey($data, 'signature_date');
        $contract['metadata']['signature_year']                = $this->getMetadataByKey($data, "signature_year");
        $contract['metadata']['resource']                      = explode(',',$data['metadata_new']['n_resources']);//[$this->getMetadataByKey($data, "resource")];
        $contract['metadata']['country']                       = $this->country->getCountryByName(
            $data['metadata_new']['n_country']
        );
        $contract['metadata']['contract_identifier']           = $this->getMetadataByKey($data, "contract_identifier");
        $contract['metadata']['project_title']                 = $data['metadata_new']['n_project_title'];//$this->getMetadataByKey($data, "project_title");
        $contract['metadata']['type_of_contract']              = $data['metadata_new']['n_type_of_contract'];//$this->getMetadataByKey($data, "type_of_contract");
        $contract['metadata']['concession'][0]['license_name'] =$data['metadata_new']['n_license_concession_name'];// $this->getMetadataByKey($data, "license_name");
        //$contract['metadata']['government_entity']             = $this->getMetadataByKey($data, "government_entity");
        $contract['metadata']['category']                      = ['olc'];

        $company_arr = array_map('trim', explode(',',$data['metadata_new']['n_company']));

        $companies = [];
        if (empty($company_arr)) {
            $companies[] = $company_template;
        } else {
            foreach ($company_arr as $company) {
                if (!is_null($company)) {
                    $company_template['name'] = $company;
                    $companies[]              = $company_template;
                }
            }

        }
        $contract['metadata']['company'] = $companies;

        $goverment_entity_template = $contract['metadata']['government_entity'][0];

        $goverment_entity_arr = array_map('trim', explode(',',$data['metadata_new']['n_government_entities']));

        $companies = [];
        if (empty($goverment_entity_arr)) {
            $companies[] = $goverment_entity_template;
        } else {
            foreach ($goverment_entity_arr as $goverment_entity) {
                if (!is_null($goverment_entity)) {
                    $goverment_entity_template['entity'] = $goverment_entity;
                    $government_entities[]              = $goverment_entity_template;
                }
            }

        }
        $contract['metadata']['government_entity'] = $government_entities;

        return $contract;
    }

    /**
     * Check if pdf file exists
     *
     * @param $file
     * @return bool
     */
    public function isPdfExists($file)
    {
        $file     = $this->getMigrationPdfFile($file);
        $fileHash = getFileHash($file);

        if ($con = $this->contract->getContractByFileHash($fileHash)) {
            $this->filesystem->delete($file);

            return true;
        }

        return false;
    }

    /**
     * Download a Pdf File
     *
     * @param $pdf
     * @return null|string
     */
    public function downloadPdf($pdf)
    {
        $pdf_name  = sha1(str_random()) . '.pdf';
        $temp_path = $this->getMigrationPdfFile($pdf_name);

        try {
            copy($pdf, $temp_path);

            return $pdf_name;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            return null;
        }
    }

    /**
     * @param $annotations
     * @return array
     */
    protected function extractMetadataFromAnnotation($annotations)
    {
        $metadata = [];

        foreach ($annotations as $key => $value) {
            $title = trim($key);
            if (!empty($title)) {
                if ($key = $this->getKeyIfValid($title)) {
                    $metadata[$key][] = $value;
                }
            }
        }

        return $metadata;
    }


    /**
     * @return array
     */
    public function getMetaDataFromAnnotation()
    {
        $metadata = array();
        foreach ($this->raw_data['Categories'] as $key => $annotation_raw_data) {
            if (strlen($annotation_raw_data['english']) > 0) {
                $detail_key = "details";
                if ($this->file_type == "xlsm") {
                    $detail_key = "details_value";
                }
                $metadata[$annotation_raw_data['english']] = $annotation_raw_data[$detail_key];
            }
        }

        return $metadata;
    }


    /**
     * @return array
     */
    public function getMetaDataFromMetadata()
    {
        $metadata = array();
        foreach ($this->raw_data['Metadata'] as $key => $annotation_raw_data) {
            if (strlen($annotation_raw_data['category']) > 0) {
                $metadata[$annotation_raw_data['category']] = $annotation_raw_data['terms'];
            }
        }

        return $metadata;
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
    protected function getKeyIfValid($title)
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
     * @param $data
     * @param $grab
     * @return array
     */
    protected function filterData($data, $grab)
    {
        $return = [];
        foreach ($grab as $key => $map) {
            if (is_string($map) && isset($data[$map])) {
                $return[$key] = $data[$map];
                continue;
            }

            if (is_array($map)) {
                list($parent, $child) = $map;
                if (isset($data[$parent]) && isset($data[$parent][$child])) {
                    $return[$key] = $data[$parent][$child];
                }
            }
        }

        return $return;
    }

    /**
     * @return array
     */
    protected function metadataMapping()
    {
        if ($this->file_type == "xlsm") {
            return $this->xlsmMetadataMapping();
        }

        return [
            'country'          => 'Countries',
            'resource'         => 'Resource',
            'type_of_contract' => 'Type of Mining Title',
            'signature_date'   => 'Signature Date',
            'signature_year'   => 'Signature Year',
        ];
    }

    public function xlsmMetadataMapping()
    {
        return [
            'country'                 => 'country',
            'resource'                => 'resource',
            'type_of_contract'        => 'Type of Mining Title',
            'signature_date'          => 'Signature Date',
            'signature_year'          => 'Signature Year',
            'company'                 => 'Local company name',
            'contract_identifier'     => 'Legal Enterprise Identifier',
            'project_title'           => 'Project title',
            'license_concession_name' => 'Name and/or number of field, block or deposit',
        ];
    }

    /**
     * @return array
     */
    public function getAnnotations()
    {
        $data = array();
        foreach ($this->raw_data['Categories'] as $key => $annotation_raw_data) {
            if ($this->file_type == "xlsm") {
                $page = $annotation_raw_data['page_permalink_page_page_top_middle_bottom'];
                $text = $annotation_raw_data['details_value'];
            } else {
                $page = $annotation_raw_data['page_permalink'];
                $text = $annotation_raw_data['details'];
            }
            $annotation['page'] = $page;
            $annotation['articlereference'] = $annotation_raw_data['articlereference'];
            $annotation['text']     = $text;
            $annotation['category'] = $annotation_raw_data['english'];
            $data[]                 = $annotation;
        }

        return $data;
    }

    /**
     * @param $string
     * @return array
     */
    public function getAnnotationPagePosition($string)
    {
        list ($page, $position) = explode(" ", trim($string));
        $my_val   = array('(', ')', '-', ',', ';');
        $position = strtolower(str_replace($my_val, "", trim($position)));

        $position = trim($position);

        return [$page, $position];
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
     * Download a Pdf File
     *
     * @param $pdf
     * @return null|string
     */
    public function downloadExcel($url)
    {
        $fileName  = basename($url);
        $url       = $url . "?dl=1";
        $temp_path = $this->getMigrationFile($fileName);
        try {
            copy($url, $temp_path);
            $this->convertToCsv($fileName);

            return $fileName;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            return null;
        }
    }

    /**
     * @param $file
     * @return bool
     */
    public function convertToCsv($file)
    {
        $command = sprintf('xlsx2csv %s  "%s" -a -i', $this->getMigrationFile($file), $this->getConvertedDir($file));
        $process = new Process($command);
        $process->setTimeout(360 * 10);
        $process->start();
        while ($process->isRunning()) {
            echo $process->getIncrementalOutput();
        }
        if (!$process->isSuccessful()) {
            echo("error while executing command.{$process->getErrorOutput()}");
            throw new \RuntimeException($process->getErrorOutput());
        }

        return true;
    }

    /**
     * Get Migration File
     *
     * @param string $fileName
     * @return string
     */
    public function getMigrationPdfFile($fileName = '')
    {
        return sprintf('%s/%s', public_path(static::UPLOAD_FOLDER), $fileName);
    }

    /**
     * Get Migration File
     *
     * @param string $fileName
     * @return string
     */
    public function getMigrationFile($fileName = '')
    {
        return sprintf('%s/%s', public_path("ethiopian-contracts/data/excels"), $fileName);
    }

    /**
     * Get Migration File
     *
     * @param string $fileName
     * @return string
     */
    public function getConvertedDir($fileName = '')
    {
        return sprintf('%s/%s', public_path("ethiopian-contracts/data/converted"), $fileName);
    }

    /**
     * @param $data
     * @return string
     */
    protected function getMetadataByKey($data, $key)
    {
        if (isset($data['metadata'][$key]) && !is_null($data['metadata'][$key])) {
            return $data['metadata'][$key];
        }

        if (isset($data['annotation'][$key]) && !is_null($data['annotation'][$key])) {
            return $data['annotation'][$key][0];
        }

        return "";
    }

    /**
     * Get Contract Array
     *
     * @param $data
     * @param $contract
     * @return array
     */
    public function updateContractMetadata($data, $contract)
    {

        $contractSchema             = config('metadata.schema');
        $metadata                   = json_decode(json_encode($contract->metadata), true);
        $metadata['signature_date'] = $this->getSignatureDate($data['n_signature_date']);
        //$metadata['signature_year'] = $data['n_signature_year'];
        $metadata['language'] = "en";

        $metadata['resource']                      = array_map('trim', explode(",", $data['n_resource']));
        $metadata['country']                       = $this->country->getCountryByName($data['n_country']);
        $metadata['project_title']                 = $data['n_project_title'];
        $metadata['type_of_contract']              = $data['n_type_of_contract'];
        $metadata['concession'][0]['license_name'] = $data['n_license_concession_name'];
        $metadata['government_entity']             = $data['n_government_entities'];

        $company_arr = array_map('trim', explode(",", $data['n_company']));
//        $company_jurisdictions_arr = array_map('trim', explode(",", $data['n_company_jurisdictions']));
//        $corporate_group_arr       = array_map('trim', explode(",", $data['n_corporate_group']));
//        $company_identifier_arr    = array_map('trim', explode(",", $data['n_company_identifier']));
//        $opencorporates_id_arr     = array_map('trim', explode(",", $data['n_opencorporates_id']));
        $companies        = [];
        $company_template = $contractSchema['metadata']['company'][0];
        if (empty($company_arr)) {
            $companies[] = $company_template;
        } else {
            foreach ($company_arr as $key => $company) {
                $company_template['name'] = $company;
//                $company_template['company_jurisdictions'] = (array_key_exists(
//                    $key,
//                    $company_jurisdictions_arr
//
//                )) ? $company_jurisdictions_arr[$key] : "";
//                $company_template['corporate_group']       = (array_key_exists(
//                    $key,
//                    $corporate_group_arr
//
//                )) ? $corporate_group_arr[$key] : "";
//                $company_template['company_identifier']    = (array_key_exists(
//                    $key,
//                    $company_identifier_arr
//
//                )) ? $company_identifier_arr[$key] : "";
//                $company_template['opencorporates_id']     = (array_key_exists(
//                    $key,
//                    $opencorporates_id_arr
//
//                )) ? $opencorporates_id_arr[$key] : "";
                $companies[] = $company_template;
            }

        }
        $metadata['company'] = $companies;

        return $metadata;
    }

    /**
     * @param $signature_date
     * @return bool|\DateTime|mixed|string
     */
    protected function getSignatureDate($signature_date)
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
}
