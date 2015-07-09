<?php namespace App\Nrgi\Repositories\Contract;

use App\Nrgi\Entities\Contract\Annotation;
use App\Nrgi\Entities\Contract\Contract;
use Illuminate\Database\DatabaseManager;

/**
 * Contract Annotation Repository
 * Class AnnotationRepository
 * @package Nrgi\Repositories\Contract
 */
class AnnotationRepository implements AnnotationRepositoryInterface
{
    /**
     * @var Annotation annotation
     */
    protected $annotation;

    /**
     * @var Contract
     */
    protected $contract;
    /**
     * @var DatabaseManager
     */
    protected $db;

    /**
     * @param Annotation      $annotation
     * @param Contract        $contract
     * @param DatabaseManager $db
     */
    public function __construct(Annotation $annotation, Contract $contract, DatabaseManager $db)
    {
        $this->annotation = $annotation;
        $this->contract   = $contract;
        $this->db         = $db;
    }

    /**
     * Save Annotation
     *
     * @param array $contractAnnotation
     * @return annotation
     */
    public function save($contractAnnotation)
    {
        return $contractAnnotation->save();
    }

    /**
     * @param array $params
     * @return Collection
     */
    public function search(array $params)
    {
        $annotations = $this->annotation
            ->where('contract_id', $params['contract'])
            ->where('document_page_no', $params['document_page_no'])
            ->get();

        return $annotations;
    }

    /**
     * finds or create annotation
     * @param $id
     * @return Annotation
     */
    public function findOrCreate($id)
    {
        return $this->annotation->findOrNew($id);
    }

    /**
     * Delete a annotation.
     *
     * @param  int $id
     * @return bool|null
     */
    public function delete($id)
    {
        return $this->getById($id)->delete();
    }

    /**
     * Get Model by id.
     *
     * @param  int $id
     * @return Annotation
     */
    public function getById($id)
    {
        return $this->annotation->findOrFail($id);
    }

    /**
     * @param $contractId
     * @return mixed
     */
    public function getAllByContractId($contractId)
    {
        $contactAnnotation = $this->contract->with('annotations')->findOrFail($contractId);

        return $contactAnnotation->annotations;
    }

    /**
     * contract with pages and annotations
     *
     * @param $contractId
     * @return Contract
     */
    public function getContractPagesWithAnnotations($contractId)
    {
        return $this->contract->with('pages.annotations')->findOrFail($contractId);
    }

    /**
     * Updates all annotation of contract
     *
     * @param $status
     * @param $contractId
     * @return bool
     */
    public function updateStatus($status, $contractId)
    {
        $rowsUpdated = $this->annotation->where('contract_id', $contractId)->update(array('status' => $status));

        return ($rowsUpdated > 0 ? true : false);
    }

    /**
     * contract annotation status
     *
     * @param $contractId
     * @return String
     */
    public function getStatus($contractId)
    {
        $statusObject = $this->annotation
            ->distinct()
            ->select('status')
            ->where('contract_id', $contractId)->get()->toArray();

        $status = $this->checkStatus(array_column($statusObject, 'status'));

        return $status;
    }

    /**
     * Check annotation status in array
     *
     * @param $status
     * @return string
     */
    public function checkStatus($status)
    {
        $annotationStatus = '';
        if (in_array(Annotation::DRAFT, $status)) {
            $annotationStatus = Annotation::DRAFT;
        } elseif (in_array(Annotation::COMPLETED, $status)) {
            $annotationStatus = Annotation::COMPLETED;
        } elseif (in_array(Annotation::REJECTED, $status)) {
            $annotationStatus = Annotation::REJECTED;
        } elseif (in_array(Annotation::PUBLISHED, $status)) {
            $annotationStatus = Annotation::PUBLISHED;
        }

        return $annotationStatus;
    }

    /**
     * Get Total Annotation status by type
     * @param $statusType
     * @return mixed
     */
    public function getStatusCountByType($statusType)
    {
        return $this->annotation->distinct()
                                ->select('c.id', 'a.status')
                                ->from('contracts as c')
                                ->leftJoin(
                                    'contract_annotations as a',
                                    function ($join) use ($statusType) {
                                        $join->on('c.id', '=', 'a.contract_id')->where('a.status', '=', $statusType);
                                    }
                                )
                                ->get();
    }
}
