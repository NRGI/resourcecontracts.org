<?php namespace App\Nrgi\Services\User;

use App\Nrgi\Entities\User\User;
use App\Nrgi\Mturk\Repositories\Activity\ActivityRepositoryInterface as MTurkActivities;
use App\Nrgi\Repositories\ActivityLog\ActivityLogRepositoryInterface;
use App\Nrgi\Repositories\Contract\Annotation\AnnotationRepositoryInterface;
use App\Nrgi\Repositories\Contract\Comment\CommentRepositoryInterface;
use App\Nrgi\Repositories\Contract\ContractRepositoryInterface;
use App\Nrgi\Repositories\Contract\Discussion\DiscussionRepositoryInterface;
use App\Nrgi\Repositories\User\UserRepositoryInterface;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Hashing\Hasher;
use Psr\Log\LoggerInterface;
use App\Nrgi\Entities\User\Role\Role;

/**
 * Class UserService
 *
 * @method void where()
 * @package App\Nrgi\Services\User
 */
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
     * @var Role
     */
    protected $role;
    /**
     * @var Guard
     */
    protected $auth;
    /**
     * @var ContractRepositoryInterface
     */
    protected $contract;
    /**
     * @var AnnotationRepositoryInterface
     */
    private $annotation;
    /**
     * @var ActivityLogRepositoryInterface
     */
    private $activity;
    /**
     * @var MTurkActivities
     */
    private $mTurkActivity;
    /**
     * @var DiscussionRepositoryInterface
     */
    private $discussion;
    /**
     * @var CommentRepositoryInterface
     */
    private $comment;

    /**
     * @param UserRepositoryInterface        $user
     * @param ContractRepositoryInterface    $contract
     * @param AnnotationRepositoryInterface  $annotation
     * @param ActivityLogRepositoryInterface $activity
     * @param MTurkActivities                $mTurkActivity
     * @param DiscussionRepositoryInterface  $discussion
     * @param CommentRepositoryInterface     $comment
     * @param LoggerInterface                $logger
     * @param Hasher                         $hash
     * @param Role                           $role
     * @param Guard                          $auth
     */
    public function __construct(
        UserRepositoryInterface $user,
        ContractRepositoryInterface $contract,
        AnnotationRepositoryInterface $annotation,
        ActivityLogRepositoryInterface $activity,
        MTurkActivities $mTurkActivity,
        DiscussionRepositoryInterface $discussion,
        CommentRepositoryInterface $comment,
        LoggerInterface $logger,
        Hasher $hash,
        Role $role,
        Guard $auth
    ) {
        $this->user          = $user;
        $this->logger        = $logger;
        $this->hash          = $hash;
        $this->role          = $role;
        $this->auth          = $auth;
        $this->contract      = $contract;
        $this->annotation    = $annotation;
        $this->activity      = $activity;
        $this->mTurkActivity = $mTurkActivity;
        $this->discussion    = $discussion;
        $this->comment       = $comment;
    }

    /**
     * Get all users
     * @return array
     */
    public function all()
    {
        if ($this->auth->user()->hasRole(config('nrgi.country_role'))) {

            return $this->user->getCountryUsers();
        } 
        
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
        $role                 = $this->role->where('name', $role)->first();

        try {
            $user = $this->user->create($formData);
            $user->roles()->sync([$role->id]);
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
    public function update($user_id, array $formData, $role = null)
    {
        $user = $this->find($user_id);
        $role = $this->role->where('name', $role)->first();
        if (isset($formData['password']) && !empty($formData['password'])) {
            $user->password = $this->hash->make($formData['password']);
        }

        $data = array_except($formData, 'password');

        foreach ($data as $key => $value) {
            $user->$key = $value;
        }

        try {
            if ($user->save()) {
                if (!is_null($role)) {
                    $user->roles()->sync([$role->id]);
                }
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
        if ($this->auth->user()->hasRole(config('nrgi.country_role'))) {
            return $this->user->getCountryRoles();
        } else if(!$this->auth->user()->hasRole('superadmin-editor')){
            return $this->user->getRolesExceptAdminEditor();
        }

        return $this->user->getAllRoles();
    }

    /**
     * Get List of users
     *
     * @return array
     */
    public function getList()
    {
        if ($this->auth->user()->hasRole(config('nrgi.country_role'))) {
            return $this->user->getUsersWithCountryContract();
        }

        return $this->user->getList();
    }

    /**
     * Gets list of all users
     * @return array
     */
    public function getAllUsersList()
    {
        return $this->user->getList();
    }

    /**
     * Has user activity
     *
     * @param $user_id
     * @return bool
     */
    public function hasNoActivity($user_id)
    {
        $models = ['contract', 'annotation', 'comment', 'discussion', 'activity', 'mTurkActivity'];

        foreach ($models as $model) {
            $contract = $this->$model->countByUser($user_id);

            if ($contract > 0) {
                return false;
            }
        }

        return true;
    }

}
