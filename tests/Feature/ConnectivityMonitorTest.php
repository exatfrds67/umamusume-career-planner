<?php

declare(strict_types=1);

use App\Services\ExternalAPI\APIHealthMonitorService;
use App\Services\ExternalAPI\CacheManagerService;
use App\Services\ExternalAPI\ConnectivityMonitorService;
use Illuminate\Support\Facades\Cache;

/**
 * Connectivity Monitor Service Tests
 *
 * Tests offline detection, connectivity monitoring, and status tracking.
 *
 * Requirements: 14.2 (Intelligent Caching and Offline Functionality)
 * Task: 2.2.1
 */
beforeEach(function () {
    // Clear cache before each test
    Cache::flush();
});

it('detects online status when APIs are healthy', function () {
    // Mock health monitor to return healthy status
    $healthMonitor = Mockery::mock(APIHealthMonitorService::class);
    $healthMonitor->shouldReceive('checkAllAPIs')
        ->once()
        ->andReturn([
            'umapyoi' => [
                'status' => 'healthy',
                'available' => true,
                'response_time_ms' => 150.0,
            ],
            'umamusumedb' => [
                'status' => 'healthy',
                'available' => true,
                'response_time_ms' => 200.0,
            ],
            'overall_status' => 'healthy',
        ]);

    $cacheManager = app(CacheManagerService::class);

    $service = new ConnectivityMonitorService($healthMonitor, $cacheManager);

    $status = $service->checkConnectivity();

    expect($status['is_online'])->toBeTrue()
        ->and($status['consecutive_failures'])->toBe(0)
        ->and($status['offline_since'])->toBeNull();
});

it('detects offline status when all APIs fail', function () {
    // Mock health monitor to return unhealthy status
    $healthMonitor = Mockery::mock(APIHealthMonitorService::class);
    $healthMonitor->shouldReceive('checkAllAPIs')
        ->once()
        ->andReturn([
            'umapyoi' => [
                'status' => 'error',
                'available' => false,
            ],
            'umamusumedb' => [
                'status' => 'error',
                'available' => false,
            ],
            'overall_status' => 'unhealthy',
        ]);

    $cacheManager = app(CacheManagerService::class);

    $service = new ConnectivityMonitorService($healthMonitor, $cacheManager);

    $status = $service->checkConnectivity();

    expect($status['is_online'])->toBeFalse()
        ->and($status['consecutive_failures'])->toBeGreaterThan(0);
});

it('enters offline mode after threshold failures', function () {
    $healthMonitor = Mockery::mock(APIHealthMonitorService::class);
    $healthMonitor->shouldReceive('checkAllAPIs')
        ->times(3)
        ->andReturn([
            'umapyoi' => ['status' => 'error', 'available' => false],
            'umamusumedb' => ['status' => 'error', 'available' => false],
            'overall_status' => 'unhealthy',
        ]);

    $cacheManager = app(CacheManagerService::class);
    $service = new ConnectivityMonitorService($healthMonitor, $cacheManager);

    // First failure
    $status1 = $service->checkConnectivity();
    expect($status1['consecutive_failures'])->toBe(1)
        ->and($status1['offline_since'])->toBeNull();

    // Second failure
    $status2 = $service->checkConnectivity();
    expect($status2['consecutive_failures'])->toBe(2)
        ->and($status2['offline_since'])->toBeNull();

    // Third failure - should enter offline mode
    $status3 = $service->checkConnectivity();
    expect($status3['consecutive_failures'])->toBe(3)
        ->and($status3['offline_since'])->not->toBeNull();
});

it('resets failure count on successful connection', function () {
    $healthMonitor = Mockery::mock(APIHealthMonitorService::class);

    // First call - failure
    $healthMonitor->shouldReceive('checkAllAPIs')
        ->once()
        ->andReturn([
            'umapyoi' => ['status' => 'error', 'available' => false],
            'umamusumedb' => ['status' => 'error', 'available' => false],
            'overall_status' => 'unhealthy',
        ]);

    $cacheManager = app(CacheManagerService::class);
    $service = new ConnectivityMonitorService($healthMonitor, $cacheManager);

    $status1 = $service->checkConnectivity();
    expect($status1['consecutive_failures'])->toBe(1);

    // Second call - success
    $healthMonitor->shouldReceive('checkAllAPIs')
        ->once()
        ->andReturn([
            'umapyoi' => ['status' => 'healthy', 'available' => true],
            'umamusumedb' => ['status' => 'healthy', 'available' => true],
            'overall_status' => 'healthy',
        ]);

    $status2 = $service->checkConnectivity();
    expect($status2['consecutive_failures'])->toBe(0)
        ->and($status2['is_online'])->toBeTrue();
});

