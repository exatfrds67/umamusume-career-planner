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
        return [
            'training_type' => $this->resource['training_type'] ?? null,
            'stat_gains' => $this->resource['stat_gains'] ?? [],
            'energy_cost' => $this->resource['energy_cost'] ?? 0,
            'failure_risk' => $this->resource['failure_risk'] ?? 0.0,
            'total_bonus' => $this->resource['total_bonus'] ?? 0.0,
            'breakdown' => [
                'base_gains' => $this->resource['breakdown']['base_gains'] ?? [],
                'support_card_bonus' => (float) ($this->resource['breakdown']['support_card_bonus'] ?? 0.0),
                'friendship_multiplier' => (float) ($this->resource['breakdown']['friendship_multiplier'] ?? 0.0),
                'facility_bonus' => (float) ($this->resource['breakdown']['facility_bonus'] ?? 0.0),
                'growth_rate_bonus' => (float) ($this->resource['breakdown']['growth_rate_bonus'] ?? 0.0),
                'total_multiplier' => (float) ($this->resource['breakdown']['total_multiplier'] ?? 1.0),
            ],
            'scenario_specific' => $this->resource['scenario_specific'] ?? [],
            'mcp_optimization' => $this->when(
                isset($this->resource['mcp_optimization']),
                $this->resource['mcp_optimization'] ?? null
            ),
            'recommendation' => $this->when(
                isset($this->resource['recommendation']),
                $this->resource['recommendation'] ?? null
            ),
            'cached' => $this->resource['cached'] ?? false,
            'cache_ttl' => $this->resource['cache_ttl'] ?? null,
            'processing_time_ms' => $this->resource['processing_time_ms'] ?? null,
            'timestamp' => $this->resource['timestamp'] ?? now()->toIso8601String(),
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
