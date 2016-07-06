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
     * @param       $limit
     * @return Collection|null
     */
    public function getAll(array $filters, $limit);

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
     * Get Contract with tasks
     *
     * @param $contractId
     * @return Contract
     */
    public function findContractWithTasks($contractId);

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

    /**
     * Updated upstream
     * Get Contract by file hash
     * @param $fileHash
     * @return mixed
     */
    public function getContractByFileHash($fileHash);

    /*
     * Get unique Resources
     * @return Contract
     */
    /**
     * write brief description
     * @return mixed
     */
    public function getUniqueResources();

    /**
     * Count total contracts by date
     * @param string $date
     * @return int
     */
    public function countTotal($date = '');

    /**
     * Get Recent Contracts
     *
     * @param $no
     * @return collection
     */
    public function recent($no);

    /**
     * Get Contract count by status
     *
     * @param $statusType
     * @return array
     */
    public function statusCount($statusType);

    /**
     * Get Contract List
     * @param $where
     * @return mixed
     */
    public function getList();

    /**
     * Get Contracts having MTurk Tasks
     *
     * @param array $filter
     * @param       $perPage
     * @return Collection
     */
    public function getMTurkContracts(array $filter = [] , $perPage);

    /**
     * Get Contract with pdf process status
     *
     * @param $status
     * @return Collection
     */
    public function getContractWithPdfProcessingStatus($status);

    /**
     * Get the count of presence of contract's metadata
     *
     * @param $metadata
     * @return collection
     */
    public function getMetadataQuality($metadata);

    /**
     * Get the count of presence of annotation's category
     *
     * @param $key
     * @return collection
     */
    public function getAnnotationsQuality($key);

    /**
     * Return the count of total contract
     *
     * @return integer
     */
    public function getTotalContractCount();

    /**
     * To save the supporting documents of contracts
     *
     * @param $documents
     * @return mixed
     */
    public function saveSupportingDocument($documents);

    /**
     * Get the contract name and id
     *
     * @param array $id
     * @return array
     */
    public function getSupportingContracts($id);

    /**
     * Return the Parent contract id
     *
     * @param $id
     * @return array
     */
    public function getSupportingDocument($id);

    /**
     * Return the supporting document
     *
     * @param $contractID
     * @return SupportingDocument
     */
    public function findSupportingContract($contractID);


    /**
     * Get all the contracts.
     * @param array $ids
     * @param       $limit
     * @return Collection
     */
    public function getContract($ids, $limit);

    /**
     * write brief description
     * @return mixed
     */
    public function getQualityCountOfMultipleMeta();

    public function getMultipleMetadataContract($string);

    public function getContractFilterByMetadata($filters, $limit, $contractId);

    /**
     * Update OCID
     *
     * @param Contract $contract
     */
    public function updateOCID(Contract $contract);

    /**
     * remove supporting contracts
     *
     * @param $contractId
     * @return bool
     */
    public function removeAsSupportingContract($contractId);

    /**
     * @return array
     */
    public function getParentContracts();

    /**
     * Get Quality control for resource and category
     *
     * @param $key
     * @return int
     */
    public function getResourceAndCategoryIssue($key);

    /**
     * Get Company name
     *
     * @return array
     */
    public function getCompanyName();

    /**
     * Return all supporting Contract
     * @return array
     */
    public function getAllSupportingContracts();

    /**
     * Return all the contracts without supporting
     * @param $supportingContract
     * @return mixed
     */
    public function getContractsWithoutSupporting($supportingContract);

    /**
     * Delete the parent contract from supporting contract if exist
     * @param $id
     * @return mixed
     */
    public function deleteSupportingContract($id);

    /**
     * Count Contracts by user
     *
     * @param $user_id
     * @return int
     */
    public function countByUser($user_id);

    /**
     * get next count of table
     *
     * @return mixed
     */
    public function getNextId();

}
