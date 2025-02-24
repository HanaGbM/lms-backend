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
            'email' => 'amandesalegnb@gmail.com',
            'phone' => '251963732919',
            'username' => 'admin',
        ], [
            'name' => 'Admin',
            'email_verified_at' => now(),
            'bod' => now()->subYears(40),
            'password' => bcrypt('password')
        ]);

        $admin->assignRole('Admin');

        $teacher = User::updateOrCreate([
            'email' => 'teacher@lms.com',
            'phone' => '251963732920',
            'username' => 'teacher',
        ], [
            'name' => 'Teacher',
            'email_verified_at' => now(),
            'bod' => now()->subYears(30),
            'password' => bcrypt('password')
        ]);

        $teacher->assignRole('Teacher');


        $student = User::updateOrCreate([
            'email' => 'student@lms.com',
            'phone' => '251963732921',
            'username' => 'student',

        ], [
            'name' => 'Student',
            'email_verified_at' => now(),
            'bod' => now()->subYears(20),
            'password' => bcrypt('password')
        ]);

        $student->assignRole('Student');
    }
}
