<?php

declare(strict_types=1);

/**
 * @property \App\Services\TrainingCalculationService $service
 * @property \App\Services\MCP\MCPClientService&\Mockery\MockInterface $mcpClient
 * @property \App\Services\MCP\TrainingOptimizationAgent&\Mockery\MockInterface $trainingAgent
 */

use App\Models\Character;
use App\Services\MCP\MCPClientService;
use App\Services\MCP\TrainingOptimizationAgent;
use App\Services\TrainingCalculationService;

beforeEach(function () {
    /** @var MCPClientService&Mockery\MockInterface $mcpClient */
    $mcpClient = Mockery::mock(MCPClientService::class);
    $mcpClient->shouldReceive('isServerEnabled')->andReturn(false);
    $this->mcpClient = $mcpClient;

    /** @var TrainingOptimizationAgent&Mockery\MockInterface $trainingAgent */
    $trainingAgent = Mockery::mock(TrainingOptimizationAgent::class);
    $this->trainingAgent = $trainingAgent;

    $this->service = new TrainingCalculationService($this->mcpClient, $this->trainingAgent);
});

describe('Base Stat Gain Calculations', function () {
    it('calculates speed training gains correctly', function () {
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
            'growth_rates' => ['speed' => 0, 'power' => 0],
            'facility_levels' => ['speed' => 1],
        ]);

        $result = $this->service->calculateTrainingPrediction($character, 'speed');

        expect($result)->toHaveKeys(['stat_gains', 'energy_cost', 'failure_risk', 'total_bonus', 'breakdown'])
            ->and($result['stat_gains']['speed'])->toBeGreaterThan(0)
            ->and($result['energy_cost'])->toBeGreaterThan(0)
            ->and($result['failure_risk'])->toBeLessThan(0.5);
    });

    it('calculates stamina training gains correctly', function () {
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
        ]);

        $result = $this->service->calculateTrainingPrediction($character, 'stamina');

        expect($result['stat_gains']['stamina'])->toBeGreaterThan(0)
            ->and($result['stat_gains'])->toHaveKey('power');
    });

    it('calculates power training gains correctly', function () {
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
        ]);

        $result = $this->service->calculateTrainingPrediction($character, 'power');

        expect($result['stat_gains']['power'])->toBeGreaterThan(0)
            ->and($result['stat_gains'])->toHaveKey('guts');
    });

    it('calculates guts training gains correctly', function () {
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
        ]);

        $result = $this->service->calculateTrainingPrediction($character, 'guts');

        expect($result['stat_gains']['guts'])->toBeGreaterThan(0)
            ->and($result['stat_gains'])->toHaveKey('wit');
    });

    it('calculates wit training gains correctly', function () {
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
        ]);

        $result = $this->service->calculateTrainingPrediction($character, 'wit');

        expect($result['stat_gains']['wit'])->toBeGreaterThan(0)
            ->and($result['stat_gains'])->toHaveKey('speed');
    });
});

describe('Energy Cost Calculations', function () {
    it('calculates base energy cost for training', function () {
        $character = Character::factory()->create([
            'energy_level' => 100,
            'mood_status' => 'normal',
        ]);

        $result = $this->service->calculateTrainingPrediction($character, 'speed');

        expect($result['energy_cost'])->toBeGreaterThanOrEqual(15)
            ->and($result['energy_cost'])->toBeLessThanOrEqual(30);
    });

    it('increases failure risk with low energy', function () {
        $character = Character::factory()->create([
            'energy_level' => 20,
            'mood_status' => 'normal',
        ]);

        $result = $this->service->calculateTrainingPrediction($character, 'speed');

        expect($result['failure_risk'])->toBeGreaterThan(0.1);
    });
});

describe('Mood Effects', function () {
    it('applies mood bonus for good mood', function () {
        $character = Character::factory()->create([
            'energy_level' => 100,
            'mood_status' => 'good',
        ]);

        $result = $this->service->calculateTrainingPrediction($character, 'speed');

        expect($result['total_bonus'])->toBeGreaterThanOrEqual(0);
    });

    it('applies mood penalty for bad mood', function () {
        $character = Character::factory()->create([
            'energy_level' => 100,
            'mood_status' => 'bad',
        ]);

        $result = $this->service->calculateTrainingPrediction($character, 'speed');

        expect($result)->toHaveKey('breakdown');
    });
});

describe('Growth Rate Bonuses', function () {
    it('applies growth rate bonus to stat gains', function () {
        $characterWithBonus = Character::factory()->create([
            'energy_level' => 100,
            'mood_status' => 'normal',
            'growth_rates' => ['speed' => 20, 'power' => 10],
        ]);

        $characterWithoutBonus = Character::factory()->create([
            'energy_level' => 100,
            'mood_status' => 'normal',
            'growth_rates' => ['speed' => 0, 'power' => 0],
        ]);

        $resultWithBonus = $this->service->calculateTrainingPrediction($characterWithBonus, 'speed');
        $resultWithoutBonus = $this->service->calculateTrainingPrediction($characterWithoutBonus, 'speed');

        expect($resultWithBonus['stat_gains']['speed'])->toBeGreaterThanOrEqual($resultWithoutBonus['stat_gains']['speed']);
    });
});

describe('Batch Predictions', function () {
    it('calculates predictions for all training types', function () {
        $character = Character::factory()->create([
            'energy_level' => 100,
            'mood_status' => 'normal',
        ]);

        $trainingTypes = ['speed', 'stamina', 'power', 'guts', 'wit'];
        $results = $this->service->calculateBatchPredictions($character, $trainingTypes);

        expect($results)->toHaveKeys(['speed', 'stamina', 'power', 'guts', 'wit'])
            ->and($results['speed'])->toHaveKey('stat_gains')
            ->and($results['stamina'])->toHaveKey('stat_gains')
            ->and($results['power'])->toHaveKey('stat_gains')
            ->and($results['guts'])->toHaveKey('stat_gains')
            ->and($results['wit'])->toHaveKey('stat_gains');
    });
});

describe('Training Recommendations', function () {
    it('returns recommended training with reason', function () {
        $character = Character::factory()->create([
            'energy_level' => 100,
            'mood_status' => 'normal',
            'current_stats' => [
                'speed' => 100,
                'stamina' => 50,
                'power' => 100,
                'guts' => 100,
                'wit' => 100,
            ],
        ]);

        $result = $this->service->getRecommendedTraining($character);

        expect($result)->toHaveKeys(['recommended_training', 'reason', 'alternatives'])
            ->and($result['recommended_training'])->toBeIn(['speed', 'stamina', 'power', 'guts', 'wit', 'rest']);
    });
});
