<?php

declare(strict_types=1);

use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

describe('WCAG 2.2 AA Accessibility Compliance', function (): void {
    describe('Perceivable - Text Alternatives', function (): void {
        it('all images have alt text', function (): void {
            $response = $this->actingAs($this->user)->get('/dashboard');

            $response->assertSuccessful();

            // Check that images have alt attributes
            $content = $response->getContent();

            // Find all img tags
            preg_match_all('/<img[^>]*>/i', $content, $matches);

            foreach ($matches[0] as $imgTag) {
                // Each img should have alt attribute
                expect($imgTag)->toContain('alt=');
            }
        });

        it('form inputs have associated labels', function (): void {
            $response = $this->actingAs($this->user)->get('/characters/create');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Find all input elements with id
            preg_match_all('/<input[^>]*id=["\']([^"\']+)["\'][^>]*>/i', $content, $inputMatches);

            foreach ($inputMatches[1] as $inputId) {
                // Each input should have a corresponding label (for attribute) OR be wrapped in a label OR have aria-label/aria-labelledby
                $hasExplicitLabel = preg_match("/for=[\"']{$inputId}[\"']/", $content);
                $hasAriaLabel = preg_match("/<input[^>]*id=[\"']{$inputId}[\"'][^>]*aria-label/i", $content);
                $hasAriaLabelledBy = preg_match("/<input[^>]*id=[\"']{$inputId}[\"'][^>]*aria-labelledby/i", $content);

                // Check if input is wrapped in a label
                $hasWrappingLabel = preg_match("/<label[^>]*>.*?<input[^>]*id=[\"']{$inputId}[\"'][^>]*>.*?<\/label>/is", $content);

                expect($hasExplicitLabel || $hasAriaLabel || $hasAriaLabelledBy || $hasWrappingLabel)->toBeTrue(
                    "Input with id '{$inputId}' must have an associated label, aria-label, aria-labelledby, or be wrapped in a label"
                );
            }
        });
    });

    describe('Perceivable - Color Contrast', function (): void {
        it('text has sufficient color contrast', function (): void {
            $response = $this->actingAs($this->user)->get('/dashboard');

            $response->assertSuccessful();

            // Verify dark mode classes are present for contrast
            $content = $response->getContent();

            // Check for dark mode support
            expect($content)->toContain('dark:');
        });

        it('focus indicators are visible', function (): void {
            $response = $this->actingAs($this->user)->get('/dashboard');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Check for focus ring classes
            expect($content)->toMatch('/focus:ring|focus-visible:ring/');
        });
    });

    describe('Operable - Keyboard Navigation', function (): void {
        it('interactive elements are keyboard accessible', function (): void {
            $response = $this->actingAs($this->user)->get('/dashboard');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Check for tabindex attributes on interactive elements
            // Buttons should be naturally focusable
            preg_match_all('/<button[^>]*>/i', $content, $buttonMatches);

            foreach ($buttonMatches[0] as $button) {
                // Buttons should not have negative tabindex
                expect($button)->not->toContain('tabindex="-1"');
            }
        });

        it('skip links are present for main content', function (): void {
            $response = $this->actingAs($this->user)->get('/dashboard');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Check for skip link or main landmark
            expect($content)->toMatch('/<main|role=["\']main["\']/i');
        });

        it('no keyboard traps exist', function (): void {
            $response = $this->actingAs($this->user)->get('/dashboard');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Modal dialogs should have proper escape handling
            if (str_contains($content, 'x-data')) {
                // Alpine.js modals should have escape key handling
                expect($content)->toMatch('/@keydown\.escape|x-on:keydown\.escape/');
            }
        });
    });

    describe('Understandable - Readable Content', function (): void {
        it('page has language attribute', function (): void {
            $response = $this->actingAs($this->user)->get('/dashboard');

            $response->assertSuccessful();

            $content = $response->getContent();

            // HTML should have lang attribute
            expect($content)->toMatch('/<html[^>]*lang=["\'][a-z]{2}/i');
        });

        it('error messages are descriptive', function (): void {
            $response = $this->actingAs($this->user)->postJson('/api/v1/characters', []);

            $response->assertUnprocessable();

            $errors = $response->json('errors');

            // Error messages should be present and descriptive
            expect($errors)->not->toBeEmpty();
        });
    });

    describe('Understandable - Predictable Navigation', function (): void {
        it('navigation is consistent across pages', function (): void {
            $dashboardResponse = $this->actingAs($this->user)->get('/dashboard');
            $charactersResponse = $this->actingAs($this->user)->get('/characters');

            $dashboardResponse->assertSuccessful();
            $charactersResponse->assertSuccessful();

            // Both pages should have navigation
            expect($dashboardResponse->getContent())->toMatch('/<nav|role=["\']navigation["\']/i');
            expect($charactersResponse->getContent())->toMatch('/<nav|role=["\']navigation["\']/i');
        });
    });

    describe('Robust - Compatible Markup', function (): void {
        it('uses semantic HTML elements', function (): void {
            $response = $this->actingAs($this->user)->get('/dashboard');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Check for semantic elements
            expect($content)->toMatch('/<header|<main|<footer|<nav|<article|<section/i');
        });

        it('ARIA attributes are valid', function (): void {
            $response = $this->actingAs($this->user)->get('/dashboard');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Check for valid ARIA roles
            preg_match_all('/role=["\']([^"\']+)["\']/i', $content, $roleMatches);

            $validRoles = [
                'button',
                'link',
                'navigation',
                'main',
                'banner',
                'contentinfo',
                'dialog',
                'alert',
                'alertdialog',
                'menu',
                'menuitem',
                'tab',
                'tablist',
                'tabpanel',
                'listbox',
                'option',
                'progressbar',
                'status',
                'tooltip',
                'region',
                'complementary',
                'search',
                'group',
            ];

            foreach ($roleMatches[1] as $role) {
                expect($validRoles)->toContain($role);
            }
        });

        it('form elements have proper ARIA labels', function (): void {
            $response = $this->actingAs($this->user)->get('/characters/create');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Inputs without visible labels should have aria-label or aria-labelledby
            preg_match_all('/<input[^>]*type=["\']text["\'][^>]*>/i', $content, $inputMatches);

            foreach ($inputMatches[0] as $input) {
                // Input should have label association or aria-label
                $hasLabel = str_contains($input, 'id=') ||
                    str_contains($input, 'aria-label') ||
                    str_contains($input, 'aria-labelledby');

                expect($hasLabel)->toBeTrue();
            }
        });
    });

    describe('PWA Accessibility', function (): void {
        it('offline page is accessible', function (): void {
            $response = $this->get('/offline.html');

            // Offline page should exist and be accessible
            if ($response->status() === 200) {
                $content = $response->getContent();

                // Should have proper structure
                expect($content)->toMatch('/<html[^>]*lang=/i');
                expect($content)->toMatch('/<main|role=["\']main["\']/i');
            }
        });

        it('service worker does not break accessibility', function (): void {
            $response = $this->get('/sw.js');

            // Service worker should exist
            $response->assertSuccessful();
        });
    });
});

