<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Models\Character;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TrainingSimulationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $characterId = $this->input('character_id');
        if (! $characterId) {
            return false;
        }

        $user = $this->user();
        if (! $user) {
            return false;
        }

        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return Character::where('id', $characterId)->exists();
        }

        return method_exists($user, 'characters') && $user->characters()->where('id', $characterId)->exists();
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'character_id' => ['required', 'integer', 'exists:ucp_characters,id'],
            'training_type' => ['required', 'string', Rule::in(['speed', 'stamina', 'power', 'guts', 'wit', 'rest'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'character_id.required' => 'Character is required for simulation.',
            'character_id.exists' => 'The selected character does not exist or does not belong to you.',
            'training_type.required' => 'Training type is required for simulation.',
            'training_type.in' => 'Training type must be speed, stamina, power, guts, wit, or rest.',
        ];
    }
}
