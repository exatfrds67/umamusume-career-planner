<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Character;
use App\Models\Factor;
use App\Services\FactorService;
use Illuminate\Database\Seeder;

/**
 * Factor Seeder
 *
 * Seeds sample factor inheritance data for testing and demonstration.
 * Creates realistic factor combinations for a subset of characters.
 */
class FactorSeeder extends Seeder
{
    public function __construct(
        private readonly FactorService $factorService
    ) {}

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding factor inheritance data...');

        // Get a sample of characters to add factors to
        $characters = Character::whereIn('name', [
            'Special Week',
            'Silence Suzuka',
            'Tokai Teio',
            'Vodka',
            'Daiwa Scarlet',
            'Gold Ship',
            'Mejiro McQueen',
            'Rice Shower',
            'Air Groove',
            'Symboli Rudolf',
        ])->get();

        if ($characters->isEmpty()) {
            $this->command->warn('No characters found. Please run EnhancedRealUmaMusumeCharactersSeeder first.');

            return;
        }

        $this->command->info("Found {$characters->count()} characters to add factors to.");

        $totalFactors = 0;

        foreach ($characters as $character) {
            $factorCount = $this->seedFactorsForCharacter($character);
            $totalFactors += $factorCount;
            $this->command->info("  ✓ {$character->name}: {$factorCount} factors");
        }

