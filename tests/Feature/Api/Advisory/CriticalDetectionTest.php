<?php

declare(strict_types=1);

use App\Collections\CriticalAlertCollection;
use App\Enums\AlertType;
use App\Enums\Priority;
use App\Models\User;
use App\Services\TrainingAdvisoryService;
use App\ValueObjects\CriticalAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user, 'sanctum');

    // Create and bind the mock service using Laravel's mock helper
    $this->mock(TrainingAdvisoryService::class, function ($mock) {
        $mock->shouldReceive('detectCriticalSituations')
            ->andReturn(new CriticalAlertCollection([
                new CriticalAlert(
                    type: AlertType::STAMINA_CRISIS,
                    message: 'Stamina critically low for upcoming Medium race (320 vs 600 required)',
                    actionItems: [
                        'Focus next 3 turns on Stamina training',
                        'Prioritize Friendship Training at Stamina facility',
                        'Consider purchasing stamina recovery skills',
                    ],
                    turnsUntilCritical: 3,
                    detailedAnalysis: 'Current stamina of 320 is 280 points below the 600 minimum.',
                    priority: Priority::CRITICAL,
                ),
            ]));
    });
});

describe('POST /api/advisory/critical/detect', function () {
    it('returns critical alerts for valid request', function () {
        $response = $this->postJson('/api/advisory/critical/detect', [
            'career_run_id' => 1,
            'turn_number' => 35,
            'context' => [
                'stats' => [
                    'speed' => 600,
                    'stamina' => 320,
                    'power' => 550,
                    'guts' => 480,
                    'wisdom' => 520,
                ],
                'energy' => 35,
                'upcoming_races' => [
                    ['distance' => 'medium', 'turn' => 38],
                ],
                'support_bonds' => [65, 70, 58, 75, 68, 72],
            ],
        ]);

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'alerts',
            'response_time_ms',
        ]);
    });

    it('returns alerts with expected structure', function () {
        $response = $this->postJson('/api/advisory/critical/detect', [
            'career_run_id' => 1,
            'turn_number' => 35,
            'context' => [
                'stats' => [
                    'speed' => 600,
                    'stamina' => 320,
                    'power' => 550,
                    'guts' => 480,
                    'wisdom' => 520,
                ],
                'energy' => 35,
                'upcoming_races' => [
                    ['distance' => 'medium', 'turn' => 38],
                ],
                'support_bonds' => [65, 70, 58, 75, 68, 72],
            ],
        ]);

        $response->assertSuccessful();

        $data = $response->json();
        expect($data)->toHaveKey('alerts');
        expect($data['alerts'])->toBeArray();

        // Verify alert structure if alerts exist
        if (! empty($data['alerts'])) {
            $alert = $data['alerts'][0];
            expect($alert)->toHaveKeys([
                'type',
                'priority',
                'message',
                'action_items',
                'turns_until_critical',
            ]);
        }
    });

    it('returns alerts with minimal required fields', function () {
        $response = $this->postJson('/api/advisory/critical/detect', [
            'career_run_id' => 1,
            'turn_number' => 15,
            'context' => [
                'stats' => [
                    'speed' => 450,
                    'stamina' => 380,
                    'power' => 420,
                    'guts' => 350,
                    'wisdom' => 400,
                ],
                'energy' => 75,
            ],
        ]);

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'alerts',
            'response_time_ms',
        ]);
    });

    it('validates career_run_id is required', function () {
        $response = $this->postJson('/api/advisory/critical/detect', [
            'turn_number' => 35,
            'context' => [
                'stats' => [
                    'speed' => 600,
                    'stamina' => 320,
                    'power' => 550,
                    'guts' => 480,
                    'wisdom' => 520,
                ],
                'energy' => 35,
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['career_run_id']);
    });

    it('validates turn_number is required', function () {
        $response = $this->postJson('/api/advisory/critical/detect', [
            'career_run_id' => 1,
            'context' => [
                'stats' => [
                    'speed' => 600,
                    'stamina' => 320,
                    'power' => 550,
                    'guts' => 480,
                    'wisdom' => 520,
                ],
                'energy' => 35,
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['turn_number']);
    });

    it('validates turn_number is within valid range', function () {
        // Test turn_number too low
        $response = $this->postJson('/api/advisory/critical/detect', [
            'career_run_id' => 1,
            'turn_number' => 0,
            'context' => [
                'stats' => [
                    'speed' => 600,
                    'stamina' => 320,
                    'power' => 550,
                    'guts' => 480,
                    'wisdom' => 520,
                ],
                'energy' => 35,
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['turn_number']);

        // Test turn_number too high
        $response = $this->postJson('/api/advisory/critical/detect', [
            'career_run_id' => 1,
            'turn_number' => 100,
            'context' => [
                'stats' => [
                    'speed' => 600,
                    'stamina' => 320,
                    'power' => 550,
                    'guts' => 480,
                    'wisdom' => 520,
                ],
                'energy' => 35,
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['turn_number']);
    });

    it('validates context is required', function () {
        $response = $this->postJson('/api/advisory/critical/detect', [
            'career_run_id' => 1,
            'turn_number' => 35,
        ]);

        $response->assertUnprocessable();
        // When context is missing, nested required fields will fail validation
        $response->assertJsonValidationErrors(['context.stats']);
    });

    it('validates context.stats is required', function () {
        $response = $this->postJson('/api/advisory/critical/detect', [
            'career_run_id' => 1,
            'turn_number' => 35,
            'context' => [
                'energy' => 35,
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['context.stats']);
    });

    it('validates all stats are required', function () {
        $response = $this->postJson('/api/advisory/critical/detect', [
            'career_run_id' => 1,
            'turn_number' => 35,
            'context' => [
                'stats' => [
                    'speed' => 600,
                    // Missing stamina, power, guts, wisdom
                ],
                'energy' => 35,
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'context.stats.stamina',
            'context.stats.power',
            'context.stats.guts',
            'context.stats.wisdom',
        ]);
    });

    it('validates stats are within valid range', function () {
        $response = $this->postJson('/api/advisory/critical/detect', [
            'career_run_id' => 1,
            'turn_number' => 35,
            'context' => [
                'stats' => [
                    'speed' => -10, // Invalid: negative
                    'stamina' => 2000, // Invalid: exceeds max
                    'power' => 550,
                    'guts' => 480,
                    'wisdom' => 520,
                ],
                'energy' => 35,
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'context.stats.speed',
            'context.stats.stamina',
        ]);
    });

    it('validates context.energy is required', function () {
        $response = $this->postJson('/api/advisory/critical/detect', [
            'career_run_id' => 1,
            'turn_number' => 35,
            'context' => [
                'stats' => [
                    'speed' => 600,
                    'stamina' => 320,
                    'power' => 550,
                    'guts' => 480,
                    'wisdom' => 520,
                ],
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['context.energy']);
    });

    it('validates energy is within valid range', function () {
        $response = $this->postJson('/api/advisory/critical/detect', [
            'career_run_id' => 1,
            'turn_number' => 35,
            'context' => [
                'stats' => [
                    'speed' => 600,
                    'stamina' => 320,
                    'power' => 550,
                    'guts' => 480,
                    'wisdom' => 520,
                ],
                'energy' => 150, // Invalid: exceeds max
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['context.energy']);
    });

    it('validates upcoming_races have required fields', function () {
        $response = $this->postJson('/api/advisory/critical/detect', [
            'career_run_id' => 1,
            'turn_number' => 35,
            'context' => [
                'stats' => [
                    'speed' => 600,
                    'stamina' => 320,
                    'power' => 550,
                    'guts' => 480,
                    'wisdom' => 520,
                ],
                'energy' => 35,
                'upcoming_races' => [
                    ['distance' => 'medium'], // Missing turn
                ],
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['context.upcoming_races.0.turn']);
    });

    it('validates upcoming_race distance must be valid', function () {
        $response = $this->postJson('/api/advisory/critical/detect', [
            'career_run_id' => 1,
            'turn_number' => 35,
            'context' => [
                'stats' => [
                    'speed' => 600,
                    'stamina' => 320,
                    'power' => 550,
                    'guts' => 480,
                    'wisdom' => 520,
                ],
                'energy' => 35,
                'upcoming_races' => [
                    ['distance' => 'invalid_distance', 'turn' => 38],
                ],
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['context.upcoming_races.0.distance']);
    });

    it('validates support_bonds cannot have more than 6 entries', function () {
        $response = $this->postJson('/api/advisory/critical/detect', [
            'career_run_id' => 1,
            'turn_number' => 35,
            'context' => [
                'stats' => [
                    'speed' => 600,
                    'stamina' => 320,
                    'power' => 550,
                    'guts' => 480,
                    'wisdom' => 520,
                ],
                'energy' => 35,
                'support_bonds' => [65, 70, 58, 75, 68, 72, 80], // 7 bonds - invalid
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['context.support_bonds']);
    });

    it('validates support_bonds values are within valid range', function () {
        $response = $this->postJson('/api/advisory/critical/detect', [
            'career_run_id' => 1,
            'turn_number' => 35,
            'context' => [
                'stats' => [
                    'speed' => 600,
                    'stamina' => 320,
                    'power' => 550,
                    'guts' => 480,
                    'wisdom' => 520,
                ],
                'energy' => 35,
                'support_bonds' => [65, 150], // 150 exceeds max of 100
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['context.support_bonds.1']);
    });

    it('requires authentication', function () {
        // Create a new test without authentication
        $this->app['auth']->forgetGuards();

        $response = $this->postJson('/api/advisory/critical/detect', [
            'career_run_id' => 1,
            'turn_number' => 35,
            'context' => [
                'stats' => [
                    'speed' => 600,
                    'stamina' => 320,
                    'power' => 550,
                    'guts' => 480,
                    'wisdom' => 520,
                ],
                'energy' => 35,
            ],
        ]);

        $response->assertUnauthorized();
    });

    it('accepts local storage mode with UUID career_run_id', function () {
        $response = $this->postJson('/api/advisory/critical/detect', [
            'career_run_id' => 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
            'turn_number' => 35,
            'storage_mode' => 'local',
            'context' => [
                'stats' => [
                    'speed' => 600,
                    'stamina' => 320,
                    'power' => 550,
                    'guts' => 480,
                    'wisdom' => 520,
                ],
                'energy' => 35,
            ],
        ]);

        $response->assertSuccessful();
    });

    it('returns response within acceptable time bounds', function () {
        $startTime = microtime(true);

        $response = $this->postJson('/api/advisory/critical/detect', [
            'career_run_id' => 1,
            'turn_number' => 35,
            'context' => [
                'stats' => [
                    'speed' => 600,
                    'stamina' => 320,
                    'power' => 550,
                    'guts' => 480,
                    'wisdom' => 520,
                ],
                'energy' => 35,
            ],
        ]);

        $duration = microtime(true) - $startTime;

        $response->assertSuccessful();

        // Response should include response_time_ms
        $response->assertJsonStructure(['response_time_ms']);

        // Critical detection should be fast (< 500ms as per design)
        expect($duration)->toBeLessThan(0.5);
    });

    it('detects stamina crisis for low stamina with upcoming race', function () {
        // Override the mock for this specific test
        $this->mock(TrainingAdvisoryService::class, function ($mock) {
            $mock->shouldReceive('detectCriticalSituations')
                ->andReturn(new CriticalAlertCollection([
                    new CriticalAlert(
                        type: AlertType::STAMINA_CRISIS,
                        message: 'Stamina critically low for upcoming Medium race (320 vs 600 required)',
                        actionItems: [
                            'Focus next 3 turns on Stamina training',
                            'Prioritize Friendship Training at Stamina facility',
                        ],
                        turnsUntilCritical: 3,
                        detailedAnalysis: 'Current stamina of 320 is 280 points below the 600 minimum.',
                        priority: Priority::CRITICAL,
                    ),
                ]));
        });

        $response = $this->postJson('/api/advisory/critical/detect', [
            'career_run_id' => 1,
            'turn_number' => 35,
            'context' => [
                'stats' => [
                    'speed' => 600,
                    'stamina' => 320, // Low stamina
                    'power' => 550,
                    'guts' => 480,
                    'wisdom' => 520,
                ],
                'energy' => 75,
                'upcoming_races' => [
                    ['distance' => 'medium', 'turn' => 38],
                ],
            ],
        ]);

        $response->assertSuccessful();

        $data = $response->json();
        expect($data['alerts'])->not->toBeEmpty();

        $hasStaminaAlert = collect($data['alerts'])
            ->contains(fn ($alert) => $alert['type'] === 'stamina_crisis');

        expect($hasStaminaAlert)->toBeTrue();
    });

    it('detects energy critical for low energy', function () {
        // Override the mock for this specific test
        $this->mock(TrainingAdvisoryService::class, function ($mock) {
            $mock->shouldReceive('detectCriticalSituations')
                ->andReturn(new CriticalAlertCollection([
                    new CriticalAlert(
                        type: AlertType::ENERGY_CRITICAL,
                        message: 'Energy at 30 - high failure rate risk',
                        actionItems: [
                            'Rest immediately or train Wisdom',
                            'Avoid high-risk training until energy recovers to 50+',
                        ],
                        turnsUntilCritical: 0,
                        detailedAnalysis: null,
                        priority: Priority::HIGH,
                    ),
                ]));
        });

        $response = $this->postJson('/api/advisory/critical/detect', [
            'career_run_id' => 1,
            'turn_number' => 35,
            'context' => [
                'stats' => [
                    'speed' => 600,
                    'stamina' => 500,
                    'power' => 550,
                    'guts' => 480,
                    'wisdom' => 520,
                ],
                'energy' => 30, // Low energy - should trigger alert
            ],
        ]);

        $response->assertSuccessful();

        $data = $response->json();
        expect($data['alerts'])->not->toBeEmpty();

        $hasEnergyAlert = collect($data['alerts'])
            ->contains(fn ($alert) => $alert['type'] === 'energy_critical');

        expect($hasEnergyAlert)->toBeTrue();
    });

    it('handles all valid race distances', function () {
        $distances = ['sprint', 'mile', 'medium', 'long'];

        foreach ($distances as $distance) {
            $response = $this->postJson('/api/advisory/critical/detect', [
                'career_run_id' => 1,
                'turn_number' => 35,
                'context' => [
                    'stats' => [
                        'speed' => 600,
                        'stamina' => 320,
                        'power' => 550,
                        'guts' => 480,
                        'wisdom' => 520,
                    ],
                    'energy' => 75,
                    'upcoming_races' => [
                        ['distance' => $distance, 'turn' => 38],
                    ],
                ],
            ]);

            $response->assertSuccessful();
        }
    });

    it('handles optional context fields', function () {
        $response = $this->postJson('/api/advisory/critical/detect', [
            'career_run_id' => 1,
            'turn_number' => 35,
            'context' => [
                'stats' => [
                    'speed' => 600,
                    'stamina' => 320,
                    'power' => 550,
                    'guts' => 480,
                    'wisdom' => 520,
                ],
                'energy' => 75,
                'sp_available' => 200,
                'mood' => 'good',
                'phase' => 'classic_year',
                'facility_levels' => [
                    'speed' => 3,
                    'stamina' => 2,
                    'power' => 3,
                    'guts' => 2,
                    'wisdom' => 4,
                ],
                'acquired_skills' => [1, 5, 12],
                'skill_hints' => [
                    ['skill_id' => 23, 'level' => 3],
                ],
            ],
        ]);

        $response->assertSuccessful();
    });

    it('validates optional mood must be valid', function () {
        $response = $this->postJson('/api/advisory/critical/detect', [
            'career_run_id' => 1,
            'turn_number' => 35,
            'context' => [
                'stats' => [
                    'speed' => 600,
                    'stamina' => 320,
                    'power' => 550,
                    'guts' => 480,
                    'wisdom' => 520,
                ],
                'energy' => 75,
                'mood' => 'invalid_mood',
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['context.mood']);
    });

    it('validates optional phase must be valid', function () {
        $response = $this->postJson('/api/advisory/critical/detect', [
            'career_run_id' => 1,
            'turn_number' => 35,
            'context' => [
                'stats' => [
                    'speed' => 600,
                    'stamina' => 320,
                    'power' => 550,
                    'guts' => 480,
                    'wisdom' => 520,
                ],
                'energy' => 75,
                'phase' => 'invalid_phase',
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['context.phase']);
    });

    it('validates facility levels are within valid range', function () {
        $response = $this->postJson('/api/advisory/critical/detect', [
            'career_run_id' => 1,
            'turn_number' => 35,
            'context' => [
                'stats' => [
                    'speed' => 600,
                    'stamina' => 320,
                    'power' => 550,
                    'guts' => 480,
                    'wisdom' => 520,
                ],
                'energy' => 75,
                'facility_levels' => [
                    'speed' => 6, // Invalid: exceeds max of 5
                    'stamina' => 0, // Invalid: below min of 1
                ],
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'context.facility_levels.speed',
            'context.facility_levels.stamina',
        ]);
    });

    it('validates skill hints have required fields', function () {
        $response = $this->postJson('/api/advisory/critical/detect', [
            'career_run_id' => 1,
            'turn_number' => 35,
            'context' => [
                'stats' => [
                    'speed' => 600,
                    'stamina' => 320,
                    'power' => 550,
                    'guts' => 480,
                    'wisdom' => 520,
                ],
                'energy' => 75,
                'skill_hints' => [
                    ['skill_id' => 23], // Missing level
                ],
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['context.skill_hints.0.level']);
    });

    it('returns empty alerts array when no critical situations detected', function () {
        // Override the mock to return empty alerts
        $this->mock(TrainingAdvisoryService::class, function ($mock) {
            $mock->shouldReceive('detectCriticalSituations')
                ->andReturn(new CriticalAlertCollection([]));
        });

        $response = $this->postJson('/api/advisory/critical/detect', [
            'career_run_id' => 1,
            'turn_number' => 15,
            'context' => [
                'stats' => [
                    'speed' => 800,
                    'stamina' => 700,
                    'power' => 750,
                    'guts' => 600,
                    'wisdom' => 650,
                ],
                'energy' => 90, // High energy - no critical situation
            ],
        ]);

        $response->assertSuccessful();

        $data = $response->json();
        expect($data['alerts'])->toBeArray();
        expect($data['alerts'])->toBeEmpty();
    });
});
