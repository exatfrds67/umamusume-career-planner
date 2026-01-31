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
                    'source' => $result['source'] === 'database_cache' ? 'database_cache' : 'umapyoi.net',
                    'cached' => in_array($result['source'], ['cache', 'database_cache']),
                    'offline_mode' => $result['source'] === 'database_cache',
                    'message' => $this->getSourceMessage($result['source']),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? 'Failed to fetch characters',
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
                    'source' => $result['source'] === 'database_cache' ? 'database_cache' : 'umapyoi.net',
                    'cached' => in_array($result['source'], ['cache', 'database_cache']),
                    'offline_mode' => $result['source'] === 'database_cache',
                    'message' => $this->getSourceMessage($result['source']),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? 'Failed to fetch support cards',
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
     *
     * Skills are not available from the external umapyoi.net API,
     * so we serve them from the local database instead.
     *
     * GET /api/external/skills
     */
    public function getSkills(): JsonResponse
    {
        try {
            // Skills are not available from umapyoi.net API, use local database
            $skills = \App\Models\Skill::query()
                ->where('is_active', true)
                ->select([
                    'id',
                    'name',
                    'internal_id',
                    'skill_type',
                    'rarity',
                    'base_sp_cost',
                    'description',
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
                        'name_en' => $skill->name,
                        'internal_id' => $skill->internal_id,
                        'type' => $skill->skill_type,
                        'rarity' => $skill->rarity,
                        'sp_cost' => $skill->base_sp_cost,
                        'description' => $skill->description,
                        'can_evolve' => $skill->can_evolve,
                        'is_evolution' => $skill->is_evolution,
                        'meta_tier' => $skill->meta_tier,
                    ];
                })
                ->toArray();

            return response()->json([
                'success' => true,
                'data' => $skills,
                'source' => 'local database',
                'cached' => false,
                'offline_mode' => false,
                'message' => 'Skills loaded from local database',
                'note' => 'Skills are served from local database as they are not available from external APIs',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch skills from local database', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch skills',
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
            $limit = $request->query('limit', '10');
            $result = $this->umapyoiClient->getNews((int) $limit);

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
     * Check API availability and database cache status
     *
     * GET /api/external/status
     */
    public function getStatus(): JsonResponse
    {
        try {
            $isAvailable = $this->umapyoiClient->isAvailable();
            $cacheStatus = $this->umapyoiClient->getCacheStatus();
            $databaseCacheStatus = $this->getDatabaseCacheStatus();

            return response()->json([
                'success' => true,
                'data' => [
                    'umapyoi' => [
                        'available' => $isAvailable,
                        'cache_status' => $cacheStatus,
                        'database_cache' => $databaseCacheStatus,
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
     * Get database cache status for all data types
     *
     * @return array<string, array<string, mixed>>
     */
    private function getDatabaseCacheStatus(): array
    {
        $types = ['characters', 'support_cards', 'news'];
        $status = [];

        foreach ($types as $type) {
            $data = \App\Models\ExternalData::where('data_source', 'umapyoi')
                ->where('data_type', $type)
                ->where('data_key', 'api_cache')
                ->valid()
                ->first();

            $status[$type] = [
                'cached' => $data !== null,
                'last_fetched' => $data?->last_fetched_at?->toISOString(),
                'records_count' => $data ? count($data->data_content) : 0,
                'expires_at' => $data?->expires_at?->toISOString(),
                'is_valid' => $data?->isValid() ?? false,
            ];
        }

        return $status;
    }

    /**
     * Get user-friendly message based on data source
     */
    private function getSourceMessage(string $source): string
    {
        return match ($source) {
            'api' => 'Fresh data from external API',
            'cache' => 'Data from memory cache',
            'database_cache' => 'Data from offline cache (API unavailable)',
            'error' => 'Unable to fetch data',
            default => 'Data retrieved successfully'
        };
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
