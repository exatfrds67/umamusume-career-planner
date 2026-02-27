<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\User;

it('renders deck builder data payload for the Alpine bootstrap', function () {
    $user = User::factory()->create();
    $character = Character::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get(route('characters.deck-builder', $character));

    $response->assertSuccessful();
    $response->assertSee('id="deck-builder-data"', false);
    $response->assertSee('"deck":', false);
    $response->assertSee('"characterId":', false);
});
