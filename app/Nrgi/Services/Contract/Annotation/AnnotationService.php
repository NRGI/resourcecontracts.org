<?php namespace App\Nrgi\Services\Contract\Annotation;

use App\Nrgi\Entities\Contract\Annotation\Annotation;
use App\Nrgi\Repositories\Contract\Annotation\AnnotationRepositoryInterface;
use App\Nrgi\Services\Contract\Annotation\Page\PageService;
use App\Nrgi\Services\Contract\Comment\CommentService;
use App\Nrgi\Services\Contract\ContractService;
use Exception;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\DatabaseManager;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Contracts\Queue\Queue;

/**
 * Class AnnotationService
 * @package Nrgi\Services\Contract\Annotation
 */
class AnnotationService
{
    /**
     * @var Queue
     */
    public $queue;
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
     * @var CommentService
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
     * @var PageService
     */
    protected $annotation_child;

    /**
     * Constructor
     *
     * @param AnnotationRepositoryInterface $annotation
     * @param Guard                         $auth
     * @param DatabaseManager               $database
     * @param CommentService                $comment
     * @param Log                           $logger
     * @param ContractService               $contract
     * @param Queue                         $queue
     * @param PageService                   $annotation_child
     */

    public function __construct(
        AnnotationRepositoryInterface $annotation,
        Guard $auth,
        DatabaseManager $database,
        CommentService $comment,
        Log $logger,
        ContractService $contract,
        Queue $queue,
        PageService $annotation_child
    ) {
        $this->annotation       = $annotation;
        $this->auth             = $auth;
        $this->database         = $database;
        $this->comment          = $comment;
        $this->logger           = $logger;
        $this->contract         = $contract;
        $this->queue            = $queue;
        $this->annotation_child = $annotation_child;
    }

    /**
     * Store/Update a contact annotation.
     *
     * @param $formData
     *
     * @return Annotation
     */
    public function save($formData)
    {
        $this->database->beginTransaction();

        if (is_null($formData['annotation_id'])) {
            $annotationData = [
                'contract_id' => $formData['contract'],
                'category'    => $formData['category'],
                'text'        => $formData['text'],
                'status'      => Annotation::DRAFT,
            ];
            $annotation     = $this->annotation->create($annotationData);
        } else {
            $annotation         = $this->annotation->find($formData['annotation_id']);
            $annotation->text   = $formData['text'];
            $annotation->status = Annotation::DRAFT;
            $annotation->save();
        }

        $annotationPageData = [
            'annotation_id'     => $annotation->id,
            'page_no'           => $formData['page'],
            'user_id'           => $this->auth->id(),
            'article_reference' => $formData['article_reference'],
        ];

        if (array_key_exists('shapes', $formData)) {
            $annotationPageData['annotation'] = ['shapes' => $formData['shapes']];
        }
        if (array_key_exists('ranges', $formData)) {
            $annotationPageData['annotation'] = ['ranges' => $formData['ranges'], 'quote' => $formData['quote']];
        }

        if (isset($formData['id'])) {
            $formData['annotation_id'] = $this->annotation_child->find($formData['id'])->annotation_id;
            $page                      = $this->annotation_child->update($formData['id'], $annotationPageData);
            $action                    = 'updated';
        } else {
            $page           = $this->annotation_child->save($annotationPageData);
            $action         = 'created';
            $formData['id'] = $page->id;
        }

        if (!$page) {
            $this->database->rollback();

            return false;
        }

        $this->database->commit();

        $this->annotation->deleteIfChildNotFound($formData['annotation_id']);

        $this->logger->activity(
            'annotation.annotation_'.$action,
            ['contract' => $formData['contract'], 'page' => $formData['page']],
            $formData['contract']
        );

        return (object) $formData;
    }

    /**
     * Delete annotation
     *
     * @param $contactAnnotationPageId
     *
     * @return bool
     */
    public function delete($contactAnnotationPageId)
    {
        $annotation = $this->annotation_child->getWithAnnotation($contactAnnotationPageId);

        if ($this->annotation_child->delete($contactAnnotationPageId)) {

            if ($annotation->parent->child->count() == 1) {
                $this->annotation->delete($annotation->parent->id);
            }

            $this->updateStatusOrPublish($annotation->parent->contract_id);

            $this->logger->activity(
                'annotation.annotation_deleted',
                [
                    'contract' => $annotation->parent->contract_id,
                    'page'     => $annotation->page_no,
                    'title'    => $annotation->text,
                ],
                $annotation->parent->contract_id
            );

            $this->logger->info('Annotation deleted', $annotation->toArray());

            return true;
        }

        return false;
    }

    /**
     * Search annotation
     *
     * @param array $params
     *
     * @return array
     */
    public function search(array $params)
    {
        $annotationData = [];
        $contract       = $this->annotation->search($params);
        foreach ($contract->annotations as $annotation) {
            foreach ($annotation->child as $child) {
                $json                    = $child->annotation;
                $json->id                = $child->id;
                $json->annotation_id     = $annotation->id;
                $json->page              = $child->page_no;
                $json->category          = $annotation->category;
                $json->text              = $annotation->text;
                $json->article_reference = $child->article_reference;
                $annotationData[]        = $json;
            }
        }

        return ['total' => count($annotationData), 'rows' => $annotationData];
    }

