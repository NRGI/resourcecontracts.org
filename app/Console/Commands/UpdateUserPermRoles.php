<?php namespace App\Console\Commands;

use App\Nrgi\Entities\User\Permission\Permission;
use Illuminate\Console\Command;
/*
 * For updating user permission and their roles
 */
class UpdateUserPermRoles extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'nrgi:updateuserrole';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update User Role and permissions.';
    /**
     * @var Permission
     */
    public $permission;


    /**
     * Create a new command instance.
     * @param Permission $permission
     */
    public function __construct(Permission $permission)
    {
        parent::__construct();

        $this->permission = $permission;
    }

    /**
     * Execute the console command
     */
    public function fire()
    {
        $this->updateUserPermission();
    }

    /**
     * Update User Permission
     */
    public function updateUserPermission()
    {
        foreach (config('nrgi.permissions') as $permission) {
            $check=$this->permission->where('name',$permission['name'])->get()->toArray();
            if (empty($check)) {
                $this->permission->firstOrCreate($permission);
                $this->info(sprintf('Permission : %s', $permission['name']));
            }
        }
    }
}
