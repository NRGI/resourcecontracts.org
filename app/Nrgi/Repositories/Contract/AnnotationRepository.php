<?php namespace App\Nrgi\Repositories\Contract;

use App\Nrgi\Entities\Contract\Annotation;
use App\Nrgi\Entities\Contract\Contract;

/**
 * Contract Annotation Repository
 * Class AnnotationRepository
 * @package Nrgi\Repositories\Contract
 */
class AnnotationRepository implements AnnotationRepositoryInterface
{
    /**
     * @var Annotation model
     */
    protected $model;

    protected $contract;

    /**
     * @param Annotation $annotation
     */
    public function __construct(Annotation $annotation, Contract $contract)
    {
        $this->model = $annotation;
        $this->contract = $contract;
    }

    /**
     * @param array $contractAnnotation
     * @return mixed
     */
    public function save($contractAnnotation)
    {
        $contractAnnotation->save();

        return $contractAnnotation;
    }

    /**
     *  @param array $params
     * @return string
     */
    public function search(array $params)
    {
        $annotations = $this->model
                        ->where('contract_id', $params['contract'])
                        ->where('document_page_no', $params['document_page_no'])
                        ->get();

        return $annotations;
    }

    /**
     * @param $range
     * @param $contractId
     * @return null/id of contract annotations id
     */
    public function getAnnotationByRange($range, $contractId)
    {
        $result = \DB::select(\DB::raw("select id from contract_annotations r, json_array_elements(r.annotation->'ranges') obj
            where obj->>'startOffset' = :startOffset
            and obj->>'endOffset' = :endOffset
            and obj->>'start' = :start
            and obj->>'end' = :end
            and r.contract_id = :contractId"),
            array(
            'contractId'    => $contractId,
            'start'         => $range['start'],
            'startOffset'   => $range['startOffset'],
            'end'           => $range['end'],
            'endOffset'     => $range['endOffset']
            ));

        if (!$result) {
            return null;
        }

        return $result[0]->id;
    }

    /**
     * @param $id
     * @return \Illuminate\Support\Collection|static
     */
    public function findOrCreate($id)
    {
        return $this->model->findOrNew($id);
    }

    /**
     * Delete a model.
     *
     * @param  int $id
     * @return void
     */
    public function delete($id)
    {
        return $this->getById($id)->delete();
    }

    /**
     * Get Model by id.
     *
     * @param  int  $id
     * @return App\Models\Model
     */
    public function getById($id)
    {
        return $this->model->findOrFail($id);
    }

    /**
     * @param $contractId
     * @return mixed
     */
    public function getAllByContractId($contractId)
    {
        $contactAnnotion = $this->contract->with('annotations')->findOrFail($contractId);
        return $contactAnnotion->annotations;
    }
}
