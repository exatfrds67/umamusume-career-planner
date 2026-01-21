<?php

declare(strict_types=1);

use App\Services\ExternalAPI\APIPerformanceMetricsService;
use App\Services\ExternalAPI\PerformanceOptimizationService;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    // Clear cache before each test
    Cache::flush();

    // Create service instance
    $this->mcpClient = Mockery::mock(MCPClientService::class);
    $this->metricsService = Mockery::mock(APIPerformanceMetricsService::class);
    $this->service = new PerformanceOptimizationService(
        $this->mcpClient,
        $this->metricsService
    );
});

afterEach(function () {
    Mockery::close();
});

describe('Request Batching', function () {
    it('adds requests to batch queue', function () {
        $result = $this->service->addToBatch('/api/characters', 'GET', ['id' => 1]);

        expect($result)->toHaveKeys(['success', 'queued', 'batch_id', 'position'])
            ->and($result['success'])->toBeTrue()
            ->and($result['queued'])->toBeTrue()
            ->and($result['position'])->toBe(1);
    });

    it('groups requests by endpoint and method', function () {
        $result1 = $this->service->addToBatch('/api/characters', 'GET', ['id' => 1]);
        $result2 = $this->service->addToBatch('/api/characters', 'GET', ['id' => 2]);

        expect($result1['batch_id'])->toBe($result2['batch_id'])
            ->and($result2['position'])->toBe(2);
    });

    it('creates separate batches for different endpoints', function () {
        $result1 = $this->service->addToBatch('/api/characters', 'GET');
        $result2 = $this->service->addToBatch('/api/support-cards', 'GET');

        expect($result1['batch_id'])->not->toBe($result2['batch_id']);
    });

    it('executes batch when full', function () {
        $this->metricsService->shouldReceive('recordBatchExecution')
            ->once()
            ->with(Mockery::type('string'), 10, Mockery::type('float'));

        // Add 10 requests (MAX_BATCH_SIZE)
        for ($i = 1; $i <= 10; $i++) {
            $this->service->addToBatch('/api/test', 'GET', ['id' => $i]);
        }

        // Batch should have been auto-executed
        $status = $this->service->getBatchQueueStatus();
        expect($status['total_batches'])->toBe(0);
    });

    it('executes batch manually', function () {
        $this->metricsService->shouldReceive('recordBatchExecution')
            ->once()
            ->with(Mockery::type('string'), 3, Mockery::type('float'));

        // Add 3 requests
        $result1 = $this->service->addToBatch('/api/test', 'GET', ['id' => 1]);
        $this->service->addToBatch('/api/test', 'GET', ['id' => 2]);
        $this->service->addToBatch('/api/test', 'GET', ['id' => 3]);

        $batchResult = $this->service->executeBatch($result1['batch_id']);

        expect($batchResult)->toHaveKeys(['success', 'batch_id', 'executed_count', 'results', 'duration_ms'])
            ->and($batchResult['success'])->toBeTrue()
            ->and($batchResult['executed_count'])->toBe(3)
            ->and($batchResult['results'])->toHaveCount(3);
    });

    it('executes all pending batches', function () {
        $this->metricsService->shouldReceive('recordBatchExecution')
            ->twice();

        // Add requests to two different batches
        $this->service->addToBatch('/api/characters', 'GET', ['id' => 1]);
        $this->service->addToBatch('/api/characters', 'GET', ['id' => 2]);
        $this->service->addToBatch('/api/support-cards', 'GET', ['id' => 1]);

        $result = $this->service->executeAllBatches();

        expect($result)->toHaveKeys(['success', 'executed_batches', 'total_requests', 'duration_ms'])
            ->and($result['success'])->toBeTrue()
            ->and($result['executed_batches'])->toBe(2)
            ->and($result['total_requests'])->toBe(3);
    });

    it('returns batch queue status', function () {
        $this->service->addToBatch('/api/characters', 'GET', ['id' => 1]);
        $this->service->addToBatch('/api/characters', 'GET', ['id' => 2]);
        $this->service->addToBatch('/api/support-cards', 'GET', ['id' => 1]);

        $status = $this->service->getBatchQueueStatus();

        expect($status)->toHaveKeys(['total_batches', 'total_requests', 'batches'])
            ->and($status['total_batches'])->toBe(2)
            ->and($status['total_requests'])->toBe(3);
    });

    it('clears batch queue', function () {
        $this->service->addToBatch('/api/test', 'GET', ['id' => 1]);
        $this->service->addToBatch('/api/test', 'GET', ['id' => 2]);

        $this->service->clearBatchQueue();

        $status = $this->service->getBatchQueueStatus();
        expect($status['total_batches'])->toBe(0)
            ->and($status['total_requests'])->toBe(0);
    });
});

