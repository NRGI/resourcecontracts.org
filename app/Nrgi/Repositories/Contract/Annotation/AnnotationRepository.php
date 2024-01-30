<?php namespace App\Nrgi\Repositories\Contract\Annotation;

use App\Nrgi\Entities\Contract\Annotation\Annotation;
use App\Nrgi\Entities\Contract\Contract;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Contract Annotation Repository
 * Class AnnotationRepository
 *
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
     *
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
     *
     * @return Collection
     */
    public function search(array $params)
    {
        if (isset($params['page'])) {
            return $this->contract->with(
                [
                    'annotations.child' => function ($query) use ($params) {
                        return $query->where('page_no', $params['page']);
                    },
                ]
            )->findOrFail($params['contract']);

        }

        return $this->contract->with(['pages', 'annotations.child'])->findOrFail($params['contract']);
    }

    /**
     * finds or create annotation
     *
     * @param $id
     *
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
     *
     * @return bool|null
     */
    public function delete($id)
    {
        return $this->annotation->destroy($id);
    }

    /**
     * @param $contractId
     *
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
     *
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
     *
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
     *
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
     * annotation status of multiple contracts
     *
     * @param $contractIdArr
     *
     * @return String
     */
    public function getStatusOfAllContracts($contractIdsArr)
    {
        $statusObjectArr = $this->annotation
        ->selectRaw(' distinct(status), contract_id')
        ->whereIn('contract_id', $contractIdsArr)->get()->toArray();
        $status=[];
        foreach($statusObjectArr as $statusObject) {
            $status[$statusObject['contract_id']] = $this->checkStatus([$statusObject['status']]);
        }
    return $status;
    }

    /**
     * annotation status of all contracts
     *
     * @return Array
     */
    public function getAllAnnotationStatus()
    {
        $statusObjectArr = $this->annotation
        ->selectRaw(' distinct(status), contract_id')
        ->get()->toArray();

        $status=[];

        foreach($statusObjectArr as $statusObject) {
            $status[$statusObject['contract_id']] = $this->checkStatus([$statusObject['status']]);
        }

        return $status;
    }

    /**
     * Check annotation status in array
     *
     * @param $status
     *
     * @return string
     */
    public function checkStatus($status)
    {     
        return in_array(Annotation::DRAFT, $status)? Annotation::DRAFT :(in_array(Annotation::REJECTED, $status)? Annotation::REJECTED :Annotation::PUBLISHED);
    }

    /**
     * Get Total Annotation status by type
     *
     * @param $statusType
     *
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
     *
     * @return Annotation
     */
    public function updateField($id, array $data)
    {
        $annotation = $this->annotation->find($id);

        if (array_key_exists('text', $data)) {
            $annotation->text = $data['text'];
        }

        if (array_key_exists('text_trans', $data)) {
            $annotation->text_trans = $data['text_trans'];
        }

        if (array_key_exists('category', $data)) {
            $annotation->category = $data['category'];
        }

        $annotation->status = Annotation::DRAFT;

        $annotation->save();

        return $annotation;
    }

    /**
     * Find annotation by id
     *
     * @param $id
     *
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
     *
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
     *
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
     *
     * @param $contract_id
     *
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
    public function getAnnotationsQuality($key, $filters)
    {
        $from  = 'contract_annotations inner join "contracts" on "contract_annotations"."contract_id" = "contracts"."id" , json_array_elements(contracts.metadata->\'resource\') r,json_array_elements(contracts.metadata->\'category\') cat';
        $query = $this->annotation->where('category', $key);


        if (isset($filters['year']) && $filters['year'] != '' && $filters['year'] != 'all') {
            $query->whereRaw("contracts.metadata->>'signature_year'=?", [$filters['year']]);
        }
        if (isset($filters['resource']) && $filters['resource'] != '' && $filters['resource'] != 'all') {
            $query->whereRaw("trim(both '\"' from r::text) = '".$filters['resource']."'");
        }
        if (isset($filters['country']) && $filters['country'] != '' && $filters['country'] != 'all') {
            $query->whereRaw("exists (
                select 1 
                from json_array_elements(contracts.metadata->'countries') as country 
                where country->>'code' = ?
            )", [$filters['country']]);
        }
        if (isset($filters['category']) && $filters['category'] != '' && $filters['category'] != 'all') {
            $query->whereRaw("trim(both '\"' from cat::text) = '".$filters['category']."'");
        }

        $result = $query->from($this->db->raw($from));
        $result = $result->select('contract_id')->distinct()->get()->toArray();


        return count($result);
    }

    /**
     * Return all the annotations
     * @param category
     *
     * @return Collection
     */
    public function getAllByAnnotation($category) 
    {
        return $this->annotation
                ->whereRaw($this->db->raw("contract_id in (select contract_id from contract_annotations where category='".$category."')"))
                ->get(['id', 'contract_id','category'])->map(function($contract) {
                    $contract->category_key = $contract->category;
                    $contract->category =  (isset($contract->category)) ? getCategoryName($contract->category) : "";
                    return $contract;
                })->groupBy('contract_id');
    }


}
