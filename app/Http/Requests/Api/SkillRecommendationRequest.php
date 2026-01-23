<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SkillRecommendationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'character_id' => ['required', 'integer', 'exists:ucp_characters,id'],
            'skill_context' => ['sometimes', 'array'],
            'skill_context.available_sp' => ['sometimes', 'integer', 'min:0'],
            'skill_context.race_preferences' => ['sometimes', 'array'],
            'skill_context.race_preferences.preferred_distance' => ['sometimes', 'string', 'in:short,mile,medium,long'],
            'skill_context.race_preferences.preferred_surface' => ['sometimes', 'string', 'in:turf,dirt'],
            'skill_context.race_preferences.preferred_running_style' => ['sometimes', 'string', 'in:runner,leader,betweener,chaser'],
            'skill_context.build_strategy' => ['sometimes', 'string', 'max:1000'],
            'skill_context.upcoming_races' => ['sometimes', 'array'],
            'skill_context.upcoming_races.*.name' => ['required_with:skill_context.upcoming_races', 'string', 'max:255'],
            'skill_context.upcoming_races.*.distance_category' => ['sometimes', 'string', 'in:short,mile,medium,long'],
            'skill_context.upcoming_races.*.surface' => ['sometimes', 'string', 'in:turf,dirt'],
            'skill_context.upcoming_races.*.turns_until' => ['sometimes', 'integer', 'min:0'],
            'skill_context.additional_context' => ['sometimes', 'string', 'max:1000'],
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
            'character_id.required' => 'Character ID is required for skill recommendations.',
            'character_id.exists' => 'The specified character does not exist.',
            'skill_context.available_sp.min' => 'Available SP cannot be negative.',
            'skill_context.race_preferences.preferred_distance.in' => 'Preferred distance must be one of: short, mile, medium, long.',
            'skill_context.race_preferences.preferred_surface.in' => 'Preferred surface must be either turf or dirt.',
            'skill_context.race_preferences.preferred_running_style.in' => 'Preferred running style must be one of: runner, leader, betweener, chaser.',
            'skill_context.build_strategy.max' => 'Build strategy cannot exceed 1000 characters.',
            'skill_context.upcoming_races.*.name.required_with' => 'Race name is required for each upcoming race.',
            'skill_context.upcoming_races.*.distance_category.in' => 'Distance category must be one of: short, mile, medium, long.',
            'skill_context.upcoming_races.*.surface.in' => 'Surface must be either turf or dirt.',
            'skill_context.upcoming_races.*.turns_until.min' => 'Turns until race cannot be negative.',
            'skill_context.additional_context.max' => 'Additional context cannot exceed 1000 characters.',
        ];
    }

    /**
     * Get custom attribute names for error messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'character_id' => 'character',
            'skill_context' => 'skill context',
            'skill_context.available_sp' => 'available SP',
            'skill_context.race_preferences' => 'race preferences',
            'skill_context.race_preferences.preferred_distance' => 'preferred distance',
            'skill_context.race_preferences.preferred_surface' => 'preferred surface',
            'skill_context.race_preferences.preferred_running_style' => 'preferred running style',
            'skill_context.build_strategy' => 'build strategy',
            'skill_context.upcoming_races' => 'upcoming races',
            'skill_context.additional_context' => 'additional context',
        ];
    }
}
