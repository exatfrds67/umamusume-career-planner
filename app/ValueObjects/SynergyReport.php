<?php

declare(strict_types=1);

namespace App\ValueObjects;

use Carbon\Carbon;

/**
 * Complete synergy analysis report for a character build.
 *
 * Aggregates 7 layer scores into an overall synergy rating with tier classification.
 * Serializable to/from JSON for caching in the character's synergy_snapshot column.
 */
final readonly class SynergyReport
{
    /**
     * @param  float  $overallScore  Overall synergy score (0–100)
     * @param  string  $tier  Tier classification (S+, S, A, B, C)
     * @param  array<int, SynergyLayerScore>  $layers  Individual layer scores
     * @param  array<int, string>  $criticalIssues  High-priority problems across all layers
     * @param  Carbon  $computedAt  When this report was generated
     */
    public function __construct(
        public float $overallScore,
        public string $tier,
        public array $layers,
        public array $criticalIssues,
        public Carbon $computedAt,
    ) {}

    /**
     * Determine tier from an overall score.
     */
    public static function tierFromScore(float $score): string
    {
        return match (true) {
            $score >= 90 => 'S+',
            $score >= 80 => 'S',
            $score >= 65 => 'A',
            $score >= 50 => 'B',
            default => 'C',
        };
    }

    /**
     * Build a report from layer scores.
     *
     * @param  array<int, SynergyLayerScore>  $layers
     * @param  array<int, string>  $criticalIssues
     */
    public static function fromLayers(array $layers, array $criticalIssues = []): self
    {
        $totalWeight = array_sum(array_map(fn (SynergyLayerScore $l) => $l->weight, $layers));

        $overallScore = $totalWeight > 0
            ? array_sum(array_map(fn (SynergyLayerScore $l) => $l->weightedScore(), $layers)) / $totalWeight
            : 0.0;

        $overallScore = round(min(100, max(0, $overallScore)), 1);

        return new self(
            overallScore: $overallScore,
            tier: self::tierFromScore($overallScore),
            layers: $layers,
            criticalIssues: $criticalIssues,
            computedAt: Carbon::now(),
        );
    }

    /**
     * @return array{overall_score: float, tier: string, layers: array<int, array<string, mixed>>, critical_issues: array<int, string>, computed_at: string}
     */
    public function toArray(): array
    {
        return [
            'overall_score' => $this->overallScore,
            'tier' => $this->tier,
            'layers' => array_map(fn (SynergyLayerScore $l) => $l->toArray(), $this->layers),
            'critical_issues' => $this->criticalIssues,
            'computed_at' => $this->computedAt->toIso8601String(),
        ];
    }

    /**
     * @param  array{overall_score: float, tier: string, layers: array<int, array<string, mixed>>, critical_issues?: array<int, string>, computed_at: string}  $data
     */
    public static function fromArray(array $data): self
    {
        $layers = array_map(
            fn (array $l) => SynergyLayerScore::fromArray($l),
            $data['layers']
        );

        return new self(
            overallScore: (float) $data['overall_score'],
            tier: $data['tier'],
            layers: $layers,
            criticalIssues: $data['critical_issues'] ?? [],
            computedAt: Carbon::parse($data['computed_at']),
        );
    }
}
