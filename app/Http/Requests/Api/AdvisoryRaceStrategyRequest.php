<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Advisory Race Strategy Request
 *
 * Validates incoming requests for AI-powered race strategy generation.
 * Ensures all required context data is present and valid for generating
 * intelligent race strategy recommendations.
 *
 * **Validates: Requirements 3.3 (Race Strategy Generation)**
 *
 * @see \App\Http\Controllers\Api\AdvisoryController::getRaceStrategy()
 * @see \App\Services\TrainingAdvisoryService::getRaceStrategy()
 */
class AdvisoryRaceStrategyRequest extends FormRequest
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
            // Character identification
            'character_id' => ['required'],
            'race_id' => ['required', 'integer', 'min:1'],

            // Character stats (required)
            'stats' => ['required', 'array'],
            'stats.speed' => ['required', 'integer', 'min:0', 'max:1500'],
            'stats.stamina' => ['required', 'integer', 'min:0', 'max:1500'],
            'stats.power' => ['required', 'integer', 'min:0', 'max:1500'],
            'stats.guts' => ['required', 'integer', 'min:0', 'max:1500'],
            'stats.wisdom' => ['required', 'integer', 'min:0', 'max:1500'],

            // Skills (array of skill IDs)
            'skills' => ['sometimes', 'array'],
            'skills.*' => ['integer', 'min:1'],

            // Aptitudes (required for strategy calculation)
            'aptitudes' => ['required', 'array'],
            'aptitudes.distance_sprint' => ['sometimes', 'string', Rule::in(['G', 'F', 'E', 'D', 'C', 'B', 'A', 'S'])],
            'aptitudes.distance_mile' => ['sometimes', 'string', Rule::in(['G', 'F', 'E', 'D', 'C', 'B', 'A', 'S'])],
            'aptitudes.distance_medium' => ['sometimes', 'string', Rule::in(['G', 'F', 'E', 'D', 'C', 'B', 'A', 'S'])],
            'aptitudes.distance_long' => ['sometimes', 'string', Rule::in(['G', 'F', 'E', 'D', 'C', 'B', 'A', 'S'])],
            'aptitudes.surface_turf' => ['sometimes', 'string', Rule::in(['G', 'F', 'E', 'D', 'C', 'B', 'A', 'S'])],
            'aptitudes.surface_dirt' => ['sometimes', 'string', Rule::in(['G', 'F', 'E', 'D', 'C', 'B', 'A', 'S'])],
            'aptitudes.style_escape' => ['sometimes', 'string', Rule::in(['G', 'F', 'E', 'D', 'C', 'B', 'A', 'S'])],
            'aptitudes.style_lead' => ['sometimes', 'string', Rule::in(['G', 'F', 'E', 'D', 'C', 'B', 'A', 'S'])],
            'aptitudes.style_pace' => ['sometimes', 'string', Rule::in(['G', 'F', 'E', 'D', 'C', 'B', 'A', 'S'])],
            'aptitudes.style_chase' => ['sometimes', 'string', Rule::in(['G', 'F', 'E', 'D', 'C', 'B', 'A', 'S'])],

            // Optional race details (if not fetching from database)
            'race_details' => ['sometimes', 'array'],
            'race_details.distance' => [
                'sometimes',
                'string',
                Rule::in(['sprint', 'mile', 'medium', 'long']),
            ],
            'race_details.distance_meters' => ['sometimes', 'integer', 'min:1000', 'max:4000'],
            'race_details.surface' => ['sometimes', 'string', Rule::in(['turf', 'dirt'])],
            'race_details.weather' => ['sometimes', 'string', 'max:50'],
            'race_details.track_condition' => ['sometimes', 'string', Rule::in(['firm', 'good', 'soft', 'heavy'])],
            'race_details.competition_level' => ['sometimes', 'string', Rule::in(['G1', 'G2', 'G3', 'OP', 'Pre-OP', 'Debut'])],
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
            // Character identification
            'character_id.required' => 'Character ID is required',
            'race_id.required' => 'Race ID is required',
            'race_id.integer' => 'Race ID must be an integer',
            'race_id.min' => 'Race ID must be at least 1',

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

            // Skills
            'skills.array' => 'Skills must be an array',
            'skills.*.integer' => 'Skill ID must be an integer',
            'skills.*.min' => 'Skill ID must be at least 1',

            // Aptitudes
            'aptitudes.required' => 'Aptitudes are required',
            'aptitudes.array' => 'Aptitudes must be an object',
            'aptitudes.*.in' => 'Invalid aptitude grade. Valid values: G, F, E, D, C, B, A, S',

            // Race details
            'race_details.array' => 'Race details must be an object',
            'race_details.distance.in' => 'Invalid distance. Valid values: sprint, mile, medium, long',
            'race_details.distance_meters.integer' => 'Distance meters must be an integer',
            'race_details.distance_meters.min' => 'Distance must be at least 1000 meters',
            'race_details.distance_meters.max' => 'Distance cannot exceed 4000 meters',
            'race_details.surface.in' => 'Invalid surface. Valid values: turf, dirt',
            'race_details.weather.max' => 'Weather description cannot exceed 50 characters',
            'race_details.track_condition.in' => 'Invalid track condition. Valid values: firm, good, soft, heavy',
            'race_details.competition_level.in' => 'Invalid competition level. Valid values: G1, G2, G3, OP, Pre-OP, Debut',
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
            'character_id' => 'character ID',
            'race_id' => 'race ID',
            'stats.speed' => 'speed stat',
            'stats.stamina' => 'stamina stat',
            'stats.power' => 'power stat',
            'stats.guts' => 'guts stat',
            'stats.wisdom' => 'wisdom stat',
            'skills' => 'skills',
            'aptitudes' => 'aptitudes',
            'aptitudes.distance_sprint' => 'sprint distance aptitude',
            'aptitudes.distance_mile' => 'mile distance aptitude',
            'aptitudes.distance_medium' => 'medium distance aptitude',
            'aptitudes.distance_long' => 'long distance aptitude',
            'aptitudes.surface_turf' => 'turf surface aptitude',
            'aptitudes.surface_dirt' => 'dirt surface aptitude',
            'aptitudes.style_escape' => 'escape style aptitude',
            'aptitudes.style_lead' => 'lead style aptitude',
            'aptitudes.style_pace' => 'pace style aptitude',
            'aptitudes.style_chase' => 'chase style aptitude',
            'race_details' => 'race details',
            'race_details.distance' => 'race distance',
            'race_details.distance_meters' => 'race distance in meters',
            'race_details.surface' => 'race surface',
            'race_details.weather' => 'weather',
            'race_details.track_condition' => 'track condition',
            'race_details.competition_level' => 'competition level',
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
        $raceDetails = $this->input('race_details');
        $raceDetailsArray = is_array($raceDetails) ? $raceDetails : [];

        $this->merge([
            'skills' => $this->input('skills', []),
            'race_details' => array_merge([
                'distance' => 'medium',
                'distance_meters' => 2000,
                'surface' => 'turf',
                'weather' => 'clear',
                'track_condition' => 'good',
                'competition_level' => 'G3',
            ], $raceDetailsArray),
        ]);

        // Set default aptitudes if not provided with proper type casting
        $aptitudes = $this->input('aptitudes');
        $aptitudesArray = is_array($aptitudes) ? $aptitudes : [];
        $defaultAptitudes = [
            'distance_sprint' => 'C',
            'distance_mile' => 'C',
            'distance_medium' => 'C',
            'distance_long' => 'C',
            'surface_turf' => 'C',
            'surface_dirt' => 'C',
            'style_escape' => 'C',
            'style_lead' => 'C',
            'style_pace' => 'C',
            'style_chase' => 'C',
        ];

        $this->merge([
            'aptitudes' => array_merge($defaultAptitudes, $aptitudesArray),
        ]);
    }
}
