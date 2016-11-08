<?php namespace App\Nrgi\Repositories\User;

use App\Nrgi\Entities\User\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface UserRepositoryInterface
 * @package App\Nrgi\Repositories\User
 */
interface UserRepositoryInterface
{
    /**
     * Get all users
     *
     * @return Collection
     */
    public function all();

    /**
     * Create New user
     *
     * @param array $userDetail
     * @return User
     */
    public function create(array $userDetail);

    /**
     * Find user by ID
     *
     * @param $user_id
     * @return User|null
     */
    public function find($user_id);

    /**
     * Get All User Roles
     *
     * @return array
     */
    public function getAllRoles();

    /**
     * Get User list
     *
     * @return array
     */
    public function getList();

    /**
     * Get Country Specific Users
     *
     * @return Array
     */
    public function getCountryUsers();

}

