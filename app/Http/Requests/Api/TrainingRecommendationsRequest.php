<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Enums\CareerPhase;
use App\Enums\Mood;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Training Recommendations Request
 *
 * Validates incoming requests for AI-powered training recommendations.
 * Ensures all required context data is present and valid for generating
 * intelligent training facility recommendations.
 *
 * **Validates: Requirements 3.1 (Real-Time Training Recommendations)**
 *
 * @see \App\Http\Controllers\Api\AdvisoryController::getTrainingRecommendations()
 * @see \App\Services\TrainingAdvisoryService::getTrainingRecommendations()
 */
class TrainingRecommendationsRequest extends FormRequest
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
            'storage_mode' => ['required', 'string', Rule::in(['local', 'account'])],

            // Turn and phase information
            'turn_number' => ['required', 'integer', 'min:1', 'max:78'],
            'phase' => ['required', 'string', Rule::in(CareerPhase::values())],

            // Character stats (required)
            'stats' => ['required', 'array'],
            'stats.speed' => ['required', 'integer', 'min:0', 'max:1500'],
            'stats.stamina' => ['required', 'integer', 'min:0', 'max:1500'],
            'stats.power' => ['required', 'integer', 'min:0', 'max:1500'],
            'stats.guts' => ['required', 'integer', 'min:0', 'max:1500'],
            'stats.wisdom' => ['required', 'integer', 'min:0', 'max:1500'],

            // Resources
            'sp_available' => ['required', 'integer', 'min:0', 'max:9999'],
            'energy' => ['required', 'integer', 'min:0', 'max:120'],
            'mood' => ['required', 'string', Rule::in(Mood::values())],

            // Skills
            'acquired_skills' => ['sometimes', 'array'],
            'acquired_skills.*' => ['integer', 'min:1'],
            'skill_hints' => ['sometimes', 'array'],
            'skill_hints.*.skill_id' => ['required_with:skill_hints', 'integer', 'min:1'],
            'skill_hints.*.level' => ['required_with:skill_hints', 'integer', 'min:1', 'max:5'],

            // Support deck
            'support_deck' => ['sometimes', 'array'],
            'support_deck.cards' => ['sometimes', 'array', 'max:6'],
            'support_deck.cards.*.id' => ['required_with:support_deck.cards', 'integer', 'min:1'],
            'support_deck.cards.*.bond' => ['required_with:support_deck.cards', 'integer', 'min:0', 'max:100'],
            'support_deck.cards.*.facility' => [
                'required_with:support_deck.cards',
                'string',
                Rule::in(['speed', 'stamina', 'power', 'guts', 'wisdom', 'friend']),
            ],

            // Facility levels
            'facility_levels' => ['sometimes', 'array'],
            'facility_levels.speed' => ['sometimes', 'integer', 'min:1', 'max:5'],
            'facility_levels.stamina' => ['sometimes', 'integer', 'min:1', 'max:5'],
            'facility_levels.power' => ['sometimes', 'integer', 'min:1', 'max:5'],
            'facility_levels.guts' => ['sometimes', 'integer', 'min:1', 'max:5'],
            'facility_levels.wisdom' => ['sometimes', 'integer', 'min:1', 'max:5'],

            // Upcoming races
            'upcoming_races' => ['sometimes', 'array'],
            'upcoming_races.*.id' => ['required_with:upcoming_races', 'integer', 'min:1'],
            'upcoming_races.*.distance' => [
                'required_with:upcoming_races',
                'string',
                Rule::in(['sprint', 'mile', 'medium', 'long']),
            ],
            'upcoming_races.*.turn' => ['required_with:upcoming_races', 'integer', 'min:1', 'max:78'],

            // Optional scenario
            'scenario' => ['sometimes', 'nullable', 'string', 'max:50'],
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
            'storage_mode.required' => 'Storage mode is required',
            'storage_mode.in' => 'Storage mode must be either "local" or "account"',

            // Turn and phase
            'turn_number.required' => 'Turn number is required',
            'turn_number.integer' => 'Turn number must be an integer',
            'turn_number.min' => 'Turn number must be at least 1',
            'turn_number.max' => 'Turn number cannot exceed 78',
            'phase.required' => 'Career phase is required',
            'phase.in' => 'Invalid career phase. Valid values: junior_year, classic_year, senior_year, ura_finals',

            // Stats
            'stats.required' => 'Character stats are required',
            'stats.array' => 'Stats must be an object',
            'stats.speed.required' => 'Speed stat is required',
            'stats.speed.integer' => 'Speed must be an integer',
            'stats.speed.min' => 'Speed cannot be negative',
            'stats.speed.max' => 'Speed cannot exceed 1500',
            'stats.stamina.required' => 'Stamina stat is required',
            'stats.stamina.integer' => 'Stamina must be an integer',
            'stats.stamina.min' => 'Stamina cannot be negative',
            'stats.stamina.max' => 'Stamina cannot exceed 1500',
            'stats.power.required' => 'Power stat is required',
            'stats.power.integer' => 'Power must be an integer',
            'stats.power.min' => 'Power cannot be negative',
            'stats.power.max' => 'Power cannot exceed 1500',
            'stats.guts.required' => 'Guts stat is required',
            'stats.guts.integer' => 'Guts must be an integer',
            'stats.guts.min' => 'Guts cannot be negative',
            'stats.guts.max' => 'Guts cannot exceed 1500',
            'stats.wisdom.required' => 'Wisdom stat is required',
            'stats.wisdom.integer' => 'Wisdom must be an integer',
            'stats.wisdom.min' => 'Wisdom cannot be negative',
            'stats.wisdom.max' => 'Wisdom cannot exceed 1500',

            // Resources
            'sp_available.required' => 'Available SP is required',
            'sp_available.integer' => 'SP must be an integer',
            'sp_available.min' => 'SP cannot be negative',
            'sp_available.max' => 'SP cannot exceed 9999',
            'energy.required' => 'Energy level is required',
            'energy.integer' => 'Energy must be an integer',
            'energy.min' => 'Energy cannot be negative',
            'energy.max' => 'Energy cannot exceed 120',
            'mood.required' => 'Mood is required',
            'mood.in' => 'Invalid mood. Valid values: very_bad, bad, normal, good, great',

            // Skills
            'acquired_skills.array' => 'Acquired skills must be an array',
            'acquired_skills.*.integer' => 'Skill ID must be an integer',
            'acquired_skills.*.min' => 'Skill ID must be at least 1',
            'skill_hints.array' => 'Skill hints must be an array',
            'skill_hints.*.skill_id.required_with' => 'Skill ID is required for each hint',
            'skill_hints.*.skill_id.integer' => 'Skill ID must be an integer',
            'skill_hints.*.level.required_with' => 'Hint level is required for each hint',
            'skill_hints.*.level.integer' => 'Hint level must be an integer',
            'skill_hints.*.level.min' => 'Hint level must be at least 1',
            'skill_hints.*.level.max' => 'Hint level cannot exceed 5',

            // Support deck
            'support_deck.array' => 'Support deck must be an object',
            'support_deck.cards.array' => 'Support deck cards must be an array',
            'support_deck.cards.max' => 'Support deck cannot have more than 6 cards',
            'support_deck.cards.*.id.required_with' => 'Card ID is required',
            'support_deck.cards.*.id.integer' => 'Card ID must be an integer',
            'support_deck.cards.*.bond.required_with' => 'Card bond level is required',
            'support_deck.cards.*.bond.integer' => 'Bond level must be an integer',
            'support_deck.cards.*.bond.min' => 'Bond level cannot be negative',
            'support_deck.cards.*.bond.max' => 'Bond level cannot exceed 100',
            'support_deck.cards.*.facility.required_with' => 'Card facility is required',
            'support_deck.cards.*.facility.in' => 'Invalid facility. Valid values: speed, stamina, power, guts, wisdom, friend',

            // Facility levels
            'facility_levels.array' => 'Facility levels must be an object',
            'facility_levels.*.integer' => 'Facility level must be an integer',
            'facility_levels.*.min' => 'Facility level must be at least 1',
            'facility_levels.*.max' => 'Facility level cannot exceed 5',

            // Upcoming races
            'upcoming_races.array' => 'Upcoming races must be an array',
            'upcoming_races.*.id.required_with' => 'Race ID is required',
            'upcoming_races.*.id.integer' => 'Race ID must be an integer',
            'upcoming_races.*.distance.required_with' => 'Race distance is required',
            'upcoming_races.*.distance.in' => 'Invalid distance. Valid values: sprint, mile, medium, long',
            'upcoming_races.*.turn.required_with' => 'Race turn is required',
            'upcoming_races.*.turn.integer' => 'Race turn must be an integer',
            'upcoming_races.*.turn.min' => 'Race turn must be at least 1',
            'upcoming_races.*.turn.max' => 'Race turn cannot exceed 78',

            // Scenario
            'scenario.string' => 'Scenario must be a string',
            'scenario.max' => 'Scenario name cannot exceed 50 characters',
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
            'storage_mode' => 'storage mode',
            'turn_number' => 'turn number',
            'phase' => 'career phase',
            'stats.speed' => 'speed stat',
            'stats.stamina' => 'stamina stat',
            'stats.power' => 'power stat',
            'stats.guts' => 'guts stat',
            'stats.wisdom' => 'wisdom stat',
            'sp_available' => 'available SP',
            'energy' => 'energy level',
            'mood' => 'mood',
            'acquired_skills' => 'acquired skills',
            'skill_hints' => 'skill hints',
            'support_deck' => 'support deck',
            'support_deck.cards' => 'support cards',
            'facility_levels' => 'facility levels',
            'upcoming_races' => 'upcoming races',
            'scenario' => 'scenario',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * Sets default values for optional fields.
     */
    protected function prepareForValidation(): void
    {
        // Set defaults for optional arrays with proper type casting
        $facilityLevels = $this->input('facility_levels');
        $facilityLevelsArray = is_array($facilityLevels) ? $facilityLevels : [];

        $this->merge([
            'acquired_skills' => $this->input('acquired_skills', []),
            'skill_hints' => $this->input('skill_hints', []),
            'support_deck' => $this->input('support_deck', ['cards' => []]),
            'facility_levels' => array_merge([
                'speed' => 1,
                'stamina' => 1,
                'power' => 1,
                'guts' => 1,
                'wisdom' => 1,
            ], $facilityLevelsArray),
            'upcoming_races' => $this->input('upcoming_races', []),
        ]);
    }
}
