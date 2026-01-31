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

        // Log the actual load time
        echo "\n=== Page Load Performance ===\n";
        echo 'Page load time: '.round($loadTime, 2)."ms\n";
        echo "Target: < 2000ms\n";
        echo 'Status: '.($loadTime < 2000 ? '✓ PASS' : '✗ FAIL')."\n";
        echo "============================\n";

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

        echo "\n=== API Response Times ===\n";

        foreach ($endpoints as $name => $endpoint) {
            $startTime = microtime(true);

            $response = $this->getJson($endpoint);

            $endTime = microtime(true);
            $responseTime = ($endTime - $startTime) * 1000;

            $response->assertStatus(200);

            // Check if response is from cache
            $isCached = $response->headers->get('X-Cache-Status') === 'HIT';
            $status = $isCached ? '(cached)' : '(fresh)';

            echo "{$name}: ".round($responseTime, 2)."ms {$status}\n";

            // Assert response time targets
            if ($isCached) {
                expect($responseTime)->toBeLessThan(
                    1000,
                    "Cached API response for {$name} should be < 1s, got ".round($responseTime, 2).'ms'
                );
            } else {
                expect($responseTime)->toBeLessThan(
                    3000,
                    "Fresh API response for {$name} should be < 3s, got ".round($responseTime, 2).'ms'
                );
            }
        }

        echo "========================\n";
    })->group('performance');

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

        echo "\n=== Database Performance ===\n";
        echo "Total queries: {$queryCount}\n";
        echo 'Total query time: '.round($queryTime, 2)."ms\n";
        echo 'Page load time: '.round($totalTime, 2)."ms\n";
        echo 'Query overhead: '.round(($queryTime / $totalTime) * 100, 1)."%\n";
        echo "===========================\n";

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

        echo "\n=== Memory Usage ===\n";
        echo 'Memory used: '.round($memoryUsed, 2)." MB\n";
        echo 'Peak memory: '.round(memory_get_peak_usage(true) / 1024 / 1024, 2)." MB\n";
        echo "===================\n";

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
        $imgCount = substr_count($content, '<img');

        echo "\n=== Asset Count ===\n";
        echo "JavaScript files: {$jsCount}\n";
        echo "CSS files: {$cssCount}\n";
        echo "Images: {$imgCount}\n";
        echo "==================\n";

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

        echo "\n=== Concurrent API Performance ===\n";
        echo 'Total time for 4 endpoints: '.round($totalTime, 2)."ms\n";
        echo 'Average per endpoint: '.round($totalTime / 4, 2)."ms\n";
        echo "Target: < 3000ms total\n";
        echo 'Status: '.($totalTime < 3000 ? '✓ PASS' : '✗ FAIL')."\n";
        echo "=================================\n";

        // Assert total time is reasonable
        expect($totalTime)->toBeLessThan(
            3000,
            'Concurrent API requests should complete in < 3s, got '.round($totalTime, 2).'ms'
        );
    })->group('performance');
});
