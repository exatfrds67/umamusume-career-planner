<?php

namespace App\Http\Requests;

use App\Models\Character;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCharacterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        $character = $this->route('character');

        if ($user === null || ! $character instanceof Character) {
            return false;
        }

        return $user->id === $character->user_id;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('stats') && is_array($this->stats)) {
            $sanitized = [];
            foreach ($this->stats as $key => $value) {
                // Strip non-numeric characters and convert to integer
                $sanitized[$key] = (int) preg_replace('/[^0-9]/', '', (string) $value);
            }
            $this->merge(['stats' => $sanitized]);
        }

        if ($this->has('goals.target_stats') && is_array($this->input('goals.target_stats'))) {
            $targetStats = $this->input('goals.target_stats');
            $sanitized = [];
            foreach ($targetStats as $key => $value) {
                if ($value !== null) {
                    $sanitized[$key] = (int) preg_replace('/[^0-9]/', '', (string) $value);
                }
            }
            $this->merge(['goals' => array_merge($this->input('goals', []), ['target_stats' => $sanitized])]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'scenario_type' => ['sometimes', 'required', Rule::in(['ura_finale', 'unity_cup'])],
            'career_stage' => ['sometimes', 'required', Rule::in(['junior', 'classic', 'senior'])],
            'current_turn' => ['sometimes', 'required', 'integer', 'min:1', 'max:78'],

            // Stats validation (0-2000 range with integer enforcement)
            'stats' => ['sometimes', 'required', 'array'],
            'stats.speed' => ['required_with:stats', 'integer', 'min:0', 'max:2000'],
            'stats.stamina' => ['required_with:stats', 'integer', 'min:0', 'max:2000'],
            'stats.power' => ['required_with:stats', 'integer', 'min:0', 'max:2000'],
            'stats.guts' => ['required_with:stats', 'integer', 'min:0', 'max:2000'],
            'stats.wit' => ['required_with:stats', 'integer', 'min:0', 'max:2000'],

            // Energy and mood
            'energy_level' => ['sometimes', 'required', 'integer', 'min:0', 'max:100'],
            'mood_status' => ['sometimes', 'required', Rule::in(['awful', 'bad', 'normal', 'good', 'great'])],

            // Goals validation
            'goals' => ['sometimes', 'nullable', 'array'],
            'goals.target_stats' => ['sometimes', 'nullable', 'array'],
            'goals.target_stats.speed' => ['nullable', 'integer', 'min:0', 'max:2000'],
            'goals.target_stats.stamina' => ['nullable', 'integer', 'min:0', 'max:2000'],
            'goals.target_stats.power' => ['nullable', 'integer', 'min:0', 'max:2000'],
            'goals.target_stats.guts' => ['nullable', 'integer', 'min:0', 'max:2000'],
            'goals.target_stats.wit' => ['nullable', 'integer', 'min:0', 'max:2000'],
            'goals.race_objectives' => ['sometimes', 'nullable', 'array'],
            'goals.notes' => ['sometimes', 'nullable', 'string', 'max:5000'],

            // Growth rates
            'growth_rates' => ['sometimes', 'nullable', 'array'],
            'growth_rates.speed' => ['nullable', 'integer', Rule::in([10, 20, 30])],
            'growth_rates.stamina' => ['nullable', 'integer', Rule::in([10, 20, 30])],
            'growth_rates.power' => ['nullable', 'integer', Rule::in([10, 20, 30])],
            'growth_rates.guts' => ['nullable', 'integer', Rule::in([10, 20, 30])],
            'growth_rates.wit' => ['nullable', 'integer', Rule::in([10, 20, 30])],

            // Facility levels (Unity Cup)
            'facility_levels' => ['sometimes', 'nullable', 'array'],
            'facility_levels.speed' => ['nullable', 'integer', 'min:1', 'max:5'],
            'facility_levels.stamina' => ['nullable', 'integer', 'min:1', 'max:5'],
            'facility_levels.power' => ['nullable', 'integer', 'min:1', 'max:5'],
            'facility_levels.guts' => ['nullable', 'integer', 'min:1', 'max:5'],
            'facility_levels.wit' => ['nullable', 'integer', 'min:1', 'max:5'],
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
            'name.required' => 'Character name is required.',
            'name.max' => 'Character name cannot exceed 255 characters.',

            'stats.*.min' => 'Stat values must be at least 0.',
            'stats.*.max' => 'Stat values cannot exceed 2000.',
            'stats.*.integer' => 'The :attribute field must be an integer.',

            'energy_level.min' => 'Energy level must be at least 0%.',
            'energy_level.max' => 'Energy level cannot exceed 100%.',

            'goals.target_stats.*.min' => 'Target stat values must be at least 0.',
            'goals.target_stats.*.max' => 'Target stat values cannot exceed 2000.',

            'growth_rates.*.in' => 'Growth rate bonuses must be 10%, 20%, or 30%.',

            'facility_levels.*.min' => 'Facility levels must be at least 1.',
            'facility_levels.*.max' => 'Facility levels cannot exceed 5.',
        ];
    }
}
