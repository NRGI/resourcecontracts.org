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
     * @return Collection|null
     */
    public function getAll();

    /**
     * Get Contract or throw exception
     *
     * @param $contractId
     * @return Contract
     */
    public function findContract($contractId);

    /**
     * Delete contract
     *
     * @param $contractID
     * @return bool
     */
    public function delete($contractID);
}
