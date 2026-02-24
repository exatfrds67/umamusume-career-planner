<?php

use App\Models\Career;
use App\Models\Character;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->character = Character::factory()->create([
        'user_id' => $this->user->id,
    ]);
    $this->career = Career::factory()->create([
        'character_id' => $this->character->id,
        'user_id' => $this->user->id,
        'status' => 'active',
        'current_turn' => 5,
        'current_phase' => 'junior',
    ]);
});

it('lists careers for authenticated user', function () {
    $response = $this->actingAs($this->user)
        ->getJson('/api/v1/careers');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data',
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ]);
});

it('filters careers by status', function () {
    Career::factory()->create([
        'character_id' => $this->character->id,
        'user_id' => $this->user->id,
        'status' => 'completed',
    ]);

    $response = $this->actingAs($this->user)
        ->getJson('/api/v1/careers?status=active');

    $response->assertSuccessful();

    $data = $response->json('data');
    foreach ($data as $career) {
        expect($career['status'])->toBe('active');
    }
});

it('creates a new career', function () {
    $response = $this->actingAs($this->user)
        ->postJson('/api/v1/careers', [
            'character_id' => $this->character->id,
            'scenario_type' => 'ura_finale',
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.status', 'active')
        ->assertJsonPath('data.current_turn', 1)
        ->assertJsonPath('data.current_phase', 'junior');
});

it('rejects career creation with invalid scenario type', function () {
    $response = $this->actingAs($this->user)
        ->postJson('/api/v1/careers', [
            'character_id' => $this->character->id,
            'scenario_type' => 'invalid_type',
        ]);

    $response->assertUnprocessable();
});

it('shows a specific career with character data', function () {
    $response = $this->actingAs($this->user)
        ->getJson("/api/v1/careers/{$this->career->id}");

    $response->assertSuccessful()
        ->assertJsonPath('data.id', $this->career->id);
});

it('updates a career', function () {
    $response = $this->actingAs($this->user)
        ->putJson("/api/v1/careers/{$this->career->id}", [
            'status' => 'completed',
            'current_turn' => 78,
            'current_phase' => 'senior',
        ]);

    $response->assertSuccessful();
});

it('deletes a career', function () {
    $response = $this->actingAs($this->user)
        ->deleteJson("/api/v1/careers/{$this->career->id}");

    $response->assertSuccessful();
    $this->assertDatabaseMissing('ucp_careers', ['id' => $this->career->id]);
});

it('returns available races for a career', function () {
    $response = $this->actingAs($this->user)
        ->getJson("/api/v1/careers/{$this->career->id}/available-races");

    $response->assertSuccessful()
        ->assertJsonStructure(['data']);
});

it('returns training predictions for a career', function () {
    $response = $this->actingAs($this->user)
        ->getJson("/api/v1/careers/{$this->career->id}/training-predictions");

    $response->assertSuccessful()
        ->assertJsonStructure(['data']);
});

it('stores a training session', function () {
    $response = $this->actingAs($this->user)
        ->postJson("/api/v1/careers/{$this->career->id}/training-sessions", [
            'training_type' => 'speed',
            'turn_number' => 5,
        ]);

    $response->assertSuccessful();
});

it('stores bulk training sessions', function () {
    $sessions = [
        ['training_type' => 'speed', 'turn_number' => 1],
        ['training_type' => 'stamina', 'turn_number' => 2],
    ];

    $response = $this->actingAs($this->user)
        ->postJson("/api/v1/careers/{$this->career->id}/training-sessions/bulk", [
            'sessions' => $sessions,
        ]);

    $response->assertSuccessful();
});

it('stores a race entry', function () {
    $response = $this->actingAs($this->user)
        ->postJson("/api/v1/careers/{$this->career->id}/races", [
            'race_name' => 'Japan Cup',
            'race_grade' => 'G1',
            'turn_number' => 10,
        ]);

    $response->assertSuccessful();
});

it('lists races for a career', function () {
    $response = $this->actingAs($this->user)
        ->getJson("/api/v1/careers/{$this->career->id}/races");

    $response->assertSuccessful()
        ->assertJsonStructure(['data']);
});

it('lists training sessions for a career', function () {
    $response = $this->actingAs($this->user)
        ->getJson("/api/v1/careers/{$this->career->id}/training-sessions");

    $response->assertSuccessful()
        ->assertJsonStructure(['data']);
});

it('generates a career report', function () {
    $response = $this->actingAs($this->user)
        ->getJson("/api/v1/careers/{$this->career->id}/report");

    $response->assertSuccessful()
        ->assertJsonStructure(['data']);
});

it('returns career statistics', function () {
    $response = $this->actingAs($this->user)
        ->getJson("/api/v1/careers/{$this->career->id}/statistics");

    $response->assertSuccessful()
        ->assertJsonStructure(['data']);
});

it('compares multiple careers', function () {
    $career2 = Career::factory()->create([
        'character_id' => $this->character->id,
        'user_id' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson('/api/v1/careers/compare', [
            'career_ids' => [$this->career->id, $career2->id],
        ]);

    $response->assertSuccessful();
});

it('identifies success patterns', function () {
    $career2 = Career::factory()->create([
        'character_id' => $this->character->id,
        'user_id' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson('/api/v1/careers/patterns', [
            'career_ids' => [$this->career->id, $career2->id],
        ]);

    $response->assertSuccessful();
});

it('returns career recommendations', function () {
    $response = $this->actingAs($this->user)
        ->postJson('/api/v1/careers/recommendations', [
            'career_ids' => [$this->career->id],
        ]);

    $response->assertSuccessful();
});

it('prevents unauthorized access to another user career', function () {
    $otherUser = User::factory()->create();

    $response = $this->actingAs($otherUser)
        ->getJson("/api/v1/careers/{$this->career->id}");

    $response->assertForbidden();
});

it('returns 401 for unauthenticated requests', function () {
    $response = $this->getJson('/api/v1/careers');

    $response->assertUnauthorized();
});
