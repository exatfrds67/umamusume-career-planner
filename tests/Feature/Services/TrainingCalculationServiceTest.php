<?php

use App\Models\Aptitude;
use App\Models\Character;
use App\Models\CharacterSupportCard;
use App\Models\SupportCardDefinition;
use App\Models\User;
use App\Services\MCP\MCPClientService;
use App\Services\TrainingCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\mock;

uses(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create();
    $character = Character::factory()->create([
        'user_id' => $user->id,
        'scenario_type' => 'ura_finale',
        'current_stats' => [
            'speed' => 500,
            'stamina' => 400,
            'power' => 300,
            'guts' => 200,
            'wit' => 250,
        ],
        'energy_level' => 80,
        'mood_status' => 'normal',
        'growth_rates' => [
            'speed' => 20,
            'stamina' => 10,
            'power' => 10,
            'guts' => 0,
            'wit' => 10,
        ],
        'goals' => [
            'target_stats' => [
                'speed' => 800,
                'stamina' => 700,
                'power' => 600,
                'guts' => 400,
                'wit' => 500,
            ],
        ],
    ]);

    $mcpClient = mock(MCPClientService::class);
    $service = new TrainingCalculationService($mcpClient);

    // Store in test context
    test()->user = $user;
    test()->character = $character;
    test()->mcpClient = $mcpClient;
    test()->service = $service;
});

it('calculates base training prediction correctly', function () {
    $prediction = test()->service->calculateTrainingPrediction(
        test()->character,
        'speed'
    );

    expect($prediction)->toHaveKeys([
        'stat_gains',
        'energy_cost',
        'failure_risk',
        'total_bonus',
        'breakdown',
    ]);

    expect($prediction['stat_gains'])->toBeArray();
    expect($prediction['energy_cost'])->toBeInt();
    expect($prediction['failure_risk'])->toBeFloat();
    expect($prediction['total_bonus'])->toBeFloat();
});

it('applies growth rate bonus correctly', function () {
    // Character has 20% growth rate for speed
    $prediction = test()->service->calculateTrainingPrediction(
        test()->character,
        'speed'
    );

    // Base gain is 10, with 20% growth rate should be 12
    expect($prediction['stat_gains']['speed'])->toBeGreaterThanOrEqual(12);
    expect($prediction['breakdown']['growth_rate_bonus'])->toBe(0.2);
});

it('calculates friendship training multiplier correctly', function () {
    // Test with 2 participants
    $prediction = test()->service->calculateTrainingPrediction(
        test()->character,
        'speed',
        ['participants' => 2]
    );

    expect($prediction['breakdown']['friendship_multiplier'])->toBe(0.02);

    // Test with 3 participants
    $prediction = test()->service->calculateTrainingPrediction(
        test()->character,
        'speed',
        ['participants' => 3]
    );

    expect($prediction['breakdown']['friendship_multiplier'])->toBe(0.03);

    // Test with no participants
    $prediction = test()->service->calculateTrainingPrediction(
        test()->character,
        'speed',
        ['participants' => 0]
    );

    expect($prediction['breakdown']['friendship_multiplier'])->toBe(0.0);
});

it('calculates facility level bonus for Unity Cup', function () {
    // Update character to Unity Cup scenario
    test()->character->update([
        'scenario_type' => 'unity_cup',
        'facility_levels' => [
            'speed' => 3,
            'stamina' => 2,
            'power' => 4,
        ],
    ]);

    // Test speed training with level 3 facility (50% bonus)
    $prediction = test()->service->calculateTrainingPrediction(
        test()->character,
        'speed'
    );

    expect($prediction['breakdown']['facility_bonus'])->toBe(0.5);

    // Test stamina training with level 2 facility (25% bonus)
    $prediction = test()->service->calculateTrainingPrediction(
        test()->character,
        'stamina'
    );

    expect($prediction['breakdown']['facility_bonus'])->toBe(0.25);
});

it('does not apply facility bonus for URA Finale', function () {
    // Character is in URA Finale scenario
    $prediction = test()->service->calculateTrainingPrediction(
        test()->character,
        'speed'
    );

    expect($prediction['breakdown']['facility_bonus'])->toBe(0.0);
});

