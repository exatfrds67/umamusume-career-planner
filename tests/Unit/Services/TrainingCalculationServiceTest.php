<?php

use App\Models\Character;
use App\Services\MCP\MCPClientService;
use App\Services\MCP\TrainingOptimizationAgent;
use App\Services\TrainingCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

/** @var MCPClientService&Mockery\MockInterface $mcpClient */
$mcpClient = null;
/** @var TrainingOptimizationAgent&Mockery\MockInterface $trainingAgent */
$trainingAgent = null;
/** @var TrainingCalculationService $service */
$service = null;
/** @var Character $character */
$character = null;

beforeEach(function () use (&$mcpClient, &$trainingAgent, &$service, &$character): void {
    /** @var MCPClientService&Mockery\MockInterface $mcpClient */
    $mcpClient = Mockery::mock(MCPClientService::class);
    /** @var TrainingOptimizationAgent&Mockery\MockInterface $trainingAgent */
    $trainingAgent = Mockery::mock(TrainingOptimizationAgent::class);

    $service = new TrainingCalculationService($mcpClient, $trainingAgent);

    $character = Character::factory()->make([
        'scenario_type' => 'ura_finale',
        'current_stats' => [
            'speed' => 500,
            'stamina' => 450,
            'power' => 400,
            'guts' => 420,
            'wit' => 380,
        ],
        'energy_level' => 80,
        'mood_status' => 'good',
        'growth_rates' => [
            'speed' => 10,
            'stamina' => 5,
            'power' => 0,
            'guts' => 0,
            'wit' => 0,
        ],
        'facility_levels' => [],
    ]);

    // Store in test context
});

it('calculates base stat gains correctly', function () use (&$service, &$character): void {
    $prediction = $service->calculateTrainingPrediction(
        $character,
        'speed'
    );

    expect($prediction)->toHaveKeys(['stat_gains', 'energy_cost', 'failure_risk', 'total_bonus', 'breakdown']);
    expect($prediction['stat_gains'])->toHaveKey('speed');
    expect($prediction['stat_gains']['speed'])->toBeGreaterThan(0);
});

it('applies growth rate bonuses', function () use (&$service, &$character): void {
    $prediction = $service->calculateTrainingPrediction(
        $character,
        'speed'
    );

    // Character has 10% growth rate for speed
    expect($prediction['breakdown']['growth_rate_bonus'])->toBe(0.10);
});

it('calculates energy cost based on mood', function () use (&$service, &$character): void {
    $character->mood_status = 'great';
    $prediction = $service->calculateTrainingPrediction(
        $character,
        'speed'
    );

    // Great mood should reduce energy cost
    expect($prediction['energy_cost'])->toBeLessThan(20);

    $character->mood_status = 'awful';
    $prediction = $service->calculateTrainingPrediction(
        $character,
        'speed'
    );

    // Awful mood should increase energy cost
    expect($prediction['energy_cost'])->toBeGreaterThan(20);
});

it('calculates failure risk based on energy level', function () use (&$service, &$character): void {
    $character->energy_level = 90;
    $prediction = $service->calculateTrainingPrediction(
        $character,
        'speed'
    );

    $highEnergyRisk = $prediction['failure_risk'];

    $character->energy_level = 20;
    $prediction = $service->calculateTrainingPrediction(
        $character,
        'speed'
    );

    $lowEnergyRisk = $prediction['failure_risk'];

    expect($lowEnergyRisk)->toBeGreaterThan($highEnergyRisk);
});

it('applies facility bonuses for Unity Cup', function () use (&$service, &$character): void {
    $character->scenario_type = 'unity_cup';
    $character->facility_levels = [
        'speed' => 5,
        'stamina' => 3,
    ];

    $prediction = $service->calculateTrainingPrediction(
        $character,
        'speed'
    );

    // Level 5 facility should provide 100% bonus
    expect($prediction['breakdown']['facility_bonus'])->toBe(1.0);
});

it('calculates batch predictions for multiple training types', function () use (&$service, &$character): void {
    $predictions = $service->calculateBatchPredictions(
        $character,
        ['speed', 'stamina', 'power']
    );

    expect($predictions)->toHaveCount(3);
    expect($predictions)->toHaveKeys(['speed', 'stamina', 'power']);
});

it('recommends training based on character goals', function () use (&$service, &$character): void {
    $character->goals = [
        'target_stats' => [
            'speed' => 800,
            'stamina' => 600,
        ],
    ];

    $recommendation = $service->getRecommendedTraining($character);

    expect($recommendation)->toHaveKeys(['recommended_training', 'reason', 'prediction', 'alternatives']);
    expect($recommendation['recommended_training'])->toBeIn(['speed', 'stamina', 'power', 'guts', 'wit']);
});

it('provides reasoning for recommendations', function () use (&$service, &$character): void {
    $character->goals = [
        'target_stats' => [
            'speed' => 800,
        ],
    ];

    $recommendation = $service->getRecommendedTraining($character);

    expect($recommendation['reason'])->toBeString();
    expect(strlen($recommendation['reason']))->toBeGreaterThan(0);
});

it('calculates support card bonuses', function () use (&$service, &$character): void {
    $supportCards = [
        [
            'card_type' => 'speed',
            'limit_break_level' => 4,
        ],
        [
            'card_type' => 'speed',
            'limit_break_level' => 2,
        ],
    ];

    $prediction = $service->calculateTrainingPrediction(
        $character,
        'speed',
        ['support_cards' => $supportCards]
    );

    // Should have bonus from matching cards
    expect($prediction['breakdown']['support_card_bonus'])->toBeGreaterThan(0);
});

it('calculates friendship training multiplier', function () use (&$service, &$character): void {
    $prediction = $service->calculateTrainingPrediction(
        $character,
        'speed',
        ['participants' => 3]
    );

    // 3 participants should give 3% bonus
    expect($prediction['breakdown']['friendship_multiplier'])->toBe(0.03);
});
