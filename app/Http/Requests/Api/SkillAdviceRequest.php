<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Skill Advice Request
 *
 * Validates incoming requests for AI-powered skill purchase advice.
 * Ensures all required context data is present and valid for generating
 * intelligent skill purchase recommendations.
 *
 * **Validates: Requirements 3.2 (Skill Purchase Advisory)**
 *
 * @see \App\Http\Controllers\Api\AdvisoryController::getSkillPurchaseAdvice()
 * @see \App\Services\TrainingAdvisoryService::getSkillPurchaseAdvice()
 */
class SkillAdviceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow all authenticated users (auth:sanctum middleware handles authentication)
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
            // Character identification
            'character_id' => ['required'],
            'storage_mode' => ['required', 'string', Rule::in(['local', 'account'])],

            // SP budget
            'sp_available' => ['required', 'integer', 'min:0', 'max:9999'],

            // Acquired skills (already purchased)
            'acquired_skills' => ['sometimes', 'array'],
            'acquired_skills.*' => ['integer', 'min:1'],

            // Available skills for purchase
            'available_skills' => ['required', 'array', 'min:1'],
            'available_skills.*.id' => ['required', 'integer', 'min:1'],
            'available_skills.*.name' => ['required', 'string', 'max:100'],
            'available_skills.*.tier' => [
                'required',
                'string',
                Rule::in(['normal', 'rare', 'gold', 'unique', 'evolution']),
            ],
            'available_skills.*.base_cost' => ['required', 'integer', 'min:1', 'max:1000'],
            'available_skills.*.hint_level' => ['required', 'integer', 'min:0', 'max:5'],
            'available_skills.*.category' => [
                'sometimes',
                'string',
                Rule::in([
                    'speed',
                    'stamina',
                    'power',
                    'guts',
                    'wisdom',
                    'stamina_recovery',
                    'positioning',
                    'acceleration',
                    'lane_change',
                    'pace_control',
                    'mental',
                    'general',
                ]),
            ],

            // Optional: Character build context for better recommendations
            'target_distance' => [
                'sometimes',
                'string',
                Rule::in(['sprint', 'mile', 'medium', 'long']),
            ],
            'running_style' => [
                'sometimes',
                'string',
                Rule::in(['escape', 'lead', 'pace', 'chase']),
            ],
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
            // Character identification
            'character_id.required' => 'Character ID is required',
            'storage_mode.required' => 'Storage mode is required',
            'storage_mode.in' => 'Storage mode must be either "local" or "account"',

            // SP budget
            'sp_available.required' => 'Available SP is required',
            'sp_available.integer' => 'SP must be an integer',
            'sp_available.min' => 'SP cannot be negative',
            'sp_available.max' => 'SP cannot exceed 9999',

            // Acquired skills
            'acquired_skills.array' => 'Acquired skills must be an array',
            'acquired_skills.*.integer' => 'Skill ID must be an integer',
            'acquired_skills.*.min' => 'Skill ID must be at least 1',

            // Available skills
            'available_skills.required' => 'Available skills are required',
            'available_skills.array' => 'Available skills must be an array',
            'available_skills.min' => 'At least one available skill is required',
            'available_skills.*.id.required' => 'Skill ID is required for each available skill',
            'available_skills.*.id.integer' => 'Skill ID must be an integer',
            'available_skills.*.id.min' => 'Skill ID must be at least 1',
            'available_skills.*.name.required' => 'Skill name is required for each available skill',
            'available_skills.*.name.string' => 'Skill name must be a string',
            'available_skills.*.name.max' => 'Skill name cannot exceed 100 characters',
            'available_skills.*.tier.required' => 'Skill tier is required for each available skill',
            'available_skills.*.tier.in' => 'Invalid skill tier. Valid values: normal, rare, gold, unique, evolution',
            'available_skills.*.base_cost.required' => 'Base cost is required for each available skill',
            'available_skills.*.base_cost.integer' => 'Base cost must be an integer',
            'available_skills.*.base_cost.min' => 'Base cost must be at least 1',
            'available_skills.*.base_cost.max' => 'Base cost cannot exceed 1000',
            'available_skills.*.hint_level.required' => 'Hint level is required for each available skill',
            'available_skills.*.hint_level.integer' => 'Hint level must be an integer',
            'available_skills.*.hint_level.min' => 'Hint level cannot be negative',
            'available_skills.*.hint_level.max' => 'Hint level cannot exceed 5',
            'available_skills.*.category.in' => 'Invalid skill category',

            // Build context
            'target_distance.in' => 'Invalid target distance. Valid values: sprint, mile, medium, long',
            'running_style.in' => 'Invalid running style. Valid values: escape, lead, pace, chase',
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
            'character_id' => 'character ID',
            'storage_mode' => 'storage mode',
            'sp_available' => 'available SP',
            'acquired_skills' => 'acquired skills',
            'available_skills' => 'available skills',
            'available_skills.*.id' => 'skill ID',
            'available_skills.*.name' => 'skill name',
            'available_skills.*.tier' => 'skill tier',
            'available_skills.*.base_cost' => 'base cost',
            'available_skills.*.hint_level' => 'hint level',
            'available_skills.*.category' => 'skill category',
            'target_distance' => 'target distance',
            'running_style' => 'running style',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * Sets default values for optional fields.
     */
    protected function prepareForValidation(): void
    {
        // Set defaults for optional arrays and fields
        $this->merge([
            'acquired_skills' => $this->input('acquired_skills', []),
            'target_distance' => $this->input('target_distance', 'medium'),
            'running_style' => $this->input('running_style', 'escape'),
        ]);

        // Ensure each available skill has a category default
        /** @var mixed $availableSkillsInput */
        $availableSkillsInput = $this->input('available_skills', []);

        if (\is_array($availableSkillsInput)) {
            $modifiedSkills = [];
            foreach ($availableSkillsInput as $index => $skill) {
                if (\is_array($skill)) {
                    /** @var array<string, mixed> $skillArray */
                    $skillArray = $skill;
                    if (! isset($skillArray['category'])) {
                        $skillArray['category'] = 'general';
                    }
                    $modifiedSkills[$index] = $skillArray;
                } else {
                    $modifiedSkills[$index] = $skill;
                }
            }
            $this->merge(['available_skills' => $modifiedSkills]);
        }
    }
}
