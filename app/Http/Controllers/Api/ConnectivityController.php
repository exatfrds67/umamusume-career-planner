<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ExternalAPI\ConnectivityMonitorService;
use Illuminate\Http\JsonResponse;

/**
 * Connectivity API Controller
 *
 * Provides API endpoints for connectivity monitoring and offline mode detection.
 *
 * Requirements: 14.2 (Intelligent Caching and Offline Functionality)
 * Task: 2.2.1
 */
class ConnectivityController extends Controller
{
    public function __construct(
        protected ConnectivityMonitorService $connectivityMonitor
    ) {}

    /**
     * Get current connectivity status
     */
    public function status(): JsonResponse
    {
        $status = $this->connectivityMonitor->getStatus();

        return response()->json($status);
    }

    /**
     * Force connectivity check (bypass cache)
     */
    public function check(): JsonResponse
    {
        $status = $this->connectivityMonitor->forceCheck();

        return response()->json($status);
    }

    /**
     * Get offline mode information
     */
    public function offlineInfo(): JsonResponse
    {
        $info = $this->connectivityMonitor->getOfflineModeInfo();

        return response()->json($info);
    }

    /**
     * Get connectivity recommendations
     */
    public function recommendations(): JsonResponse
    {
        $recommendations = $this->connectivityMonitor->getRecommendations();

        return response()->json([
            'recommendations' => $recommendations,
        ]);
    }

    /**
     * Get comprehensive connectivity report
     */
    public function report(): JsonResponse
    {
        $report = $this->connectivityMonitor->getConnectivityReport();

        return response()->json($report);
    }
}
