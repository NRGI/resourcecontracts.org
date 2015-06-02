<?php namespace App\Nrgi\Repositories\User;

use App\Nrgi\Entities\User\User;

/**
 * Interface UserRepositoryInterface
 * @package App\Nrgi\Repositories\User
 */
interface UserRepositoryInterface
{
    /**
     * Get all users
     * @return array/null
     */
    public function all();
}
