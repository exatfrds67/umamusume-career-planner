<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCharacterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
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
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'scenario_type' => ['required', 'in:ura_finale,unity_cup'],

            // External API data (optional)
            'external_source_id' => ['nullable', 'string', 'max:50'],
            'external_source' => ['nullable', 'string', 'max:100'],
            'avatar_url' => ['nullable', 'string', 'max:500'],

            // Stats validation (0-2000 range with integer enforcement)
            'stats' => ['required', 'array'],
            'stats.speed' => ['required', 'integer', 'min:0', 'max:2000'],
            'stats.stamina' => ['required', 'integer', 'min:0', 'max:2000'],
            'stats.power' => ['required', 'integer', 'min:0', 'max:2000'],
            'stats.guts' => ['required', 'integer', 'min:0', 'max:2000'],
            'stats.wit' => ['required', 'integer', 'min:0', 'max:2000'],

            // Aptitudes validation
            'aptitudes' => ['required', 'array'],
            'aptitudes.distance' => ['required', 'array'],
            'aptitudes.distance.sprint' => ['required', 'in:G,F,E,D,C,B,A,S,SS'],
            'aptitudes.distance.mile' => ['required', 'in:G,F,E,D,C,B,A,S,SS'],
            'aptitudes.distance.medium' => ['required', 'in:G,F,E,D,C,B,A,S,SS'],
            'aptitudes.distance.long' => ['required', 'in:G,F,E,D,C,B,A,S,SS'],

            'aptitudes.surface' => ['required', 'array'],
            'aptitudes.surface.turf' => ['required', 'in:G,F,E,D,C,B,A,S,SS'],
            'aptitudes.surface.dirt' => ['required', 'in:G,F,E,D,C,B,A,S,SS'],

            'aptitudes.style' => ['required', 'array'],
            'aptitudes.style.front_runner' => ['required', 'in:G,F,E,D,C,B,A,S,SS'],
            'aptitudes.style.pace_chaser' => ['required', 'in:G,F,E,D,C,B,A,S,SS'],
            'aptitudes.style.late_surger' => ['required', 'in:G,F,E,D,C,B,A,S,SS'],
            'aptitudes.style.end_closer' => ['required', 'in:G,F,E,D,C,B,A,S,SS'],
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
            'name.max' => 'Character name must not exceed 100 characters.',

            'scenario_type.required' => 'Please select a scenario type.',
            'scenario_type.in' => 'Invalid scenario type selected.',

            'stats.*.required' => 'All stat values are required.',
            'stats.*.integer' => 'The :attribute field must be an integer.',
            'stats.*.min' => 'The :attribute field must be at least 0.',
            'stats.*.max' => 'The :attribute field cannot exceed 2000.',

            'aptitudes.distance.*.required' => 'All distance aptitudes are required.',
            'aptitudes.distance.*.in' => 'Invalid aptitude grade selected. Must be G, F, E, D, C, B, A, S, or SS.',

            'aptitudes.surface.*.required' => 'All surface aptitudes are required.',
            'aptitudes.surface.*.in' => 'Invalid aptitude grade selected. Must be G, F, E, D, C, B, A, S, or SS.',

            'aptitudes.style.*.required' => 'All running style aptitudes are required.',
            'aptitudes.style.*.in' => 'Invalid aptitude grade selected. Must be G, F, E, D, C, B, A, S, or SS.',
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
            'stats.speed' => 'Speed',
            'stats.stamina' => 'Stamina',
            'stats.power' => 'Power',
            'stats.guts' => 'Guts',
            'stats.wit' => 'Wit',

            'aptitudes.distance.sprint' => 'Sprint aptitude',
            'aptitudes.distance.mile' => 'Mile aptitude',
            'aptitudes.distance.medium' => 'Medium aptitude',
            'aptitudes.distance.long' => 'Long aptitude',

            'aptitudes.surface.turf' => 'Turf aptitude',
            'aptitudes.surface.dirt' => 'Dirt aptitude',

            'aptitudes.style.front_runner' => 'Front Runner aptitude',
            'aptitudes.style.pace_chaser' => 'Pace Chaser aptitude',
            'aptitudes.style.late_surger' => 'Late Surger aptitude',
            'aptitudes.style.end_closer' => 'End Closer aptitude',
        ];
    }
}
