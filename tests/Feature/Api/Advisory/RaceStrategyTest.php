<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\TrainingAdvisoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user, 'sanctum');

    // Mock the TrainingAdvisoryService to avoid AWS credentials issues
    $this->mock(TrainingAdvisoryService::class, function (MockInterface $mock) {
        // The getRaceStrategy endpoint doesn't use the service, but we need to mock it
        // to prevent the dependency chain from trying to initialize AWS Bedrock
    });
});

describe('POST /api/advisory/race/strategy', function () {
    it('returns race strategy for valid request', function () {
        $response = $this->postJson('/api/advisory/race/strategy', [
            'character_id' => 1,
            'race_id' => 15,
            'stats' => [
                'speed' => 850,
                'stamina' => 650,
                'power' => 720,
                'guts' => 580,
                'wisdom' => 690,
            ],
            'skills' => [1, 5, 12, 23],
            'aptitudes' => [
                'distance_medium' => 'A',
                'surface_turf' => 'B',
                'style_escape' => 'A',
                'style_lead' => 'B',
                'style_pace' => 'C',
                'style_chase' => 'C',
            ],
            'race_details' => [
                'distance' => 'medium',
                'distance_meters' => 2000,
                'surface' => 'turf',
                'weather' => 'clear',
                'track_condition' => 'good',
                'competition_level' => 'G3',
            ],
        ]);

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'strategy' => [
                'recommended_style',
                'reasoning',
                'win_probability',
                'readiness_assessment' => [
                    'stamina',
                    'speed',
                    'power',
                    'overall',
                ],
                'risks',
                'preparation_checklist',
            ],
            'response_time_ms',
            'ai_provider',
        ]);
    });

    it('returns strategy with minimal required fields', function () {
        $response = $this->postJson('/api/advisory/race/strategy', [
            'character_id' => 1,
            'race_id' => 1,
            'stats' => [
                'speed' => 500,
                'stamina' => 400,
                'power' => 450,
                'guts' => 350,
                'wisdom' => 400,
            ],
            'aptitudes' => [
                'style_escape' => 'C',
            ],
        ]);

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'strategy' => [
                'recommended_style',
                'reasoning',
                'win_probability',
                'readiness_assessment',
                'risks',
                'preparation_checklist',
            ],
            'response_time_ms',
            'ai_provider',
        ]);
    });

    it('validates character_id is required', function () {
        $response = $this->postJson('/api/advisory/race/strategy', [
            'race_id' => 15,
            'stats' => [
                'speed' => 850,
                'stamina' => 650,
                'power' => 720,
                'guts' => 580,
                'wisdom' => 690,
            ],
            'aptitudes' => [
                'style_escape' => 'A',
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['character_id']);
    });

    it('validates race_id is required', function () {
        $response = $this->postJson('/api/advisory/race/strategy', [
            'character_id' => 1,
            'stats' => [
                'speed' => 850,
                'stamina' => 650,
                'power' => 720,
                'guts' => 580,
                'wisdom' => 690,
            ],
            'aptitudes' => [
                'style_escape' => 'A',
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['race_id']);
    });

    it('validates all stats are required', function () {
        $response = $this->postJson('/api/advisory/race/strategy', [
            'character_id' => 1,
            'race_id' => 15,
            'stats' => [
                'speed' => 850,
                // Missing stamina, power, guts, wisdom
            ],
            'aptitudes' => [
                'style_escape' => 'A',
            ],
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
        $response = $this->postJson('/api/advisory/race/strategy', [
            'character_id' => 1,
            'race_id' => 15,
            'stats' => [
                'speed' => -10, // Invalid: negative
                'stamina' => 2000, // Invalid: exceeds max
                'power' => 720,
                'guts' => 580,
                'wisdom' => 690,
            ],
            'aptitudes' => [
                'style_escape' => 'A',
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['stats.speed', 'stats.stamina']);
    });

    it('uses default aptitudes when not provided', function () {
        $response = $this->postJson('/api/advisory/race/strategy', [
            'character_id' => 1,
            'race_id' => 15,
            'stats' => [
                'speed' => 850,
                'stamina' => 650,
                'power' => 720,
                'guts' => 580,
                'wisdom' => 690,
            ],
        ]);

        // Should succeed with default aptitudes (all C grade)
        $response->assertSuccessful();
        $data = $response->json();

        // Default aptitudes should result in a valid strategy
        expect($data['strategy'])->toHaveKey('recommended_style');
    });

    it('validates aptitude grades must be valid', function () {
        $response = $this->postJson('/api/advisory/race/strategy', [
            'character_id' => 1,
            'race_id' => 15,
            'stats' => [
                'speed' => 850,
                'stamina' => 650,
                'power' => 720,
                'guts' => 580,
                'wisdom' => 690,
            ],
            'aptitudes' => [
                'style_escape' => 'X', // Invalid grade
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['aptitudes.style_escape']);
    });

    it('validates race_details distance must be valid', function () {
        $response = $this->postJson('/api/advisory/race/strategy', [
            'character_id' => 1,
            'race_id' => 15,
            'stats' => [
                'speed' => 850,
                'stamina' => 650,
                'power' => 720,
                'guts' => 580,
                'wisdom' => 690,
            ],
            'aptitudes' => [
                'style_escape' => 'A',
            ],
            'race_details' => [
                'distance' => 'invalid_distance',
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['race_details.distance']);
    });

    it('validates race_details surface must be valid', function () {
        $response = $this->postJson('/api/advisory/race/strategy', [
            'character_id' => 1,
            'race_id' => 15,
            'stats' => [
                'speed' => 850,
                'stamina' => 650,
                'power' => 720,
                'guts' => 580,
                'wisdom' => 690,
            ],
            'aptitudes' => [
                'style_escape' => 'A',
            ],
            'race_details' => [
                'surface' => 'grass', // Invalid: should be turf or dirt
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['race_details.surface']);
    });

    it('validates race_details track_condition must be valid', function () {
        $response = $this->postJson('/api/advisory/race/strategy', [
            'character_id' => 1,
            'race_id' => 15,
            'stats' => [
                'speed' => 850,
                'stamina' => 650,
                'power' => 720,
                'guts' => 580,
                'wisdom' => 690,
            ],
            'aptitudes' => [
                'style_escape' => 'A',
            ],
            'race_details' => [
                'track_condition' => 'wet', // Invalid
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['race_details.track_condition']);
    });

    it('validates race_details competition_level must be valid', function () {
        $response = $this->postJson('/api/advisory/race/strategy', [
            'character_id' => 1,
            'race_id' => 15,
            'stats' => [
                'speed' => 850,
                'stamina' => 650,
                'power' => 720,
                'guts' => 580,
                'wisdom' => 690,
            ],
            'aptitudes' => [
                'style_escape' => 'A',
            ],
            'race_details' => [
                'competition_level' => 'G4', // Invalid
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['race_details.competition_level']);
    });

    it('validates skills must be an array of integers', function () {
        $response = $this->postJson('/api/advisory/race/strategy', [
            'character_id' => 1,
            'race_id' => 15,
            'stats' => [
                'speed' => 850,
                'stamina' => 650,
                'power' => 720,
                'guts' => 580,
                'wisdom' => 690,
            ],
            'skills' => ['invalid', 'skills'],
            'aptitudes' => [
                'style_escape' => 'A',
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['skills.0', 'skills.1']);
    });

    it('requires authentication', function () {
        // Create a new test without authentication
        $this->app['auth']->forgetGuards();

        $response = $this->postJson('/api/advisory/race/strategy', [
            'character_id' => 1,
            'race_id' => 15,
            'stats' => [
                'speed' => 850,
                'stamina' => 650,
                'power' => 720,
                'guts' => 580,
                'wisdom' => 690,
            ],
            'aptitudes' => [
                'style_escape' => 'A',
            ],
        ]);

        $response->assertUnauthorized();
    });

    it('accepts UUID character_id', function () {
        $response = $this->postJson('/api/advisory/race/strategy', [
            'character_id' => 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
            'race_id' => 15,
            'stats' => [
                'speed' => 850,
                'stamina' => 650,
                'power' => 720,
                'guts' => 580,
                'wisdom' => 690,
            ],
            'aptitudes' => [
                'style_escape' => 'A',
            ],
        ]);

        $response->assertSuccessful();
    });

    it('returns response within acceptable time bounds', function () {
        $startTime = microtime(true);

        $response = $this->postJson('/api/advisory/race/strategy', [
            'character_id' => 1,
            'race_id' => 15,
            'stats' => [
                'speed' => 850,
                'stamina' => 650,
                'power' => 720,
                'guts' => 580,
                'wisdom' => 690,
            ],
            'aptitudes' => [
                'style_escape' => 'A',
            ],
        ]);

        $duration = microtime(true) - $startTime;

        $response->assertSuccessful();

        // Response should include response_time_ms
        $response->assertJsonStructure(['response_time_ms']);

        // Rule-based should be fast (< 2 seconds)
        expect($duration)->toBeLessThan(2.0);
    });

    it('recommends escape style when escape aptitude is highest', function () {
        $response = $this->postJson('/api/advisory/race/strategy', [
            'character_id' => 1,
            'race_id' => 15,
            'stats' => [
                'speed' => 850,
                'stamina' => 650,
                'power' => 720,
                'guts' => 580,
                'wisdom' => 690,
            ],
            'aptitudes' => [
                'style_escape' => 'S',
                'style_lead' => 'B',
                'style_pace' => 'C',
                'style_chase' => 'D',
            ],
        ]);

        $response->assertSuccessful();
        $data = $response->json();

        expect($data['strategy']['recommended_style'])->toBe('escape');
    });

    it('recommends chase style when chase aptitude is highest', function () {
        $response = $this->postJson('/api/advisory/race/strategy', [
            'character_id' => 1,
            'race_id' => 15,
            'stats' => [
                'speed' => 850,
                'stamina' => 650,
                'power' => 720,
                'guts' => 580,
                'wisdom' => 690,
            ],
            'aptitudes' => [
                'style_escape' => 'C',
                'style_lead' => 'B',
                'style_pace' => 'B',
                'style_chase' => 'S',
            ],
        ]);

        $response->assertSuccessful();
        $data = $response->json();

        expect($data['strategy']['recommended_style'])->toBe('chase');
    });

    it('returns higher win probability for better stats', function () {
        // Low stats request
        $lowStatsResponse = $this->postJson('/api/advisory/race/strategy', [
            'character_id' => 1,
            'race_id' => 15,
            'stats' => [
                'speed' => 400,
                'stamina' => 300,
                'power' => 350,
                'guts' => 300,
                'wisdom' => 350,
            ],
            'aptitudes' => [
                'style_escape' => 'C',
            ],
        ]);

        // High stats request
        $highStatsResponse = $this->postJson('/api/advisory/race/strategy', [
            'character_id' => 1,
            'race_id' => 15,
            'stats' => [
                'speed' => 1100,
                'stamina' => 900,
                'power' => 950,
                'guts' => 800,
                'wisdom' => 850,
            ],
            'aptitudes' => [
                'style_escape' => 'A',
            ],
        ]);

        $lowStatsResponse->assertSuccessful();
        $highStatsResponse->assertSuccessful();

        $lowData = $lowStatsResponse->json();
        $highData = $highStatsResponse->json();

        expect($highData['strategy']['win_probability'])
            ->toBeGreaterThan($lowData['strategy']['win_probability']);
    });

    it('identifies stamina risk when stamina is insufficient', function () {
        $response = $this->postJson('/api/advisory/race/strategy', [
            'character_id' => 1,
            'race_id' => 15,
            'stats' => [
                'speed' => 850,
                'stamina' => 300, // Low stamina for medium distance
                'power' => 720,
                'guts' => 580,
                'wisdom' => 690,
            ],
            'aptitudes' => [
                'style_escape' => 'A',
            ],
            'race_details' => [
                'distance' => 'medium',
            ],
        ]);

        $response->assertSuccessful();
        $data = $response->json();

        // Should have stamina-related risk
        $hasStaminaRisk = collect($data['strategy']['risks'])
            ->contains(fn ($risk) => str_contains(strtolower($risk), 'stamina'));

        expect($hasStaminaRisk)->toBeTrue();
    });

    it('identifies surface aptitude risk when aptitude is low', function () {
        $response = $this->postJson('/api/advisory/race/strategy', [
            'character_id' => 1,
            'race_id' => 15,
            'stats' => [
                'speed' => 850,
                'stamina' => 650,
                'power' => 720,
                'guts' => 580,
                'wisdom' => 690,
            ],
            'aptitudes' => [
                'style_escape' => 'A',
                'surface_turf' => 'F', // Low turf aptitude
            ],
            'race_details' => [
                'surface' => 'turf',
            ],
        ]);

        $response->assertSuccessful();
        $data = $response->json();

        // Should have surface-related risk
        $hasSurfaceRisk = collect($data['strategy']['risks'])
            ->contains(fn ($risk) => str_contains(strtolower($risk), 'turf'));

        expect($hasSurfaceRisk)->toBeTrue();
    });

    it('applies track condition penalty for heavy track', function () {
        // Good track condition
        $goodTrackResponse = $this->postJson('/api/advisory/race/strategy', [
            'character_id' => 1,
            'race_id' => 15,
            'stats' => [
                'speed' => 850,
                'stamina' => 650,
                'power' => 720,
                'guts' => 580,
                'wisdom' => 690,
            ],
            'aptitudes' => [
                'style_escape' => 'A',
            ],
            'race_details' => [
                'track_condition' => 'good',
            ],
        ]);

        // Heavy track condition
        $heavyTrackResponse = $this->postJson('/api/advisory/race/strategy', [
            'character_id' => 1,
            'race_id' => 15,
            'stats' => [
                'speed' => 850,
                'stamina' => 650,
                'power' => 720,
                'guts' => 580,
                'wisdom' => 690,
            ],
            'aptitudes' => [
                'style_escape' => 'A',
            ],
            'race_details' => [
                'track_condition' => 'heavy',
            ],
        ]);

        $goodTrackResponse->assertSuccessful();
        $heavyTrackResponse->assertSuccessful();

        $goodData = $goodTrackResponse->json();
        $heavyData = $heavyTrackResponse->json();

        // Heavy track should have lower win probability
        expect($heavyData['strategy']['win_probability'])
            ->toBeLessThan($goodData['strategy']['win_probability']);
    });

    it('generates preparation checklist with stamina check', function () {
        $response = $this->postJson('/api/advisory/race/strategy', [
            'character_id' => 1,
            'race_id' => 15,
            'stats' => [
                'speed' => 850,
                'stamina' => 650,
                'power' => 720,
                'guts' => 580,
                'wisdom' => 690,
            ],
            'aptitudes' => [
                'style_escape' => 'A',
            ],
        ]);

        $response->assertSuccessful();
        $data = $response->json();

        // Should have preparation checklist
        expect($data['strategy']['preparation_checklist'])->toBeArray();
        expect($data['strategy']['preparation_checklist'])->not->toBeEmpty();

        // Should have stamina-related checklist item
        $hasStaminaCheck = collect($data['strategy']['preparation_checklist'])
            ->contains(fn ($item) => str_contains(strtolower($item), 'stamina'));

        expect($hasStaminaCheck)->toBeTrue();
    });

    it('handles all valid aptitude grades', function () {
        $grades = ['G', 'F', 'E', 'D', 'C', 'B', 'A', 'S'];

        foreach ($grades as $grade) {
            $response = $this->postJson('/api/advisory/race/strategy', [
                'character_id' => 1,
                'race_id' => 15,
                'stats' => [
                    'speed' => 850,
                    'stamina' => 650,
                    'power' => 720,
                    'guts' => 580,
                    'wisdom' => 690,
                ],
                'aptitudes' => [
                    'style_escape' => $grade,
                ],
            ]);

            $response->assertSuccessful();
        }
    });

    it('handles all valid distance categories', function () {
        $distances = ['sprint', 'mile', 'medium', 'long'];

        foreach ($distances as $distance) {
            $response = $this->postJson('/api/advisory/race/strategy', [
                'character_id' => 1,
                'race_id' => 15,
                'stats' => [
                    'speed' => 850,
                    'stamina' => 650,
                    'power' => 720,
                    'guts' => 580,
                    'wisdom' => 690,
                ],
                'aptitudes' => [
                    'style_escape' => 'A',
                ],
                'race_details' => [
                    'distance' => $distance,
                ],
            ]);

            $response->assertSuccessful();
        }
    });

    it('handles all valid competition levels', function () {
        $levels = ['G1', 'G2', 'G3', 'OP', 'Pre-OP', 'Debut'];

        foreach ($levels as $level) {
            $response = $this->postJson('/api/advisory/race/strategy', [
                'character_id' => 1,
                'race_id' => 15,
                'stats' => [
                    'speed' => 850,
                    'stamina' => 650,
                    'power' => 720,
                    'guts' => 580,
                    'wisdom' => 690,
                ],
                'aptitudes' => [
                    'style_escape' => 'A',
                ],
                'race_details' => [
                    'competition_level' => $level,
                ],
            ]);

            $response->assertSuccessful();
        }
    });

    it('returns readiness assessment with overall status', function () {
        $response = $this->postJson('/api/advisory/race/strategy', [
            'character_id' => 1,
            'race_id' => 15,
            'stats' => [
                'speed' => 850,
                'stamina' => 650,
                'power' => 720,
                'guts' => 580,
                'wisdom' => 690,
            ],
            'aptitudes' => [
                'style_escape' => 'A',
            ],
        ]);

        $response->assertSuccessful();
        $data = $response->json();

        expect($data['strategy']['readiness_assessment'])->toHaveKeys([
            'stamina',
            'speed',
            'power',
            'overall',
        ]);

        // Overall should be one of the valid statuses
        $validStatuses = ['ready', 'mostly_ready', 'needs_preparation', 'not_ready'];
        expect($validStatuses)->toContain($data['strategy']['readiness_assessment']['overall']);
    });

    it('returns win probability between 0 and 1', function () {
        $response = $this->postJson('/api/advisory/race/strategy', [
            'character_id' => 1,
            'race_id' => 15,
            'stats' => [
                'speed' => 850,
                'stamina' => 650,
                'power' => 720,
                'guts' => 580,
                'wisdom' => 690,
            ],
            'aptitudes' => [
                'style_escape' => 'A',
            ],
        ]);

        $response->assertSuccessful();
        $data = $response->json();

        expect($data['strategy']['win_probability'])->toBeGreaterThanOrEqual(0);
        expect($data['strategy']['win_probability'])->toBeLessThanOrEqual(1);
    });

    it('returns ai_provider in response', function () {
        $response = $this->postJson('/api/advisory/race/strategy', [
            'character_id' => 1,
            'race_id' => 15,
            'stats' => [
                'speed' => 850,
                'stamina' => 650,
                'power' => 720,
                'guts' => 580,
                'wisdom' => 690,
            ],
            'aptitudes' => [
                'style_escape' => 'A',
            ],
        ]);

        $response->assertSuccessful();
        $data = $response->json();

        expect($data)->toHaveKey('ai_provider');
        expect($data['ai_provider'])->toBe('rule-based');
    });
});
