<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'admin@kidlink.test',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
        ]);

        User::create([
            'first_name' => 'Teacher',
            'last_name' => 'One',
            'email' => 'teacher@kidlink.test',
            'password' => Hash::make('password'),
            'role' => 'teacher',
        ]);

        User::create([
            'first_name' => 'Parent',
            'last_name' => 'One',
            'email' => 'parent@kidlink.test',
            'password' => Hash::make('password'),
            'role' => 'parent',
        ]);
    }
}
