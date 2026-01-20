<?php

declare(strict_types=1);

use App\Helpers\ImageOptimizationHelper;
use Illuminate\Support\Facades\File;

/**
 * Frontend Performance Tests
 *
 * Tests for frontend performance optimization features including:
 * - Image optimization utilities
 * - Service worker caching strategies
 * - Core Web Vitals optimization
 *
 * **Validates: Requirements 47.3, 12.5**
 */
describe('ImageOptimizationHelper', function () {
    describe('picture element generation', function () {
        it('generates picture element with modern format sources', function () {
            $html = ImageOptimizationHelper::picture(
                '/images/test.png',
                'Test image'
            );

            expect($html)->toContain('<picture>')
                ->and($html)->toContain('</picture>')
                ->and($html)->toContain('alt="Test image"')
                ->and($html)->toContain('loading="lazy"')
                ->and($html)->toContain('decoding="async"');
        });

        it('includes fallback img element', function () {
            $html = ImageOptimizationHelper::picture(
                '/images/test.png',
                'Test image'
            );

            expect($html)->toContain('<img src="/images/test.png"');
        });

        it('escapes alt text properly', function () {
            $html = ImageOptimizationHelper::picture(
                '/images/test.png',
                'Test "image" with <special> chars'
            );

            expect($html)->toContain('alt="Test &quot;image&quot; with &lt;special&gt; chars"');
        });

        it('includes additional attributes', function () {
            $html = ImageOptimizationHelper::picture(
                '/images/test.png',
                'Test image',
                ['class' => 'my-class', 'id' => 'my-id']
            );

            expect($html)->toContain('class="my-class"')
                ->and($html)->toContain('id="my-id"');
        });
    });

    describe('responsive image generation', function () {
        it('generates img element with lazy loading', function () {
            $html = ImageOptimizationHelper::responsiveImage(
                '/images/test.png',
                'Test image'
            );

            expect($html)->toContain('<img src="/images/test.png"')
                ->and($html)->toContain('alt="Test image"')
                ->and($html)->toContain('loading="lazy"')
                ->and($html)->toContain('decoding="async"');
        });

        it('includes additional attributes', function () {
            $html = ImageOptimizationHelper::responsiveImage(
                '/images/test.png',
                'Test image',
                [],
                ['class' => 'responsive-img', 'data-test' => 'value']
            );

            expect($html)->toContain('class="responsive-img"')
                ->and($html)->toContain('data-test="value"');
        });
    });

    describe('lazy image generation', function () {
        it('generates lazy loading image with data-src', function () {
            $html = ImageOptimizationHelper::lazyImage(
                '/images/test.png',
                'Test image'
            );

            expect($html)->toContain('data-src="/images/test.png"')
                ->and($html)->toContain('class="lazy-image')
                ->and($html)->toContain('loading="lazy"');
        });

        it('uses placeholder when provided', function () {
            $html = ImageOptimizationHelper::lazyImage(
                '/images/test.png',
                'Test image',
                '/images/placeholder.png'
            );

            expect($html)->toContain('src="/images/placeholder.png"')
                ->and($html)->toContain('data-src="/images/test.png"');
        });

        it('generates SVG placeholder when no placeholder provided', function () {
            $html = ImageOptimizationHelper::lazyImage(
                '/images/test.png',
                'Test image'
            );

            expect($html)->toContain('src="data:image/svg+xml,');
        });
    });

    describe('lazy background generation', function () {
        it('generates div with lazy background class', function () {
            $html = ImageOptimizationHelper::lazyBackground('/images/bg.png');

            expect($html)->toContain('class="lazy-background')
                ->and($html)->toContain('data-bg-src="/images/bg.png"');
        });

        it('includes additional classes', function () {
            $html = ImageOptimizationHelper::lazyBackground(
                '/images/bg.png',
                ['class' => 'hero-bg']
            );

            expect($html)->toContain('lazy-background')
                ->and($html)->toContain('hero-bg');
        });
    });

    describe('blur-up effect generation', function () {
        it('generates blur-up container with placeholder and main image', function () {
            $html = ImageOptimizationHelper::blurUp(
                '/images/full.png',
                '/images/placeholder.png',
                'Test image'
            );

            expect($html)->toContain('class="blur-up-container"')
                ->and($html)->toContain('class="blur-up-placeholder"')
                ->and($html)->toContain('class="blur-up-image lazy-image"')
                ->and($html)->toContain('aria-hidden="true"');
        });

        it('includes data-src for lazy loading', function () {
            $html = ImageOptimizationHelper::blurUp(
                '/images/full.png',
                '/images/placeholder.png',
                'Test image'
            );

            expect($html)->toContain('data-src="/images/full.png"');
        });
    });

    describe('placeholder generation', function () {
        it('generates SVG data URI placeholder', function () {
            $placeholder = ImageOptimizationHelper::generatePlaceholder();

            expect($placeholder)->toStartWith('data:image/svg+xml,')
                ->and($placeholder)->toContain('svg')
                ->and($placeholder)->toContain('rect');
        });

        it('uses custom dimensions', function () {
            $placeholder = ImageOptimizationHelper::generatePlaceholder(100, 50);

            expect($placeholder)->toContain('width%3D%22100%22')
                ->and($placeholder)->toContain('height%3D%2250%22');
        });

        it('uses custom color', function () {
            $placeholder = ImageOptimizationHelper::generatePlaceholder(1, 1, '#ff0000');

            expect($placeholder)->toContain('%23ff0000');
        });
    });

    describe('preload link generation', function () {
        it('generates preload link element', function () {
            $link = ImageOptimizationHelper::preloadLink('/images/hero.png');

            expect($link)->toContain('rel="preload"')
                ->and($link)->toContain('as="image"')
                ->and($link)->toContain('href="/images/hero.png"')
                ->and($link)->toContain('type="image/png"');
        });

        it('includes media query when provided', function () {
            $link = ImageOptimizationHelper::preloadLink(
                '/images/hero.png',
                'image/png',
                '(min-width: 768px)'
            );

            expect($link)->toContain('media="(min-width: 768px)"');
        });

        it('uses custom image type', function () {
            $link = ImageOptimizationHelper::preloadLink('/images/hero.webp', 'image/webp');

            expect($link)->toContain('type="image/webp"');
        });
    });

    describe('responsive preload generation', function () {
        it('generates multiple preload links', function () {
            $links = ImageOptimizationHelper::preloadResponsive([
                ['src' => '/images/hero-mobile.png', 'media' => '(max-width: 767px)'],
                ['src' => '/images/hero-desktop.png', 'media' => '(min-width: 768px)'],
            ]);

            expect($links)->toContain('href="/images/hero-mobile.png"')
                ->and($links)->toContain('href="/images/hero-desktop.png"')
                ->and($links)->toContain('media="(max-width: 767px)"')
                ->and($links)->toContain('media="(min-width: 768px)"');
        });
    });

    describe('aspect ratio calculation', function () {
        it('calculates correct aspect ratio padding', function () {
            // 16:9 aspect ratio
            $padding = ImageOptimizationHelper::calculateAspectRatioPadding(1920, 1080);
            expect($padding)->toBe(56.25);

            // 4:3 aspect ratio
            $padding = ImageOptimizationHelper::calculateAspectRatioPadding(800, 600);
            expect($padding)->toBe(75.0);

            // 1:1 aspect ratio
            $padding = ImageOptimizationHelper::calculateAspectRatioPadding(500, 500);
            expect($padding)->toBe(100.0);
        });

        it('handles zero width gracefully', function () {
            $padding = ImageOptimizationHelper::calculateAspectRatioPadding(0, 100);
            expect($padding)->toBe(0.0);
        });
    });
});

