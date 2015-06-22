<?php
namespace App\Nrgi\Repositories\Contract;

/**
 * Interface ContractAnnotationRepositoryInterface
 * @package Nrgi\Repositories\Contract
 */
interface AnnotationRepositoryInterface
{
    /**
     * Save or update contract annotation
     * @param  $contractAnnotationData
     * @return bool
     */
    public function save($contractAnnotationData);

    /**
     * delete contract annotation
     * @param $id
     */
    public function delete($id);

    /**
     * Search
     * @param $params
     * @return Annotation
     */
    public function search(array $params);

    /**
     * contract with pages and pages with annotations
     *
     * @param $contractId
     * @return contract
     */
    public function getContractPagesWithAnnotations($contractId);

    /**
     * updates contract annotation status
     * @param $status
     * @param $contractId
     * @return bool
     */
    public function updateStatus($status, $contractId);

    /**
     * annotation status by contract id
     *
     * @param $contractId
     * @return string
     */
    public function getStatus($contractId);
}
