<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Race Condition Service
 *
 * Handles weather and track condition effects on race performance.
 * Implements verified game mechanics from research report Section 6.1.
 *
 * **Phase 5: Weather/Track Conditions Implementation**
 * **Validates: Game Mechanics Research Report Section 6.1**
 */
class RaceConditionService
{
    /**
     * Weather types
     */
    public const WEATHER_SUNNY = 'sunny';

    public const WEATHER_CLOUDY = 'cloudy';

    public const WEATHER_RAINY = 'rainy';

    public const WEATHER_SNOWY = 'snowy';

    /**
     * Track conditions
     */
    public const CONDITION_FIRM = 'firm';

    public const CONDITION_GOOD = 'good';

    public const CONDITION_SOFT = 'soft';

    public const CONDITION_HEAVY = 'heavy';

    /**
     * Surface types
     */
    public const SURFACE_TURF = 'turf';

    public const SURFACE_DIRT = 'dirt';

    /**
     * Verified track condition penalties from research report Section 6.1
     *
     * @var array<string, array<string, array<string, int|float>>>
     */
    protected const CONDITION_PENALTIES = [
        self::CONDITION_FIRM => [
            self::SURFACE_TURF => [
                'power_penalty' => 0,
                'speed_penalty' => 0,
                'stamina_drain' => 0.0,
            ],
            self::SURFACE_DIRT => [
                'power_penalty' => 0,
                'speed_penalty' => 0,
                'stamina_drain' => 0.0,
            ],
        ],
        self::CONDITION_GOOD => [
            self::SURFACE_TURF => [
                'power_penalty' => -50,
                'speed_penalty' => 0,
                'stamina_drain' => 0.0,
            ],
            self::SURFACE_DIRT => [
                'power_penalty' => -50,
                'speed_penalty' => 0,
                'stamina_drain' => 0.0,
            ],
        ],
        self::CONDITION_SOFT => [
            self::SURFACE_TURF => [
                'power_penalty' => -50,
                'speed_penalty' => 0,
                'stamina_drain' => 2.0, // +2%/sec
            ],
            self::SURFACE_DIRT => [
                'power_penalty' => -100,
                'speed_penalty' => 0,
                'stamina_drain' => 2.0, // +2%/sec
            ],
        ],
        self::CONDITION_HEAVY => [
            self::SURFACE_TURF => [
                'power_penalty' => -50,
                'speed_penalty' => -50,
                'stamina_drain' => 2.0, // +2%/sec
            ],
            self::SURFACE_DIRT => [
                'power_penalty' => -100,
                'speed_penalty' => -50,
                'stamina_drain' => 2.0, // +2%/sec
            ],
        ],
    ];

    /**
     * Calculate power penalty for given track condition and surface
     *
     * @param  string  $trackCondition  Track condition (firm, good, soft, heavy)
     * @param  string  $surface  Surface type (turf, dirt)
     * @return int Power penalty (negative value)
     */
    public function calculatePowerPenalty(string $trackCondition, string $surface): int
    {
        $penalties = self::CONDITION_PENALTIES[$trackCondition][$surface] ?? null;

        if ($penalties === null) {
            return 0;
        }

        return (int) ($penalties['power_penalty'] ?? 0);
    }

    /**
     * Calculate speed penalty for given track condition and surface
     *
     * @param  string  $trackCondition  Track condition (firm, good, soft, heavy)
     * @param  string  $surface  Surface type (turf, dirt)
     * @return int Speed penalty (negative value)
     */
    public function calculateSpeedPenalty(string $trackCondition, string $surface): int
    {
        $penalties = self::CONDITION_PENALTIES[$trackCondition][$surface] ?? null;

        if ($penalties === null) {
            return 0;
        }

        return (int) ($penalties['speed_penalty'] ?? 0);
    }

    /**
     * Calculate stamina drain modifier for given track condition and surface
     *
     * @param  string  $trackCondition  Track condition (firm, good, soft, heavy)
     * @param  string  $surface  Surface type (turf, dirt)
     * @return float Stamina drain modifier (percentage per second)
     */
    public function calculateStaminaDrain(string $trackCondition, string $surface): float
    {
        $penalties = self::CONDITION_PENALTIES[$trackCondition][$surface] ?? null;

        if ($penalties === null) {
            return 0.0;
        }

        return (float) ($penalties['stamina_drain'] ?? 0.0);
    }

    /**
     * Apply track condition penalties to character stats
     *
     * @param  array<string, int>  $stats  Character stats (speed, stamina, power, guts, wit)
     * @param  string  $trackCondition  Track condition
     * @param  string  $surface  Surface type
     * @return array<string, int> Modified stats with penalties applied
     */
    public function applyConditionPenalties(array $stats, string $trackCondition, string $surface): array
    {
        $modifiedStats = $stats;

        // Apply power penalty
        $powerPenalty = $this->calculatePowerPenalty($trackCondition, $surface);
        if ($powerPenalty !== 0) {
            $modifiedStats['power'] = max(0, ($stats['power'] ?? 0) + $powerPenalty);
        }

        // Apply speed penalty
        $speedPenalty = $this->calculateSpeedPenalty($trackCondition, $surface);
        if ($speedPenalty !== 0) {
            $modifiedStats['speed'] = max(0, ($stats['speed'] ?? 0) + $speedPenalty);
        }

        return $modifiedStats;
    }

