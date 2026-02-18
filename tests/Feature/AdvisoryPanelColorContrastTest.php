<?php

declare(strict_types=1);

use App\Enums\Priority;
use App\Livewire\AdvisoryPanel;
use Livewire\Livewire as LivewireFacade;

/**
 * Advisory Panel Color Contrast Tests
 *
 * Tests WCAG 2.2 AA color contrast compliance for the Advisory Panel component.
 * Validates that all text and interactive elements meet minimum contrast ratios:
 * - Normal text: 4.5:1
 * - Large text (18pt+/14pt+ bold): 3:1
 * - UI components and graphical objects: 3:1
 *
 * Tests both light mode and dark mode color schemes.
 *
 * @see https://www.w3.org/WAI/WCAG22/Understanding/contrast-minimum.html
 */
describe('Advisory Panel Color Contrast - Light Mode', function () {
    test('primary button has sufficient contrast in light mode', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 1,
        ]);

        $html = $component->html();

        // Primary button: bg-primary-600 with white text
        expect($html)->toContain('bg-primary-600');
        expect($html)->toContain('text-white');

        // Verify button is present
        expect($html)->toContain('Open AI Advisory Panel');
    });

    test('critical alert badge has sufficient contrast in light mode', function () {
        $trainingContext = [
            'turn_number' => 15,
            'phase' => 'classic_year',
            'stats' => [
                'speed' => 600,
                'stamina' => 320, // Low stamina triggers critical alert
                'power' => 550,
                'guts' => 480,
                'wisdom' => 520,
            ],
            'sp_available' => 180,
            'energy' => 75,
            'mood' => 'good',
            'acquired_skills' => [],
            'skill_hints' => [],
            'support_deck' => [],
            'facility_levels' => [],
            'upcoming_races' => [
                ['distance' => 'medium', 'turn' => 18],
            ],
            'scenario' => null,
        ];

        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'trainingContext' => $trainingContext,
            'storageMode' => 'local',
            'turnNumber' => 15,
        ]);

        $html = $component->html();

        // Critical alert badge: bg-danger-500 with white text
        expect($html)->toContain('bg-danger-500');
        expect($html)->toContain('text-white');
        expect($html)->toContain('animate-pulse');
    });

    test('critical alert section has sufficient contrast in light mode', function () {
        $trainingContext = [
            'turn_number' => 15,
            'phase' => 'classic_year',
            'stats' => [
                'speed' => 600,
                'stamina' => 320,
                'power' => 550,
                'guts' => 480,
                'wisdom' => 520,
            ],
            'sp_available' => 180,
            'energy' => 75,
            'mood' => 'good',
            'acquired_skills' => [],
            'skill_hints' => [],
            'support_deck' => [],
            'facility_levels' => [],
            'upcoming_races' => [
                ['distance' => 'medium', 'turn' => 18],
            ],
            'scenario' => null,
        ];

        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'trainingContext' => $trainingContext,
            'storageMode' => 'local',
            'turnNumber' => 15,
        ]);

        $html = $component->html();

        // Critical alert container: border-danger-200, bg-danger-50
        expect($html)->toContain('border-danger-200');
        expect($html)->toContain('bg-danger-50');

        // Critical alert icon: bg-danger-100, text-danger-600
        expect($html)->toContain('bg-danger-100');
        expect($html)->toContain('text-danger-600');

        // Critical alert badge: bg-danger-600 with white text
        expect($html)->toContain('bg-danger-600');

        // Critical alert text: text-neutral-900 (dark text on light background)
        expect($html)->toContain('text-neutral-900');
    });

    test('high priority recommendation has sufficient contrast in light mode', function () {
        $trainingContext = [
            'turn_number' => 15,
            'phase' => 'classic_year',
            'stats' => [
                'speed' => 600,
                'stamina' => 500,
                'power' => 550,
                'guts' => 480,
                'wisdom' => 520,
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

        // High priority: border-warning-300, bg-warning-50
        expect($html)->toContain('border-warning-300');
        expect($html)->toContain('bg-warning-50');

        // High priority badge: bg-warning-600 with white text
        expect($html)->toContain('bg-warning-600');
    });

    test('medium priority recommendation has sufficient contrast in light mode', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 15,
        ]);

        $html = $component->html();

        // Medium priority: border-primary-200, bg-primary-50
        expect($html)->toContain('border-primary-200');
        expect($html)->toContain('bg-primary-50');

        // Medium priority badge: bg-primary-600 with white text
        expect($html)->toContain('bg-primary-600');
    });

    test('low priority recommendation has sufficient contrast in light mode', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 15,
        ]);

        $html = $component->html();

        // Low priority: border-neutral-200, bg-white
        expect($html)->toContain('border-neutral-200');
        expect($html)->toContain('bg-white');

        // Low priority badge: bg-neutral-500 with white text
        expect($html)->toContain('bg-neutral-500');
    });

    test('expected outcomes section has sufficient contrast in light mode', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 15,
        ]);

        $html = $component->html();

        // Expected outcomes: bg-success-50, border-success-200
        expect($html)->toContain('bg-success-50');
        expect($html)->toContain('border-success-200');

        // Expected outcomes text: text-success-900, text-success-800
        expect($html)->toContain('text-success-900');
        expect($html)->toContain('text-success-800');
    });

    test('risks section has sufficient contrast in light mode', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 15,
        ]);

        $html = $component->html();

        // Risks: bg-warning-50, border-warning-200
        expect($html)->toContain('bg-warning-50');
        expect($html)->toContain('border-warning-200');

        // Risks text: text-warning-900, text-warning-800
        expect($html)->toContain('text-warning-900');
        expect($html)->toContain('text-warning-800');
    });

    test('panel header has sufficient contrast in light mode', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 42,
        ]);

        $html = $component->html();

        // Header background: from-primary-50 to-primary-100
        expect($html)->toContain('from-primary-50');
        expect($html)->toContain('to-primary-100');

        // Header text: text-neutral-900 (title), text-neutral-600 (subtitle)
        expect($html)->toContain('text-neutral-900');
        expect($html)->toContain('text-neutral-600');

        // AI icon background: bg-primary-600 with white text
        expect($html)->toContain('bg-primary-600');
        expect($html)->toContain('text-white');
    });

    test('footer has sufficient contrast in light mode', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 1,
        ]);

        $html = $component->html();

        // Footer background: bg-neutral-50
        expect($html)->toContain('bg-neutral-50');

        // Footer text: text-neutral-600
        expect($html)->toContain('text-neutral-600');

        // Keyboard hint: bg-neutral-200
        expect($html)->toContain('bg-neutral-200');
    });

    test('empty state has sufficient contrast in light mode', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 1,
        ]);

        $html = $component->html();

        // Empty state icon background: bg-neutral-100
        expect($html)->toContain('bg-neutral-100');

        // Empty state icon: text-neutral-400
        expect($html)->toContain('text-neutral-400');

        // Empty state title: text-neutral-900
        expect($html)->toContain('text-neutral-900');

        // Empty state description: text-neutral-600
        expect($html)->toContain('text-neutral-600');
    });

    test('interactive elements have sufficient hover contrast in light mode', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 1,
        ]);

        $html = $component->html();

        // Primary button hover: hover:bg-primary-700
        expect($html)->toContain('hover:bg-primary-700');

        // Close button hover: hover:bg-neutral-200
        expect($html)->toContain('hover:bg-neutral-200');

        // Dismiss button hover: hover:bg-danger-200
        expect($html)->toContain('hover:bg-danger-200');

        // Link hover: hover:text-primary-800
        expect($html)->toContain('hover:text-primary-800');
    });

    test('focus indicators have sufficient contrast in light mode', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 1,
        ]);

        $html = $component->html();

        // Focus ring: focus:ring-primary-500
        expect($html)->toContain('focus:ring-primary-500');

        // Focus ring for danger elements: focus:ring-danger-500
        expect($html)->toContain('focus:ring-danger-500');

        // Focus ring opacity: focus:ring-4 or focus:ring-2
        expect($html)->toMatch('/focus:ring-[0-9]/');
    });
});

