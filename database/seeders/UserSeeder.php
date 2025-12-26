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
        User::create([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('securepassword'),
            'role' => 'admin',
            'status' => 'active',
        ]);
        User::create([
            'name' => 'staff',
            'email' => 'staff@gmail.com',
            'password' => bcrypt('securepassword'),
            'role' => 'staff',
            'status' => 'active',
        ]);
        User::create([
            'name' => 'customer',
            'email' => 'customer@gmail.com',
            'password' => bcrypt('securepassword'),
            'role' => 'customer',
            'status' => 'active',
        ]);
    }
}
