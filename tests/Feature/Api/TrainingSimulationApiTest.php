<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TrainingSimulationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_simulation_requires_authentication(): void
    {
        $character = Character::factory()->create();

        $response = $this->postJson('/api/training-predictions/simulate', [
            'character_id' => $character->id,
            'training_type' => 'speed',
        ]);

        $response->assertUnauthorized();
    }

    public function test_it_returns_authoritative_training_simulation_data(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'energy_level' => 72,
            'mood_status' => 'normal',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/training-predictions/simulate', [
            'character_id' => $character->id,
            'training_type' => 'speed',
        ]);

        $response->assertSuccessful()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'training_type',
                    'stat_gains',
                    'skill_points',
                    'energy_change',
                    'energy_after',
                    'failure_risk',
                    'failure_percent',
                    'risk_level',
                ],
                'message',
            ]);

        expect($response->json('data.training_type'))->toBe('speed');
        expect($response->json('data.risk_level'))->toBeIn(['low', 'medium', 'high']);
        expect($response->json('data.failure_percent'))->toBeInt();
    }

    public function test_it_simulates_rest_preview(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->create([
            'user_id' => $user->id,
            'energy_level' => 40,
            'mood_status' => 'bad',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/training-predictions/simulate', [
            'character_id' => $character->id,
            'training_type' => 'rest',
        ]);

        $response->assertSuccessful()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.training_type', 'rest')
            ->assertJsonPath('data.failure_percent', 0)
            ->assertJsonPath('data.risk_level', 'low');

        expect($response->json('data.energy_change'))->toBe(50);
        expect($response->json('data.energy_after'))->toBe(90);
    }

    public function test_it_rejects_simulation_for_character_owned_by_another_user(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $character = Character::factory()->create([
            'user_id' => $owner->id,
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->postJson('/api/training-predictions/simulate', [
            'character_id' => $character->id,
            'training_type' => 'speed',
        ]);

        $response->assertForbidden();
    }

    public function test_it_validates_training_type_for_simulation(): void
    {
        $user = User::factory()->create();
        $character = Character::factory()->create([
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/training-predictions/simulate', [
            'character_id' => $character->id,
            'training_type' => 'friendship',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['training_type']);
    }
}
