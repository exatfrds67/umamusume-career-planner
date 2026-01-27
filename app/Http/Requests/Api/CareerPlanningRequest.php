<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CareerPlanningRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
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
            'character_id' => ['required', 'integer', 'exists:ucp_characters,id'],
            'planning_context' => ['sometimes', 'array'],
            'planning_context.career_id' => ['sometimes', 'integer', 'exists:ucp_careers,id'],
            'planning_context.goal_horizon_turns' => ['sometimes', 'integer', 'min:1', 'max:120'],
            'planning_context.focus_stats' => ['sometimes', 'array'],
            'planning_context.focus_stats.*' => ['required_with:planning_context.focus_stats', 'string', 'in:speed,stamina,power,guts,wit'],
            'planning_context.target_grade' => ['sometimes', 'string', 'max:20'],
            'planning_context.preferred_races' => ['sometimes', 'array'],
            'planning_context.preferred_races.*.name' => ['required_with:planning_context.preferred_races', 'string', 'max:255'],
            'planning_context.preferred_races.*.distance_category' => ['sometimes', 'string', 'in:short,mile,medium,long'],
            'planning_context.preferred_races.*.surface' => ['sometimes', 'string', 'in:turf,dirt'],
            'planning_context.preferred_races.*.turns_until' => ['sometimes', 'integer', 'min:0'],
            'planning_context.additional_context' => ['sometimes', 'string', 'max:1000'],
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
            'character_id.required' => 'Character ID is required for career planning.',
            'character_id.exists' => 'The specified character does not exist.',
            'planning_context.career_id.exists' => 'The specified career does not exist.',
            'planning_context.goal_horizon_turns.min' => 'Goal horizon must be at least 1 turn.',
            'planning_context.goal_horizon_turns.max' => 'Goal horizon cannot exceed 120 turns.',
            'planning_context.focus_stats.*.in' => 'Focus stats must be one of: speed, stamina, power, guts, wit.',
            'planning_context.preferred_races.*.name.required_with' => 'Race name is required for each preferred race.',
            'planning_context.preferred_races.*.distance_category.in' => 'Distance category must be one of: short, mile, medium, long.',
            'planning_context.preferred_races.*.surface.in' => 'Surface must be either turf or dirt.',
            'planning_context.preferred_races.*.turns_until.min' => 'Turns until race cannot be negative.',
            'planning_context.additional_context.max' => 'Additional context cannot exceed 1000 characters.',
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
            'planning_context' => 'planning context',
            'planning_context.career_id' => 'career',
            'planning_context.goal_horizon_turns' => 'goal horizon',
            'planning_context.focus_stats' => 'focus stats',
            'planning_context.target_grade' => 'target grade',
            'planning_context.preferred_races' => 'preferred races',
            'planning_context.additional_context' => 'additional context',
        ];
    }
}
