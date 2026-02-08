<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\RaceDistance;
use App\Enums\RunningStyle;
use App\Models\Race;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Race Requirements Cache Service
 *
 * Provides caching for race requirements used by the AI Training Advisory System.
 * Implements a 1-hour TTL cache with race ID-based keys.
 *
 * Cache key format: advisory:race_requirements:{race_id}
 *
 * Race requirements include:
 * - Distance category (sprint, mile, medium, long)
 * - Distance in meters
 * - Surface (turf, dirt)
 * - Competition level (G1, G2, G3, OP, Pre-OP, Debut)
 * - Stamina requirements based on distance and running style
 *
 * @see \App\Services\TrainingAdvisoryService For usage in advisory recommendations
 * @see \App\Services\GameMechanicsEngine For stamina requirement calculations
 */
class RaceRequirementsCacheService
{
    /**
     * Cache key prefix for race requirements
     */
    protected const CACHE_KEY_PREFIX = 'advisory:race_requirements';

    /**
     * Cache TTL in seconds (1 hour)
     */
    protected const CACHE_TTL = 3600;

    /**
     * Game mechanics engine for stamina calculations
     */
    protected GameMechanicsEngine $mechanicsEngine;

    /**
     * Create a new RaceRequirementsCacheService instance.
     */
    public function __construct(?GameMechanicsEngine $mechanicsEngine = null)
    {
        $this->mechanicsEngine = $mechanicsEngine ?? new GameMechanicsEngine;
    }

    /**
     * Get race requirements from cache or calculate from race data.
     *
     * Returns comprehensive race requirements including distance category,
     * surface, competition level, and stamina requirements for all running styles.
     *
     * @param  int  $raceId  The race ID to get requirements for
     * @return array<string, mixed>|null Cached race requirements or null if race not found
     */
    public function getRaceRequirements(int $raceId): ?array
    {
        $cacheKey = $this->getCacheKey($raceId);

        /** @var array<string, mixed>|null $result */
        $result = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($raceId): ?array {
            Log::info('[RaceRequirementsCache] Building race requirements from database', [
                'race_id' => $raceId,
            ]);

            return $this->buildRaceRequirements($raceId);
        });

