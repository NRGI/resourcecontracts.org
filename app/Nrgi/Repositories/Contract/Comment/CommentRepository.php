<?php namespace App\Nrgi\Repositories\Contract\Comment;

use App\Nrgi\Entities\Contract\Comment\Comment;
use Illuminate\Auth\Guard;

/**
 * Class CommentRepository
 */
class CommentRepository implements CommentRepositoryInterface
{
    /**
     * @var Comment
     */
    protected $comment;
    /**
     * @var Guard
     */
    protected $auth;

    /**
     * @param Comment $comment
     * @param Guard   $auth
     */
    public function __construct(Comment $comment, Guard $auth)
    {
        $this->comment = $comment;
        $this->auth = $auth;
    }

    /**
     * Save Comment
     * @param $contract_id
     * @param $message
     * @return static
     */
    public function saveComment($contract_id, $message)
    {
        $data = [
            'contract_id' => $contract_id,
            'message'     => $message,
            'user_id'     => $this->auth->user()->id
        ];

        return $this->comment->create($data);
    }

    /**
     * write brief description
     * @param $contract_id
     * @return Comment
     */
    public function getLatest($contract_id)
    {
       return $this->comment->where('contract_id',$contract_id)->first();
    }
}
