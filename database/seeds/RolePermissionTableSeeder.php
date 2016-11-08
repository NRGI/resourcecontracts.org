<?php
use Illuminate\Database\Seeder;
use App\Nrgi\Entities\User\Permission\Permission;
use App\Nrgi\Entities\User\Role\Role;

/**
 * Class PermissionTableSeeder
 */
class RolePermissionTableSeeder extends Seeder
{
    /**
     * Seed Role Permissions into the database.
     */
    public function run()
    {
        $countryResearcherRole = Role::select('id')->whereName(config('nrgi.roles.country-researcher.name'))->first();
        $permission_name = ['add-contract', 'edit-contract', 'edit-text', 'complete-text', 'complete-metadata',  'add-annotation', 'edit-annotation', 'complete-annotation',];
        $permissions = Permission::whereIn('name', $permission_name)->get();
        $countryResearcherRole->perms()->sync($permissions);
    }
}
