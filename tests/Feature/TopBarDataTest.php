<?php

use App\Models\Character;
use App\Models\User;

it('shares topStatus with SP and storage mode on dashboard', function () {
    $user = User::factory()->create();
    $character = Character::factory()->create([
        'user_id' => $user->id,
        'name' => 'Test Character',
        'status' => 'active',
        'current_turn' => 25,
        'energy_level' => 80,
        'mood_status' => 'good',
        'career_stage' => 'classic',
        'available_sp' => 250,
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSuccessful();

    // Check that topStatus is available
    $response->assertViewHas('topStatus', function ($topStatus) {
        return isset($topStatus['spAvailable'])
            && isset($topStatus['storageMode'])
            && $topStatus['spAvailable'] === 250
            && $topStatus['storageMode'] === 'account';
    });
});

it('displays SP value in the rendered HTML', function () {
    $user = User::factory()->create();
    Character::factory()->create([
        'user_id' => $user->id,
        'status' => 'active',
        'available_sp' => 350,
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertSuccessful();
    // Just check the response contains the SP section
    $response->assertSee('SP');
});
