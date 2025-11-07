<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed allow/block list and admin user
        $this->call([
            AllowListSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}