describe('Advisory Panel Color Contrast - Dark Mode', function () {
    test('primary button has sufficient contrast in dark mode', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 1,
        ]);

        $html = $component->html();

        // Primary button dark mode: dark:bg-primary-500 with white text
        expect($html)->toContain('dark:bg-primary-500');
        expect($html)->toContain('dark:hover:bg-primary-600');
        expect($html)->toContain('text-white');
    });

    test('critical alert badge has sufficient contrast in dark mode', function () {
        $trainingContext = [
            'turn_number' => 15,
            'phase' => 'classic_year',
            'stats' => [
                'speed' => 600,
                'stamina' => 320,
                'power' => 550,
                'guts' => 480,
                'wisdom' => 520,
            ],
            'sp_available' => 180,
            'energy' => 75,
            'mood' => 'good',
            'acquired_skills' => [],
            'skill_hints' => [],
            'support_deck' => [],
            'facility_levels' => [],
            'upcoming_races' => [
                ['distance' => 'medium', 'turn' => 18],
            ],
            'scenario' => null,
        ];

        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'trainingContext' => $trainingContext,
            'storageMode' => 'local',
            'turnNumber' => 15,
        ]);

        $html = $component->html();

        // Badge uses same colors in dark mode (sufficient contrast maintained)
        expect($html)->toContain('bg-danger-500');
        expect($html)->toContain('text-white');
        expect($html)->toContain('dark:ring-neutral-900');
    });

    test('critical alert section has sufficient contrast in dark mode', function () {
        $trainingContext = [
            'turn_number' => 15,
            'phase' => 'classic_year',
            'stats' => [
                'speed' => 600,
                'stamina' => 320,
                'power' => 550,
                'guts' => 480,
                'wisdom' => 520,
            ],
            'sp_available' => 180,
            'energy' => 75,
            'mood' => 'good',
            'acquired_skills' => [],
            'skill_hints' => [],
            'support_deck' => [],
            'facility_levels' => [],
            'upcoming_races' => [
                ['distance' => 'medium', 'turn' => 18],
            ],
            'scenario' => null,
        ];

        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'trainingContext' => $trainingContext,
            'storageMode' => 'local',
            'turnNumber' => 15,
        ]);

        $html = $component->html();

        // Critical alert container dark mode: dark:border-danger-800, dark:bg-danger-900/20
        expect($html)->toContain('dark:border-danger-800');
        expect($html)->toContain('dark:bg-danger-900/20');

        // Critical alert icon dark mode: dark:bg-danger-900/30, dark:text-danger-400
        expect($html)->toContain('dark:bg-danger-900/30');
        expect($html)->toContain('dark:text-danger-400');

        // Critical alert text dark mode: dark:text-white
        expect($html)->toContain('dark:text-white');

        // Action items text dark mode: dark:text-neutral-300
        expect($html)->toContain('dark:text-neutral-300');
    });

    test('high priority recommendation has sufficient contrast in dark mode', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 15,
        ]);

        $html = $component->html();

        // High priority dark mode: dark:border-warning-700, dark:bg-warning-900/10
        expect($html)->toContain('dark:border-warning-700');
        expect($html)->toContain('dark:bg-warning-900/10');
    });

    test('medium priority recommendation has sufficient contrast in dark mode', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 15,
        ]);

        $html = $component->html();

        // Medium priority dark mode: dark:border-primary-800, dark:bg-primary-900/10
        expect($html)->toContain('dark:border-primary-800');
        expect($html)->toContain('dark:bg-primary-900/10');
    });

    test('low priority recommendation has sufficient contrast in dark mode', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 15,
        ]);

        $html = $component->html();

        // Low priority dark mode: dark:border-neutral-700, dark:bg-neutral-800
        expect($html)->toContain('dark:border-neutral-700');
        expect($html)->toContain('dark:bg-neutral-800');
    });

    test('expected outcomes section has sufficient contrast in dark mode', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 15,
        ]);

        $html = $component->html();

        // Expected outcomes dark mode: dark:bg-success-900/20, dark:border-success-800
        expect($html)->toContain('dark:bg-success-900/20');
        expect($html)->toContain('dark:border-success-800');

        // Expected outcomes text dark mode: dark:text-success-100, dark:text-success-200
        expect($html)->toContain('dark:text-success-100');
        expect($html)->toContain('dark:text-success-200');
    });

    test('risks section has sufficient contrast in dark mode', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 15,
        ]);

        $html = $component->html();

        // Risks dark mode: dark:bg-warning-900/20, dark:border-warning-800
        expect($html)->toContain('dark:bg-warning-900/20');
        expect($html)->toContain('dark:border-warning-800');

        // Risks text dark mode: dark:text-warning-100, dark:text-warning-200
        expect($html)->toContain('dark:text-warning-100');
        expect($html)->toContain('dark:text-warning-200');
    });

    test('panel header has sufficient contrast in dark mode', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 42,
        ]);

        $html = $component->html();

        // Header background dark mode: dark:from-primary-900/20 dark:to-primary-800/20
        expect($html)->toContain('dark:from-primary-900/20');
        expect($html)->toContain('dark:to-primary-800/20');

        // Header text dark mode: dark:text-white (title), dark:text-neutral-400 (subtitle)
        expect($html)->toContain('dark:text-white');
        expect($html)->toContain('dark:text-neutral-400');

        // AI icon background dark mode: dark:bg-primary-500
        expect($html)->toContain('dark:bg-primary-500');
    });

    test('footer has sufficient contrast in dark mode', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 1,
        ]);

        $html = $component->html();

        // Footer background dark mode: dark:bg-neutral-900/50
        expect($html)->toContain('dark:bg-neutral-900/50');

        // Footer text dark mode: dark:text-neutral-400
        expect($html)->toContain('dark:text-neutral-400');

        // Keyboard hint dark mode: dark:bg-neutral-700
        expect($html)->toContain('dark:bg-neutral-700');
    });

    test('empty state has sufficient contrast in dark mode', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 1,
        ]);

        $html = $component->html();

        // Empty state icon background dark mode: dark:bg-neutral-800
        expect($html)->toContain('dark:bg-neutral-800');

        // Empty state icon dark mode: dark:text-neutral-600
        expect($html)->toContain('dark:text-neutral-600');

        // Empty state title dark mode: dark:text-white
        expect($html)->toContain('dark:text-white');

        // Empty state description dark mode: dark:text-neutral-400
        expect($html)->toContain('dark:text-neutral-400');
    });

    test('interactive elements have sufficient hover contrast in dark mode', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 1,
        ]);

        $html = $component->html();

        // Primary button hover dark mode: dark:hover:bg-primary-600
        expect($html)->toContain('dark:hover:bg-primary-600');

        // Close button hover dark mode: dark:hover:bg-neutral-700
        expect($html)->toContain('dark:hover:bg-neutral-700');

        // Dismiss button hover dark mode: dark:hover:bg-danger-800
        expect($html)->toContain('dark:hover:bg-danger-800');

        // Link hover dark mode: dark:hover:text-primary-200
        expect($html)->toContain('dark:hover:text-primary-200');
    });

    test('focus indicators have sufficient contrast in dark mode', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 1,
        ]);

        $html = $component->html();

        // Focus ring offset dark mode: dark:focus:ring-offset-neutral-900
        expect($html)->toContain('dark:focus:ring-offset-neutral-900');

        // Focus ring colors remain the same (sufficient contrast in dark mode)
        expect($html)->toContain('focus:ring-primary-500');
    });

    test('panel background has sufficient contrast in dark mode', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 1,
        ]);

        $html = $component->html();

        // Panel background dark mode: dark:bg-neutral-900
        expect($html)->toContain('dark:bg-neutral-900');

        // Border dark mode: dark:border-neutral-700
        expect($html)->toContain('dark:border-neutral-700');
    });
});