it('calculates energy cost based on mood', function () {
    // Test with great mood (10% reduction)
    test()->character->update(['mood_status' => 'great']);
    $prediction = test()->service->calculateTrainingPrediction(
        test()->character,
        'speed'
    );
    expect($prediction['energy_cost'])->toBe(18); // 20 * 0.9

    // Test with normal mood
    test()->character->update(['mood_status' => 'normal']);
    $prediction = test()->service->calculateTrainingPrediction(
        test()->character,
        'speed'
    );
    expect($prediction['energy_cost'])->toBe(20);

    // Test with awful mood (10% increase)
    test()->character->update(['mood_status' => 'awful']);
    $prediction = test()->service->calculateTrainingPrediction(
        test()->character,
        'speed'
    );
    expect($prediction['energy_cost'])->toBe(22); // 20 * 1.1
});

it('calculates failure risk based on energy level', function () {
    // High energy (80) - 20 cost = 60 remaining (low risk)
    test()->character->update(['energy_level' => 80]);
    $prediction = test()->service->calculateTrainingPrediction(
        test()->character,
        'speed'
    );
    expect($prediction['failure_risk'])->toBe(0.05);

    // Medium energy (50) - 20 cost = 30 remaining (medium risk)
    test()->character->update(['energy_level' => 50]);
    $prediction = test()->service->calculateTrainingPrediction(
        test()->character,
        'speed'
    );
    expect($prediction['failure_risk'])->toBe(0.15);

    // Low energy (25) - 20 cost = 5 remaining (high risk)
    test()->character->update(['energy_level' => 25]);
    $prediction = test()->service->calculateTrainingPrediction(
        test()->character,
        'speed'
    );
    expect($prediction['failure_risk'])->toBe(0.50);
});

it('calculates batch predictions for multiple training types', function () {
    $trainingTypes = ['speed', 'stamina', 'power'];
    $predictions = test()->service->calculateBatchPredictions(
        test()->character,
        $trainingTypes
    );

    expect($predictions)->toHaveKeys($trainingTypes);

    foreach ($trainingTypes as $type) {
        expect($predictions[$type])->toHaveKeys([
            'stat_gains',
            'energy_cost',
            'failure_risk',
            'total_bonus',
            'breakdown',
        ]);
    }
});

it('recommends training based on character goals', function () {
    $recommendation = test()->service->getRecommendedTraining(test()->character);

    expect($recommendation)->toHaveKeys([
        'recommended_training',
        'reason',
        'prediction',
        'alternatives',
    ]);

    expect($recommendation['recommended_training'])->toBeString();
    expect($recommendation['reason'])->toBeString();
    expect($recommendation['prediction'])->toBeArray();
    expect($recommendation['alternatives'])->toBeArray();
});

it('prioritizes training for stats with largest gaps', function () {
    // Speed has largest gap (800 - 500 = 300)
    $recommendation = test()->service->getRecommendedTraining(test()->character);

    // Should recommend speed training since it has the largest gap
    // and character has 20% growth rate for speed
    expect($recommendation['recommended_training'])->toBe('speed');
});

it('generates meaningful recommendation reasons', function () {
    $recommendation = test()->service->getRecommendedTraining(test()->character);

    expect($recommendation['reason'])->toContain('priority');
});

it('handles MCP integration when enabled', function () {
    test()->mcpClient->shouldReceive('isEnabled')->andReturn(true);
    test()->mcpClient->shouldReceive('isServerEnabled')
        ->with('strands-agents')
        ->andReturn(true);

    $result = test()->service->getMCPOptimization(test()->character);

    expect($result)->toBeArray();
    expect($result)->toHaveKey('status');
});

it('skips MCP integration when disabled', function () {
    test()->mcpClient->shouldReceive('isEnabled')->andReturn(false);

    $result = test()->service->getMCPOptimization(test()->character);

    expect($result)->toBeNull();
});

