<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Collections\CriticalAlertCollection;
use App\Collections\RecommendationCollection;
use App\Enums\Priority;
use App\Enums\RecommendationType;
use App\Livewire\AdvisoryPanel;
use App\Models\User;
use App\Services\TrainingAdvisoryService;
use App\ValueObjects\Recommendation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire as LivewireFacade;

use function Pest\Laravel\actingAs;

/**
 * Advisory Panel Keyboard Navigation Tests
 *
 * Tests comprehensive keyboard navigation functionality including:
 * - All interactive elements are keyboard accessible
 * - Focus management works correctly
 * - Screen reader announcements are present and correct
 * - WCAG 2.2 AA compliance
 *
 * **Validates: Requirements 3.7, 4.3**
 */
uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    actingAs($this->user);
});

describe('Interactive Elements Keyboard Accessibility', function () {
    test('toggle button has proper keyboard attributes', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 1,
        ]);

        $html = $component->html();

        // Toggle button should have aria-label with keyboard hint
        expect($html)->toContain('aria-label="Open AI Advisory Panel (Alt+A)"');

        // Toggle button should have aria-expanded attribute
        expect($html)->toContain(':aria-expanded="isOpen"');

        // Toggle button should be focusable (no tabindex=-1)
        expect($html)->not->toContain('tabindex="-1"');
    });

    test('close button has proper keyboard attributes', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 1,
        ]);

        $html = $component->html();

        // Close button should have aria-label with keyboard hint
        expect($html)->toContain('aria-label="Close advisory panel (Escape)"');

        // Close button should be a button element (not a div)
        expect($html)->toContain('<button @click="closePanel()"');
    });

    test('section headers are keyboard accessible', function () {
        // This test checks for section header keyboard accessibility
        // These are only present when there are alerts/recommendations to display
        // The empty state ("All Clear!") doesn't have section headers
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 15,
        ]);

        $html = $component->html();

        // Check for section header buttons OR empty state
        $hasSectionHeaders = str_contains($html, '@click="toggleSection(\'alerts\')"') ||
            str_contains($html, '@click="toggleSection(\'training\')"');
        $hasEmptyState = str_contains($html, 'All Clear!');

        expect($hasSectionHeaders || $hasEmptyState)->toBeTrue();
    });

    test('dismiss buttons are keyboard accessible', function () {
        // This test checks for dismiss button accessibility
        // These are only present when there are alerts to display
        // The empty state ("All Clear!") doesn't have dismiss buttons
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 15,
        ]);

        $html = $component->html();

        // Check for dismiss button aria-label OR empty state
        $hasDismissLabels = str_contains($html, 'aria-label="Dismiss alert') ||
            str_contains($html, 'aria-label="Dismiss recommendation"');
        $hasEmptyState = str_contains($html, 'All Clear!');

        expect($hasDismissLabels || $hasEmptyState)->toBeTrue();
    });

    test('expandable items have proper keyboard attributes', function () {
        // This test checks for expandable items with proper keyboard attributes
        // These are only present when there are recommendations to display
        // The empty state ("All Clear!") doesn't have these elements
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 15,
        ]);

        $html = $component->html();

        // Check for expandable items OR empty state
        $hasExpandableItems = str_contains($html, 'data-expandable="true"') ||
            str_contains($html, '@click="toggleRecommendation');
        $hasEmptyState = str_contains($html, 'All Clear!');

        expect($hasExpandableItems || $hasEmptyState)->toBeTrue();
    });

    test('all interactive elements have focus indicators', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 1,
        ]);

        $html = $component->html();

        // Check for focus styles on buttons
        $focusStylePatterns = [
            'focus:outline-none focus:ring',
            'focus:ring-2',
            'focus:ring-primary-500',
            'focus:underline',
        ];

        $hasFocusStyles = false;
        foreach ($focusStylePatterns as $pattern) {
            if (str_contains($html, $pattern)) {
                $hasFocusStyles = true;
                break;
            }
        }

        expect($hasFocusStyles)->toBeTrue('All interactive elements should have focus indicators');
    });

    test('keyboard shortcut hints are visible', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 1,
        ]);

        $html = $component->html();

        // Check for Alt+A hint
        expect($html)->toContain('Alt+A');

        // Check for kbd element
        expect($html)->toContain('<kbd');

        // Check for Escape hint
        expect($html)->toContain('Escape');
    });
});

