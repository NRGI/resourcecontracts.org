<?php namespace App\Http\Controllers\Contract\Comment;

use App\Http\Controllers\Controller;
use App\Nrgi\Services\Contract\Comment\CommentService;
use App\Nrgi\Services\Contract\ContractService;

/**
 * Class CommentController
 * @package App\Http\Controllers\Contract\Comment
 */
class CommentController extends Controller
{
    /**
     * @var CommentService
     */
    protected $comment;

    /**
     * @param CommentService $comment
     */
    function __construct(CommentService $comment)
    {
        $this->comment = $comment;
    }

    /**
     * Display a listing of the Comment.
     *
     * @param                 $contract_id
     * @param ContractService $contract
     * @return Response
     */
    public function index($contract_id, ContractService $contract)
    {
        $contract = $contract->find($contract_id);
        $comments = $this->comment->getPaginate($contract_id);
        return view('contract.comment.index', compact('contract','comments'));
    }

}
