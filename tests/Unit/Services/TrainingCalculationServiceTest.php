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
            'scenario_type' => 'ura_finale',
        ]);

        $result = $this->service->calculateTrainingPrediction($character, 'speed');

        expect($result)->toHaveKeys(['stat_gains', 'energy_cost', 'failure_risk', 'total_bonus', 'breakdown', 'scenario_specific'])
            ->and($result['stat_gains']['speed'])->toBeGreaterThan(0)
            ->and($result['stat_gains']['power'])->toBeGreaterThanOrEqual(0) // Secondary stat
            ->and($result['energy_cost'])->toBeGreaterThan(0)
            ->and($result['failure_risk'])->toBeLessThan(0.5)
            ->and($result['breakdown'])->toHaveKeys([
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
            'growth_rates' => ['stamina' => 0, 'power' => 0],
            'scenario_type' => 'ura_finale',
        ]);

        $result = $this->service->calculateTrainingPrediction($character, 'stamina');

        expect($result['stat_gains']['stamina'])->toBeGreaterThan(0)
            ->and($result['stat_gains'])->toHaveKey('power')
            ->and($result['breakdown']['growth_rate_multiplier'])->toBe(1.0); // No growth bonus
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
            'growth_rates' => ['power' => 0, 'guts' => 0],
            'scenario_type' => 'ura_finale',
        ]);

        $result = $this->service->calculateTrainingPrediction($character, 'power');

        expect($result['stat_gains']['power'])->toBeGreaterThan(0)
            ->and($result['stat_gains'])->toHaveKey('guts')
            ->and($result['breakdown']['mood_multiplier'])->toBeGreaterThan(0.9); // Normal mood ~1.0
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
            'growth_rates' => ['guts' => 0, 'wit' => 0],
            'scenario_type' => 'ura_finale',
        ]);

        $result = $this->service->calculateTrainingPrediction($character, 'guts');

        expect($result['stat_gains']['guts'])->toBeGreaterThan(0)
            ->and($result['stat_gains'])->toHaveKey('wit')
            ->and($result['breakdown']['training_effect'])->toBeGreaterThanOrEqual(0.0);
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
            'growth_rates' => ['wit' => 0, 'speed' => 0],
            'scenario_type' => 'ura_finale',
        ]);

        $result = $this->service->calculateTrainingPrediction($character, 'wit');

        expect($result['stat_gains']['wit'])->toBeGreaterThan(0)
            ->and($result['stat_gains'])->toHaveKey('speed')
            ->and($result['breakdown']['friendship_multiplier'])->toBeGreaterThanOrEqual(1.0);
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
            'current_stats' => ['speed' => 100],
            'growth_rates' => ['speed' => 0],
            'scenario_type' => 'ura_finale',
        ]);

        $result = $this->service->calculateTrainingPrediction($character, 'speed');

        // Good mood = +2% (1 level above neutral)
        expect($result['breakdown']['mood_multiplier'])->toBe(1.02)
            ->and($result['total_bonus'])->toBeGreaterThanOrEqual(0);
    });

    it('applies mood penalty for bad mood', function () {
        $character = Character::factory()->create([
            'energy_level' => 100,
            'mood_status' => 'bad',
            'current_stats' => ['speed' => 100],
            'growth_rates' => ['speed' => 0],
            'scenario_type' => 'ura_finale',
        ]);

        $result = $this->service->calculateTrainingPrediction($character, 'speed');

        // Bad mood = -2% (1 level below neutral)
        expect($result['breakdown']['mood_multiplier'])->toBe(0.98)
            ->and($result)->toHaveKey('breakdown');
    });

    it('applies great mood bonus correctly', function () {
        $character = Character::factory()->create([
            'energy_level' => 100,
            'mood_status' => 'great',
            'current_stats' => ['speed' => 100],
            'growth_rates' => ['speed' => 0],
            'scenario_type' => 'ura_finale',
        ]);

        $result = $this->service->calculateTrainingPrediction($character, 'speed');

        // Great mood = +4% (2 levels above neutral)
        expect($result['breakdown']['mood_multiplier'])->toBe(1.04);
    });

    it('applies awful mood penalty correctly', function () {
        $character = Character::factory()->create([
            'energy_level' => 100,
            'mood_status' => 'awful',
            'current_stats' => ['speed' => 100],
            'growth_rates' => ['speed' => 0],
            'scenario_type' => 'ura_finale',
        ]);

        $result = $this->service->calculateTrainingPrediction($character, 'speed');

        // Awful mood = -4% (2 levels below neutral)
        expect($result['breakdown']['mood_multiplier'])->toBe(0.96);
    });
});