it('applies support card bonuses correctly', function () {
    // Create a support card definition
    $speedCardDef = SupportCardDefinition::factory()->create([
        'card_type' => 'speed',
        'speed_bonus' => 20,
    ]);

    // Create character support card instance
    CharacterSupportCard::factory()->create([
        'character_id' => test()->character->id,
        'support_card_id' => $speedCardDef->id,
        'limit_break_level' => 2,
        'position_slot' => 1,
    ]);

    test()->character->refresh();

    $prediction = test()->service->calculateTrainingPrediction(
        test()->character,
        'speed'
    );

    // Should have support card bonus (10% base + 4% from limit break)
    expect($prediction['breakdown']['support_card_bonus'])->toBeGreaterThan(0.1);
});

it('calculates rest energy recovery correctly', function () {
    $prediction = test()->service->calculateTrainingPrediction(
        test()->character,
        'rest'
    );

    // Rest should have negative energy cost (recovery)
    expect($prediction['energy_cost'])->toBeLessThan(0);
});

it('combines all bonuses correctly', function () {
    // Set up character with multiple bonuses
    test()->character->update([
        'scenario_type' => 'unity_cup',
        'facility_levels' => ['speed' => 5], // 100% bonus
        'growth_rates' => ['speed' => 30], // 30% bonus
    ]);

    $prediction = test()->service->calculateTrainingPrediction(
        test()->character,
        'speed',
        ['participants' => 3] // 3% friendship bonus
    );

    // Total multiplier should be 1.0 + 1.0 + 0.3 + 0.03 = 2.33
    expect($prediction['total_bonus'])->toBeGreaterThan(1.3);
    expect($prediction['breakdown']['total_multiplier'])->toBeGreaterThan(2.3);
});

// Scenario-Specific Mechanics Tests

it('includes scenario_specific data in predictions', function () {
    $prediction = test()->service->calculateTrainingPrediction(
        test()->character,
        'speed'
    );

    expect($prediction)->toHaveKey('scenario_specific');
    expect($prediction['scenario_specific'])->toBeArray();
    expect($prediction['scenario_specific'])->toHaveKey('scenario');
});

it('calculates URA Finale mechanics correctly', function () {
    test()->character->update(['scenario_type' => 'ura_finale']);

    $prediction = test()->service->calculateTrainingPrediction(
        test()->character,
        'speed'
    );

    $scenarioData = $prediction['scenario_specific'];

    expect($scenarioData['scenario'])->toBe('ura_finale');
    expect($scenarioData['optimization_focus'])->toBe('individual');
    expect($scenarioData['mechanics'])->toHaveKeys([
        'traditional_training',
        'individual_optimization',
        'race_focus',
        'stat_priority',
    ]);
    expect($scenarioData['mechanics']['traditional_training'])->toBeTrue();
    expect($scenarioData['mechanics']['individual_optimization'])->toBeTrue();
});

it('calculates Unity Cup mechanics correctly', function () {
    test()->character->update([
        'scenario_type' => 'unity_cup',
        'facility_levels' => ['speed' => 3],
    ]);

    $prediction = test()->service->calculateTrainingPrediction(
        test()->character,
        'speed',
        [
            'spirit_burst_gauge' => 2,
            'teammates_present' => ['teammate1', 'teammate2'],
        ]
    );

    $scenarioData = $prediction['scenario_specific'];

    expect($scenarioData['scenario'])->toBe('unity_cup');
    expect($scenarioData['optimization_focus'])->toBe('team');
    expect($scenarioData['mechanics'])->toHaveKeys([
        'spirit_burst',
        'team_interactions',
        'distance_team_performance',
        'facility_levels',
    ]);
});

it('calculates Spirit Burst gauge progression', function () {
    test()->character->update(['scenario_type' => 'unity_cup']);

    // Test gauge progression from 0 to 1
    $prediction = test()->service->calculateTrainingPrediction(
        test()->character,
        'speed',
        [
            'spirit_burst_gauge' => 0,
            'teammates_present' => ['teammate1'],
        ]
    );

    $spiritBurst = $prediction['scenario_specific']['mechanics']['spirit_burst'];

    expect($spiritBurst['current_gauge'])->toBe(0);
    expect($spiritBurst['gauge_after_training'])->toBe(1);
    expect($spiritBurst['will_contribute'])->toBeTrue();
    expect($spiritBurst['will_trigger'])->toBeFalse();
    expect($spiritBurst['sessions_until_trigger'])->toBe(3);
});

