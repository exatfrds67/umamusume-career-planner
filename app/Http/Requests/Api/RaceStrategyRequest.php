<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class RaceStrategyRequest extends FormRequest
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
            'race_data' => ['required', 'array'],
            'race_data.race_id' => ['sometimes', 'integer', 'exists:ucp_races,id'],
            'race_data.race_name' => ['required', 'string', 'max:255'],
            'race_data.race_grade' => ['sometimes', 'string', 'in:G1,G2,G3,OP,Pre-OP,Debut,Make Debut'],
            'race_data.distance_meters' => ['sometimes', 'integer', 'min:1000', 'max:4000'],
            'race_data.distance_category' => ['sometimes', 'string', 'in:short,mile,medium,long'],
            'race_data.surface' => ['sometimes', 'string', 'in:turf,dirt'],
            'race_data.track_type' => ['sometimes', 'string', 'in:left,right,straight'],
            'race_data.weather' => ['sometimes', 'string', 'max:50'],
            'race_data.track_condition' => ['sometimes', 'string', 'max:50'],
            'race_data.field_size' => ['sometimes', 'integer', 'min:1', 'max:18'],
            'race_data.available_skills' => ['sometimes', 'array'],
            'race_data.available_skills.*.name' => ['required_with:race_data.available_skills', 'string', 'max:255'],
            'race_data.available_skills.*.skill_type' => ['sometimes', 'string', 'max:100'],
            'race_data.available_skills.*.description' => ['sometimes', 'string', 'max:1000'],
            'race_data.race_conditions' => ['sometimes'],
            'race_data.is_ura_finale_race' => ['sometimes', 'boolean'],
            'race_data.ura_finale_stage' => ['sometimes', 'string', 'max:100'],
            'race_data.ura_finale_requirements' => ['sometimes', 'array'],
            'race_data.is_unity_cup_match' => ['sometimes', 'boolean'],
            'race_data.unity_cup_opponent_rank' => ['sometimes', 'string', 'max:100'],
            'race_data.past_race_performance' => ['sometimes', 'boolean'],
            'race_data.strategic_importance' => ['sometimes', 'string', 'max:1000'],
            'race_data.additional_context' => ['sometimes', 'string', 'max:1000'],
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
            'character_id.required' => 'Character ID is required for race strategy.',
            'character_id.exists' => 'The specified character does not exist.',
            'race_data.required' => 'Race data is required.',
            'race_data.race_name.required' => 'Race name is required.',
            'race_data.race_grade.in' => 'Race grade must be one of: G1, G2, G3, OP, Pre-OP, Debut, Make Debut.',
            'race_data.distance_meters.min' => 'Distance must be at least 1000 meters.',
            'race_data.distance_meters.max' => 'Distance cannot exceed 4000 meters.',
            'race_data.distance_category.in' => 'Distance category must be one of: short, mile, medium, long.',
            'race_data.surface.in' => 'Surface must be either turf or dirt.',
            'race_data.track_type.in' => 'Track type must be one of: left, right, straight.',
            'race_data.field_size.min' => 'Field size must be at least 1.',
            'race_data.field_size.max' => 'Field size cannot exceed 18 horses.',
            'race_data.available_skills.*.name.required_with' => 'Skill name is required for each available skill.',
            'race_data.strategic_importance.max' => 'Strategic importance cannot exceed 1000 characters.',
            'race_data.additional_context.max' => 'Additional context cannot exceed 1000 characters.',
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
            'race_data' => 'race data',
            'race_data.race_name' => 'race name',
            'race_data.race_grade' => 'race grade',
            'race_data.distance_meters' => 'distance',
            'race_data.distance_category' => 'distance category',
            'race_data.surface' => 'surface',
            'race_data.track_type' => 'track type',
            'race_data.field_size' => 'field size',
            'race_data.strategic_importance' => 'strategic importance',
            'race_data.additional_context' => 'additional context',
        ];
    }
}
