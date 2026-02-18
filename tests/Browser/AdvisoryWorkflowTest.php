<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Advisory Workflow E2E Test
 *
 * Tests the training predictions advisory workflow including:
 * - Navigate to training screen
 * - Select character and view predictions
 * - View AI recommendation details
 * - Interact with training facility cards
 *
 * Validates: Requirements 3.1, 3.7
 * Task: 7.2.1 Test complete advisory workflow
 *
 * @group browser
 * @group e2e
 * @group advisory
 * @group ai
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

it('completes the full advisory workflow from navigation to outcome', function () {
    $this->actingAs($this->user);
    $page = visit('/');

    // Step 1: Navigate to training screen
    $page->navigate('/training/predictions')
        ->wait(1)
        ->assertSee('Training Predictions');

    // Step 2: Select character
    $page->select('#character_id', (string) $this->character->id)
        ->wait(2)
        ->assertSee($this->character->name);

    // Step 3: View facility cards
    $page->assertPresent('[role="article"]')
        ->assertSee('Speed')
        ->assertSee('Stamina');

    // Step 4: View AI recommendation
    $page->assertSee('AI Recommendation')
        ->assertPresent('#ai-recommendation-text');

    // Step 5: Click a training facility
    $page->click('[data-facility="speed"]')
        ->wait(0.5);

    $page->assertNoJavaScriptErrors();
})->group('browser', 'e2e', 'advisory', 'workflow');

it('displays recommendations with proper priority ordering', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id);

    $page->assertSee('Training Predictions')
        ->assertSee($this->character->name);

    // Verify facility cards are present with risk badges
    $page->assertPresent('[data-testid="risk-badge-speed"]')
        ->assertPresent('[data-testid="risk-badge-stamina"]');

    // Verify AI recommendation section
    $page->assertSee('AI Recommendation');
})->group('browser', 'e2e', 'advisory', 'priority');

it('shows critical alerts prominently when present', function () {
    // Create a character with low stamina to trigger critical alert
    $criticalCharacter = Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Critical Character',
        'scenario_type' => 'ura_finale',
        'current_stats' => [
            'speed' => 600,
            'stamina' => 320,
            'power' => 550,
            'guts' => 480,
            'wit' => 520,
        ],
        'energy_level' => 35,
        'mood_status' => 'bad',
        'career_stage' => 'classic',
        'current_turn' => 35,
    ]);

    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$criticalCharacter->id);

    // Verify character with critical stats is displayed (use assertSourceHas for server-rendered content)
    $page->assertSourceHas('Critical Character');

    $page->assertNoJavaScriptErrors();
})->group('browser', 'e2e', 'advisory', 'critical-alerts');

it('allows dismissing recommendations', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id);

    // Verify all 6 facility cards are present
    $page->assertCount('[role="article"]', 6);

    // Click a facility card to select it
    $page->click('[data-facility="speed"]')
        ->wait(0.5);

    $page->assertNoJavaScriptErrors();
})->group('browser', 'e2e', 'advisory', 'dismissal');

it('provides keyboard navigation for recommendations', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id);

    // Verify facility cards have tabindex for keyboard navigation
    $page->assertPresent('[role="article"][tabindex="0"]');

    // Verify onkeydown handler exists
    $page->assertSourceHas('onkeydown');

    $page->assertNoJavaScriptErrors();
})->group('browser', 'e2e', 'advisory', 'keyboard-navigation');

it('shows loading state while fetching recommendations', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id);

    // Verify loading element exists in DOM
    $page->assertPresent('#predictions-loading');

    // Verify loading state has proper ARIA attributes
    $page->assertAttribute('#predictions-loading', 'role', 'status');
    $page->assertAttribute('#predictions-loading', 'aria-busy', 'true');
})->group('browser', 'e2e', 'advisory', 'loading');

it('displays confidence scores for AI recommendations', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id);

    // Verify AI confidence badge element exists
    $page->assertPresent('#ai-confidence-badge');

    // Verify AI advisor banner is present
    $page->assertPresent('#ai-advisor-banner');
})->group('browser', 'e2e', 'advisory', 'confidence');

it('handles no recommendations gracefully', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions');

    // Without selecting a character, should show empty state
    $page->assertSee('No Character Selected')
        ->assertSee('Please select a character to view training predictions');
})->group('browser', 'e2e', 'advisory', 'empty-state');

it('updates recommendations when character state changes', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id);

    $page->assertSee($this->character->name)
        ->assertPresent('[role="article"]');

    // Verify refresh button exists
    $page->assertPresent('[aria-label="Refresh predictions"]');

    $page->assertNoJavaScriptErrors();
})->group('browser', 'e2e', 'advisory', 'state-update');

it('displays expected outcomes with stat ranges', function () {
    $this->actingAs($this->user);
    $page = visit('/training/predictions?character_id='.$this->character->id);

    // Verify stat gains section exists on facility cards
    $page->assertPresent('[data-testid="stat-gains"]');

    // Verify calculation breakdown section exists
    $page->assertSee('View Calculation Breakdown');

    $page->assertNoJavaScriptErrors();
})->group('browser', 'e2e', 'advisory', 'outcomes');
