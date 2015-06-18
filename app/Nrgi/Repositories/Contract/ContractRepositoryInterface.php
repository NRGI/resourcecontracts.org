<?php namespace App\Nrgi\Repositories\Contract;

use App\Nrgi\Entities\Contract\Contract;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface ContractRepositoryInterface
 */
interface ContractRepositoryInterface
{
    /**
     * Save Contract
     * @param $contractDetail
     * @return Contract
     */
    public function save($contractDetail);

    /**
     * Get all Contracts
     *
     * @param array $filters
     * @return Collection|null
     */
    public function getAll(array $filters);

    /**
     * Get Contract
     *
     * @param $contractId
     * @return Contract
     */
    public function findContract($contractId);

    /**
     * Get Contract with pages
     *
     * @param $contractId
     * @return Contract
     */
    public function findContractWithPages($contractId);

    /**
     * Get Contract with Annotations
     *
     * @param $contractId
     * @return Contract
     */
    public function findContractWithAnnotations($contractId);

    /**
     * Delete contract
     *
     * @param $contractID
     * @return bool
     */
    public function delete($contractID);

    /**
     * Get unique countries
     * @return Contract
     */
    public function getUniqueCountries();

    /**
     * Get unique years
     * @return Contract
     */
    public function getUniqueYears();
}
