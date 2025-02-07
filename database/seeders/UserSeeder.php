<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $admin = User::updateOrCreate([
            'name' => 'Admin',
            'email' => 'amandesalegnb@gmail.com',
            'phone' => '251963732919',
            'username' => 'admin',
            'password' => bcrypt('password')
        ]);

        $admin->assignRole('Admin');


        $teacher = User::updateOrCreate([
            'name' => 'Teacher',
            'email' => 'teacher@lms.com',
            'phone' => '251963732920',
            'username' => 'teacher',
            'password' => bcrypt('password')
        ]);

        $teacher->assignRole('Teacher');


        $student = User::updateOrCreate([
            'name' => 'Student',
            'email' => 'student@lms.com',
            'phone' => '251963732921',
            'username' => 'student',
            'password' => bcrypt('password')
        ]);

        $student->assignRole('Student');
    }
}
