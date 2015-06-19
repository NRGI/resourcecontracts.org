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
     * @return mixed
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
     * @return mixed
     */
    public function search(array $params);

    /**
     * @param $range
     * @param $contractId
     * @return mixed
     */
    public function getAnnotationByRange($range, $contractId);
}
