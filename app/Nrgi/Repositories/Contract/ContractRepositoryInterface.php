<?php namespace App\Nrgi\Repositories\Contract;

use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Entities\SupportingContract\SupportingContract;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface ContractRepositoryInterface
 */
interface ContractRepositoryInterface
{
    /**
     * Save Contract
     *
     * @param $contractDetail
     *
     * @return Contract
     */
    public function save($contractDetail);

    /**
     * Get all Contracts
     *
     * @param array $filters
     * @param       $limit
     *
     * @return Collection|null
     */
    public function getAll(array $filters, $limit);

    /**
     * Get Contract
     *
     * @param $contractId
     *
     * @return Contract
     */
    public function findContract($contractId);

    /**
     * Get Contract with pages
     *
     * @param $contractId
     *
     * @return Contract
     */
    public function findContractWithPages($contractId);

    /**
     * Get Contract with tasks
     *
     * @param $contractId
     *
     * @return Contract
     */
    public function findContractWithTasks($contractId);

    /**
     * Get Contract with Annotations
     *
     * @param $contractId
     *
     * @return Contract
     */
    public function findContractWithAnnotations($contractId);

    /**
     * Delete contract
     *
     * @param $contractID
     *
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
     *
     * @param $fileHash
     *
     * @return contract
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
     *
     * @param string $date
     *
     * @return int
     */
    public function countTotal($date = '');

    /**
     * Get Recent Contracts
     *
     * @param $no
     *
     * @return collection
     */
    public function recent($no);

    /**
     * Get Contract count by status
     *
     * @param $statusType
     *
     * @return array
     */
    public function statusCount($statusType);

    /**
     * Get Contract List
     *
     * @return Collection
     */
    public function getList();

    /**
     * Get Contracts having MTurk Tasks
     *
     * @param array $filter
     * @param       $perPage
     *
     * @return Collection
     */
    public function getMTurkContracts(array $filter = [], $perPage);

    /**
     * Get Contract with pdf process status
     *
     * @param $status
     *
     * @return Collection
     */
    public function getContractWithPdfProcessingStatus($status);

    /**
     * Get the count of presence of contract's metadata
     *
     * @param $metadata
     *
     * @return collection
     */
    public function getMetadataQuality($metadata,$filter);

    /**
     * Get the count of presence of annotation's category
     *
     * @param $key
     *
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
     *
     * @return bool
     */
    public function saveSupportingDocument($documents);

    /**
     * Get the contract name and id
     *
     * @param array $id
     *
     * @return array
     */
    public function getSupportingContracts($id);

    /**
     * Return the Parent contract id
     *
     * @param $id
     *
     * @return array
     */
    public function getSupportingDocument($id);

    /**
     * Return the supporting document
     *
     * @param $contractID
     *
     * @return SupportingContract
     */
    public function findSupportingContract($contractID);

    /**
     * Get all the contracts.
     *
     * @param array $ids
     * @param       $limit
     *
     * @return Collection
     */
    public function getContract($ids, $limit);

    /**
     * Get Quality count of multiple metadata
     *
     * @return array
     */
    public function getQualityCountOfMultipleMeta();

    /**
     * Get Multiple metadata Contract
     *
     * @param $string
     *
     * @return collection
     */
    public function getMultipleMetadataContract($string);

    /**
     * Get contract filter by metadata
     *
     * @param $filters
     * @param $limit
     * @param $contractId
     *
     * @return collection
     */
    public function getContractFilterByMetadata($filters, $limit, $contractId);

    /**
     * remove supporting contracts
     *
     * @param $contractId
     *
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
     *
     * @return int
     */
    public function getResourceAndCategoryIssue($key,$filters);

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
     *
     * @param $supportingContract
     *
     * @return array
     */
    public function getContractsWithoutSupporting($supportingContract);

    /**
     * Delete the parent contract from supporting contract if exist
     *
     * @param $id
     *
     * @return bool
     */
    public function deleteSupportingContract($id);

    /**
     * Count Contracts by user
     *
     * @param $user_id
     *
     * @return int
     */
    public function countByUser($user_id);

    /**
     * Get Contract Name
     *
     * @param      $contractName
     * @param null $id
     *
     * @return collection
     */

    public function getContractByName($contractName, $id = null);

    /**
     * Get count company disclosure mode
     *
     * @param string $type
     *
     * @return Collection
     */
    public function getDisclosureModeCount($type = '');

    /**
     * Get count government disclosure mode
     *
     * @return collection
     */
    public function getUnknownDisclosureModeCount();

    /**
     * Get multiple disclosure module contract
     *
     * @param $country
     * @param $filters
     *
     * @return collection
     */
    public function getMultipleDisclosureContract($country, $filters);
}
