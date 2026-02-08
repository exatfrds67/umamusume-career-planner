<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Critical Detection Request
 *
 * Validates incoming requests for critical situation detection.
 * Ensures all required context data is present and valid for detecting
 * critical situations that require immediate attention.
 *
 * **Validates: Requirements 3.4 (Critical Situation Detection)**
 *
 * @see \App\Http\Controllers\Api\AdvisoryController::detectCriticalSituations()
 * @see \App\Services\CriticalSituationDetector
 */
class CriticalDetectionRequest extends FormRequest
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
            'career_run_id' => ['required'],
            'turn_number' => ['required', 'integer', 'min:1', 'max:78'],

            // Context object containing character state
            'context' => ['required', 'array'],

            // Character stats (required within context)
            'context.stats' => ['required', 'array'],
            'context.stats.speed' => ['required', 'integer', 'min:0', 'max:1500'],
            'context.stats.stamina' => ['required', 'integer', 'min:0', 'max:1500'],
            'context.stats.power' => ['required', 'integer', 'min:0', 'max:1500'],
            'context.stats.guts' => ['required', 'integer', 'min:0', 'max:1500'],
            'context.stats.wisdom' => ['required', 'integer', 'min:0', 'max:1500'],

            // Energy level (required within context)
            'context.energy' => ['required', 'integer', 'min:0', 'max:120'],

            // Upcoming races (optional but validated if present)
            'context.upcoming_races' => ['sometimes', 'array'],
            'context.upcoming_races.*.distance' => [
                'required_with:context.upcoming_races',
                'string',
                Rule::in(['sprint', 'mile', 'medium', 'long']),
            ],
            'context.upcoming_races.*.turn' => [
                'required_with:context.upcoming_races',
                'integer',
                'min:1',
                'max:78',
            ],

            // Support bonds (optional but validated if present)
            'context.support_bonds' => ['sometimes', 'array', 'max:6'],
            'context.support_bonds.*' => ['integer', 'min:0', 'max:100'],

            // Optional additional context fields
            'context.sp_available' => ['sometimes', 'integer', 'min:0', 'max:9999'],
            'context.mood' => ['sometimes', 'string', Rule::in(['very_bad', 'bad', 'normal', 'good', 'great'])],
            'context.phase' => ['sometimes', 'string', Rule::in(['junior_year', 'classic_year', 'senior_year', 'ura_finals'])],
            'context.facility_levels' => ['sometimes', 'array'],
            'context.facility_levels.speed' => ['sometimes', 'integer', 'min:1', 'max:5'],
            'context.facility_levels.stamina' => ['sometimes', 'integer', 'min:1', 'max:5'],
            'context.facility_levels.power' => ['sometimes', 'integer', 'min:1', 'max:5'],
            'context.facility_levels.guts' => ['sometimes', 'integer', 'min:1', 'max:5'],
            'context.facility_levels.wisdom' => ['sometimes', 'integer', 'min:1', 'max:5'],
            'context.acquired_skills' => ['sometimes', 'array'],
            'context.acquired_skills.*' => ['integer', 'min:1'],
            'context.skill_hints' => ['sometimes', 'array'],
            'context.skill_hints.*.skill_id' => ['required_with:context.skill_hints', 'integer', 'min:1'],
            'context.skill_hints.*.level' => ['required_with:context.skill_hints', 'integer', 'min:1', 'max:5'],

            // Storage mode (optional, defaults to 'account')
            'storage_mode' => ['sometimes', 'string', Rule::in(['local', 'account'])],
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
            // Career run identification
            'career_run_id.required' => 'Career run ID is required',
            'turn_number.required' => 'Turn number is required',
            'turn_number.integer' => 'Turn number must be an integer',
            'turn_number.min' => 'Turn number must be at least 1',
            'turn_number.max' => 'Turn number cannot exceed 78',

            // Context
            'context.required' => 'Context object is required',
            'context.array' => 'Context must be an object',

            // Stats
            'context.stats.required' => 'Character stats are required',
            'context.stats.array' => 'Stats must be an object',
            'context.stats.speed.required' => 'Speed stat is required',
            'context.stats.speed.integer' => 'Speed must be an integer',
            'context.stats.speed.min' => 'Speed cannot be negative',
            'context.stats.speed.max' => 'Speed cannot exceed 1500',
            'context.stats.stamina.required' => 'Stamina stat is required',
            'context.stats.stamina.integer' => 'Stamina must be an integer',
            'context.stats.stamina.min' => 'Stamina cannot be negative',
            'context.stats.stamina.max' => 'Stamina cannot exceed 1500',
            'context.stats.power.required' => 'Power stat is required',
            'context.stats.power.integer' => 'Power must be an integer',
            'context.stats.power.min' => 'Power cannot be negative',
            'context.stats.power.max' => 'Power cannot exceed 1500',
            'context.stats.guts.required' => 'Guts stat is required',
            'context.stats.guts.integer' => 'Guts must be an integer',
            'context.stats.guts.min' => 'Guts cannot be negative',
            'context.stats.guts.max' => 'Guts cannot exceed 1500',
            'context.stats.wisdom.required' => 'Wisdom stat is required',
            'context.stats.wisdom.integer' => 'Wisdom must be an integer',
            'context.stats.wisdom.min' => 'Wisdom cannot be negative',
            'context.stats.wisdom.max' => 'Wisdom cannot exceed 1500',

            // Energy
            'context.energy.required' => 'Energy level is required',
            'context.energy.integer' => 'Energy must be an integer',
            'context.energy.min' => 'Energy cannot be negative',
            'context.energy.max' => 'Energy cannot exceed 120',

            // Upcoming races
            'context.upcoming_races.array' => 'Upcoming races must be an array',
            'context.upcoming_races.*.distance.required_with' => 'Race distance is required',
            'context.upcoming_races.*.distance.in' => 'Invalid distance. Valid values: sprint, mile, medium, long',
            'context.upcoming_races.*.turn.required_with' => 'Race turn is required',
            'context.upcoming_races.*.turn.integer' => 'Race turn must be an integer',
            'context.upcoming_races.*.turn.min' => 'Race turn must be at least 1',
            'context.upcoming_races.*.turn.max' => 'Race turn cannot exceed 78',

            // Support bonds
            'context.support_bonds.array' => 'Support bonds must be an array',
            'context.support_bonds.max' => 'Support bonds cannot have more than 6 entries',
            'context.support_bonds.*.integer' => 'Bond level must be an integer',
            'context.support_bonds.*.min' => 'Bond level cannot be negative',
            'context.support_bonds.*.max' => 'Bond level cannot exceed 100',

            // Optional fields
            'context.sp_available.integer' => 'SP must be an integer',
            'context.sp_available.min' => 'SP cannot be negative',
            'context.sp_available.max' => 'SP cannot exceed 9999',
            'context.mood.in' => 'Invalid mood. Valid values: very_bad, bad, normal, good, great',
            'context.phase.in' => 'Invalid career phase. Valid values: junior_year, classic_year, senior_year, ura_finals',
            'context.facility_levels.array' => 'Facility levels must be an object',
            'context.facility_levels.*.integer' => 'Facility level must be an integer',
            'context.facility_levels.*.min' => 'Facility level must be at least 1',
            'context.facility_levels.*.max' => 'Facility level cannot exceed 5',
            'context.acquired_skills.array' => 'Acquired skills must be an array',
            'context.acquired_skills.*.integer' => 'Skill ID must be an integer',
            'context.skill_hints.array' => 'Skill hints must be an array',
            'context.skill_hints.*.skill_id.required_with' => 'Skill ID is required for each hint',
            'context.skill_hints.*.level.required_with' => 'Hint level is required for each hint',
            'context.skill_hints.*.level.min' => 'Hint level must be at least 1',
            'context.skill_hints.*.level.max' => 'Hint level cannot exceed 5',

            // Storage mode
            'storage_mode.in' => 'Storage mode must be either "local" or "account"',
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
            'context' => 'context',
            'context.stats' => 'character stats',
            'context.stats.speed' => 'speed stat',
            'context.stats.stamina' => 'stamina stat',
            'context.stats.power' => 'power stat',
            'context.stats.guts' => 'guts stat',
            'context.stats.wisdom' => 'wisdom stat',
            'context.energy' => 'energy level',
            'context.upcoming_races' => 'upcoming races',
            'context.support_bonds' => 'support bonds',
            'context.sp_available' => 'available SP',
            'context.mood' => 'mood',
            'context.phase' => 'career phase',
            'context.facility_levels' => 'facility levels',
            'context.acquired_skills' => 'acquired skills',
            'context.skill_hints' => 'skill hints',
            'storage_mode' => 'storage mode',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * Sets default values for optional fields.
     */
    protected function prepareForValidation(): void
    {
        // Set defaults for optional fields with proper type casting
        $context = $this->input('context');
        $contextArray = is_array($context) ? $context : [];

        $this->merge([
            'storage_mode' => $this->input('storage_mode', 'account'),
            'context' => array_merge([
                'upcoming_races' => [],
                'support_bonds' => [],
                'sp_available' => 0,
                'mood' => 'normal',
                'phase' => 'classic_year',
                'facility_levels' => [
                    'speed' => 1,
                    'stamina' => 1,
                    'power' => 1,
                    'guts' => 1,
                    'wisdom' => 1,
                ],
                'acquired_skills' => [],
                'skill_hints' => [],
            ], $contextArray),
        ]);
    }
}
