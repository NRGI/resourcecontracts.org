<?php
use App\Nrgi\Entities\User\Role\Role;
use Illuminate\Database\Seeder;

/**
 * Class RolesTableSeeder
 */
class RoleTableSeeder extends Seeder
{
    /**
     * Seed different Roles into the database.
     */
    public function run()
    {
        foreach (config('nrgi.roles') as $role) {
            if (!Role::where('name', $role['name'])->first()) {
                Role::firstOrCreate($role);
            }
        }
    }
}
