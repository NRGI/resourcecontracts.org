<?php namespace App\Nrgi\Entities\User\Permission;

use Zizaco\Entrust\EntrustPermission;

class Permission extends EntrustPermission
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'permissions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'display_name', 'description'];
}