describe('Growth Rate Multipliers', function () {
    it('applies growth rate multiplier to stat gains', function () {
        $characterWithBonus = Character::factory()->create([
            'energy_level' => 100,
            'mood_status' => 'normal',
            'growth_rates' => ['speed' => 20, 'power' => 10],
            'current_stats' => ['speed' => 100, 'power' => 100],
            'scenario_type' => 'ura_finale',
        ]);

        $characterWithoutBonus = Character::factory()->create([
            'energy_level' => 100,
            'mood_status' => 'normal',
            'growth_rates' => ['speed' => 0, 'power' => 0],
            'current_stats' => ['speed' => 100, 'power' => 100],
            'scenario_type' => 'ura_finale',
        ]);

        $resultWithBonus = $this->service->calculateTrainingPrediction($characterWithBonus, 'speed');
        $resultWithoutBonus = $this->service->calculateTrainingPrediction($characterWithoutBonus, 'speed');

        // With 20% growth rate, gains should be 1.2x higher
        expect($resultWithBonus['stat_gains']['speed'])->toBeGreaterThan($resultWithoutBonus['stat_gains']['speed'])
            ->and($resultWithBonus['breakdown']['growth_rate_multiplier'])->toBe(1.2)
            ->and($resultWithoutBonus['breakdown']['growth_rate_multiplier'])->toBe(1.0);
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
            'scenario_type' => 'ura_finale',
        ]);

        $result = $this->service->getRecommendedTraining($character);

        expect($result)->toHaveKeys(['recommended_training', 'reason', 'alternatives'])
            ->and($result['recommended_training'])->toBeIn(['speed', 'stamina', 'power', 'guts', 'wit', 'rest']);
    });
});

