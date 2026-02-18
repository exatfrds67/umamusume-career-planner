<?php

declare(strict_types=1);

namespace Tests\Unit\Components;

use Illuminate\Support\Facades\Blade;
use Illuminate\View\Component;

/**
 * Recommendation Card Component Unit Test
 *
 * Tests the recommendation card Blade component structure and Alpine.js integration:
 * - Component rendering
 * - Alpine.js data initialization
 * - Props handling
 * - Accessibility attributes
 *
 * @group unit
 * @group components
 * @group ai
 */
it('renders recommendation card with required attributes', function () {
    $recommendation = (object) [
        'id' => 'rec-123',
        'action' => 'Train Speed at Facility A',
        'priority' => 'high',
        'confidence_score' => 0.85,
        'reasoning' => 'High synergy with support cards',
        'expected_outcomes' => [
            'speed_gain' => '+15 Speed',
            'bond_increase' => '+5 Bond',
        ],
        'risks' => ['Energy consumption: -20'],
    ];

    $html = Blade::render(
        '<x-ai.recommendation-card :recommendation="$recommendation" />',
        ['recommendation' => $recommendation]
    );

    // Check for article role
    expect($html)->toContain('role="article"');

    // Check for Alpine.js x-data directive
    expect($html)->toContain('x-data=');

    // Check for expanded state initialization
    expect($html)->toContain('expanded:');

    // Check for dismissed state initialization
    expect($html)->toContain('dismissed:');

    // Check for aria-expanded attribute
    expect($html)->toContain('aria-expanded');

    // Check for recommendation title
    expect($html)->toContain('Train Speed at Facility A');

    // Check for priority badge
    expect($html)->toContain('Priority:');
    expect($html)->toContain('High');
});

it('initializes Alpine.js with correct default state', function () {
    $recommendation = (object) [
        'id' => 'rec-456',
        'action' => 'Test Action',
        'priority' => 'medium',
    ];

    $html = Blade::render(
        '<x-ai.recommendation-card :recommendation="$recommendation" />',
        ['recommendation' => $recommendation]
    );

    // Check expanded defaults to false
    expect($html)->toContain('expanded: false');

    // Check dismissed defaults to false
    expect($html)->toContain('dismissed: false');
});

it('respects expanded prop when set to true', function () {
    $recommendation = (object) [
        'id' => 'rec-789',
        'action' => 'Test Action',
        'priority' => 'low',
    ];

    $html = Blade::render(
        '<x-ai.recommendation-card :recommendation="$recommendation" :expanded="true" />',
        ['recommendation' => $recommendation]
    );

    // Check expanded is set to true
    expect($html)->toContain('expanded: true');
});

it('includes x-show directive for dismissal', function () {
    $recommendation = (object) [
        'id' => 'rec-dismiss',
        'action' => 'Test Action',
        'priority' => 'critical',
    ];

    $html = Blade::render(
        '<x-ai.recommendation-card :recommendation="$recommendation" />',
        ['recommendation' => $recommendation]
    );

    // Check for x-show="!dismissed"
    expect($html)->toContain('x-show="!dismissed"');
});

it('includes x-collapse directive for expand/collapse animation', function () {
    $recommendation = (object) [
        'id' => 'rec-collapse',
        'action' => 'Test Action',
        'priority' => 'high',
    ];

    $html = Blade::render(
        '<x-ai.recommendation-card :recommendation="$recommendation" />',
        ['recommendation' => $recommendation]
    );

    // Check for x-collapse directive
    expect($html)->toContain('x-collapse');
});

it('includes click handler for expand/collapse', function () {
    $recommendation = (object) [
        'id' => 'rec-click',
        'action' => 'Test Action',
        'priority' => 'medium',
    ];

    $html = Blade::render(
        '<x-ai.recommendation-card :recommendation="$recommendation" />',
        ['recommendation' => $recommendation]
    );

    // Check for @click directive
    expect($html)->toContain('@click="expanded = !expanded"');
});

