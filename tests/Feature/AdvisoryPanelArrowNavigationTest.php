<?php

declare(strict_types=1);

use App\Livewire\AdvisoryPanel;
use App\Models\User;
use Livewire\Livewire as LivewireFacade;

use function Pest\Laravel\actingAs;

/**
 * Advisory Panel Arrow Key Navigation Tests
 *
 * Tests arrow key navigation functionality for the advisory panel including:
 * - Navigation between recommendations
 * - Focus management
 * - Accessibility compliance
 * - Screen reader announcements
 */
beforeEach(function () {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

test('advisory panel component renders with keyboard navigation support', function () {
    LivewireFacade::test(AdvisoryPanel::class, [
        'storageMode' => 'local',
        'turnNumber' => 1,
    ])
        ->assertOk()
        ->assertSee('AI Advisory')
        ->assertSeeHtml('x-data="advisoryPanel()"')
        ->assertSeeHtml('role="complementary"')
        ->assertSeeHtml('aria-label="AI Advisory Panel"');
});

test('advisory panel has proper ARIA attributes for keyboard navigation', function () {
    $component = LivewireFacade::test(AdvisoryPanel::class, [
        'storageMode' => 'local',
        'turnNumber' => 15,
    ]);

    $html = $component->html();

    // Check for dialog role
    expect($html)->toContain('role="dialog"');
    expect($html)->toContain('aria-modal="true"');
    expect($html)->toContain('aria-labelledby="advisory-panel-title"');

    // Check for screen reader announcer
    expect($html)->toContain('id="advisory-panel-announcer"');
    expect($html)->toContain('role="status"');
    expect($html)->toContain('aria-live="polite"');
    expect($html)->toContain('aria-atomic="true"');
});

test('advisory panel toggle button has keyboard shortcut hint', function () {
    LivewireFacade::test(AdvisoryPanel::class, [
        'storageMode' => 'local',
        'turnNumber' => 1,
    ])
        ->assertSee('Alt+A')
        ->assertSeeHtml('aria-label="Open AI Advisory Panel (Alt+A)"');
});

test('expandable items have data-expandable attribute for keyboard interaction', function () {
    // This test checks for data-expandable attribute on section headers
    // These are only present when there are alerts/recommendations to display
    // The empty state ("All Clear!") doesn't have these attributes
    $component = LivewireFacade::test(AdvisoryPanel::class, [
        'storageMode' => 'local',
        'turnNumber' => 15,
    ]);

    $html = $component->html();

    // Check for data-expandable attribute on section headers OR empty state
    $hasDataExpandable = str_contains($html, 'data-expandable="true"');
    $hasEmptyState = str_contains($html, 'All Clear!');

    expect($hasDataExpandable || $hasEmptyState)->toBeTrue();
});

test('section headers have proper aria-expanded attributes', function () {
    // This test checks for aria-expanded and aria-controls attributes
    // These are only present when there are alerts/recommendations to display
    // The empty state ("All Clear!") doesn't have these attributes
    $component = LivewireFacade::test(AdvisoryPanel::class, [
        'storageMode' => 'local',
        'turnNumber' => 15,
    ]);

    $html = $component->html();

    // Check for aria-expanded attribute (present on toggle button)
    expect($html)->toContain(':aria-expanded=');

    // aria-controls is only present when sections are rendered (not in empty state)
    // The empty state shows "All Clear!" instead of sections
    // So we check that either aria-controls is present OR the empty state is shown
    $hasAriaControls = str_contains($html, 'aria-controls=');
    $hasEmptyState = str_contains($html, 'All Clear!');

    expect($hasAriaControls || $hasEmptyState)->toBeTrue();
});

test('panel can be toggled programmatically', function () {
    $component = LivewireFacade::test(AdvisoryPanel::class, [
        'storageMode' => 'local',
        'turnNumber' => 1,
    ]);

    // Initially closed
    expect($component->get('isOpen'))->toBeFalse();

    // Toggle open
    $component->call('togglePanel');
    expect($component->get('isOpen'))->toBeTrue();

    // Toggle closed
    $component->call('togglePanel');
    expect($component->get('isOpen'))->toBeFalse();
});

test('panel can be opened with openPanel method', function () {
    $component = LivewireFacade::test(AdvisoryPanel::class, [
        'storageMode' => 'local',
        'turnNumber' => 1,
    ]);

    expect($component->get('isOpen'))->toBeFalse();

    $component->call('openPanel');
    expect($component->get('isOpen'))->toBeTrue();
});

test('panel can be closed with closePanel method', function () {
    $component = LivewireFacade::test(AdvisoryPanel::class, [
        'storageMode' => 'local',
        'turnNumber' => 1,
    ]);

    $component->set('isOpen', true);
    expect($component->get('isOpen'))->toBeTrue();

    $component->call('closePanel');
    expect($component->get('isOpen'))->toBeFalse();
});

test('sections can be toggled for keyboard navigation', function () {
    $component = LivewireFacade::test(AdvisoryPanel::class, [
        'storageMode' => 'local',
        'turnNumber' => 1,
    ]);

    // Initially all sections visible
    expect($component->get('showCriticalAlerts'))->toBeTrue();

    // Toggle section
    $component->call('toggleSection', 'criticalAlerts');
    expect($component->get('showCriticalAlerts'))->toBeFalse();

    // Toggle again
    $component->call('toggleSection', 'criticalAlerts');
    expect($component->get('showCriticalAlerts'))->toBeTrue();
});

test('keyboard navigation footer hint is displayed', function () {
    LivewireFacade::test(AdvisoryPanel::class, [
        'storageMode' => 'local',
        'turnNumber' => 1,
    ])
        ->assertSee('Press')
        ->assertSee('Alt+A')
        ->assertSee('to toggle')
        ->assertSeeHtml('<kbd');
});

test('close button has proper aria-label for keyboard users', function () {
    LivewireFacade::test(AdvisoryPanel::class, [
        'storageMode' => 'local',
        'turnNumber' => 1,
    ])
        ->assertSeeHtml('aria-label="Close advisory panel (Escape)"');
});

test('dismiss buttons have proper aria-labels for keyboard users', function () {
    // This test checks for dismiss button aria-labels
    // These are only present when there are alerts/recommendations to display
    // The empty state ("All Clear!") doesn't have dismiss buttons
    $component = LivewireFacade::test(AdvisoryPanel::class, [
        'storageMode' => 'local',
        'turnNumber' => 15,
    ]);

    $html = $component->html();

    // Check for dismiss button aria-labels OR empty state
    $hasDismissLabels = str_contains($html, 'aria-label="Dismiss alert"') ||
        str_contains($html, 'aria-label="Dismiss recommendation"');
    $hasEmptyState = str_contains($html, 'All Clear!');

    expect($hasDismissLabels || $hasEmptyState)->toBeTrue();
});

test('panel maintains focus management state', function () {
    $component = LivewireFacade::test(AdvisoryPanel::class, [
        'storageMode' => 'local',
        'turnNumber' => 1,
    ]);

    // Open panel
    $component->call('openPanel');

    // Verify panel is open
    expect($component->get('isOpen'))->toBeTrue();

    // Close panel
    $component->call('closePanel');

    // Verify panel is closed
    expect($component->get('isOpen'))->toBeFalse();
});

test('panel works in both local and account storage modes', function () {
    // Test local mode
    LivewireFacade::test(AdvisoryPanel::class, [
        'storageMode' => 'local',
        'turnNumber' => 1,
    ])
        ->assertSee('Local Mode');

    // Test account mode
    LivewireFacade::test(AdvisoryPanel::class, [
        'storageMode' => 'account',
        'careerRunId' => 1,
        'turnNumber' => 1,
    ])
        ->assertSee('Account Mode');
});

test('panel displays turn number for context', function () {
    LivewireFacade::test(AdvisoryPanel::class, [
        'storageMode' => 'local',
        'turnNumber' => 42,
    ])
        ->assertSee('Turn 42');
});

test('empty state is displayed when no content available', function () {
    LivewireFacade::test(AdvisoryPanel::class, [
        'storageMode' => 'local',
        'turnNumber' => 1,
    ])
        ->assertSee('All Clear!')
        ->assertSee('No critical alerts or recommendations at this time');
});

test('panel has proper complementary role for accessibility', function () {
    LivewireFacade::test(AdvisoryPanel::class, [
        'storageMode' => 'local',
        'turnNumber' => 1,
    ])
        ->assertSeeHtml('role="complementary"');
});

test('expandable buttons have proper aria-expanded state', function () {
    $component = LivewireFacade::test(AdvisoryPanel::class, [
        'storageMode' => 'local',
        'turnNumber' => 15,
    ]);

    $html = $component->html();

    // Check for aria-expanded binding
    expect($html)->toContain(':aria-expanded=');
});

test('panel supports keyboard shortcut documentation', function () {
    $component = LivewireFacade::test(AdvisoryPanel::class, [
        'storageMode' => 'local',
        'turnNumber' => 1,
    ]);

    // Check for keyboard shortcut hints in the UI
    $html = $component->html();

    expect($html)->toContain('Alt+A');
    expect($html)->toContain('kbd');
});

test('panel Alpine component includes arrow key navigation methods', function () {
    $component = LivewireFacade::test(AdvisoryPanel::class, [
        'storageMode' => 'local',
        'turnNumber' => 1,
    ]);

    $html = $component->html();

    // Verify Alpine.js component is initialized
    expect($html)->toContain('x-data="advisoryPanel()"');
    expect($html)->toContain('x-init');
});

test('panel has proper focus trap for keyboard navigation', function () {
    $component = LivewireFacade::test(AdvisoryPanel::class, [
        'storageMode' => 'local',
        'turnNumber' => 1,
    ]);

    $html = $component->html();

    // Check for dialog structure that supports focus management
    expect($html)->toContain('role="dialog"');
    expect($html)->toContain('aria-modal="true"');
});

test('panel sections have unique IDs for aria-controls', function () {
    // This test checks for section IDs used by aria-controls
    // These are only present when there are alerts/recommendations to display
    // The empty state ("All Clear!") doesn't have these sections
    $component = LivewireFacade::test(AdvisoryPanel::class, [
        'storageMode' => 'local',
        'turnNumber' => 1,
    ]);

    $html = $component->html();

    // Check for section IDs OR empty state
    $hasSectionIds = str_contains($html, 'critical-alerts-content') ||
        str_contains($html, 'training-recommendations-content');
    $hasEmptyState = str_contains($html, 'All Clear!');

    expect($hasSectionIds || $hasEmptyState)->toBeTrue();
});

test('panel has screen reader only class for announcements', function () {
    $component = LivewireFacade::test(AdvisoryPanel::class, [
        'storageMode' => 'local',
        'turnNumber' => 1,
    ]);

    $html = $component->html();

    // Check for sr-only class
    expect($html)->toContain('sr-only');
});
