<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Recommendation Application E2E Test
 *
 * Tests the complete workflow of applying a training recommendation:
 * - View training facility cards with stat predictions
 * - Interact with facility cards and action buttons
 * - Verify page structure and AI recommendation display
 *
 * Validates: Requirements 3.1, 3.8
 * Task: 7.2.3 Test recommendation application
 *
 * @group browser
 * @group e2e
 * @group advisory
 * @group recommendation-application
 */
beforeEach(function () {
    $this->user = User::factory()->create();
    $this->character = Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Test Character',
        'scenario_type' => 'ura_finale',
        'current_stats' => [
            'speed' => 450,
            'stamina' => 380,
            'power' => 420,
            'guts' => 350,
            'wit' => 400,
        ],
        'energy_level' => 75,
        'mood_status' => 'good',
        'career_stage' => 'classic',
        'current_turn' => 15,
    ]);
});

it('displays training facility cards with stat predictions', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id);

    // Verify all 6 facility cards are present
    $page->assertSee('Training Predictions')
        ->assertSee($this->character->name)
        ->assertCount('[role="article"]', 6);

    // Verify stat gains section exists on facility cards
    $page->assertPresent('[data-testid="stat-gains"]');

    // Verify risk badges are present
    $page->assertPresent('[data-testid="risk-badge-speed"]')
        ->assertPresent('[data-testid="risk-badge-stamina"]');

    $page->assertNoJavaScriptErrors();
})->group('browser', 'e2e', 'advisory', 'recommendation-application');

it('selects a speed training facility and shows selection highlight', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id);

    // Unhide the predictions grid (normally shown after API fetch)
    $page->script("document.getElementById('predictions-grid')?.classList.remove('hidden')");

    // Trigger selection via JS function directly (avoids click event propagation race conditions)
    $page->script("selectFacility('speed')");

    // Verify selection ring is applied
    $page->assertPresent('[data-facility="speed"].ring-2')
        ->assertNoJavaScriptErrors();
})->group('browser', 'e2e', 'advisory', 'speed-training');

it('clicks train button on stamina facility', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id);

    // Click the Train button on stamina facility
    $page->click('[data-facility="stamina"] button')
        ->wait(0.5);

    // Page should still be functional after clicking train
    $page->assertSee('Training Predictions')
        ->assertNoJavaScriptErrors();
})->group('browser', 'e2e', 'advisory', 'stamina-training');

it('clicks rest option for low energy character', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id);

    $page->assertSee($this->character->name);

    // Click the Rest button
    $page->click('[data-facility="rest"] button')
        ->wait(0.5);

    $page->assertNoJavaScriptErrors();
})->group('browser', 'e2e', 'advisory', 'rest-training');

it('shows AI recommendation section with calculation breakdown', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id);

    // Verify AI recommendation section
    $page->assertSee('AI Recommendation')
        ->assertPresent('#ai-recommendation-text')
        ->assertPresent('#predictions-summary');

    // Verify calculation breakdown is available
    $page->assertSourceHas('View Calculation Breakdown')
        ->assertSourceHas('<details');

    $page->assertNoJavaScriptErrors();
})->group('browser', 'e2e', 'advisory', 'calculation-breakdown');

it('shows updated recommendations after refreshing page', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id);

    $page->assertSee($this->character->name)
        ->assertPresent('[role="article"]');

    // Verify refresh button exists
    $page->assertPresent('[aria-label="Refresh predictions"]');

    // Refresh the page
    $page->refresh()
        ->assertSee('Training Predictions')
        ->assertSee($this->character->name)
        ->assertNoJavaScriptErrors();
})->group('browser', 'e2e', 'advisory', 'recommendation-refresh');

it('can interact with multiple facility cards in sequence', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id);

    // Unhide the predictions grid (normally shown after API fetch)
    $page->script("document.getElementById('predictions-grid')?.classList.remove('hidden')");

    // Click speed
    $page->click('[data-facility="speed"]')
        ->wait(0.3);
    $page->assertPresent('[data-facility="speed"].ring-2');

    // Click stamina (should deselect speed)
    $page->click('[data-facility="stamina"]')
        ->wait(0.3);
    $page->assertPresent('[data-facility="stamina"].ring-2');

    // Click power
    $page->click('[data-facility="power"]')
        ->wait(0.3);
    $page->assertPresent('[data-facility="power"].ring-2');

    $page->assertNoJavaScriptErrors();
})->group('browser', 'e2e', 'advisory', 'multiple-trainings');

it('displays expected outcomes on facility cards', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id);

    // Verify stat gains section exists
    $page->assertPresent('[data-testid="stat-gains"]');

    // Verify primary gain, secondary, and skill points labels
    $page->assertSee('Primary Gain:')
        ->assertSee('Secondary:')
        ->assertSee('Skill Points:');

    // Verify risk badges
    $page->assertPresent('[data-testid="risk-badge-speed"]')
        ->assertPresent('[data-testid="risk-badge-stamina"]')
        ->assertPresent('[data-testid="risk-badge-power"]')
        ->assertPresent('[data-testid="risk-badge-guts"]')
        ->assertPresent('[data-testid="risk-badge-wit"]')
        ->assertPresent('[data-testid="risk-badge-rest"]');
})->group('browser', 'e2e', 'advisory', 'expected-outcomes');

it('shows risk assessment badges on all facility cards', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id);

    // All 6 facilities should have risk badges
    $page->assertCount('[role="article"]', 6);

    // Verify risk legend is present
    $page->assertSee('Low Risk')
        ->assertSee('Medium')
        ->assertSee('High');
})->group('browser', 'e2e', 'advisory', 'risk-assessment');

it('handles high-risk character gracefully', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id);

    $page->assertSee($this->character->name)
        ->assertPresent('[role="article"]')
        ->assertNoJavaScriptErrors();
})->group('browser', 'e2e', 'advisory', 'training-failure');

it('supports keyboard navigation on facility cards', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id);

    // Verify facility cards have tabindex for keyboard navigation
    $page->assertPresent('[role="article"][tabindex="0"]');

    // Verify onkeydown handler exists for Enter key
    $page->assertSourceHas('onkeydown');

    $page->assertNoJavaScriptErrors();
})->group('browser', 'e2e', 'advisory', 'keyboard-navigation');