it('includes click handler for dismiss button', function () {
    $recommendation = (object) [
        'id' => 'rec-dismiss-btn',
        'action' => 'Test Action',
        'priority' => 'low',
    ];

    $html = Blade::render(
        '<x-ai.recommendation-card :recommendation="$recommendation" />',
        ['recommendation' => $recommendation]
    );

    // Check for dismiss click handler
    expect($html)->toContain('@click="dismissed = true"');
});

it('binds aria-expanded to Alpine.js state', function () {
    $recommendation = (object) [
        'id' => 'rec-aria',
        'action' => 'Test Action',
        'priority' => 'high',
    ];

    $html = Blade::render(
        '<x-ai.recommendation-card :recommendation="$recommendation" />',
        ['recommendation' => $recommendation]
    );

    // Check for x-bind:aria-expanded
    expect($html)->toContain('x-bind:aria-expanded="expanded.toString()"');
});

it('includes transition directives for smooth animations', function () {
    $recommendation = (object) [
        'id' => 'rec-transition',
        'action' => 'Test Action',
        'priority' => 'critical',
    ];

    $html = Blade::render(
        '<x-ai.recommendation-card :recommendation="$recommendation" />',
        ['recommendation' => $recommendation]
    );

    // Check for transition directives
    expect($html)->toContain('x-transition:leave');
    expect($html)->toContain('x-transition:leave-start');
    expect($html)->toContain('x-transition:leave-end');
});

it('applies correct priority color classes', function () {
    $priorities = [
        'critical' => 'red',
        'high' => 'orange',
        'medium' => 'blue',
        'low' => 'neutral',
    ];

    foreach ($priorities as $priority => $color) {
        $recommendation = (object) [
            'id' => "rec-{$priority}",
            'action' => 'Test Action',
            'priority' => $priority,
        ];

        $html = Blade::render(
            '<x-ai.recommendation-card :recommendation="$recommendation" />',
            ['recommendation' => $recommendation]
        );

        // Check for color-specific classes
        expect($html)->toContain("border-{$color}");
        expect($html)->toContain("bg-{$color}");
    }
});

it('displays priority icon based on priority level', function () {
    $icons = [
        'critical' => '🚨',
        'high' => '⭐',
        'medium' => '💡',
        'low' => 'ℹ️',
    ];

    foreach ($icons as $priority => $icon) {
        $recommendation = (object) [
            'id' => "rec-icon-{$priority}",
            'action' => 'Test Action',
            'priority' => $priority,
        ];

        $html = Blade::render(
            '<x-ai.recommendation-card :recommendation="$recommendation" />',
            ['recommendation' => $recommendation]
        );

        // Check for priority icon
        expect($html)->toContain($icon);
    }
});

it('displays confidence score when provided', function () {
    $recommendation = (object) [
        'id' => 'rec-confidence',
        'action' => 'Test Action',
        'priority' => 'high',
        'confidence_score' => 0.92,
    ];

    $html = Blade::render(
        '<x-ai.recommendation-card :recommendation="$recommendation" />',
        ['recommendation' => $recommendation]
    );

    // Check for confidence display
    expect($html)->toContain('Confidence:');
    expect($html)->toContain('92%');
});

it('hides confidence score when not provided', function () {
    $recommendation = (object) [
        'id' => 'rec-no-confidence',
        'action' => 'Test Action',
        'priority' => 'medium',
    ];

    $html = Blade::render(
        '<x-ai.recommendation-card :recommendation="$recommendation" />',
        ['recommendation' => $recommendation]
    );

    // Should not contain confidence display
    expect($html)->not->toContain('Confidence:');
});

it('displays reasoning section when provided', function () {
    $recommendation = (object) [
        'id' => 'rec-reasoning',
        'action' => 'Test Action',
        'priority' => 'high',
        'reasoning' => "Line 1\nLine 2\nLine 3",
    ];

    $html = Blade::render(
        '<x-ai.recommendation-card :recommendation="$recommendation" />',
        ['recommendation' => $recommendation]
    );

    // Check for reasoning section
    expect($html)->toContain('Reasoning');
    expect($html)->toContain('Line 1');
    expect($html)->toContain('Line 2');
    expect($html)->toContain('Line 3');
});

