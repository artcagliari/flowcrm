<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@crm.com'],
            [
                'name' => 'Admin Master',
                'password' => 'password',
                'role' => 'super_admin',
                'status' => 'active',
                'is_superadmin' => true,
            ]
        );
    }
}
