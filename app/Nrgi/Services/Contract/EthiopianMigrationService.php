<?php
namespace App\Nrgi\Services\Contract;

use App\Nrgi\Repositories\Contract\ContractRepositoryInterface;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Filesystem\Filesystem;

/**
 * Class EthiopianMigrationService
 * @package App\Nrgi\Services\Contract
 */
class EthiopianMigrationService
{
    protected $raw_data;

    protected $contract_name;

    protected $file_type;

    protected $file_name;

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
     * @param CountryService              $country
     * @param ContractRepositoryInterface $contract
     * @param Log                         $logger
     * @param Filesystem                  $filesystem
     */
    public function __construct(
        ContractRepositoryInterface $contract,
        Log $logger,
        Filesystem $filesystem
    ) {
        $this->logger     = $logger;
        $this->filesystem = $filesystem;
        $this->contract   = $contract;
    }

    /**
     * @return object
     */
    public function setData($data)
    {
        return $this->raw_data = $data;
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
        $metadataFromAnnotations = $this->getMetaDataFromAnnotation();
        $refinedMetadata         = $this->extractMetadataFromAnnotation($metadataFromAnnotations);

        $metadata     = $this->filterData($this->getMetaDataFromMetadata(), $this->metadataMapping());
        $m_metadata   = $this->addMPrifix($metadata);
        $a_metadata   = $this->addAPrifix($refinedMetadata);
        $contractInfo = ['contract_name' => $this->contract_name, 'file_name' => $this->file_name];
        $newMetadata  = array_merge($contractInfo, $m_metadata, $a_metadata);

        return $newMetadata;
    }

    public function addMPrifix($array)
    {
        return array_combine(
            array_map(
                function ($k) {
                    return "m_" . $k;
                },
                array_keys($array)
            ),
            $array
        );
    }

    public function addAPrifix($array)
    {
        return array_combine(
            array_map(
                function ($k) {
                    return "a_" . $k;
                },
                array_keys($array)
            ),
            $array
        );
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
     *
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
     *
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
        foreach ($this->raw_data as $key => $annotation_raw_data) {
            $annotation['category'] = $annotation_raw_data['english'];
            $annotation['page']     = $annotation_raw_data['page_permalink'];
            $annotation['text']     = $annotation_raw_data['details'];
            $data[]                 = $annotation;
        }

        return $data;
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

}
