<?php

declare(strict_types=1);

namespace App\Http\Requests\Character;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCharacterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'scenario_type' => ['sometimes', 'string', Rule::in(['ura_finale', 'unity_cup'])],
            'current_stats' => ['sometimes', 'array'],
            'current_stats.speed' => ['sometimes', 'integer', 'min:0', 'max:1200'],
            'current_stats.stamina' => ['sometimes', 'integer', 'min:0', 'max:1200'],
            'current_stats.power' => ['sometimes', 'integer', 'min:0', 'max:1200'],
            'current_stats.guts' => ['sometimes', 'integer', 'min:0', 'max:1200'],
            'current_stats.wit' => ['sometimes', 'integer', 'min:0', 'max:1200'],
        ];
    }
}
