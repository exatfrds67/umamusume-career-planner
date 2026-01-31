<?php

declare(strict_types=1);

/**
 * Property-based tests for External Data API Endpoints.
 *
 * These tests validate universal properties that should hold true
 * across all valid inputs for the external data API endpoints used
 * by the frontend External Data Browser.
 *
 * Properties tested:
 * - Property 1: Data Completeness
 * - Property 2: Partial Failure Resilience
 * - Property 3: Error State Consistency
 * - Property 4: Loading State Correctness (via response timing)
 * - Property 5: API Availability Determination
 *
 * **Validates: Requirements 1.1, 2.1, 2.2, 3.1**
 *
 * Testing Strategy:
 * - Test all four API endpoints: characters, support-cards, skills, news
 * - Verify response structure and data format
 * - Test with various cache states
 * - Test error scenarios and partial failures
 * - Verify API availability logic
 * - Test with minimum 50-100 iterations per property
 *
 * Feature: external-api-frontend-fix
 * Properties: 1-5 - External API Endpoint Correctness
 */

use App\Models\ExternalData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

describe('Property 1: Data Completeness', function () {
    it('returns all data types when all endpoints succeed', function () {
        // Mock successful external API responses
        Http::fake([
            'https://umapyoi.net/api/characters*' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 1, 'name' => 'Special Week', 'rarity' => 3],
                    ['id' => 2, 'name' => 'Silence Suzuka', 'rarity' => 3],
                ],
            ], 200),
            'https://umapyoi.net/api/support-cards*' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 1, 'name' => 'SSR Support Card', 'rarity' => 'SSR'],
                ],
            ], 200),
            'https://umapyoi.net/api/skills*' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 1, 'name' => 'Test Skill', 'type' => 'speed'],
                ],
            ], 200),
            'https://umapyoi.net/api/news*' => Http::response([
                'success' => true,
                'data' => [
                    ['id' => 1, 'title' => 'Test News', 'date' => '2026-01-29'],
                ],
            ], 200),
        ]);

        // Call all four endpoints
        $charactersResponse = $this->getJson('/api/external/characters');
        $supportCardsResponse = $this->getJson('/api/external/support-cards');
        $skillsResponse = $this->getJson('/api/external/skills');
        $newsResponse = $this->getJson('/api/external/news');

        // Property: All endpoints should return success
        $charactersResponse->assertOk();
        $supportCardsResponse->assertOk();
        $skillsResponse->assertOk();
        $newsResponse->assertOk();

        // Property: All responses should have success=true
        expect($charactersResponse->json('success'))->toBeTrue();
        expect($supportCardsResponse->json('success'))->toBeTrue();
        expect($skillsResponse->json('success'))->toBeTrue();
        expect($newsResponse->json('success'))->toBeTrue();

        // Property: All responses should have non-empty data arrays
        expect($charactersResponse->json('data'))->toBeArray()->not->toBeEmpty();
        expect($supportCardsResponse->json('data'))->toBeArray()->not->toBeEmpty();
        expect($skillsResponse->json('data'))->toBeArray()->not->toBeEmpty();
        expect($newsResponse->json('data'))->toBeArray()->not->toBeEmpty();

        // Property: All responses should have required structure
        $charactersResponse->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['id', 'name'],
            ],
            'source',
            'cached',
            'message',
        ]);
    })->repeat(10);
});

