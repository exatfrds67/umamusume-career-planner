<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows ai career plan link in the sidebar', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('AI Career Plan');
});

it('falls back to characters index when user has no characters', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk()
        ->assertSee(route('characters.index'), false);
});

it('points ai career plan link to current character when session has one', function () {
    $user = User::factory()->create();
    $character = Character::factory()->create([
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user)
        ->withSession(['current_character_id' => $character->id])
        ->get(route('dashboard'));

    $response->assertOk()
        ->assertSee(route('characters.show', $character).'#career-plan-visualizer', false);
});

it('points ai career plan link to latest character when session has no selected character', function () {
    $user = User::factory()->create();
    $olderCharacter = Character::factory()->create([
        'user_id' => $user->id,
        'updated_at' => now()->subDay(),
    ]);
    $latestCharacter = Character::factory()->create([
        'user_id' => $user->id,
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk()
        ->assertSee(route('characters.show', $latestCharacter).'#career-plan-visualizer', false)
        ->assertDontSee(route('characters.show', $olderCharacter).'#career-plan-visualizer', false);
});

it('falls back to latest owned character when session character belongs to another user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $latestCharacter = Character::factory()->create([
        'user_id' => $user->id,
        'updated_at' => now(),
    ]);
    $otherUsersCharacter = Character::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    $response = $this->actingAs($user)
        ->withSession(['current_character_id' => $otherUsersCharacter->id])
        ->get(route('dashboard'));

    $response->assertOk()
        ->assertSee(route('characters.show', $latestCharacter).'#career-plan-visualizer', false)
        ->assertDontSee(route('characters.show', $otherUsersCharacter).'#career-plan-visualizer', false);
});

it('refreshes current character session when viewing a character', function () {
    $user = User::factory()->create();
    $character = Character::factory()->create([
        'user_id' => $user->id,
    ]);

    $response = $this->actingAs($user)
        ->withSession(['current_character_id' => 999999])
        ->get(route('characters.show', $character));

    $response->assertOk();
    $response->assertSessionHas('current_character_id', $character->id);
});

// Phase 1 Shell Stabilization: Navigation & Accessibility Tests

it('contains all 8 primary navigation items in sidebar', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('Dashboard')
        ->assertSee('Characters')
        ->assertSee('Training')
        ->assertSee('Races')
        ->assertSee('Skills')
        ->assertSee('Support Cards')
        ->assertSee('AI Advisor')
        ->assertSee('Settings');
});

it('renders sidebar with navigation role and accessible attributes', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk()
        ->assertSeeHtml('role="navigation"')
        ->assertSeeHtml('aria-label="Main navigation"')
        ->assertSeeHtml('id="sidebar-navigation"');
});

it('contains aria-current binding on navigation items', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('dashboard'));

    // aria-current is set via Alpine binding (:aria-current), so check for the Alpine directive
    $response->assertOk()
        ->assertSeeHtml(':aria-current');
});
it('includes data-testid attributes on all primary navigation items', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk()
        ->assertSeeHtml('data-testid="sidebar-nav-dashboard"')
        ->assertSeeHtml('data-testid="sidebar-nav-characters"')
        ->assertSeeHtml('data-testid="sidebar-nav-training"')
        ->assertSeeHtml('data-testid="sidebar-nav-races"')
        ->assertSeeHtml('data-testid="sidebar-nav-skills"')
        ->assertSeeHtml('data-testid="sidebar-nav-support-cards"')
        ->assertSeeHtml('data-testid="sidebar-nav-ai-advisor"')
        ->assertSeeHtml('data-testid="sidebar-nav-settings"');
});

it('links all 8 primary navigation items to correct routes', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('dashboard'));

    $response->assertOk()
        ->assertSee(route('dashboard'), false)
        ->assertSee(route('characters.index'), false)
        ->assertSee(route('training.predictions'), false)
        ->assertSee(route('races.index'), false)
        ->assertSee(route('skills.index'), false)
        ->assertSee(route('support-cards.index'), false)
        ->assertSee(route('ai.chat'), false)
        ->assertSee(route('settings.index'), false);
});
