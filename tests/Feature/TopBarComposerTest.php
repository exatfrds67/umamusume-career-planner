<?php

use App\Models\Character;
use App\Models\User;

it('provides topStatus data to dashboard when user has characters', function () {
    $user = User::factory()->create();
    $character = Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Test Runner',
        'status' => 'active',
        'current_turn' => 25,
        'energy_level' => 80,
        'mood_status' => 'good',
        'career_stage' => 'classic',
        'available_sp' => 150,
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSuccessful();
    $response->assertViewHas('topStatus');
    $response->assertViewHas('activeCharacters');
    $response->assertViewHas('currentCharacter');
});

it('provides empty data when user has no characters', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSuccessful();
    $response->assertViewHas('activeCharacters');
});

it('allows selecting a character via session', function () {
    $user = User::factory()->create();
    $character = Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Mejiro Ardan',
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->post(route('characters.select', $character));

    $response->assertRedirect();
    $response->assertSessionHas('current_character_id', $character->id);
    $response->assertSessionHas('success');
});

it('uses session-selected character for topStatus', function () {
    $user = User::factory()->create();
    $first = Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'First Character',
        'status' => 'active',
        'current_turn' => 10,
    ]);
    $second = Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Second Character',
        'status' => 'active',
        'current_turn' => 40,
    ]);

    // Select the second character
    $this->actingAs($user)->post(route('characters.select', $second));

    $response = $this->actingAs($user)
        ->withSession(['current_character_id' => $second->id])
        ->get(route('dashboard'));

    $response->assertSuccessful();
    $response->assertViewHas('currentCharacter', function ($char) use ($second) {
        return $char->id === $second->id;
    });
});

it('shows real character names in run selector', function () {
    $user = User::factory()->create();
    Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Special Week',
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSuccessful();
    $response->assertSee('Special Week');
});

it('wires critical alert count to header badge', function () {
    $user = User::factory()->create();
    $character = Character::factory()->create([
        'user_id' => $user->id,
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSuccessful();
    $response->assertViewHas('criticalAlertCount');
});

it('provides topStatus on non-dashboard pages', function () {
    $user = User::factory()->create();
    Character::factory()->create([
        'user_id' => $user->id,
        'status' => 'active',
        'current_turn' => 15,
        'energy_level' => 60,
    ]);

    $response = $this->actingAs($user)->get(route('characters.index'));

    $response->assertSuccessful();
    $response->assertViewHas('activeCharacters');
});

it('returns search results for characters', function () {
    $user = User::factory()->create();
    Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Mejiro Ardan',
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->getJson('/api/search?q=Mejiro');

    $response->assertSuccessful();
    $response->assertJsonPath('results.0.name', 'Mejiro Ardan');
    $response->assertJsonPath('results.0.type', 'Character');
});

it('returns empty results for short queries', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson('/api/search?q=a');

    $response->assertSuccessful();
    $response->assertJsonPath('results', []);
});
