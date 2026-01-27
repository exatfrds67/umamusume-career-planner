<?php

/**
 * Accessibility Settings Panel Tests
 *
 * Tests for the accessibility settings panel component including:
 * - Panel rendering and structure
 * - WCAG 2.2 AA compliance
 * - Keyboard navigation
 * - ARIA attributes
 * - Settings persistence
 */

use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

describe('Accessibility Settings Panel Rendering', function () {
    it('renders accessibility settings panel component', function () {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertOk();

        // The panel should be included in the layout
        $response->assertSee('accessibility-settings-panel', false);
    });

    it('includes proper ARIA attributes for dialog', function () {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertOk();

        // Check for dialog role and aria attributes
        $response->assertSee('role="dialog"', false);
        $response->assertSee('aria-modal="true"', false);
        $response->assertSee('aria-labelledby="accessibility-settings-title"', false);
    });

    it('has accessible title', function () {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('id="accessibility-settings-title"', false);
        $response->assertSee('Accessibility Settings', false);
    });
});

describe('Accessibility Controls', function () {
    it('includes text size control', function () {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Text Size', false);
        $response->assertSee('aria-label="Decrease text size"', false);
        $response->assertSee('aria-label="Increase text size"', false);
    });

    it('includes high contrast toggle', function () {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('High Contrast Mode', false);
        $response->assertSee('id="high-contrast-help"', false);
    });

    it('includes reduced motion toggle', function () {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Reduced Motion', false);
        $response->assertSee('id="reduced-motion-help"', false);
    });

    it('includes keyboard navigation toggle', function () {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Enhanced Keyboard Navigation', false);
        $response->assertSee('id="keyboard-nav-help"', false);
    });
});

describe('Keyboard Shortcuts Documentation', function () {
    it('displays keyboard shortcuts information', function () {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Keyboard Shortcuts', false);
        $response->assertSee('Alt + A', false);
        $response->assertSee('Alt + S', false);
        $response->assertSee('Escape', false);
    });
});

describe('Panel Actions', function () {
    it('has close button with accessible label', function () {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('aria-label="Close accessibility settings"', false);
    });

    it('has reset to defaults button', function () {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Reset to Defaults', false);
    });

    it('has done button', function () {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertOk();
        // The Done button is in the accessibility panel footer
        $response->assertSee('btn btn-primary btn-md flex-1', false);
        $response->assertSee('Done', false);
    });
});

describe('WCAG Compliance', function () {
    it('uses aria-describedby for form controls', function () {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertOk();

        // Check that toggles have aria-describedby pointing to help text
        $response->assertSee('aria-describedby="high-contrast-help"', false);
        $response->assertSee('aria-describedby="reduced-motion-help"', false);
        $response->assertSee('aria-describedby="keyboard-nav-help"', false);
    });

    it('uses aria-live for dynamic content', function () {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertOk();

        // Text size value should be announced to screen readers
        $response->assertSee('aria-live="polite"', false);
        $response->assertSee('aria-atomic="true"', false);
    });

    it('has proper heading structure', function () {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertOk();

        // Panel should have h2 heading
        $response->assertSee('<h2 id="accessibility-settings-title"', false);
    });
});

describe('Settings Status Display', function () {
    it('displays current settings status', function () {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertOk();

        // Status section should show all settings
        $response->assertSee('accessibility-status', false);
        $response->assertSee('Text Size:', false);
        $response->assertSee('High Contrast:', false);
        $response->assertSee('Reduced Motion:', false);
        $response->assertSee('Keyboard Navigation:', false);
    });
});

describe('Alpine.js Integration', function () {
    it('uses Alpine.js persist for settings', function () {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertOk();

        // Check for Alpine persist directives
        $response->assertSee('$persist', false);
        $response->assertSee('accessibility_text_size', false);
        $response->assertSee('accessibility_high_contrast', false);
        $response->assertSee('accessibility_reduced_motion', false);
        $response->assertSee('accessibility_keyboard_nav', false);
    });

    it('has escape key handler for closing', function () {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('@keydown.escape.window', false);
    });
});
