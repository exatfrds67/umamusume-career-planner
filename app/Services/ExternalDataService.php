<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Character;
use App\Models\ExternalData;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * External Data Service
 *
 * Fetches and syncs character/support card data from external APIs (umapyoi.net).
 * Implements Redis caching with 24h TTL.
 *
 * Requirements: Task 4.4
 */
class ExternalDataService
{
    /**
     * Base URL for external API
     */
    protected string $baseUrl;

    /**
     * API timeout in seconds
     */
    protected int $timeout;

    /**
     * Cache TTL in seconds (24 hours)
     */
    protected const CACHE_TTL = 86400;

    /**
     * Cache prefix
     */
    protected const CACHE_PREFIX = 'external_data:';

    public function __construct()
    {
        $this->baseUrl = (string) config('services.umapyoi.url', 'https://api.umapyoi.net');
        $this->timeout = (int) config('services.umapyoi.timeout', 30);
    }

    /**
     * Sync character data from external source
     *
     * @param  bool  $forceRefresh  Force cache refresh
     * @return array{success: bool, synced_count: int, errors: array<string>}
     */
    public function syncCharacterData(bool $forceRefresh = false): array
    {
        $cacheKey = self::CACHE_PREFIX.'characters';

        if (! $forceRefresh && Cache::has($cacheKey)) {
            return [
                'success' => true,
                'synced_count' => 0,
                'errors' => [],
                'source' => 'cache',
            ];
        }

        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::timeout($this->timeout)
                ->get("{$this->baseUrl}/v1/characters");

            if (! $response->successful()) {
                throw new \RuntimeException("API returned: {$response->status()}");
            }

            $characters = $response->json('data', []);
            $syncedCount = 0;
            $errors = [];

            foreach ($characters as $charData) {
                try {
                    $this->upsertCharacterData($charData);
                    $syncedCount++;
                } catch (\Exception $e) {
                    $charId = $charData['id'] ?? 'unknown';
                    $errors[] = "Character {$charId}: {$e->getMessage()}";
                }
            }

            // Cache the sync result
            Cache::put($cacheKey, [
                'synced_at' => now()->toIso8601String(),
                'count' => $syncedCount,
            ], self::CACHE_TTL);

            // Log sync
            $this->logSync('characters', $syncedCount, $errors);

            return [
                'success' => true,
                'synced_count' => $syncedCount,
                'errors' => $errors,
                'source' => 'api',
            ];

        } catch (\Exception $e) {
            Log::error('[ExternalDataService] Character sync failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'synced_count' => 0,
                'errors' => [$e->getMessage()],
                'source' => 'error',
            ];
        }
    }

    /**
     * Sync support card data from external source
     *
     * @param  bool  $forceRefresh  Force cache refresh
     * @return array{success: bool, synced_count: int, errors: array<string>}
     */
    public function syncSupportCardData(bool $forceRefresh = false): array
    {
        $cacheKey = self::CACHE_PREFIX.'support_cards';

        if (! $forceRefresh && Cache::has($cacheKey)) {
            return [
                'success' => true,
                'synced_count' => 0,
                'errors' => [],
                'source' => 'cache',
            ];
        }

        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::timeout($this->timeout)
                ->get("{$this->baseUrl}/v1/support-cards");

            if (! $response->successful()) {
                throw new \RuntimeException("API returned: {$response->status()}");
            }

            $cards = $response->json('data', []);
            $syncedCount = 0;
            $errors = [];

            foreach ($cards as $cardData) {
                try {
                    $this->upsertSupportCardData($cardData);
                    $syncedCount++;
                } catch (\Exception $e) {
                    $cardId = $cardData['id'] ?? 'unknown';
                    $errors[] = "Card {$cardId}: {$e->getMessage()}";
                }
            }

            // Cache the sync result
            Cache::put($cacheKey, [
                'synced_at' => now()->toIso8601String(),
                'count' => $syncedCount,
            ], self::CACHE_TTL);

            // Log sync
            $this->logSync('support_cards', $syncedCount, $errors);

            return [
                'success' => true,
                'synced_count' => $syncedCount,
                'errors' => $errors,
                'source' => 'api',
            ];

        } catch (\Exception $e) {
            Log::error('[ExternalDataService] Support card sync failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'synced_count' => 0,
                'errors' => [$e->getMessage()],
                'source' => 'error',
            ];
        }
    }

    /**
     * Fetch skill data with caching
     *
     * @return array<string, mixed>|null
     */
    public function getSkillData(string $skillId): ?array
    {
        $cacheKey = self::CACHE_PREFIX."skill:{$skillId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($skillId) {
            try {
                /** @var \Illuminate\Http\Client\Response $response */
                $response = Http::timeout($this->timeout)
                    ->get("{$this->baseUrl}/v1/skills/{$skillId}");

                if ($response->successful()) {
                    return $response->json('data');
                }
            } catch (\Exception $e) {
                Log::warning('[ExternalDataService] Skill fetch failed', [
                    'skill_id' => $skillId,
                    'error' => $e->getMessage(),
                ]);
            }

            return null;
        });
    }

    /**
     * Get sync status
     *
     * @return array<string, array{synced_at: string|null, count: int}>
     */
    public function getSyncStatus(): array
    {
        $characters = Cache::get(self::CACHE_PREFIX.'characters', [
            'synced_at' => null,
            'count' => 0,
        ]);
        $supportCards = Cache::get(self::CACHE_PREFIX.'support_cards', [
            'synced_at' => null,
            'count' => 0,
        ]);

        $characterStatus = is_array($characters) ? $characters : [];
        $supportCardStatus = is_array($supportCards) ? $supportCards : [];

        return [
            'characters' => [
                'synced_at' => isset($characterStatus['synced_at']) ? (string) $characterStatus['synced_at'] : null,
                'count' => (int) ($characterStatus['count'] ?? 0),
            ],
            'support_cards' => [
                'synced_at' => isset($supportCardStatus['synced_at']) ? (string) $supportCardStatus['synced_at'] : null,
                'count' => (int) ($supportCardStatus['count'] ?? 0),
            ],
        ];
    }

    /**
     * Upsert character data from external source
     */
    protected function upsertCharacterData(array $data): void
    {
        ExternalData::updateOrCreate(
            [
                'source' => 'umapyoi',
                'data_type' => 'character',
                'external_id' => $data['id'] ?? null,
            ],
            [
                'name' => $data['name'] ?? 'Unknown',
                'data' => $data,
                'last_synced_at' => now(),
            ]
        );
    }

    /**
     * Upsert support card data from external source
     */
    protected function upsertSupportCardData(array $data): void
    {
        ExternalData::updateOrCreate(
            [
                'source' => 'umapyoi',
                'data_type' => 'support_card',
                'external_id' => $data['id'] ?? null,
            ],
            [
                'name' => $data['name'] ?? 'Unknown',
                'data' => $data,
                'last_synced_at' => now(),
            ]
        );
    }

    /**
     * Log sync operation
     */
    protected function logSync(string $dataType, int $count, array $errors): void
    {
        Log::info('[ExternalDataService] Sync completed', [
            'data_type' => $dataType,
            'synced_count' => $count,
            'error_count' => count($errors),
        ]);
    }

    /**
     * Clear all cached external data
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_PREFIX.'characters');
        Cache::forget(self::CACHE_PREFIX.'support_cards');

        Log::info('[ExternalDataService] Cache cleared');
    }

    /**
     * Check if external API is available
     */
    public function isApiAvailable(): bool
    {
        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::timeout(5)->get("{$this->baseUrl}/health");

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
