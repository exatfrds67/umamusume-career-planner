<?php

namespace Database\Seeders;

use App\Models\Character;
use App\Models\User;
use Illuminate\Database\Seeder;

class CharacterTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test user if none exists
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );

        // Create test characters
        $characters = [
            [
                'name' => 'Silence Suzuka',
                'scenario_type' => 'ura_finale',
                'career_stage' => 'junior',
                'current_turn' => 15,
                'current_stats' => [
                    'speed' => 850,
                    'stamina' => 720,
                    'power' => 680,
                    'guts' => 550,
                    'wit' => 790,
                ],
                'stat_priorities' => [
                    'speed' => 5,
                    'stamina' => 4,
                    'power' => 3,
                    'guts' => 1,
                    'wit' => 2,
                ],
                'energy_level' => 85,
                'mood_status' => 'good',
                'goals' => [
                    'target_stats' => [
                        'speed' => 1000,
                        'stamina' => 800,
                        'power' => 750,
                        'guts' => 600,
                        'wit' => 850,
                    ],
                ],
            ],
            [
                'name' => 'Tokai Teio',
                'scenario_type' => 'unity_cup',
                'career_stage' => 'classic',
                'current_turn' => 28,
                'current_stats' => [
                    'speed' => 920,
                    'stamina' => 810,
                    'power' => 750,
                    'guts' => 620,
                    'wit' => 840,
                ],
                'stat_priorities' => [
                    'speed' => 5,
                    'stamina' => 4,
                    'power' => 3,
                    'guts' => 1,
                    'wit' => 2,
                ],
                'energy_level' => 92,
                'mood_status' => 'great',
                'goals' => [
                    'target_stats' => [
                        'speed' => 1100,
                        'stamina' => 900,
                        'power' => 850,
                        'guts' => 700,
                        'wit' => 900,
                    ],
                ],
            ],
            [
                'name' => 'Gold Ship',
                'scenario_type' => 'ura_finale',
                'career_stage' => 'senior',
                'current_turn' => 52,
                'current_stats' => [
                    'speed' => 1050,
                    'stamina' => 980,
                    'power' => 920,
                    'guts' => 780,
                    'wit' => 890,
                ],
                'stat_priorities' => [
                    'speed' => 5,
                    'stamina' => 4,
                    'power' => 3,
                    'guts' => 1,
                    'wit' => 2,
                ],
                'energy_level' => 78,
                'mood_status' => 'normal',
                'goals' => [
                    'target_stats' => [
                        'speed' => 1200,
                        'stamina' => 1100,
                        'power' => 1000,
                        'guts' => 850,
                        'wit' => 950,
                    ],
                ],
            ],
        ];

        foreach ($characters as $characterData) {
            Character::firstOrCreate(
                ['user_id' => $user->id, 'name' => $characterData['name']],
                array_merge($characterData, ['user_id' => $user->id])
            );
        }

        // Also seed characters for the admin user if they exist
        $adminUser = User::where('email', 'admin@umamusume.local')->first();
        if ($adminUser && $adminUser->id !== $user->id) {
            foreach ($characters as $characterData) {
                Character::firstOrCreate(
                    ['user_id' => $adminUser->id, 'name' => $characterData['name']],
                    array_merge($characterData, ['user_id' => $adminUser->id])
                );
            }
        }
    }
}
