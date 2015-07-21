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
        $this->db       = $db;
    }

    /**
     * Save Contract
     *
     * @param $contractDetail
     * @return Contract
     */
    public function save($contractDetail)
    {
        return $this->contract->create($contractDetail);
    }

    /**
     * Get All Contracts
     *
     * @param array $filters
     * @return Collection|static[]
     */
    public function getAll(array $filters)
    {
        $query = $this->contract->select('*');
        $from  = "contracts as c";

        $filters = array_map('trim', $filters);
        extract($filters);

        if ($year != '' && $year != 'all') {
            $query->whereRaw(sprintf("c.metadata->>'signature_year'='%s'", $year));
        }

        if ($country != '' && $country != 'all') {
            $query->whereRaw(sprintf("c.metadata->'country'->>'code'='%s'", $country));
        }

        if ($resource != '' && $resource != 'all') {
            $from .= ",json_array_elements(c.metadata->'resource') r";
            $query->whereRaw("trim(both '\"' from r::text) = '" . $resource . "'");
        }

        if ($category != '' && $category != 'all') {
            $from .= ",json_array_elements(c.metadata->'category') cat";
            $query->whereRaw("trim(both '\"' from cat::text) = '" . $category . "'");
        }

        $query->from($this->db->raw($from));

        return $query->orderBy('created_datetime', 'DESC')->get();
    }

    /**
     * Get unique contract years
     *
     * @return contract
     */
    public function getUniqueYears()
    {
        return $this->contract->select(
            $this->db->raw("metadata->>'signature_year' years, count(metadata->>'signature_year')")
        )
                              ->whereRaw("metadata->>'signature_year' !=''")
                              ->groupBy($this->db->raw("metadata->>'signature_year'"))
                              ->orderBy($this->db->raw("metadata->>'signature_year'"), "DESC")->get();
    }

    /**
     * Get unique countries
     *
     * @return contract
     */
    public function getUniqueCountries()
    {
        return $this->contract->select(
            $this->db->raw("metadata->'country'->>'code' countries, count(metadata->'country'->>'code')")
        )
                              ->whereRaw("metadata->'country'->>'code' !=''")
                              ->groupBy($this->db->raw("metadata->'country'->>'code'"))
                              ->orderBy($this->db->raw("metadata->'country'->>'code'"), "DESC")->get();
    }

    /**
     * Get unique resources
     *
     * @return contract
     */
    public function getUniqueResources()
    {
        return $this->contract->select($this->db->raw("DISTINCT trim(both '\"' from r::text) as resource"))
                              ->from($this->db->raw("contracts, json_array_elements(metadata->'resource') r"))
                              ->orderBy('resource', 'ASC')->get();
    }

    /**
     * Get Contract
     *
     * @param $contractId
     * @return Contract
     */
    public function findContract($contractId)
    {
        return $this->contract->with('created_user', 'updated_user')->findOrFail($contractId);
    }

    /**
     * Get Contract with tasks
     *
     * @param $contractId
     * @return Contract
     */
    public function findContractWithTasks($contractId)
    {
        return $this->contract->with(
            [
                'tasks' => function ($query) {
                    $query->orderBy('page_no', 'ASC');
                }
            ]
        )->findOrFail($contractId);
    }

    /**
     * Get Contract with pages
     *
     * @param $contractId
     * @return Contract
     */
    public function findContractWithPages($contractId)
    {
        return $this->contract->with('created_user', 'updated_user', 'pages')->findOrFail($contractId);
    }


    /**
     * Get Contract with Annotations
     *
     * @param $contractId
     * @return Contract
     */
    public function findContractWithAnnotations($contractId)
    {
        return $this->contract->with('created_user', 'updated_user', 'annotations')->findOrFail($contractId);
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

    /**
     * Get Contract by file hash
     *
     * @param $fileHash
     * @return Contract
     */
    public function getContractByFileHash($fileHash)
    {
        return $this->contract->where('filehash', $fileHash)->first();
    }

    /**
     * Count total contracts by date
     * @param string $date
     * @return Collection
     */
    public function countTotal($date = '')
    {
        if (is_array($date)) {
            return $this->contract->whereRaw("to_char(created_datetime, 'YYYY-MM-DD') >= '$date[0]'")->whereRaw(
                "to_char(created_datetime, 'YYYY-MM-DD') <= '$date[1]'"
            )->count();
        }

        if ($date != '') {
            return $this->contract->whereRaw("to_char(created_datetime, 'YYYY-MM-DD') = '$date'")->count();
        }

        return $this->contract->count();
    }

    /**
     * Get Recent Contracts
     *
     * @param $no
     * @return collection
     */
    public function recent($no)
    {
        return $this->contract->with('created_user')->orderBy('created_datetime', 'DESC')->take($no)->get();
    }

    /**
     * Get Contract count by status
     *
     * @param $statusType
     * @return array
     */
    public function statusCount($statusType)
    {
        return $this->contract->selectRaw("$statusType as status, COUNT(*)")->groupBy($statusType)->get()->toArray();
    }

    /**
     * Get Contract List
     * @return mixed
     */
    public function getList()
    {
        return $this->contract->all();
    }

    /**
     * Get Contracts having MTurk Tasks
     *
     * @return collection
     */
    public function getMTurkContracts()
    {
        return $this->contract->with('tasks')->where('mturk_status', '!=', '')->get();
    }

}
