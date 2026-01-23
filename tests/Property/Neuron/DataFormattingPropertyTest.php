<?php

declare(strict_types=1);

/**
 * Property-based tests for Data Formatting for Agents.
 *
 * These tests validate universal properties that should hold true
 * across all valid inputs for data formatting.
 *
 * Property 7: Data Formatting for Agents
 * For any raw database model data, when formatted for agent consumption,
 * the output should be a structured string or array that includes all
 * relevant fields in an LLM-friendly format.
 *
 * **Validates: Requirements 7.3**
 *
 * Testing Strategy:
 * - Generate random character data with various configurations
 * - Format data using service methods
 * - Verify output is a non-empty string
 * - Verify output contains key character information
 * - Verify output is structured with sections
 * - Test with minimum 100 iterations
 *
 * Feature: neuron-ai-integration
 * Property: 7 - Data Formatting for Agents
 */

use App\Models\Character;
use App\Services\Neuron\TrainingAdvisorService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Property 7: Data Formatting for Agents', function () {
    it('formats character data into non-empty structured string', function () {
        // Create a character with random data
        $character = Character::factory()->create();

        // Create service instance
        $service = new TrainingAdvisorService(
            new Character,
            new \App\Models\TrainingSession,
            new \App\Models\SupportCard
        );

        // Use reflection to access protected method
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('formatTrainingContext');
        $method->setAccessible(true);

        // Format data
        $formatted = $method->invoke($service, $character, []);

        // Property: Output should be a non-empty string
        expect($formatted)->toBeString();
        expect($formatted)->not->toBeEmpty();

        // Property: Output should contain character name
        expect($formatted)->toContain($character->name);

        // Property: Output should have structured sections
        expect($formatted)->toContain('# Training Decision Context');
        expect($formatted)->toContain('## Character Information');
        expect($formatted)->toContain('## Current Statistics');
    })->repeat(100);

    it('includes all stat information in formatted output', function () {
        // Create character with specific stats
        $character = Character::factory()->create([
            'current_stats' => [
                'speed' => rand(400, 1200),
                'stamina' => rand(400, 1200),
                'power' => rand(400, 1200),
                'guts' => rand(400, 1200),
                'wit' => rand(400, 1200),
            ],
        ]);

        // Create service instance
        $service = new TrainingAdvisorService(
            new Character,
            new \App\Models\TrainingSession,
            new \App\Models\SupportCard
        );

        // Use reflection to access protected method
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('formatTrainingContext');
        $method->setAccessible(true);

        // Format data
        $formatted = $method->invoke($service, $character, []);

        // Property: Output should contain all stat names
        expect($formatted)->toContain('Speed:');
        expect($formatted)->toContain('Stamina:');
        expect($formatted)->toContain('Power:');
        expect($formatted)->toContain('Guts:');
        expect($formatted)->toContain('Wit:');

        // Property: Output should contain stat values
        foreach ($character->current_stats as $stat => $value) {
            expect($formatted)->toContain((string) $value);
        }
    })->repeat(100);

    it('includes character metadata in formatted output', function () {
        // Create character with random metadata
        $character = Character::factory()->create();

        // Create service instance
        $service = new TrainingAdvisorService(
            new Character,
            new \App\Models\TrainingSession,
            new \App\Models\SupportCard
        );

        // Use reflection to access protected method
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('formatTrainingContext');
        $method->setAccessible(true);

        // Format data
        $formatted = $method->invoke($service, $character, []);

        // Property: Output should contain scenario type
        expect($formatted)->toContain($character->scenario_type);

        // Property: Output should contain career stage
        expect($formatted)->toContain($character->career_stage);

        // Property: Output should contain current turn
        expect($formatted)->toContain((string) $character->current_turn);

        // Property: Output should contain energy level
        expect($formatted)->toContain((string) $character->energy_level);

        // Property: Output should contain mood status
        expect($formatted)->toContain($character->mood_status);
    })->repeat(100);

    it('formats training options into readable structure', function () {
        // Create character
        $character = Character::factory()->create();

        // Create random training options
        $trainingOptions = [
            'available_trainings' => [
                [
                    'type' => ['speed', 'stamina', 'power'][rand(0, 2)],
                    'energy_cost' => rand(10, 30),
                    'failure_risk' => rand(0, 50) / 100,
                    'expected_gains' => [
                        'speed' => rand(5, 20),
                        'stamina' => rand(5, 20),
                    ],
                ],
            ],
        ];

        // Create service instance
        $service = new TrainingAdvisorService(
            new Character,
            new \App\Models\TrainingSession,
            new \App\Models\SupportCard
        );

        // Use reflection to access protected method
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('formatTrainingContext');
        $method->setAccessible(true);

        // Format data
        $formatted = $method->invoke($service, $character, $trainingOptions);

        // Property: Output should contain training options section
        expect($formatted)->toContain('## Available Training Options');

        // Property: Output should contain training type
        $trainingType = $trainingOptions['available_trainings'][0]['type'];
        expect($formatted)->toContain(ucfirst($trainingType));

        // Property: Output should contain energy cost
        $energyCost = $trainingOptions['available_trainings'][0]['energy_cost'];
        expect($formatted)->toContain((string) $energyCost);
    })->repeat(100);

    it('produces consistent output format across multiple calls', function () {
        // Create character
        $character = Character::factory()->create();

        // Create service instance
        $service = new TrainingAdvisorService(
            new Character,
            new \App\Models\TrainingSession,
            new \App\Models\SupportCard
        );

        // Use reflection to access protected method
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('formatTrainingContext');
        $method->setAccessible(true);

        // Format data multiple times
        $formatted1 = $method->invoke($service, $character, []);
        $formatted2 = $method->invoke($service, $character, []);
        $formatted3 = $method->invoke($service, $character, []);

        // Property: Multiple calls should produce identical output
        expect($formatted1)->toBe($formatted2);
        expect($formatted2)->toBe($formatted3);
    })->repeat(50);

    it('formats output with proper line breaks and sections', function () {
        // Create character
        $character = Character::factory()->create();

        // Create service instance
        $service = new TrainingAdvisorService(
            new Character,
            new \App\Models\TrainingSession,
            new \App\Models\SupportCard
        );

        // Use reflection to access protected method
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('formatTrainingContext');
        $method->setAccessible(true);

        // Format data
        $formatted = $method->invoke($service, $character, []);

        // Property: Output should contain line breaks
        expect($formatted)->toContain("\n");

        // Property: Output should have multiple lines
        $lines = explode("\n", $formatted);
        expect(count($lines))->toBeGreaterThan(10);

        // Property: Output should have section headers (##)
        expect($formatted)->toContain('##');
    })->repeat(50);

    it('includes stat grades in formatted output', function () {
        // Create character with specific stats
        $character = Character::factory()->create([
            'current_stats' => [
                'speed' => 1100, // Should be SS grade
                'stamina' => 800, // Should be A grade
                'power' => 600, // Should be B grade
                'guts' => 400, // Should be C grade
                'wit' => 200, // Should be E grade
            ],
        ]);

        // Create service instance
        $service = new TrainingAdvisorService(
            new Character,
            new \App\Models\TrainingSession,
            new \App\Models\SupportCard
        );

        // Use reflection to access protected method
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('formatTrainingContext');
        $method->setAccessible(true);

        // Format data
        $formatted = $method->invoke($service, $character, []);

        // Property: Output should contain grade information
        expect($formatted)->toContain('Grade:');

        // Property: Output should contain specific grades
        expect($formatted)->toContain('SS');
        expect($formatted)->toContain('A');
        expect($formatted)->toContain('B');
        expect($formatted)->toContain('C');
        expect($formatted)->toContain('E');
    })->repeat(50);

    it('handles characters with no support cards gracefully', function () {
        // Create character without support cards
        $character = Character::factory()->create();

        // Create service instance
        $service = new TrainingAdvisorService(
            new Character,
            new \App\Models\TrainingSession,
            new \App\Models\SupportCard
        );

        // Use reflection to access protected method
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('formatTrainingContext');
        $method->setAccessible(true);

        // Format data
        $formatted = $method->invoke($service, $character, []);

        // Property: Output should still be valid
        expect($formatted)->toBeString();
        expect($formatted)->not->toBeEmpty();

        // Property: Output should mention no support cards
        expect($formatted)->toContain('## Support Card Deck');
        expect($formatted)->toContain('No support cards');
    })->repeat(50);

    it('formats output without raw database structures', function () {
        // Create character
        $character = Character::factory()->create();

        // Create service instance
        $service = new TrainingAdvisorService(
            new Character,
            new \App\Models\TrainingSession,
            new \App\Models\SupportCard
        );

        // Use reflection to access protected method
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('formatTrainingContext');
        $method->setAccessible(true);

        // Format data
        $formatted = $method->invoke($service, $character, []);

        // Property: Output should not contain raw PHP structures
        expect($formatted)->not->toContain('Array(');
        expect($formatted)->not->toContain('stdClass');
        expect($formatted)->not->toContain('Object');

        // Property: Output should not contain database column names in raw form
        expect($formatted)->not->toContain('created_at:');
        expect($formatted)->not->toContain('updated_at:');
        expect($formatted)->not->toContain('user_id:');
    })->repeat(50);

    it('includes request for analysis at the end', function () {
        // Create character
        $character = Character::factory()->create();

        // Create service instance
        $service = new TrainingAdvisorService(
            new Character,
            new \App\Models\TrainingSession,
            new \App\Models\SupportCard
        );

        // Use reflection to access protected method
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('formatTrainingContext');
        $method->setAccessible(true);

        // Format data
        $formatted = $method->invoke($service, $character, []);

        // Property: Output should end with a request for analysis
        expect($formatted)->toContain('Please analyze');
        expect($formatted)->toContain('recommendation');
    })->repeat(50);
});
