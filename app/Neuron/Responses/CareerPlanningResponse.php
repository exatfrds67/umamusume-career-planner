<?php

declare(strict_types=1);

namespace App\Neuron\Responses;

use NeuronAI\StructuredOutput\SchemaProperty;
use NeuronAI\StructuredOutput\Validation\Rules\Length;
use NeuronAI\StructuredOutput\Validation\Rules\NotBlank;

/**
 * Structured output response for career planning recommendations.
 *
 * Defines the schema for long-term career planning responses including
 * milestone steps, focus areas, and upcoming race preparation.
 */
class CareerPlanningResponse
{
    /**
     * Create a new career planning response instance.
     *
     * @param  string  $summary  High-level career plan summary (20-500 characters)
     * @param  array<int, string>  $focusAreas  Primary focus areas for the next phase
     * @param  array<int, array{label: string, target_turn?: int, target_phase?: string, objective: string, rationale: string}>  $milestones  Milestone plan with timing and rationale
     * @param  array<int, array{race_name: string, target_turn?: int, preparation: string}>  $racePlan  Optional upcoming race preparation plan
     * @param  array<int, string>  $riskNotes  Optional risks or watch-outs
     */
    public function __construct(
        #[SchemaProperty(description: 'Concise summary of the long-term career plan', required: true)]
        #[NotBlank]
        #[Length(min: 20, max: 500)]
        public string $summary,

        #[SchemaProperty(description: 'Primary focus areas for the next phase (stats, skills, preparation)', required: true)]
        public array $focusAreas,

        #[SchemaProperty(description: 'Milestone steps with timing and rationale', required: true)]
        public array $milestones,

        #[SchemaProperty(description: 'Upcoming race preparation plan, if applicable', required: false)]
        public array $racePlan = [],

        #[SchemaProperty(description: 'Risks or watch-outs that could derail targets', required: false)]
        public array $riskNotes = [],
    ) {}

    /**
     * Convert the response to an array format.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'summary' => $this->summary,
            'focus_areas' => $this->focusAreas,
            'milestones' => $this->milestones,
            'race_plan' => $this->racePlan,
            'risk_notes' => $this->riskNotes,
        ];
    }

    /**
     * Validate that the response contains valid data.
     *
     * @return array<string, string>
     */
    public function validate(): array
    {
        $errors = [];

        if (empty($this->focusAreas)) {
            $errors['focus_areas'] = 'At least one focus area is required.';
        } else {
            foreach ($this->focusAreas as $index => $focus) {
                if (trim($focus) === '') {
                    $errors['focus_areas'] = "Focus area at index {$index} must be a non-empty string.";

                    break;
                }
            }
        }

        if (empty($this->milestones)) {
            $errors['milestones'] = 'At least one milestone is required.';
        } else {
            foreach ($this->milestones as $index => $milestone) {
                if (! isset($milestone['label'], $milestone['objective'], $milestone['rationale'])) {
                    $errors['milestones'] = "Milestone at index {$index} must include label, objective, and rationale.";

                    break;
                }
                if (trim($milestone['label']) === '' || trim($milestone['objective']) === '' || trim($milestone['rationale']) === '') {
                    $errors['milestones'] = "Milestone at index {$index} has empty required fields.";

                    break;
                }
            }
        }

        foreach ($this->racePlan as $index => $race) {
            if (! isset($race['race_name'], $race['preparation'])) {
                $errors['race_plan'] = "Race plan item at index {$index} must include race_name and preparation.";

                break;
            }
            if (trim($race['race_name']) === '' || trim($race['preparation']) === '') {
                $errors['race_plan'] = "Race plan item at index {$index} has empty required fields.";

                break;
            }
        }

        foreach ($this->riskNotes as $index => $risk) {
            if (trim($risk) === '') {
                $errors['risk_notes'] = "Risk note at index {$index} must be a non-empty string.";

                break;
            }
        }

        return $errors;
    }
}
