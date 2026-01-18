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
        // Create admin user first
        $this->call([
            AdminUserSeeder::class,
        ]);

        // Create a test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Seed UCP-specific data
        $this->call([
            UcpSkillsSeeder::class,
            UcpSupportCardsSeeder::class,
            // UcpAptitudesSeeder::class, // Skip for now - requires characters to be created first
        ]);
    }
}
