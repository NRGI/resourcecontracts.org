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
     * @return collection
     */
    public function getMTurkContracts();

    /**
     * Get Contract with pdf process status
     *
     * @param $status
     * @return Collection
     */
    public function getContractWithPdfProcessingStatus($status);
}
