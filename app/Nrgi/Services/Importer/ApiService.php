<?php namespace App\Nrgi\Services\Importer;

use App\Nrgi\Entities\ExternalApi\ExternalApi;
use Guzzle\Http\Client;
use Illuminate\Contracts\Logging\Log;

/**
 * Class ApiService
 * @package App\Nrgi\Services\Importer
 */
class ApiService
{
    /**
     * @var Client
     */
    protected $http;
    /**
     * @var string
     */
    protected $lastUpdatedDate;
    /**
     * @var string
     */
    protected $apiUrl;
    /**
     * @var Log
     */
    protected $log;
    /**
     * @var ExternalApi
     */
    protected $api;

    /**
     * ApiService constructor.
     *
     * @param Log         $log
     * @param ExternalApi $api
     */
    public function __construct(Log $log, ExternalApi $api)
    {
        $this->log = $log;
        $this->api = $api;
    }

    /**
     * Run importer
     *
     * @param $apiUrl
     * @param $lastUpdatedDate
     * @param $source
     */
    public function run($apiUrl, $lastUpdatedDate, $source)
    {
        $this->apiUrl          = $apiUrl;
        $this->lastUpdatedDate = $lastUpdatedDate;

        $this->http = new Client($this->apiUrl);
        $contracts  = $this->getStatus();
        foreach ($contracts as $contract) {
            $id = $contract['id'];
            foreach ($contract as $method => $param) {
                if (method_exists($this, $method)) {
                    $param['source'] = $source;
                    $this->$method($id, $param);
                }
            }
        }
    }

    /**
     * Remove index contracts
     *
     * @param $id
     *
     * @return array|bool
     */
    public function remove($id)
    {
        $api  = $this->api->find($id)->first();
        $http = new Client();
        $res  = $http->post($this->indexUrl('delete/source'), null, ['source' => $api->site])->send();

        if ($res->getStatusCode() != 200) {
            $this->log->error($res->getBody());

            return false;
        }

        try {
            $api->updateIndexDate(null);
            $res->json();
            return true;
        } catch (\Exception $e) {
            $this->log->error('API Importer remove index contracts : '.$res->getBody().$e->getMessage());

            false;
        }
    }

    /**
     * Index Metadata
     *
     *
     * @param $id
     * @param $param
     *
     * @return bool
     */
    protected function metadata($id, $param)
    {
        $apiData = (object) $this->api(sprintf('contract/%s/metadata', $id));
        if (empty($apiData)) {
            return false;
        }

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
        $metadata->signature_date    = (!empty($apiData->date_signed)) ? $apiData->date_signed : null;
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
        $metadata->date_retrieval         = (!empty($apiData->retrieved_at)) ? $apiData->retrieved_at : null;
        $metadata->category               = $this->getCategory($param);
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
        $supporting_contracts      = [];

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
            'external_source'      => $param['source'],
        ];

        return $this->indexToEl('metadata', $data);
    }

    /**
     * Index Pdf Text
     *
     * @param $id
     * @param $param
     *
     * @return bool
     */
    protected function pdf_text($id, $param)
    {
        $res      = (object) $this->api(sprintf('contract/%s/text', $id));
        $metadata = (object) $this->api(sprintf('contract/%s/metadata', $id));

        if (empty($res) || empty($metadata)) {
            return false;
        }

        $apiData = $res->result;
        $data    = [
            'contract_id'         => $apiData[0]['contract_id'],
            'open_contracting_id' => $apiData[0]['open_contracting_id'],
            'total_pages'         => $res->total,
            'pages'               => $this->formatPdfTextPages($res->result, $param, $metadata),
        ];

        return $this->indexToEl('pdf-text', $data);
    }

    /**
     * Index Annotations
     *
     * @param $id
     * @param $param
     *
     * @return bool|string
     * @throws \Exception
     */
    protected function annotations($id, $param)
    {
        $res = (object) $this->api(sprintf('contract/%s/annotations', $id));

        if (empty($res)) {
            return false;
        }

        $annotations = [];
        foreach ($res->result as $key => $annotation) {
            $annotation['created_at'] = $param['created_at'];
            $annotation['updated_at'] = $param['updated_at'];
            $annotation['page']       = $annotation['page_no'];
            unset($annotation['page_no']);
            $annotations[] = $annotation;
        }

        return $this->indexToEl('annotations', ['annotations' => json_encode($annotations)]);
    }

    /**
     * Get Formatted Pdf Text
     *
     * @param $pages
     * @param $param
     * @param $metadata
     *
     * @return array
     */
    protected function formatPdfTextPages($pages, $param, $metadata)
    {
        foreach ($pages as $key => $page) {
            $pages[$key]['created_at'] = $param['created_at'];
            $pages[$key]['updated_at'] = $param['updated_at'];
            if (isset($metadata->is_ocr_reviewed) && $metadata->is_ocr_reviewed == false) {
                $pages[$key]['text'] = "";
            }
        }

        return json_encode($pages);
    }

    /**
     * Get Category
     *
     * @param $param
     *
     * @return array
     */
    protected function getCategory($param)
    {
        return isset($param['category']) ? [$param['category']] : ['rc'];
    }

    /**
     * Get Contract Status
     *
     * @return array
     * @throws \Exception
     */
    protected function getStatus()
    {
        $updated_date = $this->lastUpdatedDate;

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
        $res   = $this->http->get($request, null, $query)->send();

        if ($res->getStatusCode() != 200) {
            $this->log->error($res->getBody());

            return [];
        }

        try {
            return $res->json();
        } catch (\Exception $e) {
            $this->log->error('API Importer : '.$res->getBody().$e->getMessage());

            return [];
        }
    }

    /**
     * Index data to Elastic Search
     *
     * @param $type
     * @param $data
     *
     * @return string
     * @throws \Exception
     */
    protected function indexToEl($type, $data)
    {
        $res = $this->http->post($this->indexUrl('contract/'.$type), null, $data)->send();

        if ($res->getStatusCode() != 200) {
            $this->log->error($res->getBody());

            return false;
        }

        try {
            $result = $res->json();
        } catch (\Exception $e) {
            $this->log->error('API Importer POST : '.$res->getEffectiveUrl().$res->getBody().$e->getMessage());

            return false;
        }

        if (isset($result['_index'])) {
            $this->log->info(
                sprintf(
                    'ID - %s,  %s index - %s, %s',
                    $result['_id'],
                    $type,
                    isset($result['created']) ? 'created' : 'updated',
                    $res->getEffectiveUrl()
                )
            );

            return true;
        }

        $this->log->error(sprintf('Error While indexing %s - %s, %s', $type, $res->getBody(), $res->getEffectiveUrl()));

        return false;
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

    /**
     * Update Index
     *
     * @param      $api_id
     * @param bool $all
     *
     * @return bool
     */
    public function updateIndex($api_id, $all = false)
    {
        try {
            $api             = $this->api->find($api_id)->first();
            $lastUpdatedDate = $all ? null : $api->last_index_date;
            $this->run($api->url, $lastUpdatedDate, $api->site);
            $api->updateIndexDate();
            $this->log->info($api->site.' Api - '.$api->url.' updated at '.$api->last_index_date);

            return true;
        } catch (\Exception $e) {
            $this->log->error('Error while updating index : '.$e->getMessage(), ['id' => $api_id, 'all' => $all]);

            return false;
        }
    }

}
