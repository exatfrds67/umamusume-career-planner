<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Models\Character;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Batch Training Prediction Request
 *
 * Validates batch training prediction requests for multiple training types
 */
class BatchTrainingPredictionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Verify the character belongs to the authenticated user
        $characterId = $this->input('character_id');

        if (! $characterId) {
            return false;
        }

        $user = $this->user();

        if (! $user) {
            return false;
        }

        // Admins can view all characters
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return Character::where('id', $characterId)->exists();
        }

        // Regular users can only view their own characters
        return method_exists($user, 'characters') && $user->characters()->where('id', $characterId)->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'character_id' => [
                'required',
                'integer',
                'exists:ucp_characters,id',
            ],
            'training_types' => [
                'nullable',
                'array',
                'min:1',
                'max:5',
            ],
            'training_types.*' => [
                'nullable',
                'string',
                Rule::in(['speed', 'stamina', 'power', 'guts', 'wit']),
            ],
            'support_cards' => [
                'nullable',
                'array',
            ],
            'support_cards.*' => [
                'integer',
                'exists:ucp_support_cards,id',
            ],
            'participants' => [
                'nullable',
                'integer',
                'min:0',
                'max:6',
            ],
            'teammates_present' => [
                'nullable',
                'array',
            ],
            'spirit_burst_gauge' => [
                'nullable',
                'integer',
                'min:0',
                'max:4',
            ],
            'team_stat_ranks' => [
                'nullable',
                'array',
            ],
            'include_recommendations' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'character_id.required' => 'Character ID is required for batch predictions.',
            'character_id.exists' => 'The selected character does not exist or does not belong to you.',
            'training_types.required' => 'At least one training type is required.',
            'training_types.array' => 'Training types must be provided as an array.',
            'training_types.min' => 'At least one training type is required.',
            'training_types.max' => 'Maximum 5 training types allowed.',
            'training_types.*.in' => 'Each training type must be one of: speed, stamina, power, guts, wit.',
            'support_cards.array' => 'Support cards must be provided as an array.',
            'support_cards.*.exists' => 'One or more support cards do not exist.',
            'participants.integer' => 'Participants must be a number.',
            'participants.min' => 'Participants cannot be negative.',
            'participants.max' => 'Maximum 6 participants allowed.',
            'spirit_burst_gauge.min' => 'Spirit Burst gauge cannot be negative.',
            'spirit_burst_gauge.max' => 'Spirit Burst gauge maximum is 4.',
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
            'character_id' => 'character',
            'training_types' => 'training types',
            'support_cards' => 'support cards',
            'participants' => 'participants',
            'spirit_burst_gauge' => 'Spirit Burst gauge',
        ];
    }
}
