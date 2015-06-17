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
    public function saveComment($contract_id, $message, $type);

    /**
     * Get Latest Comment
     * @param $contract_id
     * @param $type
     * @return Comment
     */
    public function getLatest($contract_id, $type);
}