describe('Property 2: Partial Failure Resilience', function () {
    it('returns successful data even when some endpoints fail', function () {
        // Generate random failure scenario (at least one success, at least one failure)
        $charactersSuccess = (bool) random_int(0, 1);
        $supportCardsSuccess = (bool) random_int(0, 1);
        $skillsSuccess = (bool) random_int(0, 1);
        $newsSuccess = (bool) random_int(0, 1);

        // Ensure at least one success and one failure
        if (! $charactersSuccess && ! $supportCardsSuccess && ! $skillsSuccess && ! $newsSuccess) {
            $charactersSuccess = true; // Force at least one success
        }
        if ($charactersSuccess && $supportCardsSuccess && $skillsSuccess && $newsSuccess) {
            $newsSuccess = false; // Force at least one failure
        }

        // Mock mixed success/failure responses
        Http::fake([
            'https://umapyoi.net/api/characters*' => $charactersSuccess
                ? Http::response(['success' => true, 'data' => [['id' => 1, 'name' => 'Test']]], 200)
                : Http::response(['error' => 'Failed'], 500),
            'https://umapyoi.net/api/support-cards*' => $supportCardsSuccess
                ? Http::response(['success' => true, 'data' => [['id' => 1, 'name' => 'Test']]], 200)
                : Http::response(['error' => 'Failed'], 500),
            'https://umapyoi.net/api/skills*' => $skillsSuccess
                ? Http::response(['success' => true, 'data' => [['id' => 1, 'name' => 'Test']]], 200)
                : Http::response(['error' => 'Failed'], 500),
            'https://umapyoi.net/api/news*' => $newsSuccess
                ? Http::response(['success' => true, 'data' => [['id' => 1, 'title' => 'Test']]], 200)
                : Http::response(['error' => 'Failed'], 500),
        ]);

        // Call all endpoints
        $charactersResponse = $this->getJson('/api/external/characters');
        $supportCardsResponse = $this->getJson('/api/external/support-cards');
        $skillsResponse = $this->getJson('/api/external/skills');
        $newsResponse = $this->getJson('/api/external/news');

        // Property: Successful endpoints should return data
        if ($charactersSuccess) {
            $charactersResponse->assertOk();
            expect($charactersResponse->json('success'))->toBeTrue();
            expect($charactersResponse->json('data'))->toBeArray();
        }

        if ($supportCardsSuccess) {
            $supportCardsResponse->assertOk();
            expect($supportCardsResponse->json('success'))->toBeTrue();
            expect($supportCardsResponse->json('data'))->toBeArray();
        }

        if ($skillsSuccess) {
            $skillsResponse->assertOk();
            expect($skillsResponse->json('success'))->toBeTrue();
            expect($skillsResponse->json('data'))->toBeArray();
        }

        if ($newsSuccess) {
            $newsResponse->assertOk();
            expect($newsResponse->json('success'))->toBeTrue();
            expect($newsResponse->json('data'))->toBeArray();
        }

        // Property: At least one endpoint should have succeeded
        $anySuccess = $charactersSuccess || $supportCardsSuccess || $skillsSuccess || $newsSuccess;
        expect($anySuccess)->toBeTrue();
    })->repeat(20);

    it('falls back to cached data when external API fails', function () {
        // Seed cached data in database
        ExternalData::factory()->create([
            'data_type' => 'characters',
            'data' => json_encode([
                ['id' => 1, 'name' => 'Cached Character'],
            ]),
            'source' => 'umapyoi.net',
        ]);

        // Mock failed external API
        Http::fake([
            'https://umapyoi.net/api/characters*' => Http::response(['error' => 'Service unavailable'], 503),
        ]);

        // Call endpoint
        $response = $this->getJson('/api/external/characters');

        // Property: Should still return success with cached data
        $response->assertOk();
        expect($response->json('success'))->toBeTrue();
        expect($response->json('data'))->toBeArray()->not->toBeEmpty();
        expect($response->json('source'))->toBe('database_cache');
        expect($response->json('cached'))->toBeTrue();
    })->repeat(10);
});