        $this->command->info("✅ Successfully seeded {$totalFactors} factors for {$characters->count()} characters.");
    }

    /**
     * Seed factors for a specific character.
     */
    private function seedFactorsForCharacter(Character $character): int
    {
        $count = 0;

        // Blue Factors (Stat Bonuses)
        $count += $this->seedBlueFactors($character);

        // Red Factors (Aptitude Upgrades)
        $count += $this->seedRedFactors($character);

        // Green Factors (Unique Skills)
        $count += $this->seedGreenFactors($character);

        // White Factors (Normal Skills)
        $count += $this->seedWhiteFactors($character);

        return $count;
    }

    /**
     * Seed Blue Factors (stat bonuses).
     */
    private function seedBlueFactors(Character $character): int
    {
        $count = 0;

        // Speed factors (common for most characters)
        $this->factorService->createBlueFactor(
            $character,
            'speed',
            '3_star',
            'main_parent_1',
            'Silence Suzuka'
        );
        $count++;

        $this->factorService->createBlueFactor(
            $character,
            'speed',
            '2_star',
            'grandparent_1',
            'Symboli Rudolf'
        );
        $count++;

        // Stamina factors (for long-distance specialists)
        if (in_array($character->name, ['Mejiro McQueen', 'Rice Shower', 'Gold Ship'])) {
            $this->factorService->createBlueFactor(
                $character,
                'stamina',
                '3_star',
                'main_parent_2',
                'Mejiro McQueen'
            );
            $count++;

            $this->factorService->createBlueFactor(
                $character,
                'stamina',
                '2_star',
                'grandparent_2',
                'Stay Gold'
            );
            $count++;
        }

        // Power factors (for front runners)
        if (in_array($character->name, ['Special Week', 'Tokai Teio', 'Vodka'])) {
            $this->factorService->createBlueFactor(
                $character,
                'power',
                '2_star',
                'grandparent_3',
                'Oguri Cap'
            );
            $count++;
        }

        // Wit factors (for strategists)
        if (in_array($character->name, ['Daiwa Scarlet', 'Air Groove', 'Symboli Rudolf'])) {
            $this->factorService->createBlueFactor(
                $character,
                'wit',
                '2_star',
                'grandparent_4',
                'Grass Wonder'
            );
            $count++;
        }

        return $count;
    }

    /**
     * Seed Red Factors (aptitude upgrades).
     */
    private function seedRedFactors(Character $character): int
    {
        $count = 0;

        // Mile aptitude upgrade (common)
        $this->factorService->createRedFactor(
            $character,
            'mile',
            1,
            '1_star',
            'main_parent_1',
            'Silence Suzuka'
        );
        $count++;

        // Long distance aptitude upgrade (for long-distance specialists)
        if (in_array($character->name, ['Mejiro McQueen', 'Rice Shower', 'Gold Ship'])) {
            $this->factorService->createRedFactor(
                $character,
                'long',
                1,
                '1_star',
                'main_parent_2',
                'Mejiro McQueen'
            );
            $count++;
        }

        // Turf aptitude upgrade (most characters)
        $this->factorService->createRedFactor(
            $character,
            'turf',
            1,
            '1_star',
            'grandparent_1',
            'Symboli Rudolf'
        );
        $count++;

        // Running style aptitude upgrades
        if (in_array($character->name, ['Special Week', 'Tokai Teio'])) {
            $this->factorService->createRedFactor(
                $character,
                'front_runner',
                1,
                '1_star',
                'grandparent_2',
                'Oguri Cap'
            );
            $count++;
        }

        if (in_array($character->name, ['Daiwa Scarlet', 'Vodka'])) {
            $this->factorService->createRedFactor(
                $character,
                'pace_chaser',
                1,
                '1_star',
                'grandparent_3',
                'Daiwa Scarlet'
            );
            $count++;
        }

        return $count;
    }

    /**
     * Seed Green Factors (unique skills).
     */
    private function seedGreenFactors(Character $character): int
    {
        $count = 0;

        // Unique skills based on character specialization
        $uniqueSkills = [
            'Special Week' => 'Winning Ticket',
            'Silence Suzuka' => 'Absolute Silence',
            'Tokai Teio' => 'Emperor\'s Dignity',
            'Vodka' => 'Vodka Miracle',
            'Daiwa Scarlet' => 'Scarlet Blaze',
            'Gold Ship' => 'Golden Journey',
            'Mejiro McQueen' => 'Queen\'s Pride',
            'Rice Shower' => 'April Fool',
            'Air Groove' => 'Groove in the Heart',
            'Symboli Rudolf' => 'Emperor\'s Road',
        ];

        if (isset($uniqueSkills[$character->name])) {
            $this->factorService->createGreenFactor(
                $character,
                $uniqueSkills[$character->name],
                [
                    'effect_type' => 'speed_boost',
                    'effect_value' => 15,
                    'condition' => 'final_straight',
                    'duration' => 'permanent',
                ],
                'main_parent_1',
                $character->name
            );
            $count++;
        }

        return $count;
    }

    /**
     * Seed White Factors (normal skills).
     */
    private function seedWhiteFactors(Character $character): int
    {
        $count = 0;

        // Common racing skills
        $this->factorService->createWhiteFactor(
            $character,
            'Acceleration',
            [
                'distance_type' => 'mile',
                'bonus_value' => 8,
                'condition' => 'mid_race',
            ],
            '2_star',
            'grandparent_1',
            'Silence Suzuka'
        );
        $count++;

        $this->factorService->createWhiteFactor(
            $character,
            'Endurance',
            [
                'distance_type' => 'long',
                'bonus_value' => 10,
                'condition' => 'late_race',
            ],
            '2_star',
            'grandparent_2',
            'Mejiro McQueen'
        );
        $count++;

        // Specialized skills based on character type
        if (in_array($character->name, ['Special Week', 'Tokai Teio', 'Vodka'])) {
            $this->factorService->createWhiteFactor(
                $character,
                'Front Runner',
                [
                    'distance_type' => 'medium',
                    'bonus_value' => 12,
                    'condition' => 'early_race',
                ],
                '3_star',
                'main_parent_1',
                'Oguri Cap'
            );
            $count++;
        }

        if (in_array($character->name, ['Daiwa Scarlet', 'Air Groove'])) {
            $this->factorService->createWhiteFactor(
                $character,
                'Late Surge',
                [
                    'distance_type' => 'mile',
                    'bonus_value' => 14,
                    'condition' => 'final_straight',
                ],
                '3_star',
                'main_parent_2',
                'Daiwa Scarlet'
            );
            $count++;
        }

        return $count;
    }
}