    /**
     * Check if track condition is wet (good, soft, or heavy)
     *
     * @param  string  $trackCondition  Track condition
     * @return bool True if wet condition
     */
    public function isWetCondition(string $trackCondition): bool
    {
        return in_array($trackCondition, [
            self::CONDITION_GOOD,
            self::CONDITION_SOFT,
            self::CONDITION_HEAVY,
        ], true);
    }

    /**
     * Get condition severity level (0-3)
     *
     * @param  string  $trackCondition  Track condition
     * @return int Severity level (0=firm, 1=good, 2=soft, 3=heavy)
     */
    public function getConditionSeverity(string $trackCondition): int
    {
        return match ($trackCondition) {
            self::CONDITION_FIRM => 0,
            self::CONDITION_GOOD => 1,
            self::CONDITION_SOFT => 2,
            self::CONDITION_HEAVY => 3,
            default => 0,
        };
    }

    /**
     * Get human-readable condition impact description
     *
     * @param  string  $trackCondition  Track condition
     * @param  string  $surface  Surface type
     * @return string Description of condition impact
     */
    public function getConditionImpactDescription(string $trackCondition, string $surface): string
    {
        $powerPenalty = $this->calculatePowerPenalty($trackCondition, $surface);
        $speedPenalty = $this->calculateSpeedPenalty($trackCondition, $surface);
        $staminaDrain = $this->calculateStaminaDrain($trackCondition, $surface);

        if ($powerPenalty === 0 && $speedPenalty === 0 && $staminaDrain === 0.0) {
            return 'Optimal conditions - no penalties';
        }

        $impacts = [];

        if ($powerPenalty !== 0) {
            $impacts[] = "Power {$powerPenalty}";
        }

        if ($speedPenalty !== 0) {
            $impacts[] = "Speed {$speedPenalty}";
        }

        if ($staminaDrain > 0.0) {
            $impacts[] = "Stamina drain +{$staminaDrain}%/sec";
        }

        return implode(', ', $impacts);
    }

    /**
     * Calculate total performance impact score
     *
     * Combines all penalties into a single impact score (0-100)
     * Lower score = worse conditions
     *
     * @param  string  $trackCondition  Track condition
     * @param  string  $surface  Surface type
     * @return float Performance impact score (0-100)
     */
    public function calculatePerformanceImpact(string $trackCondition, string $surface): float
    {
        $powerPenalty = abs($this->calculatePowerPenalty($trackCondition, $surface));
        $speedPenalty = abs($this->calculateSpeedPenalty($trackCondition, $surface));
        $staminaDrain = $this->calculateStaminaDrain($trackCondition, $surface);

        // Calculate impact score
        // Base score: 100 (optimal)
        // Power penalty: -0.04 per point (max -100 = -4.0)
        // Speed penalty: -0.04 per point (max -50 = -2.0)
        // Stamina drain: -1.0 per % (max 2% = -2.0)

        $score = 100.0;
        $score -= ($powerPenalty * 0.04);
        $score -= ($speedPenalty * 0.04);
        $score -= ($staminaDrain * 1.0);

        return max(0.0, min(100.0, $score));
    }

    /**
     * Get recommended skills for weather/track conditions
     *
     * @param  string|null  $weather  Weather type
     * @param  string  $trackCondition  Track condition
     * @return array<int, string> Recommended skill names
     */
    public function getRecommendedSkills(?string $weather, string $trackCondition): array
    {
        $skills = [];

        // Weather-specific skills
        if ($weather !== null) {
            $skills[] = match ($weather) {
                self::WEATHER_SUNNY => 'Sunny Days ◯',
                self::WEATHER_CLOUDY => 'Cloudy Days ◯',
                self::WEATHER_RAINY => 'Rainy Days ◯',
                self::WEATHER_SNOWY => 'Snowy Days ◯',
                default => null,
            };
        }

        // Condition-specific skills
        if ($trackCondition === self::CONDITION_FIRM) {
            $skills[] = 'Firm Conditions ◯';
        } elseif ($this->isWetCondition($trackCondition)) {
            $skills[] = 'Wet Conditions ◯';
        }

        return array_filter($skills, fn ($skill) => $skill !== null);
    }

    /**
     * Validate track condition value
     *
     * @param  string  $trackCondition  Track condition to validate
     * @return bool True if valid
     */
    public function isValidTrackCondition(string $trackCondition): bool
    {
        return in_array($trackCondition, [
            self::CONDITION_FIRM,
            self::CONDITION_GOOD,
            self::CONDITION_SOFT,
            self::CONDITION_HEAVY,
        ], true);
    }

    /**
     * Validate surface type
     *
     * @param  string  $surface  Surface type to validate
     * @return bool True if valid
     */
    public function isValidSurface(string $surface): bool
    {
        return in_array($surface, [
            self::SURFACE_TURF,
            self::SURFACE_DIRT,
        ], true);
    }

    /**
     * Validate weather type
     *
     * @param  string  $weather  Weather type to validate
     * @return bool True if valid
     */
    public function isValidWeather(string $weather): bool
    {
        return in_array($weather, [
            self::WEATHER_SUNNY,
            self::WEATHER_CLOUDY,
            self::WEATHER_RAINY,
            self::WEATHER_SNOWY,
        ], true);
    }
}
