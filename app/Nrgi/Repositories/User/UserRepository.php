<?php namespace App\Nrgi\Repositories\User;

use App\Nrgi\Entities\User\Role\Role;
use App\Nrgi\Entities\User\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class UserRepository
 *
 * @method void orderby()
 * @method void lists()
 * @method void whereIn()
 * @method void select()
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
     * @var Guard
     */
    protected $auth;
    /**
     * @var DatabaseManager
     */
    protected $db;

    /**
     * @param User            $user
     * @param Role            $role
     * @param Guard           $auth
     * @param DatabaseManager $db
     */
    public function __construct(User $user, Role $role, Guard $auth, DatabaseManager $db)
    {
        $this->user = $user;
        $this->role = $role;
        $this->auth = $auth;
        $this->db   = $db;
    }

    /**
     * Get all users
     *
     * @return Collection|null
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
     *
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
        return $this->role->pluck('display_name', 'name')->all();
    }

    /**
     * Get All User Roles
     *
     * @return array
     */
    public function getCountryRoles()
    {
        return $this->role->whereIn('name', config('nrgi.country_role'))->pluck('display_name', 'name')->all();
    }

    /**
     * Get User list
     *
     * @return array
     */
    public function getList()
    {
        return $this->user->pluck('name', 'id')->all();
    }

    /**
     * Get country all users
     *
     * @return Collection|null
     */
    public function getCountryUsers()
    {
        $countries = $this->auth->user()->country;
        $query     = $this->user->select('*');
        $from      = "users";
        $from .= ",json_array_elements(users.country) r";
        $query->whereRaw("trim(both '\"' from r::text) in (?)", $countries);
        $query->from($this->db->raw($from));

        return $query->get();
    }

    /**
     * Get country all users
     *
     * @return Collection/null
     */
    public function getUsersWithCountryContract()
    {
        $query     = $this->user->select('name', 'id');
        $countries = $this->auth->user()->country;

        $from = "users";
        $from .= ",json_array_elements(users.country) r";

        if (!is_null($countries)) {
            $query->whereRaw("trim(both '\"' from r::text) in (?)", $countries);
        }

        $query->from($this->db->raw($from));
        $list = [];
        foreach ($query->get() as $v) {
            $list[$v->id] = $v->name;
        }

        return $list;
    }

    /**
     * Get All User Roles except superadmin editor
     *
     * @return array
     */
    public function getRolesExceptAdminEditor()
    {
        return $this->role->whereIn('name', config('nrgi.role_except_editor'))->pluck('display_name', 'name')->all();
    }

}
