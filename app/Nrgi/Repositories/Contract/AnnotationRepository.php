<?php namespace App\Nrgi\Repositories\Contract;

use App\Nrgi\Entities\Contract\Annotation;
use App\Nrgi\Entities\Contract\Contract;
use Illuminate\Database\DatabaseManager;

/**
 * Contract Annotation Repository
 * Class AnnotationRepository
 *
 * @method Illuminate\Database\Query\Builder where()
 * @method void findOrFail()
 * @method void distinct()
 * @package Nrgi\Repositories\Contract
 *
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
        if (isset($params['page'])) {
            return $this->annotation
                ->where('contract_id', $params['contract'])
                ->where('document_page_no', $params['page'])
                ->get();
        }

        return $this->annotation
            ->where('contract_id', $params['contract'])
            ->get();
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
     * @return bool
     */
    public function updateAnnotationField($id, array $data)
    {
        $annotationObj   = $this->annotation->find($id);
        $annotationArray = json_encode($annotationObj->annotation);
        $annotationArray = json_decode($annotationArray, true);
        if (array_key_exists('page', $data)) {
            $annotationObj->document_page_no = $data['page'];
        }
        if (array_key_exists('text', $data)) {
            $annotationArray['text'] = $data['text'];
        }
        if (array_key_exists('section', $data)) {
            $annotationArray['section'] = $data['section'];
        }

        if (array_key_exists('category', $data)) {
            $annotationArray['category'] = $data['category'];
            $annotationArray['cluster']  = _l(config("annotation_category.cluster.{$annotationArray['category']}"));

            $childs = $this->getChildAnnotations($id);

            if ($childs) {
                foreach ($childs as $child) {
                    $annArr             = json_encode($child->annotation);
                    $annArr             = json_decode($annArr, true);
                    $annArr['category'] = $data['category'];
                    $annArr['cluster']  = _l(config("annotation_category.cluster.{$annArr['category']}"));
                    $child->annotation  = $annArr;
                    $child->save();
                }
            }

        }
        $annotationObj->annotation = $annotationArray;

        return $annotationObj->save();
    }

    /**
     * Get Child Annotations
     *
     * @param $annotation_id
     * @return mixed
     */
    public function getChildAnnotations($annotation_id)
    {
        return $this->annotation->whereRaw("annotation->>'parent'='$annotation_id'")->get();
    }
}