describe('Service Worker', function () {
    it('service worker file exists', function () {
        expect(File::exists(public_path('sw.js')))->toBeTrue();
    });

    it('service worker contains cache version', function () {
        $content = File::get(public_path('sw.js'));

        expect($content)->toContain('CACHE_VERSION')
            ->and($content)->toContain('CACHE_NAME');
    });

    it('service worker contains precache assets', function () {
        $content = File::get(public_path('sw.js'));

        expect($content)->toContain('PRECACHE_ASSETS')
            ->and($content)->toContain('/offline.html')
            ->and($content)->toContain('/manifest.json');
    });

    it('service worker implements caching strategies', function () {
        $content = File::get(public_path('sw.js'));

        expect($content)->toContain('networkFirstStrategy')
            ->and($content)->toContain('cacheFirstStrategy')
            ->and($content)->toContain('staleWhileRevalidate');
    });

    it('service worker handles different request types', function () {
        $content = File::get(public_path('sw.js'));

        expect($content)->toContain('isNavigationRequest')
            ->and($content)->toContain('isImageRequest')
            ->and($content)->toContain('isStaticAsset')
            ->and($content)->toContain('isApiRequest');
    });

    it('service worker implements cache size limits', function () {
        $content = File::get(public_path('sw.js'));

        expect($content)->toContain('CACHE_LIMITS')
            ->and($content)->toContain('trimCache');
    });

    it('service worker handles messages from clients', function () {
        $content = File::get(public_path('sw.js'));

        expect($content)->toContain('SKIP_WAITING')
            ->and($content)->toContain('CLEAR_CACHE')
            ->and($content)->toContain('CLEAR_API_CACHE')
            ->and($content)->toContain('PRECACHE_ASSETS')
            ->and($content)->toContain('GET_CACHE_STATUS');
    });
});

