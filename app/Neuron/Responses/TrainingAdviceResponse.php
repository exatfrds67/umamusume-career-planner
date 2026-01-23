<?php

declare(strict_types=1);

namespace App\Neuron\Responses;

use NeuronAI\StructuredOutput\SchemaProperty;
use NeuronAI\StructuredOutput\Validation\Rules\Length;
use NeuronAI\StructuredOutput\Validation\Rules\NotBlank;

/**
 * Structured output response for training advice recommendations.
 *
 * This class defines the schema for AI agent responses when providing
 * training recommendations. It uses PHP 8 attributes for schema definition
 * and validation to ensure type-safe, validated responses from the AI.
 *
 * **Validates: Requirements 9.1, 9.2, 9.3**
 */
class TrainingAdviceResponse
{
    /**
     * Create a new training advice response instance.
     *
     * @param  string  $recommendedTraining  The recommended training type (e.g., 'speed', 'stamina', 'power', 'guts', 'wit', 'rest')
     * @param  string  $reasoning  Detailed explanation for the recommendation (20-500 characters)
     * @param  array<string, int>  $expectedGains  Expected stat gains from the recommended training (e.g., ['speed' => 15, 'power' => 5])
     * @param  array<int, array{training: string, reason: string}>  $alternatives  Alternative training options with brief explanations
     */
    public function __construct(
        #[SchemaProperty(description: 'Recommended training type (speed, stamina, power, guts, wit, rest)', required: true)]
        #[NotBlank]
        public string $recommendedTraining,

        #[SchemaProperty(description: 'Clear explanation of why this training is recommended, considering stats, aptitudes, and support cards', required: true)]
        #[NotBlank]
        #[Length(min: 20, max: 500)]
        public string $reasoning,

        #[SchemaProperty(description: 'Predicted stat increases as key-value pairs (e.g., {"speed": 15, "power": 5})', required: true)]
        public array $expectedGains,

        #[SchemaProperty(description: 'Other viable training choices with brief explanations, each containing "training" and "reason" keys', required: false)]
        public array $alternatives = [],
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
            'recommended_training' => $this->recommendedTraining,
            'reasoning' => $this->reasoning,
            'expected_gains' => $this->expectedGains,
            'alternatives' => $this->alternatives,
        ];
    }

    /**
     * Get a human-readable summary of the recommendation.
     *
     * Formats the recommendation in a concise, user-friendly format.
     */
    public function getSummary(): string
    {
        $summary = "Recommended: {$this->recommendedTraining}\n";
        $summary .= "Reason: {$this->reasoning}\n";

        if (! empty($this->expectedGains)) {
            $gains = [];
            foreach ($this->expectedGains as $stat => $value) {
                $gains[] = "{$stat}: +{$value}";
            }
            $summary .= 'Expected Gains: '.implode(', ', $gains)."\n";
        }

        if (! empty($this->alternatives)) {
            $summary .= "\nAlternatives:\n";
            foreach ($this->alternatives as $alt) {
                $training = $alt['training'] ?? 'unknown';
                $reason = $alt['reason'] ?? 'no reason provided';
                $summary .= "  - {$training}: {$reason}\n";
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

        // Validate training type
        $validTrainingTypes = ['speed', 'stamina', 'power', 'guts', 'wit', 'rest'];
        if (! in_array(strtolower($this->recommendedTraining), $validTrainingTypes, true)) {
            $errors['recommended_training'] = 'Training type must be one of: '.implode(', ', $validTrainingTypes);
        }

        // Validate reasoning length
        $reasoningLength = mb_strlen($this->reasoning);
        if ($reasoningLength < 20) {
            $errors['reasoning'] = 'Reasoning must be at least 20 characters long';
        } elseif ($reasoningLength > 500) {
            $errors['reasoning'] = 'Reasoning must not exceed 500 characters';
        }

        // Validate expected gains structure
        if (empty($this->expectedGains)) {
            $errors['expected_gains'] = 'Expected gains cannot be empty';
        } else {
            $validStats = ['speed', 'stamina', 'power', 'guts', 'wit'];
            foreach ($this->expectedGains as $stat => $value) {
                if (! in_array($stat, $validStats, true)) {
                    $errors['expected_gains'] = "Invalid stat name: {$stat}";

                    break;
                }
                if (! is_int($value) || $value < 0) {
                    $errors['expected_gains'] = 'Stat gain values must be non-negative integers';

                    break;
                }
            }
        }

        // Validate alternatives structure
        foreach ($this->alternatives as $index => $alt) {
            if (! is_array($alt)) {
                $errors['alternatives'] = "Alternative at index {$index} must be an array";

                break;
            }
            if (! isset($alt['training']) || ! isset($alt['reason'])) {
                $errors['alternatives'] = "Alternative at index {$index} must contain 'training' and 'reason' keys";

                break;
            }
            if (! in_array(strtolower($alt['training']), $validTrainingTypes, true)) {
                $errors['alternatives'] = "Invalid training type in alternative at index {$index}";

                break;
            }
        }

        return $errors;
    }
}
