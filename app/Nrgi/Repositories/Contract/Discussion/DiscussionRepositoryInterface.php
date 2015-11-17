<?php namespace App\Nrgi\Repositories\Contract\Discussion;

use App\Nrgi\Entities\Contract\Discussion\Discussion;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface DiscussionRepositoryInterface
 * @package App\Nrgi\Repositories\Contract\Discussion
 */
interface DiscussionRepositoryInterface
{
    /**
     * Save Discussion
     *
     * @param       $contract_id
     * @param array $data
     * @return Discussion
     */
    public function save($contract_id, array $data);

    /**
     * Get all discussion
     *
     * @param $contract_id
     * @param $key
     * @param $type
     * @return Collection
     */
    public function get($contract_id, $key, $type);

    /**
     * Get Discussion Count
     *
     * @param $contract_id
     * @return array
     */
    public function getCount($contract_id);

    /**
     * Get Resolved Discussion
     *
     * @param $contract_id
     * @return array
     */
    public function getResolved($contract_id);

    /**
     * Delete Discussion
     *
     * @param $contract_id
     * @param $key
     * @return bool
     */
    public function delete($contract_id, $key);

    /**
     * Update Discussion
     * @param       $contract_id
     * @param       $key
     * @param array $data
     * @return bool
     */
    public function update($contract_id, $key, array $data);

}
