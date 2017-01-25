<?php
namespace App\Nrgi\Repositories\Contract\Annotation;

use App\Nrgi\Entities\Contract\Annotation\Annotation;
use App\Nrgi\Entities\Contract\Contract;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface ContractAnnotationRepositoryInterface
 * @package Nrgi\Repositories\Contract
 */
interface AnnotationRepositoryInterface
{
    /**
     * Create contract annotation
     *
     * @param  $contractAnnotationData
     *
     * @return bool
     */
    public function create($contractAnnotationData);

    /**
     * Delete a annotation.
     *
     * @param  int $id
     *
     * @return bool|null
     */
    public function delete($id);

    /**
     * Search Annotations
     *
     * @param $params
     *
     * @return Collection
     */
    public function search(array $params);

    /**
     * Contract with pages and pages with annotations
     *
     * @param $contractId
     *
     * @return Contract
     */
    public function getContractPagesWithAnnotations($contractId);

    /**
     * Update contract annotation status
     *
     * @param $status
     * @param $contractId
     *
     * @return bool
     */
    public function updateStatus($status, $contractId);

    /**
     * Annotation status by contract id
     *
     * @param $contractId
     *
     * @return string
     */
    public function getStatus($contractId);

    /**
     * Get Total Annotation status by type
     *
     * @param $statusType
     *
     * @return array
     */
    public function getStatusCountByType($statusType);

    /**
     * Update annotation category or text
     *
     * @param       $id
     * @param array $data
     *
     * @return Annotation
     */
    public function updateField($id, array $data);

    /**
     * Find annotation by id
     *
     * @param $id
     *
     * @return Annotation
     */
    public function find($id);

    /**
     * Delete Annotation If child Not found
     *
     * @param $annotation_id
     *
     * @return boolean
     */
    public function deleteIfChildNotFound($annotation_id);

    /**
     * Return all the annotations of a specific contract
     *
     * @param $contract_id
     *
     * @return object
     */
    public function getAnnotationByContractId($contract_id);

    /**
     * Check if category of annotations exist or not.
     *
     * @param $key
     *
     * @param $filters
     *
     * @return array
     */
    public function getAnnotationsQuality($key, $filters);

    /**
     * Get all annotations by contract id
     *
     * @param $contract_id
     *
     * @return Collection
     */
    public function getAllByContractId($contract_id);

}
