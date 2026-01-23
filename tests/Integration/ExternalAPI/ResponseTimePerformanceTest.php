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

describe('Response Time Performance', function () {
    describe('Cache Response Times', function () {
        it('retrieves cached data within 500ms', function () {
            $key = 'character_data:performance_test';
            $data = [
                'name' => 'Performance Test Character',
                'speed' => 100,
                'stamina' => 90,
                'power' => 85,
                'guts' => 80,
                'wit' => 75,
            ];

            // Pre-populate cache
            $this->cacheManager->put($key, $data);

            // Measure retrieval time
            $startTime = microtime(true);
            $cached = $this->cacheManager->get($key);
            $duration = (microtime(true) - $startTime) * 1000;

            expect($cached)->toBeArray()
                ->and($cached['name'])->toBe('Performance Test Character')
                ->and($duration)->toBeLessThan(500); // Should be under 500ms
        });

        it('retrieves multiple cached items within acceptable time', function () {
            // Pre-populate cache with multiple items
            for ($i = 1; $i <= 10; $i++) {
                $this->cacheManager->put("character_data:char{$i}", [
                    'name' => "Character {$i}",
                    'speed' => 100,
                ]);
            }

            // Measure batch retrieval time
            $startTime = microtime(true);
            $results = [];
            for ($i = 1; $i <= 10; $i++) {
                $results[] = $this->cacheManager->get("character_data:char{$i}");
            }
            $duration = (microtime(true) - $startTime) * 1000;

            expect(count($results))->toBe(10)
                ->and($duration)->toBeLessThan(1000); // 10 items in under 1 second
        });

        it('performs cache write operations efficiently', function () {
            $data = [
                'name' => 'Write Performance Test',
                'speed' => 100,
                'stamina' => 90,
            ];

            $startTime = microtime(true);
            $result = $this->cacheManager->put('character_data:write_test', $data);
            $duration = (microtime(true) - $startTime) * 1000;

            expect($result)->toBeTrue()
                ->and($duration)->toBeLessThan(100); // Write should be very fast
        });

        it('handles cache statistics retrieval efficiently', function () {
            // Generate some cache activity
            for ($i = 1; $i <= 5; $i++) {
                $this->cacheManager->put("test:item{$i}", ['data' => $i]);
                $this->cacheManager->get("test:item{$i}");
            }

            $startTime = microtime(true);
            $stats = $this->cacheManager->getStatistics();
            $duration = (microtime(true) - $startTime) * 1000;

            expect($stats)->toBeArray()
                ->and($duration)->toBeLessThan(100); // Stats retrieval should be fast
        });
    });

    describe('MCP Fetch Response Times', function () {
        it('performs simulated HTTP GET within acceptable time', function () {
            $startTime = microtime(true);
            $response = $this->mcpClient->get('https://api.example.com/test');
            $duration = (microtime(true) - $startTime) * 1000;

            expect($response['success'])->toBeTrue()
                ->and($duration)->toBeLessThan(2000); // Should complete within 2 seconds
        });

        it('performs simulated HTTP POST within acceptable time', function () {
            $startTime = microtime(true);
            $response = $this->mcpClient->post('https://api.example.com/test', [
                'data' => 'test_value',
            ]);
            $duration = (microtime(true) - $startTime) * 1000;

            expect($response['success'])->toBeTrue()
                ->and($duration)->toBeLessThan(2000);
        });

        it('performs JSON fetch and decode efficiently', function () {
            $startTime = microtime(true);
            $response = $this->mcpClient->fetchJson('https://api.example.com/test');
            $duration = (microtime(true) - $startTime) * 1000;

            expect($response['success'])->toBeTrue()
                ->and($response['data'])->toBeArray()
                ->and($duration)->toBeLessThan(2000);
        });
    });

    describe('Cache Warming Performance', function () {
        it('completes high priority warming within acceptable time', function () {
            $startTime = microtime(true);
            $result = $this->cacheManager->warmCache('high');
            $duration = (microtime(true) - $startTime) * 1000;

            expect($result['success'])->toBeTrue()
                ->and($duration)->toBeLessThan(10000); // High priority should complete in under 10 seconds
        });

        it('tracks warming duration accurately', function () {
            $result = $this->cacheManager->warmCache('high');

            expect($result)->toHaveKey('duration_ms')
                ->and($result['duration_ms'])->toBeGreaterThan(0)
                ->and($result['duration_ms'])->toBeFloat();
        });

        it('completes all priority warming within reasonable time', function () {
            $startTime = microtime(true);
            $result = $this->cacheManager->warmCache('all');
            $duration = (microtime(true) - $startTime) * 1000;

            expect($result['success'])->toBeTrue()
                ->and($duration)->toBeLessThan(30000); // All priorities in under 30 seconds
        });
    });

    describe('Cache Metadata Performance', function () {
        it('retrieves cache metadata efficiently', function () {
            $key = 'character_data:metadata_test';
            $this->cacheManager->put($key, ['name' => 'Test']);

            $startTime = microtime(true);
            $metadata = $this->cacheManager->getCacheMetadata($key);
            $duration = (microtime(true) - $startTime) * 1000;

            expect($metadata)->toBeArray()
                ->and($duration)->toBeLessThan(50); // Metadata retrieval should be very fast
        });

        it('calculates staleness indicators efficiently', function () {
            $key = 'character_data:staleness_test';
            $this->cacheManager->put($key, ['name' => 'Test']);

            $startTime = microtime(true);
            $cached = $this->cacheManager->get($key);
            $duration = (microtime(true) - $startTime) * 1000;

            expect($cached)->toHaveKey('_cache')
                ->and($cached['_cache'])->toHaveKey('is_stale')
                ->and($cached['_cache'])->toHaveKey('staleness_percentage')
                ->and($duration)->toBeLessThan(100);
        });
    });

    describe('Batch Operations Performance', function () {
        it('handles batch cache writes efficiently', function () {
            $items = [];
            for ($i = 1; $i <= 50; $i++) {
                $items["item{$i}"] = ['id' => $i, 'name' => "Item {$i}"];
            }

            $startTime = microtime(true);
            foreach ($items as $key => $data) {
                $this->cacheManager->put("batch_test:{$key}", $data);
            }
            $duration = (microtime(true) - $startTime) * 1000;

            expect($duration)->toBeLessThan(2000); // 50 writes in under 2 seconds
        });

        it('handles batch cache reads efficiently', function () {
            // Pre-populate cache
            for ($i = 1; $i <= 50; $i++) {
                $this->cacheManager->put("batch_read:item{$i}", ['id' => $i]);
            }

            $startTime = microtime(true);
            $results = [];
            for ($i = 1; $i <= 50; $i++) {
                $results[] = $this->cacheManager->get("batch_read:item{$i}");
            }
            $duration = (microtime(true) - $startTime) * 1000;

            expect(count($results))->toBe(50)
                ->and($duration)->toBeLessThan(2000); // 50 reads in under 2 seconds
        });
    });
});
