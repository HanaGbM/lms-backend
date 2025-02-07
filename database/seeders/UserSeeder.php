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
            'phone' => '251912345678',
            'username' => 'admin',
            'password' => bcrypt('password')
        ]);

        $admin->assignRole('Admin');
    }
}
