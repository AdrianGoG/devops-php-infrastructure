<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Creates the demo account used to present the dashboard.
     *
     * updateOrCreate keeps the seeder idempotent, so it can run on every
     * deployment without failing on the unique email index.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@devops.test'],
            [
                'name' => 'DevOps Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
    }
}
