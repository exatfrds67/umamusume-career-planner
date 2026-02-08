<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Record Training Outcome Request
 *
 * Validates incoming requests to record training outcomes for prediction accuracy tracking.
 *
 * **Validates: Requirements 3.8 (Prediction Accuracy Tracking)**
 *
 * @see \App\Http\Controllers\Api\AdvisoryController::recordTrainingOutcome()
 */
class RecordTrainingOutcomeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow all authenticated users (auth:sanctum middleware handles authentication)
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Career run identification
            'career_run_id' => ['required', 'integer', 'min:1'],
            'turn_number' => ['required', 'integer', 'min:1', 'max:78'],

            // Recommendation data (predicted values)
            'recommendation' => ['required', 'array'],
            'recommendation.type' => ['required', 'string', 'in:training_facility,skill_purchase,race_strategy,rest_recovery,bond_building'],
            'recommendation.priority' => ['required', 'string', 'in:critical,high,medium,low'],
            'recommendation.action' => ['required', 'string', 'max:255'],
            'recommendation.reasoning' => ['required', 'string', 'max:1000'],
            'recommendation.expected_outcomes' => ['required', 'array'],
            'recommendation.expected_outcomes.stat_gains' => ['sometimes', 'array'],
            'recommendation.expected_outcomes.stat_gains.*' => ['integer', 'min:0', 'max:200'],
            'recommendation.risks' => ['sometimes', 'array'],
            'recommendation.risks.*' => ['string', 'max:255'],
            'recommendation.confidence_score' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:1'],

            // Actual outcome data
            'actual_outcome' => ['required', 'array'],
            'actual_outcome.facility' => ['required', 'string', 'in:speed,stamina,power,guts,wisdom,rest'],
            'actual_outcome.stat_gains' => ['required', 'array'],
            'actual_outcome.stat_gains.speed' => ['sometimes', 'integer', 'min:0', 'max:200'],
            'actual_outcome.stat_gains.stamina' => ['sometimes', 'integer', 'min:0', 'max:200'],
            'actual_outcome.stat_gains.power' => ['sometimes', 'integer', 'min:0', 'max:200'],
            'actual_outcome.stat_gains.guts' => ['sometimes', 'integer', 'min:0', 'max:200'],
            'actual_outcome.stat_gains.wisdom' => ['sometimes', 'integer', 'min:0', 'max:200'],
            'actual_outcome.bond_increases' => ['sometimes', 'array'],
            'actual_outcome.bond_increases.*' => ['integer', 'min:0', 'max:50'],
            'actual_outcome.skill_hints' => ['sometimes', 'array'],
            'actual_outcome.skill_hints.*' => ['integer', 'min:1', 'max:5'],
            'actual_outcome.was_failure' => ['sometimes', 'boolean'],
            'actual_outcome.was_injury' => ['sometimes', 'boolean'],
            'actual_outcome.energy_change' => ['sometimes', 'integer', 'min:-50', 'max:50'],
            'actual_outcome.additional_data' => ['sometimes', 'array'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'career_run_id.required' => 'Career run ID is required',
            'career_run_id.integer' => 'Career run ID must be an integer',
            'career_run_id.min' => 'Career run ID must be at least 1',

            'turn_number.required' => 'Turn number is required',
            'turn_number.integer' => 'Turn number must be an integer',
            'turn_number.min' => 'Turn number must be at least 1',
            'turn_number.max' => 'Turn number cannot exceed 78',

            'recommendation.required' => 'Recommendation data is required',
            'recommendation.array' => 'Recommendation must be an array',
            'recommendation.type.required' => 'Recommendation type is required',
            'recommendation.type.in' => 'Invalid recommendation type',
            'recommendation.priority.required' => 'Recommendation priority is required',
            'recommendation.priority.in' => 'Invalid recommendation priority',
            'recommendation.action.required' => 'Recommendation action is required',
            'recommendation.reasoning.required' => 'Recommendation reasoning is required',
            'recommendation.expected_outcomes.required' => 'Expected outcomes are required',
            'recommendation.confidence_score.numeric' => 'Confidence score must be a number',
            'recommendation.confidence_score.min' => 'Confidence score must be between 0 and 1',
            'recommendation.confidence_score.max' => 'Confidence score must be between 0 and 1',

            'actual_outcome.required' => 'Actual outcome data is required',
            'actual_outcome.array' => 'Actual outcome must be an array',
            'actual_outcome.facility.required' => 'Training facility is required',
            'actual_outcome.facility.in' => 'Invalid training facility',
            'actual_outcome.stat_gains.required' => 'Stat gains are required',
            'actual_outcome.stat_gains.array' => 'Stat gains must be an array',
            'actual_outcome.stat_gains.*.integer' => 'Stat gain must be an integer',
            'actual_outcome.stat_gains.*.min' => 'Stat gain cannot be negative',
            'actual_outcome.stat_gains.*.max' => 'Stat gain cannot exceed 200',
            'actual_outcome.bond_increases.*.integer' => 'Bond increase must be an integer',
            'actual_outcome.bond_increases.*.min' => 'Bond increase cannot be negative',
            'actual_outcome.bond_increases.*.max' => 'Bond increase cannot exceed 50',
            'actual_outcome.skill_hints.*.integer' => 'Skill hint level must be an integer',
            'actual_outcome.skill_hints.*.min' => 'Skill hint level must be at least 1',
            'actual_outcome.skill_hints.*.max' => 'Skill hint level cannot exceed 5',
            'actual_outcome.was_failure.boolean' => 'Was failure must be a boolean',
            'actual_outcome.was_injury.boolean' => 'Was injury must be a boolean',
            'actual_outcome.energy_change.integer' => 'Energy change must be an integer',
            'actual_outcome.energy_change.min' => 'Energy change cannot be less than -50',
            'actual_outcome.energy_change.max' => 'Energy change cannot exceed 50',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'career_run_id' => 'career run ID',
            'turn_number' => 'turn number',
            'recommendation.type' => 'recommendation type',
            'recommendation.priority' => 'recommendation priority',
            'recommendation.action' => 'recommendation action',
            'recommendation.reasoning' => 'recommendation reasoning',
            'recommendation.expected_outcomes' => 'expected outcomes',
            'recommendation.confidence_score' => 'confidence score',
            'actual_outcome.facility' => 'training facility',
            'actual_outcome.stat_gains' => 'stat gains',
            'actual_outcome.bond_increases' => 'bond increases',
            'actual_outcome.skill_hints' => 'skill hints',
            'actual_outcome.was_failure' => 'training failure status',
            'actual_outcome.was_injury' => 'injury status',
            'actual_outcome.energy_change' => 'energy change',
        ];
    }
}
