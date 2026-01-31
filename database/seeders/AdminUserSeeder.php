<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin user already exists
        $adminExists = User::where('email', 'admin@umamusume.local')->exists();

        if ($adminExists) {
            $this->command->info('Admin user already exists!');

            return;
        }

        // Create admin user
        User::create([
            'uuid' => Str::uuid()->toString(),
            'email' => 'admin@umamusume.local',
            'name' => 'Admin',
            'password' => Hash::make('admin123'),
            'is_admin' => true, // CRITICAL: Set admin flag
            'preferences' => [
                'theme' => 'dark',
                'language' => 'en',
            ],
            'accessibility_settings' => [],
            'ai_settings' => [
                'subscription_tier' => 'admin',
                'budget_limit' => 1000.0,
                'preferred_model' => 'ollama',
            ],
            'mcp_settings' => [
                'enabled' => true,
            ],
        ]);

        $this->command->info('Admin user created successfully!');
        $this->command->info('Email: admin@umamusume.local');
        $this->command->info('Password: admin123');
        $this->command->warn('Please change the password after first login!');
    }
}
