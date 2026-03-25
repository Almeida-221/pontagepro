<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@pointagepro.com'],
            [
                'name'       => 'Super Admin',
                'email'      => 'admin@pointagepro.com',
                'password'   => Hash::make('Admin@2024'),
                'role'       => 'super_admin',
                'company_id' => null,
            ]
        );
    }
}
