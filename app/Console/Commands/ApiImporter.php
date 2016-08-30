<?php namespace App\Console\Commands;

use Guzzle\Http\Client;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class ApiImporter
 * @package App\Console\Commands
 */
class ApiImporter extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'nrgi:apiImporter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull contracts from API and index in elasticSearch.';
    /**
     * @var Client
     */
    protected $http;

    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     */
    public function fire()
    {
        $this->http = new Client($this->argument('api'));
        $contracts  = $this->getStatus();
        foreach ($contracts as $contract) {
            $id = $contract['id'];
            foreach ($contract as $method => $param) {
                if (method_exists($this, $method)) {
                    $this->$method($id, $param);
                }
            }
        }
    }

    /**
     * Index Metadata
     *
     *
     * @param $id
     * @param $param
     */
    function metadata($id, $param)
    {
        $apiData                       = (object) $this->api(sprintf('contract/%s/metadata', 1973));
        $metadata                      = new \stdClass();
        $metadata->contract_name       = $apiData->name;
        $metadata->contract_identifier = $apiData->identifier;
        $metadata->language            = $apiData->language;
        $metadata->country             = (object) $apiData->country;
        $metadata->resource            = $apiData->resource;
        $government_entity             = [];
        foreach ($apiData->government_entity as $ge) {
            $government_entity[] = (object) [
                'entity'     => $ge['name'],
                'identifier' => $ge['identifier'],
            ];
        }
        $metadata->government_entity = $government_entity;
        $metadata->document_type     = $apiData->type;
        $metadata->type_of_contract  = $apiData->contract_type;
        $metadata->signature_date    = $apiData->date_signed;
        $metadata->signature_year    = $this->getSignatureYear($apiData->year_signed);

        $company = [];
        foreach ($apiData->participation as $api) {
            $company[] = [
                'name'                          => $api['company']['name'],
                'participation_share'           => $this->getShare($api['share']),
                'jurisdiction_of_incorporation' => $api['company']['identifier']['creator']['spatial'],
                'registration_agency'           => $api['company']['identifier']['creator']['name'],
                'company_founding_date'         => ($api['company']['founding_date'] == '') ? null : $api['company']['founding_date'],
                'company_address'               => $api['company']['address'],
                'company_number'                => $api['company']['identifier']['id'],
                'parent_company'                => $api['company']['corporate_grouping'],
                'open_corporate_id'             => $api['company']['opencorporates_url'],
                'operator'                      => $this->getBoolean($api['is_operator']),
            ];
        }

        $metadata->company            = $company;
        $metadata->project_title      = $apiData->project['name'];
        $metadata->project_identifier = $apiData->project['identifier'];

        $concession = [];
        foreach ($apiData->concession as $api) {
            $concession[] = [
                'license_name'       => $api['name'],
                'license_identifier' => $api['identifier'],
            ];
        }
        $metadata->concession             = $concession;
        $metadata->source_url             = $apiData->source_url;
        $metadata->disclosure_mode        = $apiData->publisher_type;
        $metadata->date_retrieval         = $apiData->retrieved_at;
        $metadata->category               = isset($param['category']) ? [$param['category']] : ['rc'];
        $metadata->deal_number            = $apiData->deal_number;
        $metadata->matrix_page            = $apiData->matrix_page;
        $metadata->contract_note          = $apiData->note;
        $metadata->is_supporting_document = $this->getBoolean($apiData->is_associated_document);
        $metadata->annexes_missing        = $this->getBoolean($apiData->is_annexes_missing);
        $metadata->pages_missing          = $this->getBoolean($apiData->is_pages_missing);
        $metadata->show_pdf_text          = $this->getBoolean($apiData->is_ocr_reviewed);
        $metadata->open_contracting_id    = $apiData->open_contracting_id;
        $metadata->amla_url               = $apiData->amla_url;
        foreach ($apiData->file as $file) {

            if ($file['media_type'] == "application/pdf") {
                $metadata->file_size = $file['byte_size'];
                $metadata->file_url  = $file['url'];
            }

            if ($file['media_type'] == "text/plain") {
                $metadata->word_file = $file['url'];
            }

        }

        $par = [];
        foreach ($apiData->parent as $parent) {
            $par[] = [
                'id'            => (string) $parent['id'],
                'contract_name' => $parent['name'],
            ];
        }

        $metadata->translated_from = $par;

        $supporting_contracts = [];
        foreach ($apiData->associated as $parent) {
            $supporting_contracts[] = [
                'id'            => (string) $parent['id'],
                'contract_name' => $parent['name'],
            ];
        }

        $data = [
            'id'                   => $apiData->id,
            'metadata'             => collect($metadata)->toJson(),
            'total_pages'          => $apiData->number_of_pages,
            'created_by'           => json_encode(
                [
                    'name'  => $param['created_by']['name'],
                    'email' => $param['created_by']['email'],
                ]
            ),
            'supporting_contracts' => $supporting_contracts,
            'updated_by'           => json_encode(
                [
                    'name'  => $param['updated_by']['name'],
                    'email' => $param['updated_by']['email'],
                ]
            ),
            'created_at'           => $param['created_at'],
            'updated_at'           => $param['updated_at'],
        ];


        file_put_contents(public_path('metadata_converted.html'), json_encode($data));

        $this->indexToEl('metadata', $data);
    }


    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['api', InputArgument::REQUIRED, 'Api to pull contracts.'],
            ['updated_date', InputArgument::REQUIRED, 'Last updated date.'],
        ];
    }

    /**
     * Get Contract Status
     *
     * @return array
     * @throws \Exception
     */
    protected function getStatus()
    {
        $updated_date = $this->argument('updated_date');

        $data = $this->api('status', compact('updated_date'));

        if (isset($data['results'])) {
            return $data['results'];
        }

        return [];
    }

    /**
     * Api Request
     *
     * @param       $request
     * @param array $query
     *
     * @return array
     * @throws \Exception
     */
    protected function api($request, $query = [])
    {
        $query = empty($query) ? [] : ['query' => $query];
        $res   = $this->http->get($request, $query)->send();
        if ($res->getStatusCode() != 200) {
            throw new \Exception($res->getBody());
        }

        try {
            return $res->json();
        } catch (\Exception $e) {
            $this->error('API Importer : '.$e->getMessage());

            return [];
        }
    }

    protected function indexToEl($type, $data)
    {
        $res = $this->http->post($this->indexUrl('contract/'.$type), null, $data)->send();
        dd($res->json());
    }

    /**
     * Get full qualified ES url
     *
     * @param $request
     *
     * @return string
     */
    protected function indexUrl($request)
    {
        return trim(env('ELASTIC_SEARCH_URL'), '/').'/'.$request;
    }

    /**
     * Get Boolean value
     *
     * @param $operator
     *
     * @return string
     */
    protected function getBoolean($operator)
    {
        if ($operator === null) {
            return '-1';
        }
        if ($operator === true) {
            return '1';
        }
        if ($operator === false) {
            return '0';
        }
    }

    /**
     * Get Share value
     *
     * @param $participationShare
     *
     * @return float|string
     */
    protected function getShare($participationShare)
    {
        if (is_null($participationShare)) {
            return '';
        }

        return (string) $participationShare;
    }

    /**
     * Get signature Year
     *
     * @param $signatureYear
     *
     * @return int|string
     */
    public function getSignatureYear($signatureYear)
    {
        if (is_null($signatureYear)) {
            return '';
        }

        return (string) $signatureYear;
    }

}
