<?php namespace App\Console\Commands;

use GuzzleHttp\Client;
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
    protected $client;

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
        $this->client = new Client(['base_url' => $this->argument('api')]);
        $contracts    = $this->getStatus();
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
        $apiData = (object) $this->api(sprintf('contract/%s/metadata', $id));

        $contract                                  = new \stdClass();
        $contract->id                              = $apiData->id;
        $contract->metadata                        = new \stdClass();
        $contract->metaddata->contract_name        = $apiData->name;
        $contract->metaddata->contract_identifier  = $apiData->identifier
        $contract->metadata->language              = $apiData->language;
$contract->metadata->country                       = $apiData->country;
$contract->metadata->resource                      = $apiData->resource;
$contract->metadata->government_entity             = $apiData->government_entity;
$contract->metadata->entity                        = $apiData->entity;
$contract->metadata->identifier                    = $apiData->identifier;
$contract->metadata->type_of_contract              = $apiData->type_of_contract;
$contract->metadata->signature_date                = $apiData->signature_date;
$contract->metadata->document_type                 = $apiData->document_type;
$contract->metadata->company                       = $apiData->company;
$contract->metadata->name                          = $apiData->name;
$contract->metadata->participation_share           = $apiData->participation_share;
$contract->metadata->jurisdiction_of_incorporation = $apiData->jurisdiction_of_incorporation;
$contract->metadata->registration_agency           = $apiData->registration_agency;
$contract->metadata->company_founding_date         = $apiData->company_founding_date;
$contract->metadata->company_address               = $apiData->company_address;
$contract->metadata->company_number                = $apiData->company_number;
$contract->metadata->parent_company                = $apiData->parent_company;
$contract->metadata->open_corporate_id             = $apiData->open_corporate_id;
$contract->metadata->operator                      = $apiData->operator;
$contract->metadata->project_title                 = $apiData->project_title;
$contract->metadata->project_identifier            = $apiData->project_identifier;
$contract->metadata->concession                    = $apiData->concession;
$contract->metadata->license_name                  = $apiData->license_name;
$contract->metadata->license_identifier            = $apiData->license_identifier;
$contract->metadata->source_url                    = $apiData->source_url;
$contract->metadata->http                          = $apiData->http;
$contract->metadata->disclosure_mode               = $apiData->disclosure_mode;
$contract->metadata->date_retrieval                = $apiData->date_retrieval;
$contract->metadata->category                      = $apiData->category;
$contract->metadata->signature_year                = $apiData->signature_year;
$contract->metadata->file_size                     = $apiData->file_size;
$contract->metadata->open_contracting_id           = $apiData->open_contracting_id;
$contract->metadata->show_pdf_text                 = $apiData->show_pdf_text;
$contract->metadata->is_supporting_document        = $apiData->is_supporting_document;
$contract->metadata->contract_note                 = $apiData->contract_note;
$contract->metadata->deal_number                   = $apiData->deal_number;
$contract->metadata->matrix_page                   = $apiData->matrix_page;
$contract->metadata->amla_url                      = $apiData->amla_url;
$contract->metadata->http                          = $apiData->http;
$contract->metadata->file_url                      = $apiData->file_url;
$contract->metadata->https                         = $apiData->https;
$contract->metadata->word_file                     = $apiData->word_file;
$contract->metadata->https                         = $apiData->https;
$contract->metadata->translated_from               = $apiData->translated_from;
$contract->metadata->total_pages                   = $apiData->total_pages;
$contract->metadata->created_by                    = $apiData->created_by;
$contract->metadata->name                          = $apiData->name;
$contract->metadata->email                         = $apiData->email;
$contract->metadata->updated_by                    = $apiData->updated_by;
$contract->metadata->name                          = $apiData->name;
$contract->metadata->email                         = $apiData->email;
$contract->metadata->created_at                    = $apiData->created_at;
12:
29:
updated_at:

        {
            id:
            "1164",
metadata: {
            contract_name:
            "Liberia, B & V Timber Company, Timber Sale Contract, Area A-6, 27 June 2008",
contract_identifier: "",
language: "en",
country: {
                code:
                "LR",
name: "Liberia"
},
resource: [
                "Timber (Wood)",
            ],
government_entity: [
{
    entity:
    "Forestry Development Authority",
identifier: ""
}
],
type_of_contract: [
                "Timber Sale Contract",
            ],
signature_date: "2008-06-27",
document_type: "Contract",
company: [
{
    name:
    "B & V Timber Company",
participation_share: "",
jurisdiction_of_incorporation: "",
registration_agency: "",
company_founding_date: null,
company_address: "",
company_number: "",
parent_company: "",
open_corporate_id: "",
operator: "0"
}
],
project_title: "",
project_identifier: "",
concession: [
{
    license_name:
    "",
license_identifier: ""
}
],
source_url: "http://www.scribd.com/doc/152412344/Contract-to-Manage-Timber-Sale-Area-A-6-Bokomu-Faumah-Districts-Gbarpolu-Bong-Counties",
disclosure_mode: "Government",
date_retrieval: null,
category: [
                "olc",
            ],
signature_year: "2008",
file_size: 2154364,
open_contracting_id: "ocds-591adf-LR1218979910OL",
show_pdf_text: "0",
is_supporting_document: "0",
contract_note: "",
deal_number: "",
matrix_page: "",
amla_url: "http://a-mla.org/index.php/countries/26",
file_url: "https://rc-stage.s3-us-west-2.amazonaws.com/1164/1164-liberia-b-v-timber-company-timber-sale-contract-a-6-27-june-2008.pdf",
word_file: "https://rc-stage.s3-us-west-2.amazonaws.com/1164/1164-liberia-b-v-timber-company-timber-sale-contract-a-6-27-june-2008.txt",
translated_from: []
},
total_pages: "38",
created_by: {
            name:
            "admin",
email: "admin@nrgi.app"
},
updated_by: {
            name:
            "Rashida Williams",
email: "rkw2119@columbia.edu"
},
created_at: "2015-09-28 12:29:23",
updated_at: "2016-08-04 07:57:25"


        $created_by = ['name' => $param['created_by']['name'], 'email' => $param['created_by']['email']];
        $updated_by = ['name' => $param['updated_by']['name'], 'email' => $param['updated_by']['email']];

        $metadata = [
            'contract_id'     => $apiData->id,
            'page_number'     => $apiData->number_of_pages,
            'translated_from' => $apiData->parent,
        ];


        $indexData = [
            'id'                   => $apiData->id,
            'metadata'             => collect($metadata)->toJson(),
            'total_pages'          => $apiData->number_of_pages,
            'created_by'           => json_encode($created_by),
            '$updated_by'          => json_encode($updated_by),
            'supporting_contracts' => '',
            'updated_by'           => json_encode($updated_by),
            'created_at'           => $param['created_at'],
            'updated_at'           => $param['updated_at'],
        ];


        $this->indexToEl('metadata', $indexData);
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
        $res   = $this->client->get($request, $query);

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
        $res = $this->client->post($this->indexUrl('contract/'.$type), ['body' => $data]);
        dd($res);
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

}
