<?php

declare(strict_types=1);

namespace App\Services\OCR;

use Illuminate\Support\Facades\Log;

/**
 * Data Transformation Service
 *
 * Transforms extracted OCR data into database model structures.
 * Handles data normalization and preparation for database insertion.
 *
 * Requirements: Task 5.1.4, Requirement 23.4
 */
class DataTransformationService
{
    /**
     * Transform character stats data for database insertion
     *
     * @param  array<string, mixed>  $extractedData
     * @return array<string, mixed>
     */
    public function transformCharacterStats(array $extractedData): array
    {
        /** @var array<string, mixed> $data */
        $data = is_array($extractedData['data'] ?? null) ? $extractedData['data'] : [];

        /** @var array<string, int|null> $stats */
        $stats = is_array($data['stats'] ?? null) ? $data['stats'] : [];

        $transformed = [
            'stats' => $this->normalizeStats($stats),
            'energy_level' => $data['energy_level'] ?? null,
            'mood_status' => $data['mood_status'] ?? 'normal',
            'current_turn' => $data['current_turn'] ?? null,
            'total_turns' => $data['total_turns'] ?? null,
            'character_name' => $data['character_name'] ?? null,
            'extracted_at' => $extractedData['extracted_at'] ?? now()->toIso8601String(),
            'confidence' => $extractedData['confidence'] ?? 0.0,
        ];

        Log::info('[DataTransformationService] Transformed character stats', [
            'stats_count' => \count(\array_filter($transformed['stats'])),
            'confidence' => $transformed['confidence'],
        ]);

        return $transformed;
    }

    /**
     * Transform training session data for database insertion
     *
     * @param  array<string, mixed>  $extractedData
     * @return array<string, mixed>
     */
    public function transformTrainingSession(array $extractedData): array
    {
        /** @var array<string, mixed> $data */
        $data = is_array($extractedData['data'] ?? null) ? $extractedData['data'] : [];

        /** @var array<string, int|null> $statGains */
        $statGains = is_array($data['stat_gains'] ?? null) ? $data['stat_gains'] : [];

        $transformed = [
            'training_type' => $data['training_type'] ?? null,
            'stat_gains' => $this->normalizeStats($statGains),
            'energy_cost' => $data['energy_cost'] ?? null,
            'has_skill_hint' => $data['has_skill_hint'] ?? false,
            'has_friendship' => $data['has_friendship'] ?? false,
            'has_spirit_burst' => $data['has_spirit_burst'] ?? false,
            'extracted_at' => $extractedData['extracted_at'] ?? now()->toIso8601String(),
            'confidence' => $extractedData['confidence'] ?? 0.0,
        ];

        Log::info('[DataTransformationService] Transformed training session', [
            'training_type' => $transformed['training_type'],
            'stat_gains_count' => \count(\array_filter($transformed['stat_gains'])),
            'confidence' => $transformed['confidence'],
        ]);

        return $transformed;
    }

    /**
     * Transform race result data for database insertion
     *
     * @param  array<string, mixed>  $extractedData
     * @return array<string, mixed>
     */
    public function transformRaceResult(array $extractedData): array
    {
        /** @var array<string, mixed> $data */
        $data = is_array($extractedData['data'] ?? null) ? $extractedData['data'] : [];

        $transformed = [
            'race_name' => $data['race_name'] ?? null,
            'race_grade' => $data['race_grade'] ?? null,
            'position' => $data['position'] ?? null,
            'distance' => $data['distance'] ?? null,
            'distance_category' => $data['distance_category'] ?? null,
            'surface' => $data['surface'] ?? null,
            'fans_gained' => $data['fans_gained'] ?? 0,
            'skill_points_gained' => $data['skill_points_gained'] ?? 0,
            'outcome' => $data['outcome'] ?? null,
            'extracted_at' => $extractedData['extracted_at'] ?? now()->toIso8601String(),
            'confidence' => $extractedData['confidence'] ?? 0.0,
        ];

        Log::info('[DataTransformationService] Transformed race result', [
            'race_name' => $transformed['race_name'],
            'position' => $transformed['position'],
            'confidence' => $transformed['confidence'],
        ]);

        return $transformed;
    }

    /**
     * Transform skill list data for database insertion
     *
     * @param  array<string, mixed>  $extractedData
     * @return array<string, mixed>
     */
    public function transformSkillList(array $extractedData): array
    {
        /** @var array<string, mixed> $data */
        $data = is_array($extractedData['data'] ?? null) ? $extractedData['data'] : [];

        /** @var array<int, array<string, mixed>> $skills */
        $skills = is_array($data['skills'] ?? null) ? $data['skills'] : [];

        $transformed = [
            'total_sp' => $data['total_sp'] ?? null,
            'skills' => $this->normalizeSkills($skills),
            'skill_count' => $data['skill_count'] ?? 0,
            'extracted_at' => $extractedData['extracted_at'] ?? now()->toIso8601String(),
            'confidence' => $extractedData['confidence'] ?? 0.0,
        ];

        Log::info('[DataTransformationService] Transformed skill list', [
            'total_sp' => $transformed['total_sp'],
            'skill_count' => $transformed['skill_count'],
            'confidence' => $transformed['confidence'],
        ]);

        return $transformed;
    }

