<?php

declare(strict_types=1);

use App\Services\CacheManagementService;
use App\Services\ExternalAPI\ResponseTransformer;
use App\Services\ExternalAPI\ResponseValidator;
use App\Services\ExternalAPI\UmapyoiApiClient;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Live Integration Tests for Umapyoi.net API
 *
 * These tests actually call the live umapyoi.net API to verify connectivity
 * and response formats. They are designed to be run manually or in a separate
 * CI environment to avoid hitting the API during regular test runs.
 *
 * To run only these tests:
 * php artisan test --filter UmapyoiLiveApiTest
 *
 * IMPORTANT: These tests may fail if:
 * - The umapyoi.net API is down or unavailable
 * - The API rate limit is reached
 * - The API endpoint structure has changed
 * - Network connectivity issues occur
 */
beforeEach(function () {
    Cache::flush();

    // Note: These tests call the live API - they may fail if the API is down
    // Set SKIP_LIVE_API_TESTS=true to skip them in CI
    if (env('SKIP_LIVE_API_TESTS') === 'true' || env('SKIP_LIVE_API_TESTS') === true) {
        $this->markTestSkipped('Live API tests are disabled. Set SKIP_LIVE_API_TESTS=false to enable.');
    }

    /** @var MCPClientService&Mockery\MockInterface $mcpClient */
    $mcpClient = Mockery::mock(MCPClientService::class);
    $mcpClient->shouldReceive('isServerEnabled')->withArgs(['fetch'])->andReturn(false);
    $mcpClient->shouldReceive('isServerHealthy')->andReturn(true);

    /** @var CacheManagementService&Mockery\MockInterface $cacheManager */
    $cacheManager = Mockery::mock(CacheManagementService::class);
    $cacheManager->shouldReceive('remember')->andReturnUsing(function ($key, $callback, $ttl) {
        if (Cache::has($key)) {
            return [
                'success' => true,
                'data' => Cache::get($key),
                'source' => 'cache',
            ];
        }

        $result = $callback();

        if ($result['success'] ?? false) {
            Cache::put($key, $result['data'], $ttl);
        }

        return $result;
    });
    $cacheManager->shouldReceive('recordApiResponseTime')->andReturn(null);

    /** @var ResponseValidator&Mockery\MockInterface $validator */
    $validator = Mockery::mock(ResponseValidator::class);
    $validator->shouldReceive('validate')->andReturnUsing(function ($type, $data) {
        $wrapperKeys = [
            'umapyoi_characters' => 'characters',
            'umapyoi_character' => 'character',
            'umapyoi_support_cards' => 'support_cards',
            'umapyoi_support_card' => 'support_card',
            'umapyoi_skills' => 'skills',
            'umapyoi_skill' => 'skill',
            'umapyoi_news' => 'news',
        ];

        $wrapperKey = $wrapperKeys[$type] ?? null;
        $extractedData = $wrapperKey && isset($data[$wrapperKey]) ? $data[$wrapperKey] : $data;

        return [
            'valid' => true,
            'errors' => [],
            'data' => $extractedData,
        ];
    });

    /** @var ResponseTransformer&Mockery\MockInterface $transformer */
    $transformer = Mockery::mock(ResponseTransformer::class);
    $transformer->shouldReceive('transform')->andReturnUsing(function ($type, $data) {
        return $data;
    });

    $this->client = new UmapyoiApiClient($mcpClient, $cacheManager, $validator, $transformer);
});

afterEach(function () {
    Mockery::close();
});

