<?php namespace App\Nrgi\Repositories\User;

use App\Nrgi\Entities\User\Role\Role;
use App\Nrgi\Entities\User\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class UserRepository
 * @package App\Nrgi\Repositories\User
 */
class UserRepository implements UserRepositoryInterface
{
    /**
     * @var User
     */
    protected $user;
    /**
     * @var Role
     */
    protected $role;

    /**
     * @param User $user
     * @param Role $role
     */
    public function __construct(User $user, Role $role)
    {
        $this->user = $user;
        $this->role = $role;
    }

    /**
     * Get all users
     *
     * @return Collection/null
     */
    public function all()
    {
        return $this->user->orderby('created_at', 'DESC')->get();
    }

    /**
     * Create New user
     *
     * @param array $userDetail
     * @return User
     */
    public function create(array $userDetail)
    {
        return $this->user->create($userDetail);
    }

    /**
     * Find user by ID
     * @param $user_id
     * @return User
     */
    public function find($user_id)
    {
        return $this->user->find($user_id);
    }

    /**
     * Get All User Roles
     *
     * @return array
     */
    public function getAllRoles()
    {
        return $this->role->lists('display_name', 'id');
    }
}
