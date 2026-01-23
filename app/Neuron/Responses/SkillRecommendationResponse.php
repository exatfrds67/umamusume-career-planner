<?php

declare(strict_types=1);

namespace App\Neuron\Responses;

use NeuronAI\StructuredOutput\SchemaProperty;
use NeuronAI\StructuredOutput\Validation\Rules\Length;
use NeuronAI\StructuredOutput\Validation\Rules\NotBlank;

/**
 * Structured output response for skill recommendations.
 *
 * This class defines the schema for AI agent responses when providing
 * skill acquisition recommendations. It uses PHP 8 attributes for schema definition
 * and validation to ensure type-safe, validated responses from the AI.
 *
 * **Validates: Requirements 9.1, 9.2, 9.3**
 */
class SkillRecommendationResponse
{
    /**
     * Create a new skill recommendation response instance.
     *
     * @param  array<int, array{name: string, reason: string, priority: string}>  $recommendedSkills  Array of recommended skills with name, reason, and priority (high/medium/low)
     * @param  string  $acquisitionStrategy  Overall strategy for skill acquisition (20-500 characters)
     * @param  string  $spBudgetConsiderations  Advice on managing skill point budget (20-500 characters)
     * @param  array<int, array{skills: array<int, string>, benefit: string}>  $skillSynergies  Optional array of skill combinations that work well together
     */
    public function __construct(
        #[SchemaProperty(description: 'Array of recommended skills, each containing "name" (skill name), "reason" (why to acquire it), and "priority" (high/medium/low)', required: true)]
        public array $recommendedSkills,

        #[SchemaProperty(description: 'Overall strategy for acquiring skills, considering character build and race preferences', required: true)]
        #[NotBlank]
        #[Length(min: 20, max: 500)]
        public string $acquisitionStrategy,

        #[SchemaProperty(description: 'Advice on managing skill point budget, including cost-effectiveness and prioritization', required: true)]
        #[NotBlank]
        #[Length(min: 20, max: 500)]
        public string $spBudgetConsiderations,

        #[SchemaProperty(description: 'Optional array of skill synergies, each containing "skills" (array of skill names) and "benefit" (description of synergy)', required: false)]
        public array $skillSynergies = [],
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
            'recommended_skills' => $this->recommendedSkills,
            'acquisition_strategy' => $this->acquisitionStrategy,
            'sp_budget_considerations' => $this->spBudgetConsiderations,
            'skill_synergies' => $this->skillSynergies,
        ];
    }

    /**
     * Get a human-readable summary of the skill recommendations.
     *
     * Formats the recommendations in a concise, user-friendly format.
     */
    public function getSummary(): string
    {
        $summary = "Skill Acquisition Strategy: {$this->acquisitionStrategy}\n";
        $summary .= "SP Budget Considerations: {$this->spBudgetConsiderations}\n";

        if (! empty($this->recommendedSkills)) {
            $summary .= "\nRecommended Skills:\n";
            foreach ($this->recommendedSkills as $skill) {
                $name = $skill['name'] ?? 'unknown';
                $reason = $skill['reason'] ?? 'no reason provided';
                $priority = $skill['priority'] ?? 'medium';
                $summary .= "  - {$name} [{$priority}]: {$reason}\n";
            }
        }

        if (! empty($this->skillSynergies)) {
            $summary .= "\nSkill Synergies:\n";
            foreach ($this->skillSynergies as $synergy) {
                $skills = $synergy['skills'] ?? [];
                $benefit = $synergy['benefit'] ?? 'no benefit described';
                $skillNames = implode(' + ', $skills);
                $summary .= "  - {$skillNames}: {$benefit}\n";
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

        // Validate recommended skills structure
        if (empty($this->recommendedSkills)) {
            $errors['recommended_skills'] = 'At least one skill must be recommended';
        } else {
            $validPriorities = ['high', 'medium', 'low'];
            foreach ($this->recommendedSkills as $index => $skill) {
                if (! is_array($skill)) {
                    $errors['recommended_skills'] = "Skill at index {$index} must be an array";

                    break;
                }
                if (! isset($skill['name']) || ! isset($skill['reason']) || ! isset($skill['priority'])) {
                    $errors['recommended_skills'] = "Skill at index {$index} must contain 'name', 'reason', and 'priority' keys";

                    break;
                }
                if (! is_string($skill['name']) || trim($skill['name']) === '') {
                    $errors['recommended_skills'] = "Skill name at index {$index} must be a non-empty string";

                    break;
                }
                if (! is_string($skill['reason']) || trim($skill['reason']) === '') {
                    $errors['recommended_skills'] = "Skill reason at index {$index} must be a non-empty string";

                    break;
                }
                if (! in_array(strtolower($skill['priority']), $validPriorities, true)) {
                    $errors['recommended_skills'] = "Skill priority at index {$index} must be one of: ".implode(', ', $validPriorities);

                    break;
                }
            }
        }

        // Validate acquisition strategy length
        $strategyLength = mb_strlen($this->acquisitionStrategy);
        if ($strategyLength < 20) {
            $errors['acquisition_strategy'] = 'Acquisition strategy must be at least 20 characters long';
        } elseif ($strategyLength > 500) {
            $errors['acquisition_strategy'] = 'Acquisition strategy must not exceed 500 characters';
        }

        // Validate SP budget considerations length
        $budgetLength = mb_strlen($this->spBudgetConsiderations);
        if ($budgetLength < 20) {
            $errors['sp_budget_considerations'] = 'SP budget considerations must be at least 20 characters long';
        } elseif ($budgetLength > 500) {
            $errors['sp_budget_considerations'] = 'SP budget considerations must not exceed 500 characters';
        }

        // Validate skill synergies structure
        foreach ($this->skillSynergies as $index => $synergy) {
            if (! is_array($synergy)) {
                $errors['skill_synergies'] = "Synergy at index {$index} must be an array";

                break;
            }
            if (! isset($synergy['skills']) || ! isset($synergy['benefit'])) {
                $errors['skill_synergies'] = "Synergy at index {$index} must contain 'skills' and 'benefit' keys";

                break;
            }
            if (! is_array($synergy['skills']) || empty($synergy['skills'])) {
                $errors['skill_synergies'] = "Synergy skills at index {$index} must be a non-empty array";

                break;
            }
            foreach ($synergy['skills'] as $skillIndex => $skillName) {
                if (! is_string($skillName) || trim($skillName) === '') {
                    $errors['skill_synergies'] = "Skill name at synergy {$index}, skill {$skillIndex} must be a non-empty string";

                    break 2;
                }
            }
            if (! is_string($synergy['benefit']) || trim($synergy['benefit']) === '') {
                $errors['skill_synergies'] = "Synergy benefit at index {$index} must be a non-empty string";

                break;
            }
        }

        return $errors;
    }
}
