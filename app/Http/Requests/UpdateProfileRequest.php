<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();
        $userId = $user instanceof \App\Models\User ? $user->id : null;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('ucp_users')->ignore($userId),
            ],
            'bio' => ['nullable', 'string', 'max:500'],
            'preferences' => ['nullable', 'array'],
            'preferences.theme' => ['nullable', 'string', 'in:light,dark,auto'],
            'preferences.language' => ['nullable', 'string', 'max:10'],
            'preferences.timezone' => ['nullable', 'string', 'timezone'],
            'preferences.date_format' => ['nullable', 'string', 'max:50'],
            'preferences.time_format' => ['nullable', 'string', 'in:12h,24h'],
            'preferences.notifications' => ['nullable', 'array'],
            'preferences.notifications.email' => ['nullable', 'boolean'],
            'preferences.notifications.training_reminders' => ['nullable', 'boolean'],
            'preferences.notifications.race_alerts' => ['nullable', 'boolean'],
            'preferences.notifications.ai_recommendations' => ['nullable', 'boolean'],
            'preferences.privacy' => ['nullable', 'array'],
            'preferences.privacy.local_first' => ['nullable', 'boolean'],
            'accessibility_settings' => ['nullable', 'array'],
            'accessibility_settings.high_contrast' => ['nullable', 'boolean'],
            'accessibility_settings.large_text' => ['nullable', 'boolean'],
            'accessibility_settings.reduce_motion' => ['nullable', 'boolean'],
            'accessibility_settings.screen_reader' => ['nullable', 'boolean'],
            'ai_settings' => ['nullable', 'array'],
            'ai_settings.enable_cloud_ai' => ['nullable', 'boolean'],
            'ai_settings.preferred_model' => ['nullable', 'string', 'max:100'],
            'ai_settings.budget_limit' => ['nullable', 'numeric', 'min:0'],
            'mcp_settings' => ['nullable', 'array'],
            'mcp_settings.enable_external_apis' => ['nullable', 'boolean'],
            'mcp_settings.enable_analytics' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please enter your name.',
            'name.max' => 'Your name cannot exceed 255 characters.',
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already in use.',
            'preferences.theme.in' => 'Please select a valid theme option.',
            'preferences.timezone.timezone' => 'Please select a valid timezone.',
            'preferences.time_format.in' => 'Please select either 12h or 24h time format.',
            'ai_settings.budget_limit.min' => 'Budget limit must be a positive number.',
        ];
    }
}
