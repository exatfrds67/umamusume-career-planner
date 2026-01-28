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
