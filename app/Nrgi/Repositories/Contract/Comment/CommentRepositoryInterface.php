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
     * @return Comment
     */
    public function saveComment($contract_id, $message);

    /**
     * write brief description
     * @param $contract_id
     * @return Comment
     */
    public function getLatest($contract_id);
}
