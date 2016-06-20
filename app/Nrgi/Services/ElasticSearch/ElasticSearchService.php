<?php namespace App\Nrgi\Services\ElasticSearch;

use App\Nrgi\Repositories\Contract\Annotation\AnnotationRepositoryInterface;
use App\Nrgi\Services\Contract\ContractService;
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
     * @var ContractService
     */
    protected $contract;
    /**
     * @var AnnotationRepositoryInterface
     */
    protected $annotation;

    /**
     * @param Client                        $http
     * @param AnnotationRepositoryInterface $annotation
     * @param ContractService               $contract
     * @param LoggerInterface               $logger
     */
    public function __construct(Client $http, AnnotationRepositoryInterface $annotation, ContractService $contract, LoggerInterface $logger)
    {
        $this->http       = $http;
        $this->logger     = $logger;
        $this->contract   = $contract;
        $this->annotation = $annotation;
    }

    /**
     * Get full qualified ES url
     *
     * @param $request
     *
     * @return string
     */
    protected function apiURL($request)
    {
        return trim(env('ELASTIC_SEARCH_URL'), '/') . '/' . $request;
    }

    /**
     * Post data to Elastic Search
     *
     * @param $id
     * @param $type
     *
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
        $contract   = $this->contract->find($id);
        $updated_by = ['name' => '', 'email' => ''];

        if (!empty($contract->updated_user)) {
            $updated_by = ['name' => $contract->updated_user->name, 'email' => $contract->updated_user->email];
        }

        $contract->metadata->contract_id = $contract->id;
        $contract->metadata->page_number = $contract->pages()->count();
        $metadataAttr                    = $contract->metadata;
        $parent                          = $this->contract->getcontracts((int) $contract->getParentContract());

        $metadataAttr->translated_from = $parent;;
        $contract->metadata = $metadataAttr;
        $metadata           = [
            'id'                   => $contract->id,
            'metadata'             => collect($contract->metadata)->toJson(),
            'total_pages'          => $contract->pages->count(),
            'created_by'           => json_encode(
                ['name' => isset($contract->created_user->name)?$contract->created_user->name:'', 'email' => isset($contract->created_user->email)?$contract->created_user->email:'']
            ),
            'supporting_contracts' => $this->contract->getSupportingDocuments($contract->id),
            'updated_by'           => json_encode($updated_by),
            'created_at'           => $contract->created_datetime->format('Y-m-d H:i:s'),
            'updated_at'           => $contract->last_updated_datetime->format('Y-m-d H:i:s')
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
        $contract = $this->contract->findWithPages($id);
        $pages    = [
            'contract_id'         => $contract->id,
            'open_contracting_id' => $contract->metadata->open_contracting_id,
            'total_pages'         => $contract->pages->count(),
            'pages'               => $this->formatPdfTextPages($contract),
            'metadata'            => $this->getMetadataForES($contract->metadata)
        ];

        try {
            $this->contract->updateWordFile($contract->id);
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
     * @param $contractId
     *
     */
    public function postAnnotation($contractId)
    {
        $data           = [];
        $annotationData = [];
        $contract       = $this->annotation->getContractPagesWithAnnotations($contractId);
        foreach ($contract->annotations as $annotation) {
            foreach ($annotation->child as $child) {
                $json                    = $child->annotation;
                $json->id                = $child->id;
                $json->page              = $child->page_no;
                $json->article_reference = $child->article_reference;

                $json->contract_id         = $contract->id;
                $json->open_contracting_id = $contract->metadata->open_contracting_id;

                $json->annotation_id = $annotation->id;
                $json->text          = $annotation->text;
                $json->category_key  = $annotation->category;
                $json->category      = (isset($annotation->category)) ? getCategoryName($annotation->category) : "";
                $json->cluster       = (isset($annotation->category)) ? _l("codelist/annotation.cluster.{$annotation->category}") : "";
                $annotationData[] = $json;
            }
        }

        $data['annotations'] = json_encode($annotationData);

        try {
            $response = $this->http->post($this->apiURL('contract/delete/annotation'), null, ["contract_id" => $contractId])->send();
            $this->logger->info('Annotations deleted', [$response->json()]);
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
     *
     * @return string
     */
    protected function getMetadataForES($metadata, $array = false)
    {
        $metadata_array = (array) $metadata;

        $meta = array_only(
            $metadata_array,
            ['category', 'contract_name', 'signature_date', 'resource', 'file_size', 'file_url', 'country']
        );

        return $array ? $meta : json_encode($meta);
    }

    /**
     * Delete contract in elastic search
     *
     * @param $contract_id
     */
    public function delete($contract_id)
    {
        try {
            $request  = $this->http->post($this->apiURL('contract/delete'), null, ['id' => $contract_id]);
            $response = $request->send();
            $this->logger->info('Contract deleted from Elastic Search.', $response->json());
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Delete contract Annotations in elastic search
     *
     * @param $contract_id
     */
    public function deleteAnnotations($contract_id)
    {
        try {
            $request  = $this->http->post(
                $this->apiURL('contract/delete/annotation'),
                null,
                ['contract_id' => $contract_id]
            );
            $response = $request->send();
            $this->logger->info('Contract Annotations deleted from Elastic Search.', $response->json());
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Get Formatted Pdf Text
     * @param $contract
     *
     * @return string
     */
    public function formatPdfTextPages($contract)
    {
        if (isset($contract->metadata->show_pdf_text) && $contract->metadata->show_pdf_text == 1) {
            return $contract->pages->toJson();
        }

        $contractPagesArray = [];

        foreach ($contract->pages->toArray() as $array) {
            $array['text']        = "";
            $contractPagesArray[] = $array;
        }

        return json_encode($contractPagesArray);
    }

}
