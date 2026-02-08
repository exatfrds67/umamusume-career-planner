<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Record Race Outcome Request
 *
 * Validates incoming requests to record race outcomes for prediction accuracy tracking.
 *
 * **Validates: Requirements 3.8 (Prediction Accuracy Tracking)**
 *
 * @see \App\Http\Controllers\Api\AdvisoryController::recordRaceOutcome()
 */
class RecordRaceOutcomeRequest extends FormRequest
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
            // Career run and race identification
            'career_run_id' => ['required', 'integer', 'min:1'],
            'race_id' => ['required', 'integer', 'min:1'],

            // Race strategy data (predicted values)
            'strategy' => ['required', 'array'],
            'strategy.recommended_style' => ['required', 'string', 'in:escape,lead,pace,chase'],
            'strategy.reasoning' => ['required', 'string', 'max:1000'],
            'strategy.win_probability' => ['required', 'numeric', 'min:0', 'max:1'],
            'strategy.readiness_assessment' => ['required', 'array'],
            'strategy.readiness_assessment.stamina' => ['sometimes', 'string', 'max:50'],
            'strategy.readiness_assessment.speed' => ['sometimes', 'string', 'max:50'],
            'strategy.readiness_assessment.power' => ['sometimes', 'string', 'max:50'],
            'strategy.readiness_assessment.overall' => ['required', 'string', 'max:50'],
            'strategy.risks' => ['sometimes', 'array'],
            'strategy.risks.*' => ['string', 'max:255'],
            'strategy.preparation_checklist' => ['sometimes', 'array'],
            'strategy.preparation_checklist.*' => ['string', 'max:255'],
            'strategy.predicted_outcomes' => ['sometimes', 'array'],
            'strategy.model_version' => ['sometimes', 'string', 'max:50'],

            // Actual race result data
            'actual_result' => ['required', 'array'],
            'actual_result.placement' => ['required', 'integer', 'min:1', 'max:18'],
            'actual_result.total_competitors' => ['required', 'integer', 'min:1', 'max:18'],
            'actual_result.running_style' => ['required', 'string', 'in:escape,lead,pace,chase'],
            'actual_result.was_win' => ['sometimes', 'boolean'],
            'actual_result.was_placed' => ['sometimes', 'boolean'],
            'actual_result.finish_time' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'actual_result.fan_gain' => ['sometimes', 'integer', 'min:0'],
            'actual_result.additional_data' => ['sometimes', 'array'],
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

            'race_id.required' => 'Race ID is required',
            'race_id.integer' => 'Race ID must be an integer',
            'race_id.min' => 'Race ID must be at least 1',

            'strategy.required' => 'Race strategy data is required',
            'strategy.array' => 'Race strategy must be an array',
            'strategy.recommended_style.required' => 'Recommended running style is required',
            'strategy.recommended_style.in' => 'Invalid running style (must be escape, lead, pace, or chase)',
            'strategy.reasoning.required' => 'Strategy reasoning is required',
            'strategy.reasoning.max' => 'Strategy reasoning cannot exceed 1000 characters',
            'strategy.win_probability.required' => 'Win probability is required',
            'strategy.win_probability.numeric' => 'Win probability must be a number',
            'strategy.win_probability.min' => 'Win probability must be between 0 and 1',
            'strategy.win_probability.max' => 'Win probability must be between 0 and 1',
            'strategy.readiness_assessment.required' => 'Readiness assessment is required',
            'strategy.readiness_assessment.array' => 'Readiness assessment must be an array',
            'strategy.readiness_assessment.overall.required' => 'Overall readiness is required',
            'strategy.risks.*.string' => 'Each risk must be a string',
            'strategy.risks.*.max' => 'Each risk cannot exceed 255 characters',
            'strategy.preparation_checklist.*.string' => 'Each checklist item must be a string',
            'strategy.preparation_checklist.*.max' => 'Each checklist item cannot exceed 255 characters',
            'strategy.model_version.string' => 'Model version must be a string',
            'strategy.model_version.max' => 'Model version cannot exceed 50 characters',

            'actual_result.required' => 'Actual race result data is required',
            'actual_result.array' => 'Actual race result must be an array',
            'actual_result.placement.required' => 'Race placement is required',
            'actual_result.placement.integer' => 'Race placement must be an integer',
            'actual_result.placement.min' => 'Race placement must be at least 1',
            'actual_result.placement.max' => 'Race placement cannot exceed 18',
            'actual_result.total_competitors.required' => 'Total competitors is required',
            'actual_result.total_competitors.integer' => 'Total competitors must be an integer',
            'actual_result.total_competitors.min' => 'Total competitors must be at least 1',
            'actual_result.total_competitors.max' => 'Total competitors cannot exceed 18',
            'actual_result.running_style.required' => 'Running style used is required',
            'actual_result.running_style.in' => 'Invalid running style (must be escape, lead, pace, or chase)',
            'actual_result.was_win.boolean' => 'Win status must be a boolean',
            'actual_result.was_placed.boolean' => 'Placed status must be a boolean',
            'actual_result.finish_time.numeric' => 'Finish time must be a number',
            'actual_result.finish_time.min' => 'Finish time cannot be negative',
            'actual_result.fan_gain.integer' => 'Fan gain must be an integer',
            'actual_result.fan_gain.min' => 'Fan gain cannot be negative',
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
            'race_id' => 'race ID',
            'strategy.recommended_style' => 'recommended running style',
            'strategy.reasoning' => 'strategy reasoning',
            'strategy.win_probability' => 'win probability',
            'strategy.readiness_assessment' => 'readiness assessment',
            'strategy.readiness_assessment.overall' => 'overall readiness',
            'strategy.model_version' => 'model version',
            'actual_result.placement' => 'race placement',
            'actual_result.total_competitors' => 'total competitors',
            'actual_result.running_style' => 'running style used',
            'actual_result.was_win' => 'win status',
            'actual_result.was_placed' => 'placed status',
            'actual_result.finish_time' => 'finish time',
            'actual_result.fan_gain' => 'fan gain',
        ];
    }
}
