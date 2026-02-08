<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Skill;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Skill Catalog Cache Service
 *
 * Provides caching for the skill catalog used by the AI Training Advisory System.
 * Implements a 24-hour TTL cache with version-based invalidation.
 *
 * Cache key format: advisory:skill_catalog:{version}
 *
 * @see \App\Services\TrainingAdvisoryService For usage in advisory recommendations
 */
class SkillCatalogCacheService
{
    /**
     * Cache key prefix for skill catalog
     */
    protected const CACHE_KEY_PREFIX = 'advisory:skill_catalog';

    /**
     * Cache TTL in seconds (24 hours)
     */
    protected const CACHE_TTL = 86400;

    /**
     * Current catalog version - increment when skill data structure changes
     */
    protected const CATALOG_VERSION = '1.0.0';

    /**
     * Get the full skill catalog from cache or database.
     *
     * Returns all active skills with their evolution relationships,
     * meta tiers, and strategic notes for advisory recommendations.
     *
     * @param  string|null  $version  Optional version override for testing
     * @return Collection<int, array<string, mixed>> Cached skill catalog
     */
    public function getSkillCatalog(?string $version = null): Collection
    {
        $cacheKey = $this->getCacheKey($version);

        /** @var Collection<int, array<string, mixed>> $result */
        $result = Cache::remember($cacheKey, self::CACHE_TTL, function (): Collection {
            Log::info('[SkillCatalogCache] Building skill catalog from database');

            return $this->buildSkillCatalog();
        });

        return $result;
    }

    /**
     * Get skills filtered by type from the cached catalog.
     *
     * @param  string  $type  Skill type (speed, stamina, power, guts, wisdom, recovery, positioning, etc.)
     * @param  string|null  $version  Optional version override
     * @return Collection<int, array<string, mixed>> Filtered skills
     */
    public function getSkillsByType(string $type, ?string $version = null): Collection
    {
        return $this->getSkillCatalog($version)
            ->filter(fn (array $skill): bool => ($skill['skill_type'] ?? '') === $type);
    }

    /**
     * Get skills filtered by rarity from the cached catalog.
     *
     * @param  string  $rarity  Skill rarity (common, rare, gold)
     * @param  string|null  $version  Optional version override
     * @return Collection<int, array<string, mixed>> Filtered skills
     */
    public function getSkillsByRarity(string $rarity, ?string $version = null): Collection
    {
        return $this->getSkillCatalog($version)
            ->filter(fn (array $skill): bool => ($skill['rarity'] ?? '') === $rarity);
    }

    /**
     * Get skills filtered by meta tier from the cached catalog.
     *
     * @param  string  $tier  Meta tier (S, A, B, C)
     * @param  string|null  $version  Optional version override
     * @return Collection<int, array<string, mixed>> Filtered skills
     */
    public function getSkillsByMetaTier(string $tier, ?string $version = null): Collection
    {
        return $this->getSkillCatalog($version)
            ->filter(fn (array $skill): bool => ($skill['meta_tier'] ?? '') === $tier);
    }

    /**
     * Get high-tier skills that can be recommended for purchase.
     *
     * Returns high-tier skills (unique/rare with high meta tier) sorted by meta tier
     * for advisory recommendations. In game terminology, these are "gold skills".
     *
     * Note: Database uses 'normal', 'rare', 'unique' for rarity.
     * Advisory system uses 'gold' to refer to high-impact skills (typically unique rarity
     * or rare skills with S/A meta tier).
     *
     * @param  string|null  $version  Optional version override
     * @return Collection<int, array<string, mixed>> High-tier skills sorted by meta tier
     */
    public function getGoldSkillsForAdvisory(?string $version = null): Collection
    {
        return $this->getSkillCatalog($version)
            ->filter(function (array $skill): bool {
                // "Gold skills" in game terms are:
                // 1. Unique rarity skills (highest tier)
                // 2. Rare skills with S or A meta tier
                $rarity = $skill['rarity'] ?? '';
                $metaTier = isset($skill['meta_tier']) && is_string($skill['meta_tier'])
                    ? strtoupper($skill['meta_tier'])
                    : 'C';

                if ($rarity === 'unique') {
                    return true;
                }

                if ($rarity === 'rare' && in_array($metaTier, ['S', 'S+', 'A'], true)) {
                    return true;
                }

                return false;
            })
            ->sortByDesc(fn (array $skill): int => $this->getMetaTierPriority(
                isset($skill['meta_tier']) && is_string($skill['meta_tier'])
                    ? $skill['meta_tier']
                    : 'C'
            ))
            ->values();
    }

    /**
     * Get recovery skills for stamina requirement calculations.
     *
     * @param  string|null  $version  Optional version override
     * @return Collection<int, array<string, mixed>> Recovery skills
     */
    public function getRecoverySkills(?string $version = null): Collection
    {
        return $this->getSkillsByType('recovery', $version);
    }

