<?php

declare(strict_types=1);

use App\Models\Character;
use App\Services\MCP\MCPClientService;
use App\Services\MCP\TrainingOptimizationAgent;
use App\Services\TrainingCalculationService;

test('calculates speed training gains correctly', function () {
    // Mock dependencies
    /** @var MCPClientService&\Mockery\MockInterface $mcpClient */
    $mcpClient = \Mockery::mock(MCPClientService::class);
    /** @var TrainingOptimizationAgent&\Mockery\MockInterface $trainingAgent */
    $trainingAgent = \Mockery::mock(TrainingOptimizationAgent::class);

    $service = new TrainingCalculationService($mcpClient, $trainingAgent);

    $character = Character::factory()->create([
        'current_stats' => [
            'speed' => 100,
            'stamina' => 100,
            'power' => 100,
            'guts' => 100,
            'wit' => 100,
        ],
        'energy_level' => 100,
        'mood_status' => 'normal',
        'growth_rates' => ['speed' => 0, 'power' => 0], // No growth bonus to keep math simple
        'facility_levels' => ['speed' => 1], // Level 1 = 0 bonus
        'scenario_type' => 'ura_finale',
    ]);

    $result = $service->calculateTrainingPrediction($character, 'speed');

    expect($result['stat_gains']['speed'])->toBeGreaterThan(0);
    expect($result['energy_cost'])->toBeGreaterThan(0);
    expect($result['failure_risk'])->toBeLessThan(0.1);
    expect($result['breakdown'])->toHaveKeys([
        'base_gains',
        'stat_bonus',
        'growth_rate_multiplier',
        'mood_multiplier',
        'training_effect',
        'support_card_presence_multiplier',
        'friendship_multiplier',
        'facility_bonus',
        'total_multiplier',
        'per_training_cap',
    ]);
});

test('verified formula produces consistent results', function () {
    /** @var MCPClientService&\Mockery\MockInterface $mcpClient */
    $mcpClient = \Mockery::mock(MCPClientService::class);
    /** @var TrainingOptimizationAgent&\Mockery\MockInterface $trainingAgent */
    $trainingAgent = \Mockery::mock(TrainingOptimizationAgent::class);

    $service = new TrainingCalculationService($mcpClient, $trainingAgent);

    $character = Character::factory()->create([
        'current_stats' => [
            'speed' => 500,
            'stamina' => 500,
            'power' => 500,
            'guts' => 500,
            'wit' => 500,
        ],
        'energy_level' => 100,
        'mood_status' => 'good', // +2%
        'growth_rates' => ['speed' => 20, 'power' => 10], // +20% speed, +10% power
        'scenario_type' => 'ura_finale',
    ]);

    $result = $service->calculateTrainingPrediction($character, 'speed', [
        'support_cards' => [
            ['card_type' => 'speed', 'limit_break_level' => 2, 'friendship_level' => 80],
            ['card_type' => 'speed', 'limit_break_level' => 3, 'friendship_level' => 85],
            ['card_type' => 'speed', 'limit_break_level' => 0, 'friendship_level' => 90],
        ],
        'participants' => 3,
    ]);

    // Verify all formula components are present
    expect($result['breakdown']['growth_rate_multiplier'])->toBe(1.2) // 1 + 0.20
        ->and($result['breakdown']['mood_multiplier'])->toBe(1.02) // Good mood
        ->and($result['breakdown']['support_card_presence_multiplier'])->toBe(1.15) // 3 cards
        ->and($result['breakdown']['training_effect'])->toBeGreaterThan(0.0)
        ->and($result['breakdown']['friendship_multiplier'])->toBe(1.2) // Flat 1.2x with 3+ rainbow cards
        ->and($result['stat_gains']['speed'])->toBeGreaterThan(0)
        ->and($result['stat_gains']['speed'])->toBeLessThanOrEqual(100); // Per-training cap
});

test('per-training cap applies correctly', function () {
    /** @var MCPClientService&\Mockery\MockInterface $mcpClient */
    $mcpClient = \Mockery::mock(MCPClientService::class);
    /** @var TrainingOptimizationAgent&\Mockery\MockInterface $trainingAgent */
    $trainingAgent = \Mockery::mock(TrainingOptimizationAgent::class);

    $service = new TrainingCalculationService($mcpClient, $trainingAgent);

    // Test below 1200 (cap = 100)
    $characterBelow = Character::factory()->create([
        'current_stats' => [
            'speed' => 500,
            'stamina' => 500,
            'power' => 500,
            'guts' => 500,
            'wit' => 500,
        ],
        'energy_level' => 100,
        'mood_status' => 'great',
        'growth_rates' => ['speed' => 100], // 2x multiplier
        'scenario_type' => 'ura_finale',
    ]);

    $resultBelow = $service->calculateTrainingPrediction($characterBelow, 'speed');
    expect($resultBelow['stat_gains']['speed'])->toBeLessThanOrEqual(100);

    // Test above 1200 (cap = 50)
    $characterAbove = Character::factory()->create([
        'current_stats' => [
            'speed' => 1250,
            'stamina' => 1250,
            'power' => 1250,
            'guts' => 1250,
            'wit' => 1250,
        ],
        'energy_level' => 100,
        'mood_status' => 'great',
        'growth_rates' => ['speed' => 100], // 2x multiplier
        'scenario_type' => 'ura_finale',
    ]);

    $resultAbove = $service->calculateTrainingPrediction($characterAbove, 'speed');
    expect($resultAbove['stat_gains']['speed'])->toBeLessThanOrEqual(50);
});

test('scenario-specific mechanics are calculated', function () {
    /** @var MCPClientService&\Mockery\MockInterface $mcpClient */
    $mcpClient = \Mockery::mock(MCPClientService::class);
    /** @var TrainingOptimizationAgent&\Mockery\MockInterface $trainingAgent */
    $trainingAgent = \Mockery::mock(TrainingOptimizationAgent::class);

    $service = new TrainingCalculationService($mcpClient, $trainingAgent);

    // Test URA Finale
    $uraCharacter = Character::factory()->create([
        'current_stats' => [
            'speed' => 100,
            'stamina' => 100,
            'power' => 100,
            'guts' => 100,
            'wit' => 100,
        ],
        'energy_level' => 100,
        'mood_status' => 'normal',
        'scenario_type' => 'ura_finale',
    ]);

    $uraResult = $service->calculateTrainingPrediction($uraCharacter, 'speed');
    expect($uraResult['scenario_specific']['scenario'])->toBe('ura_finale');

    // Test Unity Cup
    $unityCupCharacter = Character::factory()->create([
        'current_stats' => [
            'speed' => 100,
            'stamina' => 100,
            'power' => 100,
            'guts' => 100,
            'wit' => 100,
        ],
        'energy_level' => 100,
        'mood_status' => 'normal',
        'scenario_type' => 'unity_cup',
        'facility_levels' => ['speed' => 3],
    ]);

    $unityCupResult = $service->calculateTrainingPrediction($unityCupCharacter, 'speed');
    expect($unityCupResult['scenario_specific']['scenario'])->toBe('unity_cup')
        ->and($unityCupResult['breakdown']['facility_bonus'])->toBe(0.5); // Level 3 = 50%
});
