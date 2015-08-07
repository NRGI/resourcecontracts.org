<?php
namespace App\Nrgi\Services\Contract;

/**
 * Class migration
 * @package App\Nrgi\Services\Contract
 */
class migrationService
{
    /**
     * @var string
     */
    protected $raw_data;
    /**
     * @var array
     */
    protected $annotation_title = [];

    /**
     * @param null $file
     */
    public function __construct($file = null)
    {
        if (is_null($file)) {
            if (isset($_GET['url']) && $_GET['url'] != '') {
                $file = $_GET['url'];
            } else {
                $file = './data.json';
            }
        }

        $this->raw_data = file_get_contents($file);
    }

    /**
     * @return mixed
     */
    public function data()
    {
        return json_decode($this->raw_data)->document;
    }

    /**
     * @return array
     */
    public function run()
    {
        $default_keys = array_keys(array_merge($this->contractMapping(), $this->annotationTitleMaping()));
        $default      = [];
        foreach ($default_keys as $key => $value) {
            $default[$value] = '';
        }

        $available = array_merge(
            $this->filterData($this->data(), $this->contractMapping()),
            $this->extractMetadata($this->data()->annotations, $this->annotationTitleMaping())
        );

        return array_merge($default, $available);
    }

    /**
     * @return array
     */
    protected function contractMapping()
    {
        return [
            'language'         => 'language',
            'contract_name'    => 'title',
            'created_datetime' => 'created_at',
            'updated_datetime' => 'updated_at',
            'pdf_url'          => ['resources', 'pdf'],
            'signature_date_1' => ['data', 'Signature Date'],
            'signature_year_1' => ['data', 'Signature Year'],
            'resources'        => ['data', 'Resource'],
            'country'          => ['data', 'Countries']
        ];
    }

    /**
     * @return array
     */
    protected function annotationTitleMaping()
    {
        return [
            'company'                 => 'Local company name',
            'contract_identifier'     => 'Legal Enterprise Identifier',
            'project_title'           => 'Project title',
            'signature_date'          => 'Date of contract signature',
            'signature_year'          => 'Year of contract signature',
            'contract_identifier'     => 'Legal Enterprise Identifier',
            'project_title'           => 'Project title',
            'signature_date'          => 'Date of contract signature',
            'signature_year'          => 'Year of contract signature',
            'type_of_contract_1'      => 'Type of document / right (Concession, Lease, Production Sharing Agreement, Service Agreement, etc.)',
            'type_of_contract_2'      => 'Type of document / right (Concession, Lease, Production Sharing contract, Service contract, etc.)',
            'resources_1'             => 'Type of mining title associated with the contract',
            'resources_2'             => 'Type of resources',
            'resources_3'             => 'Type of resources (mineral type, crude oil, gas, etc.)',
            'resources_4'             => 'Type of resources (mineral type, crude oil, gas, timber, etc.) OR specific crops planned (ex: food crops, oil palm, etc.)',
            'resources_5'             => 'Type of resources (mineral type, crude oil, gas, timber, etc.) OR specific crops planned (ex: food crops, oil palm, etc.)',
            'License/concession name' => 'Name and/or number of field, block or deposit'

        ];
    }

    /**
     * @param string $title
     * @return int|null|string
     */
    protected function getKeyIfValid($title = '')
    {
        foreach ($this->annotationTitleMaping() as $key => $value) {
            if (trim(strtolower($title)) == trim(strtolower($value))) {
                return $key;
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

            list($fn, $en) = explode('//', $value->title);

            $this->annotation_title[] = $en;

            if ($key = $this->getKeyIfValid($en)) {
                $return[$key] = $value->content;
            }
        }

        //  $return = array_merge(array_keys($this->annotationTitleMaping(), $return));

        return $return;
    }

    /**
     * @return array
     */
    public function getTitles()
    {
        return $this->annotation_title;
    }
}

