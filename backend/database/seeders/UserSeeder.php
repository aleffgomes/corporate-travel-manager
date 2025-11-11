<?php

namespace Database\Seeders;

use App\Models\RoleModel;
use App\Models\UserModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin User
        UserModel::create([
            'name' => 'Administrator',
            'email' => 'admin@corporatetravel.com',
            'password' => Hash::make('admin123'),
            'role_id' => RoleModel::ADMIN_ID,
        ]);

        // Regular Users
        UserModel::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password123'),
            'role_id' => RoleModel::USER_ID,
        ]);

        UserModel::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password123'),
            'role_id' => RoleModel::USER_ID,
        ]);

        UserModel::create([
            'name' => 'Bob Johnson',
            'email' => 'bob@example.com',
            'password' => Hash::make('password123'),
            'role_id' => RoleModel::USER_ID,
        ]);
    }
}
