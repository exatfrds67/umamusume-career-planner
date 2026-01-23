<?php

declare(strict_types=1);

namespace App\Services\ExternalAPI;

use Illuminate\Support\Facades\Log;

/**
 * External API Facade
 *
 * Unified interface for all external API integrations with intelligent
 * fallback, context management, and health monitoring.
 *
 * Requirements: 14.1, 14.2, 55.3, Task 4.4.1
 */
class ExternalAPIFacade
{
    public function __construct(
        protected UmapyoiApiClient $umapyoiClient,
        protected UmamusumeDBApiClient $umamusumeDBClient,
        protected Context7Service $contextService
    ) {}

    /**
     * Get character data with intelligent fallback
     *
     * @return array{success: bool, data: array<int, array<string, mixed>>, source: string, error?: string}
     */
    public function getCharacters(): array
        // Store context for this API call
        $this->contextService->storeApiCallContext('umapyoi', '/v1/characters', [
            'force_refresh' => $forceRefresh,
            'timestamp' => now()->toIso8601String(),
        ]);

        // Try primary source (umapyoi.net)
        $result = $this->umapyoiClient->getCharacters($forceRefresh);

        if ($result['success']) {
            Log::info('[ExternalAPIFacade] Characters fetched successfully', [
                'source' => $result['source'],
                'count' => count($result['data']),
            ]);

            return $result;
        }

        // Log failure and return error
        Log::warning('[ExternalAPIFacade] Failed to fetch characters from all sources', [
            'primary_error' => $result['error'] ?? 'Unknown error',
        ]);

