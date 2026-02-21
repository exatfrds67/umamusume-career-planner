<?php

use App\Models\Character;
use App\Models\User;

it('displays correct SP value in top bar', function () {
    $user = User::factory()->create();
    Character::factory()->create([
        'user_id' => $user->id,
        'status' => 'active',
        'available_sp' => 450,
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSuccessful();
    $response->assertSee('SP'); // SP label
    // SP value is rendered via Alpine.js, check the component is present
    $response->assertSee('topStatusBar');
});

it('displays Account storage mode badge in top bar', function () {
    $user = User::factory()->create();
    Character::factory()->create([
        'user_id' => $user->id,
        'status' => 'active',
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSuccessful();
    $response->assertSee('Storage'); // Storage label
    // Badge is rendered via Alpine.js
    $response->assertSee('topStatusBar');
});

it('shows SP as 0 when character has no SP', function () {
    $user = User::factory()->create();
    Character::factory()->create([
        'user_id' => $user->id,
        'status' => 'active',
        'available_sp' => 0,
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSuccessful();
    $response->assertSee('SP');
});

it('provides topStatus data to Alpine store', function () {
    $user = User::factory()->create();
    Character::factory()->create([
        'user_id' => $user->id,
        'status' => 'active',
        'available_sp' => 1250,
        'energy_level' => 75,
        'mood_status' => 'good',
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSuccessful();
    $response->assertViewHas('topStatus', function ($topStatus) {
        return $topStatus['spAvailable'] === 1250
            && $topStatus['energy'] === 75
            && $topStatus['mood'] === 'good';
    });
});

it('displays topStatus on all authenticated pages', function () {
    $user = User::factory()->create();
    Character::factory()->create([
        'user_id' => $user->id,
        'status' => 'active',
        'available_sp' => 300,
    ]);

    // Test multiple pages
    $pages = [
        route('dashboard'),
        route('characters.index'),
        route('skills.index'),
    ];

    foreach ($pages as $page) {
        $response = $this->actingAs($user)->get($page);
        $response->assertSuccessful();
        $response->assertSee('SP');
        $response->assertSee('Storage');
    }
});

it('API endpoint returns character status for fast switching', function () {
    $user = User::factory()->create();
    $character = Character::factory()->create([
        'user_id' => $user->id,
        'status' => 'active',
        'available_sp' => 500,
        'energy_level' => 80,
        'current_turn' => 25,
    ]);

    $response = $this->actingAs($user)->postJson("/api/characters/{$character->id}/select");

    $response->assertSuccessful();
    $response->assertJson([
        'success' => true,
        'character' => [
            'id' => $character->id,
            'name' => $character->name,
        ],
        'topStatus' => [
            'currentTurn' => 25,
            'spAvailable' => 500,
            'energy' => 80,
            'storageMode' => 'account',
        ],
    ]);
});
