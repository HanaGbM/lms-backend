<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
        ]);




        $permissionPrefixes = [
            'read_',
            'create_',
            'update_',
            'delete_',
        ];


        /**
         *Super_Admin */
        $superAdmin = Role::where('name', 'Super_Admin')->first();
        $superAdminPermissions = [];
        foreach ($permissionPrefixes as $prefix) {
            $superAdminPermissions[] = "{$prefix}role";
            $superAdminPermissions[] = "{$prefix}permission";
        }

        $superAdminPermissions[] = "assign_role";
        $superAdminPermissions[] = "attach_permission";
        $superAdminPermissions[] = "detach_permission";
        $superAdminPermissions[] = "read_activity_log";

        $superAdmin->givePermissionTo($superAdminPermissions);


        /**
         *Admin */
        $admin = Role::where('name', 'Admin')->first();
        $adminPermissions = [];
        foreach ($permissionPrefixes as $prefix) {
            $adminPermissions[] = "{$prefix}discussion";
            $adminPermissions[] = "{$prefix}reply";
        }

        $admin->givePermissionTo($adminPermissions);
    }
}
