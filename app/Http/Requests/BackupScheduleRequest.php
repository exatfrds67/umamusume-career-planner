<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Services\BackupService;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Backup Schedule Request
 *
 * Validates backup schedule creation requests with frequency and retention options.
 *
 * Requirements: 23.4
 */
class BackupScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'frequency' => ['nullable', 'string', 'in:'.implode(',', [
                BackupService::SCHEDULE_DAILY,
                BackupService::SCHEDULE_WEEKLY,
                BackupService::SCHEDULE_MONTHLY,
                BackupService::SCHEDULE_CUSTOM,
            ])],
            'time' => ['nullable', 'string', 'regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/'],
            'type' => ['nullable', 'string', 'in:'.implode(',', [
                BackupService::TYPE_FULL,
                BackupService::TYPE_INCREMENTAL,
                BackupService::TYPE_SELECTIVE,
            ])],
            'compress' => ['nullable', 'boolean'],
            'encrypt' => ['nullable', 'boolean'],
            'retention_days' => ['nullable', 'integer', 'min:1', 'max:90'],
            'include_types' => ['nullable', 'array'],
            'include_types.*' => ['string', 'in:character,career,skill,support_card,training_session'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'frequency.in' => 'Invalid frequency. Must be daily, weekly, monthly, or custom.',
            'time.regex' => 'Invalid time format. Use HH:MM format (e.g., 02:00).',
            'type.in' => 'Invalid backup type. Must be full, incremental, or selective.',
            'retention_days.min' => 'Retention days must be at least 1.',
            'retention_days.max' => 'Retention days cannot exceed 90.',
            'include_types.*.in' => 'Invalid data type. Must be one of: character, career, skill, support_card, training_session.',
        ];
    }
}
