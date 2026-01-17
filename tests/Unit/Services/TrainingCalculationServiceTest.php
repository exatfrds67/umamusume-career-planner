<?php

use App\Models\Character;
use App\Services\MCP\MCPClientService;
use App\Services\MCP\TrainingOptimizationAgent;
use App\Services\TrainingCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->mcpClient = Mockery::mock(MCPClientService::class);
    $this->trainingAgent = Mockery::mock(TrainingOptimizationAgent::class);
    $this->service = new TrainingCalculationService($this->mcpClient, $this->trainingAgent);

    $this->character = Character::factory()->make([
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
});

it('calculates base stat gains correctly', function () {
    $prediction = $this->service->calculateTrainingPrediction(
        $this->character,
        'speed'
    );

    expect($prediction)->toHaveKeys(['stat_gains', 'energy_cost', 'failure_risk', 'total_bonus', 'breakdown']);
    expect($prediction['stat_gains'])->toHaveKey('speed');
    expect($prediction['stat_gains']['speed'])->toBeGreaterThan(0);
});

it('applies growth rate bonuses', function () {
    $prediction = $this->service->calculateTrainingPrediction(
        $this->character,
        'speed'
    );

    // Character has 10% growth rate for speed
    expect($prediction['breakdown']['growth_rate_bonus'])->toBe(0.10);
});

it('calculates energy cost based on mood', function () {
    $this->character->mood_status = 'great';
    $prediction = $this->service->calculateTrainingPrediction(
        $this->character,
        'speed'
    );

    // Great mood should reduce energy cost
    expect($prediction['energy_cost'])->toBeLessThan(20);

    $this->character->mood_status = 'awful';
    $prediction = $this->service->calculateTrainingPrediction(
        $this->character,
        'speed'
    );

    // Awful mood should increase energy cost
    expect($prediction['energy_cost'])->toBeGreaterThan(20);
});

it('calculates failure risk based on energy level', function () {
    $this->character->energy_level = 90;
    $prediction = $this->service->calculateTrainingPrediction(
        $this->character,
        'speed'
    );

    $highEnergyRisk = $prediction['failure_risk'];

    $this->character->energy_level = 20;
    $prediction = $this->service->calculateTrainingPrediction(
        $this->character,
        'speed'
    );

    $lowEnergyRisk = $prediction['failure_risk'];

    expect($lowEnergyRisk)->toBeGreaterThan($highEnergyRisk);
});

it('applies facility bonuses for Unity Cup', function () {
    $this->character->scenario_type = 'unity_cup';
    $this->character->facility_levels = [
        'speed' => 5,
        'stamina' => 3,
    ];

    $prediction = $this->service->calculateTrainingPrediction(
        $this->character,
        'speed'
    );

    // Level 5 facility should provide 100% bonus
    expect($prediction['breakdown']['facility_bonus'])->toBe(1.0);
});

it('calculates batch predictions for multiple training types', function () {
    $predictions = $this->service->calculateBatchPredictions(
        $this->character,
        ['speed', 'stamina', 'power']
    );

    expect($predictions)->toHaveCount(3);
    expect($predictions)->toHaveKeys(['speed', 'stamina', 'power']);
});

it('recommends training based on character goals', function () {
    $this->character->goals = [
        'target_stats' => [
            'speed' => 800,
            'stamina' => 600,
        ],
    ];

    $recommendation = $this->service->getRecommendedTraining($this->character);

    expect($recommendation)->toHaveKeys(['recommended_training', 'reason', 'prediction', 'alternatives']);
    expect($recommendation['recommended_training'])->toBeIn(['speed', 'stamina', 'power', 'guts', 'wit']);
});

it('provides reasoning for recommendations', function () {
    $this->character->goals = [
        'target_stats' => [
            'speed' => 800,
        ],
    ];

    $recommendation = $this->service->getRecommendedTraining($this->character);

    expect($recommendation['reason'])->toBeString();
    expect($recommendation['reason'])->not->toBeEmpty();
});

it('calculates support card bonuses', function () {
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

    $prediction = $this->service->calculateTrainingPrediction(
        $this->character,
        'speed',
        ['support_cards' => $supportCards]
    );

    // Should have bonus from matching cards
    expect($prediction['breakdown']['support_card_bonus'])->toBeGreaterThan(0);
});

it('calculates friendship training multiplier', function () {
    $prediction = $this->service->calculateTrainingPrediction(
        $this->character,
        'speed',
        ['participants' => 3]
    );

    // 3 participants should give 3% bonus
    expect($prediction['breakdown']['friendship_multiplier'])->toBe(0.03);
});
