<?php

declare(strict_types=1);

namespace App\ValueObjects;

/**
 * Represents the synergy analysis score for a single layer.
 *
 * Each layer (stat, skill phase, running style, inheritance, team, environmental, debuff)
 * produces one of these with a 0–100 score, weight, issues, recommendations, and optional breakdown data.
 */
final readonly class SynergyLayerScore
{
    /**
     * @param  string  $layer  Layer identifier (e.g. 'stat', 'skill_phase', 'running_style')
     * @param  float  $score  Layer score (0–100)
     * @param  float  $weight  Weight multiplier for overall score calculation
     * @param  array<int, string>  $issues  Problems found in this layer
     * @param  array<int, string>  $recommendations  Actionable suggestions
     * @param  array<string, mixed>  $breakdown  Optional structured metadata for UI/details
     */
    public function __construct(
        public string $layer,
        public float $score,
        public float $weight,
        public array $issues = [],
        public array $recommendations = [],
        public array $breakdown = [],
    ) {}

    /**
     * @return array{layer: string, score: float, weight: float, issues: array<int, string>, recommendations: array<int, string>, breakdown: array<string, mixed>}
     */
    public function toArray(): array
    {
        return [
            'layer' => $this->layer,
            'score' => round($this->score, 1),
            'weight' => $this->weight,
            'issues' => $this->issues,
            'recommendations' => $this->recommendations,
            'breakdown' => $this->breakdown,
        ];
    }

    /**
     * @param  array{layer: string, score: float, weight: float, issues?: array<int, string>, recommendations?: array<int, string>, breakdown?: array<string, mixed>}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            layer: $data['layer'],
            score: (float) $data['score'],
            weight: (float) $data['weight'],
            issues: $data['issues'] ?? [],
            recommendations: $data['recommendations'] ?? [],
            breakdown: $data['breakdown'] ?? [],
        );
    }

    /**
     * Weighted contribution to overall score.
     */
    public function weightedScore(): float
    {
        return $this->score * $this->weight;
    }
}
