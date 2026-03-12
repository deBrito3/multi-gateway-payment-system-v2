<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@payment.com'],
            [
                'name' => 'Admin',
                'password' => 'password',
            ]
        );

        $adminRole = Role::where('name', 'ADMIN')->first();
        if ($adminRole && !$admin->hasRole('ADMIN')) {
            $admin->roles()->attach($adminRole);
        }
    }
}