        return $result;
    }

    /**
     * Get stamina requirements for a specific race and running style.
     *
     * @param  int  $raceId  The race ID
     * @param  RunningStyle  $style  The running style to calculate for
     * @param  array<int>  $recoverySkills  Array of recovery skill IDs equipped
     * @return int|null Stamina requirement or null if race not found
     */
    public function getStaminaRequirement(
        int $raceId,
        RunningStyle $style,
        array $recoverySkills = []
    ): ?int {
        $requirements = $this->getRaceRequirements($raceId);

        if ($requirements === null) {
            return null;
        }

        $distanceCategory = $requirements['distance_category'] ?? null;

        if ($distanceCategory === null || ! is_string($distanceCategory)) {
            return null;
        }

        $distance = RaceDistance::tryFrom($distanceCategory);

        if ($distance === null) {
            return null;
        }

        return $this->mechanicsEngine->calculateStaminaRequirement(
            $distance,
            $style,
            $recoverySkills
        );
    }

    /**
     * Get stamina requirements for all running styles for a race.
     *
     * @param  int  $raceId  The race ID
     * @param  array<int>  $recoverySkills  Array of recovery skill IDs equipped
     * @return array<string, int>|null Stamina requirements by style or null if race not found
     */
    public function getAllStaminaRequirements(
        int $raceId,
        array $recoverySkills = []
    ): ?array {
        $requirements = $this->getRaceRequirements($raceId);

        if ($requirements === null) {
            return null;
        }

        $distanceCategory = $requirements['distance_category'] ?? null;

        if ($distanceCategory === null || ! is_string($distanceCategory)) {
            return null;
        }

        $distance = RaceDistance::tryFrom($distanceCategory);

        if ($distance === null) {
            return null;
        }

        $staminaRequirements = [];

        foreach (RunningStyle::cases() as $style) {
            $staminaRequirements[$style->value] = $this->mechanicsEngine->calculateStaminaRequirement(
                $distance,
                $style,
                $recoverySkills
            );
        }

        return $staminaRequirements;
    }

    /**
     * Get races by distance category from cache.
     *
     * @param  RaceDistance  $distance  The distance category to filter by
     * @return array<int, array<string, mixed>> Array of race requirements indexed by race ID
     */
    public function getRacesByDistance(RaceDistance $distance): array
    {
        // This method queries the database and caches individual results
        $races = Race::query()
            ->where('distance_category', $distance->value)
            ->get();

        $results = [];

        foreach ($races as $race) {
            $requirements = $this->getRaceRequirements($race->id);
            if ($requirements !== null) {
                $results[$race->id] = $requirements;
            }
        }

        return $results;
    }

    /**
     * Get races by competition level (grade).
     *
     * @param  string  $grade  Competition level (G1, G2, G3, OP, Pre-OP, Debut)
     * @return array<int, array<string, mixed>> Array of race requirements indexed by race ID
     */
    public function getRacesByGrade(string $grade): array
    {
        $races = Race::query()
            ->where('race_grade', $grade)
            ->get();

        $results = [];

        foreach ($races as $race) {
            $requirements = $this->getRaceRequirements($race->id);
            if ($requirements !== null) {
                $results[$race->id] = $requirements;
            }
        }

        return $results;
    }

    /**
     * Invalidate the cache for a specific race.
     *
     * Should be called when race data is updated in the database.
     *
     * @param  int  $raceId  The race ID to invalidate cache for
     * @return bool True if cache was successfully invalidated
     */
    public function invalidateCache(int $raceId): bool
    {
        $cacheKey = $this->getCacheKey($raceId);

        $result = Cache::forget($cacheKey);

        Log::info('[RaceRequirementsCache] Cache invalidated', [
            'race_id' => $raceId,
            'cache_key' => $cacheKey,
            'success' => $result,
        ]);

        return $result;
    }

    /**
     * Invalidate cache for multiple races.
     *
     * @param  array<int>  $raceIds  Array of race IDs to invalidate
     * @return int Number of caches successfully invalidated
     */
    public function invalidateMultiple(array $raceIds): int
    {
        $invalidated = 0;

        foreach ($raceIds as $raceId) {
            if ($this->invalidateCache($raceId)) {
                $invalidated++;
            }
        }

        return $invalidated;
    }

    /**
     * Check if race requirements are currently cached.
     *
     * @param  int  $raceId  The race ID to check
     * @return bool True if requirements are cached
     */
    public function isCached(int $raceId): bool
    {
        return Cache::has($this->getCacheKey($raceId));
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
     * Get cache statistics for a specific race.
     *
     * @param  int  $raceId  The race ID to get stats for
     * @return array{is_cached: bool, cache_key: string, ttl_seconds: int}
     */
    public function getCacheStats(int $raceId): array
    {
        return [
            'is_cached' => $this->isCached($raceId),
            'cache_key' => $this->getCacheKey($raceId),
            'ttl_seconds' => self::CACHE_TTL,
        ];
    }

    /**
     * Build the cache key for race requirements.
     *
     * @param  int  $raceId  The race ID
     * @return string Cache key
     */
    protected function getCacheKey(int $raceId): string
    {
        return self::CACHE_KEY_PREFIX.':'.$raceId;
    }

    /**
     * Build race requirements from the database.
     *
     * @param  int  $raceId  The race ID to build requirements for
     * @return array<string, mixed>|null Race requirements or null if race not found
     */
    protected function buildRaceRequirements(int $raceId): ?array
    {
        $race = Race::find($raceId);

        if ($race === null) {
            return null;
        }

        // Determine distance category from meters if not set
        $distanceCategory = $race->distance_category;
        if ($distanceCategory === null && $race->distance_meters !== null) {
            $distanceCategory = RaceDistance::fromMeters($race->distance_meters)->value;
        }

        // Get the RaceDistance enum for stamina calculations
        $distance = null;
        if ($distanceCategory !== null && is_string($distanceCategory)) {
            $distance = RaceDistance::tryFrom($distanceCategory);
        }

        // Calculate stamina requirements for all running styles
        $staminaRequirements = [];
        if ($distance !== null) {
            foreach (RunningStyle::cases() as $style) {
                $staminaRequirements[$style->value] = $this->mechanicsEngine->calculateStaminaRequirement(
                    $distance,
                    $style,
                    [] // No recovery skills for base requirements
                );
            }
        }

        return [
            'id' => $race->id,
            'race_name' => $race->race_name,
            'race_internal_id' => $race->race_internal_id,
            'distance_category' => $distanceCategory,
            'distance_meters' => $race->distance_meters,
            'surface' => $race->surface ?? $race->track_type,
            'track_type' => $race->track_type,
            'race_grade' => $race->race_grade,
            'competition_level' => $this->mapGradeToCompetitionLevel($race->race_grade),
            'turn_number' => $race->turn_number,
            'career_phase' => $race->career_phase,
            'weather' => $race->weather,
            'track_condition' => $race->track_condition,
            'field_size' => $race->field_size,
            'is_ura_finale_race' => $race->is_ura_finale_race,
            'ura_finale_stage' => $race->ura_finale_stage,
            'is_unity_cup_match' => $race->is_unity_cup_match,
            'stamina_requirements' => $staminaRequirements,
            'optimal_styles' => $distance?->optimalStyles() ?? [],
            'strategic_importance' => $race->strategic_importance,
        ];
    }

    /**
     * Map race grade to competition level description.
     *
     * @param  string|null  $grade  Race grade (G1, G2, G3, OP, etc.)
     * @return string Competition level description
     */
    protected function mapGradeToCompetitionLevel(?string $grade): string
    {
        if ($grade === null) {
            return 'Unknown';
        }

        return match (strtoupper($grade)) {
            'G1' => 'Grade 1 (Highest)',
            'G2' => 'Grade 2 (High)',
            'G3' => 'Grade 3 (Moderate)',
            'OP' => 'Open (Standard)',
            'PRE-OP', 'PREOP' => 'Pre-Open (Entry)',
            'DEBUT' => 'Debut (Beginner)',
            default => $grade,
        };
    }
}
