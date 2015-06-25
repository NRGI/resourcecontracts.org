<?php namespace App\Nrgi\Services\User;

use App\Nrgi\Entities\User\User;
use App\Nrgi\Repositories\User\UserRepositoryInterface;
use Illuminate\Contracts\Hashing\Hasher;
use Psr\Log\LoggerInterface;

class UserService
{
    /**
     * @var UserRepositoryInterface
     */
    protected $user;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var Hasher
     */
    protected $hash;

    /**
     * @param UserRepositoryInterface $user
     * @param LoggerInterface         $logger
     * @param Hasher                  $hash
     */
    public function __construct(UserRepositoryInterface $user, LoggerInterface $logger, Hasher $hash)
    {
        $this->user   = $user;
        $this->logger = $logger;
        $this->hash   = $hash;
    }

    /**
     * Get all users
     * @return array
     */
    public function all()
    {
        return $this->user->all();
    }

    /**
     * Find user by ID
     * @param $id
     * @return User
     */
    public function find($id)
    {
        return $this->user->find($id);
    }

    /**
     * Delete user by ID
     * @param $id
     * @return User
     */
    public function delete($id)
    {
        $user = $this->user->find($id);
        $user->roles()->sync([]);

        return $user->delete();
    }

    /**
     * Create new user
     *
     * @param array $formData
     * @return \App\Nrgi\Entities\User\User
     */
    public function create(array $formData, $role)
    {
        $formData['password'] = $this->hash->make($formData['password']);

        try {
            $user = $this->user->create($formData);
            $user->roles()->sync([$role]);
            $this->logger->info('User successfully created.', $formData);

            return true;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            return false;
        }
    }

    /**
     * Update User detail
     *
     * @param       $user_id
     * @param array $formData
     * @param       $role
     * @return bool
     */
    public function update($user_id, array $formData, $role)
    {
        $user = $this->find($user_id);

        if (!empty($formData['password'])) {
            $user->password = $this->hash->make($formData['password']);
        }

        $user->organization = $formData['organization'];
        $user->status       = $formData['status'];
        $user->name         = $formData['name'];

        try {
            if ($user->save()) {
                $user->roles()->sync([$role]);
                $this->logger->info('User successfully updated.', $formData);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            return false;
        }
    }

    /**
     * Get All Roles
     *
     * @return array
     */
    public function getAllRoles()
    {
        return $this->user->getAllRoles();
    }

}