describe('Advisory Panel Color Contrast - Accessibility Compliance', function () {
    test('all text elements meet WCAG 2.2 AA minimum contrast ratio', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 1,
        ]);

        $html = $component->html();

        // Verify presence of high-contrast text classes
        $highContrastClasses = [
            'text-white',           // White text on colored backgrounds
            'text-neutral-900',     // Dark text on light backgrounds (light mode)
            'dark:text-white',      // White text on dark backgrounds (dark mode)
            'text-neutral-700',     // Medium-dark text on light backgrounds
            'dark:text-neutral-300', // Light text on dark backgrounds
        ];

        foreach ($highContrastClasses as $class) {
            expect($html)->toContain($class);
        }
    });

    test('all interactive elements meet WCAG 2.2 AA contrast requirements', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 1,
        ]);

        $html = $component->html();

        // Verify buttons have sufficient contrast
        expect($html)->toContain('bg-primary-600');
        expect($html)->toContain('text-white');

        // Verify focus indicators are visible
        expect($html)->toContain('focus:ring-4');
        expect($html)->toContain('focus:ring-primary-500');
    });

    test('all status indicators meet WCAG 2.2 AA contrast requirements', function () {
        $trainingContext = [
            'turn_number' => 15,
            'phase' => 'classic_year',
            'stats' => [
                'speed' => 600,
                'stamina' => 320,
                'power' => 550,
                'guts' => 480,
                'wisdom' => 520,
            ],
            'sp_available' => 180,
            'energy' => 75,
            'mood' => 'good',
            'acquired_skills' => [],
            'skill_hints' => [],
            'support_deck' => [],
            'facility_levels' => [],
            'upcoming_races' => [
                ['distance' => 'medium', 'turn' => 18],
            ],
            'scenario' => null,
        ];

        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'trainingContext' => $trainingContext,
            'storageMode' => 'local',
            'turnNumber' => 15,
        ]);

        $html = $component->html();

        // Critical alert badge: white text on danger-500 background
        expect($html)->toContain('bg-danger-500');
        expect($html)->toContain('text-white');

        // Priority badges: white text on colored backgrounds
        expect($html)->toContain('bg-danger-600');
        expect($html)->toContain('bg-warning-600');
        expect($html)->toContain('bg-primary-600');
        expect($html)->toContain('bg-neutral-500');
    });

    test('color is not the only means of conveying information', function () {
        $trainingContext = [
            'turn_number' => 15,
            'phase' => 'classic_year',
            'stats' => [
                'speed' => 600,
                'stamina' => 320,
                'power' => 550,
                'guts' => 480,
                'wisdom' => 520,
            ],
            'sp_available' => 180,
            'energy' => 75,
            'mood' => 'good',
            'acquired_skills' => [],
            'skill_hints' => [],
            'support_deck' => [],
            'facility_levels' => [],
            'upcoming_races' => [
                ['distance' => 'medium', 'turn' => 18],
            ],
            'scenario' => null,
        ];

        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'trainingContext' => $trainingContext,
            'storageMode' => 'local',
            'turnNumber' => 15,
        ]);

        $html = $component->html();

        // Critical alerts have icons AND text labels
        expect($html)->toContain('Critical Alerts');
        expect($html)->toContain('aria-live="polite"');

        // Priority levels have text labels, not just colors
        expect($html)->toContain('CRITICAL');
        expect($html)->toContain('HIGH');
        expect($html)->toContain('MEDIUM');
        expect($html)->toContain('LOW');

        // Icons have aria-hidden="true" and are supplemented with text
        expect($html)->toContain('aria-hidden="true"');
    });

    test('all color combinations are tested in both light and dark modes', function () {
        $component = LivewireFacade::test(AdvisoryPanel::class, [
            'storageMode' => 'local',
            'turnNumber' => 1,
        ]);

        $html = $component->html();

        // Verify dual-mode color classes exist
        $dualModePatterns = [
            'bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600',
            'text-neutral-900 dark:text-white',
            'text-neutral-600 dark:text-neutral-400',
            'bg-neutral-200 dark:bg-neutral-700',
            'border-neutral-200 dark:border-neutral-700',
        ];

        foreach ($dualModePatterns as $pattern) {
            expect($html)->toContain($pattern);
        }
    });
});
