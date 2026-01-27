<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ExternalData;
use App\Models\SupportCardDefinition;
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

    protected CharacterMappingService $characterMapping;

    public function __construct(?CharacterMappingService $characterMapping = null)
    {
        /** @var string $baseUrl */
        $baseUrl = config('services.umapyoi.url', 'https://www.umapyoi.net');
        $this->baseUrl = \is_string($baseUrl) ? $baseUrl : 'https://www.umapyoi.net';

        /** @var int $timeout */
        $timeout = config('services.umapyoi.timeout', 30);
        $this->timeout = \is_int($timeout) ? $timeout : 30;

        $this->characterMapping = $characterMapping ?? new CharacterMappingService;
    }

    /**
     * Fetch all support cards from umapyoi.net API
     *
     * @return array<int, array<string, mixed>>
     */
    public function fetchAllSupportCards(): array
    {
        $cacheKey = self::CACHE_PREFIX.'all_support_cards';

        /** @var array<int, array<string, mixed>>|null $cached */
        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->baseUrl}/api/v1/support");

            if (! $response->successful()) {
                Log::warning('[ExternalDataService] Support cards API request failed', [
                    'status' => $response->status(),
                ]);

                return [];
            }

            $cards = $response->json();
            if (! \is_array($cards)) {
                return [];
            }

            // Ensure we return array<int, array<string, mixed>>
            /** @var array<int, array<string, mixed>> */
            $indexedCards = array_values($cards);
            Cache::put($cacheKey, $indexedCards, self::CACHE_TTL);

            return $indexedCards;
        } catch (\Exception $e) {
            Log::error('[ExternalDataService] Failed to fetch support cards', [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Sync all support cards from umapyoi.net to database
     *
     * @param  string|null  $rarityFilter  Filter by rarity (R, SR, SSR, or null for all)
     * @param  int|null  $limit  Limit number of cards to sync
     * @param  callable|null  $progressCallback  Callback for progress updates
     * @return array{success: bool, synced_count: int, skipped_count: int, errors: array<string>, source: string}
     */
    public function syncAllSupportCards(
        ?string $rarityFilter = null,
        ?int $limit = null,
        ?callable $progressCallback = null
    ): array {
        $apiCards = $this->fetchAllSupportCards();

        if (empty($apiCards)) {
            return [
                'success' => false,
                'synced_count' => 0,
                'skipped_count' => 0,
                'errors' => ['Failed to fetch cards from API'],
                'source' => 'error',
            ];
        }

        // Filter by rarity if specified
        if ($rarityFilter !== null) {
            $apiCards = array_filter($apiCards, function ($card) use ($rarityFilter) {
                $cardId = isset($card['id']) && \is_numeric($card['id']) ? (int) $card['id'] : 0;
                $rarity = $this->determineRarity($cardId);

                return strtoupper($rarity) === strtoupper($rarityFilter);
            });
        }

        // Apply limit if specified
        if ($limit !== null && $limit > 0) {
            $apiCards = \array_slice($apiCards, 0, $limit);
        }

        $syncedCount = 0;
        $skippedCount = 0;
        $errors = [];
        $total = \count($apiCards);
        $current = 0;

        foreach ($apiCards as $apiCard) {
            $current++;

            if ($progressCallback !== null) {
                $progressCallback($current, $total);
            }

            if (! \is_array($apiCard)) {
                $skippedCount++;

                continue;
            }

            try {
                $transformed = $this->transformUmapyoiCard($apiCard);

                if ($transformed === null) {
                    $skippedCount++;

                    continue;
                }

                SupportCardDefinition::updateOrCreate(
                    ['external_source_id' => $transformed['external_source_id']],
                    $transformed
                );

                $syncedCount++;
            } catch (\Exception $e) {
                $cardId = isset($apiCard['id']) && \is_scalar($apiCard['id']) ? (string) $apiCard['id'] : 'unknown';
                $errors[] = "Card {$cardId}: {$e->getMessage()}";
            }
        }

        $this->logSync('support_cards_full', $syncedCount, $errors);

        return [
            'success' => true,
            'synced_count' => $syncedCount,
            'skipped_count' => $skippedCount,
            'errors' => $errors,
            'source' => 'umapyoi_api',
        ];
    }

    /**
     * Transform umapyoi.net API card data to database format
     *
     * @param  array<string, mixed>  $apiCard
     * @return array<string, mixed>|null
     */
    public function transformUmapyoiCard(array $apiCard): ?array
    {
        $id = $apiCard['id'] ?? null;
        $charaId = $apiCard['chara_id'] ?? null;
        $gametoraId = $apiCard['gametora'] ?? null;
        $titleEn = $apiCard['title_en'] ?? null;

        if ($id === null) {
            return null;
        }

        $idInt = \is_numeric($id) ? (int) $id : 0;
        $charaIdInt = $charaId !== null && \is_numeric($charaId) ? (int) $charaId : null;
        $gametoraIdStr = $gametoraId !== null && \is_string($gametoraId) ? $gametoraId : null;
        $titleEnStr = $titleEn !== null && \is_string($titleEn) ? $titleEn : null;

        $characterName = $charaIdInt !== null
            ? $this->characterMapping->getCharacterName($charaIdInt)
            : ($gametoraIdStr !== null ? $this->characterMapping->extractNameFromGametoraId($gametoraIdStr) : 'Unknown');

        $rarity = $this->determineRarity($idInt);
        $cardType = $this->determineCardType($charaIdInt ?? 0, $gametoraIdStr ?? '');

        // Build card name: "Character Name [Title]"
        $cardName = $titleEnStr !== null
            ? "{$characterName} {$titleEnStr}"
            : "{$characterName} [{$rarity}]";

        // Generate internal ID from gametora ID or card ID
        $internalId = $gametoraIdStr !== null
            ? 'UMAPYOI_'.strtoupper(str_replace('-', '_', $gametoraIdStr))
            : "UMAPYOI_{$idInt}";

        return [
            'name' => $cardName,
            'internal_id' => $internalId,
            'external_source_id' => (string) $idInt,
            'external_source' => 'umapyoi',
            'gametora_id' => $gametoraIdStr,
            'chara_id' => $charaIdInt,
            'card_type' => $cardType,
            'rarity' => $rarity,
            'character_name' => $characterName,
            'character_internal_id' => $charaIdInt !== null ? "CHAR_{$charaIdInt}" : null,
            'is_active' => $titleEnStr !== null, // Cards without English title are unreleased
            'server_availability' => $titleEn !== null ? 'both' : 'jp',
            'meta_tier' => $this->getDefaultMetaTier($rarity),
            'unique_effects' => [],
            'skill_hints_provided' => [],
            'deck_synergies' => [],
            'recommended_scenarios' => [],
            'strategic_notes' => [],
        ];
    }

    /**
     * Determine card rarity from ID
     *
     * ID ranges:
     * - 10xxx: R rarity
     * - 20xxx: SR rarity
     * - 30xxx: SSR rarity
     */
    public function determineRarity(int $cardId): string
    {
        if ($cardId >= 30000) {
            return 'SSR';
        }
        if ($cardId >= 20000) {
            return 'SR';
        }
        if ($cardId >= 10000) {
            return 'R';
        }

        return 'R'; // Default
    }

    /**
     * Determine card type from character ID and gametora ID
     */
    public function determineCardType(int $charaId, string $gametoraId): string
    {
        // Support/trainer characters are Friend type
        if ($charaId >= 9000) {
            return 'friend';
        }

        // Character-based type inference (simplified)
        // In reality, this would need more detailed mapping
        $speedCharacters = [1002, 1003, 1006, 1041, 1046, 1066, 1068];
        $staminaCharacters = [1013, 1025, 1030, 1045, 1058];
        $powerCharacters = [1007, 1008, 1014, 1050];
        $gutsCharacters = [1052, 1055, 1065];
        $witCharacters = [1017, 1022, 1032];

        if (\in_array($charaId, $speedCharacters, true)) {
            return 'speed';
        }
        if (\in_array($charaId, $staminaCharacters, true)) {
            return 'stamina';
        }
        if (\in_array($charaId, $powerCharacters, true)) {
            return 'power';
        }
        if (\in_array($charaId, $gutsCharacters, true)) {
            return 'guts';
        }
        if (\in_array($charaId, $witCharacters, true)) {
            return 'wit';
        }

        // Default to speed for unknown characters
        return 'speed';
    }

    /**
     * Get default meta tier based on rarity
     */
    protected function getDefaultMetaTier(string $rarity): string
    {
        return match ($rarity) {
            'SSR' => 'B',
            'SR' => 'C',
            'R' => 'C',
            default => 'C',
        };
    }

    /**
     * Sync character data from external source
     *
     * @param  bool  $forceRefresh  Force cache refresh
     * @return array{success: bool, synced_count: int, errors: array<string>, source: string}
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
                ->get("{$this->baseUrl}/api/v1/chara");

            if (! $response->successful()) {
                throw new \RuntimeException("API returned: {$response->status()}");
            }

            $characters = $response->json();
            if (! \is_array($characters)) {
                throw new \RuntimeException('Invalid response format: expected array');
            }
            $syncedCount = 0;
            $errors = [];

            foreach ($characters as $charData) {
                if (! \is_array($charData)) {
                    continue;
                }
                try {
                    /** @var array<string, mixed> $charData */
                    $this->upsertCharacterData($charData);
                    $syncedCount++;
                } catch (\Exception $e) {
                    $charId = 'unknown';
                    if (isset($charData['id'])) {
                        $idValue = $charData['id'];
                        if (\is_string($idValue) || \is_int($idValue)) {
                            $charId = (string) $idValue;
                        }
                    }
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
     * Sync support card data from external source (legacy method)
     *
     * @param  bool  $forceRefresh  Force cache refresh
     * @return array{success: bool, synced_count: int, errors: array<string>, source: string}
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

        // Use the new comprehensive sync method
        $result = $this->syncAllSupportCards();

        if ($result['success']) {
            Cache::put($cacheKey, [
                'synced_at' => now()->toIso8601String(),
                'count' => $result['synced_count'],
            ], self::CACHE_TTL);
        }

        return [
            'success' => $result['success'],
            'synced_count' => $result['synced_count'],
            'errors' => $result['errors'],
            'source' => $result['source'],
        ];
    }

    /**
     * Fetch skill data with caching
     *
     * @return array<string, mixed>|null
     */
    public function getSkillData(string $skillId): ?array
    {
        $cacheKey = self::CACHE_PREFIX."skill:{$skillId}";

        /** @var array<string, mixed>|null $result */
        $result = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($skillId) {
            try {
                /** @var \Illuminate\Http\Client\Response $response */
                $response = Http::timeout($this->timeout)
                    ->get("{$this->baseUrl}/api/v1/skills/{$skillId}");

                if ($response->successful()) {
                    $data = $response->json('data');

                    return \is_array($data) ? $data : null;
                }
            } catch (\Exception $e) {
                Log::warning('[ExternalDataService] Skill fetch failed', [
                    'skill_id' => $skillId,
                    'error' => $e->getMessage(),
                ]);
            }

            return null;
        });

        return $result;
    }

    /**
     * Get sync status
     *
     * @return array<string, array{synced_at: string|null, count: int}>
     */
    public function getSyncStatus(): array
    {
        /** @var array<string, mixed>|null $characters */
        $characters = Cache::get(self::CACHE_PREFIX.'characters', [
            'synced_at' => null,
            'count' => 0,
        ]);
        /** @var array<string, mixed>|null $supportCards */
        $supportCards = Cache::get(self::CACHE_PREFIX.'support_cards', [
            'synced_at' => null,
            'count' => 0,
        ]);

        $characterStatus = \is_array($characters) ? $characters : [];
        $supportCardStatus = \is_array($supportCards) ? $supportCards : [];

        return [
            'characters' => [
                'synced_at' => isset($characterStatus['synced_at']) && \is_string($characterStatus['synced_at']) ? $characterStatus['synced_at'] : null,
                'count' => isset($characterStatus['count']) && \is_int($characterStatus['count']) ? $characterStatus['count'] : 0,
            ],
            'support_cards' => [
                'synced_at' => isset($supportCardStatus['synced_at']) && \is_string($supportCardStatus['synced_at']) ? $supportCardStatus['synced_at'] : null,
                'count' => isset($supportCardStatus['count']) && \is_int($supportCardStatus['count']) ? $supportCardStatus['count'] : 0,
            ],
        ];
    }

    /**
     * Upsert character data from external source
     *
     * @param  array<string, mixed>  $data
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
     *
     * @param  array<string, mixed>  $data
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
     *
     * @param  array<int, string>  $errors
     */
    protected function logSync(string $dataType, int $count, array $errors): void
    {
        Log::info('[ExternalDataService] Sync completed', [
            'data_type' => $dataType,
            'synced_count' => $count,
            'error_count' => \count($errors),
        ]);
    }

    /**
     * Clear all cached external data
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_PREFIX.'characters');
        Cache::forget(self::CACHE_PREFIX.'support_cards');
        Cache::forget(self::CACHE_PREFIX.'all_support_cards');

        Log::info('[ExternalDataService] Cache cleared');
    }

    /**
     * Check if external API is available
     */
    public function isApiAvailable(): bool
    {
        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::timeout(5)->get("{$this->baseUrl}/api/v1/support");

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get support card statistics
     *
     * @return array{total: int, by_rarity: array<string, int>, by_type: array<string, int>}
     */
    public function getSupportCardStats(): array
    {
        $cards = $this->fetchAllSupportCards();

        $byRarity = ['SSR' => 0, 'SR' => 0, 'R' => 0];
        $byType = ['speed' => 0, 'stamina' => 0, 'power' => 0, 'guts' => 0, 'wit' => 0, 'friend' => 0];

        foreach ($cards as $card) {
            if (! \is_array($card)) {
                continue;
            }

            $cardId = isset($card['id']) && \is_numeric($card['id']) ? (int) $card['id'] : 0;
            $charaId = isset($card['chara_id']) && \is_numeric($card['chara_id']) ? (int) $card['chara_id'] : 0;
            $gametoraId = isset($card['gametora']) && \is_string($card['gametora']) ? $card['gametora'] : '';

            $rarity = $this->determineRarity($cardId);
            $type = $this->determineCardType(
                $charaId,
                $gametoraId
            );

            $byRarity[$rarity] = ($byRarity[$rarity] ?? 0) + 1;
            $byType[$type] = ($byType[$type] ?? 0) + 1;
        }

        return [
            'total' => \count($cards),
            'by_rarity' => $byRarity,
            'by_type' => $byType,
        ];
    }
}
