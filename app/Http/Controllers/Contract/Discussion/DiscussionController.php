<?php namespace App\Http\Controllers\Contract\Discussion;

use App\Http\Controllers\Controller;
use App\Nrgi\Services\Contract\ContractService;
use App\Nrgi\Services\Contract\Discussion\DiscussionService;
use Illuminate\Http\Request;

/**
 * Class DiscussionController
 * @package App\Http\Controllers\Contract\Discussion
 */
class DiscussionController extends Controller
{
    /**
     * @var DiscussionService
     */
    protected $discussion;
    /**
     * @var ContractService
     */
    protected $contract;

    /**
     * @param DiscussionService $discussion
     * @param ContractService   $contract
     */
    public function __construct(DiscussionService $discussion, ContractService $contract)
    {
        $this->middleware('auth');
        $this->discussion = $discussion;
        $this->contract   = $contract;
    }

    /**
     * Display all discussion
     *
     * @param $contract_id
     * @param $type
     * @param $key
     * @return \Illuminate\View\View
     */
    public function index($contract_id, $type, $key)
    {
        $discussions = $this->discussion->get($contract_id, $key, $type);
        $contract    = $this->contract->find($contract_id);

        return view('contract.discussion.index', compact('discussions', 'contract', 'key', 'type'));
    }

    /**
     * Create new discussion
     *
     * @param Request $request
     * @param         $contract_id
     * @param         $type
     * @param         $key
     * @internal param $message
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(Request $request, $contract_id, $type, $key)
    {
        $formData = [
            'message' => $request->input('comment'),
            'status'  => $request->input('status', 0),
            'key'     => $key,
            'type'    => $type
        ];

        $response = ['result' => false, 'message' => 'Comment could not be added.'];

        if ($this->discussion->save($contract_id, $formData)) {
            $discussions = $this->discussion->get($contract_id, $key, $type);
            $response        = ['result' => true, 'message' => $discussions->toArray()];
        }

        return response()->json($response);
    }

}
