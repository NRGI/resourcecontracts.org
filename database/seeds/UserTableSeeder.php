<?php
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
        $admin = User::firstOrCreate(
            [
                'name'     => "admin",
                'email'    => 'admin@nrgi.app',
                'password' => Hash::make('admin123'),
            ]
        );
        $adminRole = Role::select('id')->whereName(config('nrgi.roles.superadmin.name'))->first();
        $admin->roles()->sync([$adminRole->id]);
    }
}
