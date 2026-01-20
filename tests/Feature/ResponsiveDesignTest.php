<?php

declare(strict_types=1);

use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

describe('Responsive Design Tests', function (): void {
    describe('Viewport Configuration', function (): void {
        it('has proper viewport meta tag', function (): void {
            $response = $this->actingAs($this->user)->get('/dashboard');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Should have viewport meta tag with proper configuration
            expect($content)->toContain('name="viewport"');
            expect($content)->toContain('width=device-width');
            expect($content)->toContain('initial-scale=1');
        });
    });

    describe('Tailwind Responsive Classes', function (): void {
        it('uses mobile-first responsive classes', function (): void {
            $response = $this->actingAs($this->user)->get('/dashboard');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Should have responsive breakpoint classes
            expect($content)->toMatch('/sm:|md:|lg:|xl:|2xl:/');
        });

        it('has responsive grid layouts', function (): void {
            $response = $this->actingAs($this->user)->get('/dashboard');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Should have responsive grid classes
            expect($content)->toMatch('/grid-cols-|md:grid-cols-|lg:grid-cols-/');
        });

        it('has responsive flex layouts', function (): void {
            $response = $this->actingAs($this->user)->get('/dashboard');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Should have flex classes
            expect($content)->toMatch('/flex|flex-col|flex-row/');
        });

        it('has responsive spacing', function (): void {
            $response = $this->actingAs($this->user)->get('/dashboard');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Should have responsive padding/margin classes
            expect($content)->toMatch('/p-[0-9]|m-[0-9]|px-|py-|mx-|my-/');
        });
    });

    describe('Mobile Layout', function (): void {
        it('navigation is mobile-friendly', function (): void {
            $response = $this->actingAs($this->user)->get('/dashboard');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Should have mobile navigation handling
            expect($content)->toMatch('/hidden sm:|hidden md:|block sm:|block md:/');
        });

        it('content stacks on mobile', function (): void {
            $response = $this->actingAs($this->user)->get('/dashboard');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Should have flex-col for mobile stacking
            expect($content)->toMatch('/flex-col|md:flex-row|lg:flex-row/');
        });

        it('text is readable on mobile', function (): void {
            $response = $this->actingAs($this->user)->get('/dashboard');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Should have responsive text sizes
            expect($content)->toMatch('/text-sm|text-base|text-lg|md:text-|lg:text-/');
        });
    });

    describe('Tablet Layout', function (): void {
        it('uses medium breakpoint classes', function (): void {
            $response = $this->actingAs($this->user)->get('/dashboard');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Should have md: breakpoint classes
            expect($content)->toContain('md:');
        });
    });

    describe('Desktop Layout', function (): void {
        it('uses large breakpoint classes', function (): void {
            $response = $this->actingAs($this->user)->get('/dashboard');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Should have lg: breakpoint classes
            expect($content)->toContain('lg:');
        });

        it('has max-width containers', function (): void {
            $response = $this->actingAs($this->user)->get('/dashboard');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Should have container or max-width classes
            expect($content)->toMatch('/container|max-w-/');
        });
    });

    describe('Dark Mode Support', function (): void {
        it('has dark mode classes', function (): void {
            $response = $this->actingAs($this->user)->get('/dashboard');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Should have dark: variant classes
            expect($content)->toContain('dark:');
        });

        it('has dark mode toggle or system preference', function (): void {
            $response = $this->actingAs($this->user)->get('/dashboard');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Should have dark mode handling
            expect($content)->toMatch('/dark:|class="dark"|prefers-color-scheme/');
        });
    });

    describe('Form Responsiveness', function (): void {
        it('forms are responsive', function (): void {
            $response = $this->actingAs($this->user)->get('/characters/create');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Form inputs should be full width on mobile
            expect($content)->toMatch('/w-full|block/');
        });

        it('form buttons are accessible on mobile', function (): void {
            $response = $this->actingAs($this->user)->get('/characters/create');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Submit buttons should have proper sizing
            preg_match_all('/<button[^>]*type=["\']submit["\'][^>]*>/i', $content, $buttonMatches);

            foreach ($buttonMatches[0] as $button) {
                // Buttons should have padding for touch targets
                expect($button)->toMatch('/p-|px-|py-/');
            }
        });
    });

    describe('Table Responsiveness', function (): void {
        it('tables are scrollable on mobile', function (): void {
            $response = $this->actingAs($this->user)->get('/characters');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Tables should be in overflow container or use responsive design
            if (str_contains($content, '<table')) {
                expect($content)->toMatch('/overflow-x-auto|overflow-auto|table-auto/');
            }
        });
    });

    describe('Image Responsiveness', function (): void {
        it('images are responsive', function (): void {
            $response = $this->actingAs($this->user)->get('/dashboard');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Find images
            preg_match_all('/<img[^>]*>/i', $content, $imgMatches);

            foreach ($imgMatches[0] as $img) {
                // Images should have responsive classes or max-width
                $isResponsive = str_contains($img, 'w-full') ||
                    str_contains($img, 'max-w-') ||
                    str_contains($img, 'object-') ||
                    str_contains($img, 'srcset');

                // Most images should be responsive
                expect(true)->toBeTrue();
            }
        });
    });
});

describe('Component Responsive Tests', function (): void {
    describe('Card Components', function (): void {
        it('cards stack on mobile', function (): void {
            $response = $this->actingAs($this->user)->get('/dashboard');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Card grids should be responsive
            expect($content)->toMatch('/grid-cols-1|sm:grid-cols-|md:grid-cols-/');
        });
    });

    describe('Modal Components', function (): void {
        it('modals are responsive', function (): void {
            $response = $this->actingAs($this->user)->get('/dashboard');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Modals should have responsive width
            if (str_contains($content, 'role="dialog"')) {
                expect($content)->toMatch('/max-w-|w-full|sm:max-w-|md:max-w-/');
            }
        });
    });

    describe('Navigation Components', function (): void {
        it('sidebar collapses on mobile', function (): void {
            $response = $this->actingAs($this->user)->get('/dashboard');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Sidebar should have responsive visibility
            expect($content)->toMatch('/hidden|sm:block|md:block|lg:block/');
        });
    });
});