describe('Offline Page', function () {
    it('offline page exists', function () {
        expect(File::exists(public_path('offline.html')))->toBeTrue();
    });

    it('offline page contains retry functionality', function () {
        $content = File::get(public_path('offline.html'));

        expect($content)->toContain('retryConnection')
            ->and($content)->toContain('navigator.onLine');
    });

    it('offline page supports dark mode', function () {
        $content = File::get(public_path('offline.html'));

        expect($content)->toContain('prefers-color-scheme: dark');
    });

    it('offline page is accessible', function () {
        $content = File::get(public_path('offline.html'));

        expect($content)->toContain('aria-hidden')
            ->and($content)->toContain('alt=');
    });
});

describe('Vite Configuration', function () {
    it('vite config file exists', function () {
        expect(File::exists(base_path('vite.config.js')))->toBeTrue();
    });

    it('vite config contains code splitting configuration', function () {
        $content = File::get(base_path('vite.config.js'));

        expect($content)->toContain('manualChunks')
            ->and($content)->toContain('rollupOptions');
    });

    it('vite config contains asset optimization', function () {
        $content = File::get(base_path('vite.config.js'));

        expect($content)->toContain('assetFileNames')
            ->and($content)->toContain('chunkFileNames')
            ->and($content)->toContain('entryFileNames');
    });

    it('vite config targets modern browsers', function () {
        $content = File::get(base_path('vite.config.js'));

        expect($content)->toContain('target')
            ->and($content)->toContain('esnext');
    });

    it('vite config enables CSS code splitting', function () {
        $content = File::get(base_path('vite.config.js'));

        expect($content)->toContain('cssCodeSplit');
    });
});

describe('Performance JavaScript Modules', function () {
    it('ImageOptimization module exists', function () {
        expect(File::exists(resource_path('js/core/ImageOptimization.js')))->toBeTrue();
    });

    it('ImageOptimization module contains format detection', function () {
        $content = File::get(resource_path('js/core/ImageOptimization.js'));

        expect($content)->toContain('checkAvifSupport')
            ->and($content)->toContain('checkWebpSupport')
            ->and($content)->toContain('getBestFormat');
    });

    it('ImageOptimization module contains lazy loading', function () {
        $content = File::get(resource_path('js/core/ImageOptimization.js'));

        expect($content)->toContain('IntersectionObserver')
            ->and($content)->toContain('lazy-image')
            ->and($content)->toContain('image-loaded');
    });

    it('PerformanceMonitor module exists', function () {
        expect(File::exists(resource_path('js/core/PerformanceMonitor.js')))->toBeTrue();
    });

    it('PerformanceMonitor module tracks Core Web Vitals', function () {
        $content = File::get(resource_path('js/core/PerformanceMonitor.js'));

        expect($content)->toContain('LCP')
            ->and($content)->toContain('INP')
            ->and($content)->toContain('CLS')
            ->and($content)->toContain('FCP')
            ->and($content)->toContain('TTFB');
    });

    it('PerformanceMonitor module contains correct thresholds', function () {
        $content = File::get(resource_path('js/core/PerformanceMonitor.js'));

        // LCP threshold: 2.5s = 2500ms
        expect($content)->toContain('2500')
            // INP threshold: 200ms
            ->and($content)->toContain('200')
            // CLS threshold: 0.1
            ->and($content)->toContain('0.1');
    });

    it('PerformanceMonitor module uses PerformanceObserver', function () {
        $content = File::get(resource_path('js/core/PerformanceMonitor.js'));

        expect($content)->toContain('PerformanceObserver')
            ->and($content)->toContain('largest-contentful-paint')
            ->and($content)->toContain('layout-shift');
    });
});

describe('CSS Performance Optimizations', function () {
    it('app.css contains lazy loading styles', function () {
        $content = File::get(resource_path('css/app.css'));

        expect($content)->toContain('.lazy-image')
            ->and($content)->toContain('.lazy-background')
            ->and($content)->toContain('.image-loaded');
    });

    it('app.css contains blur-up effect styles', function () {
        $content = File::get(resource_path('css/app.css'));

        expect($content)->toContain('.blur-up-container')
            ->and($content)->toContain('.blur-up-placeholder')
            ->and($content)->toContain('.blur-up-image');
    });

    it('app.css contains loading placeholder animation', function () {
        $content = File::get(resource_path('css/app.css'));

        expect($content)->toContain('.image-placeholder')
            ->and($content)->toContain('loading-shimmer');
    });

    it('app.css contains WebP support detection classes', function () {
        $content = File::get(resource_path('css/app.css'));

        expect($content)->toContain('.webp')
            ->and($content)->toContain('.no-webp');
    });
});
