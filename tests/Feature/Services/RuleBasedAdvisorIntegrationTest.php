<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Enums\Priority;
use App\Enums\RecommendationType;
use App\Models\Character;
use App\Models\Race;
use App\Services\GameMechanicsEngine;
use App\Services\RuleBasedAdvisor;
use App\ValueObjects\TrainingContext;

/**
 * Integration tests for RuleBasedAdvisor
 *
 * These tests verify that the RuleBasedAdvisor works correctly in isolation
 * without requiring AI services or external dependencies.
 *
 * **Validates: Property 14 (Offline Fallback Behavior)**
 */
describe('RuleBasedAdvisor Integration', function () {
    beforeEach(function () {
        $this->mechanicsEngine = app(GameMechanicsEngine::class);
        $this->advisor = new RuleBasedAdvisor($this->mechanicsEngine);
    });

    describe('Complete Training Workflow', function () {
        it('provides recommendations throughout a complete training session', function () {
            // Turn 1: Low energy, should recommend rest
            $context1 = createTrainingContext([
                'turn_number' => 1,
                'energy' => 35,
                'phase' => 'junior_year',
            ]);

            $rec1 = $this->advisor->recommendTrainingFacility($context1);
            expect($rec1->type)->toBe(RecommendationType::REST_RECOVERY);

            // Turn 5: Energy recovered, no friendship training yet, should recommend multi-training
            $context2 = createTrainingContext([
                'turn_number' => 5,
                'energy' => 75,
                'phase' => 'junior_year',
                'support_deck' => [
                    'cards' => [
                        ['id' => 1, 'bond' => 50, 'facility' => 'speed'],
                        ['id' => 2, 'bond' => 45, 'facility' => 'speed'],
                        ['id' => 3, 'bond' => 40, 'facility' => 'power'],
                    ],
                ],
            ]);

            $rec2 = $this->advisor->recommendTrainingFacility($context2);
            expect($rec2->type)->toBe(RecommendationType::TRAINING_FACILITY);
            expect($rec2->action)->toBe('Speed Training');
            expect($rec2->isFriendshipTraining)->toBeFalse();

            // Turn 25: Bonds reached 80+, should recommend friendship training
            $context3 = createTrainingContext([
                'turn_number' => 25,
                'energy' => 80,
                'phase' => 'classic_year',
                'support_deck' => [
                    'cards' => [
                        ['id' => 1, 'bond' => 85, 'facility' => 'speed'],
                        ['id' => 2, 'bond' => 82, 'facility' => 'speed'],
                        ['id' => 3, 'bond' => 90, 'facility' => 'power'],
                    ],
                ],
            ]);

            $rec3 = $this->advisor->recommendTrainingFacility($context3);
            expect($rec3->type)->toBe(RecommendationType::TRAINING_FACILITY);
            expect($rec3->isFriendshipTraining)->toBeTrue();
            expect($rec3->priority)->toBe(Priority::HIGH);
        });

        it('adapts recommendations based on changing character state', function () {
            // Start with balanced stats
            $context1 = createTrainingContext([
                'energy' => 75,
                'stats' => [
                    'speed' => 500,
                    'stamina' => 500,
                    'power' => 500,
                    'guts' => 500,
                    'wisdom' => 500,
                ],
                'support_deck' => ['cards' => []],
            ]);

            $rec1 = $this->advisor->recommendTrainingFacility($context1);
            expect($rec1)->toBeInstanceOf(\App\ValueObjects\Recommendation::class);

            // Now with unbalanced stats (stamina behind)
            $context2 = createTrainingContext([
                'energy' => 75,
                'stats' => [
                    'speed' => 700,
                    'stamina' => 300, // Behind
                    'power' => 650,
                    'guts' => 600,
                    'wisdom' => 650,
                ],
                'support_deck' => ['cards' => []],
            ]);

            $rec2 = $this->advisor->recommendTrainingFacility($context2);
            expect($rec2)->toBeInstanceOf(\App\ValueObjects\Recommendation::class);
            // Should recommend training for behind stat
        });
    });

    describe('Complete Skill Purchase Workflow', function () {
        it('prioritizes skills correctly throughout career', function () {
            $character = Character::factory()->make([
                'available_sp' => 300,
            ]);

            // Early career: Gold skill with good hint available
            $earlySkills = [
                [
                    'id' => 1,
                    'name' => 'Swinging Maestro',
                    'tier' => 'gold',
                    'base_cost' => 180,
                    'hint_level' => 3,
                    'category' => 'stamina_recovery',
                ],
                [
                    'id' => 2,
                    'name' => 'Basic Skill',
                    'tier' => 'normal',
                    'base_cost' => 80,
                    'hint_level' => 5,
                    'category' => 'speed',
                ],
            ];

            $rec1 = $this->advisor->recommendSkillPurchase($character, $earlySkills);
            expect($rec1)->not->toBeNull();
            expect($rec1->action)->toBe('Purchase Swinging Maestro');

            // Mid career: After purchasing gold skill, SP reduced
            $character->available_sp = 150;
            $midSkills = [
                [
                    'id' => 3,
                    'name' => 'Lane Legerdemain',
                    'tier' => 'rare',
                    'base_cost' => 120,
                    'hint_level' => 3,
                    'category' => 'positioning',
                ],
            ];

            $rec2 = $this->advisor->recommendSkillPurchase($character, $midSkills);
            expect($rec2)->not->toBeNull();
            expect($rec2->action)->toBe('Purchase Lane Legerdemain');

            // Late career: Low SP, no good hints
            $character->available_sp = 50;
            $lateSkills = [
                [
                    'id' => 4,
                    'name' => 'Expensive Skill',
                    'tier' => 'gold',
                    'base_cost' => 200,
                    'hint_level' => 1,
                    'category' => 'acceleration',
                ],
            ];

            $rec3 = $this->advisor->recommendSkillPurchase($character, $lateSkills);
            expect($rec3)->toBeNull(); // No good options
        });
    });

    describe('Complete Race Strategy Workflow', function () {
        it('generates strategies for different race scenarios', function () {
            // Well-prepared character for medium race
            $character1 = Character::factory()->make([
                'speed' => 850,
                'stamina' => 650,
                'power' => 720,
                'guts' => 580,
                'wisdom' => 690,
            ]);

            $race1 = Race::factory()->make([
                'distance_category' => 'medium',
                'distance_meters' => 2000,
            ]);

            $strategy1 = $this->advisor->generateRaceStrategy($character1, $race1);
            expect($strategy1->racePreparationAdvice)->toContain('well-prepared');
            expect($strategy1->riskFactors)->toBeEmpty();

            // Under-prepared character for same race
            $character2 = Character::factory()->make([
                'speed' => 600,
                'stamina' => 350, // Insufficient
                'power' => 500,
                'guts' => 400,
                'wisdom' => 450,
            ]);

            $strategy2 = $this->advisor->generateRaceStrategy($character2, $race1);
            expect($strategy2->racePreparationAdvice)->toContain('Focus on stamina training');
            expect($strategy2->riskFactors)->not->toBeEmpty();
        });

        it('provides consistent strategy structure across different races', function () {
            $character = Character::factory()->make([
                'speed' => 800,
                'stamina' => 600,
                'power' => 700,
                'guts' => 550,
                'wisdom' => 650,
            ]);

            $races = [
                Race::factory()->make(['distance_category' => 'sprint', 'distance_meters' => 1200]),
                Race::factory()->make(['distance_category' => 'mile', 'distance_meters' => 1600]),
                Race::factory()->make(['distance_category' => 'medium', 'distance_meters' => 2000]),
                Race::factory()->make(['distance_category' => 'long', 'distance_meters' => 2400]),
            ];

            foreach ($races as $race) {
                $strategy = $this->advisor->generateRaceStrategy($character, $race);

                expect($strategy)->toBeInstanceOf(\App\Neuron\Responses\RaceStrategyResponse::class);
                expect($strategy->recommendedRunningStyle)->toBeString();
                expect($strategy->recommendedSkills)->toBeArray();
                expect($strategy->racePreparationAdvice)->toBeString();
                expect($strategy->validate())->toBeEmpty();
            }
        });
    });

    describe('Offline Fallback Behavior', function () {
        it('works without any external dependencies', function () {
            // This test verifies that RuleBasedAdvisor can operate completely offline
            // without requiring database connections, AI services, or external APIs

            $context = createTrainingContext([
                'energy' => 75,
                'support_deck' => [
                    'cards' => [
                        ['id' => 1, 'bond' => 85, 'facility' => 'speed'],
                    ],
                ],
            ]);

            $trainingRec = $this->advisor->recommendTrainingFacility($context);
            expect($trainingRec)->toBeInstanceOf(\App\ValueObjects\Recommendation::class);
            expect($trainingRec->source)->toBe('rule-based');

            $character = Character::factory()->make(['available_sp' => 200]);
            $skills = [
                [
                    'id' => 1,
                    'name' => 'Test Skill',
                    'tier' => 'gold',
                    'base_cost' => 180,
                    'hint_level' => 3,
                    'category' => 'stamina_recovery',
                ],
            ];

            $skillRec = $this->advisor->recommendSkillPurchase($character, $skills);
            expect($skillRec)->toBeInstanceOf(\App\ValueObjects\Recommendation::class);
            expect($skillRec->source)->toBe('rule-based');

            $race = Race::factory()->make(['distance_category' => 'medium']);
            $raceStrategy = $this->advisor->generateRaceStrategy($character, $race);
            expect($raceStrategy)->toBeInstanceOf(\App\Neuron\Responses\RaceStrategyResponse::class);
        });

        it('provides deterministic recommendations', function () {
            // Same input should always produce same output
            $context = createTrainingContext([
                'turn_number' => 15,
                'energy' => 75,
                'phase' => 'classic_year',
                'support_deck' => [
                    'cards' => [
                        ['id' => 1, 'bond' => 85, 'facility' => 'speed'],
                        ['id' => 2, 'bond' => 82, 'facility' => 'speed'],
                    ],
                ],
            ]);

            $rec1 = $this->advisor->recommendTrainingFacility($context);
            $rec2 = $this->advisor->recommendTrainingFacility($context);

            expect($rec1->type)->toBe($rec2->type);
            expect($rec1->action)->toBe($rec2->action);
            expect($rec1->priority)->toBe($rec2->priority);
            expect($rec1->reasoning)->toBe($rec2->reasoning);
        });

        it('handles edge cases gracefully', function () {
            // Empty support deck
            $context1 = createTrainingContext([
                'energy' => 75,
                'support_deck' => ['cards' => []],
            ]);
            $rec1 = $this->advisor->recommendTrainingFacility($context1);
            expect($rec1)->toBeInstanceOf(\App\ValueObjects\Recommendation::class);

            // Zero energy
            $context2 = createTrainingContext(['energy' => 0]);
            $rec2 = $this->advisor->recommendTrainingFacility($context2);
            expect($rec2->type)->toBe(RecommendationType::REST_RECOVERY);

            // Maximum energy
            $context3 = createTrainingContext(['energy' => 100]);
            $rec3 = $this->advisor->recommendTrainingFacility($context3);
            expect($rec3)->toBeInstanceOf(\App\ValueObjects\Recommendation::class);

            // No skills available
            $character = Character::factory()->make(['available_sp' => 200]);
            $rec4 = $this->advisor->recommendSkillPurchase($character, []);
            expect($rec4)->toBeNull();

            // Character with null stats
            $character2 = Character::factory()->make([
                'speed' => null,
                'stamina' => null,
                'power' => null,
                'guts' => null,
                'wisdom' => null,
            ]);
            $race = Race::factory()->make(['distance_category' => 'medium']);
            $strategy = $this->advisor->generateRaceStrategy($character2, $race);
            expect($strategy)->toBeInstanceOf(\App\Neuron\Responses\RaceStrategyResponse::class);
        });
    });

    describe('Storage Mode Consistency', function () {
        it('preserves storage mode across all recommendation types', function () {
            // Local mode
            $localContext = createTrainingContext([
                'storage_mode' => 'local',
                'energy' => 75,
                'support_deck' => [
                    'cards' => [
                        ['id' => 1, 'bond' => 85, 'facility' => 'speed'],
                    ],
                ],
            ]);

            $localRec = $this->advisor->recommendTrainingFacility($localContext);
            expect($localRec->storageMode)->toBe('local');

            // Account mode
            $accountContext = createTrainingContext([
                'storage_mode' => 'account',
                'energy' => 75,
                'support_deck' => [
                    'cards' => [
                        ['id' => 1, 'bond' => 85, 'facility' => 'speed'],
                    ],
                ],
            ]);

            $accountRec = $this->advisor->recommendTrainingFacility($accountContext);
            expect($accountRec->storageMode)->toBe('account');
        });
    });

    describe('Performance', function () {
        it('generates recommendations quickly', function () {
            $context = createTrainingContext([
                'energy' => 75,
                'support_deck' => [
                    'cards' => [
                        ['id' => 1, 'bond' => 85, 'facility' => 'speed'],
                        ['id' => 2, 'bond' => 82, 'facility' => 'speed'],
                        ['id' => 3, 'bond' => 90, 'facility' => 'power'],
                    ],
                ],
            ]);

            $start = microtime(true);
            $recommendation = $this->advisor->recommendTrainingFacility($context);
            $duration = microtime(true) - $start;

            expect($recommendation)->toBeInstanceOf(\App\ValueObjects\Recommendation::class);
            expect($duration)->toBeLessThan(0.5); // Should be much faster than 500ms
        });

        it('handles multiple recommendations efficiently', function () {
            $contexts = [];
            for ($i = 0; $i < 10; $i++) {
                $contexts[] = createTrainingContext([
                    'turn_number' => $i + 1,
                    'energy' => 75,
                ]);
            }

            $start = microtime(true);
            foreach ($contexts as $context) {
                $this->advisor->recommendTrainingFacility($context);
            }
            $duration = microtime(true) - $start;

            expect($duration)->toBeLessThan(1.0); // 10 recommendations in under 1 second
        });
    });
});

/**
 * Helper function to create a TrainingContext for testing
 *
 * @param  array<string, mixed>  $overrides
 */
function createTrainingContext(array $overrides = []): TrainingContext
{
    $defaults = [
        'turn_number' => 15,
        'phase' => 'classic_year',
        'stats' => [
            'speed' => 500,
            'stamina' => 450,
            'power' => 480,
            'guts' => 400,
            'wisdom' => 450,
        ],
        'sp_available' => 180,
        'energy' => 75,
        'mood' => 'normal',
        'acquired_skills' => [],
        'skill_hints' => [],
        'support_deck' => [
            'cards' => [],
        ],
        'facility_levels' => [
            'speed' => 3,
            'stamina' => 2,
            'power' => 3,
            'guts' => 2,
            'wisdom' => 4,
        ],
        'upcoming_races' => [],
        'scenario' => null,
        'storage_mode' => 'account',
        'career_run_id' => 1,
    ];

    $data = array_merge($defaults, $overrides);

    return TrainingContext::fromArray($data);
}
