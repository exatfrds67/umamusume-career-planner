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

        /**
         * @var array{
         *     base_gains?: array<string, mixed>,
         *     stat_bonus?: array<string, mixed>,
         *     growth_rate_multiplier?: float,
         *     mood_multiplier?: float,
         *     training_effect?: float,
         *     support_card_presence_multiplier?: float,
         *     friendship_multiplier?: float,
         *     facility_bonus?: float,
         *     total_multiplier?: float,
         *     per_training_cap?: string,
         *     friendship_status?: array<string, mixed>
         * } $breakdown
         */
        $breakdown = \is_array($resource['breakdown'] ?? null) ? $resource['breakdown'] : [];

        // Extract friendship training status
        $friendshipStatus = \is_array($breakdown['friendship_status'] ?? null) ? $breakdown['friendship_status'] : [
            'is_active' => false,
            'friendship_card_count' => 0,
            'cards_at_threshold' => [],
            'threshold' => 80,
        ];
        $cardsAtThreshold = \is_array($friendshipStatus['cards_at_threshold'] ?? null) ? $friendshipStatus['cards_at_threshold'] : [];
        $cardsNeedingBond = \is_array($friendshipStatus['cards_needing_bond'] ?? null) ? $friendshipStatus['cards_needing_bond'] : [];

        // Extract current Wit stat value for adequacy calculation
        $currentStats = \is_array($resource['current_stats'] ?? null) ? $resource['current_stats'] : [];
        $witValue = \is_int($currentStats['wit'] ?? null) ? $currentStats['wit'] : 0;

        return [
            'training_type' => $resource['training_type'] ?? null,
            'stat_gains' => $resource['stat_gains'] ?? [],
            'energy_cost' => $resource['energy_cost'] ?? 0,
            'failure_risk' => $resource['failure_risk'] ?? 0.0,
            'total_bonus' => $resource['total_bonus'] ?? 0.0,
            'wit_adequacy' => $this->getWitAdequacy($witValue),
            'friendship_training' => [
                'is_active' => $friendshipStatus['is_active'] ?? false,
                'multiplier_base' => 1.2, // 20% bonus when active
                'status_text' => $this->getFriendshipStatusText($friendshipStatus),
                'cards_at_threshold' => $cardsAtThreshold,
                'total_cards_at_threshold' => count($cardsAtThreshold),
                'cards_needing_bond' => $cardsNeedingBond,
                'estimated_turns_until_active' => $friendshipStatus['estimated_turns_until_active'] ?? null,
            ],
            'breakdown' => [
                'base_gains' => \is_array($breakdown['base_gains'] ?? null) ? $breakdown['base_gains'] : [],
                'stat_bonus' => \is_array($breakdown['stat_bonus'] ?? null) ? $breakdown['stat_bonus'] : [],
                'growth_rate_multiplier' => \is_float($breakdown['growth_rate_multiplier'] ?? null) ? $breakdown['growth_rate_multiplier'] : 1.0,
                'mood_multiplier' => \is_float($breakdown['mood_multiplier'] ?? null) ? $breakdown['mood_multiplier'] : 1.0,
                'training_effect' => \is_float($breakdown['training_effect'] ?? null) ? $breakdown['training_effect'] : 0.0,
                'support_card_presence_multiplier' => \is_float($breakdown['support_card_presence_multiplier'] ?? null) ? $breakdown['support_card_presence_multiplier'] : 1.0,
                'friendship_multiplier' => \is_float($breakdown['friendship_multiplier'] ?? null) ? $breakdown['friendship_multiplier'] : 1.0,
                'facility_bonus' => \is_float($breakdown['facility_bonus'] ?? null) ? $breakdown['facility_bonus'] : 0.0,
                'total_multiplier' => \is_float($breakdown['total_multiplier'] ?? null) ? $breakdown['total_multiplier'] : 1.0,
                'per_training_cap' => \is_string($breakdown['per_training_cap'] ?? null) ? $breakdown['per_training_cap'] : 'Applied: +100 max (+50 if stat > 1200)',
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
     * Generate Wit-based skill activation adequacy data.
     *
     * Uses the formula: max(100 - 9000 / BaseWit, 20%)
     *
     * @return array{wit_value: int, activation_chance: float, status: string, status_text: string, threshold_met: bool}
     */
    private function getWitAdequacy(int $witValue): array
    {
        if ($witValue <= 0) {
            return [
                'wit_value' => 0,
                'activation_chance' => 0.0,
                'status' => 'unknown',
                'status_text' => '❓ Wit unknown — cannot calculate skill activation chance',
                'threshold_met' => false,
            ];
        }

        // Formula: max(100 - 9000 / BaseWit, 20)
        $activationChance = max(100 - (9000 / $witValue), 20.0);
        $activationChance = round($activationChance, 1);

        if ($witValue >= 600) {
            $status = 'excellent';
            $statusText = "✅ Wit {$witValue} — excellent ({$activationChance}% skill activation)";
        } elseif ($witValue >= 400) {
            $status = 'reliable';
            $statusText = "✅ Wit {$witValue} — reliable ({$activationChance}% skill activation)";
        } elseif ($witValue >= 300) {
            $status = 'marginal';
            $statusText = "⚠ Wit {$witValue} — marginal ({$activationChance}% activation; consider raising Wit)";
        } else {
            $statusText = "❌ Wit {$witValue} — critical: skills frequently misfire ({$activationChance}% activation; floor is 20%)";
            $status = 'critical';
        }

        return [
            'wit_value' => $witValue,
            'activation_chance' => $activationChance,
            'status' => $status,
            'status_text' => $statusText,
            'threshold_met' => $witValue >= 400,
        ];
    }

    /**
     * Generate human-readable friendship training status text
     *
     * @param  array<string, mixed>  $status
     */
    private function getFriendshipStatusText(array $status): string
    {
        $cardsAtThreshold = \is_array($status['cards_at_threshold'] ?? null) ? $status['cards_at_threshold'] : [];
        $cardsNeedingBond = \is_array($status['cards_needing_bond'] ?? null) ? $status['cards_needing_bond'] : [];

        if ($status['is_active'] ?? false) {
            $count = count($cardsAtThreshold);

            return "✅ Friendship Training ACTIVE ({$count} cards at bond ≥80)";
        }

        $current = count($cardsAtThreshold);
        $needing = count($cardsNeedingBond);
        $estimatedValue = $status['estimated_turns_until_active'] ?? 'unknown';
        $estimated = \is_scalar($estimatedValue) ? (string) $estimatedValue : 'unknown';

        if ($current > 0) {
            return "⏳ Friendship Training: {$current}/3 cards ready (est. {$estimated} turns)";
        }

        return "❌ Friendship Training: {$needing} cards need bond development";
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
