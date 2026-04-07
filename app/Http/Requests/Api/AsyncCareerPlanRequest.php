<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class AsyncCareerPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'character_id' => ['required', 'integer', 'exists:ucp_characters,id'],
            'goal' => ['nullable', 'string', 'max:500'],
            'options' => ['sometimes', 'array'],
            'options.depth' => ['sometimes', 'string', 'in:summary,full'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'character_id.required' => 'Character ID is required to generate a career plan.',
            'character_id.exists' => 'The selected character could not be found.',
            'goal.max' => 'The plan goal may not exceed 500 characters.',
            'options.depth.in' => 'The plan depth must be either summary or full.',
        ];
    }
}
