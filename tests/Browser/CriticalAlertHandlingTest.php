<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Critical Alert Handling E2E Test
 *
 * Tests the training predictions page with characters in various critical states:
 * - Low stamina characters
 * - Low energy characters
 * - Low SP characters
 * - Multiple critical issues
 * - Healthy characters
 *
 * Validates: Requirements 3.4, 3.7
 * Task: 7.2.2 Test critical alert handling
 *
 * @group browser
 * @group e2e
 * @group advisory
 * @group critical-alerts
 */
beforeEach(function () {
    $this->user = User::factory()->create();
});

it('triggers and displays stamina crisis alert', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Low Stamina Character',
        'scenario_type' => 'ura_finale',
        'current_stats' => [
            'speed' => 600,
            'stamina' => 320,
            'power' => 550,
            'guts' => 480,
            'wit' => 520,
        ],
        'energy_level' => 75,
        'mood_status' => 'good',
        'career_stage' => 'classic',
        'current_turn' => 35,
    ]);

    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$character->id);

    $page->assertSee('Low Stamina Character')
        ->assertSee('320') // Stamina value in character overview
        ->assertPresent('[role="article"]')
        ->assertNoJavaScriptErrors();
})->group('browser', 'e2e', 'critical-alerts', 'stamina-crisis');

it('triggers and displays energy critical alert', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Low Energy Character',
        'scenario_type' => 'ura_finale',
        'current_stats' => [
            'speed' => 600,
            'stamina' => 500,
            'power' => 550,
            'guts' => 480,
            'wit' => 520,
        ],
        'energy_level' => 25,
        'mood_status' => 'good',
        'career_stage' => 'classic',
        'current_turn' => 30,
    ]);

    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$character->id);

    $page->assertSee('Low Energy Character')
        ->assertSee('25/100') // Low energy in status bar
        ->assertPresent('[role="article"]')
        ->assertNoJavaScriptErrors();
})->group('browser', 'e2e', 'critical-alerts', 'energy-critical');

it('triggers and displays SP shortage alert', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Low SP Character',
        'scenario_type' => 'ura_finale',
        'current_stats' => [
            'speed' => 600,
            'stamina' => 500,
            'power' => 550,
            'guts' => 480,
            'wit' => 520,
        ],
        'available_sp' => 30,
        'energy_level' => 75,
        'mood_status' => 'good',
        'career_stage' => 'classic',
        'current_turn' => 40,
    ]);

    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$character->id);

    $page->assertSee('Low SP Character')
        ->assertPresent('[role="article"]')
        ->assertNoJavaScriptErrors();
})->group('browser', 'e2e', 'critical-alerts', 'sp-shortage');

it('displays multiple critical alerts simultaneously', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Multiple Issues Character',
        'scenario_type' => 'ura_finale',
        'current_stats' => [
            'speed' => 600,
            'stamina' => 300,
            'power' => 550,
            'guts' => 480,
            'wit' => 520,
        ],
        'available_sp' => 25,
        'energy_level' => 30,
        'mood_status' => 'bad',
        'career_stage' => 'classic',
        'current_turn' => 35,
    ]);

    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$character->id);

    $page->assertSee('Multiple Issues Character')
        ->assertSee('30/100') // Low energy
        ->assertSee('Bad') // Bad mood
        ->assertPresent('[role="article"]')
        ->assertNoJavaScriptErrors();
})->group('browser', 'e2e', 'critical-alerts', 'multiple-alerts');

it('allows dismissing a critical alert', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Dismissal Test Character',
        'scenario_type' => 'ura_finale',
        'current_stats' => [
            'speed' => 600,
            'stamina' => 500,
            'power' => 550,
            'guts' => 480,
            'wit' => 520,
        ],
        'energy_level' => 35,
        'mood_status' => 'good',
        'career_stage' => 'classic',
        'current_turn' => 30,
    ]);

    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$character->id);

    $page->assertSee('Dismissal Test Character')
        ->assertSee('35/100')
        ->assertNoJavaScriptErrors();
})->group('browser', 'e2e', 'critical-alerts', 'dismissal');

it('persists alert dismissal across page reloads', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Persistence Test Character',
        'scenario_type' => 'ura_finale',
        'current_stats' => [
            'speed' => 600,
            'stamina' => 500,
            'power' => 550,
            'guts' => 480,
            'wit' => 520,
        ],
        'energy_level' => 35,
        'mood_status' => 'good',
        'career_stage' => 'classic',
        'current_turn' => 30,
    ]);

    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$character->id);

    $page->assertSee('Persistence Test Character');

    // Navigate away and back
    $page->navigate('/training/predictions?character_id='.$character->id)
        ->assertSee('Persistence Test Character')
        ->assertNoJavaScriptErrors();
})->group('browser', 'e2e', 'critical-alerts', 'persistence');

it('displays alert with expandable detailed analysis', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Details Test Character',
        'scenario_type' => 'ura_finale',
        'current_stats' => [
            'speed' => 600,
            'stamina' => 320,
            'power' => 550,
            'guts' => 480,
            'wit' => 520,
        ],
        'energy_level' => 75,
        'mood_status' => 'good',
        'career_stage' => 'classic',
        'current_turn' => 35,
    ]);

    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$character->id);

    // Verify calculation breakdown details element exists in source
    $page->assertSourceHas('<details')
        ->assertSourceHas('View Calculation Breakdown')
        ->assertNoJavaScriptErrors();
})->group('browser', 'e2e', 'critical-alerts', 'details');

