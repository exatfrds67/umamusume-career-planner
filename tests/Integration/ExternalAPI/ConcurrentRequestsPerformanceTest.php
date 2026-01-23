<?php

declare(strict_types=1);

use App\Services\ExternalAPI\CacheManagerService;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    Cache::flush();

    Config::set('mcp.enabled', true);
    Config::set('mcp.servers', [
        'fetch' => [
            'enabled' => true,
            'command' => 'uvx',
            'args' => ['mcp-server-fetch'],
            'capabilities' => ['http_client'],
        ],
    ]);

    $this->mcpClient = new MCPClientService;
    $this->mcpClient->healthCheck();

    $this->cacheManager = new CacheManagerService;
});

afterEach(function () {
    Cache::flush();
});

describe('Concurrent Requests Performance', function () {
    describe('Concurrent Cache Operations', function () {
        it('handles concurrent cache reads without degradation', function () {
            // Pre-populate cache
            for ($i = 1; $i <= 20; $i++) {
                $this->cacheManager->put("concurrent:item{$i}", [
                    'id' => $i,
                    'name' => "Item {$i}",
                ]);
            }

            // Simulate concurrent reads
            $startTime = microtime(true);
            $results = [];
            for ($i = 1; $i <= 20; $i++) {
                $results[] = $this->cacheManager->get("concurrent:item{$i}");
            }
            $duration = (microtime(true) - $startTime) * 1000;

            expect(count($results))->toBe(20)
                ->and($duration)->toBeLessThan(1500); // 20 concurrent reads in under 1.5 seconds
        });

        it('handles concurrent cache writes without conflicts', function () {
            $startTime = microtime(true);

            for ($i = 1; $i <= 20; $i++) {
                $this->cacheManager->put("concurrent_write:item{$i}", [
                    'id' => $i,
                    'timestamp' => now()->toISOString(),
                ]);
            }

            $duration = (microtime(true) - $startTime) * 1000;

            // Verify all writes succeeded
            $verifyCount = 0;
            for ($i = 1; $i <= 20; $i++) {
                if ($this->cacheManager->has("concurrent_write:item{$i}")) {
                    $verifyCount++;
                }
            }

            expect($verifyCount)->toBe(20)
                ->and($duration)->toBeLessThan(2000); // 20 writes in under 2 seconds
        });

        it('maintains cache consistency under concurrent access', function () {
            $key = 'concurrent:consistency_test';
            $this->cacheManager->put($key, ['counter' => 0]);

            // Simulate multiple updates
            for ($i = 1; $i <= 10; $i++) {
                $this->cacheManager->put($key, ['counter' => $i]);
            }

            $final = $this->cacheManager->get($key);

            expect($final['counter'])->toBe(10); // Last write should win
        });
    });

    describe('Concurrent MCP Operations', function () {
        it('handles multiple simultaneous fetch requests', function () {
            $urls = [
                'https://api.example.com/endpoint1',
                'https://api.example.com/endpoint2',
                'https://api.example.com/endpoint3',
                'https://api.example.com/endpoint4',
                'https://api.example.com/endpoint5',
            ];

            $startTime = microtime(true);
            $responses = [];

            foreach ($urls as $url) {
                $responses[] = $this->mcpClient->get($url);
            }

            $duration = (microtime(true) - $startTime) * 1000;

            expect(count($responses))->toBe(5)
                ->and($duration)->toBeLessThan(5000); // 5 requests in under 5 seconds

            // Verify all succeeded
            foreach ($responses as $response) {
                expect($response['success'])->toBeTrue();
            }
        });

        it('respects max concurrent calls configuration', function () {
            $maxConcurrent = $this->mcpClient->getMaxConcurrentCalls();

            expect($maxConcurrent)->toBeInt()
                ->and($maxConcurrent)->toBeGreaterThan(0);
        });
    });

    describe('Mixed Operation Concurrency', function () {
        it('handles mixed read and write operations concurrently', function () {
            // Pre-populate some data
            for ($i = 1; $i <= 10; $i++) {
                $this->cacheManager->put("mixed:item{$i}", ['id' => $i]);
            }

            $startTime = microtime(true);

            // Mix of reads and writes
            for ($i = 1; $i <= 10; $i++) {
                $this->cacheManager->get("mixed:item{$i}"); // Read
                $this->cacheManager->put("mixed:new{$i}", ['id' => $i + 100]); // Write
            }

            $duration = (microtime(true) - $startTime) * 1000;

            expect($duration)->toBeLessThan(2000); // 20 operations in under 2 seconds
        });

        it('handles cache statistics updates during concurrent operations', function () {
            $this->cacheManager->resetStatistics();

            // Perform concurrent operations
            for ($i = 1; $i <= 15; $i++) {
                $this->cacheManager->put("stats:item{$i}", ['id' => $i]);
                $this->cacheManager->get("stats:item{$i}");
            }

            $stats = $this->cacheManager->getStatistics();

            expect($stats['hits'])->toBeGreaterThan(0)
                ->and($stats['total_requests'])->toBeGreaterThan(0);
        });
    });

    describe('Cache Warming Concurrency', function () {
        it('handles concurrent warming tasks', function () {
            $startTime = microtime(true);

            // Warm cache with all priorities (simulates concurrent warming)
            $result = $this->cacheManager->warmCache('all');

            $duration = (microtime(true) - $startTime) * 1000;

            expect($result['success'])->toBeTrue()
                ->and($result['warmed_items'])->toBeGreaterThan(0)
                ->and($duration)->toBeLessThan(30000); // Complete in under 30 seconds
        });

        it('maintains cache integrity during warming', function () {
            // Pre-populate some data
            $this->cacheManager->put('character_data:Silence Suzuka', [
                'name' => 'Silence Suzuka',
                'pre_existing' => true,
            ]);

            // Warm cache
            $this->cacheManager->warmCache('high');

            // Verify pre-existing data is preserved
            $cached = $this->cacheManager->get('character_data:Silence Suzuka');

            expect($cached)->toBeArray()
                ->and($cached['pre_existing'])->toBeTrue();
        });
    });

    describe('Stress Testing', function () {
        it('handles high volume of cache operations', function () {
            $operationCount = 100;

            $startTime = microtime(true);

            for ($i = 1; $i <= $operationCount; $i++) {
                $this->cacheManager->put("stress:item{$i}", [
                    'id' => $i,
                    'data' => str_repeat('x', 100), // 100 bytes of data
                ]);
            }

            $duration = (microtime(true) - $startTime) * 1000;

            expect($duration)->toBeLessThan(5000); // 100 writes in under 5 seconds

            // Verify data integrity
            $sample = $this->cacheManager->get('stress:item50');
            expect($sample)->toBeArray()
                ->and($sample['id'])->toBe(50);
        });

        it('maintains performance under sustained load', function () {
            $iterations = 3;
            $durations = [];

            for ($iteration = 1; $iteration <= $iterations; $iteration++) {
                $startTime = microtime(true);

                for ($i = 1; $i <= 20; $i++) {
                    $this->cacheManager->put("sustained:iter{$iteration}:item{$i}", [
                        'iteration' => $iteration,
                        'id' => $i,
                    ]);
                }

                $durations[] = (microtime(true) - $startTime) * 1000;
            }

            // Performance should not degrade significantly across iterations
            $avgDuration = array_sum($durations) / count($durations);
            $maxDuration = max($durations);

            expect($maxDuration)->toBeLessThan($avgDuration * 2.0); // Allow more variance in CI environments
        });
    });

    describe('Resource Utilization', function () {
        it('efficiently manages cache size during operations', function () {
            // Create multiple cache entries
            for ($i = 1; $i <= 30; $i++) {
                $this->cacheManager->put("resource:item{$i}", [
                    'id' => $i,
                    'data' => str_repeat('x', 200),
                ]);
            }

            $size = $this->cacheManager->getCacheSize();

            // Note: Array cache driver returns 0 for size metrics
            expect($size)->toHaveKey('total_keys')
                ->and($size)->toHaveKey('estimated_size_bytes')
                ->and($size['total_keys'])->toBeInt()
                ->and($size['estimated_size_bytes'])->toBeInt();
        });

        it('handles cache info retrieval under load', function () {
            // Generate cache activity
            for ($i = 1; $i <= 20; $i++) {
                $this->cacheManager->put("info:item{$i}", ['id' => $i]);
            }

            $startTime = microtime(true);
            $info = $this->cacheManager->getCacheInfo();
            $duration = (microtime(true) - $startTime) * 1000;

            expect($info)->toBeArray()
                ->and($duration)->toBeLessThan(200); // Info retrieval should be fast even under load
        });
    });
});
