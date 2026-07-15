<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Dev-only bootstrap admin — there is no self-registration, so an admin
        // account must exist before anyone can log in. Change this password
        // immediately in any non-local environment.
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@cdrrmo.test',
            'is_admin' => true,
        ]);
    }
}