    /**
     * Get a specific skill by ID from the cached catalog.
     *
     * @param  int  $skillId  Skill ID to find
     * @param  string|null  $version  Optional version override
     * @return array<string, mixed>|null Skill data or null if not found
     */
    public function getSkillById(int $skillId, ?string $version = null): ?array
    {
        $skill = $this->getSkillCatalog($version)
            ->firstWhere('id', $skillId);

        return $skill !== null ? (array) $skill : null;
    }

    /**
     * Search skills by name in the cached catalog.
     *
     * @param  string  $searchTerm  Search term to match against skill names
     * @param  string|null  $version  Optional version override
     * @return Collection<int, array<string, mixed>> Matching skills
     */
    public function searchSkillsByName(string $searchTerm, ?string $version = null): Collection
    {
        $searchLower = strtolower($searchTerm);

        return $this->getSkillCatalog($version)
            ->filter(function (array $skill) use ($searchLower): bool {
                $name = isset($skill['name']) && is_string($skill['name'])
                    ? strtolower($skill['name'])
                    : '';
                $nameEn = isset($skill['name_en']) && is_string($skill['name_en'])
                    ? strtolower($skill['name_en'])
                    : '';

                return str_contains($name, $searchLower) || str_contains($nameEn, $searchLower);
            });
    }

    /**
     * Invalidate the skill catalog cache.
     *
     * Should be called when skill data is updated in the database.
     *
     * @param  string|null  $version  Optional version to invalidate (defaults to current)
     * @return bool True if cache was successfully invalidated
     */
    public function invalidateCache(?string $version = null): bool
    {
        $cacheKey = $this->getCacheKey($version);

        $result = Cache::forget($cacheKey);

        Log::info('[SkillCatalogCache] Cache invalidated', [
            'cache_key' => $cacheKey,
            'success' => $result,
        ]);

        return $result;
    }

    /**
     * Check if the skill catalog is currently cached.
     *
     * @param  string|null  $version  Optional version to check
     * @return bool True if catalog is cached
     */
    public function isCached(?string $version = null): bool
    {
        return Cache::has($this->getCacheKey($version));
    }

    /**
     * Get the current catalog version.
     *
     * @return string Current version string
     */
    public function getCurrentVersion(): string
    {
        return self::CATALOG_VERSION;
    }

    /**
     * Get the cache TTL in seconds.
     *
     * @return int TTL in seconds
     */
    public function getCacheTtl(): int
    {
        return self::CACHE_TTL;
    }

    /**
     * Get cache statistics for monitoring.
     *
     * @param  string|null  $version  Optional version to check
     * @return array{is_cached: bool, cache_key: string, version: string, ttl_seconds: int}
     */
    public function getCacheStats(?string $version = null): array
    {
        return [
            'is_cached' => $this->isCached($version),
            'cache_key' => $this->getCacheKey($version),
            'version' => $version ?? self::CATALOG_VERSION,
            'ttl_seconds' => self::CACHE_TTL,
        ];
    }

    /**
     * Build the cache key for the skill catalog.
     *
     * @param  string|null  $version  Optional version override
     * @return string Cache key
     */
    protected function getCacheKey(?string $version = null): string
    {
        $effectiveVersion = $version ?? self::CATALOG_VERSION;

        return self::CACHE_KEY_PREFIX.':'.$effectiveVersion;
    }

    /**
     * Build the skill catalog from the database.
     *
     * @return Collection<int, array<string, mixed>> Skill catalog data
     */
    protected function buildSkillCatalog(): Collection
    {
        /** @var Collection<int, array<string, mixed>> $catalog */
        $catalog = Skill::query()
            ->where('is_active', true)
            ->with(['evolutionTarget', 'evolutionSource'])
            ->get()
            ->map(function (Skill $skill): array {
                return [
                    'id' => $skill->id,
                    'name' => $skill->name,
                    'name_en' => $skill->name_en ?? $skill->name,
                    'internal_id' => $skill->internal_id,
                    'skill_type' => $skill->skill_type,
                    'rarity' => $skill->rarity,
                    'base_sp_cost' => $skill->base_sp_cost,
                    'can_evolve' => $skill->can_evolve,
                    'is_evolution' => $skill->is_evolution,
                    'evolution_target_id' => $skill->evolution_target_id,
                    'evolution_source_id' => $skill->evolution_source_id,
                    'meta_tier' => $skill->meta_tier,
                    'description' => $skill->description,
                    'effects' => $skill->effects,
                    'activation_conditions' => $skill->activation_conditions,
                    'strategic_notes' => $skill->strategic_notes,
                    'synergy_skills' => $skill->synergy_skills,
                    // Include evolution chain info for advisory
                    'evolution_target_name' => $skill->evolutionTarget?->name,
                    'evolution_source_name' => $skill->evolutionSource?->name,
                ];
            });

        return $catalog;
    }

    /**
     * Get numeric priority for meta tier sorting.
     *
     * @param  string  $tier  Meta tier (S, A, B, C)
     * @return int Priority value (higher = better)
     */
    protected function getMetaTierPriority(string $tier): int
    {
        return match (strtoupper($tier)) {
            'S' => 4,
            'A' => 3,
            'B' => 2,
            'C' => 1,
            default => 0,
        };
    }
}
