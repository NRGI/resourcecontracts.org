<?php namespace App\Nrgi\Repositories\Contract\Comment;

use App\Nrgi\Entities\Contract\Comment\Comment;

/**
 * Class CommentRepositoryInterface
 * @package App\Nrgi\Repositories\Contract\Comment
 */
interface CommentRepositoryInterface
{
    /**
     * Save Comment
     * @param $contract_id
     * @param $message
     * @param $type
     * @return Comment
     */
    public function saveComment($contract_id, $message, $type ,$status);

    /**
     * Get Latest Comment
     * @param $contract_id
     * @param $type
     * @return Comment
     */
    public function getLatest($contract_id, $type);

    /**
     * Get Contract comments with pagination
     * @param $perPage
     * @return mixed
     */
    public function paginate($contract_id, $perPage);
}
