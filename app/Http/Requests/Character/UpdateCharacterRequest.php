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
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $currentStats = $this->input('current_stats');
        if ($this->has('current_stats') && \is_array($currentStats)) {
            $sanitized = [];
            foreach ($currentStats as $key => $value) {
                // Strip non-numeric characters and convert to integer
                // Ensure value is scalar before casting to string
                if (\is_scalar($value)) {
                    $sanitized[$key] = (int) preg_replace('/[^0-9]/', '', (string) $value);
                } else {
                    $sanitized[$key] = 0;
                }
            }
            $this->merge(['current_stats' => $sanitized]);
        }
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
            'current_stats.speed' => ['sometimes', 'integer', 'min:0', 'max:2000'],
            'current_stats.stamina' => ['sometimes', 'integer', 'min:0', 'max:2000'],
            'current_stats.power' => ['sometimes', 'integer', 'min:0', 'max:2000'],
            'current_stats.guts' => ['sometimes', 'integer', 'min:0', 'max:2000'],
            'current_stats.wit' => ['sometimes', 'integer', 'min:0', 'max:2000'],
        ];
    }
}
