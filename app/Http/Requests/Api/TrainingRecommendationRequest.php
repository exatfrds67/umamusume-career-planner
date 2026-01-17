<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class TrainingRecommendationRequest extends FormRequest
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
            'support_cards' => ['sometimes', 'array', 'max:6'],
            'support_cards.*.id' => ['required_with:support_cards', 'integer'],
            'support_cards.*.limit_break_level' => ['sometimes', 'integer', 'min:0', 'max:4'],
            'support_cards.*.friendship_level' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'participants' => ['sometimes', 'integer', 'min:0', 'max:3'],
            'teammates_present' => ['sometimes', 'array', 'max:3'],
            'spirit_burst_gauge' => ['sometimes', 'integer', 'min:0', 'max:4'],
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
            'character_id.required' => 'Character ID is required for training recommendations.',
            'character_id.exists' => 'The specified character does not exist.',
            'support_cards.max' => 'Maximum 6 support cards allowed.',
            'support_cards.*.limit_break_level.max' => 'Limit break level cannot exceed 4.',
            'support_cards.*.friendship_level.max' => 'Friendship level cannot exceed 100.',
            'participants.max' => 'Maximum 3 participants allowed in friendship training.',
            'teammates_present.max' => 'Maximum 3 teammates can be present.',
            'spirit_burst_gauge.max' => 'Spirit Burst gauge cannot exceed 4.',
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
            'support_cards' => 'support cards',
            'participants' => 'participants',
            'teammates_present' => 'teammates',
            'spirit_burst_gauge' => 'Spirit Burst gauge',
        ];
    }
}