it('triggers Spirit Burst at gauge 4', function () {
    test()->character->update(['scenario_type' => 'unity_cup']);

    $prediction = test()->service->calculateTrainingPrediction(
        test()->character,
        'speed',
        [
            'spirit_burst_gauge' => 3,
            'teammates_present' => ['teammate1', 'teammate2'],
        ]
    );

    $spiritBurst = $prediction['scenario_specific']['mechanics']['spirit_burst'];

    expect($spiritBurst['current_gauge'])->toBe(3);
    expect($spiritBurst['gauge_after_training'])->toBe(4);
    expect($spiritBurst['will_trigger'])->toBeTrue();
    expect($spiritBurst['flame_icon_visible'])->toBeTrue();
    expect($spiritBurst['bonuses'])->toHaveKeys([
        'stat_bonus',
        'skill_hint_chance',
        'energy_recovery',
    ]);
    expect($spiritBurst['bonuses']['stat_bonus']['speed'])->toBe(50);
    expect($spiritBurst['bonuses']['skill_hint_chance'])->toBe(0.8);
});

it('does not contribute to Spirit Burst without teammates', function () {
    test()->character->update(['scenario_type' => 'unity_cup']);

    $prediction = test()->service->calculateTrainingPrediction(
        test()->character,
        'speed',
        [
            'spirit_burst_gauge' => 2,
            'teammates_present' => [],
        ]
    );

    $spiritBurst = $prediction['scenario_specific']['mechanics']['spirit_burst'];

    expect($spiritBurst['will_contribute'])->toBeFalse();
    expect($spiritBurst['gauge_after_training'])->toBe(2); // No change
});

it('calculates team member interactions correctly', function () {
    test()->character->update(['scenario_type' => 'unity_cup']);

    // Test with 2 teammates
    $prediction = test()->service->calculateTrainingPrediction(
        test()->character,
        'speed',
        [
            'teammates_present' => ['teammate1', 'teammate2'],
        ]
    );

    $teamInteractions = $prediction['scenario_specific']['mechanics']['team_interactions'];

    expect($teamInteractions['teammate_count'])->toBe(2);
    expect($teamInteractions['unity_bonus'])->toBe(2);
    expect($teamInteractions['coordination_level'])->toBe('good');
    expect($teamInteractions['team_synergy']['level'])->toBe('high');

    // Test with 3 teammates
    $prediction = test()->service->calculateTrainingPrediction(
        test()->character,
        'speed',
        [
            'teammates_present' => ['teammate1', 'teammate2', 'teammate3'],
        ]
    );

    $teamInteractions = $prediction['scenario_specific']['mechanics']['team_interactions'];

    expect($teamInteractions['teammate_count'])->toBe(3);
    expect($teamInteractions['unity_bonus'])->toBe(3);
    expect($teamInteractions['coordination_level'])->toBe('excellent');
});

it('calculates distance team performance tracking', function () {
    test()->character->update(['scenario_type' => 'unity_cup']);

    $prediction = test()->service->calculateTrainingPrediction(
        test()->character,
        'speed',
        [
            'team_stat_ranks' => [
                'sprint' => 'S',
                'mile' => 'A',
                'medium' => 'B',
                'long' => 'C',
                'dirt' => 'D',
            ],
        ]
    );

    $distanceTeam = $prediction['scenario_specific']['mechanics']['distance_team_performance'];

    expect($distanceTeam)->toHaveKeys([
        'distance_teams',
        'character_specialization',
        'team_stat_ranks',
        'facility_impacts',
        'team_race_schedule',
    ]);

    // Check facility level conversions
    expect($distanceTeam['facility_impacts']['sprint']['facility_level'])->toBe(5);
    expect($distanceTeam['facility_impacts']['sprint']['bonus_multiplier'])->toBe(1.0);
    expect($distanceTeam['facility_impacts']['mile']['facility_level'])->toBe(4);
    expect($distanceTeam['facility_impacts']['mile']['bonus_multiplier'])->toBe(0.75);
    expect($distanceTeam['facility_impacts']['dirt']['facility_level'])->toBe(1);
    expect($distanceTeam['facility_impacts']['dirt']['bonus_multiplier'])->toBe(0.0);
});

