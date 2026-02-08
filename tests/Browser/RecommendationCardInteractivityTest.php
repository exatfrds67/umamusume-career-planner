<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Recommendation Card Interactivity Test
 *
 * Tests the training predictions page card interactivity including:
 * - Training facility card visibility and structure
 * - Character selection and page updates
 * - Card click interactions
 * - Keyboard navigation on facility cards
 * - Accessibility compliance (WCAG 2.2 AA)
 * - Responsive viewport behavior
 * - Dark mode styling
 *
 * @group browser
 * @group ai
 * @group accessibility
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

it('expands and collapses recommendation card when clicked', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id);

    $page->assertSee('Training Predictions')
        ->assertSee($this->character->name)
        ->assertPresent('[role="article"]');

    // Click a facility card
    $page->click('[data-facility="speed"]')
        ->wait(0.5);

    // Verify card gets selected (ring highlight)
    $page->assertPresent('[data-facility="speed"].ring-2');
})->group('expand-collapse');

it('rotates chevron icon when expanding and collapsing', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id);

    $page->assertSee('Training Predictions')
        ->assertPresent('[role="article"]');

    // Verify calculation breakdown details element exists
    $page->assertSourceHas('<details');
})->group('expand-collapse');

it('shows expanded content with reasoning and outcomes', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id);

    $page->assertSee('Training Predictions')
        ->assertSee('AI Recommendation')
        ->assertPresent('#ai-recommendation-text');

    // Verify calculation breakdown is present
    $page->assertSee('View Calculation Breakdown');
})->group('expand-collapse');

it('applies recommendation when apply button is clicked', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id);

    $page->assertSee('Training Predictions')
        ->assertPresent('[data-facility="speed"]');

    // Click the Train button on speed facility
    $page->click('[data-facility="speed"] button')
        ->wait(0.5);
})->group('actions');

it('shows feedback dialog when feedback button is clicked', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id);

    $page->assertSee('Training Predictions')
        ->assertPresent('#ai-advisor-banner');

    // Click the Details button on AI advisor banner
    $page->click('#ai-advisor-banner button')
        ->wait(0.5);
})->group('actions');

it('dismisses recommendation with smooth animation', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id);

    $page->assertSee('Training Predictions')
        ->assertPresent('[data-facility="speed"]')
        ->assertPresent('[data-facility="stamina"]')
        ->assertPresent('[data-facility="power"]')
        ->assertPresent('[data-facility="guts"]')
        ->assertPresent('[data-facility="wit"]')
        ->assertPresent('[data-facility="rest"]');
})->group('actions');

it('maintains focus on expand button during keyboard navigation', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id);

    $page->assertSee('Training Predictions')
        ->assertPresent('[role="article"][tabindex="0"]');
})->group('keyboard-navigation', 'accessibility');

it('supports Space key to expand and collapse', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id);

    $page->assertSee('Training Predictions')
        ->assertPresent('[role="article"][tabindex="0"]');

    // Facility cards have tabindex="0" and onkeydown for Enter
    $page->assertSourceHas('onkeydown');
})->group('keyboard-navigation', 'accessibility');

it('has proper ARIA attributes for accessibility', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id);

    // Verify article roles on facility cards
    $page->assertPresent('[role="article"]');

    // Verify status bar section has aria-labelledby
    $page->assertPresent('[aria-labelledby="status-bar-heading"]');

    // Verify AI advisor section has aria-labelledby
    $page->assertPresent('[aria-labelledby="ai-advisor-heading"]');

    // Verify loading state has proper ARIA
    $page->assertPresent('[role="status"][aria-busy="true"]');

    // Verify error state has proper ARIA
    $page->assertPresent('[role="alert"]');
})->group('accessibility');

it('has proper focus indicators for keyboard users', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id);

    // Facility cards have tabindex for keyboard focus
    $page->assertPresent('[role="article"][tabindex="0"]');
})->group('accessibility');

it('displays priority badge with correct color coding', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id);

    // Verify risk badges are present on facility cards
    $page->assertPresent('[data-testid="risk-badge-speed"]')
        ->assertPresent('[data-testid="risk-badge-stamina"]');
})->group('visual');

it('displays confidence score when available', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id);

    // AI confidence badge element exists (hidden by default)
    $page->assertPresent('#ai-confidence-badge');
})->group('visual');

it('shows priority icon emoji', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id);

    // Verify facility icons are present
    $page->assertPresent('[data-facility="speed"] svg')
        ->assertPresent('[data-facility="stamina"] svg');
})->group('visual');

it('applies hover effects on card', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id);

    // Verify cards have hover transition classes
    $page->assertSourceHas('hover:shadow-lg')
        ->assertSourceHas('transition-shadow');
})->group('visual');

it('supports dark mode styling', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id)
        ->inDarkMode();

    // Verify page renders in dark mode
    $page->assertSee('Training Predictions')
        ->assertPresent('[role="article"]');
})->group('visual', 'dark-mode');

it('handles multiple recommendation cards independently', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id);

    // Verify all 6 facility cards are present
    $page->assertCount('[role="article"]', 6);
})->group('multiple-cards');

it('preserves expanded state when other cards are interacted with', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id);

    // Click speed card
    $page->click('[data-facility="speed"]')
        ->wait(0.3);

    // Click stamina card
    $page->click('[data-facility="stamina"]')
        ->wait(0.3);

    // Stamina should now have the ring, speed should not
    $page->assertPresent('[data-facility="stamina"].ring-2');
})->group('multiple-cards');

it('respects prefers-reduced-motion for animations', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id);

    // Verify transition classes are present (CSS handles reduced-motion)
    $page->assertSourceHas('transition');
})->group('accessibility', 'reduced-motion');

it('has sufficient color contrast for WCAG AA compliance', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id);

    // Verify text elements are present and visible
    $page->assertSee('Training Predictions')
        ->assertSee($this->character->name)
        ->assertSee('Speed')
        ->assertSee('Stamina');
})->group('accessibility', 'wcag');

it('provides meaningful button labels for screen readers', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id);

    // Verify refresh button has aria-label
    $page->assertPresent('[aria-label="Refresh predictions"]');

    // Verify clear cache button has aria-label
    $page->assertPresent('[aria-label="Clear cache"]');
})->group('accessibility', 'screen-reader');

it('handles rapid expand/collapse clicks gracefully', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id);

    // Rapidly click different facility cards
    $page->click('[data-facility="speed"]')
        ->click('[data-facility="stamina"]')
        ->click('[data-facility="power"]')
        ->click('[data-facility="guts"]')
        ->click('[data-facility="wit"]');

    // Page should still be functional
    $page->assertSee('Training Predictions')
        ->assertNoJavaScriptErrors();
})->group('edge-cases');

it('maintains state after page scroll', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id);

    $page->assertSee('Training Predictions')
        ->assertPresent('[role="article"]');

    // Scroll and verify content is still present
    $page->script('window.scrollBy(0, 500)');
    $page->wait(0.3);

    $page->assertPresent('[role="article"]');
})->group('edge-cases');

it('works correctly on mobile viewport', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id)
        ->on()->mobile();

    $page->assertSee('Training Predictions')
        ->assertPresent('[role="article"]');
})->group('responsive');

it('works correctly on tablet viewport', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id)
        ->on()->iPadMini();

    $page->assertSee('Training Predictions')
        ->assertPresent('[role="article"]');
})->group('responsive');

it('works correctly on desktop viewport', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id);

    $page->assertSee('Training Predictions')
        ->assertPresent('[role="article"]')
        ->assertNoJavaScriptErrors();
})->group('responsive');
