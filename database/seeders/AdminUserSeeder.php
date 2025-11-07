<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminEmail = 'admin@anon.to';

        User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Admin User',
                'username' => 'admin',
                'email' => $adminEmail,
                'password' => Hash::make('password'), // Change this in production!
                'email_verified_at' => now(),
                'is_admin' => true,
                'is_verified' => true,
                'api_rate_limit' => PHP_INT_MAX,
                'last_login_at' => null,
            ]
        );

        $this->command->info('Admin user created: '.$adminEmail.' (password: password)');
        $this->command->warn('IMPORTANT: Change the admin password before deploying to production!');
    }
}
