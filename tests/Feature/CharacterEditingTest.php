<?php

use App\Models\Character;
use App\Models\User;

test('user can view edit page for their character', function () {
    $user = User::factory()->create();
    $character = Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Test Character',
    ]);

    $response = $this->actingAs($user)->get(route('characters.edit', $character));

    $response->assertSuccessful();
    $response->assertSee('Edit Test Character');
    $response->assertSee('Update character stats, goals, and tracking information');
});

test('user can update character basic information', function () {
    $user = User::factory()->create();
    $character = Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Original Name',
        'career_stage' => 'junior',
        'current_turn' => 1,
    ]);

    $response = $this->actingAs($user)->put(route('characters.update', $character), [
        'name' => 'Updated Name',
        'career_stage' => 'classic',
        'current_turn' => 25,
    ]);

    $response->assertRedirect(route('characters.show', $character));
    $response->assertSessionHas('success', 'Character updated successfully!');

    $character->refresh();
    expect($character->name)->toBe('Updated Name');
    expect($character->career_stage)->toBe('classic');
    expect($character->current_turn)->toBe(25);
});

test('user can update character stats', function () {
    $user = User::factory()->create();
    $character = Character::factory()->create([
        'user_id' => $user->id,
        'current_stats' => [
            'speed' => 100,
            'stamina' => 100,
            'power' => 100,
            'guts' => 100,
            'wit' => 100,
        ],
    ]);

    $response = $this->actingAs($user)->put(route('characters.update', $character), [
        'stats' => [
            'speed' => 500,
            'stamina' => 600,
            'power' => 400,
            'guts' => 300,
            'wit' => 450,
        ],
    ]);

    $response->assertRedirect(route('characters.show', $character));

    $character->refresh();
    expect($character->current_stats['speed'])->toBe(500);
    expect($character->current_stats['stamina'])->toBe(600);
    expect($character->current_stats['power'])->toBe(400);
});

test('user can update character goals', function () {
    $user = User::factory()->create();
    $character = Character::factory()->create([
        'user_id' => $user->id,
        'goals' => [],
    ]);

    $response = $this->actingAs($user)->put(route('characters.update', $character), [
        'goals' => [
            'target_stats' => [
                'speed' => 1000,
                'stamina' => 900,
                'power' => 800,
                'guts' => 600,
                'wit' => 700,
            ],
            'notes' => 'Focus on speed and stamina for long-distance races',
        ],
    ]);

    $response->assertRedirect(route('characters.show', $character));

    $character->refresh();
    expect($character->goals['target_stats']['speed'])->toBe(1000);
    expect($character->goals['notes'])->toBe('Focus on speed and stamina for long-distance races');
});

test('user can update energy and mood', function () {
    $user = User::factory()->create();
    $character = Character::factory()->create([
        'user_id' => $user->id,
        'energy_level' => 100,
        'mood_status' => 'normal',
    ]);

    $response = $this->actingAs($user)->put(route('characters.update', $character), [
        'energy_level' => 75,
        'mood_status' => 'good',
    ]);

    $response->assertRedirect(route('characters.show', $character));

    $character->refresh();
    expect($character->energy_level)->toBe(75);
    expect($character->mood_status)->toBe('good');
});

test('stat values must be between 0 and 1200', function () {
    $user = User::factory()->create();
    $character = Character::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->put(route('characters.update', $character), [
        'stats' => [
            'speed' => 1500, // Invalid: exceeds max
            'stamina' => 600,
            'power' => 400,
            'guts' => 300,
            'wit' => 450,
        ],
    ]);

    $response->assertSessionHasErrors('stats.speed');
});

test('energy level must be between 0 and 100', function () {
    $user = User::factory()->create();
    $character = Character::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->put(route('characters.update', $character), [
        'energy_level' => 150, // Invalid: exceeds max
    ]);

    $response->assertSessionHasErrors('energy_level');
});

test('user cannot edit another users character', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $character = Character::factory()->create(['user_id' => $user1->id]);

    $response = $this->actingAs($user2)->put(route('characters.update', $character), [
        'name' => 'Hacked Name',
    ]);

    $response->assertForbidden();
});

test('user can delete their character', function () {
    $user = User::factory()->create();
    $character = Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Character to Delete',
    ]);

    $characterId = $character->id;

    $response = $this->actingAs($user)->delete(route('characters.destroy', $character));

    $response->assertRedirect(route('characters.index'));
    $response->assertSessionHas('success', 'Character deleted successfully.');

    expect(Character::find($characterId))->toBeNull();
});

test('user cannot delete another users character', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $character = Character::factory()->create(['user_id' => $user1->id]);

    $response = $this->actingAs($user2)->delete(route('characters.destroy', $character));

    $response->assertForbidden();

    expect(Character::find($character->id))->not->toBeNull();
});

test('character notes can be up to 5000 characters', function () {
    $user = User::factory()->create();
    $character = Character::factory()->create(['user_id' => $user->id]);

    $longNotes = str_repeat('a', 5000);

    $response = $this->actingAs($user)->put(route('characters.update', $character), [
        'goals' => [
            'notes' => $longNotes,
        ],
    ]);

    $response->assertRedirect(route('characters.show', $character));

    $character->refresh();
    expect(strlen($character->goals['notes']))->toBe(5000);
});

test('character notes cannot exceed 5000 characters', function () {
    $user = User::factory()->create();
    $character = Character::factory()->create(['user_id' => $user->id]);

    $tooLongNotes = str_repeat('a', 5001);

    $response = $this->actingAs($user)->put(route('characters.update', $character), [
        'goals' => [
            'notes' => $tooLongNotes,
        ],
    ]);

    $response->assertSessionHasErrors('goals.notes');
});
