<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\ExternalAPI\APIAlertingService;
use App\Services\ExternalAPI\APIHealthMonitorService;
use App\Services\ExternalAPI\BackgroundSyncService;
use App\Services\ExternalAPI\GracefulDegradationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Fallback and Recovery API Controller
 *
 * Provides API endpoints for managing intelligent fallback and recovery system.
 *
 * Requirements: 14.2, 55.3, 56.3, Task 4.4.3
 */
class FallbackRecoveryController extends Controller
{
    public function __construct(
        protected APIHealthMonitorService $healthMonitor,
        protected GracefulDegradationService $degradationService,
        protected BackgroundSyncService $syncService,
        protected APIAlertingService $alertingService
    ) {}

    /**
     * Get comprehensive health status
     */
    public function healthStatus(): JsonResponse
    {
        $health = $this->healthMonitor->checkAllAPIs();

        return response()->json([
            'success' => true,
            'data' => $health,
        ]);
    }

    /**
     * Get health metrics
     */
    public function healthMetrics(): JsonResponse
    {
        $metrics = $this->healthMonitor->getHealthMetrics();

        return response()->json([
            'success' => true,
            'data' => $metrics,
        ]);
    }

    /**
     * Reset circuit breaker
     */
    public function resetCircuitBreaker(Request $request): JsonResponse
    {
        $apiName = $request->input('api');

        if (! is_string($apiName) || $apiName === '') {
            return response()->json([
                'success' => false,
                'message' => 'API name is required',
            ], 400);
        }

        $this->healthMonitor->resetCircuitBreaker($apiName);

        return response()->json([
            'success' => true,
            'message' => sprintf('Circuit breaker reset for %s', $apiName),
        ]);
    }

    /**
     * Reset all circuit breakers
     */
    public function resetAllCircuitBreakers(): JsonResponse
    {
        $this->healthMonitor->resetAllCircuitBreakers();

        return response()->json([
            'success' => true,
            'message' => 'All circuit breakers reset',
        ]);
    }

    /**
     * Get degradation status
     */
    public function degradationStatus(): JsonResponse
    {
        $status = $this->degradationService->getDegradationStatus();

        return response()->json([
            'success' => true,
            'data' => $status,
        ]);
    }

    /**
     * Get degradation metrics
     */
    public function degradationMetrics(): JsonResponse
    {
        $metrics = $this->degradationService->getDegradationMetrics();

        return response()->json([
            'success' => true,
            'data' => $metrics,
        ]);
    }

    /**
     * Enable manual input mode
     */
    public function enableManualInput(Request $request): JsonResponse
    {
        $apiName = $request->input('api');

        if (! is_string($apiName) || $apiName === '') {
            return response()->json([
                'success' => false,
                'message' => 'API name is required',
            ], 400);
        }

        $this->degradationService->enableManualInput($apiName);

        return response()->json([
            'success' => true,
            'message' => sprintf('Manual input enabled for %s', $apiName),
        ]);
    }

    /**
     * Disable manual input mode
     */
    public function disableManualInput(Request $request): JsonResponse
    {
        $apiName = $request->input('api');

        if (! is_string($apiName) || $apiName === '') {
            return response()->json([
                'success' => false,
                'message' => 'API name is required',
            ], 400);
        }

        $this->degradationService->disableManualInput($apiName);

        return response()->json([
            'success' => true,
            'message' => sprintf('Manual input disabled for %s', $apiName),
        ]);
    }

    /**
     * Attempt recovery
     */
    public function attemptRecovery(Request $request): JsonResponse
    {
        $apiName = $request->input('api');

        if (is_string($apiName) && $apiName !== '') {
            $result = $this->degradationService->attemptRecovery($apiName);
        } else {
            $result = $this->degradationService->attemptAllRecovery();
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Get sync status
     */
    public function syncStatus(): JsonResponse
    {
        $status = $this->syncService->getAllSyncStatus();

        return response()->json([
            'success' => true,
            'data' => $status,
        ]);
    }

    /**
     * Queue sync
     */
    public function queueSync(Request $request): JsonResponse
    {
        $dataType = $request->input('data_type');

        if (! is_string($dataType) || $dataType === '') {
            return response()->json([
                'success' => false,
                'message' => 'Data type is required',
            ], 400);
        }

        $options = $request->input('options', []);
        if (! is_array($options)) {
            $options = [];
        }

        $this->syncService->queueSync($dataType, $options);

        return response()->json([
            'success' => true,
            'message' => sprintf('Sync queued for %s', $dataType),
        ]);
    }

    /**
     * Process sync queue
     */
    public function processSyncQueue(Request $request): JsonResponse
    {
        $dataType = $request->input('data_type');

        if (is_string($dataType) && $dataType !== '') {
            $result = $this->syncService->processSyncQueue($dataType);
        } else {
            $result = $this->syncService->processAllQueues();
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Get sync history
     */
    public function syncHistory(Request $request): JsonResponse
    {
        $dataType = $request->input('data_type');
        $limit = $request->input('limit', 10);

        if (! is_string($dataType) || $dataType === '') {
            return response()->json([
                'success' => false,
                'message' => 'Data type is required',
            ], 400);
        }

        $limitValue = is_numeric($limit) ? (int) $limit : 10;
        $history = $this->syncService->getSyncHistory($dataType, $limitValue);

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }

    /**
     * Reconcile data
     */
    public function reconcileData(Request $request): JsonResponse
    {
        $dataType = $request->input('data_type');

        if (! $dataType) {
            return response()->json([
                'success' => false,
                'message' => 'Data type is required',
            ], 400);
        }

        $result = $this->syncService->reconcileData($dataType);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Get alert history
     */
    public function alertHistory(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 50);
        $type = $request->input('type');

        $history = $this->alertingService->getAlertHistory($limit, $type);

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }

    /**
     * Get unacknowledged alerts
     */
    public function unacknowledgedAlerts(): JsonResponse
    {
        $alerts = $this->alertingService->getUnacknowledgedAlerts();

        return response()->json([
            'success' => true,
            'data' => $alerts,
        ]);
    }

    /**
     * Acknowledge alert
     */
    public function acknowledgeAlert(Request $request): JsonResponse
    {
        $alertId = $request->input('alert_id');

        if (! $alertId) {
            return response()->json([
                'success' => false,
                'message' => 'Alert ID is required',
            ], 400);
        }

        $acknowledged = $this->alertingService->acknowledgeAlert($alertId);

        return response()->json([
            'success' => $acknowledged,
            'message' => $acknowledged
                ? 'Alert acknowledged'
                : 'Alert not found',
        ]);
    }

    /**
     * Get alert statistics
     */
    public function alertStatistics(): JsonResponse
    {
        $stats = $this->alertingService->getAlertStatistics();

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get comprehensive system status
     */
    public function systemStatus(): JsonResponse
    {
        $health = $this->healthMonitor->checkAllAPIs();
        $degradation = $this->degradationService->getDegradationStatus();
        $syncStatus = $this->syncService->getAllSyncStatus();
        $alertStats = $this->alertingService->getAlertStatistics();

        return response()->json([
            'success' => true,
            'data' => [
                'health' => $health,
                'degradation' => $degradation,
                'sync_status' => $syncStatus,
                'alert_statistics' => $alertStats,
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }
}
