<?php

declare(strict_types=1);

namespace App\ValueObjects;

/**
 * Race Strategy Value Object
 *
 * Represents a recommended race strategy with predicted outcomes.
 * Used for race planning and accuracy tracking.
 *
 * @see \App\Services\TrainingAdvisoryService
 * @see \App\Services\PredictionAccuracyTracker
 */
final readonly class RaceStrategy
{
    /**
     * Create new Race Strategy
     *
     * @param  int  $raceId  Race identifier
     * @param  string  $recommendedStyle  Recommended running style (escape, lead, pace, chase)
     * @param  string  $reasoning  Explanation for the recommendation
     * @param  float  $winProbability  Predicted win probability (0.0-1.0)
     * @param  array<string, string>  $readinessAssessment  Readiness check (stamina, speed, power, overall)
     * @param  array<string>  $risks  Potential risks or concerns
     * @param  array<string>  $preparationChecklist  Pre-race preparation items
     * @param  array<string, mixed>  $predictedOutcomes  Predicted race outcomes
     * @param  string  $modelVersion  AI model version used for prediction
     */
    public function __construct(
        public int $raceId,
        public string $recommendedStyle,
        public string $reasoning,
        public float $winProbability,
        public array $readinessAssessment,
        public array $risks = [],
        public array $preparationChecklist = [],
        public array $predictedOutcomes = [],
        public string $modelVersion = 'unknown',
    ) {}

    /**
     * Check if character is ready for the race
     */
    public function isReady(): bool
    {
        return ($this->readinessAssessment['overall'] ?? 'not_ready') === 'ready';
    }

    /**
     * Check if win probability is high (≥0.7)
     */
    public function hasHighWinProbability(): bool
    {
        return $this->winProbability >= 0.7;
    }

    /**
     * Check if win probability is low (<0.4)
     */
    public function hasLowWinProbability(): bool
    {
        return $this->winProbability < 0.4;
    }

    /**
     * Get win probability as a percentage string
     */
    public function getWinProbabilityPercentage(): string
    {
        return number_format($this->winProbability * 100, 1).'%';
    }

    /**
     * Get readiness level as a string
     */
    public function getReadinessLevel(): string
    {
        return match (true) {
            $this->winProbability >= 0.8 => 'Excellent',
            $this->winProbability >= 0.6 => 'Good',
            $this->winProbability >= 0.4 => 'Fair',
            default => 'Poor',
        };
    }

    /**
     * Get a summary string for display
     */
    public function getSummary(): string
    {
        $style = ucfirst($this->recommendedStyle);
        $probability = $this->getWinProbabilityPercentage();
        $readiness = $this->getReadinessLevel();

        return "{$style} style, {$probability} win probability ({$readiness})";
    }

    /**
     * Convert strategy to an array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'race_id' => $this->raceId,
            'recommended_style' => $this->recommendedStyle,
            'reasoning' => $this->reasoning,
            'win_probability' => $this->winProbability,
            'win_probability_percentage' => $this->getWinProbabilityPercentage(),
            'readiness_assessment' => $this->readinessAssessment,
            'readiness_level' => $this->getReadinessLevel(),
            'is_ready' => $this->isReady(),
            'risks' => $this->risks,
            'preparation_checklist' => $this->preparationChecklist,
            'predicted_outcomes' => $this->predictedOutcomes,
            'model_version' => $this->modelVersion,
        ];
    }

    /**
     * Create from an array
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        // Validate and cast readinessAssessment to array<string, string>
        $readinessAssessment = [];
        if (isset($data['readiness_assessment']) && is_array($data['readiness_assessment'])) {
            foreach ($data['readiness_assessment'] as $key => $value) {
                $keyStr = is_string($key) ? $key : (string) $key;
                $valueStr = is_string($value) ? $value : (is_scalar($value) ? (string) $value : '');
                $readinessAssessment[$keyStr] = $valueStr;
            }
        }

        // Validate and cast risks to array<string>
        $risks = [];
        if (isset($data['risks']) && is_array($data['risks'])) {
            foreach ($data['risks'] as $risk) {
                if (is_string($risk) || is_numeric($risk)) {
                    $risks[] = (string) $risk;
                }
            }
        }

        // Validate and cast preparationChecklist to array<string>
        $preparationChecklist = [];
        if (isset($data['preparation_checklist']) && is_array($data['preparation_checklist'])) {
            foreach ($data['preparation_checklist'] as $item) {
                if (is_string($item) || is_numeric($item)) {
                    $preparationChecklist[] = (string) $item;
                }
            }
        }

        $raceId = $data['race_id'] ?? 0;
        $recommendedStyle = $data['recommended_style'] ?? 'escape';
        $reasoning = $data['reasoning'] ?? '';
        $winProbability = $data['win_probability'] ?? 0.5;
        $predictedOutcomes = $data['predicted_outcomes'] ?? [];
        $modelVersion = $data['model_version'] ?? 'unknown';

        return new self(
            raceId: is_numeric($raceId) ? (int) $raceId : 0,
            recommendedStyle: is_string($recommendedStyle) ? $recommendedStyle : 'escape',
            reasoning: is_string($reasoning) ? $reasoning : '',
            winProbability: is_numeric($winProbability) ? (float) $winProbability : 0.5,
            readinessAssessment: $readinessAssessment,
            risks: $risks,
            preparationChecklist: $preparationChecklist,
            predictedOutcomes: is_array($predictedOutcomes) ? $predictedOutcomes : [],
            modelVersion: is_string($modelVersion) ? $modelVersion : 'unknown',
        );
    }
}