it('displays expected outcomes section when provided', function () {
    $recommendation = (object) [
        'id' => 'rec-outcomes',
        'action' => 'Test Action',
        'priority' => 'medium',
        'expected_outcomes' => [
            'speed_gain' => '+20 Speed',
            'stamina_gain' => '+10 Stamina',
        ],
    ];

    $html = Blade::render(
        '<x-ai.recommendation-card :recommendation="$recommendation" />',
        ['recommendation' => $recommendation]
    );

    // Check for outcomes section
    expect($html)->toContain('Expected Outcomes');
    expect($html)->toContain('+20 Speed');
    expect($html)->toContain('+10 Stamina');
});

it('displays risks section when provided', function () {
    $recommendation = (object) [
        'id' => 'rec-risks',
        'action' => 'Test Action',
        'priority' => 'critical',
        'risks' => [
            'High energy consumption',
            'May trigger injury',
        ],
    ];

    $html = Blade::render(
        '<x-ai.recommendation-card :recommendation="$recommendation" />',
        ['recommendation' => $recommendation]
    );

    // Check for risks section
    expect($html)->toContain('Risks');
    expect($html)->toContain('High energy consumption');
    expect($html)->toContain('May trigger injury');
});

it('includes action buttons when showActions is true', function () {
    $recommendation = (object) [
        'id' => 'rec-actions',
        'action' => 'Test Action',
        'priority' => 'high',
    ];

    $html = Blade::render(
        '<x-ai.recommendation-card :recommendation="$recommendation" :showActions="true" />',
        ['recommendation' => $recommendation]
    );

    // Check for action buttons
    expect($html)->toContain('Apply Recommendation');
    expect($html)->toContain('Dismiss');
    expect($html)->toContain('Feedback');
});

it('hides action buttons when showActions is false', function () {
    $recommendation = (object) [
        'id' => 'rec-no-actions',
        'action' => 'Test Action',
        'priority' => 'medium',
    ];

    $html = Blade::render(
        '<x-ai.recommendation-card :recommendation="$recommendation" :showActions="false" />',
        ['recommendation' => $recommendation]
    );

    // Should not contain action buttons
    expect($html)->not->toContain('Apply Recommendation');
    expect($html)->not->toContain('wire:click="applyRecommendation');
});

it('includes Livewire wire:click for apply action', function () {
    $recommendation = (object) [
        'id' => 'rec-wire-apply',
        'action' => 'Test Action',
        'priority' => 'high',
    ];

    $html = Blade::render(
        '<x-ai.recommendation-card :recommendation="$recommendation" />',
        ['recommendation' => $recommendation]
    );

    // Check for Livewire directive
    expect($html)->toContain('wire:click="applyRecommendation');
});

it('includes Livewire wire:click for feedback action', function () {
    $recommendation = (object) [
        'id' => 'rec-wire-feedback',
        'action' => 'Test Action',
        'priority' => 'medium',
    ];

    $html = Blade::render(
        '<x-ai.recommendation-card :recommendation="$recommendation" />',
        ['recommendation' => $recommendation]
    );

    // Check for Livewire directive
    expect($html)->toContain('wire:click="provideFeedback');
});

it('has proper keyboard navigation attributes', function () {
    $recommendation = (object) [
        'id' => 'rec-keyboard',
        'action' => 'Test Action',
        'priority' => 'high',
    ];

    $html = Blade::render(
        '<x-ai.recommendation-card :recommendation="$recommendation" />',
        ['recommendation' => $recommendation]
    );

    // Check for keyboard attributes
    expect($html)->toContain('aria-controls');
    expect($html)->toContain('aria-label');
});