describe('UmapyoiLiveApiTest - Live API Connectivity', function () {
    it('can check if umapyoi.net API is available', function () {
        $available = $this->client->isAvailable();

        // Log the result for debugging
        echo "\nUmapyoi.net API Available: ".($available ? 'YES' : 'NO')."\n";

        // We don't assert here as the API may be down
        expect($available)->toBeIn([true, false]);
    })->group('live', 'external');

    it('attempts to fetch characters from live API', function () {
        $result = $this->client->getCharacters(true);

        echo "\nCharacters API Response:\n";
        echo '  Success: '.($result['success'] ? 'YES' : 'NO')."\n";
        echo "  Source: {$result['source']}\n";

        if ($result['success']) {
            echo '  Character Count: '.\count($result['data'])."\n";
        } else {
            echo '  Error: '.($result['error'] ?? 'Unknown')."\n";
        }

        // Don't assert - just document the response
        expect($result)->toHaveKeys(['success', 'data', 'source']);
    })->group('live', 'external');

    it('attempts to fetch support cards from live API', function () {
        $result = $this->client->getSupportCards(true);

        echo "\nSupport Cards API Response:\n";
        echo '  Success: '.($result['success'] ? 'YES' : 'NO')."\n";
        echo "  Source: {$result['source']}\n";

        if ($result['success']) {
            echo '  Support Card Count: '.\count($result['data'])."\n";
        } else {
            echo '  Error: '.($result['error'] ?? 'Unknown')."\n";
        }

        expect($result)->toHaveKeys(['success', 'data', 'source']);
    })->group('live', 'external');

    it('attempts to fetch skills from live API', function () {
        $result = $this->client->getSkills(true);

        echo "\nSkills API Response:\n";
        echo '  Success: '.($result['success'] ? 'YES' : 'NO')."\n";
        echo "  Source: {$result['source']}\n";

        if ($result['success']) {
            echo '  Skill Count: '.\count($result['data'])."\n";
        } else {
            echo '  Error: '.($result['error'] ?? 'Unknown')."\n";
        }

        expect($result)->toHaveKeys(['success', 'data', 'source']);
    })->group('live', 'external');

    it('attempts to fetch news from live API', function () {
        $result = $this->client->getNews(10, true);

        echo "\nNews API Response:\n";
        echo '  Success: '.($result['success'] ? 'YES' : 'NO')."\n";
        echo "  Source: {$result['source']}\n";

        if ($result['success']) {
            echo '  News Count: '.\count($result['data'])."\n";
        } else {
            echo '  Error: '.($result['error'] ?? 'Unknown')."\n";
        }

        expect($result)->toHaveKeys(['success', 'data', 'source']);
    })->group('live', 'external');

    it('tests direct HTTP request to umapyoi.net base URL', function () {
        $baseUrl = config('services.umapyoi.url', 'https://api.umapyoi.net');

        echo "\nDirect HTTP Test to: {$baseUrl}\n";

        try {
            $response = Http::timeout(10)->get($baseUrl);

            echo "  Status Code: {$response->status()}\n";
            echo '  Success: '.($response->successful() ? 'YES' : 'NO')."\n";
            echo "  Content Type: {$response->header('Content-Type')}\n";

            $bodyPreview = substr($response->body(), 0, 200);
            echo '  Body Preview: '.$bodyPreview."...\n";

            expect($response->status())->toBeIn([200, 404, 403, 500, 503]);
        } catch (\Exception $e) {
            echo "  Error: {$e->getMessage()}\n";
            expect(true)->toBeTrue(); // Test passes even on error
        }
    })->group('live', 'external');

    it('tests direct HTTP request to umapyoi.net /v1/characters', function () {
        $baseUrl = (string) config('services.umapyoi.url', 'https://api.umapyoi.net');
        $endpoint = '/v1/characters';
        $url = $baseUrl.$endpoint;

        echo "\nDirect HTTP Test to: {$url}\n";

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'User-Agent' => 'UmamusumeCareerPlanner/1.0',
                ])
                ->get($url);

            echo "  Status Code: {$response->status()}\n";
            echo '  Success: '.($response->successful() ? 'YES' : 'NO')."\n";
            echo "  Content Type: {$response->header('Content-Type')}\n";

            if ($response->successful()) {
                $data = $response->json();
                echo '  Response Keys: '.implode(', ', array_keys($data ?? []))."\n";
            } else {
                $bodyPreview = substr($response->body(), 0, 200);
                echo '  Error Body Preview: '.$bodyPreview."...\n";
            }

            expect($response->status())->toBeIn([200, 404, 403, 500, 503]);
        } catch (\Exception $e) {
            echo "  Error: {$e->getMessage()}\n";
            expect(true)->toBeTrue();
        }
    })->group('live', 'external');
});

describe('UmapyoiLiveApiTest - API Configuration Validation', function () {
    it('verifies umapyoi configuration is loaded correctly', function () {
        $url = config('services.umapyoi.url');
        $timeout = config('services.umapyoi.timeout');
        $enabled = config('services.umapyoi.enabled');

        expect($url)->toBe('https://api.umapyoi.net')
            ->and($timeout)->toBe(30)
            ->and($enabled)->toBeTrue();
    })->group('config');

    it('verifies cache configuration', function () {
        $cacheTtl = config('services.umapyoi.cache_ttl');
        $maxRetries = config('services.umapyoi.retry.max_attempts');
        $retryDelay = config('services.umapyoi.retry.delay_ms');

        expect($cacheTtl)->toBe(86400)
            ->and($maxRetries)->toBe(3)
            ->and($retryDelay)->toBe(1000);
    })->group('config');
});
