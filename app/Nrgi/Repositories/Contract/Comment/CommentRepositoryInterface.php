<?php namespace App\Nrgi\Repositories\Contract\Comment;

use App\Nrgi\Entities\Contract\Comment\Comment;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class CommentRepositoryInterface
 * @package App\Nrgi\Repositories\Contract\Comment
 */
interface CommentRepositoryInterface
{
    /**
     * Save Comment
     *
     * @param $contract_id
     * @param $message
     * @param $type
     * @return Comment
     */
    public function saveComment($contract_id, $message, $type, $status);

    /**
     * Get Latest Comment
     *
     * @param $contract_id
     * @param $type
     * @return Collection
     */
    public function getLatest($contract_id, $type);

    /**
     * Get Contract comments with pagination
     *
     * @param $perPage
     * @return collection
     */
    public function paginate($contract_id, $perPage);

    /**
     * Count comments By User
     *
     * @param $user_id
     * @return int
     */
    public function countByUser($user_id);
}