it('includes focus ring classes for accessibility', function () {
    $recommendation = (object) [
        'id' => 'rec-focus',
        'action' => 'Test Action',
        'priority' => 'critical',
    ];

    $html = Blade::render(
        '<x-ai.recommendation-card :recommendation="$recommendation" />',
        ['recommendation' => $recommendation]
    );

    // Check for focus ring classes
    expect($html)->toContain('focus:ring');
    expect($html)->toContain('focus:outline-none');
});

it('includes dark mode classes', function () {
    $recommendation = (object) [
        'id' => 'rec-dark',
        'action' => 'Test Action',
        'priority' => 'medium',
    ];

    $html = Blade::render(
        '<x-ai.recommendation-card :recommendation="$recommendation" />',
        ['recommendation' => $recommendation]
    );

    // Check for dark mode classes
    expect($html)->toContain('dark:');
});

it('generates unique card ID for each instance', function () {
    $recommendation = (object) [
        'id' => 'rec-unique',
        'action' => 'Test Action',
        'priority' => 'low',
    ];

    $html1 = Blade::render(
        '<x-ai.recommendation-card :recommendation="$recommendation" />',
        ['recommendation' => $recommendation]
    );

    $html2 = Blade::render(
        '<x-ai.recommendation-card :recommendation="$recommendation" />',
        ['recommendation' => $recommendation]
    );

    // Extract card IDs from both renders
    preg_match('/id="(recommendation-[^"]+)-title"/', $html1, $matches1);
    preg_match('/id="(recommendation-[^"]+)-title"/', $html2, $matches2);

    // IDs should be different
    expect($matches1[1])->not->toBe($matches2[1]);
});

it('renders without errors', function () {
    $recommendation = (object) [
        'id' => 'rec-css',
        'action' => 'Test Action',
        'priority' => 'high',
    ];

    $html = Blade::render(
        '<x-ai.recommendation-card :recommendation="$recommendation" />',
        ['recommendation' => $recommendation]
    );

    // Component should render successfully
    expect($html)->toBeString();
    expect($html)->not->toBeEmpty();
});

it('renders complete HTML structure', function () {
    $recommendation = (object) [
        'id' => 'rec-structure',
        'action' => 'Test Action',
        'priority' => 'medium',
    ];

    $html = Blade::render(
        '<x-ai.recommendation-card :recommendation="$recommendation" />',
        ['recommendation' => $recommendation]
    );

    // Check for complete structure
    expect($html)->toContain('<div x-data');
    expect($html)->toContain('</div>');
    expect($html)->toContain('<button');
    expect($html)->toContain('</button>');
});

it('handles empty expected outcomes array gracefully', function () {
    $recommendation = (object) [
        'id' => 'rec-empty-outcomes',
        'action' => 'Test Action',
        'priority' => 'low',
        'expected_outcomes' => [],
    ];

    $html = Blade::render(
        '<x-ai.recommendation-card :recommendation="$recommendation" />',
        ['recommendation' => $recommendation]
    );

    // Should not show outcomes section
    expect($html)->not->toContain('Expected Outcomes');
});

it('handles empty risks array gracefully', function () {
    $recommendation = (object) [
        'id' => 'rec-empty-risks',
        'action' => 'Test Action',
        'priority' => 'high',
        'risks' => [],
    ];

    $html = Blade::render(
        '<x-ai.recommendation-card :recommendation="$recommendation" />',
        ['recommendation' => $recommendation]
    );

    // Should not show risks section
    expect($html)->not->toContain('Risks');
});

it('rotates chevron icon based on expanded state', function () {
    $recommendation = (object) [
        'id' => 'rec-chevron',
        'action' => 'Test Action',
        'priority' => 'medium',
    ];

    $html = Blade::render(
        '<x-ai.recommendation-card :recommendation="$recommendation" />',
        ['recommendation' => $recommendation]
    );

    // Check for rotation class binding
    expect($html)->toContain(':class="{ \'rotate-180\': expanded }"');
});
