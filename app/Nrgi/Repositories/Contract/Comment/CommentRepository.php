<?php namespace App\Nrgi\Repositories\Contract\Comment;

use App\Nrgi\Entities\Contract\Comment\Comment;
use Illuminate\Auth\Guard;
use Illuminate\Database\Eloquent\Collection;

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
     *
     * @param $contract_id
     * @param $message
     * @param $type
     * @param $status
     * @return Comment
     */
    public function saveComment($contract_id, $message, $type, $status)
    {
        $data = [
            'contract_id' => $contract_id,
            'message'     => $message,
            'type'        => $type,
            'action'      => $status,
            'user_id'     => $this->auth->user()->id
        ];

        return $this->comment->create($data);
    }

    /**
     * Get Latest Comment
     *
     * @param $contract_id
     * @param $type
     * @return Collection
     */
    public function getLatest($contract_id, $type)
    {
        return $this->comment->with('user')
                             ->where('type', $type)
                             ->where('contract_id', $contract_id)
                             ->limit(5)
                             ->orderBy(
                                 'created_at',
                                 'DESC'
                             )
                             ->get();
    }

    /**
     * Get Contract comments with pagination
     *
     * @param $perPage
     * @return Collection
     */
    public function paginate($contract_id, $perPage)
    {
        return $this->comment->with('user')
                             ->where('contract_id', $contract_id)
                             ->orderBy('created_at', 'DESC')
                             ->paginate($perPage);
    }
}
