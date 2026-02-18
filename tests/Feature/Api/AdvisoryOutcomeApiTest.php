<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\AI\BedrockService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Advisory Outcome API Tests
 *
 * Tests for the Advisory API endpoints that record training and race outcomes
 * for prediction accuracy tracking.
 *
 * **Validates: Requirements 3.8 (Prediction Accuracy Tracking)**
 * **Validates: Task 6.2.2 (Create API endpoints for outcome recording)**
 *
 * @see \App\Http\Controllers\Api\AdvisoryController
 */
beforeEach(function () {
    // Mock BedrockService to avoid AWS credential requirements in tests
    $this->mock(BedrockService::class, function ($mock) {
        $mock->shouldReceive('isAvailable')->andReturn(false);
        $mock->shouldReceive('isHealthy')->andReturn(false);
        $mock->shouldReceive('getStatus')->andReturn([
            'available' => false,
            'healthy' => false,
            'default_model' => 'claude-3-5-sonnet',
            'pricing' => [],
            'timeout' => 30,
            'temperature' => 0.3,
        ]);
    });

    // Create and authenticate a user
    $this->user = User::factory()->create();
    $this->actingAs($this->user, 'sanctum');
});

describe('Training Outcome Recording', function () {
    test('can record training outcome with valid data', function () {
        $payload = [
            'career_run_id' => 123,
            'turn_number' => 15,
            'recommendation' => [
                'type' => 'training_facility',
                'priority' => 'high',
                'action' => 'Speed Training',
                'reasoning' => '3 support cards present (Friendship Training available), facility at Level 3',
                'expected_outcomes' => [
                    'stat_gains' => [
                        'speed' => 45,
                        'power' => 12,
                    ],
                ],
                'risks' => ['5% failure rate due to energy level'],
                'confidence_score' => 0.92,
            ],
            'actual_outcome' => [
                'facility' => 'speed',
                'stat_gains' => [
                    'speed' => 48,
                    'power' => 10,
                ],
                'bond_increases' => [7, 7, 7],
                'skill_hints' => [],
                'was_failure' => false,
                'was_injury' => false,
                'energy_change' => -10,
            ],
        ];

        $response = $this->postJson('/api/advisory/training/outcome', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Training outcome recorded successfully',
                'data' => [
                    'career_run_id' => 123,
                    'turn_number' => 15,
                    'facility' => 'speed',
                    'was_successful' => true,
                ],
            ]);
    });

    test('validates required fields for training outcome', function () {
        $response = $this->postJson('/api/advisory/training/outcome', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'career_run_id',
                'turn_number',
                'recommendation',
                'actual_outcome',
            ]);
    });

    test('validates career_run_id is a positive integer', function () {
        $payload = [
            'career_run_id' => -1,
            'turn_number' => 15,
            'recommendation' => [
                'type' => 'training_facility',
                'priority' => 'high',
                'action' => 'Speed Training',
                'reasoning' => 'Test reasoning',
                'expected_outcomes' => ['stat_gains' => ['speed' => 45]],
            ],
            'actual_outcome' => [
                'facility' => 'speed',
                'stat_gains' => ['speed' => 48],
            ],
        ];

        $response = $this->postJson('/api/advisory/training/outcome', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['career_run_id']);
    });

    test('validates turn_number is within valid range', function () {
        $payload = [
            'career_run_id' => 123,
            'turn_number' => 100, // Exceeds max of 78
            'recommendation' => [
                'type' => 'training_facility',
                'priority' => 'high',
                'action' => 'Speed Training',
                'reasoning' => 'Test reasoning',
                'expected_outcomes' => ['stat_gains' => ['speed' => 45]],
            ],
            'actual_outcome' => [
                'facility' => 'speed',
                'stat_gains' => ['speed' => 48],
            ],
        ];

        $response = $this->postJson('/api/advisory/training/outcome', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['turn_number']);
    });

    test('validates recommendation type is valid', function () {
        $payload = [
            'career_run_id' => 123,
            'turn_number' => 15,
            'recommendation' => [
                'type' => 'invalid_type',
                'priority' => 'high',
                'action' => 'Speed Training',
                'reasoning' => 'Test reasoning',
                'expected_outcomes' => ['stat_gains' => ['speed' => 45]],
            ],
            'actual_outcome' => [
                'facility' => 'speed',
                'stat_gains' => ['speed' => 48],
            ],
        ];

        $response = $this->postJson('/api/advisory/training/outcome', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['recommendation.type']);
    });

    test('validates recommendation priority is valid', function () {
        $payload = [
            'career_run_id' => 123,
            'turn_number' => 15,
            'recommendation' => [
                'type' => 'training_facility',
                'priority' => 'invalid_priority',
                'action' => 'Speed Training',
                'reasoning' => 'Test reasoning',
                'expected_outcomes' => ['stat_gains' => ['speed' => 45]],
            ],
            'actual_outcome' => [
                'facility' => 'speed',
                'stat_gains' => ['speed' => 48],
            ],
        ];

        $response = $this->postJson('/api/advisory/training/outcome', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['recommendation.priority']);
    });

    test('validates confidence_score is between 0 and 1', function () {
        $payload = [
            'career_run_id' => 123,
            'turn_number' => 15,
            'recommendation' => [
                'type' => 'training_facility',
                'priority' => 'high',
                'action' => 'Speed Training',
                'reasoning' => 'Test reasoning',
                'expected_outcomes' => ['stat_gains' => ['speed' => 45]],
                'confidence_score' => 1.5, // Exceeds max of 1.0
            ],
            'actual_outcome' => [
                'facility' => 'speed',
                'stat_gains' => ['speed' => 48],
            ],
        ];

        $response = $this->postJson('/api/advisory/training/outcome', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['recommendation.confidence_score']);
    });

    test('validates actual_outcome facility is valid', function () {
        $payload = [
            'career_run_id' => 123,
            'turn_number' => 15,
            'recommendation' => [
                'type' => 'training_facility',
                'priority' => 'high',
                'action' => 'Speed Training',
                'reasoning' => 'Test reasoning',
                'expected_outcomes' => ['stat_gains' => ['speed' => 45]],
            ],
            'actual_outcome' => [
                'facility' => 'invalid_facility',
                'stat_gains' => ['speed' => 48],
            ],
        ];

        $response = $this->postJson('/api/advisory/training/outcome', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['actual_outcome.facility']);
    });

    test('validates stat_gains are within valid range', function () {
        $payload = [
            'career_run_id' => 123,
            'turn_number' => 15,
            'recommendation' => [
                'type' => 'training_facility',
                'priority' => 'high',
                'action' => 'Speed Training',
                'reasoning' => 'Test reasoning',
                'expected_outcomes' => ['stat_gains' => ['speed' => 45]],
            ],
            'actual_outcome' => [
                'facility' => 'speed',
                'stat_gains' => [
                    'speed' => 250, // Exceeds max of 200
                ],
            ],
        ];

        $response = $this->postJson('/api/advisory/training/outcome', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['actual_outcome.stat_gains.speed']);
    });

    test('can record training outcome with failure', function () {
        $payload = [
            'career_run_id' => 123,
            'turn_number' => 15,
            'recommendation' => [
                'type' => 'training_facility',
                'priority' => 'high',
                'action' => 'Speed Training',
                'reasoning' => 'Test reasoning',
                'expected_outcomes' => ['stat_gains' => ['speed' => 45]],
            ],
            'actual_outcome' => [
                'facility' => 'speed',
                'stat_gains' => ['speed' => 10], // Failures still give reduced stats
                'was_failure' => true,
                'was_injury' => false,
                'energy_change' => -5,
            ],
        ];

        $response = $this->postJson('/api/advisory/training/outcome', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'was_successful' => false,
                ],
            ]);
    });

    test('can record training outcome with injury', function () {
        $payload = [
            'career_run_id' => 123,
            'turn_number' => 15,
            'recommendation' => [
                'type' => 'training_facility',
                'priority' => 'high',
                'action' => 'Speed Training',
                'reasoning' => 'Test reasoning',
                'expected_outcomes' => ['stat_gains' => ['speed' => 45]],
            ],
            'actual_outcome' => [
                'facility' => 'speed',
                'stat_gains' => ['speed' => 20],
                'was_failure' => false,
                'was_injury' => true,
                'energy_change' => -15,
            ],
        ];

        $response = $this->postJson('/api/advisory/training/outcome', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'was_successful' => false,
                ],
            ]);
    });

    test('requires authentication', function () {
        $this->app['auth']->forgetGuards();

        $payload = [
            'career_run_id' => 123,
            'turn_number' => 15,
            'recommendation' => [
                'type' => 'training_facility',
                'priority' => 'high',
                'action' => 'Speed Training',
                'reasoning' => 'Test reasoning',
                'expected_outcomes' => ['stat_gains' => ['speed' => 45]],
            ],
            'actual_outcome' => [
                'facility' => 'speed',
                'stat_gains' => ['speed' => 48],
            ],
        ];

        $response = $this->postJson('/api/advisory/training/outcome', $payload);

        $response->assertStatus(401);
    });

    test('respects rate limiting', function () {
        $payload = [
            'career_run_id' => 123,
            'turn_number' => 15,
            'recommendation' => [
                'type' => 'training_facility',
                'priority' => 'high',
                'action' => 'Speed Training',
                'reasoning' => 'Test reasoning',
                'expected_outcomes' => ['stat_gains' => ['speed' => 45]],
            ],
            'actual_outcome' => [
                'facility' => 'speed',
                'stat_gains' => ['speed' => 48],
            ],
        ];

        // Make 11 requests (rate limit is 10 per minute)
        for ($i = 0; $i < 11; $i++) {
            $response = $this->postJson('/api/advisory/training/outcome', $payload);

            if ($i < 10) {
                $response->assertStatus(201);
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }
    });
});

