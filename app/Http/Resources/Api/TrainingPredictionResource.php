<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainingPredictionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array<string, mixed> $data */
        $data = is_array($this->resource) ? $this->resource : [];

        /** @var array<string, mixed> $breakdown */
        $breakdown = isset($data['breakdown']) && is_array($data['breakdown']) ? $data['breakdown'] : [];

        $supportCardBonus = $breakdown['support_card_bonus'] ?? 0.0;
        $friendshipMultiplier = $breakdown['friendship_multiplier'] ?? 0.0;
        $facilityBonus = $breakdown['facility_bonus'] ?? 0.0;
        $growthRateBonus = $breakdown['growth_rate_bonus'] ?? 0.0;
        $totalMultiplier = $breakdown['total_multiplier'] ?? 1.0;

        return [
            'training_type' => $data['training_type'] ?? null,
            'stat_gains' => $data['stat_gains'] ?? [],
            'energy_cost' => $data['energy_cost'] ?? 0,
            'failure_risk' => $data['failure_risk'] ?? 0.0,
            'total_bonus' => $data['total_bonus'] ?? 0.0,
            'breakdown' => [
                'base_gains' => $breakdown['base_gains'] ?? [],
                'support_card_bonus' => is_numeric($supportCardBonus) ? (float) $supportCardBonus : 0.0,
                'friendship_multiplier' => is_numeric($friendshipMultiplier) ? (float) $friendshipMultiplier : 0.0,
                'facility_bonus' => is_numeric($facilityBonus) ? (float) $facilityBonus : 0.0,
                'growth_rate_bonus' => is_numeric($growthRateBonus) ? (float) $growthRateBonus : 0.0,
                'total_multiplier' => is_numeric($totalMultiplier) ? (float) $totalMultiplier : 1.0,
            ],
            'scenario_specific' => $data['scenario_specific'] ?? [],
            'mcp_optimization' => $this->when(
                isset($data['mcp_optimization']),
                $data['mcp_optimization'] ?? null
            ),
            'recommendation' => $this->when(
                isset($data['recommendation']),
                $data['recommendation'] ?? null
            ),
            'cached' => $data['cached'] ?? false,
            'cache_ttl' => $data['cache_ttl'] ?? null,
            'processing_time_ms' => $data['processing_time_ms'] ?? null,
            'timestamp' => $data['timestamp'] ?? now()->toIso8601String(),
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
            'meta' => [
                'version' => '1.0',
                'api_endpoint' => $request->url(),
            ],
        ];
    }
}