    /**
     * Get all annotation by contract id
     *
     * @param $contractId
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllByContractId($contractId)
    {
        return $this->annotation->getAllByContractId($contractId);
    }

    /**
     * Get contract with pages and annotations
     *
     * @param $contractId
     *
     * @return \App\Nrgi\Repositories\Contract\contract
     */
    public function getContractPagesWithAnnotations($contractId)
    {
        return $this->annotation->getContractPagesWithAnnotations($contractId);
    }

    /**
     * Get status of annotation
     *
     * @param $contractId
     *
     * @return annotation status
     */
    public function getStatus($contractId)
    {
        return $this->annotation->getStatus($contractId);
    }

    /**
     * Updates status of annotations of contract
     *
     * @param $annotationStatus
     * @param $currentAnnStatus
     * @param $contractId
     *
     * @return bool
     */
    public function updateStatus($annotationStatus, $currentAnnStatus, $contractId)
    {
        $annStatus = $annotationStatus;
        if ($annotationStatus == Annotation::UNPUBLISH) {
            $annStatus = ($currentAnnStatus == Annotation::PUBLISHED) ? 'completed' : $currentAnnStatus;
        }

        $status = $this->annotation->updateStatus($annStatus, $contractId);
        if ($status) {
            if ($annotationStatus == Annotation::PUBLISHED) {
                $this->queue->push(
                    'App\Nrgi\Services\Queue\PostToElasticSearchQueue',
                    ['contract_id' => $contractId, 'type' => 'annotation'],
                    'elastic_search'
                );
            }

            if ($annotationStatus == Annotation::UNPUBLISH) {
                $this->queue->push(
                    'App\Nrgi\Services\Queue\DeleteElementQueue',
                    ['contract_id' => $contractId, 'type' => 'annotation'],
                    'elastic_search'
                );
            }
            $this->logger->activity(
                "contract.log.status",
                ['type' => 'annotation', 'new_status' => $annotationStatus],
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
     * Adds annotation comment to contract
     *
     * @param $contractId
     * @param $message
     * @param $annotationStatus
     *
     * @return bool
     */
    public function comment($contractId, $message, $annotationStatus, $currentAnnStatus)
    {
        $this->database->beginTransaction();
        $status = $this->updateStatus($annotationStatus, $currentAnnStatus, $contractId);

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
     * Get all annotation of contact
     *
     * @param $contractId
     *
     * @return array
     */
    public function getContractAnnotations($contractId)
    {
        $annotationData = [];
        $contract       = $this->annotation->getContractPagesWithAnnotations($contractId);
        foreach ($contract->annotations as $annotation) {
            foreach ($annotation->child as $child) {
                $json                    = $child->annotation;
                $json->id                = $child->id;
                $json->annotation_id     = $annotation->id;
                $json->page              = $child->page_no;
                $json->category_key      = $annotation->category;
                $json->category          = (isset($annotation->category)) ? getCategoryName(
                    $annotation->category,
                    true
                ) : "";
                $json->cluster           = (isset($annotation->category)) ? getCategoryClusterName(
                    $annotation->category,
                    true
                ) : "";
                $json->text              = $annotation->text;
                $json->article_reference = $child->article_reference;
                $annotationData[]        = $json;
            }
        }

        return ["result" => $annotationData];
    }

    /**
     * Updates text or category of annotation
     *`
     *
     * @param       $id
     * @param array $data
     *
     * @return bool
     */
    public function update($id, array $data)
    {
        $annotation       = ['text', 'category'];
        $annotation_child = ['page_no', 'article_reference'];

        $key = key($data);
        try {

            if (in_array($key, $annotation)) {
                $annotation  = $this->annotation->updateField($id, $data);
                $contract_id = $annotation->contract_id;
                $page_no     = null;
            }

            if (in_array($key, $annotation_child)) {
                $page        = $this->annotation_child->updateChildField($id, $data);
                $parent      = $this->annotation->find($page->annotation_id);
                $contract_id = $parent->contract_id;
                $page_no     = $page->page_no;
                $this->annotation->updateStatus(Annotation::DRAFT, $contract_id);
            }

            $this->logger->activity(
                "annotation.annotation_updated",
                ['contract' => $contract_id, 'page' => $page_no],
                $contract_id
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

    /**
     * Update Annotation status or publish
     *
     * @param $id
     */
    public function updateStatusOrPublish($id)
    {
        try {
            $collection = $this->annotation->getAllByContractId($id);
            if ($collection->count() > 0) {
                $this->updateStatus(Annotation::UNPUBLISH, Annotation::PUBLISHED, $id);
            }
        } catch (\Exception $e) {
            $this->logger->activity(
                "contract.log.status",
                ['type' => 'annotation', 'new_status' => Annotation::UNPUBLISH],
                $id
            );
            $this->queue->push(
                'App\Nrgi\Services\Queue\PostToElasticSearchQueue',
                ['contract_id' => $id, 'type' => 'annotation'],
                'elastic_search'
            );
        }
    }
}
