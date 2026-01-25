<?php

declare(strict_types=1);

namespace App\Neuron\Responses;

use NeuronAI\StructuredOutput\SchemaProperty;
use NeuronAI\StructuredOutput\Validation\Rules\Length;
use NeuronAI\StructuredOutput\Validation\Rules\NotBlank;

/**
 * Structured output response for race strategy recommendations.
 *
 * This class defines the schema for AI agent responses when providing
 * race strategy recommendations. It uses PHP 8 attributes for schema definition
 * and validation to ensure type-safe, validated responses from the AI.
 *
 * **Validates: Requirements 9.1, 9.2, 9.3**
 */
class RaceStrategyResponse
{
    /**
     * Create a new race strategy response instance.
     *
     * @param  string  $recommendedRunningStyle  The recommended running style (e.g., 'escape', 'leader', 'betweener', 'chaser')
     * @param  array<int, string>  $recommendedSkills  List of skill names to equip for the race
     * @param  string  $racePreparationAdvice  Detailed advice on preparing for the race (20-500 characters)
     * @param  string|null  $expectedPerformance  Optional prediction of race performance (e.g., 'high chance of winning', 'moderate difficulty')
     * @param  array<int, string>  $riskFactors  Optional list of potential risks or challenges in the race
     */
    public function __construct(
        #[SchemaProperty(description: 'Recommended running style for the race (escape, leader, betweener, chaser)', required: true)]
        #[NotBlank]
        public string $recommendedRunningStyle,

        #[SchemaProperty(description: 'Array of skill names that should be equipped for optimal race performance', required: true)]
        public array $recommendedSkills,

        #[SchemaProperty(description: 'Detailed advice on how to prepare for the race, considering character stats, race conditions, and strategy', required: true)]
        #[NotBlank]
        #[Length(min: 20, max: 500)]
        public string $racePreparationAdvice,

        #[SchemaProperty(description: 'Optional prediction of expected race performance and win probability', required: false)]
        public ?string $expectedPerformance = null,

        #[SchemaProperty(description: 'Optional array of risk factors or challenges to be aware of during the race', required: false)]
        public array $riskFactors = [],
    ) {}

    /**
     * Convert the response to an array format.
     *
     * Useful for JSON serialization and API responses.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'recommended_running_style' => $this->recommendedRunningStyle,
            'recommended_skills' => $this->recommendedSkills,
            'race_preparation_advice' => $this->racePreparationAdvice,
            'expected_performance' => $this->expectedPerformance,
            'risk_factors' => $this->riskFactors,
        ];
    }

    /**
     * Get a human-readable summary of the race strategy.
     *
     * Formats the strategy in a concise, user-friendly format.
     */
    public function getSummary(): string
    {
        $summary = "Recommended Running Style: {$this->recommendedRunningStyle}\n";
        $summary .= "Race Preparation: {$this->racePreparationAdvice}\n";

        if (! empty($this->recommendedSkills)) {
            $summary .= "\nRecommended Skills:\n";
            foreach ($this->recommendedSkills as $skill) {
                $summary .= "  - {$skill}\n";
            }
        }

        if ($this->expectedPerformance !== null) {
            $summary .= "\nExpected Performance: {$this->expectedPerformance}\n";
        }

        if (! empty($this->riskFactors)) {
            $summary .= "\nRisk Factors:\n";
            foreach ($this->riskFactors as $risk) {
                $summary .= "  - {$risk}\n";
            }
        }

        return $summary;
    }

    /**
     * Validate that the response contains valid data.
     *
     * Performs additional validation beyond attribute constraints.
     *
     * @return array<string, string> Array of validation errors (empty if valid)
     */
    public function validate(): array
    {
        $errors = [];

        // Validate running style
        $validRunningStyles = ['escape', 'leader', 'betweener', 'chaser'];
        if (! \in_array(strtolower($this->recommendedRunningStyle), $validRunningStyles, true)) {
            $errors['recommended_running_style'] = 'Running style must be one of: '.\implode(', ', $validRunningStyles);
        }

        // Validate recommended skills
        if (empty($this->recommendedSkills)) {
            $errors['recommended_skills'] = 'At least one skill must be recommended';
        } else {
            foreach ($this->recommendedSkills as $index => $skill) {
                if (trim($skill) === '') {
                    $errors['recommended_skills'] = "Skill at index {$index} must be a non-empty string";

                    break;
                }
            }
        }

        // Validate race preparation advice length
        $adviceLength = \mb_strlen($this->racePreparationAdvice);
        if ($adviceLength < 20) {
            $errors['race_preparation_advice'] = 'Race preparation advice must be at least 20 characters long';
        } elseif ($adviceLength > 500) {
            $errors['race_preparation_advice'] = 'Race preparation advice must not exceed 500 characters';
        }

        // Validate expected performance (if provided)
        if ($this->expectedPerformance !== null && trim($this->expectedPerformance) === '') {
            $errors['expected_performance'] = 'Expected performance must not be an empty string if provided';
        }

        // Validate risk factors structure
        foreach ($this->riskFactors as $index => $risk) {
            if (trim($risk) === '') {
                $errors['risk_factors'] = "Risk factor at index {$index} must be a non-empty string";

                break;
            }
        }

        return $errors;
    }
}
