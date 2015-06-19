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
        $this->auth    = $auth;
    }

    /**
     * Save Comment
     * @param $contract_id
     * @param $message
     * @param $type
     * @return static
     */
    public function saveComment($contract_id, $message, $type)
    {
        $data = [
            'contract_id' => $contract_id,
            'message'     => $message,
            'type'        => $type,
            'user_id'     => $this->auth->user()->id
        ];

        return $this->comment->create($data);
    }

    /**
     * Get Latest Comment
     * @param $contract_id
     * @param $type
     * @return Comment
     */
    public function getLatest($contract_id, $type)
    {
        return $this->comment->with('user')->where('type', $type)->where('contract_id', $contract_id)->orderBy('created_at', 'DESC')->first();
    }

    /**
     * Get Contract comments with pagination
     * @param $perPage
     * @return mixed
     */
    public function paginate($contract_id, $perPage)
    {
        return $this->comment->with('user')->where('contract_id', $contract_id)->orderBy('created_at', 'DESC')->paginate($perPage);
    }
}
