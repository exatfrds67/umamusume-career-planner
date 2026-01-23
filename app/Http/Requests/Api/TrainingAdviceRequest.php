<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class TrainingAdviceRequest extends FormRequest
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
            'training_options' => ['sometimes', 'array'],
            'training_options.available_trainings' => ['sometimes', 'array'],
            'training_options.available_trainings.*.type' => ['required_with:training_options.available_trainings', 'string', 'in:speed,stamina,power,guts,wit,rest'],
            'training_options.available_trainings.*.support_cards_present' => ['sometimes', 'array'],
            'training_options.available_trainings.*.energy_cost' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'training_options.available_trainings.*.failure_risk' => ['sometimes', 'numeric', 'min:0', 'max:1'],
            'training_options.available_trainings.*.expected_gains' => ['sometimes', 'array'],
            'training_options.spirit_burst_gauge' => ['sometimes', 'integer', 'min:0', 'max:4'],
            'training_options.additional_context' => ['sometimes', 'string', 'max:1000'],
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
            'character_id.required' => 'Character ID is required for training advice.',
            'character_id.exists' => 'The specified character does not exist.',
            'training_options.available_trainings.*.type.required_with' => 'Training type is required for each training option.',
            'training_options.available_trainings.*.type.in' => 'Training type must be one of: speed, stamina, power, guts, wit, rest.',
            'training_options.available_trainings.*.energy_cost.max' => 'Energy cost cannot exceed 100.',
            'training_options.available_trainings.*.failure_risk.max' => 'Failure risk must be between 0 and 1.',
            'training_options.spirit_burst_gauge.max' => 'Spirit Burst gauge cannot exceed 4.',
            'training_options.additional_context.max' => 'Additional context cannot exceed 1000 characters.',
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
            'training_options' => 'training options',
            'training_options.spirit_burst_gauge' => 'Spirit Burst gauge',
            'training_options.additional_context' => 'additional context',
        ];
    }
}
