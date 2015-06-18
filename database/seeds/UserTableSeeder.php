<?php
use App\Nrgi\Entities\User\Permission\Permission;
use Illuminate\Database\Seeder;
use App\Nrgi\Entities\User\User;
use Illuminate\Support\Facades\Hash;
use App\Nrgi\Entities\User\Role\Role;

/**
 * Class UserTableSeeder
 */
class UserTableSeeder extends Seeder
{
    /**
     * Seed Admin User with Roles
     */
    public function run()
    {
        $admin     = User::firstOrCreate(
            [
                'name'     => "admin",
                'email'    => 'admin@nrgi.app',
                'password' => Hash::make('admin123'),
            ]
        );
        $adminRole = Role::select('id')->whereName(config('nrgi.roles.superadmin.name'))->first();
        $admin->roles()->sync([$adminRole->id]);

        $researcher     = User::firstOrCreate(
            [
                'name'     => "researcher",
                'email'    => 'researcher@nrgi.app',
                'password' => Hash::make('researcher123'),
            ]
        );
        $researcherRole = Role::select('id')->whereName(config('nrgi.roles.researcher.name'))->first();
        $researcher->roles()->sync([$researcherRole->id]);

        $permission_name = ['add-contract', 'edit-contract',  'complete-metadata', 'add-annotation', 'edit-annotation', 'complete-annotation',];
        $permissions = Permission::whereIn('name', $permission_name)->get();
        $researcherRole->perms()->sync($permissions);
    }
}