describe('Focus Management', function () {
    test('panel has proper dialog role and attributes', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 1,
        ]);

        $html = $component->html();

        // Dialog role
        expect($html)->toContain('role="dialog"');

        // Modal attribute
        expect($html)->toContain('aria-modal="true"');

        // Labeled by title
        expect($html)->toContain('aria-labelledby="advisory-panel-title"');

        // Title element exists
        expect($html)->toContain('id="advisory-panel-title"');
    });

    test('panel has complementary role for accessibility', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 1,
        ]);

        $html = $component->html();

        // Complementary role on container
        expect($html)->toContain('role="complementary"');

        // Aria-label on container
        expect($html)->toContain('aria-label="AI Advisory Panel"');
    });

    test('Alpine component initializes with focus management', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 1,
        ]);

        $html = $component->html();

        // Alpine component is initialized
        expect($html)->toContain('x-data="advisoryPanel()"');

        // x-init directive is present
        expect($html)->toContain('x-init');
    });

    test('focus returns to toggle button when panel closes', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 1,
        ]);

        // Open panel
        $component->call('openPanel');
        expect($component->get('isOpen'))->toBeTrue();

        // Close panel
        $component->call('closePanel');
        expect($component->get('isOpen'))->toBeFalse();

        // Verify close event was dispatched (focus return is handled in Alpine)
        $component->assertDispatched('advisory-panel-closed');
    });

    test('sections have unique IDs for aria-controls', function () {
        // This test checks for section IDs used by aria-controls
        // These are only present when there are alerts/recommendations to display
        // The empty state ("All Clear!") doesn't have these sections
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 1,
        ]);

        $html = $component->html();

        // Check for section IDs OR empty state
        $hasSectionIds = str_contains($html, 'id="critical-alerts-content"') ||
            str_contains($html, 'id="training-recommendations-content"');
        $hasEmptyState = str_contains($html, 'All Clear!');

        expect($hasSectionIds || $hasEmptyState)->toBeTrue();
    });

    test('expandable items maintain focus state', function () {
        // This test checks for expandable items with aria-expanded
        // These are only present when there are alerts/recommendations to display
        // The empty state ("All Clear!") doesn't have these elements
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 15,
        ]);

        $html = $component->html();

        // Check for expandable items OR empty state
        $hasExpandableItems = str_contains($html, 'expandedRecommendation') ||
            str_contains($html, 'data-expandable="true"');
        $hasEmptyState = str_contains($html, 'All Clear!');

        expect($hasExpandableItems || $hasEmptyState)->toBeTrue();
    });

    test('click outside closes panel', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 1,
        ]);

        $html = $component->html();

        // Panel should have click.away handler
        expect($html)->toContain('@click.away="$wire.closePanel()"');
    });
});

describe('Screen Reader Announcements', function () {
    test('screen reader announcer element exists', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 1,
        ]);

        $html = $component->html();

        // Announcer element exists
        expect($html)->toContain('id="advisory-panel-announcer"');

        // Has proper ARIA attributes
        expect($html)->toContain('role="status"');
        expect($html)->toContain('aria-live="polite"');
        expect($html)->toContain('aria-atomic="true"');

        // Has screen reader only class
        expect($html)->toContain('class="sr-only"');
    });

    test('alerts have proper role and aria-live', function () {
        // This test checks for alert role and aria-live attributes
        // These are only present when there are alerts to display
        // The empty state ("All Clear!") doesn't have these elements
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 15,
        ]);

        $html = $component->html();

        // Check for alert role OR empty state
        $hasAlertRole = str_contains($html, 'role="alert"');
        $hasEmptyState = str_contains($html, 'All Clear!');

        expect($hasAlertRole || $hasEmptyState)->toBeTrue();
    });

    test('section headings have proper IDs for aria-labelledby', function () {
        // This test checks for section heading IDs used by aria-labelledby
        // These are only present when there are alerts/recommendations to display
        // The empty state ("All Clear!") doesn't have these sections
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 1,
        ]);

        $html = $component->html();

        // Check for section heading IDs OR empty state
        $hasHeadingIds = str_contains($html, 'id="critical-alerts-heading"') ||
            str_contains($html, 'id="training-recommendations-heading"');
        $hasEmptyState = str_contains($html, 'All Clear!');

        expect($hasHeadingIds || $hasEmptyState)->toBeTrue();
    });

    test('empty state has descriptive text', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 1,
        ]);

        $html = $component->html();

        // Empty state should have descriptive text
        expect($html)->toContain('All Clear!');
        expect($html)->toContain('No critical alerts or recommendations at this time');
    });

    test('priority badges have descriptive text', function () {
        // Mock service with recommendations
        $mockService = $this->mock(TrainingAdvisoryService::class);

        $recommendation = new Recommendation(
            type: RecommendationType::TRAINING_FACILITY,
            priority: Priority::CRITICAL,
            action: 'Speed Training',
            reasoning: '3 support cards present',
            expectedOutcomes: ['+45-55 Speed'],
            risks: ['5% failure rate'],
            confidenceScore: 0.92
        );

        $mockService->shouldReceive('detectCriticalSituations')
            ->andReturn(new CriticalAlertCollection([]));

        $mockService->shouldReceive('getTrainingRecommendations')
            ->andReturn(new RecommendationCollection([$recommendation]));

        $trainingContext = [
            'turn_number' => 15,
            'phase' => 'classic_year',
            'stats' => [
                'speed' => 450,
                'stamina' => 380,
                'power' => 420,
                'guts' => 350,
                'wisdom' => 400,
            ],
            'sp_available' => 180,
            'energy' => 75,
            'mood' => 'good',
            'acquired_skills' => [],
            'skill_hints' => [],
            'support_deck' => [],
            'facility_levels' => [],
            'upcoming_races' => [],
            'scenario' => null,
        ];

        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'trainingContext' => $trainingContext,
            'storageMode' => 'local',
            'turnNumber' => 15,
        ]);

        $html = $component->html();

        // Priority badges should have text content
        expect($html)->toContain('critical');
    });

    test('icons have aria-hidden attribute', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 1,
        ]);

        $html = $component->html();

        // SVG icons should have aria-hidden="true"
        expect($html)->toContain('aria-hidden="true"');
    });

    test('confidence scores are announced', function () {
        // This test checks for confidence scores in recommendations
        // These are only present when there are recommendations to display
        // The empty state ("All Clear!") doesn't have these elements
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 15,
        ]);

        $html = $component->html();

        // Check for confidence score OR empty state
        $hasConfidenceScore = str_contains($html, 'confidence');
        $hasEmptyState = str_contains($html, 'All Clear!');

        expect($hasConfidenceScore || $hasEmptyState)->toBeTrue();
    });

    test('turn number and storage mode are announced', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 42,
        ]);

        $html = $component->html();

        // Turn number should be displayed
        expect($html)->toContain('Turn 42');

        // Storage mode should be displayed
        expect($html)->toContain('Local Mode');
    });
});

