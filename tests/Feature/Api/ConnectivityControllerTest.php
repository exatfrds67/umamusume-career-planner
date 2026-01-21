<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;

/**
 * Connectivity API Controller Tests
 *
 * Tests API endpoints for connectivity monitoring and offline detection.
 *
 * Requirements: 14.2 (Intelligent Caching and Offline Functionality)
 * Task: 2.2.1
 */
beforeEach(function () {
    Cache::flush();
});

it('returns connectivity status via API', function () {
    $response = $this->getJson('/api/connectivity/status');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'is_online',
            'api_status',
            'last_check',
            'offline_since',
            'consecutive_failures',
        ]);
});

it('forces connectivity check via API', function () {
    $response = $this->postJson('/api/connectivity/check');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'is_online',
            'api_status',
            'last_check',
            'offline_since',
            'consecutive_failures',
        ]);
});

it('returns offline mode information via API', function () {
    $response = $this->getJson('/api/connectivity/offline-info');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'is_offline',
            'offline_since',
            'duration_seconds',
            'cached_data_available',
            'cache_statistics',
            'last_successful_connection',
        ]);
});

it('returns connectivity recommendations via API', function () {
    $response = $this->getJson('/api/connectivity/recommendations');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'recommendations',
        ])
        ->assertJson([
            'recommendations' => expect()->toBeArray(),
        ]);
});

it('returns comprehensive connectivity report via API', function () {
    $response = $this->getJson('/api/connectivity/report');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'status',
            'offline_info',
            'recommendations',
            'cache_info',
        ]);
});

it('caches connectivity status between requests', function () {
    // First request
    $response1 = $this->getJson('/api/connectivity/status');
    $response1->assertSuccessful();

    $data1 = $response1->json();

    // Second request (should use cache)
    $response2 = $this->getJson('/api/connectivity/status');
    $response2->assertSuccessful();

    $data2 = $response2->json();

    // Last check timestamp should be the same (cached)
    expect($data1['last_check'])->toBe($data2['last_check']);
});

it('bypasses cache when forcing check', function () {
    // First request
    $response1 = $this->getJson('/api/connectivity/status');
    $response1->assertSuccessful();

    $data1 = $response1->json();

    // Wait a moment
    sleep(1);

    // Force check (should bypass cache)
    $response2 = $this->postJson('/api/connectivity/check');
    $response2->assertSuccessful();

    $data2 = $response2->json();

    // Last check timestamp should be different (not cached)
    expect($data1['last_check'])->not->toBe($data2['last_check']);
});
