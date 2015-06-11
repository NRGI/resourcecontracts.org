<?php namespace App\Nrgi\Repositories\Contract;

use App\Nrgi\Entities\Contract\Contract;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class ContractRepository
 * @package App\Nrgi\Repositories\Contract
 */
class ContractRepository implements ContractRepositoryInterface
{
    /**
     * @var Contract
     */
    protected $contract;

    /**
     * @param Contract $contract
     */
    public function __construct(Contract $contract)
    {
        $this->contract = $contract;
    }

    /**
     * Save Contract
     * @param $contractDetail
     * @return Contract
     */
    public function save($contractDetail)
    {
        return $this->contract->create($contractDetail);
    }

    /**
     * Get All Contracts
     * @return Collection|static[]
     */
    public function getAll()
    {
        return $this->contract->orderBy('created_datetime', 'DESC')->get();
    }

    /**
     * Get Contract or throw exception
     *
     * @param $contractId
     * @return Contract
     */
    public function findContract($contractId)
    {
        return $this->contract->findOrFail($contractId);
    }

    /**
     * Get Contract with pages or throw exception
     *
     * @param $contractId
     * @return Contract
     */
    public function findContractWithPages($contractId)
    {
        return $this->contract->with('pages')->findOrFail($contractId);
    }

    /**
     * Delete contract
     *
     * @param $contractID
     * @return bool
     */
    public function delete($contractID)
    {
        return $this->contract->destroy($contractID);
    }
}
