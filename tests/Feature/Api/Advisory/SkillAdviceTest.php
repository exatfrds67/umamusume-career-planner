<?php

declare(strict_types=1);

use App\Collections\SkillRecommendationCollection;
use App\Enums\Priority;
use App\Enums\RecommendationType;
use App\Models\User;
use App\Services\TrainingAdvisoryService;
use App\ValueObjects\Recommendation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user, 'sanctum');

    // Disable throttle middleware for all tests in this file
    $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);

    // Create and bind the mock service using Laravel's mock helper
    $this->mock(TrainingAdvisoryService::class, function ($mock) {
        $mock->shouldReceive('getSkillPurchaseAdvice')
            ->andReturn(new SkillRecommendationCollection([
                new Recommendation(
                    type: RecommendationType::SKILL_PURCHASE,
                    priority: Priority::HIGH,
                    action: 'Purchase Swinging Maestro',
                    reasoning: 'Gold stamina recovery skill with Level 3 hint (30% discount). Reduces stamina requirements by 150-200.',
                    expectedOutcomes: [
                        'skill_id' => 23,
                        'sp_cost' => 126,
                        'expected_impact' => 'Enables Medium/Long distance races with lower stamina investment',
                    ],
                    risks: [],
                    confidenceScore: 0.88,
                ),
            ]));
    });
});

