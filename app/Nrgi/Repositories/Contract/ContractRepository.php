<?php namespace App\Nrgi\Repositories\Contract;

use App\Nrgi\Entities\Contract\Contract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

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
     * @param array $filters
     * @return Collection|static[]
     */
    public function getAll(array $filters)
    {
        $query   = $this->contract->select('*');
        $filters = array_map('trim', $filters);
        extract($filters);

        if ($year != '' && $year != 'all') {
            $query->whereRaw(sprintf("metadata->>'signature_year'='%s'", $year));
        }

       /* if ($resource != '' && $resource != 'all') {
            $query->whereRaw(sprintf("metadata->>'resource'='%s'", $resource));
        }*/

        if ($country != '' && $country != 'all') {
            $query->whereRaw(sprintf("metadata->'country'->>'code'='%s'", $country));
        }

        return $query->orderBy('created_datetime', 'DESC')->get();
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