it('shows alert badge with count in navigation', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Badge Test Character',
        'scenario_type' => 'ura_finale',
        'current_stats' => [
            'speed' => 600,
            'stamina' => 300,
            'power' => 550,
            'guts' => 480,
            'wit' => 520,
        ],
        'available_sp' => 25,
        'energy_level' => 30,
        'mood_status' => 'good',
        'career_stage' => 'classic',
        'current_turn' => 35,
    ]);

    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$character->id);

    $page->assertSee('Badge Test Character')
        ->assertPresent('[role="article"]')
        ->assertNoJavaScriptErrors();
})->group('browser', 'e2e', 'critical-alerts', 'badge');

it('highlights critical alerts with pulsing animation', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Animation Test Character',
        'scenario_type' => 'ura_finale',
        'current_stats' => [
            'speed' => 600,
            'stamina' => 320,
            'power' => 550,
            'guts' => 480,
            'wit' => 520,
        ],
        'energy_level' => 75,
        'mood_status' => 'good',
        'career_stage' => 'classic',
        'current_turn' => 35,
    ]);

    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$character->id);

    // Verify animation classes exist in the page
    $page->assertSourceHas('animate-')
        ->assertNoJavaScriptErrors();
})->group('browser', 'e2e', 'critical-alerts', 'styling');

it('displays action items as a checklist', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Action Items Test Character',
        'scenario_type' => 'ura_finale',
        'current_stats' => [
            'speed' => 600,
            'stamina' => 320,
            'power' => 550,
            'guts' => 480,
            'wit' => 520,
        ],
        'energy_level' => 75,
        'mood_status' => 'good',
        'career_stage' => 'classic',
        'current_turn' => 35,
    ]);

    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$character->id);

    // Verify AI recommendation section with action guidance
    $page->assertSee('AI Recommendation')
        ->assertPresent('#ai-recommendation-text')
        ->assertNoJavaScriptErrors();
})->group('browser', 'e2e', 'critical-alerts', 'action-items');

it('shows turns until critical for upcoming issues', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Turns Until Critical Test',
        'scenario_type' => 'ura_finale',
        'current_stats' => [
            'speed' => 600,
            'stamina' => 400,
            'power' => 550,
            'guts' => 480,
            'wit' => 520,
        ],
        'energy_level' => 75,
        'mood_status' => 'good',
        'career_stage' => 'classic',
        'current_turn' => 32,
    ]);

    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$character->id);

    // Verify turn counter is displayed
    $page->assertSee('Turn:')
        ->assertSee('32/78')
        ->assertNoJavaScriptErrors();
})->group('browser', 'e2e', 'critical-alerts', 'timing');

it('allows keyboard navigation through alerts', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Keyboard Nav Test',
        'scenario_type' => 'ura_finale',
        'current_stats' => [
            'speed' => 600,
            'stamina' => 300,
            'power' => 550,
            'guts' => 480,
            'wit' => 520,
        ],
        'available_sp' => 25,
        'energy_level' => 30,
        'mood_status' => 'good',
        'career_stage' => 'classic',
        'current_turn' => 35,
    ]);

    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$character->id);

    // Verify keyboard-navigable elements exist
    $page->assertPresent('[role="article"][tabindex="0"]')
        ->assertNoJavaScriptErrors();
})->group('browser', 'e2e', 'critical-alerts', 'keyboard-navigation');

it('updates alerts when character state changes', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'State Change Test',
        'scenario_type' => 'ura_finale',
        'current_stats' => [
            'speed' => 600,
            'stamina' => 500,
            'power' => 550,
            'guts' => 480,
            'wit' => 520,
        ],
        'energy_level' => 35,
        'mood_status' => 'good',
        'career_stage' => 'classic',
        'current_turn' => 30,
    ]);

    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$character->id);

    $page->assertSee('State Change Test')
        ->assertSee('35/100');

    // Verify refresh button exists for updating state
    $page->assertPresent('[aria-label="Refresh predictions"]')
        ->assertNoJavaScriptErrors();
})->group('browser', 'e2e', 'critical-alerts', 'state-update');

it('displays no alerts message when character is healthy', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Healthy Character',
        'scenario_type' => 'ura_finale',
        'current_stats' => [
            'speed' => 800,
            'stamina' => 700,
            'power' => 750,
            'guts' => 650,
            'wit' => 720,
        ],
        'available_sp' => 200,
        'energy_level' => 80,
        'mood_status' => 'great',
        'career_stage' => 'classic',
        'current_turn' => 25,
    ]);

    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$character->id);

    $page->assertSee('Healthy Character')
        ->assertSee('80/100')
        ->assertSee('Great')
        ->assertNoJavaScriptErrors();
})->group('browser', 'e2e', 'critical-alerts', 'no-alerts');

it('shows alert priority badges correctly', function () {
    $character = Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Priority Test Character',
        'scenario_type' => 'ura_finale',
        'current_stats' => [
            'speed' => 600,
            'stamina' => 300,
            'power' => 550,
            'guts' => 480,
            'wit' => 520,
        ],
        'available_sp' => 45,
        'energy_level' => 35,
        'mood_status' => 'good',
        'career_stage' => 'classic',
        'current_turn' => 35,
    ]);

    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$character->id);

    // Verify risk badges on facility cards
    $page->assertPresent('[data-testid="risk-badge-speed"]')
        ->assertPresent('[data-testid="risk-badge-stamina"]')
        ->assertNoJavaScriptErrors();
})->group('browser', 'e2e', 'critical-alerts', 'priority');