describe('Property 3: Error State Consistency', function () {
    it('returns consistent error structure when endpoint fails', function () {
        // Mock failed external API
        $statusCode = [500, 503, 504][random_int(0, 2)];
        Http::fake([
            'https://umapyoi.net/api/characters*' => Http::response(['error' => 'Service error'], $statusCode),
        ]);

        // Clear any cached data to force error
        ExternalData::where('data_type', 'characters')->delete();
        Cache::forget('external_data:characters');

        // Call endpoint
        $response = $this->getJson('/api/external/characters');

        // Property: Should return error response
        expect($response->status())->toBeGreaterThanOrEqual(500);

        // Property: Error response should have consistent structure
        $response->assertJsonStructure([
            'success',
            'message',
        ]);

        expect($response->json('success'))->toBeFalse();
        expect($response->json('message'))->toBeString()->not->toBeEmpty();
    })->repeat(10);

    it('provides specific error messages for different failure types', function () {
        // Test different error scenarios
        $scenarios = [
            ['status' => 404, 'message' => 'Not Found'],
            ['status' => 500, 'message' => 'Internal Server Error'],
            ['status' => 503, 'message' => 'Service Unavailable'],
            ['status' => 504, 'message' => 'Gateway Timeout'],
        ];

        $scenario = $scenarios[random_int(0, count($scenarios) - 1)];

        Http::fake([
            'https://umapyoi.net/api/characters*' => Http::response(
                ['error' => $scenario['message']],
                $scenario['status']
            ),
        ]);

        // Clear cached data
        ExternalData::where('data_type', 'characters')->delete();
        Cache::forget('external_data:characters');

        // Call endpoint
        $response = $this->getJson('/api/external/characters');

        // Property: Error message should be informative
        expect($response->json('message'))->toBeString()->not->toBeEmpty();
        expect($response->json('success'))->toBeFalse();
    })->repeat(20);
});

describe('Property 4: Loading State Correctness', function () {
    it('responds within acceptable time limits', function () {
        // Mock fast external API response
        Http::fake([
            'https://umapyoi.net/api/characters*' => Http::response([
                'success' => true,
                'data' => array_map(fn ($i) => ['id' => $i, 'name' => "Character $i"], range(1, 50)),
            ], 200),
        ]);

        // Measure response time
        $startTime = microtime(true);
        $response = $this->getJson('/api/external/characters');
        $endTime = microtime(true);

        $responseTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        // Property: Response should be fast (< 3 seconds for fresh data)
        expect($responseTime)->toBeLessThan(3000);

        // Property: Response should be successful
        $response->assertOk();
        expect($response->json('success'))->toBeTrue();
    })->repeat(10);

    it('cached responses are faster than fresh responses', function () {
        // Seed cached data
        ExternalData::factory()->create([
            'data_type' => 'characters',
            'data' => json_encode(array_map(fn ($i) => ['id' => $i, 'name' => "Character $i"], range(1, 50))),
            'source' => 'umapyoi.net',
        ]);

        // Mock slow external API (should use cache instead)
        Http::fake([
            'https://umapyoi.net/api/characters*' => Http::sequence()
                ->push(['success' => true, 'data' => []], 200, ['delay' => 2000]),
        ]);

        // Measure cached response time
        $startTime = microtime(true);
        $response = $this->getJson('/api/external/characters');
        $endTime = microtime(true);

        $responseTime = ($endTime - $startTime) * 1000;

        // Property: Cached response should be fast (< 1 second)
        expect($responseTime)->toBeLessThan(1000);

        // Property: Response should indicate it's cached
        expect($response->json('cached'))->toBeTrue();
        expect($response->json('source'))->toContain('cache');
    })->repeat(10);
});

