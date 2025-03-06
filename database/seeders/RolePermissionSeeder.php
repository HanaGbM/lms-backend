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


        $superAdmin = Role::where('name', 'Super_Admin')->first();


        $permissionPrefixes = [
            'read_',
            'detail_',
            'create_',
            'update_',
            'delete_',
        ];

        $permissions = [];
        foreach ($permissionPrefixes as $prefix) {
            $permissions[] = "{$prefix}role";
            $permissions[] = "{$prefix}permission";
        }

        $permissions[] = "assign_role";
        $permissions[] = "attach_permission";
        $permissions[] = "detach_permission";
        $permissions[] = "read_activity_log";

        $superAdmin->givePermissionTo($permissions);
    }
}