describe('Parallel Fetching', function () {
    it('fetches multiple URLs in parallel', function () {
        $this->mcpClient->shouldReceive('fetch')
            ->times(3)
            ->andReturn([
                'success' => true,
                'status' => 200,
                'body' => json_encode(['data' => 'test']),
            ]);

        $this->metricsService->shouldReceive('recordParallelFetch')
            ->once()
            ->with(3, Mockery::type('float'));

        $requests = [
            ['url' => 'https://api.example.com/1'],
            ['url' => 'https://api.example.com/2'],
            ['url' => 'https://api.example.com/3'],
        ];

        $result = $this->service->fetchParallel($requests);

        expect($result)->toHaveKeys(['success', 'results', 'duration_ms', 'parallel_count'])
            ->and($result['success'])->toBeTrue()
            ->and($result['parallel_count'])->toBe(3)
            ->and($result['results'])->toHaveCount(3);
    });

    it('handles failed requests in parallel fetch', function () {
        $this->mcpClient->shouldReceive('fetch')
            ->twice()
            ->andReturnUsing(function ($url) {
                if (str_contains($url, '/fail')) {
                    throw new \RuntimeException('Request failed');
                }

                return [
                    'success' => true,
                    'status' => 200,
                    'body' => json_encode(['data' => 'test']),
                ];
            });

        $this->metricsService->shouldReceive('recordParallelFetch')
            ->once();

        $requests = [
            ['url' => 'https://api.example.com/success'],
            ['url' => 'https://api.example.com/fail'],
        ];

        $result = $this->service->fetchParallel($requests);

        expect($result['success'])->toBeTrue()
            ->and($result['results'])->toHaveCount(2)
            ->and($result['results'][0]['success'])->toBeTrue()
            ->and($result['results'][1]['success'])->toBeFalse()
            ->and($result['results'][1])->toHaveKey('error');
    });

    it('limits parallel requests to maximum', function () {
        $this->mcpClient->shouldReceive('fetch')
            ->times(10)
            ->andReturn([
                'success' => true,
                'status' => 200,
                'body' => json_encode(['data' => 'test']),
            ]);

        $this->metricsService->shouldReceive('recordParallelFetch')
            ->once();

        // Create 10 requests (will be chunked into groups of 5)
        $requests = array_map(
            fn ($i) => ['url' => "https://api.example.com/{$i}"],
            range(1, 10)
        );

        $result = $this->service->fetchParallel($requests);

        expect($result['parallel_count'])->toBe(10)
            ->and($result['results'])->toHaveCount(10);
    });
});

describe('Connection Pooling', function () {
    it('returns connection pool status', function () {
        $status = $this->service->getConnectionPoolStatus();

        expect($status)->toHaveKeys(['pool_size', 'max_size', 'in_use', 'available', 'connections'])
            ->and($status['pool_size'])->toBe(0)
            ->and($status['max_size'])->toBe(5)
            ->and($status['in_use'])->toBe(0)
            ->and($status['available'])->toBe(0);
    });

    it('reuses connections from pool', function () {
        $this->mcpClient->shouldReceive('fetch')
            ->twice()
            ->andReturn([
                'success' => true,
                'status' => 200,
                'body' => json_encode(['data' => 'test']),
            ]);

        $this->metricsService->shouldReceive('recordParallelFetch')
            ->once();

        // Make two requests to the same host
        $requests = [
            ['url' => 'https://api.example.com/1'],
            ['url' => 'https://api.example.com/2'],
        ];

        $this->service->fetchParallel($requests);

        $status = $this->service->getConnectionPoolStatus();
        expect($status['pool_size'])->toBeGreaterThan(0);
    });

    it('clears connection pool', function () {
        $this->mcpClient->shouldReceive('fetch')
            ->once()
            ->andReturn([
                'success' => true,
                'status' => 200,
                'body' => json_encode(['data' => 'test']),
            ]);

        $this->metricsService->shouldReceive('recordParallelFetch')
            ->once();

        $requests = [['url' => 'https://api.example.com/1']];
        $this->service->fetchParallel($requests);

        $this->service->clearConnectionPool();

        $status = $this->service->getConnectionPoolStatus();
        expect($status['pool_size'])->toBe(0);
    });
});

describe('Response Compression', function () {
    it('compresses large responses', function () {
        $this->metricsService->shouldReceive('recordCompression')
            ->once()
            ->with(Mockery::type('int'), Mockery::type('int'));

        $largeData = [
            'data' => str_repeat('test data ', 200), // Large enough to trigger compression
        ];

        $result = $this->service->compressResponse($largeData);

        expect($result)->toHaveKeys(['compressed', 'original_size', 'compressed_size', 'compression_ratio', 'data'])
            ->and($result['compressed'])->toBeTrue()
            ->and($result['compressed_size'])->toBeLessThan($result['original_size'])
            ->and($result['compression_ratio'])->toBeLessThan(1.0);
    });

    it('skips compression for small responses', function () {
        $smallData = ['id' => 1, 'name' => 'test'];

        $result = $this->service->compressResponse($smallData);

        expect($result['compressed'])->toBeFalse()
            ->and($result['original_size'])->toBe($result['compressed_size'])
            ->and($result['compression_ratio'])->toBe(1.0);
    });

    it('decompresses compressed data', function () {
        $this->metricsService->shouldReceive('recordCompression')
            ->once();

        $originalData = [
            'data' => str_repeat('test data ', 200),
            'nested' => ['key' => 'value'],
        ];

        $compressed = $this->service->compressResponse($originalData);
        $decompressed = $this->service->decompressResponse($compressed['data']);

        expect($decompressed)->toBe($originalData);
    });

    it('throws exception on invalid compressed data', function () {
        expect(fn () => $this->service->decompressResponse('invalid-data'))
            ->toThrow(\RuntimeException::class);
    });
});

describe('Performance Statistics', function () {
    it('returns comprehensive statistics', function () {
        $stats = $this->service->getStatistics();

        expect($stats)->toHaveKeys(['batch_queue', 'connection_pool', 'configuration'])
            ->and($stats['batch_queue'])->toHaveKeys(['total_batches', 'total_requests', 'batches'])
            ->and($stats['connection_pool'])->toHaveKeys(['pool_size', 'max_size', 'in_use', 'available', 'connections'])
            ->and($stats['configuration'])->toHaveKeys(['max_batch_size', 'connection_pool_size', 'compression_threshold', 'max_parallel_requests']);
    });
});