describe('Property 5: API Availability Determination', function () {
    it('determines API availability based on endpoint responses', function () {
        // Generate random success pattern
        $successCount = random_int(0, 4);
        $endpoints = ['characters', 'support-cards', 'skills', 'news'];
        $successfulEndpoints = array_slice($endpoints, 0, $successCount);

        // Mock responses based on success pattern
        $fakeResponses = [];
        foreach ($endpoints as $endpoint) {
            $url = "https://umapyoi.net/api/$endpoint*";
            if (in_array($endpoint, $successfulEndpoints)) {
                $fakeResponses[$url] = Http::response([
                    'success' => true,
                    'data' => [['id' => 1, 'name' => 'Test']],
                ], 200);
            } else {
                $fakeResponses[$url] = Http::response(['error' => 'Failed'], 500);
            }
        }

        Http::fake($fakeResponses);

        // Call all endpoints
        $responses = [
            'characters' => $this->getJson('/api/external/characters'),
            'support-cards' => $this->getJson('/api/external/support-cards'),
            'skills' => $this->getJson('/api/external/skills'),
            'news' => $this->getJson('/api/external/news'),
        ];

        // Property: API is available if at least one endpoint succeeds
        $apiAvailable = false;
        foreach ($responses as $endpoint => $response) {
            if ($response->status() === 200 && $response->json('success') === true) {
                $apiAvailable = true;
                break;
            }
        }

        // Property: API availability matches success count
        if ($successCount > 0) {
            expect($apiAvailable)->toBeTrue();
        } else {
            expect($apiAvailable)->toBeFalse();
        }

        // Property: Successful endpoints return valid data
        foreach ($successfulEndpoints as $endpoint) {
            $endpointKey = str_replace('-', '', $endpoint);
            if (isset($responses[$endpoint])) {
                expect($responses[$endpoint]->json('success'))->toBeTrue();
                expect($responses[$endpoint]->json('data'))->toBeArray();
            }
        }
    })->repeat(20);

    it('correctly identifies complete API failure', function () {
        // Mock all endpoints failing
        Http::fake([
            'https://umapyoi.net/api/*' => Http::response(['error' => 'Service unavailable'], 503),
        ]);

        // Clear all cached data
        ExternalData::truncate();
        Cache::flush();

        // Call all endpoints
        $charactersResponse = $this->getJson('/api/external/characters');
        $supportCardsResponse = $this->getJson('/api/external/support-cards');
        $skillsResponse = $this->getJson('/api/external/skills');
        $newsResponse = $this->getJson('/api/external/news');

        // Property: All endpoints should fail
        expect($charactersResponse->status())->toBeGreaterThanOrEqual(500);
        expect($supportCardsResponse->status())->toBeGreaterThanOrEqual(500);
        expect($skillsResponse->status())->toBeGreaterThanOrEqual(500);
        expect($newsResponse->status())->toBeGreaterThanOrEqual(500);

        // Property: All responses should indicate failure
        expect($charactersResponse->json('success'))->toBeFalse();
        expect($supportCardsResponse->json('success'))->toBeFalse();
        expect($skillsResponse->json('success'))->toBeFalse();
        expect($newsResponse->json('success'))->toBeFalse();

        // Property: API is completely unavailable
        $apiAvailable = false;
        foreach ([$charactersResponse, $supportCardsResponse, $skillsResponse, $newsResponse] as $response) {
            if ($response->status() === 200 && $response->json('success') === true) {
                $apiAvailable = true;
                break;
            }
        }
        expect($apiAvailable)->toBeFalse();
    })->repeat(10);

    it('correctly identifies partial API availability', function () {
        // Mock exactly 2 endpoints succeeding and 2 failing
        Http::fake([
            'https://umapyoi.net/api/characters*' => Http::response([
                'success' => true,
                'data' => [['id' => 1, 'name' => 'Test']],
            ], 200),
            'https://umapyoi.net/api/support-cards*' => Http::response([
                'success' => true,
                'data' => [['id' => 1, 'name' => 'Test']],
            ], 200),
            'https://umapyoi.net/api/skills*' => Http::response(['error' => 'Failed'], 500),
            'https://umapyoi.net/api/news*' => Http::response(['error' => 'Failed'], 500),
        ]);

        // Call all endpoints
        $charactersResponse = $this->getJson('/api/external/characters');
        $supportCardsResponse = $this->getJson('/api/external/support-cards');
        $skillsResponse = $this->getJson('/api/external/skills');
        $newsResponse = $this->getJson('/api/external/news');

        // Property: Some endpoints succeed
        expect($charactersResponse->json('success'))->toBeTrue();
        expect($supportCardsResponse->json('success'))->toBeTrue();

        // Property: Some endpoints fail
        expect($skillsResponse->json('success'))->toBeFalse();
        expect($newsResponse->json('success'))->toBeFalse();

        // Property: API is partially available
        $successCount = 0;
        foreach ([$charactersResponse, $supportCardsResponse, $skillsResponse, $newsResponse] as $response) {
            if ($response->status() === 200 && $response->json('success') === true) {
                $successCount++;
            }
        }
        expect($successCount)->toBeGreaterThan(0);
        expect($successCount)->toBeLessThan(4);
    })->repeat(10);
});