describe('Race Outcome Recording', function () {
    test('can record race outcome with valid data', function () {
        $payload = [
            'career_run_id' => 123,
            'race_id' => 15,
            'strategy' => [
                'recommended_style' => 'escape',
                'reasoning' => 'A-grade Escape aptitude, sufficient stamina (650 vs 600 requirement)',
                'win_probability' => 0.78,
                'readiness_assessment' => [
                    'stamina' => 'sufficient',
                    'speed' => 'excellent',
                    'power' => 'good',
                    'overall' => 'ready',
                ],
                'risks' => ['B-grade turf aptitude may reduce effectiveness by 5-10%'],
                'preparation_checklist' => ['✓ Stamina requirement met', '✓ Speed above 800'],
                'predicted_outcomes' => [],
                'model_version' => 'v1.0',
            ],
            'actual_result' => [
                'placement' => 1,
                'total_competitors' => 18,
                'running_style' => 'escape',
                'was_win' => true,
                'was_placed' => true,
                'finish_time' => 125.5,
                'fan_gain' => 5000,
            ],
        ];

        $response = $this->postJson('/api/advisory/race/outcome', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Race outcome recorded successfully',
                'data' => [
                    'career_run_id' => 123,
                    'race_id' => 15,
                    'placement' => 1,
                    'placement_ordinal' => '1st',
                    'result_quality' => 'win',
                ],
            ]);
    });

    test('validates required fields for race outcome', function () {
        $response = $this->postJson('/api/advisory/race/outcome', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'career_run_id',
                'race_id',
                'strategy',
                'actual_result',
            ]);
    });

    test('validates running style is valid', function () {
        $payload = [
            'career_run_id' => 123,
            'race_id' => 15,
            'strategy' => [
                'recommended_style' => 'invalid_style',
                'reasoning' => 'Test reasoning',
                'win_probability' => 0.78,
                'readiness_assessment' => ['overall' => 'ready'],
            ],
            'actual_result' => [
                'placement' => 1,
                'total_competitors' => 18,
                'running_style' => 'escape',
            ],
        ];

        $response = $this->postJson('/api/advisory/race/outcome', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['strategy.recommended_style']);
    });

    test('validates win_probability is between 0 and 1', function () {
        $payload = [
            'career_run_id' => 123,
            'race_id' => 15,
            'strategy' => [
                'recommended_style' => 'escape',
                'reasoning' => 'Test reasoning',
                'win_probability' => 1.5, // Exceeds max of 1.0
                'readiness_assessment' => ['overall' => 'ready'],
            ],
            'actual_result' => [
                'placement' => 1,
                'total_competitors' => 18,
                'running_style' => 'escape',
            ],
        ];

        $response = $this->postJson('/api/advisory/race/outcome', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['strategy.win_probability']);
    });

    test('validates placement is within valid range', function () {
        $payload = [
            'career_run_id' => 123,
            'race_id' => 15,
            'strategy' => [
                'recommended_style' => 'escape',
                'reasoning' => 'Test reasoning',
                'win_probability' => 0.78,
                'readiness_assessment' => ['overall' => 'ready'],
            ],
            'actual_result' => [
                'placement' => 20, // Exceeds max of 18
                'total_competitors' => 18,
                'running_style' => 'escape',
            ],
        ];

        $response = $this->postJson('/api/advisory/race/outcome', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['actual_result.placement']);
    });

    test('can record race outcome with loss', function () {
        $payload = [
            'career_run_id' => 123,
            'race_id' => 15,
            'strategy' => [
                'recommended_style' => 'escape',
                'reasoning' => 'Test reasoning',
                'win_probability' => 0.45,
                'readiness_assessment' => ['overall' => 'fair'],
            ],
            'actual_result' => [
                'placement' => 8,
                'total_competitors' => 18,
                'running_style' => 'escape',
                'was_win' => false,
                'was_placed' => false,
                'finish_time' => 130.2,
                'fan_gain' => 1000,
            ],
        ];

        $response = $this->postJson('/api/advisory/race/outcome', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'placement' => 8,
                    'placement_ordinal' => '8th',
                    'result_quality' => 'loss',
                ],
            ]);
    });

    test('can record race outcome with placed but not win', function () {
        $payload = [
            'career_run_id' => 123,
            'race_id' => 15,
            'strategy' => [
                'recommended_style' => 'escape',
                'reasoning' => 'Test reasoning',
                'win_probability' => 0.65,
                'readiness_assessment' => ['overall' => 'ready'],
            ],
            'actual_result' => [
                'placement' => 2,
                'total_competitors' => 18,
                'running_style' => 'escape',
                'was_win' => false,
                'was_placed' => true,
                'finish_time' => 126.1,
                'fan_gain' => 4000,
            ],
        ];

        $response = $this->postJson('/api/advisory/race/outcome', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'placement' => 2,
                    'placement_ordinal' => '2nd',
                    'result_quality' => 'placed',
                ],
            ]);
    });

    test('requires authentication', function () {
        $this->app['auth']->forgetGuards();

        $payload = [
            'career_run_id' => 123,
            'race_id' => 15,
            'strategy' => [
                'recommended_style' => 'escape',
                'reasoning' => 'Test reasoning',
                'win_probability' => 0.78,
                'readiness_assessment' => ['overall' => 'ready'],
            ],
            'actual_result' => [
                'placement' => 1,
                'total_competitors' => 18,
                'running_style' => 'escape',
            ],
        ];

        $response = $this->postJson('/api/advisory/race/outcome', $payload);

        $response->assertStatus(401);
    });

    test('respects rate limiting', function () {
        $payload = [
            'career_run_id' => 123,
            'race_id' => 15,
            'strategy' => [
                'recommended_style' => 'escape',
                'reasoning' => 'Test reasoning',
                'win_probability' => 0.78,
                'readiness_assessment' => ['overall' => 'ready'],
            ],
            'actual_result' => [
                'placement' => 1,
                'total_competitors' => 18,
                'running_style' => 'escape',
            ],
        ];

        // Make 11 requests (rate limit is 10 per minute)
        for ($i = 0; $i < 11; $i++) {
            $response = $this->postJson('/api/advisory/race/outcome', $payload);

            if ($i < 10) {
                $response->assertStatus(201);
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }
    });
});
