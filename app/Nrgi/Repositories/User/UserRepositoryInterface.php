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
     * Gets all Roles with Permissions
     * @return mixed
     */
    public function getAllRolesWithPermissions();

    /**
     * Gets single Role
     *
     * @param $id
     *
     * @return mixed
     */
    public function getRole($id);

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

    /**
     * Creates User Defined Role
     *
     * @param $roleDetail
     *
     * @return mixed
     */
    public function createRole($roleDetail);

    /**
     * Gets Users associated with Role
     *
     * @param $id
     *
     * @return mixed
     */
    public function getUsersOfRole($id);
}
