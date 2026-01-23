<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CareerComparisonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Career Comparison API Controller
 *
 * Provides API endpoints for multi-career comparison functionality including:
 * - Side-by-side career comparison
 * - Pattern identification for successful decision sequences
 * - Success factor analysis
 * - Statistical significance testing
 *
 * Requirements: 15.1, 15.2 (Task 5.2.2)
 */
class CareerComparisonController extends Controller
{
    public function __construct(
        protected CareerComparisonService $comparisonService
    ) {}

    /**
     * Compare multiple careers side-by-side
     */
    public function compare(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'career_ids' => 'required|array|min:2',
            'career_ids.*' => 'required|integer|exists:ucp_careers,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $careerIds = $this->getCareerIds($request);

        $comparison = $this->comparisonService->compareCareers($careerIds);

        return response()->json([
            'success' => true,
            'data' => $comparison,
        ]);
    }

    /**
     * Identify success patterns across careers
     */
    public function patterns(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'career_ids' => 'required|array|min:2',
            'career_ids.*' => 'required|integer|exists:ucp_careers,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $careerIds = $this->getCareerIds($request);

        $patterns = $this->comparisonService->identifySuccessPatterns($careerIds);

        return response()->json([
            'success' => true,
            'data' => $patterns,
        ]);
    }

    /**
     * Analyze success factors across careers
     */
    public function successFactors(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'career_ids' => 'required|array|min:5',
            'career_ids.*' => 'required|integer|exists:ucp_careers,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Minimum 5 careers required for success factor analysis.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $careerIds = $this->getCareerIds($request);

        $factors = $this->comparisonService->analyzeSuccessFactors($careerIds);

        return response()->json([
            'success' => true,
            'data' => $factors,
        ]);
    }

    /**
     * Perform statistical significance testing
     */
    public function statisticalTests(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'career_ids' => 'required|array|min:5',
            'career_ids.*' => 'required|integer|exists:ucp_careers,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Minimum 5 careers required for statistical testing.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $careerIds = $this->getCareerIds($request);

        $tests = $this->comparisonService->performStatisticalTests($careerIds);

        return response()->json([
            'success' => true,
            'data' => $tests,
        ]);
    }

    /**
     * Get comprehensive analysis (all comparison features)
     */
    public function comprehensive(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'career_ids' => 'required|array|min:2',
            'career_ids.*' => 'required|integer|exists:ucp_careers,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $careerIds = $this->getCareerIds($request);

        $analysis = $this->comparisonService->getComprehensiveAnalysis($careerIds);

        return response()->json([
            'success' => true,
            'data' => $analysis,
        ]);
    }

    /**
     * Clear comparison cache for specific careers
     */
    public function clearCache(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'career_ids' => 'required|array|min:1',
            'career_ids.*' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $careerIds = $this->getCareerIds($request);

        $this->comparisonService->clearCache($careerIds);

        return response()->json([
            'success' => true,
            'message' => 'Cache cleared successfully',
        ]);
    }

    /**
     * @return array<int>
     */
    private function getCareerIds(Request $request): array
    {
        $careerIds = $request->input('career_ids', []);
        if (! is_array($careerIds)) {
            return [];
        }

        return array_values(array_map(fn (mixed $id): int => is_numeric($id) ? (int) $id : 0, $careerIds));
    }
}
