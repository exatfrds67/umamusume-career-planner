<?php

declare(strict_types=1);

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Training Prediction API Resource
 *
 * Formats training prediction data consistently for API responses
 */
class TrainingPredictionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array<string, mixed> $resource */
        $resource = $this->resource;

        return [
            'training_type' => $resource['training_type'] ?? null,
            'stat_gains' => $resource['stat_gains'] ?? [],
            'energy_cost' => $resource['energy_cost'] ?? 0,
            'failure_risk' => $resource['failure_risk'] ?? 0.0,
            'total_bonus' => $resource['total_bonus'] ?? 0.0,
            'breakdown' => [
                'base_gains' => $resource['breakdown']['base_gains'] ?? [],
                'stat_bonus' => $resource['breakdown']['stat_bonus'] ?? [],
                'growth_rate_multiplier' => $resource['breakdown']['growth_rate_multiplier'] ?? 1.0,
                'mood_multiplier' => $resource['breakdown']['mood_multiplier'] ?? 1.0,
                'training_effect' => $resource['breakdown']['training_effect'] ?? 0.0,
                'support_card_presence_multiplier' => $resource['breakdown']['support_card_presence_multiplier'] ?? 1.0,
                'friendship_multiplier' => $resource['breakdown']['friendship_multiplier'] ?? 1.0,
                'facility_bonus' => $resource['breakdown']['facility_bonus'] ?? 0.0,
                'total_multiplier' => $resource['breakdown']['total_multiplier'] ?? 1.0,
                'per_training_cap' => $resource['breakdown']['per_training_cap'] ?? 'Applied: +100 max (+50 if stat > 1200)',
            ],
            'scenario_specific' => $resource['scenario_specific'] ?? [],
            'mcp_optimization' => $this->when(
                isset($resource['mcp_optimization']),
                $resource['mcp_optimization'] ?? null
            ),
            'recommendation' => $this->when(
                isset($resource['recommendation']),
                $resource['recommendation'] ?? null
            ),
            'meta' => [
                'cached' => $resource['cached'] ?? false,
                'cache_ttl' => $resource['cache_ttl'] ?? 300,
                'processing_time_ms' => $resource['processing_time_ms'] ?? 0,
                'timestamp' => $resource['timestamp'] ?? now()->toIso8601String(),
            ],
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'success' => true,
            'message' => 'Training prediction generated successfully',
        ];
    }
}
