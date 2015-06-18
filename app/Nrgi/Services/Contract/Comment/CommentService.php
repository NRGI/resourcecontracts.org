<?php namespace App\Nrgi\Services\Contract\Comment;

use App\Nrgi\Repositories\Contract\Comment\CommentRepositoryInterface;
use Psr\Log\LoggerInterface;

class CommentService
{
    /**
     * @var CommentRepositoryInterface
     */
    protected $comment;
    /**
     * @var LoggerAwareInterface
     */
    private $logger;

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
    public function save($contract_id, $message)
    {
        try {
            $this->comment->saveComment($contract_id, $message);
            $this->logger->info('Comment successfully saved.');

            return true;
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return false;
    }

    public function getLatest($contract_id)
    {
       return $this->comment->getLatest($contract_id);
    }
}