        return $result;
    }

    /**
     * Get a specific character by ID
     *
     * @return array{success: bool, data: array<string, mixed>|null, source: string, error?: string}
     */
    public function getCharacter(): array
        $this->contextService->storeApiCallContext('umapyoi', "/v1/characters/{$characterId}", [
            'character_id' => $characterId,
            'force_refresh' => $forceRefresh,
            'timestamp' => now()->toIso8601String(),
        ]);

        return $this->umapyoiClient->getCharacter($characterId, $forceRefresh);
    }

    /**
     * Get support cards with intelligent fallback
     *
     * @return array{success: bool, data: array<int, array<string, mixed>>, source: string, error?: string}
     */
    public function getSupportCards(): array
        $this->contextService->storeApiCallContext('umapyoi', '/v1/support-cards', [
            'force_refresh' => $forceRefresh,
            'timestamp' => now()->toIso8601String(),
        ]);

        $result = $this->umapyoiClient->getSupportCards($forceRefresh);

        if ($result['success']) {
            Log::info('[ExternalAPIFacade] Support cards fetched successfully', [
                'source' => $result['source'],
                'count' => count($result['data']),
            ]);

            return $result;
        }

        Log::warning('[ExternalAPIFacade] Failed to fetch support cards', [
            'error' => $result['error'] ?? 'Unknown error',
        ]);

        return $result;
    }

    /**
     * Get a specific support card by ID
     *
     * @return array{success: bool, data: array<string, mixed>|null, source: string, error?: string}
     */
    public function getSupportCard(): array
        $this->contextService->storeApiCallContext('umapyoi', "/v1/support-cards/{$cardId}", [
            'card_id' => $cardId,
            'force_refresh' => $forceRefresh,
            'timestamp' => now()->toIso8601String(),
        ]);

        return $this->umapyoiClient->getSupportCard($cardId, $forceRefresh);
    }

    /**
     * Get latest news
     *
     * @return array{success: bool, data: array<int, array<string, mixed>>, source: string, error?: string}
     */
    public function getNews(): array
        $this->contextService->storeApiCallContext('umapyoi', '/v1/news', [
            'limit' => $limit,
            'force_refresh' => $forceRefresh,
            'timestamp' => now()->toIso8601String(),
        ]);

        return $this->umapyoiClient->getNews($limit, $forceRefresh);
    }

    /**
     * Get training calculation from UmamusumeDB
     *
     * @param  array<string, mixed>  $params
     * @return array{success: bool, data: array<string, mixed>|null, source: string, error?: string}
     */
    public function getTrainingCalculation(): array
        $this->contextService->storeApiCallContext('umamusumedb', '/v1/training/calculate', [
            'params' => $params,
            'force_refresh' => $forceRefresh,
            'timestamp' => now()->toIso8601String(),
        ]);

        return $this->umamusumeDBClient->getTrainingCalculation($params, $forceRefresh);
    }

    /**
     * Get meta tier rankings from UmamusumeDB
     *
     * @return array{success: bool, data: array<int, array<string, mixed>>, source: string, error?: string}
     */
    public function getMetaTierRankings(): array
        $this->contextService->storeApiCallContext('umamusumedb', '/v1/meta/tier-rankings', [
            'force_refresh' => $forceRefresh,
            'timestamp' => now()->toIso8601String(),
        ]);

        return $this->umamusumeDBClient->getMetaTierRankings($forceRefresh);
    }

    /**
     * Get community builds for a character
     *
     * @return array{success: bool, data: array<int, array<string, mixed>>, source: string, error?: string}
     */
    public function getCommunityBuilds(): array
        $this->contextService->storeApiCallContext('umamusumedb', "/v1/characters/{$characterId}/builds", [
            'character_id' => $characterId,
            'force_refresh' => $forceRefresh,
            'timestamp' => now()->toIso8601String(),
        ]);

        return $this->umamusumeDBClient->getCommunityBuilds($characterId, $forceRefresh);
    }

    /**
     * Get skill effectiveness data
     *
     * @return array{success: bool, data: array<int, array<string, mixed>>, source: string, error?: string}
     */
    public function getSkillEffectiveness(): array
        $this->contextService->storeApiCallContext('umamusumedb', '/v1/skills/effectiveness', [
            'force_refresh' => $forceRefresh,
            'timestamp' => now()->toIso8601String(),
        ]);

        return $this->umamusumeDBClient->getSkillEffectiveness($forceRefresh);
    }

    /**
     * Get race strategy recommendations
     *
     * @param  array<string, mixed>  $params
     * @return array{success: bool, data: array<string, mixed>|null, source: string, error?: string}
     */
    public function getRaceStrategy(): array
        $this->contextService->storeApiCallContext('umamusumedb', '/v1/race/strategy', [
            'params' => $params,
            'force_refresh' => $forceRefresh,
            'timestamp' => now()->toIso8601String(),
        ]);

        return $this->umamusumeDBClient->getRaceStrategy($params, $forceRefresh);
    }

    /**
     * Check health status of all external APIs
     *
     * @return array{umapyoi: array{available: bool, cache_status: array<string, bool>}, umamusumedb: array{available: bool, cache_status: array<string, bool>}, context7: array{enabled: bool, healthy: bool}}
     */
    public function getHealthStatus(): array
        return [
            'umapyoi' => [
                'available' => $this->umapyoiClient->isAvailable(),
                'cache_status' => $this->umapyoiClient->getCacheStatus(),
            ],
            'umamusumedb' => [
                'available' => $this->umamusumeDBClient->isAvailable(),
                'cache_status' => $this->umamusumeDBClient->getCacheStatus(),
            ],
            'context7' => [
                'enabled' => $this->contextService->isAvailable(),
                'healthy' => $this->contextService->isAvailable(),
            ],
        ];
    }

    /**
     * Clear all API caches
     */
    public function clearAllCaches(): void
    {
        $this->umapyoiClient->clearCache();
        $this->umamusumeDBClient->clearCache();
        $this->contextService->clearAllContexts();

        Log::info('[ExternalAPIFacade] All API caches cleared');
    }

    /**
     * Get comprehensive API statistics
     *
     * @return array{health: array<string, mixed>, context_summary: array<string, int>, cache_status: array<string, array<string, bool>>}
     */
    public function getStatistics(): array
        return [
            'health' => $this->getHealthStatus(),
            'context_summary' => $this->contextService->getContextSummary(),
            'cache_status' => [
                'umapyoi' => $this->umapyoiClient->getCacheStatus(),
                'umamusumedb' => $this->umamusumeDBClient->getCacheStatus(),
            ],
        ];
    }

    /**
     * Sync all external data
     *
     * @return array{characters: array{success: bool, synced_count: int}, support_cards: array{success: bool, synced_count: int}, meta_data: array{success: bool, synced_count: int}}
     */
    public function syncAllData(): array
        Log::info('[ExternalAPIFacade] Starting full data sync');

        $results = [
            'characters' => $this->getCharacters(true),
            'support_cards' => $this->getSupportCards(true),
            'meta_data' => $this->getMetaTierRankings(true),
        ];

        $totalSuccess = $results['characters']['success'] &&
            $results['support_cards']['success'] &&
            $results['meta_data']['success'];

        Log::info('[ExternalAPIFacade] Full data sync completed', [
            'success' => $totalSuccess,
            'characters_count' => count($results['characters']['data'] ?? []),
            'support_cards_count' => count($results['support_cards']['data'] ?? []),
            'meta_data_count' => count($results['meta_data']['data'] ?? []),
        ]);

        return $results;
    }
}
