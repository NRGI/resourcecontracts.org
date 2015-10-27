<?php namespace App\Nrgi\Repositories\Contract;

use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Entities\SupportingContract\SupportingContract;
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
     * @var SupportingDocument
     */
    private $document;

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
     * @return Contract
     */
    public function save($contractDetail)
    {
        $contract = $this->contract->create($contractDetail);
        $this->updateOCID($contract);

        return $contract;
    }

    /**
     * Get All Contracts
     *
     * @param array $filters
     * @return Collection|static[]
     */
    public function getAll(array $filters, $limit)
    {
        $query    = $this->contract->select('*');
        $from     = "contracts ";
        $operator = "";
        $filters  = array_map('trim', $filters);
        extract($filters);
        $operator = (!empty($issue) && $issue == "present") ? "!=" : "=";

        if ($year != '' && $year != 'all') {
            $query->whereRaw("contracts.metadata->>'signature_year'=?", [$year]);
        }
        if ($type == 'metadata' && $status != '') {
            $query->whereRaw("contracts.metadata_status=?", [$status]);
        }
        if ($type == 'ocr' && $status != '') {
            if ($status == "null") {
                $query->whereRaw("contracts.\"textType\" is null");
            } else {
                $query->whereRaw("contracts.\"textType\"=?", [$status]);
            }
        }

        if ($type == 'pdftext' && $status != '') {
            if ($status == "null") {
                $query->whereRaw("contracts.text_status is null");
            } else {
                $query->whereRaw("contracts.text_status=?", [$status]);
            }
        }
        if ($country != '' && $country != 'all') {
            $query->whereRaw("contracts.metadata->'country'->>'code' = ?", [$country]);
        }


        if ($resource != '' && $resource != 'all') {
            $from .= ",json_array_elements(contracts.metadata->'resource') r";
            $query->whereRaw("trim(both '\"' from r::text) = '" . $resource . "'");
        }

        if ($category != '' && $category != 'all') {
            $from .= ",json_array_elements(contracts.metadata->'category') cat";
            $query->whereRaw("trim(both '\"' from cat::text) = '" . $category . "'");
        }
        if ($type == "metadata" && $word != '' && $issue != '') {
            $query->whereRaw(sprintf("contracts.metadata->>'%s' %s''", $word, $operator));
        }
        if ($type == 'annotations' && $status != '') {
            $query->whereRaw("contracts.metadata_status=?", [$status]);
        }
        if ($type == "annotations" && $word != '' && $issue != '') {
            $contractsId = DB::table('contract_annotations')->select(DB::raw('contract_id'))->whereRaw(
                "contract_annotations.annotation->>'category' = ?",
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
        if ($q != '') {
            $q = '%' . $q . '%';
            $query->whereRaw("contracts.metadata->>'contract_name' ILIKE ?", [$q]);
        }
        $query->from($this->db->raw($from));

        return $query->orderBy('created_datetime', 'DESC')->paginate($limit);
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
        return $this->contract->with(
            [
                'annotations' => function ($query) {
                    $query->orderByRaw("annotation->>'category'");
                }
            ]
        )->with('created_user', 'updated_user')->findOrFail($contractId);
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
     * @return Collection
     */
    public function getMTurkContracts()
    {
        return $this->contract->with('tasks')->where('mturk_status', '!=', '')->get();
    }


    /**
     * Get Contract with pdf process status
     *
     * @param $status
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
     * @return collection
     */
    public function getMetadataQuality($metadata)
    {
        $from   = "contracts ";
        $result = $this->contract->whereRaw(sprintf("contracts.metadata->>'%s'!=''", $metadata))
                                 ->from($this->db->raw($from))
                                 ->count();

        return $result;
    }

    /**
     * Check if category of annotations exist or not.
     *
     * @param $key
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
     * @return mixed
     */
    public function saveSupportingDocument($documents)
    {
        return $this->contract->SupportingContract()->attach($documents);
    }

    /**
     * Get the contract name and id
     *
     * @param array $id
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
     * @return SupportingDocument
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
     * @return Collection
     */
    public function getContract($ids, $limit)
    {
        if ($limit) {
            return $this->contract->whereIn('id', $ids)->paginate($limit);
        }

        return $this->contract->whereIn('id', $ids)->get();
    }

    public function getQualityCountOfMultipleMeta()
    {
        return DB::select('select get_quality_issue()');
    }

    public function getMultipleMetadataContract($string)
    {
        return DB::select(sprintf("select getMultipleMetadataContract('%s')", $string));
    }

    public function getContractFilterByMetadata($filters, $limit, $contractId)
    {
        $query    = $this->contract->select('*');
        $from     = "contracts ";
        $operator = "";
        $filters  = array_map('trim', $filters);
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
     * Update OCID
     *
     * @param Contract $contract
     */
    public function updateOCID(Contract $contract)
    {
        if (!empty($contract->metadata->translated_from)) {
            $parent_contract                 = $this->findContract($contract->metadata->translated_from);
            $ocid                            = $parent_contract->metadata->open_contracting_id . '-' . $contract->id;
            $metadata                        = json_decode(json_encode($contract->metadata), true);
            $metadata['open_contracting_id'] = $ocid;
            $contract->metadata              = $metadata;
            $contract->save();
        }
    }

}