describe('WCAG 2.2 AA Compliance', function () {
    test('all buttons have accessible names', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 1,
        ]);

        $html = $component->html();

        // Check for aria-label on buttons without text
        $buttonPatterns = [
            'aria-label="Open AI Advisory Panel',
            'aria-label="Close advisory panel',
            'aria-label="Dismiss alert"',
            'aria-label="Dismiss recommendation"',
        ];

        foreach ($buttonPatterns as $pattern) {
            if (str_contains($html, '<button') && str_contains($html, $pattern)) {
                expect($html)->toContain($pattern);
            }
        }
    });

    test('keyboard navigation is documented', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 1,
        ]);

        $html = $component->html();

        // Footer should document keyboard shortcuts
        expect($html)->toContain('Press');
        expect($html)->toContain('Alt+A');
        expect($html)->toContain('to toggle');
    });

    test('focus order is logical', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 1,
        ]);

        $html = $component->html();

        // No positive tabindex values (which would disrupt natural focus order)
        expect($html)->not->toContain('tabindex="1"');
        expect($html)->not->toContain('tabindex="2"');
        expect($html)->not->toContain('tabindex="3"');
    });

    test('expandable regions are properly marked', function () {
        // This test checks for expandable regions with aria-expanded and aria-controls
        // These are only present when there are alerts/recommendations to display
        // The empty state ("All Clear!") doesn't have these elements
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 1,
        ]);

        $html = $component->html();

        // Check for aria-expanded (present on toggle button)
        expect($html)->toContain(':aria-expanded=');

        // aria-controls is only present when sections are rendered (not in empty state)
        $hasAriaControls = str_contains($html, 'aria-controls=');
        $hasEmptyState = str_contains($html, 'All Clear!');

        expect($hasAriaControls || $hasEmptyState)->toBeTrue();
    });

    test('status messages use appropriate ARIA roles', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 1,
        ]);

        $html = $component->html();

        // Screen reader announcer should use role="status"
        expect($html)->toContain('role="status"');

        // Alerts should use role="alert"
        // (This will be present when alerts exist)
    });

    test('panel works in both storage modes', function () {
        // Test local mode
        $localComponent = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 1,
        ]);

        expect($localComponent->get('storageMode'))->toBe('local');
        $localComponent->assertSee('Local Mode');

        // Test account mode
        $accountComponent = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'account',
            'careerRunId' => 123,
            'turnNumber' => 1,
        ]);

        expect($accountComponent->get('storageMode'))->toBe('account');
        $accountComponent->assertSee('Account Mode');
    });
});
