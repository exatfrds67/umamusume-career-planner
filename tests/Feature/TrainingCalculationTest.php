<?php

declare(strict_types=1);

use App\Models\Character;
use App\Services\MCP\MCPClientService;
use App\Services\MCP\TrainingOptimizationAgent;
use App\Services\TrainingCalculationService;

test('calculates speed training gains correctly', function () {
    // Mock dependencies
    /** @var MCPClientService&Mockery\MockInterface $mcpClient */
    $mcpClient = Mockery::mock(MCPClientService::class);
    /** @var TrainingOptimizationAgent&Mockery\MockInterface $trainingAgent */
    $trainingAgent = Mockery::mock(TrainingOptimizationAgent::class);

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
    ]);

    $result = $service->calculateTrainingPrediction($character, 'speed');

    expect($result['stat_gains']['speed'])->toBeGreaterThan(0);
    expect($result['energy_cost'])->toBeGreaterThan(0);
    expect($result['failure_risk'])->toBeLessThan(0.1);
});
