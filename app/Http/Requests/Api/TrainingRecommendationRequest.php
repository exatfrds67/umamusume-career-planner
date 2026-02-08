<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Training Recommendation Request
 *
 * Validates training recommendation requests with goal-based optimization
 */
class TrainingRecommendationRequest extends FormRequest
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

        return method_exists($user, 'characters') && $user->characters()->where('id', $characterId)->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();

        return [
            'character_id' => [
                'required',
                'integer',
                Rule::exists('ucp_characters', 'id')->where(function ($query) use ($user) {
                    if ($user) {
                        $query->where('user_id', $user->id);
                    }
                }),
            ],
            'goal_stats' => [
                'nullable',
                'array',
            ],
            'goal_stats.speed' => [
                'nullable',
                'integer',
                'min:0',
                'max:1200',
            ],
            'goal_stats.stamina' => [
                'nullable',
                'integer',
                'min:0',
                'max:1200',
            ],
            'goal_stats.power' => [
                'nullable',
                'integer',
                'min:0',
                'max:1200',
            ],
            'goal_stats.guts' => [
                'nullable',
                'integer',
                'min:0',
                'max:1200',
            ],
            'goal_stats.wit' => [
                'nullable',
                'integer',
                'min:0',
                'max:1200',
            ],
            'turns_remaining' => [
                'nullable',
                'integer',
                'min:1',
                'max:70',
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
            'character_id.required' => 'Character ID is required for recommendations.',
            'character_id.exists' => 'The selected character does not exist or does not belong to you.',
            'goal_stats.array' => 'Goal stats must be provided as an object.',
            'goal_stats.*.integer' => 'Each goal stat must be a number.',
            'goal_stats.*.min' => 'Goal stats cannot be negative.',
            'goal_stats.*.max' => 'Goal stats cannot exceed 1200.',
            'turns_remaining.integer' => 'Turns remaining must be a number.',
            'turns_remaining.min' => 'At least 1 turn must remain.',
            'turns_remaining.max' => 'Maximum 70 turns allowed.',
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
            'goal_stats' => 'goal stats',
            'turns_remaining' => 'turns remaining',
            'support_cards' => 'support cards',
            'participants' => 'participants',
            'spirit_burst_gauge' => 'Spirit Burst gauge',
        ];
    }
}
