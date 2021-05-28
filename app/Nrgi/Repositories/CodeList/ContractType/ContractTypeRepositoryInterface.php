<?php namespace App\Nrgi\Repositories\CodeList\ContractType;

/**
 * Contract Type interface
 */
interface ContractTypeRepositoryInterface
{
     /**
     * Save Contract type
     *
     * @param $codelist
     * 
     * @return ContractRType
     */
    public function save($codelist);


    /**
     * Delete contract type
     *
     * @param $id
     * 
     * @return bool
     */
    public function delete($id);

    /**
     * Get all code list
     * 
     * @param $limit
     * 
     * @return array
     */
    public function paginate($limit);

     /**
     * Get all contract type
     *
     * @param $lang
     * 
     * @return array
     */
    public function getContractTypes($lang);


    /**
     * Find resource by ID
     *
     * @param $id
     * 
     * @return ContractType
     */
    public function find($id);
}
