<?php

declare(strict_types=1);

namespace App\Http\Requests\Character;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCharacterRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'scenario_type' => ['required', 'string', Rule::in(['ura_finale', 'unity_cup'])],
            'current_stats' => ['required', 'array'],
            'current_stats.speed' => ['required', 'integer', 'min:0', 'max:1200'],
            'current_stats.stamina' => ['required', 'integer', 'min:0', 'max:1200'],
            'current_stats.power' => ['required', 'integer', 'min:0', 'max:1200'],
            'current_stats.guts' => ['required', 'integer', 'min:0', 'max:1200'],
            'current_stats.wit' => ['required', 'integer', 'min:0', 'max:1200'],
            'stat_priorities' => ['required', 'array'],
            'stat_priorities.*' => ['integer', 'min:0'],
        ];
    }
}
