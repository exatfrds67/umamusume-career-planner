<?php

declare(strict_types=1);

use App\Models\Career;
use App\Models\Character;
use App\Models\Race;
use App\Models\TrainingSession;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);
});

describe('Career API Endpoints', function (): void {
    describe('GET /api/v1/careers', function (): void {
        it('returns list of careers for authenticated user', function (): void {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            Career::factory()->count(3)->create(['character_id' => $character->id]);

            $response = $this->getJson('/api/v1/careers');

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'data' => [
                        '*' => ['id', 'character_id', 'scenario_type', 'status'],
                    ],
                ]);
        });

        it('returns 401 for unauthenticated request', function (): void {
            // Clear any existing authentication set by beforeEach
            $this->app['auth']->forgetGuards();

            $response = $this->getJson('/api/v1/careers');

            $response->assertUnauthorized();
        });

        it('paginates results', function (): void {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            Career::factory()->count(25)->create(['character_id' => $character->id]);

            $response = $this->getJson('/api/v1/careers?per_page=10');

            $response->assertSuccessful()
                ->assertJsonCount(10, 'data');
        });

        it('filters by status', function (): void {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            Career::factory()->count(3)->create([
                'character_id' => $character->id,
                'status' => 'active',
            ]);
            Career::factory()->count(2)->create([
                'character_id' => $character->id,
                'status' => 'completed',
            ]);

            $response = $this->getJson('/api/v1/careers?status=active');

            $response->assertSuccessful()
                ->assertJsonCount(3, 'data');
        });
    });

    describe('POST /api/v1/careers', function (): void {
        it('creates a new career', function (): void {
            $character = Character::factory()->create(['user_id' => $this->user->id]);

            $response = $this->postJson('/api/v1/careers', [
                'character_id' => $character->id,
                'scenario_type' => 'ura_finale',
            ]);

            $response->assertCreated()
                ->assertJsonStructure([
                    'data' => ['id', 'character_id', 'scenario_type', 'status'],
                ]);
        });

        it('validates required fields', function (): void {
            $response = $this->postJson('/api/v1/careers', []);

            $response->assertUnprocessable()
                ->assertJsonValidationErrors(['character_id', 'scenario_type']);
        });

        it('validates scenario type', function (): void {
            $character = Character::factory()->create(['user_id' => $this->user->id]);

            $response = $this->postJson('/api/v1/careers', [
                'character_id' => $character->id,
                'scenario_type' => 'invalid_scenario',
            ]);

            $response->assertUnprocessable()
                ->assertJsonValidationErrors(['scenario_type']);
        });
    });

    describe('GET /api/v1/careers/{id}', function (): void {
        it('returns career details', function (): void {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);

            $response = $this->getJson("/api/v1/careers/{$career->id}");

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'data' => ['id', 'character_id', 'scenario_type', 'status', 'current_turn'],
                ]);
        });

        it('returns 404 for non-existent career', function (): void {
            $response = $this->getJson('/api/v1/careers/99999');

            $response->assertNotFound();
        });

        it('returns 403 for career belonging to another user', function (): void {
            $otherUser = User::factory()->create();
            $character = Character::factory()->create(['user_id' => $otherUser->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);

            $response = $this->getJson("/api/v1/careers/{$career->id}");

            $response->assertForbidden();
        });
    });

    describe('PUT /api/v1/careers/{id}', function (): void {
        it('updates career status', function (): void {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create([
                'character_id' => $character->id,
                'status' => 'active',
            ]);

            $response = $this->putJson("/api/v1/careers/{$career->id}", [
                'status' => 'completed',
            ]);

            $response->assertSuccessful();

            $career->refresh();
            expect($career->status)->toBe('completed');
        });
    });

    describe('DELETE /api/v1/careers/{id}', function (): void {
        it('deletes a career', function (): void {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);

            $response = $this->deleteJson("/api/v1/careers/{$career->id}");

            $response->assertSuccessful();

            expect(Career::find($career->id))->toBeNull();
        });
    });

    describe('GET /api/v1/careers/{id}/training-sessions', function (): void {
        it('returns training sessions for career', function (): void {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);
            TrainingSession::factory()->count(10)->create(['career_id' => $career->id]);

            $response = $this->getJson("/api/v1/careers/{$career->id}/training-sessions");

            $response->assertSuccessful()
                ->assertJsonCount(10, 'data');
        });
    });

    describe('GET /api/v1/careers/{id}/races', function (): void {
        it('returns races for career', function (): void {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);
            Race::factory()->count(5)->create(['career_id' => $career->id]);

            $response = $this->getJson("/api/v1/careers/{$career->id}/races");

            $response->assertSuccessful()
                ->assertJsonCount(5, 'data');
        });
    });

    describe('GET /api/v1/careers/{id}/statistics', function (): void {
        it('returns career statistics', function (): void {
            $character = Character::factory()->create(['user_id' => $this->user->id]);
            $career = Career::factory()->create(['character_id' => $character->id]);

            TrainingSession::factory()->count(20)->create([
                'career_id' => $career->id,
                'speed_gain' => 10,
            ]);

            $response = $this->getJson("/api/v1/careers/{$career->id}/statistics");

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'data' => [
                        'total_training_sessions',
                        'total_stat_gains',
                        'efficiency_rating',
                    ],
                ]);
        });
    });
});
