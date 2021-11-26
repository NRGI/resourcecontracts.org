<?php namespace App\Nrgi\Services\ElasticSearch;

use App\Nrgi\Mturk\Services\ActivityService;
use App\Nrgi\Repositories\Contract\Annotation\AnnotationRepositoryInterface;
use App\Nrgi\Services\Contract\ContractService;
use App\Nrgi\Services\Language\LanguageService;
use Exception;
use GuzzleHttp\Client;
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
     * @var ActivityService
     */
    protected $activity;
    /**
     * @var LanguageService
     */
    protected $lang;

    /**
     * @param Client                        $http
     * @param AnnotationRepositoryInterface $annotation
     * @param ContractService               $contract
     * @param LoggerInterface               $logger
     * @param ActivityService               $activity
     * @param LanguageService               $lang
     */
    public function __construct(
        Client $http,
        AnnotationRepositoryInterface $annotation,
        ContractService $contract,
        LoggerInterface $logger,
        ActivityService $activity,
        LanguageService $lang
    ) {
        $this->http       = $http;
        $this->logger     = $logger;
        $this->contract   = $contract;
        $this->annotation = $annotation;
        $this->activity   = $activity;
        $this->lang       = $lang;
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
        $contract = $this->contract->find($id);
        $this->indexMetadata($contract);
        $associatedContracts = $this->contract->getAssociatedContracts($contract);

        foreach ($associatedContracts as $contract) {
            $contractId   = $contract['contract']['id'];
            $elementState = $this->activity->getElementState($contractId);

            if ($elementState['metadata'] == 'published') {
                $contract = $this->contract->find($contractId);
                $this->indexMetadata($contract);
            }
        }
    }

    /**
     * Re-updates the metadata of contracts both main and associated
     *
     * @param $contract
     */
    public function indexMetadata($contract)
    {
        $id           = $contract->id;
        $showText     = false;
        $elementState = $this->activity->getElementState($id);

        if ($elementState['text'] == 'published') {
            $showText = true;
        }

        $updated_by = ['name' => '', 'email' => ''];

        if (!empty($contract->updated_user)) {
            $updated_by = ['name' => $contract->updated_user->name, 'email' => $contract->updated_user->email];
        }

        $contract->metadata->contract_id = $contract->id;
        $contract->metadata->page_number = $contract->pages()->count();
        $metadataAttr                    = $contract->metadata;
        $parent                          = $this->contract->getcontracts((int) $contract->getParentContract());

        if (isset($metadataAttr->open_contracting_id_old)) {
            unset($metadataAttr->open_contracting_id_old);
        }

        $contract->metadata           = $metadataAttr;
        $trans                        = [];
        $trans['en']                  = $metadataAttr;
        $trans['en']->translated_from = $parent;
        $trans['en']->show_pdf_text   = (int) $showText;

        foreach ($this->lang->translation_lang() as $l) {
            $contract->setLang($l['code']);
            $trans[$l['code']]                  = $contract->metadata;
            $trans[$l['code']]->translated_from = $parent;
            $trans[$l['code']]->show_pdf_text   = (int) $showText;
        }

        $metadata = [
            'id'                   => $contract->id,
            'metadata'             => json_encode($trans),
            'total_pages'          => $contract->pages->count(),
            'created_by'           => json_encode(
                [
                    'name'  => isset($contract->created_user->name) ? $contract->created_user->name : '',
                    'email' => isset($contract->created_user->email) ? $contract->created_user->email : '',
                ]
            ),
            'supporting_contracts' => json_encode($this->contract->getSupportingDocuments($contract->id)),
            'parent_contract'      => json_encode($this->contract->getParentDocument($contract->id)),
            'updated_by'           => json_encode($updated_by),
            'created_at'           => $contract->created_datetime->format('Y-m-d H:i:s'),
            'updated_at'           => $contract->last_updated_datetime->format('Y-m-d H:i:s'),
            'published_at'         => '',
        ];

        if ($elementState['metadata'] == 'published' && isset($elementState['metadata_published_at']) && !empty($elementState['metadata_published_at'])) {
            $metadata['published_at'] = $elementState['metadata_published_at']->format('Y-m-d H:i:s');
        }
        try {
            $response  = $this->http->post($this->apiURL('contract/metadata'), [
                'body' => $metadata
            ]);
            $this->logger->info('Metadata submitted to Elastic Search.', $response->json());
            if (!$showText) {
                $this->postText($id, false);
            }

        } catch (Exception $e) {
            $this->logger->error($e->getMessage(), (array) $e);
        }
    }

    /**
     * Post pdf text
     *
     * @param      $id
     * @param bool $showText
     * @param bool $generateWord
     */
    public function postText($id, $showText = true, $generateWord = true)
    {
        $contract = $this->contract->findWithPages($id);
        $pages    = [
            'contract_id'         => $contract->id,
            'open_contracting_id' => $contract->metadata->open_contracting_id,
            'total_pages'         => $contract->pages->count(),
            'pages'               => $this->formatPdfTextPages($contract, $showText),
            'metadata'            => $this->getMetadataForES($contract->metadata),
        ];

        try {
            if ($generateWord) {
                $this->contract->updateWordFile($contract->id);
            }
            $response  = $this->http->post($this->apiURL('contract/pdf-text'), [
                'body' => $pages
            ]);
            $this->logger->info('Pdf Text submitted to Elastic Search.', $response->json());
            if ($showText) {
                $this->postMetadata($id);
            }
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
                $json                           = $child->annotation;
                $json->id                       = $child->id;
                $json->page                     = $child->page_no;
                $json->article_reference        = $child->article_reference;
                $json->article_reference_locale = $child->article_reference_trans;

                $json->contract_id         = $contract->id;
                $json->open_contracting_id = $contract->metadata->open_contracting_id;

                $json->annotation_id = $annotation->id;

                $json->text        = $annotation->text;
                $json->text_locale = $annotation->text_trans;

                $json->category_key = $annotation->category;
                $json->category     = (isset($annotation->category)) ? getCategoryName($annotation->category) : "";
                $json->cluster      = (isset($annotation->category)) ? getCategoryClusterName(
                    $annotation->category
                ) : "";
                $annotationData[]   = $json;
            }
        }

        $data['annotations'] = json_encode($annotationData);

        try {
            $this->deleteAnnotation($contractId);
            $response  = $this->http->post($this->apiURL('contract/annotations'), [
                'body' => $data
            ]);
            $this->logger->info('Annotation submitted to Elastic Search.', [$response->json()]);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Delete contract in elastic search
     *
     * @param $contract_id
     */
    public function delete($contract_id)
    {
        try {
            $response  = $this->http->post($this->apiURL('contract/delete'), [
                'body' => ['id' => $contract_id]
            ]);
            $this->logger->info('Contract deleted from Elastic Search.', $response->json());
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Get Formatted Pdf Text
     *
     * @param $contract
     *
     * @return string
     */
    public function formatPdfTextPages($contract, $showText)
    {
        if ($showText) {
            return $contract->pages->toJson();
        }

        $contractPagesArray = [];

        foreach ($contract->pages->toArray() as $array) {
            $array['text']        = "";
            $contractPagesArray[] = $array;
        }

        return json_encode($contractPagesArray);
    }

    /**
     * Call appropriate function to delete element
     *
     * @param $id
     * @param $type
     *
     * @return mixed
     */
    public function deleteElement($id, $type)
    {
        $method = sprintf('delete%s', ucfirst($type));
        if (method_exists($this, $method)) {
            return $this->$method($id);
        }
    }

    /**
     * Delete metadata document from elasticsearch
     *
     * @param $id
     */
    public function deleteMetadata($id)
    {
        try {
            $response  = $this->http->post($this->apiURL('contract/delete/metadata'), [
                'body' => ['contract_id' => $id]
            ]);
            $this->logger->info('Metadata deleted from Elastic Search.', [$response->json()]);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Delete text document from elasticsearch
     *
     * @param $id
     */
    public function deleteText($id)
    {
        try {
            $this->postText($id, false);
            $this->postMetadata($id);
            $this->logger->info('Text deleted from Elastic Search.');
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Delete contract Annotations in elastic search
     *
     * @param $contract_id
     */
    public function deleteAnnotation($contract_id)
    {
        try {
            $response = $this->http->post($this->apiURL('contract/delete/annotation'), [
                'body' => ["contract_id" => $contract_id]
            ]);
            $this->logger->info('Annotations deleted', [$response->json()]);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
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
        return trim(env('ELASTIC_SEARCH_URL'), '/').'/'.$request;
    }

    /**
     * Get Metadata for Elastic Search
     *
     * @param      $metadata
     *
     * @param bool $array
     *
     * @return string
     */
    protected function getMetadataForES($metadata, $array = false)
    {
        $metadata_array = (array) $metadata;

        $meta = Arr::only(
            $metadata_array,
            ['category', 'contract_name', 'signature_date', 'resource', 'file_size', 'file_url', 'country']
        );

        return $array ? $meta : json_encode($meta);
    }
}