describe('Verified Formula Components (Phase 4)', function () {
    it('applies per-training cap of +100 for stats below 1200', function () {
        $character = Character::factory()->create([
            'current_stats' => ['speed' => 500],
            'energy_level' => 100,
            'mood_status' => 'great',
            'growth_rates' => ['speed' => 100], // 2x multiplier
            'scenario_type' => 'ura_finale',
        ]);

        $result = $this->service->calculateTrainingPrediction($character, 'speed', [
            'support_cards' => [],
            'participants' => 3,
        ]);

        // Even with high multipliers, cap should limit to +100
        expect($result['stat_gains']['speed'])->toBeLessThanOrEqual(100);
    });

    it('applies per-training cap of +50 for stats above 1200', function () {
        $character = Character::factory()->create([
            'current_stats' => ['speed' => 1250],
            'energy_level' => 100,
            'mood_status' => 'great',
            'growth_rates' => ['speed' => 100], // 2x multiplier
            'scenario_type' => 'ura_finale',
        ]);

        $result = $this->service->calculateTrainingPrediction($character, 'speed', [
            'support_cards' => [],
            'participants' => 3,
        ]);

        // Above 1200, cap should limit to +50
        expect($result['stat_gains']['speed'])->toBeLessThanOrEqual(50);
    });

    it('calculates support card presence multiplier correctly', function () {
        $character = Character::factory()->create([
            'current_stats' => ['speed' => 100],
            'energy_level' => 100,
            'mood_status' => 'normal',
            'growth_rates' => ['speed' => 0],
            'scenario_type' => 'ura_finale',
        ]);

        // Test with 3 support cards
        $result = $this->service->calculateTrainingPrediction($character, 'speed', [
            'support_cards' => [
                ['card_type' => 'speed', 'limit_break_level' => 0],
                ['card_type' => 'speed', 'limit_break_level' => 0],
                ['card_type' => 'speed', 'limit_break_level' => 0],
            ],
        ]);

        // 3 cards = 1 + (0.05 × 3) = 1.15
        expect($result['breakdown']['support_card_presence_multiplier'])->toBe(1.15);
    });

    it('caps support card presence multiplier at 6 cards', function () {
        $character = Character::factory()->create([
            'current_stats' => ['speed' => 100],
            'energy_level' => 100,
            'mood_status' => 'normal',
            'growth_rates' => ['speed' => 0],
            'scenario_type' => 'ura_finale',
        ]);

        // Test with 8 support cards (should cap at 6)
        $supportCards = array_fill(0, 8, ['card_type' => 'speed', 'limit_break_level' => 0]);
        $result = $this->service->calculateTrainingPrediction($character, 'speed', [
            'support_cards' => $supportCards,
        ]);

        // Max 6 cards = 1 + (0.05 × 6) = 1.30
        expect($result['breakdown']['support_card_presence_multiplier'])->toBe(1.30);
    });

    it('applies flat 1.2x friendship multiplier when 3 or more cards have bond >= 80', function () {
        $character = Character::factory()->create([
            'current_stats' => ['speed' => 100],
            'energy_level' => 100,
            'mood_status' => 'normal',
            'growth_rates' => ['speed' => 0],
            'scenario_type' => 'ura_finale',
        ]);

        // 3 rainbow cards (friendship_level >= 80) — threshold met
        $result = $this->service->calculateTrainingPrediction($character, 'speed', [
            'support_cards' => [
                ['card_type' => 'speed', 'friendship_level' => 80, 'limit_break_level' => 0],
                ['card_type' => 'speed', 'friendship_level' => 85, 'limit_break_level' => 0],
                ['card_type' => 'speed', 'friendship_level' => 90, 'limit_break_level' => 0],
            ],
            'participants' => 0,
        ]);

        // Flat 1.2x multiplier when 3+ cards at bond >= 80
        expect($result['breakdown']['friendship_multiplier'])->toBe(1.2);
    });

    it('applies no friendship bonus when fewer than 3 rainbow cards', function () {
        $character = Character::factory()->create([
            'current_stats' => ['speed' => 100],
            'energy_level' => 100,
            'mood_status' => 'normal',
            'growth_rates' => ['speed' => 0],
            'scenario_type' => 'ura_finale',
        ]);

        // Only 2 rainbow cards — threshold not met
        $result = $this->service->calculateTrainingPrediction($character, 'speed', [
            'support_cards' => [
                ['card_type' => 'speed', 'friendship_level' => 80, 'limit_break_level' => 0],
                ['card_type' => 'speed', 'friendship_level' => 85, 'limit_break_level' => 0],
            ],
            'participants' => 0,
        ]);

        expect($result['breakdown']['friendship_multiplier'])->toBe(1.0);
    });

    it('applies training effect from support card traits with rarity and limit break bonuses', function () {
        $character = Character::factory()->create([
            'current_stats' => ['speed' => 100],
            'energy_level' => 100,
            'mood_status' => 'normal',
            'growth_rates' => ['speed' => 0],
            'scenario_type' => 'ura_finale',
        ]);

        // SSR card (10%) at LB2 (1.2x) + SR card (7%) at LB0 (1.0x) = 0.12 + 0.07 = 0.19
        $result = $this->service->calculateTrainingPrediction($character, 'speed', [
            'support_cards' => [
                ['card_type' => 'speed', 'rarity' => 'SSR', 'limit_break_level' => 2],
                ['card_type' => 'speed', 'rarity' => 'SR', 'limit_break_level' => 0],
            ],
        ]);

        expect($result['breakdown']['training_effect'])->toBeGreaterThan(0.10);
    });

    it('applies facility bonus for Unity Cup scenario', function () {
        $character = Character::factory()->create([
            'current_stats' => ['speed' => 100],
            'energy_level' => 100,
            'mood_status' => 'normal',
            'growth_rates' => ['speed' => 0],
            'scenario_type' => 'unity_cup',
            'facility_levels' => ['speed' => 5], // Max level
        ]);

        $result = $this->service->calculateTrainingPrediction($character, 'speed');

        // Level 5 = 100% bonus (1.0)
        expect($result['breakdown']['facility_bonus'])->toBe(1.0);
    });

    it('does not apply facility bonus for URA Finale scenario', function () {
        $character = Character::factory()->create([
            'current_stats' => ['speed' => 100],
            'energy_level' => 100,
            'mood_status' => 'normal',
            'growth_rates' => ['speed' => 0],
            'scenario_type' => 'ura_finale',
            'facility_levels' => ['speed' => 5],
        ]);

        $result = $this->service->calculateTrainingPrediction($character, 'speed');

        // URA Finale doesn't use facility bonuses
        expect($result['breakdown']['facility_bonus'])->toBe(0.0);
    });

    it('calculates total multiplier as product of all components', function () {
        $character = Character::factory()->create([
            'current_stats' => ['speed' => 100],
            'energy_level' => 100,
            'mood_status' => 'good', // 1.02
            'growth_rates' => ['speed' => 20], // 1.2
            'scenario_type' => 'ura_finale',
        ]);

        $result = $this->service->calculateTrainingPrediction($character, 'speed', [
            'support_cards' => [
                ['card_type' => 'speed', 'limit_break_level' => 0],
            ],
        ]);

        // Total = GrowthRate × Mood × (1 + TrainingEffect) × SupportPresence × Friendship
        // Total = 1.2 × 1.02 × (1 + effect) × 1.05 × 1.0
        expect($result['breakdown']['total_multiplier'])->toBeGreaterThan(1.2);
    });
});
