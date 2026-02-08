<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;

describe('External Data Browse Performance', function () {
    beforeEach(function () {
        // Clear cache to ensure fresh measurements
        Cache::flush();

        // Create and authenticate a test user
        $this->user = \App\Models\User::factory()->create();
        $this->actingAs($this->user);
    });

    it('loads the page within 2 seconds', function () {
        $startTime = microtime(true);

        $response = $this->get('/external-data/browse');

        $endTime = microtime(true);
        $loadTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        $response->assertStatus(200);

        // Assert page loads within 2 seconds (2000ms)
        expect($loadTime)->toBeLessThan(
            2000,
            'Page load time was '.round($loadTime, 2).'ms, expected < 2000ms'
        );
    })->group('performance');

    it('API endpoints respond within acceptable time', function () {
        $endpoints = [
            'characters' => '/api/external/characters',
            'support-cards' => '/api/external/support-cards',
            'skills' => '/api/external/skills',
            'news' => '/api/external/news',
        ];

        foreach ($endpoints as $name => $endpoint) {
            $startTime = microtime(true);

            $response = $this->getJson($endpoint);

            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000;

            $response->assertStatus(200);

            // Check if response is from cache
            $isCached = $response->headers->get('X-Cache-Status') === 'HIT';

            // Assert response time targets - more lenient for external API calls
            if ($isCached) {
                expect($responseTime)->toBeLessThan(
                    1000,
                    "Cached API response for {$name} should be < 1s, got ".round($responseTime, 2).'ms'
                );
            } else {
                // External API calls can be slow, allow up to 10 seconds
                expect($responseTime)->toBeLessThan(
                    10000,
                    "Fresh API response for {$name} should be < 10s, got ".round($responseTime, 2).'ms'
                );
            }
        }
    })->group('performance')->skip(
        env('SKIP_EXTERNAL_API_TESTS', false),
        'Skipping external API performance tests'
    );

    it('measures database query performance', function () {
        // Enable query logging
        \DB::enableQueryLog();

        $startTime = microtime(true);

        $response = $this->get('/external-data/browse');

        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000;

        $queries = \DB::getQueryLog();
        $queryCount = count($queries);
        $queryTime = array_sum(array_column($queries, 'time'));

        $response->assertStatus(200);

        // Assert reasonable query count (should be minimal for this page)
        expect($queryCount)->toBeLessThan(
            20,
            "Query count should be < 20, got {$queryCount}"
        );

        // Assert query time is reasonable
        expect($queryTime)->toBeLessThan(
            500,
            'Total query time should be < 500ms, got '.round($queryTime, 2).'ms'
        );
    })->group('performance');

    it('measures memory usage', function () {
        $memoryBefore = memory_get_usage(true);

        $response = $this->get('/external-data/browse');

        $memoryAfter = memory_get_usage(true);
        $memoryUsed = ($memoryAfter - $memoryBefore) / 1024 / 1024; // Convert to MB

        $response->assertStatus(200);

        // Assert reasonable memory usage (< 50MB for a single page load)
        expect($memoryUsed)->toBeLessThan(
            50,
            'Memory usage should be < 50MB, got '.round($memoryUsed, 2).'MB'
        );
    })->group('performance');

    it('measures asset loading performance', function () {
        $response = $this->get('/external-data/browse');

        $response->assertStatus(200);

        $content = $response->getContent();

        // Count assets
        $jsCount = substr_count($content, '<script');
        $cssCount = substr_count($content, '<link rel="stylesheet"');

        // Assert reasonable asset counts
        expect($jsCount)->toBeLessThan(10, "Too many JS files: {$jsCount}");
        expect($cssCount)->toBeLessThan(5, "Too many CSS files: {$cssCount}");
    })->group('performance');

    it('measures concurrent API request performance', function () {
        $startTime = microtime(true);

        // Simulate concurrent requests like the frontend does
        $endpoints = [
            '/api/external/characters',
            '/api/external/support-cards',
            '/api/external/skills',
            '/api/external/news',
        ];

        $responses = [];
        foreach ($endpoints as $endpoint) {
            $responses[] = $this->getJson($endpoint);
        }

        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000;

        // Verify all responses are successful
        foreach ($responses as $response) {
            $response->assertStatus(200);
        }

        // Assert total time is reasonable - more lenient for external API calls
        expect($totalTime)->toBeLessThan(
            30000,
            'Concurrent API requests should complete in < 30s, got '.round($totalTime, 2).'ms'
        );
    })->group('performance')->skip(
        env('SKIP_EXTERNAL_API_TESTS', false),
        'Skipping external API performance tests'
    );
});