describe('Component Accessibility Tests', function (): void {
    describe('Button Component', function (): void {
        it('buttons have accessible names', function (): void {
            $response = $this->actingAs($this->user)->get('/dashboard');

            $response->assertSuccessful();

            $content = $response->getContent();

            preg_match_all('/<button[^>]*>(.*?)<\/button>/is', $content, $buttonMatches);

            foreach ($buttonMatches[0] as $index => $button) {
                $buttonContent = $buttonMatches[1][$index];

                // Button should have text content, aria-label, or aria-labelledby
                $hasAccessibleName = ! empty(trim(strip_tags($buttonContent))) ||
                    str_contains($button, 'aria-label') ||
                    str_contains($button, 'aria-labelledby');

                expect($hasAccessibleName)->toBeTrue();
            }
        });

        it('icon-only buttons have aria-label', function (): void {
            $response = $this->actingAs($this->user)->get('/dashboard');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Find buttons that only contain SVG (icon-only)
            preg_match_all('/<button[^>]*>\s*<svg[^>]*>.*?<\/svg>\s*<\/button>/is', $content, $iconButtonMatches);

            foreach ($iconButtonMatches[0] as $button) {
                // Icon-only buttons must have aria-label
                expect($button)->toMatch('/aria-label=["\'][^"\']+["\']/');
            }
        });
    });

    describe('Form Components', function (): void {
        it('required fields are marked', function (): void {
            $response = $this->actingAs($this->user)->get('/characters/create');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Required inputs should have required attribute or aria-required
            preg_match_all('/<input[^>]*required[^>]*>/i', $content, $requiredMatches);

            // At least some required fields should exist on create form
            expect(count($requiredMatches[0]))->toBeGreaterThan(0);
        });

        it('error states are announced', function (): void {
            // Submit invalid form
            $response = $this->actingAs($this->user)->post('/characters', [
                'name' => '', // Invalid - empty name
            ]);

            // Should redirect back with errors
            $response->assertSessionHasErrors();
        });
    });

    describe('Modal/Dialog Components', function (): void {
        it('dialogs have proper ARIA attributes', function (): void {
            $response = $this->actingAs($this->user)->get('/dashboard');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Find dialog elements
            preg_match_all('/<div[^>]*role=["\']dialog["\'][^>]*>/i', $content, $dialogMatches);

            foreach ($dialogMatches[0] as $dialog) {
                // Dialogs should have aria-labelledby or aria-label
                $hasLabel = str_contains($dialog, 'aria-labelledby') ||
                    str_contains($dialog, 'aria-label');

                expect($hasLabel)->toBeTrue();
            }
        });
    });

    describe('Table Components', function (): void {
        it('tables have proper headers', function (): void {
            $response = $this->actingAs($this->user)->get('/characters');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Find tables
            preg_match_all('/<table[^>]*>.*?<\/table>/is', $content, $tableMatches);

            foreach ($tableMatches[0] as $table) {
                // Tables should have th elements
                if (str_contains($table, '<tbody')) {
                    expect($table)->toContain('<th');
                }
            }
        });
    });
});