it('caches connectivity status', function () {
    $healthMonitor = Mockery::mock(APIHealthMonitorService::class);
    $healthMonitor->shouldReceive('checkAllAPIs')
        ->once()
        ->andReturn([
            'umapyoi' => ['status' => 'healthy', 'available' => true],
            'umamusumedb' => ['status' => 'healthy', 'available' => true],
            'overall_status' => 'healthy',
        ]);

    $cacheManager = app(CacheManagerService::class);
    $service = new ConnectivityMonitorService($healthMonitor, $cacheManager);

    // First call - should check APIs
    $status1 = $service->checkConnectivity();

    // Second call - should use cache
    $status2 = $service->getStatus();

    expect($status2['cached'])->toBeTrue()
        ->and($status2['is_online'])->toBe($status1['is_online']);
});

it('provides offline mode information', function () {
    $healthMonitor = Mockery::mock(APIHealthMonitorService::class);
    $healthMonitor->shouldReceive('checkAllAPIs')
        ->andReturn([
            'umapyoi' => ['status' => 'error', 'available' => false],
            'umamusumedb' => ['status' => 'error', 'available' => false],
            'overall_status' => 'unhealthy',
        ]);

    $cacheManager = app(CacheManagerService::class);
    $service = new ConnectivityMonitorService($healthMonitor, $cacheManager);

    // Trigger offline mode
    for ($i = 0; $i < 3; $i++) {
        $service->checkConnectivity();
    }

    $offlineInfo = $service->getOfflineModeInfo();

    expect($offlineInfo['is_offline'])->toBeTrue()
        ->and($offlineInfo['offline_since'])->not->toBeNull()
        ->and($offlineInfo['duration_seconds'])->not->toBeNull()
        ->and($offlineInfo)->toHaveKey('cached_data_available')
        ->and($offlineInfo)->toHaveKey('cache_statistics');
});

it('generates connectivity recommendations', function () {
    $healthMonitor = Mockery::mock(APIHealthMonitorService::class);
    $healthMonitor->shouldReceive('checkAllAPIs')
        ->andReturn([
            'umapyoi' => ['status' => 'error', 'available' => false],
            'umamusumedb' => ['status' => 'error', 'available' => false],
            'overall_status' => 'unhealthy',
        ]);

    $cacheManager = app(CacheManagerService::class);
    $service = new ConnectivityMonitorService($healthMonitor, $cacheManager);

    // Trigger offline mode
    for ($i = 0; $i < 3; $i++) {
        $service->checkConnectivity();
    }

    $recommendations = $service->getRecommendations();

    expect($recommendations)->toBeArray()
        ->and($recommendations)->not->toBeEmpty()
        ->and($recommendations[0])->toContain('offline');
});

it('can manually set offline mode', function () {
    $healthMonitor = Mockery::mock(APIHealthMonitorService::class);
    $healthMonitor->shouldReceive('checkAllAPIs')
        ->andReturn([
            'umapyoi' => ['status' => 'error', 'available' => false],
            'umamusumedb' => ['status' => 'error', 'available' => false],
            'overall_status' => 'unhealthy',
        ]);

    $cacheManager = app(CacheManagerService::class);
    $service = new ConnectivityMonitorService($healthMonitor, $cacheManager);

    // Manually set offline mode
    $service->setOfflineMode(true);

    $offlineInfo = $service->getOfflineModeInfo();

    expect($offlineInfo['is_offline'])->toBeTrue()
        ->and($offlineInfo['offline_since'])->not->toBeNull();

    // Manually disable offline mode
    $service->setOfflineMode(false);

    // Check that offline_since is cleared from cache
    $offlineSince = Cache::get('connectivity:offline_mode');
    expect($offlineSince)->toBeNull();
});

it('provides comprehensive connectivity report', function () {
    $healthMonitor = Mockery::mock(APIHealthMonitorService::class);
    $healthMonitor->shouldReceive('checkAllAPIs')
        ->andReturn([
            'umapyoi' => ['status' => 'healthy', 'available' => true],
            'umamusumedb' => ['status' => 'healthy', 'available' => true],
            'overall_status' => 'healthy',
        ]);

    $cacheManager = app(CacheManagerService::class);
    $service = new ConnectivityMonitorService($healthMonitor, $cacheManager);

    $report = $service->getConnectivityReport();

    expect($report)->toHaveKeys(['status', 'offline_info', 'recommendations', 'cache_info'])
        ->and($report['status'])->toBeArray()
        ->and($report['offline_info'])->toBeArray()
        ->and($report['recommendations'])->toBeArray()
        ->and($report['cache_info'])->toBeArray();
});

it('handles degraded API status as online', function () {
    $healthMonitor = Mockery::mock(APIHealthMonitorService::class);
    $healthMonitor->shouldReceive('checkAllAPIs')
        ->once()
        ->andReturn([
            'umapyoi' => [
                'status' => 'degraded',
                'available' => true,
                'response_time_ms' => 2500.0,
            ],
            'umamusumedb' => [
                'status' => 'healthy',
                'available' => true,
                'response_time_ms' => 200.0,
            ],
            'overall_status' => 'degraded',
        ]);

    $cacheManager = app(CacheManagerService::class);
    $service = new ConnectivityMonitorService($healthMonitor, $cacheManager);

    $status = $service->checkConnectivity();

    expect($status['is_online'])->toBeTrue()
        ->and($status['consecutive_failures'])->toBe(0);
});
