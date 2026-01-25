<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ExternalAPI\UmapyoiApiClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * External Data Controller
 *
 * Handles fetching and syncing data from external APIs (umapyoi.net, umamusumedb.com)
 * to local database for display in frontend
 */
class ExternalDataController extends Controller
{
    public function __construct(
        protected UmapyoiApiClient $umapyoiClient
    ) {}

    /**
     * Fetch characters from umapyoi.net API
     *
     * GET /api/external/characters
     */
    public function getCharacters(): JsonResponse
    {
        try {
            $result = $this->umapyoiClient->getCharacters();

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => $result['data'],
                    'source' => 'umapyoi.net',
                    'cached' => ($result['source'] ?? null) === 'cache',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Failed to fetch characters',
                'error' => $result['error'] ?? null,
            ], 500);
        } catch (\Exception $e) {
            Log::error('Failed to fetch characters from umapyoi API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fetch support cards from umapyoi.net API
     *
     * GET /api/external/support-cards
     */
    public function getSupportCards(): JsonResponse
    {
        try {
            $result = $this->umapyoiClient->getSupportCards();

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => $result['data'],
                    'source' => 'umapyoi.net',
                    'cached' => ($result['source'] ?? null) === 'cache',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Failed to fetch support cards',
                'error' => $result['error'] ?? null,
            ], 500);
        } catch (\Exception $e) {
            Log::error('Failed to fetch support cards from umapyoi API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fetch skills from local database
     * Note: umapyoi.net API does not provide skills endpoint, using local data
     *
     * GET /api/external/skills
     */
    public function getSkills(): JsonResponse
    {
        try {
            $skills = \App\Models\Skill::where('is_active', true)
                ->select([
                    'id',
                    'name',
                    'internal_id',
                    'skill_type',
                    'rarity',
                    'base_sp_cost',
                    'description',
                    'effects',
                    'activation_conditions',
                    'can_evolve',
                    'is_evolution',
                    'meta_tier',
                ])
                ->orderBy('name')
                ->get()
                ->map(function ($skill) {
                    return [
                        'id' => $skill->id,
                        'name' => $skill->name,
                        'internal_id' => $skill->internal_id,
                        'type' => $skill->skill_type,
                        'rarity' => $skill->rarity,
                        'sp_cost' => $skill->base_sp_cost,
                        'description' => $skill->description,
                        'effects' => $skill->effects,
                        'activation_conditions' => $skill->activation_conditions,
                        'can_evolve' => $skill->can_evolve,
                        'is_evolution' => $skill->is_evolution,
                        'meta_tier' => $skill->meta_tier,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $skills,
                'source' => 'local database',
                'cached' => false,
                'note' => 'umapyoi.net API does not provide skills endpoint',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch skills from local database', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fetch news from umapyoi.net API
     *
     * GET /api/external/news?limit=10
     */
    public function getNews(Request $request): JsonResponse
    {
        try {
            $limit = (int) $request->query('limit', 10);
            $result = $this->umapyoiClient->getNews($limit);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => $result['data'],
                    'source' => 'umapyoi.net',
                    'cached' => ($result['source'] ?? null) === 'cache',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Failed to fetch news',
                'error' => $result['error'] ?? null,
            ], 500);
        } catch (\Exception $e) {
            Log::error('Failed to fetch news from umapyoi API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check API availability
     *
     * GET /api/external/status
     */
    public function getStatus(): JsonResponse
    {
        try {
            $isAvailable = $this->umapyoiClient->isAvailable();
            $cacheStatus = $this->umapyoiClient->getCacheStatus();

            return response()->json([
                'success' => true,
                'data' => [
                    'umapyoi' => [
                        'available' => $isAvailable,
                        'cache_status' => $cacheStatus,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to check API status', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to check API status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear cache for external APIs
     *
     * POST /api/external/clear-cache
     */
    public function clearCache(): JsonResponse
    {
        try {
            $this->umapyoiClient->clearCache();

            return response()->json([
                'success' => true,
                'message' => 'Cache cleared successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to clear cache', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
