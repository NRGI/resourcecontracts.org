<?php namespace App\Nrgi\Repositories\User;

use App\Nrgi\Entities\User\User;

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
     * @param User $user
     */
    function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get all users
     * @return array/null
     */
    public function all()
    {
        return $this->user->all();
    }
}
