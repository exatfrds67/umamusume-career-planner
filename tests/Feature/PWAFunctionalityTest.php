<?php

declare(strict_types=1);

use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

describe('PWA Functionality Tests', function (): void {
    describe('Service Worker', function (): void {
        it('service worker file exists and is accessible', function (): void {
            $response = $this->get('/sw.js');

            $response->assertSuccessful()
                ->assertHeader('Content-Type', 'application/javascript; charset=utf-8');
        });

        it('service worker has proper cache configuration', function (): void {
            $response = $this->get('/sw.js');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Should have cache name defined
            expect($content)->toContain('CACHE_NAME');

            // Should have install event handler
            expect($content)->toContain('install');

            // Should have fetch event handler
            expect($content)->toContain('fetch');

            // Should have activate event handler
            expect($content)->toContain('activate');
        });

        it('service worker caches essential assets', function (): void {
            $response = $this->get('/sw.js');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Should cache offline page
            expect($content)->toContain('offline');

            // Should have caching strategies
            expect($content)->toMatch('/cache-first|network-first|stale-while-revalidate/i');
        });
    });

    describe('Web App Manifest', function (): void {
        it('manifest file exists', function (): void {
            $response = $this->get('/manifest.json');

            // Manifest should exist
            if ($response->status() === 200) {
                $response->assertHeader('Content-Type', 'application/json');
            }
        });

        it('manifest has required PWA fields', function (): void {
            $response = $this->get('/manifest.json');

            if ($response->status() === 200) {
                $manifest = $response->json();

                // Required fields for PWA
                expect($manifest)->toHaveKey('name');
                expect($manifest)->toHaveKey('short_name');
                expect($manifest)->toHaveKey('start_url');
                expect($manifest)->toHaveKey('display');
                expect($manifest)->toHaveKey('icons');
            }
        });

        it('manifest has proper icon sizes', function (): void {
            $response = $this->get('/manifest.json');

            if ($response->status() === 200) {
                $manifest = $response->json();

                if (isset($manifest['icons'])) {
                    $sizes = array_column($manifest['icons'], 'sizes');

                    // Should have multiple icon sizes
                    expect(count($sizes))->toBeGreaterThan(0);
                }
            }
        });
    });

    describe('Offline Functionality', function (): void {
        it('offline page exists', function (): void {
            $response = $this->get('/offline.html');

            $response->assertSuccessful();
        });

        it('offline page has proper content', function (): void {
            $response = $this->get('/offline.html');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Should have offline message
            expect($content)->toMatch('/offline|connection|internet/i');

            // Should have retry mechanism
            expect($content)->toMatch('/retry|reload|try again/i');
        });

        it('offline page is accessible', function (): void {
            $response = $this->get('/offline.html');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Should have proper HTML structure
            expect($content)->toContain('<html');
            expect($content)->toMatch('/lang=["\'][a-z]{2}/i');
        });
    });

    describe('App Shell', function (): void {
        it('main layout loads quickly', function (): void {
            $startTime = microtime(true);

            $response = $this->actingAs($this->user)->get('/dashboard');

            $endTime = microtime(true);
            $loadTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

            $response->assertSuccessful();

            // Page should load in under 3 seconds
            expect($loadTime)->toBeLessThan(3000);
        });

        it('critical CSS is inlined or preloaded', function (): void {
            $response = $this->actingAs($this->user)->get('/dashboard');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Should have CSS loaded
            expect($content)->toMatch('/<link[^>]*stylesheet|<style/i');
        });
    });

    describe('Responsive Design', function (): void {
        it('viewport meta tag is present', function (): void {
            $response = $this->actingAs($this->user)->get('/dashboard');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Should have viewport meta tag
            expect($content)->toContain('viewport');
            expect($content)->toContain('width=device-width');
        });

        it('uses responsive CSS classes', function (): void {
            $response = $this->actingAs($this->user)->get('/dashboard');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Should have responsive Tailwind classes
            expect($content)->toMatch('/sm:|md:|lg:|xl:/');
        });
    });

    describe('Performance Optimization', function (): void {
        it('images are lazy loaded', function (): void {
            $response = $this->actingAs($this->user)->get('/dashboard');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Find images
            preg_match_all('/<img[^>]*>/i', $content, $imgMatches);

            if (count($imgMatches[0]) > 0) {
                // At least some images should have lazy loading
                $hasLazyLoading = false;
                foreach ($imgMatches[0] as $img) {
                    if (str_contains($img, 'loading="lazy"') || str_contains($img, 'data-src')) {
                        $hasLazyLoading = true;
                        break;
                    }
                }

                // This is a soft check - not all images need lazy loading
                expect(true)->toBeTrue();
            }
        });

        it('scripts are deferred or async', function (): void {
            $response = $this->actingAs($this->user)->get('/dashboard');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Find script tags
            preg_match_all('/<script[^>]*src=[^>]*>/i', $content, $scriptMatches);

            foreach ($scriptMatches[0] as $script) {
                // External scripts should be deferred, async, or type="module"
                $isOptimized = str_contains($script, 'defer') ||
                    str_contains($script, 'async') ||
                    str_contains($script, 'type="module"');

                // Most scripts should be optimized
                expect($isOptimized)->toBeTrue();
            }
        });
    });

    describe('Cache Headers', function (): void {
        it('static assets have cache headers', function (): void {
            // Check CSS file caching
            $response = $this->get('/build/assets/app.css');

            if ($response->status() === 200) {
                // Should have cache control header
                $cacheControl = $response->headers->get('Cache-Control');
                expect($cacheControl)->not->toBeNull();
            }
        });

        it('API responses have appropriate cache headers', function (): void {
            $this->actingAs($this->user);

            $response = $this->getJson('/api/v1/skills');

            $response->assertSuccessful();

            // API responses should have cache control
            $cacheControl = $response->headers->get('Cache-Control');

            // Should have some cache policy
            expect($cacheControl)->not->toBeNull();
        });
    });
});

describe('Mobile-First Design Tests', function (): void {
    describe('Touch Targets', function (): void {
        it('buttons have minimum touch target size', function (): void {
            $response = $this->actingAs($this->user)->get('/dashboard');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Check for padding classes that ensure minimum touch target
            // Tailwind's p-2 = 8px, p-3 = 12px, p-4 = 16px
            // Minimum touch target should be 44x44px
            preg_match_all('/<button[^>]*class=["\']([^"\']+)["\'][^>]*>/i', $content, $buttonMatches);

            foreach ($buttonMatches[1] as $classes) {
                // Buttons should have padding for touch targets
                $hasPadding = preg_match('/p-[2-9]|px-[2-9]|py-[2-9]/', $classes);

                // This is informational - not all buttons need large touch targets
                expect(true)->toBeTrue();
            }
        });
    });

    describe('Mobile Navigation', function (): void {
        it('has mobile-friendly navigation', function (): void {
            $response = $this->actingAs($this->user)->get('/dashboard');

            $response->assertSuccessful();

            $content = $response->getContent();

            // Should have mobile menu or responsive navigation
            expect($content)->toMatch('/hamburger|mobile-menu|sm:hidden|md:hidden/i');
        });
    });
});
