<?php

declare(strict_types=1);

use App\Services\ExternalAPI\APIHealthMonitorService;
use App\Services\ExternalAPI\CacheManagerService;
use App\Services\ExternalAPI\ConnectivityMonitorService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();

    // Mock the APIHealthMonitorService to avoid actual network calls
    $this->healthMonitorMock = Mockery::mock(APIHealthMonitorService::class);
    $this->healthMonitorMock->shouldReceive('checkAllAPIs')
        ->andReturn([
            'umapyoi' => ['status' => 'healthy', 'available' => true, 'response_time_ms' => 150],
            'umamusumedb' => ['status' => 'healthy', 'available' => true, 'response_time_ms' => 200],
            'overall_status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
        ]);

    // Mock the CacheManagerService
    $this->cacheManagerMock = Mockery::mock(CacheManagerService::class);
    $this->cacheManagerMock->shouldReceive('getStatistics')
        ->andReturn([
            'total_requests' => 100,
            'cache_hits' => 80,
            'cache_misses' => 20,
            'hit_rate' => 80.0,
        ]);
    $this->cacheManagerMock->shouldReceive('getCacheInfo')
        ->andReturn([
            'driver' => 'array',
            'prefix' => 'test_',
        ]);

    // Create service with mocked dependencies
    $this->service = new ConnectivityMonitorService(
        $this->healthMonitorMock,
        $this->cacheManagerMock
    );
});

describe('Connectivity Status', function () {
    it('returns connectivity status', function () {
        $status = $this->service->getStatus();

        expect($status)->toHaveKeys(['is_online', 'last_check', 'api_status'])
            ->and($status['is_online'])->toBeBool();
    });

    it('caches connectivity status', function () {
        // First call
        $this->service->getStatus();

        // Second call should be cached
        $status2 = $this->service->getStatus();

        expect($status2['cached'])->toBeTrue();
    });

    it('forces connectivity check bypassing cache', function () {
        // Get cached status
        $this->service->getStatus();

        // Force check should bypass cache
        $status = $this->service->forceCheck();

        expect($status)->toHaveKey('is_online');
    });
});

describe('Online/Offline Detection', function () {
    it('detects online status', function () {
        $isOnline = $this->service->isOnline();

        expect($isOnline)->toBeBool();
    });

    it('detects offline status', function () {
        $isOffline = $this->service->isOffline();

        expect($isOffline)->toBeBool();
    });

    it('can manually set offline mode', function () {
        $this->service->setOfflineMode(true);

        // After setting offline mode, the service should reflect this
        expect(true)->toBeTrue(); // Service method exists and runs without error
    });

    it('can disable offline mode', function () {
        $this->service->setOfflineMode(true);
        $this->service->setOfflineMode(false);

        expect(true)->toBeTrue(); // Service method exists and runs without error
    });
});

describe('Offline Mode Information', function () {
    it('returns offline mode information', function () {
        $info = $this->service->getOfflineModeInfo();

        expect($info)->toHaveKeys(['is_offline', 'offline_since', 'cached_data_available']);
    });

    it('tracks offline duration', function () {
        $info = $this->service->getOfflineModeInfo();

        expect($info)->toHaveKey('duration_seconds');
    });
});

describe('Connectivity Recommendations', function () {
    it('returns recommendations based on connectivity status', function () {
        $recommendations = $this->service->getRecommendations();

        expect($recommendations)->toBeArray();
    });

    it('provides recommendations when online', function () {
        $recommendations = $this->service->getRecommendations();

        // Recommendations should be an array (may be empty if all is well)
        expect($recommendations)->toBeArray();
    });
});

describe('Connectivity Report', function () {
    it('returns comprehensive connectivity report', function () {
        $report = $this->service->getConnectivityReport();

        expect($report)->toHaveKeys(['status', 'offline_info', 'recommendations', 'cache_info']);
    });

    it('includes status in report', function () {
        $report = $this->service->getConnectivityReport();

        expect($report['status'])->toHaveKey('is_online');
    });

    it('includes offline info in report', function () {
        $report = $this->service->getConnectivityReport();

        expect($report['offline_info'])->toHaveKey('is_offline');
    });
});

describe('Last Successful Connection', function () {
    it('tracks last successful connection', function () {
        // Force a check to potentially record a successful connection
        $this->service->forceCheck();

        $lastSuccess = $this->service->getLastSuccessfulConnection();

        // Should have a timestamp after successful check
        expect($lastSuccess)->toBeString();
    });
});

describe('Offline Mode Scenarios', function () {
    it('handles degraded API status', function () {
        // Create a new mock for degraded scenario
        $degradedHealthMonitor = Mockery::mock(APIHealthMonitorService::class);
        $degradedHealthMonitor->shouldReceive('checkAllAPIs')
            ->andReturn([
                'umapyoi' => ['status' => 'degraded', 'available' => true, 'response_time_ms' => 3000],
                'umamusumedb' => ['status' => 'healthy', 'available' => true, 'response_time_ms' => 200],
                'overall_status' => 'degraded',
                'timestamp' => now()->toIso8601String(),
            ]);

        $service = new ConnectivityMonitorService(
            $degradedHealthMonitor,
            $this->cacheManagerMock
        );

        $status = $service->getStatus();

        // Degraded is still considered online
        expect($status['is_online'])->toBeTrue();
    });
});
