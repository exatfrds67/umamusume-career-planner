<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class LockCareerPlanRequest extends FormRequest
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
            'start_turn' => ['sometimes', 'integer', 'min:1'],
            'notification_preferences' => ['sometimes', 'array'],
            'notification_preferences.email' => ['sometimes', 'boolean'],
            'notification_preferences.push' => ['sometimes', 'boolean'],
        ];
    }
}
