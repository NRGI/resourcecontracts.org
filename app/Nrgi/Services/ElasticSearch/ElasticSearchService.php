<?php namespace App\Nrgi\Services\ElasticSearch;

use App\Nrgi\Repositories\Contract\ContractRepository;
use Exception;
use Guzzle\Http\Client;
use Psr\Log\LoggerInterface;

/**
 * Class ElasticSearchService
 * @package App\Nrgi\Services\ElasticSearch
 */
class ElasticSearchService
{
    /**
     * @var Client
     */
    protected $http;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var ContractRepository
     */
    protected $contract;

    /**
     * @param Client             $http
     * @param ContractRepository $contract
     * @param LoggerInterface    $logger
     */
    public function __construct(Client $http, ContractRepository $contract, LoggerInterface $logger)
    {
        $this->http     = $http;
        $this->logger   = $logger;
        $this->contract = $contract;
    }

    /**
     * Get full qualified ES url
     *
     * @param $request
     * @return string
     */
    protected function apiURL($request)
    {
        return env('ELASTIC_SEARCH_URL') . $request;
    }


    /**
     * Post data to Elastic Search
     *
     * @param $id
     * @param $type
     * @return mixed
     */
    public function post($id, $type)
    {
        $method = sprintf('post%s', ucfirst($type));
        if (method_exists($this, $method)) {
            return $this->$method($id);
        }
    }

    /**
     * Post metadata to ElasticSearch
     *
     * @param $id
     */
    public function postMetadata($id)
    {
        $contract   = $this->contract->findContract($id);
        $updated_by = ['name' => '', 'email' => ''];

        if (!empty($contract->updated_user)) {
            $updated_by = ['name' => $contract->updated_user->name, 'email' => $contract->updated_user->email];
        }

        $contract->metadata->contract_id = $contract->id;
        $contract->metadata->page_number = $contract->pages()->count();

        $metadata = [
            'id'          => $contract->id,
            'metadata'    => collect($contract->metadata)->toJson(),
            'total_pages' => $contract->pages->count(),
            'created_by'  => json_encode(
                ['name' => $contract->created_user->name, 'email' => $contract->created_user->email]
            ),
            'updated_by'  => json_encode($updated_by),
            'created_at'  => $contract->created_datetime->format('Y-m-d H:i:s'),
            'updated_at'  => $contract->last_updated_datetime->format('Y-m-d H:i:s')
        ];

        try {
            $request  = $this->http->post($this->apiURL('contract/metadata'), null, $metadata);
            $response = $request->send();
            $this->logger->info('Metadata successfully submitted to Elastic Search.', $response->json());
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Post pdf text
     *
     * @param $id
     */
    public function postText($id)
    {
        $contract = $this->contract->findContractWithPages($id);
        $pages    = [
            'contract_id' => $contract->id,
            'total_pages' => $contract->pages->count(),
            'pages'       => $contract->pages->toJson(),
            'metadata'    => $this->getMetadataForES($contract->metadata)
        ];

        try {
            $request  = $this->http->post($this->apiURL('contract/pdf-text'), null, $pages);
            $response = $request->send();
            $this->logger->info('Pdf Text successfully submitted to Elastic Search.', $response->json());
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Post contract annotations
     *
     * @param $id
     */
    public function postAnnotation($id)
    {
        $contract       = $this->contract->findContractWithAnnotations($id);
        $annotationData = [];
        $annotations    = $contract->annotations;
        foreach ($annotations as $annotation) {
            $json             = $annotation->annotation;
            $json->id         = $annotation->id;
            $json->contact_id = $contract->id;
            $json->metadata   = $this->getMetadataForES($contract->metadata, true);
            $annotationData[] = $json;
        }
        $data['annotations'] = json_encode($annotationData);
        try {
            $request  = $this->http->post($this->apiURL('contract/annotations'), null, $data);
            $response = $request->send();
            $this->logger->info('Annotation successfully submitted to Elastic Search.', $response->json());
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Get Metadata for Elastic Search
     *
     * @param $metadata
     * @return string
     */
    protected function getMetadataForES($metadata, $array = false)
    {
        $metadata_array = (array) $metadata;

        $meta = array_only(
            $metadata_array,
            ['category', 'contract_name', 'signature_date', 'resource', 'file_size', 'country']
        );

        $meta['file_size'] = getFileSize($meta['file_size']);

        return $array ? $meta : json_encode($meta);
    }
}