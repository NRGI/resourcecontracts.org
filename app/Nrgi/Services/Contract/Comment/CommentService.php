<?php namespace App\Nrgi\Services\Contract\Comment;

use App\Nrgi\Entities\Contract\Comment\Comment;
use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Repositories\Contract\Comment\CommentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Psr\Log\LoggerInterface;

/**
 * Class CommentService
 * @package App\Nrgi\Services\Contract\Comment
 */
class CommentService
{
    /**
     * @var CommentRepositoryInterface
     */
    protected $comment;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param CommentRepositoryInterface $comment
     * @param LoggerInterface            $logger
     */
    public function __construct(CommentRepositoryInterface $comment, LoggerInterface $logger)
    {
        $this->comment = $comment;
        $this->logger  = $logger;
    }

    /**
     * Save Contract Comment
     *
     * @param $contract_id
     * @param $message
     * @return bool
     */
    public function save($contract_id, $message, $type, $status)
    {
        try {
            if (!empty($message)) {
                $this->comment->saveComment($contract_id, $message, $type, $status);
                $this->logger->info(
                    'Comment successfully saved.',
                    ['Contract id' => $contract_id, 'status' => $status]
                );
            }

            return true;
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return false;
    }

    /**
     * Get Latest Comment by type
     *
     * @param $contract_id
     * @param $type
     * @return Collection
     */
    public function getLatest($contract_id, $type)
    {
        return $this->comment->getLatest($contract_id, $type);
    }

    /**
     * Get Contract with pagination
     * @param $id
     * @return Collection
     */
    public function getPaginate($id)
    {
        return $this->comment->paginate($id, 25);
    }
}
