<?php

declare(strict_types=1);

namespace App\Services\OCR;

use App\Models\Character;
use App\Models\Race;
use App\Models\Skill;
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
        $data = $extractedData['data'] ?? [];

        $transformed = [
            'stats' => $this->normalizeStats($data['stats'] ?? []),
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
        $data = $extractedData['data'] ?? [];

        $transformed = [
            'training_type' => $data['training_type'] ?? null,
            'stat_gains' => $this->normalizeStats($data['stat_gains'] ?? []),
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
        $data = $extractedData['data'] ?? [];

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
        $data = $extractedData['data'] ?? [];

        $transformed = [
            'total_sp' => $data['total_sp'] ?? null,
            'skills' => $this->normalizeSkills($data['skills'] ?? []),
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
    public function prepareTrainingSessionCreate(int $careerId, array $transformedData): array
    {
        return [
            'career_id' => $careerId,
            'turn_number' => $transformedData['current_turn'] ?? null,
            'training_type' => $transformedData['training_type'],
            'stat_gains' => \json_encode($transformedData['stat_gains']),
            'energy_cost' => $transformedData['energy_cost'],
            'skill_hints' => $transformedData['has_skill_hint'] ? \json_encode(['detected' => true]) : null,
            'participants' => $transformedData['has_friendship'] ? \json_encode(['friendship' => true]) : null,
            'spirit_burst' => $transformedData['has_spirit_burst'],
        ];
    }

    /**
     * Prepare race creation data
     *
     * @param  array<string, mixed>  $transformedData
     * @return array<string, mixed>
     */
    public function prepareRaceCreate(int $careerId, array $transformedData): array
    {
        return [
            'career_id' => $careerId,
            'race_name' => $transformedData['race_name'],
            'race_grade' => $transformedData['race_grade'],
            'distance' => $transformedData['distance'],
            'surface' => $transformedData['surface'],
            'final_position' => $transformedData['position'],
            'performance' => \json_encode([
                'outcome' => $transformedData['outcome'],
                'fans_gained' => $transformedData['fans_gained'],
                'skill_points_gained' => $transformedData['skill_points_gained'],
                'distance_category' => $transformedData['distance_category'],
            ]),
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
        return \array_map(function (array $skill) {
            return [
                'name' => $skill['name'] ?? null,
                'sp_cost' => $skill['sp_cost'] ?? null,
                'hint_level' => $skill['hint_level'] ?? 0,
                'is_acquired' => $skill['is_acquired'] ?? false,
                'skill_type' => $skill['skill_type'] ?? 'unknown',
            ];
        }, $skills);
    }
}
