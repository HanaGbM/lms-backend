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
        $superAdminPermissions[] = "view_pulse";

        $superAdmin->givePermissionTo($superAdminPermissions);


        /**
         *Admin */
        $admin = Role::where('name', 'Admin')->first();
        $adminPermissions = [];
        foreach ($permissionPrefixes as $prefix) {
            $adminPermissions[] = "{$prefix}discussion";
            $adminPermissions[] = "{$prefix}reply";
            $adminPermissions[] = "{$prefix}user";
            $adminPermissions[] = "{$prefix}module";
            $teacherPermissions[] = "{$prefix}chapter";
            $teacherPermissions[] = "{$prefix}chapter_material";
        }
        $adminPermissions[] = "read_teacher";
        $adminPermissions[] = "read_student";
        $adminPermissions[] = "assign_teachers_module";
        $adminPermissions[] = "read_module_teachers";
        $adminPermissions[] = "read_module_students";
        $adminPermissions[] = "assign_students_module";

        $admin->givePermissionTo($adminPermissions);


        /**
         *Teacher */
        $teacher = Role::where('name', 'Teacher')->first();
        $teacherPermissions = [];
        foreach ($permissionPrefixes as $prefix) {
            $teacherPermissions[] = "{$prefix}module";
            $teacherPermissions[] = "{$prefix}chapter";
            $teacherPermissions[] = "{$prefix}chapter_material";
            $teacherPermissions[] = "{$prefix}test";
            $teacherPermissions[] = "{$prefix}question";
        }
        $teacherPermissions[] = "sort_chapters";
        $teacherPermissions[] = "read_student";
        $teacherPermissions[] = "delete_file";
        $teacherPermissions[] = "read_question_response";
        $teacherPermissions[] = "evaluate_question_response";

        $teacher->givePermissionTo($teacherPermissions);


        /**
         *Student */
        $student = Role::where('name', 'Student')->first();
        $studentPermissions = [];
        foreach ($permissionPrefixes as $prefix) {
            // $studentPermissions[] = "{$prefix}module";
        }
        $studentPermissions[] = "read_module";
        $studentPermissions[] = "read_module_tests";
        $studentPermissions[] = "read_test_questions";
        $studentPermissions[] = "read_module_chapters";
        $studentPermissions[] = "create_question_response";
        $studentPermissions[] = "read_grade_report";
        $studentPermissions[] = "read_chapter_material";


        $student->givePermissionTo($studentPermissions);
    }
}
