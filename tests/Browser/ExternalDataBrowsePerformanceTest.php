<?php

use function Pest\Laravel\visit;

describe('External Data Browse Performance', function () {
    it('loads within 2 seconds target', function () {
        $startTime = microtime(true);

        $page = visit('/external-data/browse');

        // Wait for the page to be fully loaded
        $page->waitFor('[x-data]', timeout: 5000);

        $endTime = microtime(true);
        $loadTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        // Assert page loads within 2 seconds (2000ms)
        expect($loadTime)->toBeLessThan(2000, "Page load time was {$loadTime}ms, expected < 2000ms");

        // Verify no JavaScript errors
        $page->assertNoJavascriptErrors();

        // Log the actual load time for reference
        echo "\nPage load time: ".round($loadTime, 2)."ms\n";
    })->group('performance', 'browser');

    it('measures Core Web Vitals', function () {
        $page = visit('/external-data/browse');

        // Wait for page to be fully loaded
        $page->waitFor('[x-data]', timeout: 5000);

        // Measure Core Web Vitals using JavaScript
        $vitalsScript = <<<'JS'
            (async function() {
                const vitals = {
                    fcp: null,
                    lcp: null,
                    ttfb: null
                };
                
                // Get navigation timing for TTFB
                const navTiming = performance.getEntriesByType('navigation')[0];
                if (navTiming) {
                    vitals.ttfb = navTiming.responseStart - navTiming.requestStart;
                }
                
                // Get paint timing for FCP
                const paintEntries = performance.getEntriesByType('paint');
                const fcpEntry = paintEntries.find(entry => entry.name === 'first-contentful-paint');
                if (fcpEntry) {
                    vitals.fcp = fcpEntry.startTime;
                }
                
                // Get LCP from performance entries
                const lcpEntries = performance.getEntriesByType('largest-contentful-paint');
                if (lcpEntries.length > 0) {
                    const lastEntry = lcpEntries[lcpEntries.length - 1];
                    vitals.lcp = lastEntry.renderTime || lastEntry.loadTime;
                }
                
                return vitals;
            })()
        JS;

        $vitals = $page->evaluate($vitalsScript);

        // Log the Core Web Vitals
        echo "\n=== Core Web Vitals ===\n";
        echo 'TTFB (Time to First Byte): '.($vitals['ttfb'] ? round($vitals['ttfb'], 2).'ms' : 'N/A')."\n";
        echo 'FCP (First Contentful Paint): '.($vitals['fcp'] ? round($vitals['fcp'], 2).'ms' : 'N/A')."\n";
        echo 'LCP (Largest Contentful Paint): '.($vitals['lcp'] ? round($vitals['lcp'], 2).'ms' : 'N/A')."\n";
        echo "======================\n";

        // Assert Core Web Vitals meet targets
        if ($vitals['ttfb']) {
            expect($vitals['ttfb'])->toBeLessThan(800, 'TTFB should be < 800ms');
        }

        if ($vitals['fcp']) {
            expect($vitals['fcp'])->toBeLessThan(1800, 'FCP should be < 1.8s (1800ms)');
        }

        if ($vitals['lcp']) {
            expect($vitals['lcp'])->toBeLessThan(2500, 'LCP should be < 2.5s (2500ms)');
        }

        // Verify no JavaScript errors
        $page->assertNoJavascriptErrors();
    })->group('performance', 'browser');

    it('measures API response times', function () {
        $page = visit('/external-data/browse');

        // Wait for Alpine.js to initialize
        $page->waitFor('[x-data]', timeout: 5000);

        // Measure API response times using Performance API
        $apiTimesScript = <<<'JS'
            (function() {
                const resources = performance.getEntriesByType('resource');
                const apiCalls = resources.filter(r => 
                    r.name.includes('/api/external/') && 
                    r.initiatorType === 'fetch'
                );
                
                return apiCalls.map(call => ({
                    url: call.name.split('/').pop(),
                    duration: call.duration,
                    cached: call.transferSize === 0
                }));
            })()
        JS;

        $apiTimes = $page->evaluate($apiTimesScript);

        echo "\n=== API Response Times ===\n";
        foreach ($apiTimes as $api) {
            $status = $api['cached'] ? '(cached)' : '(fresh)';
            echo "{$api['url']}: ".round($api['duration'], 2)."ms {$status}\n";

            // Assert response time targets
            if ($api['cached']) {
                expect($api['duration'])->toBeLessThan(
                    1000,
                    "Cached API response for {$api['url']} should be < 1s"
                );
            } else {
                expect($api['duration'])->toBeLessThan(
                    3000,
                    "Fresh API response for {$api['url']} should be < 3s"
                );
            }
        }
        echo "========================\n";

        // Verify no JavaScript errors
        $page->assertNoJavascriptErrors();
    })->group('performance', 'browser');

    it('measures time to interactive', function () {
        $startTime = microtime(true);

        $page = visit('/external-data/browse');

        // Wait for Alpine.js to be fully initialized and interactive
        $page->waitFor('[x-data]', timeout: 5000);

        // Wait for the first tab to be clickable (indicating interactivity)
        $page->waitFor('button[role="tab"]', timeout: 5000);

        $endTime = microtime(true);
        $tti = ($endTime - $startTime) * 1000;

        echo "\nTime to Interactive (TTI): ".round($tti, 2)."ms\n";

        // TTI should be reasonable (< 3 seconds)
        expect($tti)->toBeLessThan(3000, 'Time to Interactive should be < 3s');

        // Verify the page is actually interactive by clicking a tab
        $page->click('button[role="tab"]:nth-child(2)');

        // Verify no JavaScript errors
        $page->assertNoJavascriptErrors();
    })->group('performance', 'browser');
});