    /**
     * Prepare character update data from extracted stats
     *
     * @param  array<string, mixed>  $transformedData
     * @return array<string, mixed>
     */
    public function prepareCharacterUpdate(array $transformedData): array
    {
        $updateData = [];

        // Update stats if present (keep as array - model handles JSON encoding)
        if (! empty($transformedData['stats'])) {
            $updateData['current_stats'] = $transformedData['stats'];
        }

        // Update energy level if present
        if ($transformedData['energy_level'] !== null) {
            $updateData['energy_level'] = $transformedData['energy_level'];
        }

        // Update mood status if present
        if ($transformedData['mood_status'] !== null) {
            $updateData['mood_status'] = $transformedData['mood_status'];
        }

        return $updateData;
    }

    /**
     * Prepare training session creation data
     *
     * @param  array<string, mixed>  $transformedData
     * @return array<string, mixed>
     */
    public function prepareTrainingSessionCreate(array $transformedData, int $careerId): array
    {
        /** @var array<string, int|null> $statGains */
        $statGains = is_array($transformedData['stat_gains'] ?? null) ? $transformedData['stat_gains'] : [];
        $totalStatPoints = array_sum(array_filter($statGains, fn ($v) => $v !== null));
        $energyCost = is_int($transformedData['energy_cost'] ?? null) ? $transformedData['energy_cost'] : 0;

        return [
            'career_id' => $careerId,
            'character_id' => 1, // Will be updated by service if needed
            'turn_number' => $transformedData['current_turn'] ?? 1,
            'career_phase' => 'junior', // Default phase
            'training_type' => $transformedData['training_type'],
            'speed_gain' => $statGains['speed'] ?? 0,
            'stamina_gain' => $statGains['stamina'] ?? 0,
            'power_gain' => $statGains['power'] ?? 0,
            'guts_gain' => $statGains['guts'] ?? 0,
            'wit_gain' => $statGains['wit'] ?? 0,
            'sp_gain' => $statGains['sp'] ?? 0,
            'energy_cost' => $energyCost,
            'energy_before' => 100, // Default value
            'energy_after' => max(0, 100 - $energyCost),
            'total_stat_points_gained' => $totalStatPoints,
            'skill_hints_obtained' => ($transformedData['has_skill_hint'] ?? false) ? ['detected' => true] : null,
            'friendship_training' => $transformedData['has_friendship'] ?? false,
            'training_metadata' => [
                'spirit_burst' => $transformedData['has_spirit_burst'],
            ],
        ];
    }

    /**
     * Prepare race creation data
     *
     * @param  array<string, mixed>  $transformedData
     * @return array<string, mixed>
     */
    public function prepareRaceCreate(array $transformedData, int $careerId): array
    {
        return [
            'career_id' => $careerId,
            'character_id' => 1, // Will be updated by service
            'race_name' => $transformedData['race_name'],
            'race_grade' => $transformedData['race_grade'],
            'turn_number' => 1, // Default value
            'career_phase' => 'junior', // Default value
            'distance_category' => $transformedData['distance_category'],
            'distance_meters' => $transformedData['distance'],
            'surface' => $transformedData['surface'],
            'running_style' => 'insert', // Default value
            'field_size' => 18, // Default value
            'energy_level' => 100, // Default value
            'speed_at_race' => 0, // Default value
            'stamina_at_race' => 0, // Default value
            'power_at_race' => 0, // Default value
            'guts_at_race' => 0, // Default value
            'wit_at_race' => 0, // Default value
            'finish_position' => $transformedData['position'],
            'won_race' => $transformedData['position'] === 1,
            'race_result' => $transformedData['outcome'],
            'fans_gained' => $transformedData['fans_gained'],
            'sp_reward' => $transformedData['skill_points_gained'],
            'race_metadata' => [
                'outcome' => $transformedData['outcome'],
                'fans_gained' => $transformedData['fans_gained'],
                'skill_points_gained' => $transformedData['skill_points_gained'],
                'distance_category' => $transformedData['distance_category'],
            ],
        ];
    }

    /**
     * Normalize stats array to ensure all stats are present
     *
     * @param  array<string, int|null>  $stats
     * @return array<string, int|null>
     */
    private function normalizeStats(array $stats): array
    {
        $normalized = [
            'speed' => null,
            'stamina' => null,
            'power' => null,
            'guts' => null,
            'wit' => null,
        ];

        foreach ($stats as $stat => $value) {
            if (\array_key_exists($stat, $normalized)) {
                $normalized[$stat] = $value;
            }
        }

        return $normalized;
    }

    /**
     * Normalize skills array
     *
     * @param  array<int, array<string, mixed>>  $skills
     * @return array<int, array<string, mixed>>
     */
    private function normalizeSkills(array $skills): array
    {
        return \array_map(fn (array $skill) => [
            'name' => $skill['name'] ?? null,
            'sp_cost' => $skill['sp_cost'] ?? null,
            'hint_level' => $skill['hint_level'] ?? 0,
            'is_acquired' => $skill['is_acquired'] ?? false,
            'skill_type' => $skill['skill_type'] ?? 'passive',
        ], $skills);
    }
}
