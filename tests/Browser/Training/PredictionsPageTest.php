<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('shows empty-state quick actions when no character is selected', function () {
    $this->actingAs($this->user);

    $page = visit('/training/predictions');

    $page->assertSee('No Character Selected')
        ->assertSee('Quick Switch')
        ->assertSee('Create Character')
        ->click('Create Character')
        ->assertPathIs('/characters/create');
})->group('browser', 'training-predictions');

it('opens training confirmation modal and loads authoritative preview', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'energy_level' => 70,
        'mood_status' => 'normal',
    ]);

    $this->actingAs($this->user);

    $page = visit('/training/predictions?character_id='.$character->id);

    $page->assertSee('Training Predictions')
        ->wait(2.5)
        ->click('[data-facility="speed"] button')
        ->wait(0.8)
        ->assertSee('Confirm Training Action')
        ->assertSee('Confirm Training');
})->group('browser', 'training-predictions');

it('renders mobile-friendly collapsible overview labels and ai why button', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'energy_level' => 68,
    ]);

    $this->actingAs($this->user);

    $page = visit('/training/predictions?character_id='.$character->id)
        ->on()->mobile();

    $page->assertSee('Current Stats')
        ->assertSee('Support Cards')
        ->assertSee('Why?')
        ->assertNoJavaScriptErrors();
})->group('browser', 'training-predictions', 'responsive');
