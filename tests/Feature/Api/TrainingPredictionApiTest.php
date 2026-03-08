<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Training Prediction API Test
 *
 * Tests the training prediction API endpoints with authentication,
 * validation, and error handling.
 */
class TrainingPredictionApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Character $character;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create();

        // Create test character
        $this->character = Character::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Test Character',
            'scenario_type' => 'unity_cup',
            'current_stats' => [
                'speed' => 500,
                'stamina' => 500,
                'power' => 500,
                'guts' => 500,
                'wit' => 500,
            ],
            'growth_rates' => [
                'speed' => 10,
                'stamina' => 10,
                'power' => 10,
                'guts' => 10,
                'wit' => 10,
            ],
            'mood_status' => 'normal',
            'energy_level' => 100,
        ]);
    }

    /** @test */
    public function it_requires_authentication_for_predictions(): void
    {
        $response = $this->postJson('/api/training-predictions', [
            'character_id' => $this->character->id,
            'training_type' => 'speed',
        ]);

        $response->assertUnauthorized();
    }

    /** @test */
    public function it_requires_authentication_for_batch_predictions(): void
    {
        $response = $this->postJson('/api/training-predictions/batch', [
            'character_id' => $this->character->id,
            'training_types' => ['speed', 'stamina'],
        ]);

        $response->assertUnauthorized();
    }

    /** @test */
    public function it_validates_character_ownership(): void
    {
        // Create another user's character
        $otherUser = User::factory()->create();
        $otherCharacter = Character::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/training-predictions', [
            'character_id' => $otherCharacter->id,
            'training_type' => 'speed',
        ]);

        $response->assertForbidden();
    }

    /** @test */
    public function it_validates_required_fields(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/training-predictions', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['character_id', 'training_type']);
    }

    /** @test */
    public function it_validates_training_type(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/training-predictions', [
            'character_id' => $this->character->id,
            'training_type' => 'invalid_type',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['training_type']);
    }

    /** @test */
    public function it_returns_training_prediction_successfully(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/training-predictions', [
            'character_id' => $this->character->id,
            'training_type' => 'speed',
        ]);

        $response->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    'training_type',
                    'stat_gains',
                    'energy_cost',
                    'failure_risk',
                    'total_bonus',
                    'wit_adequacy' => [
                        'wit_value',
                        'activation_chance',
                        'status',
                        'status_text',
                        'threshold_met',
                    ],
                    'friendship_training' => [
                        'is_active',
                        'multiplier_base',
                        'status_text',
                        'cards_at_threshold',
                        'total_cards_at_threshold',
                        'cards_needing_bond',
                        'estimated_turns_until_active',
                    ],
                    'breakdown' => [
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
                    ],
                    'scenario_specific',
                    'meta' => [
                        'cached',
                        'cache_ttl',
                        'processing_time_ms',
                        'timestamp',
                    ],
                ],
                'success',
                'message',
            ]);

        expect($response->json('data.training_type'))->toBe('speed');
        expect($response->json('data.stat_gains'))->toBeArray();
        expect($response->json('data.energy_cost'))->toBeInt();
        expect($response->json('data.failure_risk'))->toBeFloat();
        expect($response->json('data.wit_adequacy.wit_value'))->toBe(500);
        expect($response->json('data.wit_adequacy.status'))->toBe('reliable');
        expect($response->json('data.wit_adequacy.threshold_met'))->toBeTrue();
    }

    /** @test */
    public function it_returns_batch_predictions_successfully(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/training-predictions/batch', [
            'character_id' => $this->character->id,
            'training_types' => ['speed', 'stamina', 'power'],
        ]);

        $response->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'training_type',
                        'stat_gains',
                        'energy_cost',
                        'failure_risk',
                        'total_bonus',
                        'breakdown',
                        'scenario_specific',
                        'meta',
                    ],
                ],
            ]);

        expect($response->json('data'))->toHaveCount(3);
    }

    /** @test */
    public function it_validates_batch_training_types(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/training-predictions/batch', [
            'character_id' => $this->character->id,
            'training_types' => [], // Empty array
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['training_types']);
    }

    /** @test */
    public function it_limits_batch_training_types_to_five(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/training-predictions/batch', [
            'character_id' => $this->character->id,
            'training_types' => ['speed', 'stamina', 'power', 'guts', 'wit', 'extra'], // 6 types
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['training_types']);
    }

    /** @test */
    public function it_includes_recommendations_when_requested(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/training-predictions/batch', [
            'character_id' => $this->character->id,
            'training_types' => ['speed', 'stamina', 'power'],
            'include_recommendations' => true,
        ]);

        $response->assertSuccessful();

        $data = $response->json('data');
        foreach ($data as $prediction) {
            expect($prediction)->toHaveKey('recommendation');
            expect($prediction['recommendation'])->toHaveKeys(['is_recommended', 'rank']);
        }
    }

    /** @test */
    public function it_returns_training_recommendation_successfully(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/training-predictions/recommend', [
            'character_id' => $this->character->id,
            'goal_stats' => [
                'speed' => 800,
                'stamina' => 700,
            ],
        ]);

        $response->assertSuccessful()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'recommended_training',
                    'reason',
                    'prediction',
                    'alternatives',
                ],
                'message',
            ]);

        expect($response->json('data.recommended_training'))->toBeString();
        expect($response->json('data.reason'))->toBeString();
    }

    /** @test */
    public function it_validates_goal_stats_range(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/training-predictions/recommend', [
            'character_id' => $this->character->id,
            'goal_stats' => [
                'speed' => 1500, // Exceeds max of 1200
            ],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['goal_stats.speed']);
    }

    /** @test */
    public function it_validates_turns_remaining_range(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/training-predictions/recommend', [
            'character_id' => $this->character->id,
            'turns_remaining' => 100, // Exceeds max of 70
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['turns_remaining']);
    }

    /** @test */
    public function it_caches_predictions(): void
    {
        Sanctum::actingAs($this->user);

        // First request - not cached
        $response1 = $this->postJson('/api/training-predictions', [
            'character_id' => $this->character->id,
            'training_type' => 'speed',
        ]);

        $response1->assertSuccessful();
        expect($response1->json('data.meta.cached'))->toBeFalse();

        // Second request - should be cached
        $response2 = $this->postJson('/api/training-predictions', [
            'character_id' => $this->character->id,
            'training_type' => 'speed',
        ]);

        $response2->assertSuccessful();
        expect($response2->json('data.meta.cached'))->toBeTrue();
    }

    /** @test */
    public function it_clears_cache_for_character(): void
    {
        Sanctum::actingAs($this->user);

        // Make a prediction to populate cache
        $this->postJson('/api/training-predictions', [
            'character_id' => $this->character->id,
            'training_type' => 'speed',
        ]);

        // Clear cache
        $response = $this->deleteJson("/api/training-predictions/cache/{$this->character->id}");

        $response->assertSuccessful()
            ->assertJson([
                'success' => true,
                'message' => 'Training prediction cache cleared successfully',
            ]);
    }

    /** @test */
    public function it_returns_cache_statistics(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson("/api/training-predictions/cache/{$this->character->id}/stats");

        $response->assertSuccessful()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'character_id',
                    'cache_enabled',
                    'cache_driver',
                    'cache_ttl',
                    'cache_prefix',
                    'timestamp',
                ],
                'message',
            ]);
    }

    /** @test */
    public function it_handles_non_existent_character_gracefully(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/training-predictions', [
            'character_id' => 99999, // Non-existent ID
            'training_type' => 'speed',
        ]);

        $response->assertForbidden(); // Authorization fails before model lookup
    }

    /** @test */
    public function it_validates_participants_range(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/training-predictions', [
            'character_id' => $this->character->id,
            'training_type' => 'speed',
            'participants' => 10, // Exceeds max of 6
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['participants']);
    }

    /** @test */
    public function it_validates_spirit_burst_gauge_range(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/training-predictions', [
            'character_id' => $this->character->id,
            'training_type' => 'speed',
            'spirit_burst_gauge' => 5, // Exceeds max of 4
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['spirit_burst_gauge']);
    }

    /** @test */
    public function it_includes_scenario_specific_mechanics(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/training-predictions', [
            'character_id' => $this->character->id,
            'training_type' => 'speed',
        ]);

        $response->assertSuccessful();

        $scenarioSpecific = $response->json('data.scenario_specific');
        expect($scenarioSpecific)->toBeArray();
        expect($scenarioSpecific)->toHaveKey('scenario');
        expect($scenarioSpecific['scenario'])->toBe('unity_cup');
    }
}
