<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Focus Management System', function () {
    it('renders skip links in the main layout', function () {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Skip to main content');
        $response->assertSee('Skip to navigation');
    });

    it('includes ARIA live region for announcements', function () {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('id="aria-live-region"', false);
        $response->assertSee('role="status"', false);
        $response->assertSee('aria-live="polite"', false);
    });

    it('includes keyboard shortcuts help component', function () {
        $response = $this->get('/');

        $response->assertStatus(200);
        // Check for keyboard shortcuts help modal
        $response->assertSee('Keyboard Shortcuts');
    });

    it('includes accessibility system JavaScript', function () {
        $response = $this->get('/');

        $response->assertStatus(200);
        // Verify the layout includes the necessary scripts
        $response->assertSee('app.js', false);
    });

    it('has proper focus indicator styles in CSS', function () {
        // Read the CSS file
        $cssContent = file_get_contents(resource_path('css/app.css'));

        // Check for focus-visible styles
        expect($cssContent)->toContain(':focus-visible');
        expect($cssContent)->toContain('outline: 2px solid');

        // Check for skip link styles
        expect($cssContent)->toContain('.skip-link');

        // Check for keyboard navigation styles
        expect($cssContent)->toContain('.keyboard-navigation');
    });

    it('includes navigation with proper ARIA labels', function () {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('id="navigation"', false);
        $response->assertSee('aria-label="Main navigation"', false);
    });

    it('includes main content landmark', function () {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('id="main-content"', false);
    });

    it('has accessible button components with focus styles', function () {
        $response = $this->get('/');

        $response->assertStatus(200);
        // Buttons should have proper classes for focus management
        $response->assertSee('class="btn', false);
    });

    it('includes accessibility settings panel', function () {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('accessibility-settings-panel', false);
    });

    it('has proper keyboard shortcut documentation', function () {
        // Check if the keyboard shortcuts help file exists
        $helpComponent = file_get_contents(resource_path('views/components/keyboard-shortcuts-help.blade.php'));

        // Verify key shortcuts are documented
        expect($helpComponent)->toContain('Alt + 1');
        expect($helpComponent)->toContain('Alt + S');
        expect($helpComponent)->toContain('Alt + /');
        expect($helpComponent)->toContain('Alt + A');
        expect($helpComponent)->toContain('Shift + ?');
        expect($helpComponent)->toContain('Escape');
        expect($helpComponent)->toContain('Tab');
    });

    it('has focus management documentation', function () {
        // Check if the focus management documentation exists
        $docPath = base_path('docs/accessibility/focus-management.md');
        expect(file_exists($docPath))->toBeTrue();

        $docContent = file_get_contents($docPath);

        // Verify key sections are documented
        expect($docContent)->toContain('Focus Management System');
        expect($docContent)->toContain('Visible Focus Indicators');
        expect($docContent)->toContain('Skip Links');
        expect($docContent)->toContain('Keyboard Shortcuts');
        expect($docContent)->toContain('Focus Trap');
        expect($docContent)->toContain('WCAG 2.2');
    });

    it('has accessibility system JavaScript with focus management', function () {
        $jsContent = file_get_contents(resource_path('js/core/AccessibilitySystem.js'));

        // Verify key focus management methods exist
        expect($jsContent)->toContain('setupFocusManagement');
        expect($jsContent)->toContain('setupSkipLinks');
        expect($jsContent)->toContain('setupKeyboardShortcuts');
        expect($jsContent)->toContain('trapFocus');
        expect($jsContent)->toContain('releaseFocus');
        expect($jsContent)->toContain('focusFirstError');
        expect($jsContent)->toContain('ensureFocusVisible');
    });

    it('registers default keyboard shortcuts', function () {
        $jsContent = file_get_contents(resource_path('js/core/AccessibilitySystem.js'));

        // Verify default shortcuts are registered
        expect($jsContent)->toContain('registerDefaultShortcuts');
        expect($jsContent)->toContain('Alt+a'); // Accessibility settings
        expect($jsContent)->toContain('Alt+s'); // Toggle sidebar
        expect($jsContent)->toContain('Alt+/'); // Focus search
        expect($jsContent)->toContain('Shift+?'); // Show keyboard shortcuts
        expect($jsContent)->toContain('Escape'); // Close modal
        expect($jsContent)->toContain('Home'); // Scroll to top
        expect($jsContent)->toContain('End'); // Scroll to bottom
    });

    it('has proper contrast ratio for focus indicators', function () {
        $cssContent = file_get_contents(resource_path('css/app.css'));

        // Check that focus indicators use primary-500 color (sufficient contrast)
        expect($cssContent)->toContain('var(--color-primary-500)');

        // Check for enhanced focus indicators in keyboard navigation mode
        expect($cssContent)->toContain('.keyboard-navigation *:focus-visible');
        expect($cssContent)->toContain('box-shadow: 0 0 0 4px');
    });

    it('includes high contrast mode support', function () {
        $cssContent = file_get_contents(resource_path('css/app.css'));

        // Check for high contrast mode styles
        expect($cssContent)->toContain('.high-contrast');
        expect($cssContent)->toContain('.high-contrast :focus-visible');
        expect($cssContent)->toContain('outline-width: 3px');
    });

    it('has keyboard navigation enhancement toggle', function () {
        $response = $this->get('/');

        $response->assertStatus(200);
        // Check that the layout includes keyboard navigation state
        $response->assertSee('keyboardNav', false);
    });
});
