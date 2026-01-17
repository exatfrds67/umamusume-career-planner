<?php

use App\Models\Character;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->character = Character::factory()->create([
        'user_id' => $this->user->id,
        'scenario_type' => 'ura_finale',
        'current_stats' => [
            'speed' => 500,
            'stamina' => 450,
            'power' => 400,
            'guts' => 420,
            'wit' => 380,
        ],
        'energy_level' => 80,
        'mood_status' => 'good',
    ]);
});

it('returns batch training predictions for all training types', function () {
    $response = $this->postJson('/api/training-predictions/batch', [
        'character_id' => $this->character->id,
        'training_types' => ['speed', 'stamina', 'power', 'guts', 'wit'],
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
                    'cached',
                    'processing_time_ms',
                ],
            ],
        ]);

    expect($response->json('data'))->toHaveCount(5);
});

it('includes recommendations when requested', function () {
    $response = $this->postJson('/api/training-predictions/batch', [
        'character_id' => $this->character->id,
        'training_types' => ['speed', 'stamina', 'power'],
        'include_recommendations' => true,
    ]);

    $response->assertSuccessful();

    $data = $response->json('data');
    $hasRecommendation = collect($data)->contains(fn ($item) => isset($item['recommendation']['is_recommended']) && $item['recommendation']['is_recommended']);

    expect($hasRecommendation)->toBeTrue();
});

it('caches predictions for performance', function () {
    Cache::flush();

    // First request - not cached
    $response1 = $this->postJson('/api/training-predictions/batch', [
        'character_id' => $this->character->id,
        'training_types' => ['speed'],
    ]);

    expect($response1->json('data.0.cached'))->toBeFalse();

    // Second request - should be cached
    $response2 = $this->postJson('/api/training-predictions/batch', [
        'character_id' => $this->character->id,
        'training_types' => ['speed'],
    ]);

    expect($response2->json('data.0.cached'))->toBeTrue();
});

it('validates required character_id', function () {
    $response = $this->postJson('/api/training-predictions/batch', [
        'training_types' => ['speed'],
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['character_id']);
});

it('validates training types are valid', function () {
    $response = $this->postJson('/api/training-predictions/batch', [
        'character_id' => $this->character->id,
        'training_types' => ['invalid_type'],
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['training_types.0']);
});

it('returns single training prediction', function () {
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
                'breakdown' => [
                    'base_gains',
                    'support_card_bonus',
                    'friendship_multiplier',
                    'facility_bonus',
                    'growth_rate_bonus',
                    'total_multiplier',
                ],
            ],
        ]);
});

it('returns training recommendation with reasoning', function () {
    $response = $this->postJson('/api/training-predictions/recommend', [
        'character_id' => $this->character->id,
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
        ]);
});

it('clears cache for character', function () {
    // Create some cached predictions
    $this->postJson('/api/training-predictions/batch', [
        'character_id' => $this->character->id,
        'training_types' => ['speed'],
    ]);

    $response = $this->deleteJson("/api/training-predictions/cache/{$this->character->id}");

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'message' => 'Training prediction cache cleared successfully',
        ]);
});

it('returns cache statistics', function () {
    $response = $this->getJson("/api/training-predictions/cache/{$this->character->id}/stats");

    $response->assertSuccessful()
        ->assertJsonStructure([
            'success',
            'data' => [
                'character_id',
                'cache_enabled',
                'cache_driver',
                'cache_ttl',
            ],
        ]);
});
