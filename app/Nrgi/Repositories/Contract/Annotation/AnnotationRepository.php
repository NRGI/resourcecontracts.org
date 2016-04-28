<?php namespace App\Nrgi\Repositories\Contract\Annotation;

use App\Nrgi\Entities\Contract\Annotation\Annotation;
use App\Nrgi\Entities\Contract\Contract;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Collection;

/**
 * Contract Annotation Repository
 * Class AnnotationRepository
 *
 * @method Illuminate\Database\Query\Builder where()
 * @method void findOrFail()
 * @method void distinct()
 * @package Nrgi\Repositories\Contract\Annotation
 *
 */
class AnnotationRepository implements AnnotationRepositoryInterface
{
    /**
     * @var Annotation
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
     * @param array $annotationData
     * @return Annotation
     */
    public function create($annotationData)
    {
        return $this->annotation->create($annotationData);
    }

    /**
     *
     *
     * @param array $params
     * @return Collection
     */
    public function search(array $params)
    {
        if (isset($params['page'])) {
            return $this->contract->with(
                [
                    'annotations.child' => function ($query) use ($params) {
                        return $query->where('page_no', $params['page']);
                    }
                ]
            )->findOrFail($params['contract']);

        }

        return $this->contract->with(['pages', 'annotations.child'])->findOrFail($params['contract']);
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
        return $this->annotation->destroy($id);
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
        return $this->contract->with(['pages', 'annotations.child'])->findOrFail($contractId);
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
        $rowsUpdated = $this->annotation->where('contract_id', $contractId)->update(['status' => $status]);

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
        return $this->contract
            ->distinct()
            ->select('contracts.id', 'a.status')
            ->from('contracts')
            ->leftJoin(
                'contract_annotations as a',
                function ($join) use ($statusType) {
                    $join->on('contracts.id', '=', 'a.contract_id')->where('a.status', '=', $statusType);
                }
            )
            ->get();
    }

    /**
     * updated annotation text or category
     *
     * @param       $id
     * @param array $data
     * @return Annotation
     */
    public function updateField($id, array $data)
    {
        $annotation = $this->annotation->find($id);

        if (array_key_exists('text', $data)) {
            $annotation->text = $data['text'];
        }

        if (array_key_exists('category', $data)) {
            $annotation->category = $data['category'];
        }

        $annotation->save();

        return $annotation;
    }

    /**
     * Find annotation by id
     *
     * @param $id
     * @return mixed
     */
    public function find($id)
    {
        return $this->annotation->find($id);
    }

    /**
     * Delete Annotation If child Not found
     *
     * @param $annotation_id
     * @return boolean
     */
    public function deleteIfChildNotFound($annotation_id)
    {
        if (is_null($annotation_id)) {
            return false;
        }

        $annotation = $this->annotation->with('child')->find($annotation_id);

        $count = $annotation->child->count();
        if ($count < 1) {
            return $annotation->delete();
        }

        return true;
    }

    /**
     * Count Contracts By user
     *
     * @param $user_id
     * @return int
     */
    public function countByUser($user_id)
    {
        return $this->annotation->where('user_id', $user_id)->count();
    }

    /**
     * Return all the annotations order by contract_id
     *
     * @return array
     */
    public function getAllAnnotations()
    {
        return $this->annotation->orderBy('contract_id')->get();
    }

    /**
     * Return all the annotations of a specific contract
     * @param $contract_id
     * @return object
     */
    public function getAnnotationByContractId($contract_id)
    {
        return $this->annotation->where('contract_id', $contract_id)->get();
    }

    /*
     * Check if category of annotations exist or not.
     *
     * @param $key
     * @return array
     */
    public function getAnnotationsQuality($key)
    {
        $result = $this->annotation->select('contract_id')->where("category", $key)->distinct('contract_id')->get();

        return $result->toArray();
    }
}
