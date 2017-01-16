<?php namespace App\Nrgi\Entities\User;

use App\Nrgi\Services\Traits\DateTrait;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Zizaco\Entrust\Traits\EntrustUserTrait;

/**
 * @property Collection roles
 * @property string     role
 * @property string     password
 */
class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword;
    use EntrustUserTrait;
    use DateTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password', 'organization', 'status', 'country'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contracts()
    {
        return $this->hasMany('App\Nrgi\Entities\Contract\Contract');
    }

    /**
     * Get User Role Name
     * @return string
     */
    public function roleName()
    {
        $roles = [];
        foreach ($this->roles->toArray() as $role) {
            $roles[] = $role['display_name'];
        }

        $roles = array_map('ucfirst', $roles);

        return join(', ', $roles);
    }

    /**
     * Get User Role Name
     * @return string
     */
    public function getRoleAttribute()
    {
        $roles = [];
        foreach ($this->roles->toArray() as $role) {
            $roles[$role['name']] = $role['display_name'];
        }

        return $roles;
    }


    /**
     * Check if user has a permission by its name.
     *
     * @param string|array $permission Permission string or array of permissions.
     * @param bool         $requireAll All permissions in the array are required.
     *
     * @return bool
     */
    public function can($permission, $requireAll = false)
    {
        if ($this->hasRole(['superadmin', 'country-admin'])) {
            return true;
        }

        if (is_array($permission)) {
            foreach ($permission as $permName) {
                $hasPerm = $this->can($permName);

                if ($hasPerm && !$requireAll) {
                    return true;
                } elseif (!$hasPerm && $requireAll) {
                    return false;
                }
            }

            return $requireAll;
        } else {
            foreach ($this->roles as $role) {
                foreach ($role->perms as $perm) {
                    if ($perm->name == $permission) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Check if user is admin.
     *
     * @return bool
     */
    public function isAdmin()
    {
        if ($this->hasRole(['superadmin', 'country-admin'])) {
            return true;
        }

        return false;
    }

    /**
     * Checks if user is country user
     * @return bool
     */
    public function isCountryUser()
    {
        if ($this->hasRole(['country-researcher', 'country-admin'])) {
            return true;
        }

        return false;
    }

    /**
     * @param $data
     * @return array
     */
    public function getCountryAttribute($data)
    {
        return json_decode($data);
    }

    /**
     * @param $data
     */
    public function setCountryAttribute($data)
    {
        $this->attributes['country'] = json_encode($data);
    }

    /**
     * check if user has country role
     *
     * @return bool
     */
    public function hasCountryRole()
    {
        if ($this->hasRole(['superadmin'])) {
            return false;
        }

        if (isset($this->role)) {
            $roles = array_keys($this->role);

            if (array_intersect($roles, config('nrgi.country_role'))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has country researcher role
     *
     * @return bool
     */
    public function isCountryResearch()
    {
        if ($this->hasRole(['country-researcher'])){
            return true;
        }
        return false;
    }
}
