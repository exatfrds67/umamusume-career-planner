<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\BenchmarkingService;
use App\Services\HistoricalTrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Historical Tracking Controller
 *
 * Handles historical tracking and benchmarking functionality including:
 * - Long-term trend analysis across multiple careers
 * - Performance benchmarking against community averages
 * - Success rate tracking with confidence intervals
 * - ML model update recommendations
 *
 * Requirements: 25.4 (Task 5.2.5)
 */
class HistoricalTrackingController extends Controller
{
    public function __construct(
        protected HistoricalTrackingService $historicalService,
        protected BenchmarkingService $benchmarkingService
    ) {}

    /**
     * Display the historical tracking dashboard
     */
    public function index(): View
    {
        $user = Auth::user();

        $longTermTrends = $this->historicalService->analyzeLongTermTrends($user);
        $successRates = $this->historicalService->calculateSuccessRatesWithConfidence($user);
        $benchmarkComparison = $this->benchmarkingService->compareUserPerformance($user);

        return view('historical.index', [
            'longTermTrends' => $longTermTrends,
            'successRates' => $successRates,
            'benchmarkComparison' => $benchmarkComparison,
        ]);
    }

    /**
     * Get long-term trend analysis data
     */
    public function getLongTermTrends(): JsonResponse
    {
        $user = Auth::user();
        $trends = $this->historicalService->analyzeLongTermTrends($user);

        return response()->json([
            'success' => ! isset($trends['error']),
            'data' => $trends,
        ]);
    }

    /**
     * Get success rates with confidence intervals
     */
    public function getSuccessRates(): JsonResponse
    {
        $user = Auth::user();
        $successRates = $this->historicalService->calculateSuccessRatesWithConfidence($user);

        return response()->json([
            'success' => ! isset($successRates['error']),
            'data' => $successRates,
        ]);
    }

    /**
     * Get ML model update recommendations
     */
    public function getMLRecommendations(): JsonResponse
    {
        $user = Auth::user();
        $recommendations = $this->historicalService->generateMLModelUpdateRecommendations($user);

        return response()->json([
            'success' => ! isset($recommendations['error']),
            'data' => $recommendations,
        ]);
    }

    /**
     * Get community benchmarks
     */
    public function getCommunityBenchmarks(): JsonResponse
    {
        $benchmarks = $this->benchmarkingService->getCommunityBenchmarks();

        return response()->json([
            'success' => ! isset($benchmarks['error']),
            'data' => $benchmarks,
        ]);
    }

    /**
     * Get user performance comparison against benchmarks
     */
    public function getUserBenchmarkComparison(): JsonResponse
    {
        $user = Auth::user();
        $comparison = $this->benchmarkingService->compareUserPerformance($user);

        return response()->json([
            'success' => ! isset($comparison['error']),
            'data' => $comparison,
        ]);
    }

    /**
     * Get benchmark trends analysis
     */
    public function getBenchmarkTrends(): JsonResponse
    {
        $trends = $this->benchmarkingService->analyzeBenchmarkTrends();

        return response()->json([
            'success' => $trends['community_trend'] !== 'insufficient_data',
            'data' => $trends,
        ]);
    }

    /**
     * Clear historical tracking cache for the current user
     */
    public function clearCache(): JsonResponse
    {
        $user = Auth::user();

        $this->historicalService->clearCache($user);
        $this->benchmarkingService->clearCache($user);

        return response()->json([
            'success' => true,
            'message' => 'Historical tracking cache cleared successfully.',
        ]);
    }
}
