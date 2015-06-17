<?php namespace App\Nrgi\Entities\User;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Zizaco\Entrust\Traits\EntrustUserTrait;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword;
    use EntrustUserTrait;

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
    protected $fillable = ['name', 'email', 'password'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * Contract Status
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_COMPLETE = 'complete';
    const STATUS_PUBLISH = 'publish';


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
    public function RoleName()
    {
        $roles = [];
        foreach ($this->roles->toArray() as $role) {
            $roles[] = $role['name'];
        }

        $roles = array_map('ucfirst', $roles);

        return join(', ', $roles);
    }

    /**
     * Boot the Contact model
     * Attach event listener to add draft status when creating a contract
     *
     * @return void|bool
     */
    public static function boot()
    {
        parent::boot();

        static::creating(
            function ($contract) {
                $contract->status = static::STATUS_DRAFT;

                return true;
            }
        );
    }
}