it('identifies character distance specialization', function () {
    test()->character->update([
        'scenario_type' => 'unity_cup',
    ]);

    // Create aptitude records for the character
    Aptitude::factory()->create([
        'character_id' => test()->character->id,
        'distance_type' => 'sprint',
        'grade' => 'A',
    ]);
    Aptitude::factory()->create([
        'character_id' => test()->character->id,
        'distance_type' => 'mile',
        'grade' => 'S',
    ]);
    Aptitude::factory()->create([
        'character_id' => test()->character->id,
        'distance_type' => 'medium',
        'grade' => 'B',
    ]);
    Aptitude::factory()->create([
        'character_id' => test()->character->id,
        'distance_type' => 'long',
        'grade' => 'C',
    ]);

    test()->character->refresh();

    $prediction = test()->service->calculateTrainingPrediction(
        test()->character,
        'speed'
    );

    $distanceTeam = $prediction['scenario_specific']['mechanics']['distance_team_performance'];

    // Should identify mile as best specialization (S grade)
    expect($distanceTeam['character_specialization'])->toBeString();
});

it('provides URA Finale race focus recommendations', function () {
    test()->character->update([
        'scenario_type' => 'ura_finale',
    ]);

    // Create aptitude records for the character
    Aptitude::factory()->create([
        'character_id' => test()->character->id,
        'distance_type' => 'sprint',
        'grade' => 'B',
    ]);
    Aptitude::factory()->create([
        'character_id' => test()->character->id,
        'distance_type' => 'mile',
        'grade' => 'A',
    ]);
    Aptitude::factory()->create([
        'character_id' => test()->character->id,
        'distance_type' => 'medium',
        'grade' => 'S',
    ]);
    Aptitude::factory()->create([
        'character_id' => test()->character->id,
        'distance_type' => 'long',
        'grade' => 'C',
    ]);
    Aptitude::factory()->create([
        'character_id' => test()->character->id,
        'surface_type' => 'turf',
        'grade' => 'A',
    ]);
    Aptitude::factory()->create([
        'character_id' => test()->character->id,
        'surface_type' => 'dirt',
        'grade' => 'B',
    ]);

    test()->character->refresh();

    $prediction = test()->service->calculateTrainingPrediction(
        test()->character,
        'speed'
    );

    $raceFocus = $prediction['scenario_specific']['mechanics']['race_focus'];

    expect($raceFocus)->toHaveKeys([
        'best_distance',
        'best_surface',
        'distance_aptitudes',
        'surface_aptitudes',
    ]);
    expect($raceFocus['best_distance'])->toBeString();
    expect($raceFocus['best_surface'])->toBeString();
});

it('provides URA Finale stat priority based on goals', function () {
    test()->character->update(['scenario_type' => 'ura_finale']);

    $prediction = test()->service->calculateTrainingPrediction(
        test()->character,
        'speed'
    );

    $statPriority = $prediction['scenario_specific']['mechanics']['stat_priority'];

    expect($statPriority)->toBeArray();
    // Speed has largest gap (800 - 500 = 300)
    expect(array_key_first($statPriority))->toBe('speed');
});

it('provides scenario-specific recommendations', function () {
    // Test URA Finale recommendations
    test()->character->update(['scenario_type' => 'ura_finale']);
    $prediction = test()->service->calculateTrainingPrediction(
        test()->character,
        'speed'
    );

    expect($prediction['scenario_specific']['recommendations']['focus'])
        ->toContain('individual');

    // Test Unity Cup recommendations
    test()->character->update(['scenario_type' => 'unity_cup']);
    $prediction = test()->service->calculateTrainingPrediction(
        test()->character,
        'speed'
    );

    expect($prediction['scenario_specific']['recommendations']['focus'])
        ->toContain('team');
});
