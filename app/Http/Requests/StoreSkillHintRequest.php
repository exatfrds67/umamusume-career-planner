<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSkillHintRequest extends FormRequest
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
            'skill_id' => ['required', 'integer', 'exists:ucp_skills,id'],
            'source_type' => ['required', 'string', Rule::in(['support_card', 'event', 'inheritance', 'training'])],
            'source_name' => ['required', 'string', 'max:255'],
            'source_id' => ['nullable', 'integer'],
            'turn_obtained' => ['nullable', 'integer', 'min:1', 'max:72'],
            'career_phase' => ['nullable', 'string', Rule::in(['junior', 'classic', 'senior'])],
            'guaranteed_hint' => ['nullable', 'boolean'],
            'training_type' => ['nullable', 'string', Rule::in(['speed', 'stamina', 'power', 'guts', 'wit'])],
            'training_participants' => ['nullable', 'array'],
            'training_participants.*' => ['integer'],
            'friendship_training' => ['nullable', 'boolean'],
            'hint_metadata' => ['nullable', 'array'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'character_id.required' => 'Character ID is required.',
            'character_id.exists' => 'The specified character does not exist.',
            'skill_id.required' => 'Skill ID is required.',
            'skill_id.exists' => 'The specified skill does not exist.',
            'source_type.required' => 'Hint source type is required.',
            'source_type.in' => 'Invalid hint source type. Must be one of: support_card, event, inheritance, training.',
            'source_name.required' => 'Source name is required.',
            'turn_obtained.min' => 'Turn number must be at least 1.',
            'turn_obtained.max' => 'Turn number cannot exceed 72.',
            'career_phase.in' => 'Invalid career phase. Must be one of: junior, classic, senior.',
            'training_type.in' => 'Invalid training type. Must be one of: speed, stamina, power, guts, wit.',
        ];
    }

    /**
     * Get custom attribute names for error messages.
     */
    public function attributes(): array
    {
        return [
            'character_id' => 'character',
            'skill_id' => 'skill',
            'source_type' => 'hint source type',
            'source_name' => 'source name',
            'turn_obtained' => 'turn number',
            'career_phase' => 'career phase',
            'training_type' => 'training type',
        ];
    }
}
