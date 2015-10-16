<?php namespace App\Nrgi\Services\Contract;

use App\Nrgi\Entities\Contract\Annotation;
use App\Nrgi\Repositories\Contract\AnnotationRepositoryInterface;
use App\Nrgi\Services\Contract\Comment\CommentService;
use App\Nrgi\Services\ElasticSearch\ElasticSearchService;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\DatabaseManager;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Contracts\Queue\Queue;

/**
 * Class AnnotationService
 * @package Nrgi\Services\Contract
 */
class AnnotationService
{
    /**
     * @var AnnotationRepositoryInterface
     */
    protected $annotation;
    /**
     * @var Guard
     */
    protected $auth;
    /**
     * @var DatabaseManager
     */
    protected $database;
    /**
     * @var Comment
     */
    protected $comment;
    /**
     * @var Log
     */
    protected $logger;
    /**
     * @var ContractService
     */
    protected $contract;
    /**
     * @var Queue
     */
    public $queue;
    /**
     * @var ElasticSearchService
     */
    protected $elasticSearch;

    /**
     * Constructor
     * @param AnnotationRepositoryInterface $annotation
     * @param Guard                         $auth
     * @param DatabaseManager               $database
     * @param Comment|CommentService        $comment
     * @param LoggerInterface|Log           $logger
     * @param ContractService               $contract
     * @param Queue                         $queue
     * @param ElasticSearchService          $elasticSearch
     */

    public function __construct(
        AnnotationRepositoryInterface $annotation,
        Guard $auth,
        DatabaseManager $database,
        CommentService $comment,
        Log $logger,
        ContractService $contract,
        Queue $queue,
        ElasticSearchService $elasticSearch
    ) {
        $this->annotation    = $annotation;
        $this->auth          = $auth;
        $this->user          = $auth->user();
        $this->database      = $database;
        $this->comment       = $comment;
        $this->logger        = $logger;
        $this->contract      = $contract;
        $this->queue         = $queue;
        $this->elasticSearch = $elasticSearch;
    }

    /**
     * Store/Update a contact annotation.
     * @param $annotation
     * @param $inputData
     * @return Annotation
     */
    public function save($annotation, $inputData)
    {
        $data                                = json_decode($annotation, true);
        $contactAnnotation                   = $this->annotation->findOrCreate(
            isset($data['id']) ? $data['id'] : null
        );
        $data['category']                    = $data['category_key'];
        $data ['cluster']                    = _l(config("annotation_category.cluster.{$data['category_key']}"));
        $contactAnnotation->annotation       = $data;
        $contactAnnotation->user_id          = $this->user->id;
        $contactAnnotation->contract_id      = $inputData['contract'];
        $contactAnnotation->url              = $inputData['url'];
        $contactAnnotation->document_page_no = $inputData['document_page_no'];
        $contactAnnotation->page_id          = $inputData['page_id'];
        $logMessage                          = 'annotation.annotation_created';
        if (isset($data['id'])) {
            $logMessage = 'annotation.annotation_updated';
        }
        $this->logger->activity(
            $logMessage,
            ['contract' => $inputData['contract'], 'page' => $inputData['document_page_no']],
            $inputData['contract']
        );

        $this->annotation->save($contactAnnotation);

        return $contactAnnotation;
    }

    /**
     * @param       $annotation
     * @param array $inputs
     * @return boolean
     */
    public function delete($inputs)
    {
        $contactAnnotationId = $inputs['id'];
        $annotation          = $this->annotation->getById($contactAnnotationId);
        if ($this->annotation->delete($contactAnnotationId)) {
            $this->logger->activity(
                'annotation.annotation_deleted',
                [
                    'contract' => $annotation->contract_id,
                    'page' => $annotation->document_page_no,
                    'title' => $annotation->annotation->text
                ],
                $annotation->contract_id
            );
            $this->logger->info('annotation deleted', $annotation->toArray());

            return true;
        }

        return false;
    }

