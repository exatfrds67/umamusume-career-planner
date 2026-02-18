<?php

declare(strict_types=1);

use App\Collections\CriticalAlertCollection;
use App\Collections\RecommendationCollection;
use App\Enums\AlertType;
use App\Enums\Priority;
use App\Enums\RecommendationType;
use App\Models\User;
use App\Services\TrainingAdvisoryService;
use App\ValueObjects\CriticalAlert;
use App\ValueObjects\Recommendation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create and bind the mock service using Laravel's mock helper
    $this->mock(TrainingAdvisoryService::class, function (MockInterface $mock) {
        $mock->shouldReceive('getTrainingRecommendations')
            ->andReturn(new RecommendationCollection([
                new Recommendation(
                    type: RecommendationType::TRAINING_FACILITY,
                    priority: Priority::HIGH,
                    action: 'Speed Training',
                    reasoning: 'Test recommendation',
                    expectedOutcomes: ['speed_gain' => '+45'],
                    risks: ['5% failure rate'],
                    confidenceScore: 0.92,
                ),
            ]));

        $mock->shouldReceive('detectCriticalSituations')
            ->andReturn(new CriticalAlertCollection([]));
    });

    $this->user = User::factory()->create();
    $this->actingAs($this->user, 'sanctum');
});

describe('POST /api/advisory/training/recommendations', function () {
    it('returns training recommendations for valid request', function () {
        $response = $this->postJson('/api/advisory/training/recommendations', [
            'career_run_id' => 1,
            'storage_mode' => 'account',
            'turn_number' => 15,
            'phase' => 'classic_year',
            'stats' => [
                'speed' => 450,
                'stamina' => 380,
                'power' => 420,
                'guts' => 350,
                'wisdom' => 400,
            ],
            'sp_available' => 180,
            'energy' => 75,
            'mood' => 'good',
            'acquired_skills' => [1, 5, 12],
            'skill_hints' => [
                ['skill_id' => 23, 'level' => 3],
                ['skill_id' => 45, 'level' => 2],
            ],
            'support_deck' => [
                'cards' => [
                    ['id' => 1, 'bond' => 85, 'facility' => 'speed'],
                    ['id' => 2, 'bond' => 72, 'facility' => 'stamina'],
                ],
            ],
            'facility_levels' => [
                'speed' => 3,
                'stamina' => 2,
                'power' => 3,
                'guts' => 2,
                'wisdom' => 4,
            ],
            'upcoming_races' => [
                ['id' => 15, 'distance' => 'medium', 'turn' => 18],
            ],
        ]);

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'recommendations',
            'critical_alerts',
            'response_time_ms',
            'ai_provider',
        ]);
    });

    it('returns recommendations with minimal required fields', function () {
        $response = $this->postJson('/api/advisory/training/recommendations', [
            'career_run_id' => 1,
            'storage_mode' => 'local',
            'turn_number' => 1,
            'phase' => 'junior_year',
            'stats' => [
                'speed' => 100,
                'stamina' => 100,
                'power' => 100,
                'guts' => 100,
                'wisdom' => 100,
            ],
            'sp_available' => 0,
            'energy' => 100,
            'mood' => 'normal',
        ]);

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'recommendations',
            'critical_alerts',
            'response_time_ms',
            'ai_provider',
        ]);
    });

    it('validates career_run_id is required', function () {
        $response = $this->postJson('/api/advisory/training/recommendations', [
            'storage_mode' => 'account',
            'turn_number' => 15,
            'phase' => 'classic_year',
            'stats' => [
                'speed' => 450,
                'stamina' => 380,
                'power' => 420,
                'guts' => 350,
                'wisdom' => 400,
            ],
            'sp_available' => 180,
            'energy' => 75,
            'mood' => 'good',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['career_run_id']);
    });

    it('validates storage_mode must be local or account', function () {
        $response = $this->postJson('/api/advisory/training/recommendations', [
            'career_run_id' => 1,
            'storage_mode' => 'invalid',
            'turn_number' => 15,
            'phase' => 'classic_year',
            'stats' => [
                'speed' => 450,
                'stamina' => 380,
                'power' => 420,
                'guts' => 350,
                'wisdom' => 400,
            ],
            'sp_available' => 180,
            'energy' => 75,
            'mood' => 'good',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['storage_mode']);
    });

    it('validates turn_number is within valid range', function () {
        // Test turn_number too low
        $response = $this->postJson('/api/advisory/training/recommendations', [
            'career_run_id' => 1,
            'storage_mode' => 'account',
            'turn_number' => 0,
            'phase' => 'junior_year',
            'stats' => [
                'speed' => 100,
                'stamina' => 100,
                'power' => 100,
                'guts' => 100,
                'wisdom' => 100,
            ],
            'sp_available' => 0,
            'energy' => 100,
            'mood' => 'normal',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['turn_number']);

        // Test turn_number too high
        $response = $this->postJson('/api/advisory/training/recommendations', [
            'career_run_id' => 1,
            'storage_mode' => 'account',
            'turn_number' => 100,
            'phase' => 'ura_finals',
            'stats' => [
                'speed' => 100,
                'stamina' => 100,
                'power' => 100,
                'guts' => 100,
                'wisdom' => 100,
            ],
            'sp_available' => 0,
            'energy' => 100,
            'mood' => 'normal',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['turn_number']);
    });

    it('validates phase must be a valid career phase', function () {
        $response = $this->postJson('/api/advisory/training/recommendations', [
            'career_run_id' => 1,
            'storage_mode' => 'account',
            'turn_number' => 15,
            'phase' => 'invalid_phase',
            'stats' => [
                'speed' => 450,
                'stamina' => 380,
                'power' => 420,
                'guts' => 350,
                'wisdom' => 400,
            ],
            'sp_available' => 180,
            'energy' => 75,
            'mood' => 'good',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['phase']);
    });

    it('validates all stats are required', function () {
        $response = $this->postJson('/api/advisory/training/recommendations', [
            'career_run_id' => 1,
            'storage_mode' => 'account',
            'turn_number' => 15,
            'phase' => 'classic_year',
            'stats' => [
                'speed' => 450,
                // Missing stamina, power, guts, wisdom
            ],
            'sp_available' => 180,
            'energy' => 75,
            'mood' => 'good',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'stats.stamina',
            'stats.power',
            'stats.guts',
            'stats.wisdom',
        ]);
    });

    it('validates stats are within valid range', function () {
        $response = $this->postJson('/api/advisory/training/recommendations', [
            'career_run_id' => 1,
            'storage_mode' => 'account',
            'turn_number' => 15,
            'phase' => 'classic_year',
            'stats' => [
                'speed' => -10, // Invalid: negative
                'stamina' => 2000, // Invalid: exceeds max
                'power' => 420,
                'guts' => 350,
                'wisdom' => 400,
            ],
            'sp_available' => 180,
            'energy' => 75,
            'mood' => 'good',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['stats.speed', 'stats.stamina']);
    });

    it('validates mood must be a valid mood value', function () {
        $response = $this->postJson('/api/advisory/training/recommendations', [
            'career_run_id' => 1,
            'storage_mode' => 'account',
            'turn_number' => 15,
            'phase' => 'classic_year',
            'stats' => [
                'speed' => 450,
                'stamina' => 380,
                'power' => 420,
                'guts' => 350,
                'wisdom' => 400,
            ],
            'sp_available' => 180,
            'energy' => 75,
            'mood' => 'invalid_mood',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['mood']);
    });

    it('validates energy is within valid range', function () {
        $response = $this->postJson('/api/advisory/training/recommendations', [
            'career_run_id' => 1,
            'storage_mode' => 'account',
            'turn_number' => 15,
            'phase' => 'classic_year',
            'stats' => [
                'speed' => 450,
                'stamina' => 380,
                'power' => 420,
                'guts' => 350,
                'wisdom' => 400,
            ],
            'sp_available' => 180,
            'energy' => 150, // Invalid: exceeds max
            'mood' => 'good',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['energy']);
    });

    it('validates support deck cards have required fields', function () {
        $response = $this->postJson('/api/advisory/training/recommendations', [
            'career_run_id' => 1,
            'storage_mode' => 'account',
            'turn_number' => 15,
            'phase' => 'classic_year',
            'stats' => [
                'speed' => 450,
                'stamina' => 380,
                'power' => 420,
                'guts' => 350,
                'wisdom' => 400,
            ],
            'sp_available' => 180,
            'energy' => 75,
            'mood' => 'good',
            'support_deck' => [
                'cards' => [
                    ['id' => 1], // Missing bond and facility
                ],
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'support_deck.cards.0.bond',
            'support_deck.cards.0.facility',
        ]);
    });

    it('validates support deck cannot have more than 6 cards', function () {
        $response = $this->postJson('/api/advisory/training/recommendations', [
            'career_run_id' => 1,
            'storage_mode' => 'account',
            'turn_number' => 15,
            'phase' => 'classic_year',
            'stats' => [
                'speed' => 450,
                'stamina' => 380,
                'power' => 420,
                'guts' => 350,
                'wisdom' => 400,
            ],
            'sp_available' => 180,
            'energy' => 75,
            'mood' => 'good',
            'support_deck' => [
                'cards' => [
                    ['id' => 1, 'bond' => 80, 'facility' => 'speed'],
                    ['id' => 2, 'bond' => 80, 'facility' => 'stamina'],
                    ['id' => 3, 'bond' => 80, 'facility' => 'power'],
                    ['id' => 4, 'bond' => 80, 'facility' => 'guts'],
                    ['id' => 5, 'bond' => 80, 'facility' => 'wisdom'],
                    ['id' => 6, 'bond' => 80, 'facility' => 'friend'],
                    ['id' => 7, 'bond' => 80, 'facility' => 'speed'], // 7th card - invalid
                ],
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['support_deck.cards']);
    });

    it('validates facility levels are within valid range', function () {
        $response = $this->postJson('/api/advisory/training/recommendations', [
            'career_run_id' => 1,
            'storage_mode' => 'account',
            'turn_number' => 15,
            'phase' => 'classic_year',
            'stats' => [
                'speed' => 450,
                'stamina' => 380,
                'power' => 420,
                'guts' => 350,
                'wisdom' => 400,
            ],
            'sp_available' => 180,
            'energy' => 75,
            'mood' => 'good',
            'facility_levels' => [
                'speed' => 6, // Invalid: exceeds max of 5
                'stamina' => 0, // Invalid: below min of 1
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'facility_levels.speed',
            'facility_levels.stamina',
        ]);
    });

    it('validates skill hints have required fields', function () {
        $response = $this->postJson('/api/advisory/training/recommendations', [
            'career_run_id' => 1,
            'storage_mode' => 'account',
            'turn_number' => 15,
            'phase' => 'classic_year',
            'stats' => [
                'speed' => 450,
                'stamina' => 380,
                'power' => 420,
                'guts' => 350,
                'wisdom' => 400,
            ],
            'sp_available' => 180,
            'energy' => 75,
            'mood' => 'good',
            'skill_hints' => [
                ['skill_id' => 23], // Missing level
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['skill_hints.0.level']);
    });

    it('validates skill hint levels are within valid range', function () {
        $response = $this->postJson('/api/advisory/training/recommendations', [
            'career_run_id' => 1,
            'storage_mode' => 'account',
            'turn_number' => 15,
            'phase' => 'classic_year',
            'stats' => [
                'speed' => 450,
                'stamina' => 380,
                'power' => 420,
                'guts' => 350,
                'wisdom' => 400,
            ],
            'sp_available' => 180,
            'energy' => 75,
            'mood' => 'good',
            'skill_hints' => [
                ['skill_id' => 23, 'level' => 6], // Invalid: exceeds max of 5
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['skill_hints.0.level']);
    });

    it('validates upcoming races have required fields', function () {
        $response = $this->postJson('/api/advisory/training/recommendations', [
            'career_run_id' => 1,
            'storage_mode' => 'account',
            'turn_number' => 15,
            'phase' => 'classic_year',
            'stats' => [
                'speed' => 450,
                'stamina' => 380,
                'power' => 420,
                'guts' => 350,
                'wisdom' => 400,
            ],
            'sp_available' => 180,
            'energy' => 75,
            'mood' => 'good',
            'upcoming_races' => [
                ['id' => 15], // Missing distance and turn
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'upcoming_races.0.distance',
            'upcoming_races.0.turn',
        ]);
    });

    it('validates upcoming race distance must be valid', function () {
        $response = $this->postJson('/api/advisory/training/recommendations', [
            'career_run_id' => 1,
            'storage_mode' => 'account',
            'turn_number' => 15,
            'phase' => 'classic_year',
            'stats' => [
                'speed' => 450,
                'stamina' => 380,
                'power' => 420,
                'guts' => 350,
                'wisdom' => 400,
            ],
            'sp_available' => 180,
            'energy' => 75,
            'mood' => 'good',
            'upcoming_races' => [
                ['id' => 15, 'distance' => 'invalid_distance', 'turn' => 18],
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['upcoming_races.0.distance']);
    });

    it('requires authentication', function () {
        // Create a new test without authentication
        $this->app['auth']->forgetGuards();

        $response = $this->postJson('/api/advisory/training/recommendations', [
            'career_run_id' => 1,
            'storage_mode' => 'account',
            'turn_number' => 15,
            'phase' => 'classic_year',
            'stats' => [
                'speed' => 450,
                'stamina' => 380,
                'power' => 420,
                'guts' => 350,
                'wisdom' => 400,
            ],
            'sp_available' => 180,
            'energy' => 75,
            'mood' => 'good',
        ]);

        $response->assertUnauthorized();
    });

    it('accepts local storage mode with UUID career_run_id', function () {
        $response = $this->postJson('/api/advisory/training/recommendations', [
            'career_run_id' => 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
            'storage_mode' => 'local',
            'turn_number' => 15,
            'phase' => 'classic_year',
            'stats' => [
                'speed' => 450,
                'stamina' => 380,
                'power' => 420,
                'guts' => 350,
                'wisdom' => 400,
            ],
            'sp_available' => 180,
            'energy' => 75,
            'mood' => 'good',
        ]);

        $response->assertSuccessful();
    });

    it('returns response within acceptable time bounds', function () {
        $startTime = microtime(true);

        $response = $this->postJson('/api/advisory/training/recommendations', [
            'career_run_id' => 1,
            'storage_mode' => 'account',
            'turn_number' => 15,
            'phase' => 'classic_year',
            'stats' => [
                'speed' => 450,
                'stamina' => 380,
                'power' => 420,
                'guts' => 350,
                'wisdom' => 400,
            ],
            'sp_available' => 180,
            'energy' => 75,
            'mood' => 'good',
        ]);

        $duration = microtime(true) - $startTime;

        $response->assertSuccessful();

        // Response should include response_time_ms
        $response->assertJsonStructure(['response_time_ms']);

        // Rule-based fallback should be fast (< 2 seconds)
        expect($duration)->toBeLessThan(2.0);
    });

    it('returns recommendations with expected structure', function () {
        $response = $this->postJson('/api/advisory/training/recommendations', [
            'career_run_id' => 1,
            'storage_mode' => 'account',
            'turn_number' => 15,
            'phase' => 'classic_year',
            'stats' => [
                'speed' => 450,
                'stamina' => 380,
                'power' => 420,
                'guts' => 350,
                'wisdom' => 400,
            ],
            'sp_available' => 180,
            'energy' => 75,
            'mood' => 'good',
        ]);

        $response->assertSuccessful();

        $data = $response->json();

        // Verify recommendations array exists
        expect($data)->toHaveKey('recommendations');
        expect($data['recommendations'])->toBeArray();

        // If recommendations exist, verify structure
        if (! empty($data['recommendations'])) {
            $recommendation = $data['recommendations'][0];
            expect($recommendation)->toHaveKeys([
                'type',
                'priority',
                'action',
                'reasoning',
                'expected_outcomes',
                'risks',
            ]);
        }
    });

    it('detects critical alerts for low energy', function () {
        // Override the mock for this specific test to return critical alerts
        $this->mock(TrainingAdvisoryService::class, function ($mock) {
            $mock->shouldReceive('getTrainingRecommendations')
                ->andReturn(new RecommendationCollection([
                    new Recommendation(
                        type: RecommendationType::TRAINING_FACILITY,
                        priority: Priority::HIGH,
                        action: 'Wisdom Training',
                        reasoning: 'Low energy - recommend rest or wisdom',
                        expectedOutcomes: ['energy_recovery' => '+5'],
                        risks: [],
                        confidenceScore: 0.95,
                    ),
                ]));

            $mock->shouldReceive('detectCriticalSituations')
                ->andReturn(new CriticalAlertCollection([
                    new CriticalAlert(
                        type: AlertType::ENERGY_CRITICAL,
                        message: 'Energy at 30 - high failure rate risk',
                        actionItems: ['Rest immediately or train Wisdom'],
                        turnsUntilCritical: 0,
                        detailedAnalysis: null,
                        priority: Priority::CRITICAL,
                    ),
                ]));
        });

        $response = $this->postJson('/api/advisory/training/recommendations', [
            'career_run_id' => 1,
            'storage_mode' => 'account',
            'turn_number' => 15,
            'phase' => 'classic_year',
            'stats' => [
                'speed' => 450,
                'stamina' => 380,
                'power' => 420,
                'guts' => 350,
                'wisdom' => 400,
            ],
            'sp_available' => 180,
            'energy' => 30, // Low energy - should trigger alert
            'mood' => 'good',
        ]);

        $response->assertSuccessful();

        $data = $response->json();
        expect($data)->toHaveKey('critical_alerts');
        expect($data['critical_alerts'])->toBeArray();

        // Should have at least one critical alert for low energy
        $hasEnergyAlert = collect($data['critical_alerts'])
            ->contains(fn ($alert) => $alert['type'] === 'energy_critical');

        expect($hasEnergyAlert)->toBeTrue();
    });

    it('handles all valid career phases', function () {
        $phases = ['junior_year', 'classic_year', 'senior_year', 'ura_finals'];

        foreach ($phases as $phase) {
            $response = $this->postJson('/api/advisory/training/recommendations', [
                'career_run_id' => 1,
                'storage_mode' => 'account',
                'turn_number' => 15,
                'phase' => $phase,
                'stats' => [
                    'speed' => 450,
                    'stamina' => 380,
                    'power' => 420,
                    'guts' => 350,
                    'wisdom' => 400,
                ],
                'sp_available' => 180,
                'energy' => 75,
                'mood' => 'good',
            ]);

            $response->assertSuccessful();
        }
    });

    it('handles all valid mood values', function () {
        $moods = ['very_bad', 'bad', 'normal', 'good', 'great'];

        foreach ($moods as $mood) {
            $response = $this->postJson('/api/advisory/training/recommendations', [
                'career_run_id' => 1,
                'storage_mode' => 'account',
                'turn_number' => 15,
                'phase' => 'classic_year',
                'stats' => [
                    'speed' => 450,
                    'stamina' => 380,
                    'power' => 420,
                    'guts' => 350,
                    'wisdom' => 400,
                ],
                'sp_available' => 180,
                'energy' => 75,
                'mood' => $mood,
            ]);

            $response->assertSuccessful();
        }
    });
});
