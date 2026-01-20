<?php

use App\Models\Character;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

uses()->group('character-api');

beforeEach(function () {
    // Create a user and authenticate via Sanctum
    $this->user = User::factory()->create();
    Sanctum::actingAs($this->user);
    // Ensure a character exists for GET/PUT/DELETE tests
    $this->character = Character::factory()->create([
        'user_id' => $this->user->id,
    ]);
});

it('lists characters for the authenticated user', function () {
    $response = getJson('/api/characters');
    $response->assertOk();
    $response->assertJsonFragment(['id' => $this->character->id]);
});

it('creates a new character', function () {
    $payload = [
        'name' => 'Test Character',
        'user_id' => $this->user->id,
        'scenario_type' => 'ura_finale',
        'current_stats' => [
            'speed' => 120,
            'stamina' => 120,
            'power' => 120,
            'guts' => 120,
            'wit' => 120,
        ],
        'stat_priorities' => [
            'speed' => 5,
            'stamina' => 4,
            'power' => 3,
            'guts' => 2,
            'wit' => 1,
        ],
    ];
    $response = postJson('/api/characters', $payload);
    $response->assertCreated();
    $this->assertDatabaseHas('ucp_characters', ['name' => 'Test Character']);
});

it('shows a specific character', function () {
    $response = getJson('/api/characters/'.$this->character->id);
    $response->assertOk();
    $response->assertJsonFragment(['id' => $this->character->id]);
});

it('updates a character', function () {
    $payload = ['name' => 'Updated Name'];
    $response = putJson('/api/characters/'.$this->character->id, $payload);
    $response->assertOk();
    $this->assertDatabaseHas('ucp_characters', ['id' => $this->character->id, 'name' => 'Updated Name']);
});

it('deletes a character', function () {
    $response = deleteJson('/api/characters/'.$this->character->id);
    $response->assertNoContent();
    $this->assertDatabaseMissing('ucp_characters', ['id' => $this->character->id]);
});