    /**
     * search annotation
     * @param array $params
     * @return mixed
     */
    public function search(array $params)
    {
        $annotationData = [];
        $annotations    = $this->annotation->search($params);

        foreach ($annotations as $annotation) {
            $json             = $annotation->annotation;
            $json->id         = $annotation->id;
            $annotationData[] = $json;
        }

        return ['total' => count($annotationData), 'rows' => $annotationData];
    }

    /**
     * get all annotation by contract id
     * @param $contractId
     * return List of annotation
     */
    public function getAllByContractId($contractId)
    {
        return $this->annotation->getAllByContractId($contractId);
    }

    /**
     * get contract with pages and annotations
     * @param $contractId
     * @return \App\Nrgi\Repositories\Contract\contract
     */
    public function getContractPagesWithAnnotations($contractId)
    {
        return $this->annotation->getContractPagesWithAnnotations($contractId);
    }

    /**
     * get status of annotation
     *
     * @param $contractId
     * @return annotation status
     */
    public function getStatus($contractId)
    {
        return $this->annotation->getStatus($contractId);
    }

    /**
     * updates status of annotations of contract
     * @param $annotationStatus
     * @param $contractId
     * @return bool
     */
    public function updateStatus($annotationStatus, $contractId)
    {
        $status = $this->annotation->updateStatus($annotationStatus, $contractId);
        if ($status) {
            if ($annotationStatus == Annotation::PUBLISHED) {
                $this->queue->push(
                    'App\Nrgi\Services\Queue\PostToElasticSearchQueue',
                    ['contract_id' => $contractId, 'type' => 'annotation'],
                    'elastic_search'
                );
            }
            $this->logger->activity(
                "annotation.status_update",
                ['status' => $annotationStatus],
                $contractId
            );
            $this->logger->info(
                'Annotation status updated.',
                ['Contract id' => $contractId, 'status' => $annotationStatus]
            );
        }

        return $status;
    }

    /**
     * adds annotation comment to contract
     * @param $contractId
     * @param $message
     * @param $type
     * @param $annotationStatus
     * @return bool
     */
    public function comment($contractId, $message, $annotationStatus)
    {
        $this->database->beginTransaction();
        $status = $this->updateStatus($annotationStatus, $contractId);

        if ($status) {
            try {
                $this->comment->save($contractId, $message, "annotation", $annotationStatus);
                $this->logger->info(
                    'Comment successfully added.',
                    ['Contract id' => $contractId, 'type' => 'annotation', 'status' => $status]
                );
                $this->database->commit();

                return true;
            } catch (Exception $e) {
                $this->database->rollback();
                $this->logger->error($e->getMessage());
            }
        }

        return false;
    }

    /**
     * get all annotation of contact
     *
     * @param $contractId
     * @return array
     */
    public function getContractAnnotations($contractId)
    {
        $annotationData = [];
        $contract       = $this->annotation->getContractPagesWithAnnotations($contractId);
        foreach ($contract->annotations as $annotation) {
            $json = $annotation->annotation;
            try {
                if (!isset($json->category_key)) {
                    $json->category_key = $json->category;
                }
                $json->category = (isset($json->category_key)) ? _l(
                    "codelist/annotation.annotation_category.{$json->category_key}"
                ) : "";
            } catch (Exception $e) {
                $json->category = "";
            }
            $json->page       = $annotation->document_page_no;
            $json->id         = $annotation->id;
            $annotationData[] = $json;
        }

        return ["result" => $annotationData];
    }

    /**
     * updates text or category of annotation
     * @param       $id
     * @param array $data
     * @return bool
     */
    public function update($id, array $data)
    {
        try {
            $status = $this->annotation->updateAnnotationField($id, $data);

            if (!$status) {
                return false;
            }

            $annotation = $this->annotation->getById($id);

            $annotation->status = Annotation::DRAFT;
            $annotation->save();
            $this->logger->activity(
                "annotation.annotation_updated",
                ['contract' => $annotation->contract_id, 'page' => $annotation->document_page_no],
                $annotation->contract_id
            );
            $this->logger->info(
                'Annotation updated.',
                ['id' => $id]
            );

            return true;
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return false;
    }
}
