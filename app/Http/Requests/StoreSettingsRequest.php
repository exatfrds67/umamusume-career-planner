<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSettingsRequest extends FormRequest
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
            // Profile fields
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('ucp_users')->ignore($userId),
            ],

            // Preferences JSON blob
            'preferences' => ['nullable', 'array'],
            'preferences.theme' => ['nullable', 'string', 'in:light,dark,system'],
            'preferences.language' => ['nullable', 'string', 'max:10'],
            'preferences.timezone' => ['nullable', 'string', 'timezone'],
            'preferences.compact_mode' => ['nullable', 'boolean'],
            'preferences.animations' => ['nullable', 'boolean'],
            'preferences.font_size' => ['nullable', 'integer', 'min:100', 'max:200'],
            'preferences.analytics' => ['nullable', 'boolean'],
            'preferences.cloud_backup' => ['nullable', 'boolean'],
            'preferences.cloud_ai' => ['nullable', 'boolean'],
            'preferences.umapyoi' => ['nullable', 'boolean'],
            'preferences.umamusumedb' => ['nullable', 'boolean'],
            'preferences.two_factor' => ['nullable', 'boolean'],
            'preferences.auto_accept_ai' => ['nullable', 'boolean'],
            'preferences.skill_hints' => ['nullable', 'boolean'],
            'preferences.sp_budget_alerts' => ['nullable', 'boolean'],
            'preferences.default_facility' => ['nullable', 'string', 'max:50'],
            'preferences.card_sorting' => ['nullable', 'string', 'max:50'],
            'preferences.auto_save' => ['nullable', 'string', 'max:50'],
            'preferences.backup_frequency' => ['nullable', 'string', 'max:50'],
            'preferences.lazy_loading' => ['nullable', 'boolean'],
            'preferences.debug_mode' => ['nullable', 'boolean'],
            'preferences.error_reporting' => ['nullable', 'boolean'],

            // AI settings JSON blob
            'ai_settings' => ['nullable', 'array'],
            'ai_settings.provider' => ['nullable', 'string', 'in:ollama,bedrock,hybrid'],
            'ai_settings.model' => ['nullable', 'string', 'max:100'],
            'ai_settings.recommendation_frequency' => ['nullable', 'string', 'in:per_turn,per_session,manual'],
            'ai_settings.explanation_detail' => ['nullable', 'string', 'in:brief,detailed,expert'],
            'ai_settings.show_model' => ['nullable', 'boolean'],
            'ai_settings.daily_limit' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'ai_settings.weekly_limit' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'ai_settings.monthly_limit' => ['nullable', 'numeric', 'min:0', 'max:2000'],

            // Accessibility settings JSON blob
            'accessibility_settings' => ['nullable', 'array'],
            'accessibility_settings.high_contrast' => ['nullable', 'boolean'],
            'accessibility_settings.reduced_motion' => ['nullable', 'boolean'],
            'accessibility_settings.screen_reader_optimization' => ['nullable', 'boolean'],
            'accessibility_settings.color_blind_mode' => ['nullable', 'string', 'in:none,deuteranopia,protanopia,tritanopia'],
            'accessibility_settings.font_size' => ['nullable', 'string', 'in:small,medium,large,x-large'],
            'accessibility_settings.focus_indicators' => ['nullable', 'boolean'],

            // Notification preferences JSON blob
            'notification_preferences' => ['nullable', 'array'],
            'notification_preferences.training_alerts' => ['nullable', 'boolean'],
            'notification_preferences.race_reminders' => ['nullable', 'boolean'],
            'notification_preferences.goal_progress' => ['nullable', 'boolean'],
            'notification_preferences.budget_alerts' => ['nullable', 'boolean'],
            'notification_preferences.in_app' => ['nullable', 'boolean'],
            'notification_preferences.sound' => ['nullable', 'boolean'],
            'notification_preferences.quiet_hours_start' => ['nullable', 'string', 'date_format:H:i'],
            'notification_preferences.quiet_hours_end' => ['nullable', 'string', 'date_format:H:i'],
        ];
    }
}
