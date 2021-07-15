<?php
use App\Nrgi\Entities\User\Permission\Permission;
use Illuminate\Database\Seeder;
use App\Nrgi\Entities\User\User;
use Illuminate\Support\Facades\Hash;
use App\Nrgi\Entities\User\Role\Role;

/**
 * Class UserTableSeeder
 */
class SuperAdminEditorSeeder extends Seeder
{
    /**
     * Seed Admin User with Roles
     */
    public function run()
    {
        $superadminEditor    = User::firstOrCreate(
            [
                'name'     => "SuperAdmin Editor",
                'email'    => 'admin_editor@nrgi.app',
                'password' => Hash::make('admin_editor123'),
                'organization' => '',
                'status' => 'true'
            ]
        );
        $superadminEditorRole = Role::select('id')->whereName(config('nrgi.roles.superadmin-editor.name'))->first();
        $superadminEditor->roles()->sync([$superadminEditorRole->id]);
    }
}