describe('POST /api/advisory/skills/advice', function () {
    it('returns skill purchase advice for valid request', function () {
        $response = $this->postJson('/api/advisory/skills/advice', [
            'character_id' => 1,
            'storage_mode' => 'account',
            'sp_available' => 220,
            'acquired_skills' => [1, 5, 12],
            'available_skills' => [
                [
                    'id' => 23,
                    'name' => 'Swinging Maestro',
                    'tier' => 'gold',
                    'base_cost' => 180,
                    'hint_level' => 3,
                    'category' => 'stamina_recovery',
                ],
                [
                    'id' => 45,
                    'name' => 'Lane Legerdemain',
                    'tier' => 'rare',
                    'base_cost' => 120,
                    'hint_level' => 2,
                    'category' => 'positioning',
                ],
            ],
        ]);

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'recommendations',
            'sp_budget_analysis' => [
                'current',
                'recommended_spend',
                'remaining',
                'projected_total',
            ],
            'response_time_ms',
            'ai_provider',
        ]);
    });

    it('returns recommendations with minimal required fields', function () {
        $response = $this->postJson('/api/advisory/skills/advice', [
            'character_id' => 1,
            'storage_mode' => 'local',
            'sp_available' => 100,
            'available_skills' => [
                [
                    'id' => 1,
                    'name' => 'Basic Skill',
                    'tier' => 'normal',
                    'base_cost' => 50,
                    'hint_level' => 0,
                ],
            ],
        ]);

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'recommendations',
            'sp_budget_analysis',
            'response_time_ms',
            'ai_provider',
        ]);
    });

    it('validates character_id is required', function () {
        $response = $this->postJson('/api/advisory/skills/advice', [
            'storage_mode' => 'account',
            'sp_available' => 220,
            'available_skills' => [
                [
                    'id' => 23,
                    'name' => 'Test Skill',
                    'tier' => 'gold',
                    'base_cost' => 180,
                    'hint_level' => 3,
                ],
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['character_id']);
    });

    it('validates storage_mode must be local or account', function () {
        $response = $this->postJson('/api/advisory/skills/advice', [
            'character_id' => 1,
            'storage_mode' => 'invalid',
            'sp_available' => 220,
            'available_skills' => [
                [
                    'id' => 23,
                    'name' => 'Test Skill',
                    'tier' => 'gold',
                    'base_cost' => 180,
                    'hint_level' => 3,
                ],
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['storage_mode']);
    });

    it('validates sp_available is required', function () {
        $response = $this->postJson('/api/advisory/skills/advice', [
            'character_id' => 1,
            'storage_mode' => 'account',
            'available_skills' => [
                [
                    'id' => 23,
                    'name' => 'Test Skill',
                    'tier' => 'gold',
                    'base_cost' => 180,
                    'hint_level' => 3,
                ],
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['sp_available']);
    });

    it('validates sp_available is within valid range', function () {
        // Test negative SP
        $response = $this->postJson('/api/advisory/skills/advice', [
            'character_id' => 1,
            'storage_mode' => 'account',
            'sp_available' => -10,
            'available_skills' => [
                [
                    'id' => 23,
                    'name' => 'Test Skill',
                    'tier' => 'gold',
                    'base_cost' => 180,
                    'hint_level' => 3,
                ],
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['sp_available']);

        // Test SP exceeding max
        $response = $this->postJson('/api/advisory/skills/advice', [
            'character_id' => 1,
            'storage_mode' => 'account',
            'sp_available' => 10000,
            'available_skills' => [
                [
                    'id' => 23,
                    'name' => 'Test Skill',
                    'tier' => 'gold',
                    'base_cost' => 180,
                    'hint_level' => 3,
                ],
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['sp_available']);
    });

    it('validates available_skills is required', function () {
        $response = $this->postJson('/api/advisory/skills/advice', [
            'character_id' => 1,
            'storage_mode' => 'account',
            'sp_available' => 220,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['available_skills']);
    });

    it('validates available_skills must have at least one skill', function () {
        $response = $this->postJson('/api/advisory/skills/advice', [
            'character_id' => 1,
            'storage_mode' => 'account',
            'sp_available' => 220,
            'available_skills' => [],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['available_skills']);
    });

    it('validates available_skills have required fields', function () {
        $response = $this->postJson('/api/advisory/skills/advice', [
            'character_id' => 1,
            'storage_mode' => 'account',
            'sp_available' => 220,
            'available_skills' => [
                [
                    'id' => 23,
                    // Missing name, tier, base_cost, hint_level
                ],
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'available_skills.0.name',
            'available_skills.0.tier',
            'available_skills.0.base_cost',
            'available_skills.0.hint_level',
        ]);
    });

    it('validates skill tier must be valid', function () {
        $response = $this->postJson('/api/advisory/skills/advice', [
            'character_id' => 1,
            'storage_mode' => 'account',
            'sp_available' => 220,
            'available_skills' => [
                [
                    'id' => 23,
                    'name' => 'Test Skill',
                    'tier' => 'invalid_tier',
                    'base_cost' => 180,
                    'hint_level' => 3,
                ],
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['available_skills.0.tier']);
    });

    it('validates hint_level is within valid range', function () {
        // Test negative hint level
        $response = $this->postJson('/api/advisory/skills/advice', [
            'character_id' => 1,
            'storage_mode' => 'account',
            'sp_available' => 220,
            'available_skills' => [
                [
                    'id' => 23,
                    'name' => 'Test Skill',
                    'tier' => 'gold',
                    'base_cost' => 180,
                    'hint_level' => -1,
                ],
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['available_skills.0.hint_level']);

        // Test hint level exceeding max
        $response = $this->postJson('/api/advisory/skills/advice', [
            'character_id' => 1,
            'storage_mode' => 'account',
            'sp_available' => 220,
            'available_skills' => [
                [
                    'id' => 23,
                    'name' => 'Test Skill',
                    'tier' => 'gold',
                    'base_cost' => 180,
                    'hint_level' => 6,
                ],
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['available_skills.0.hint_level']);
    });

    it('validates base_cost is within valid range', function () {
        // Test zero base cost
        $response = $this->postJson('/api/advisory/skills/advice', [
            'character_id' => 1,
            'storage_mode' => 'account',
            'sp_available' => 220,
            'available_skills' => [
                [
                    'id' => 23,
                    'name' => 'Test Skill',
                    'tier' => 'gold',
                    'base_cost' => 0,
                    'hint_level' => 3,
                ],
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['available_skills.0.base_cost']);

        // Test base cost exceeding max
        $response = $this->postJson('/api/advisory/skills/advice', [
            'character_id' => 1,
            'storage_mode' => 'account',
            'sp_available' => 220,
            'available_skills' => [
                [
                    'id' => 23,
                    'name' => 'Test Skill',
                    'tier' => 'gold',
                    'base_cost' => 1500,
                    'hint_level' => 3,
                ],
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['available_skills.0.base_cost']);
    });

    it('validates skill category must be valid when provided', function () {
        $response = $this->postJson('/api/advisory/skills/advice', [
            'character_id' => 1,
            'storage_mode' => 'account',
            'sp_available' => 220,
            'available_skills' => [
                [
                    'id' => 23,
                    'name' => 'Test Skill',
                    'tier' => 'gold',
                    'base_cost' => 180,
                    'hint_level' => 3,
                    'category' => 'invalid_category',
                ],
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['available_skills.0.category']);
    });

    it('validates target_distance must be valid when provided', function () {
        $response = $this->postJson('/api/advisory/skills/advice', [
            'character_id' => 1,
            'storage_mode' => 'account',
            'sp_available' => 220,
            'available_skills' => [
                [
                    'id' => 23,
                    'name' => 'Test Skill',
                    'tier' => 'gold',
                    'base_cost' => 180,
                    'hint_level' => 3,
                ],
            ],
            'target_distance' => 'invalid_distance',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['target_distance']);
    });

    it('validates running_style must be valid when provided', function () {
        $response = $this->postJson('/api/advisory/skills/advice', [
            'character_id' => 1,
            'storage_mode' => 'account',
            'sp_available' => 220,
            'available_skills' => [
                [
                    'id' => 23,
                    'name' => 'Test Skill',
                    'tier' => 'gold',
                    'base_cost' => 180,
                    'hint_level' => 3,
                ],
            ],
            'running_style' => 'invalid_style',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['running_style']);
    });

    it('requires authentication', function () {
        // Create a new test without authentication
        $this->app['auth']->forgetGuards();

        $response = $this->postJson('/api/advisory/skills/advice', [
            'character_id' => 1,
            'storage_mode' => 'account',
            'sp_available' => 220,
            'available_skills' => [
                [
                    'id' => 23,
                    'name' => 'Test Skill',
                    'tier' => 'gold',
                    'base_cost' => 180,
                    'hint_level' => 3,
                ],
            ],
        ]);

        $response->assertUnauthorized();
    });

    it('accepts local storage mode with UUID character_id', function () {
        $response = $this->postJson('/api/advisory/skills/advice', [
            'character_id' => 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
            'storage_mode' => 'local',
            'sp_available' => 220,
            'available_skills' => [
                [
                    'id' => 23,
                    'name' => 'Test Skill',
                    'tier' => 'gold',
                    'base_cost' => 180,
                    'hint_level' => 3,
                ],
            ],
        ]);

        $response->assertSuccessful();
    });

    it('returns response within acceptable time bounds', function () {
        $startTime = microtime(true);

        $response = $this->postJson('/api/advisory/skills/advice', [
            'character_id' => 1,
            'storage_mode' => 'account',
            'sp_available' => 220,
            'available_skills' => [
                [
                    'id' => 23,
                    'name' => 'Test Skill',
                    'tier' => 'gold',
                    'base_cost' => 180,
                    'hint_level' => 3,
                ],
            ],
        ]);

        $duration = microtime(true) - $startTime;

        $response->assertSuccessful();

        // Response should include response_time_ms
        $response->assertJsonStructure(['response_time_ms']);

        // Rule-based fallback should be fast (< 2 seconds)
        expect($duration)->toBeLessThan(2.0);
    });

    it('returns recommendations with expected structure', function () {
        $response = $this->postJson('/api/advisory/skills/advice', [
            'character_id' => 1,
            'storage_mode' => 'account',
            'sp_available' => 220,
            'available_skills' => [
                [
                    'id' => 23,
                    'name' => 'Swinging Maestro',
                    'tier' => 'gold',
                    'base_cost' => 180,
                    'hint_level' => 3,
                    'category' => 'stamina_recovery',
                ],
            ],
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
                'skill_id',
                'priority',
                'action',
                'reasoning',
                'sp_cost',
                'sp_remaining',
                'expected_impact',
            ]);
        }
    });

    it('handles all valid skill tiers', function () {
        $tiers = ['normal', 'rare', 'gold', 'unique', 'evolution'];

        foreach ($tiers as $tier) {
            $response = $this->postJson('/api/advisory/skills/advice', [
                'character_id' => 1,
                'storage_mode' => 'account',
                'sp_available' => 220,
                'available_skills' => [
                    [
                        'id' => 23,
                        'name' => 'Test Skill',
                        'tier' => $tier,
                        'base_cost' => 180,
                        'hint_level' => 3,
                    ],
                ],
            ]);

            $response->assertSuccessful();
        }
    });

    it('handles all valid skill categories', function () {
        $categories = [
            'speed',
            'stamina',
            'power',
            'guts',
            'wisdom',
            'stamina_recovery',
            'positioning',
            'acceleration',
            'lane_change',
            'pace_control',
            'mental',
            'general',
        ];

        foreach ($categories as $category) {
            $response = $this->postJson('/api/advisory/skills/advice', [
                'character_id' => 1,
                'storage_mode' => 'account',
                'sp_available' => 220,
                'available_skills' => [
                    [
                        'id' => 23,
                        'name' => 'Test Skill',
                        'tier' => 'gold',
                        'base_cost' => 180,
                        'hint_level' => 3,
                        'category' => $category,
                    ],
                ],
            ]);

            $response->assertSuccessful();
        }
    });

    it('handles all valid target distances', function () {
        $distances = ['sprint', 'mile', 'medium', 'long'];

        foreach ($distances as $distance) {
            $response = $this->postJson('/api/advisory/skills/advice', [
                'character_id' => 1,
                'storage_mode' => 'account',
                'sp_available' => 220,
                'available_skills' => [
                    [
                        'id' => 23,
                        'name' => 'Test Skill',
                        'tier' => 'gold',
                        'base_cost' => 180,
                        'hint_level' => 3,
                    ],
                ],
                'target_distance' => $distance,
            ]);

            $response->assertSuccessful();
        }
    });

    it('handles all valid running styles', function () {
        $styles = ['escape', 'lead', 'pace', 'chase'];

        foreach ($styles as $style) {
            $response = $this->postJson('/api/advisory/skills/advice', [
                'character_id' => 1,
                'storage_mode' => 'account',
                'sp_available' => 220,
                'available_skills' => [
                    [
                        'id' => 23,
                        'name' => 'Test Skill',
                        'tier' => 'gold',
                        'base_cost' => 180,
                        'hint_level' => 3,
                    ],
                ],
                'running_style' => $style,
            ]);

            $response->assertSuccessful();
        }
    });

    it('handles multiple available skills', function () {
        $response = $this->postJson('/api/advisory/skills/advice', [
            'character_id' => 1,
            'storage_mode' => 'account',
            'sp_available' => 500,
            'available_skills' => [
                [
                    'id' => 1,
                    'name' => 'Skill One',
                    'tier' => 'normal',
                    'base_cost' => 50,
                    'hint_level' => 1,
                ],
                [
                    'id' => 2,
                    'name' => 'Skill Two',
                    'tier' => 'rare',
                    'base_cost' => 100,
                    'hint_level' => 2,
                ],
                [
                    'id' => 3,
                    'name' => 'Skill Three',
                    'tier' => 'gold',
                    'base_cost' => 180,
                    'hint_level' => 3,
                ],
                [
                    'id' => 4,
                    'name' => 'Skill Four',
                    'tier' => 'unique',
                    'base_cost' => 200,
                    'hint_level' => 4,
                ],
                [
                    'id' => 5,
                    'name' => 'Skill Five',
                    'tier' => 'evolution',
                    'base_cost' => 250,
                    'hint_level' => 5,
                ],
            ],
        ]);

        $response->assertSuccessful();
    });

    it('returns SP budget analysis with correct structure', function () {
        $response = $this->postJson('/api/advisory/skills/advice', [
            'character_id' => 1,
            'storage_mode' => 'account',
            'sp_available' => 220,
            'available_skills' => [
                [
                    'id' => 23,
                    'name' => 'Test Skill',
                    'tier' => 'gold',
                    'base_cost' => 180,
                    'hint_level' => 3,
                ],
            ],
        ]);

        $response->assertSuccessful();

        $data = $response->json();

        expect($data)->toHaveKey('sp_budget_analysis');
        expect($data['sp_budget_analysis'])->toHaveKeys([
            'current',
            'recommended_spend',
            'remaining',
            'projected_total',
        ]);

        // Verify current SP matches input
        expect($data['sp_budget_analysis']['current'])->toBe(220);
    });

    it('handles acquired_skills array correctly', function () {
        $response = $this->postJson('/api/advisory/skills/advice', [
            'character_id' => 1,
            'storage_mode' => 'account',
            'sp_available' => 220,
            'acquired_skills' => [1, 5, 12, 23, 45],
            'available_skills' => [
                [
                    'id' => 100,
                    'name' => 'New Skill',
                    'tier' => 'gold',
                    'base_cost' => 180,
                    'hint_level' => 3,
                ],
            ],
        ]);

        $response->assertSuccessful();
    });

    it('validates acquired_skills contains valid integers', function () {
        $response = $this->postJson('/api/advisory/skills/advice', [
            'character_id' => 1,
            'storage_mode' => 'account',
            'sp_available' => 220,
            'acquired_skills' => ['invalid', 0, -1],
            'available_skills' => [
                [
                    'id' => 23,
                    'name' => 'Test Skill',
                    'tier' => 'gold',
                    'base_cost' => 180,
                    'hint_level' => 3,
                ],
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'acquired_skills.0',
            'acquired_skills.2',
        ]);
    });
});
