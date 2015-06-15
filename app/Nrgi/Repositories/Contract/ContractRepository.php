<?php namespace App\Nrgi\Repositories\Contract;

use App\Nrgi\Entities\Contract\Contract;
use Illuminate\Database\DatabaseManager;
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
     * @var DatabaseManager
     */
    protected $db;

    /**
     * @param Contract        $contract
     * @param DatabaseManager $db
     */
    public function __construct(Contract $contract, DatabaseManager $db)
    {
        $this->contract = $contract;
        $this->db = $db;
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

        if ($country != '' && $country != 'all') {
            $query->whereRaw(sprintf("metadata->'country'->>'code'='%s'", $country));
        }

        return $query->orderBy('created_datetime', 'DESC')->get();
    }


    /**
     * Get unique contract years
     * @return contract
     */
    function getUniqueYears()
    {
        return $this->contract->select($this->db->raw("metadata->>'signature_year' years, count(metadata->>'signature_year')"))
                              ->whereRaw("metadata->>'signature_year' !=''")
                              ->groupBy($this->db->raw("metadata->>'signature_year'"))
                              ->orderBy($this->db->raw("metadata->>'signature_year'"), "DESC")->get();
    }

    /**
     * Get unique countries
     * @return contract
     */
    function getUniqueCountries()
    {
        return $this->contract->select($this->db->raw("metadata->'country'->>'code' countries, count(metadata->'country'->>'code')"))
                              ->whereRaw("metadata->'country'->>'code' !=''")
                              ->groupBy($this->db->raw("metadata->'country'->>'code'"))
                              ->orderBy($this->db->raw("metadata->'country'->>'code'"), "DESC")->get();
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
