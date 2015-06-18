<?php
use App\Nrgi\Entities\User\Permission\Permission;
use Illuminate\Database\Seeder;

/**
 * Class PermissionTableSeeder
 */
class PermissionTableSeeder extends Seeder
{
    /**
     * Seed Permissions into the database.
     */
    public function run()
    {
        foreach (config('nrgi.permissions') as $permission) {
            Permission::firstOrCreate($permission);
        }
    }
}
