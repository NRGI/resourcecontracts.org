<?php namespace App\Nrgi\Services\User;

use App\Nrgi\Repositories\User\UserRepositoryInterface;

class UserService
{
    /**
     * @var UserRepositoryInterface
     */
    protected $user;

    /**
     * @param UserRepositoryInterface $user
     */
    public function __construct(UserRepositoryInterface $user)
    {
        $this->user = $user;
    }

    /**
     * Get all users
     * @return array
     */
    public function all()
    {
        return $this->user->all();
    }
}
