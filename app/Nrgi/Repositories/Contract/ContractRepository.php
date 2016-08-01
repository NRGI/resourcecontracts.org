<?php namespace App\Nrgi\Repositories\Contract;

use App\Nrgi\Entities\Contract\Annotation\Annotation;
use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Entities\SupportingContract\SupportingContract;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class ContractRepository
 *
 * @method void where()
 * @method void findOrFail()
 * @method void distinct()
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
     * @var SupportingContract
     */
    protected $document;

    /**
     * @param Contract           $contract
     * @param DatabaseManager    $db
     * @param SupportingContract $document
     */
    public function __construct(Contract $contract, DatabaseManager $db, SupportingContract $document)
    {
        $this->contract = $contract;
        $this->db       = $db;
        $this->document = $document;
    }

    /**
     * Save Contract
     *
     * @param $contractDetail
     *
     * @return Contract
     */
    public function save($contractDetail)
    {
        $contract = $this->contract->create($contractDetail);

        return $contract;
    }

    /**
     * Get All Contracts
     *
     * @param array $filters
     *
     * @return Collection|static[]
     */
    public function getAll(array $filters, $limit = null)
    {
        $query         = $this->contract->select('*');
        $from          = "contracts ";
        $multipleField = ["resource", "category", "type_of_contract"];
        $filters       = array_map('trim', $filters);
        extract($filters);
        $operator = (!empty($issue) && $issue == "present") ? "!=" : "=";

        if (isset($year) && $year != '' && $year != 'all') {
            $query->whereRaw("contracts.metadata->>'signature_year'=?", [$year]);
        }
        if (isset($type) && $type == 'metadata' && $status != '') {

            $query->whereRaw("contracts.metadata_status=?", [$status]);
        }
        if (isset($type) && $type == 'ocr' && $status != '') {
            if ($status == "null") {
                $query->whereRaw("contracts.\"textType\" is null");
            } else {
                $query->whereRaw("contracts.\"textType\"=?", [$status]);
            }
        }

        if (isset($type) && $type == 'pdftext' && $status != '') {
            if ($status == "null") {
                $query->whereRaw("contracts.text_status is null");
            } else {
                $query->whereRaw("contracts.text_status=?", [$status]);
            }
        }
        if (isset($country) && $country != '' && $country != 'all') {
            $query->whereRaw("contracts.metadata->'country'->>'code' = ?", [$country]);
        }


        if (isset($resource) && $resource != '' && $resource != 'all') {
            $from .= ",json_array_elements(contracts.metadata->'resource') r";
            $query->whereRaw("trim(both '\"' from r::text) = '" . $resource . "'");
        }

        if (isset($category) && $category != '' && $category != 'all') {
            $from .= ",json_array_elements(contracts.metadata->'category') cat";
            $query->whereRaw("trim(both '\"' from cat::text) = '" . $category . "'");
        }
        if (isset($type) && $type == "metadata" && $word != '' && $issue != '' && !in_array($word, $multipleField)) {
            if ($word == 'company') {
                $query = $query->whereRaw(sprintf("contracts.metadata->'company'->0->>'name' %s ''", $operator));
            } elseif ($word == 'concession') {
                $query = $query->whereRaw(sprintf("contracts.metadata->'concession'->0->>'license_name' %s ''", $operator));
            } elseif ($word == 'government_entity') {
                $query = $query->whereRaw(sprintf("contracts.metadata->'government_entity'->0->>'entity' %s ''", $operator));
            } else {
                $query->whereRaw(sprintf("contracts.metadata->>'%s' %s''", $word, $operator));
            }

        }
        if (isset($type) && $type == "metadata" && $word != '' && $issue != '' && in_array($word, $multipleField)) {
            $query->whereRaw(sprintf(" json_array_length(metadata->'%s') %s 0", $word, $operator));
        }
        if (isset($type) && $type == 'annotations' && $status != '') {
            $query->whereRaw("contracts.metadata_status=?", [$status]);
        }
        if (isset($type) && $type == "annotations" && $word != '' && $issue != '') {
            $contractsId = DB::table('contract_annotations')->select(DB::raw('contract_id'))->whereRaw(
                "category = ?",
                [$word]
            )->distinct('contract_id')->get();
            $contracts   = [];
            foreach ($contractsId as $contractId) {
                array_push($contracts, $contractId->contract_id);
            }
            if ($issue == "present") {
                $query->whereIn("id", $contracts);
            } else {
                $query->whereNotIn("id", $contracts);
            }
        }
        if (isset($q) && $q != '') {

            $q = '%' . $q . '%';
            $query->whereRaw("contracts.metadata->>'contract_name' ILIKE ?", [$q]);
        }
        $query->from($this->db->raw($from))->orderBy('created_datetime', 'DESC');
        if (is_null($limit)) {
            return $query->get();
        }

        return $query->paginate($limit);
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
                              ->orderBy($this->db->raw("metadata->'country'->>'code'"), "ASC")->get();
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
     *
     * @return Contract
     */
    public function findContract($contractId)
    {
        return $this->contract->with('created_user', 'updated_user')->findOrFail($contractId);
    }

    /**
     * Get Contract with tasks
     *
     * @param      $contractId
     * @param null $status
     * @param null $approved
     *
     * @return Contract
     */
    public function findContractWithTasks($contractId, $status = null, $approved = null)
    {
        return $this->contract->with(
            [
                'tasks' => function ($query) use ($status, $approved) {
                    if (!is_null($status)) {
                        $query->where('status', $status);
                    }

                    if (!is_null($approved)) {
                        $query->where('approved', $approved);
                    }

                    $query->orderBy('page_no', 'ASC');
                },
            ]
        )->findOrFail($contractId);
    }

    /**
     * Get Contract with pages
     *
     * @param $contractId
     *
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
     *
     * @return Contract
     */
    public function findContractWithAnnotations($contractId)
    {
        $contract = $this->contract->with('annotations.child', 'created_user', 'updated_user')->findOrFail($contractId);

        return $contract;
    }

    /**
     * Delete contract
     *
     * @param $contractID
     *
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
     *
     * @return Contract
     */
    public function getContractByFileHash($fileHash)
    {
        return $this->contract->where('filehash', $fileHash)->first();
    }

    /**
     * Count total contracts by date
     *
     * @param string $date
     *
     * @return Collection
     */
    public function countTotal($date = '')
    {
        if (is_array($date)) {
            return $this->contract->whereRaw("date(created_datetime) >= '$date[0]'")->whereRaw(
                "date(created_datetime) <= '$date[1]'"
            )->count();
        }

        if ($date != '') {
            return $this->contract->whereRaw("date(created_datetime) = '$date'")->count();
        }

        return $this->contract->count();
    }

    /**
     * Get Recent Contracts
     *
     * @param $no
     *
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
     *
     * @return array
     */
    public function statusCount($statusType)
    {
        return $this->contract->selectRaw(sprintf("contracts.\"%s\" as status, COUNT(*)", $statusType))->groupBy(
            $statusType
        )->get()->toArray();
    }

    /**
     * Get Contract List
     * @return Collection
     */
    public function getList()
    {
        return $this->contract->orderByRaw("metadata->>'contract_name' ASC")->get();
    }

    /**
     * Get Contracts having MTurk Tasks
     *
     * @param array $filter
     * @param int   $perPage
     *
     * @return Collection
     */
    public function getMTurkContracts(array $filter = [], $perPage = null)
    {
        $query = $this->contract->with('tasks');

        if (isset($filter['status']) && !is_null($filter['status'])) {
            $query->where('mturk_status', $filter['status']);
        }

        $cat_list = array_keys(config('metadata.category'));
        if (isset($filter['category']) && in_array($filter['category'], $cat_list)) {
            $query->whereRaw("metadata->'category'->>0 ='" . $filter['category'] . "'");
        }

        $query->orderBy('created_datetime', 'DESC');


        if (!is_null($perPage)) {
            return $query->paginate($perPage);
        }


        return $query->get();
    }

    /**
     * Get Contract with pdf process status
     *
     * @param $status
     *
     * @return Collection
     */
    public function getContractWithPdfProcessingStatus($status)
    {
        return $this->contract->where('pdf_process_status', $status)->get();
    }

    /**
     * Get the count of presence of contract's metadatas
     *
     * @param $metadata
     *
     * @return collection
     */
    public function getMetadataQuality($metadata = '', $filters)
    {
        $from  = "contracts ";
        $query = $this->contract->select('*');

        if (isset($filters['year']) && $filters['year'] != '' && $filters['year'] != 'all') {
            $query->whereRaw("contracts.metadata->>'signature_year'=?", [$filters['year']]);
        }
        if (isset($filters['resource']) && $filters['resource'] != '' && $filters['resource'] != 'all') {
            $from .= ",json_array_elements(contracts.metadata->'resource') r";
            $query->whereRaw("trim(both '\"' from r::text) = '" . $filters['resource'] . "'");
        }
        if (isset($filters['country']) && $filters['country'] != '' && $filters['country'] != 'all') {
            $query->whereRaw("contracts.metadata->'country'->>'code' = ?", [$filters['country']]);
        }
        if (isset($filters['category']) && $filters['category'] != '' && $filters['category'] != 'all') {
            $from .= ",json_array_elements(contracts.metadata->'category') cat";
            $query->whereRaw("trim(both '\"' from cat::text) = '" . $filters['category'] . "'");
        }
        if (!empty($metadata) && in_array($metadata, ['company', 'concession', 'government_entity'])) {

            if ($metadata == 'company') {
                $query = $query->whereRaw("contracts.metadata->'company'->0->>'name'!=''");
            }
            if ($metadata == 'concession') {
                $query = $query->whereRaw("contracts.metadata->'concession'->0->>'license_name'!=''");
            }
            if ($metadata == 'government_entity') {
                $query = $query->whereRaw("contracts.metadata->'government_entity'->0->>'entity'!=''");
            }


        } else {
            if (!empty($metadata)) {
                $query = $query->whereRaw(sprintf("contracts.metadata->>'%s'!=''", $metadata));
            }
        }

        $result = $query->from($this->db->raw($from))
                        ->count();

        return $result;
    }

    /**
     * Check if category of annotations exist or not.
     *
     * @param $key
     *
     * @return array
     */
    public function getAnnotationsQuality($key)
    {
        $from   = "contract_annotations";
        $result = $this->contract->select('contract_id')->whereRaw(
            "contract_annotations.annotation->>'category'= ? ",
            [$key]
        )
                                 ->from($from)
                                 ->distinct('contract_id')
                                 ->get();

        return $result->toArray();
    }

    /**
     * Get the count of total contracts
     *
     * @return integer
     */
    public function getTotalContractCount()
    {
        return $this->contract->count();
    }

    /**
     * To save the supporting documents of contracts
     *
     * @param $documents
     *
     * @return bool
     */
    public function saveSupportingDocument($documents)
    {
        return $this->contract->SupportingContract()->attach($documents);
    }

    /**
     * Get the contract name and id
     *
     * @param array $id
     *
     * @return collection
     */
    public function getSupportingContracts($id)
    {
        return $this->contract->select(DB::raw("id, metadata->>'contract_name' as contract_name"))
                              ->whereIn('id', $id)
                              ->orderByRaw("metadata->>'contract_name' ASC")
                              ->get()
                              ->toArray();

    }

    /**
     * Return the Parent contract id
     *
     * @param $id
     *
     * @return array
     */
    public function getSupportingDocument($id)
    {
        return $this->document->select('supporting_contract_id')->where('contract_id', $id)->get()->toArray();
    }

    /**
     * Return the supporting document
     *
     * @param $contractID
     *
     * @return SupportingContract
     */
    public function findSupportingContract($contractID)
    {
        return $this->document->where('parent_contract_id', $contractID)->first();
    }

    /**
     * Get all the contracts.
     *
     * @param array $ids
     * @param bool  $limit
     *
     * @return Collection
     */
    public function getContract($ids, $limit)
    {
        if ($limit) {
            return $this->contract->whereIn('id', $ids)->paginate($limit);
        }

        return $this->contract->whereIn('id', $ids)->get();
    }

    /**
     * Get Quality Count of multiple Metadata.
     *
     * @return array
     */
    public function getQualityCountOfMultipleMeta()
    {
        return DB::select('select get_quality_issue()');
    }

    /**
     * get Multiple Metadata Contract.
     *
     * @param $string
     *
     * @return array
     */
    public function getMultipleMetadataContract($string)
    {
        return DB::select(sprintf("select getMultipleMetadataContract('%s')", $string));
    }

    /**
     * Get Contract filter by Metadata
     *
     * @param $filters
     * @param $limit
     * @param $contractId
     *
     * @return collection
     */
    public function getContractFilterByMetadata($filters, $limit, $contractId)
    {
        $query   = $this->contract->select('*');
        $from    = "contracts ";
        $filters = array_map('trim', $filters);
        extract($filters);

        if ($issue == "present") {
            $query->WhereIn("id", $contractId);
        }
        if ($issue == "missing") {
            $query->WhereNotIn("id", $contractId);
        }

        $query->from($this->db->raw($from));

        return $query->orderBy('created_datetime', 'DESC')->paginate($limit);

    }

    /**
     * @return array
     */
    public function getParentContracts()
    {
        return $this->contract
            ->selectRaw("id,metadata->>'contract_name' as name")
            ->whereRaw("metadata->>'is_supporting_document' ='0'")->orderByRaw(
                "metadata->>'contract_name' ASC"
            )
            ->lists('name', 'id');
    }

    /**
     * remove supporting contracts
     *
     * @param $contractId
     *
     * @return bool
     */
    public function removeAsSupportingContract($contractId)
    {
        return $this->db->table('supporting_contracts')->where('supporting_contract_id', $contractId)->delete();
    }

    /**
     * Get Quality control for resource and category
     *
     * @param $key
     *
     * @return int
     */
    public function getResourceAndCategoryIssue($key,$filters)
    {
        $from  = "contracts ";
        $query = $this->contract->whereRaw(sprintf("json_array_length(metadata->'%s')!=0", $key));

        if (isset($filters['year']) && $filters['year'] != '' && $filters['year'] != 'all') {
            $query->whereRaw("contracts.metadata->>'signature_year'=?", [$filters['year']]);
        }
        if (isset($filters['resource']) && $filters['resource'] != '' && $filters['resource'] != 'all') {
            $from .= ",json_array_elements(contracts.metadata->'resource') r";
            $query->whereRaw("trim(both '\"' from r::text) = '" . $filters['resource'] . "'");
        }
        if (isset($filters['country']) && $filters['country'] != '' && $filters['country'] != 'all') {
            $query->whereRaw("contracts.metadata->'country'->>'code' = ?", [$filters['country']]);
        }
        if (isset($filters['category']) && $filters['category'] != '' && $filters['category'] != 'all') {
            $from .= ",json_array_elements(contracts.metadata->'category') cat";
            $query->whereRaw("trim(both '\"' from cat::text) = '" . $filters['category'] . "'");
        }


        $result = $query->from($this->db->raw($from))
                        ->count();

        return $result;
    }

    /**
     * get Company Name
     *
     * @return array
     */
    public function getCompanyName()
    {
        $query = $this->contract->selectRaw("distinct(company->>'name') as company_name");
        $from  = "contracts, json_array_elements(metadata->'company') as company";

        return $query->from($this->db->raw($from))
                     ->get()
                     ->toArray();
    }

    /**
     * Return all supporting Contract
     *
     * @return array
     */
    public function getAllSupportingContracts()
    {
        $query = $this->document->selectRaw("distinct(supporting_contract_id) as supporting");

        return $query->get()->toArray();
    }

    /**
     * Return all the contracts without supporting
     *
     * @param $supportingContract
     *
     * @return array
     */
    public function getContractsWithoutSupporting($supportingContract)
    {
        $query = $this->contract->selectRaw('*')->whereNotIn('id', $supportingContract);

        return $query->get()->toArray();
    }

    /**
     * Delete the parent contract from supporting contract if exist
     *
     * @param $id
     *
     * @return boolean
     */
    public function deleteSupportingContract($id)
    {
        $supporting = $this->document->where('contract_id', '=', $id)->get()->toArray();

        if (!empty($supporting)) {
            return $this->document->where('contract_id', '=', $id)->delete();
        }

        return false;
    }

    /**
     * Count Contracts By user
     *
     * @param $user_id
     *
     * @return int
     */
    public function countByUser($user_id)
    {
        return $this->contract->where('user_id', $user_id)->orWhere('updated_by', $user_id)->count();

    }

    /**
     * Get Contract Name
     *
     * @param      $contractName
     * @param null $id
     *
     * @return collection
     */
    public function getContractByName($contractName, $id = null)
    {
        $query = $this->contract->whereRaw("contracts.metadata->>'contract_name' LIKE  ?", [$contractName . '%']);

        if (!is_null($id)) {
            $query->where('id', '!=', $id);
        }

        return $query->orderByRaw("contracts.metadata->>'contract_name' ASC")->get();
    }

    /**
     * Get Disclosure Mode Count
     *
     * @param string $type | 'Government' | 'Corporate' | ''
     *
     * @return array
     */
    public function getDisclosureModeCount($type = '')
    {
        $counts = $this->contract->selectRaw("metadata->'country'->>'code' code, count(metadata->'country'->>'code')")
                                 ->whereRaw("metadata->>'disclosure_mode' ='".$type."'")
                                 ->groupBy($this->db->raw("metadata->'country'->>'code'"))
                                 ->orderBy($this->db->raw("metadata->'country'->>'code'"), "ASC")
                                 ->get();
        $mode   = [];
        foreach ($counts as $c) {
            $mode[$c->code] = $c->count;
        }

        return $mode;
    }

    /**
     * Get UnKnown Disclosure mode Count
     *
     * @return array
     */
    public function getUnknownDisclosureModeCount()
    {
        $counts = $this->contract->selectRaw("metadata->'country'->>'code' code,count(metadata->'country'->>'code')")
                                 ->whereRaw("metadata->>'disclosure_mode' !='".'Government'."'")
                                 ->whereRaw("metadata->>'disclosure_mode' !='".'Company'."'")
                                 ->groupBy($this->db->raw("metadata->'country'->>'code'"))
                                 ->orderBy($this->db->raw("metadata->'country'->>'code'"), "ASC")
                                 ->get();

        $mode = [];
        foreach ($counts as $c) {
            $mode[$c->code] = $c->count;
        }

        return $mode;
    }

    /**
     * Get multiple disclosure module contract
     *
     * @param $country
     * @param $filters
     *
     * @return collection
     */
    public function getMultipleDisclosureContract($country, $filters)
    {
        $limit      = 25;
        $disclosure = $filters['disclosure'];
        if ($disclosure == 'unknown') {
            $query = $this->contract->whereRaw("metadata->>'disclosure_mode' !='".'Government'."'")
                                    ->whereRaw("metadata->>'disclosure_mode' !='".'Company'."'");

        } else {
            $query = $this->contract->whereRaw("metadata->>'disclosure_mode' ='".$disclosure."'");
        }

        return $query->whereRaw("metadata->'country'->>'code' ='".$country."'")
                     ->paginate($limit);
    }
    /**
     * get Company Names
     *
     * @return array
     */
    public function getAllCompanyNames($contractId='')
    {
        $query = $this->contract->selectRaw("id,company->>'name' as company_name");
        if (!empty($contractId)) {
            $query->where('id', '=', $contractId);
        }
        $from  = "contracts, json_array_elements(metadata->'company') as company";

        return $query->from($this->db->raw($from))
                     ->get()
                     ->toArray();
    }
}
