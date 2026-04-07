<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\User;

it('renders training detail view with character metadata', function () {
    $user = User::factory()->create();
    $character = Character::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get(route('training.predictions.show', $character));

    $response->assertSuccessful();
    $response->assertSee($character->name, false);
    $response->assertSee('data-character-id="'.$character->id.'"', false);
    $response->assertSee('data-scenario-type="'.$character->scenario_type.'"', false);
});

it('forbids another user from viewing non-seeded character on training show route', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $character = Character::factory()->create([
        'user_id' => $owner->id,
        'is_seeded' => false,
    ]);

    $response = $this->actingAs($otherUser)->get(route('training.predictions.show', $character));

    $response->assertForbidden();
});
