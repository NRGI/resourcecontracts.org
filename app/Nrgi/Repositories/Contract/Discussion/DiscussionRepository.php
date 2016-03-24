<?php namespace App\Nrgi\Repositories\Contract\Discussion;

use App\Nrgi\Entities\Contract\Discussion\Discussion;
use Illuminate\Auth\Guard;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class DiscussionRepository
 *
 * @method void where()
 * @method void selectRaw()
 * @package App\Nrgi\Repositories\Contract\Discussion
 */
class DiscussionRepository implements DiscussionRepositoryInterface
{
    /**
     * @var Discussion
     */
    protected $discussion;
    /**
     * @var Guard
     */
    protected $auth;
    /**
     * @var DatabaseManager
     */
    protected $db;

    /**
     * @param Discussion $discussion
     * @param Guard      $auth
     */
    public function __construct(Discussion $discussion, Guard $auth, DatabaseManager $db)
    {
        $this->discussion = $discussion;
        $this->auth       = $auth;
        $this->db         = $db;
    }

    /**
     * Save Discussion
     *
     * @param       $contract_id
     * @param array $data
     * @return Discussion
     */
    public function save($contract_id, array $data)
    {
        $data = [
            'contract_id' => $contract_id,
            'message'     => $data['message'],
            'key'         => $data['key'],
            'type'        => $data['type'],
            'status'      => $data['status'],
            'user_id'     => $this->auth->id()
        ];

        return $this->discussion->create($data);
    }

    /**
     * Get all discussion
     *
     * @param $contract_id
     * @param $key
     * @param $type
     * @return Collection
     */
    public function get($contract_id, $key, $type)
    {
        return $this->discussion->with('user')->where('contract_id', $contract_id)->where('key', $key)->where('type', $type)->orderBy('created_at', 'DESC')->get();
    }

    /**
     * Get Discussion Count
     *
     * @param $contract_id
     * @return array
     */
    public function getCount($contract_id)
    {
        return $this->discussion->selectRaw('count(*), key')->where('contract_id', $contract_id)->groupBy('key')->get()->toArray();
    }

    /**
     * Get Resolved Discussion
     *
     * @param $contract_id
     * @return array
     */
    public function getResolved($contract_id)
    {
        return $this->db->select( "SELECT t1.key, t1.status, t1.created_at FROM contract_discussions t1
                                            JOIN
                                            (
                                                SELECT key, MAX(created_at) AS MAXDATE
                                               FROM contract_discussions
                                               where contract_id = " . $contract_id . "
                                               GROUP BY key
                                            ) t2
                                            ON t1.key = t2.key
                                                AND t1.created_at = t2.MAXDATE"
        );
    }

    /**
     * Delete Discussion
     *
     * @param $contract_id
     * @param $key
     * @return boolean
     */
    public function delete($contract_id, $key)
    {
        return $this->discussion->where('contract_id', $contract_id)->where('key', $key)->delete();
    }

    /**
     * Update Discussion
     * @param       $contract_id
     * @param       $key
     * @param array $data
     * @return bool
     */
    public function update($contract_id, $key, array $data)
    {
        return $this->discussion->where('contract_id', $contract_id)->where('key', $key)->update($data);
    }

    /**
     * Count Discussion By User
     *
     * @param $user_id
     * @return mixed
     */
    public function countByUser($user_id)
    {
        return $this->discussion->where('user_id',$user_id)->count();
    }
}
