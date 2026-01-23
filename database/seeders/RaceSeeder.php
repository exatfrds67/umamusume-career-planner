<?php

namespace Database\Seeders;

use App\Models\Career;
use App\Models\Character;
use App\Models\Race;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have a user
        $user = User::query()->first();
        if (! $user) {
            $user = User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        // Ensure we have a character
        $character = Character::query()->first();
        if (! $character) {
            // Create a generic character if none exists
            $character = Character::create([
                'name' => 'Special Week',
                'initials' => 'SW',
                'base_stars' => 3,
                'stat_growth_rates' => [
                    'speed' => 20,
                    'stamina' => 10,
                    'power' => 10,
                    'guts' => 10,
                    'wit' => 0,
                ],
                'aptitudes' => [
                    'turf' => 'A',
                    'dirt' => 'G',
                    'short' => 'F',
                    'mile' => 'A',
                    'middle' => 'A',
                    'long' => 'A',
                    'runner' => 'G',
                    'leader' => 'A',
                    'betweener' => 'A',
                    'chaser' => 'F',
                ],
                // Add minimum required fields based on schema
                'data_source' => 'seeder',
            ]);
        }

        // Ensure we have a career
        $career = Career::query()->where('character_id', $character->id)->first();
        if (! $career) {
            $career = Career::create([
                'character_id' => $character->id,
                'user_id' => $user->id,
                'career_name' => 'URA Finals Run',
                'scenario_type' => 'URA',
                'status' => 'active',
                'current_turn' => 1,
                'started_at' => now(),
            ]);
        }

        // Define some sample races
        $races = [
            [
                'race_name' => 'Junior Debut',
                'race_grade' => 'debut',
                'distance' => 2000,
                'distance_category' => 'intermediate',
                'surface' => 'turf',
                'turn_number' => 1,
                'finish_position' => 1,
                'race_result' => 'victory',
                'created_at' => Carbon::now()->subMonths(6),
            ],
            [
                'race_name' => 'Hopeful Stakes',
                'race_grade' => 'G1',
                'distance' => 2000, // 2000m is intermediate usually, or middle in Japan terms
                'distance_category' => 'intermediate',
                'surface' => 'turf',
                'turn_number' => 12,
                'finish_position' => 1,
                'race_result' => 'victory',
                'created_at' => Carbon::now()->subMonths(5),
            ],
            [
                'race_name' => 'Satsuki Sho',
                'race_grade' => 'G1',
                'distance' => 2000,
                'distance_category' => 'intermediate',
                'surface' => 'turf',
                'turn_number' => 20,
                'finish_position' => 2,
                'race_result' => 'place',
                'created_at' => Carbon::now()->subMonths(4),
            ],
            [
                'race_name' => 'Japan Derby',
                'race_grade' => 'G1',
                'distance' => 2400,
                'distance_category' => 'intermediate',
                'surface' => 'turf',
                'turn_number' => 24,
                'finish_position' => 1,
                'race_result' => 'victory',
                'created_at' => Carbon::now()->subMonths(3),
            ],
            [
                'race_name' => 'Kikuka Sho',
                'race_grade' => 'G1',
                'distance' => 3000,
                'distance_category' => 'long',
                'surface' => 'turf',
                'turn_number' => 36,
                'finish_position' => 1,
                'race_result' => 'victory',
                'created_at' => Carbon::now()->subMonths(1),
            ],
            [
                'race_name' => 'Arima Kinen',
                'race_grade' => 'G1',
                'distance' => 2500,
                'distance_category' => 'long',
                'surface' => 'turf',
                'turn_number' => 48, // Future/Current race
                'finish_position' => 1, // Assume won for simplicity of seeding, or null if nullable
                'race_result' => 'victory',
                'created_at' => Carbon::now(),
            ],
        ];

        foreach ($races as $raceData) {
            Race::create([
                'career_id' => $career->id,
                'character_id' => $character->id,
                'race_name' => $raceData['race_name'],
                'race_grade' => $raceData['race_grade'],
                'distance_meters' => $raceData['distance'], // Correct field name from model property
                'distance_category' => $raceData['distance_category'],
                'surface' => $raceData['surface'],
                'turn_number' => $raceData['turn_number'],
                'finish_position' => $raceData['finish_position'],
                'race_result' => $raceData['race_result'],
                'won_race' => $raceData['finish_position'] === 1,
                // Defaults
                'weather' => 'sunny',
                'track_condition' => 'good',
                'field_size' => 18,
                'motivation' => 'very_high',
                'energy_level' => 100,
                // Stats at race time (mocked)
                'speed_at_race' => 300 + ($raceData['turn_number'] * 10),
                'stamina_at_race' => 200 + ($raceData['turn_number'] * 8),
                'power_at_race' => 250 + ($raceData['turn_number'] * 9),
                'guts_at_race' => 200 + ($raceData['turn_number'] * 5),
                'wit_at_race' => 150 + ($raceData['turn_number'] * 5),
            ]);
        }
    }
}
