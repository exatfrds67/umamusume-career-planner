<?php

use App\Models\Character;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('dashboard loads successfully with no characters', function () {
    $response = $this->get(route('dashboard'));

    $response->assertSuccessful();
    $response->assertViewIs('dashboard');
    $response->assertViewHas('hasCharacters', false);
});

test('dashboard loads successfully with character', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $response = $this->get(route('dashboard'));

    $response->assertSuccessful();
    $response->assertViewIs('dashboard');
    $response->assertViewHas('hasCharacters', true);
    $response->assertViewHas('selectedCharacter');
});

test('dashboard handles character with string race_schedule', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'race_schedule' => json_encode([
            ['name' => 'Test Race', 'turn' => 10, 'grade' => 'G1'],
        ]),
    ]);

    $response = $this->get(route('dashboard'));

    $response->assertSuccessful();
});

test('dashboard handles character with invalid race_schedule', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'race_schedule' => 'invalid json string',
    ]);

    $response = $this->get(route('dashboard'));

    $response->assertSuccessful();
});

test('dashboard handles character with null race_schedule', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'race_schedule' => null,
    ]);

    $response = $this->get(route('dashboard'));

    $response->assertSuccessful();
});

test('dashboard handles character with string current_stats', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'current_stats' => json_encode([
            'speed' => 500,
            'stamina' => 400,
            'power' => 400,
            'guts' => 300,
            'wit' => 300,
        ]),
    ]);

    $response = $this->get(route('dashboard'));

    $response->assertSuccessful();
});

test('dashboard handles character with string goals', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'goals' => json_encode([
            'target_grade' => 'A+',
            'target_skills' => 12,
        ]),
    ]);

    $response = $this->get(route('dashboard'));

    $response->assertSuccessful();
});
