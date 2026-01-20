<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Services\DataExportService;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Export Schedule Request
 *
 * Validates export scheduling requests with frequency and timing validation.
 *
 * Requirements: 23.2
 */
class ExportScheduleRequest extends FormRequest
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
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        $exportTypes = implode(',', array_keys(DataExportService::EXPORT_TYPES));
        $formats = implode(',', DataExportService::SUPPORTED_FORMATS);

        return [
            'export_type' => ['required', 'string', "in:{$exportTypes}"],
            'format' => ['sometimes', 'string', "in:{$formats}"],
            'frequency' => ['required', 'string', 'in:daily,weekly,monthly'],
            'day_of_week' => ['required_if:frequency,weekly', 'nullable', 'integer', 'min:0', 'max:6'],
            'day_of_month' => ['required_if:frequency,monthly', 'nullable', 'integer', 'min:1', 'max:28'],
            'time' => ['sometimes', 'string', 'date_format:H:i'],
            'email_notification' => ['sometimes', 'boolean'],
            'filters' => ['sometimes', 'array'],
            'filters.scenario_type' => ['sometimes', 'string', 'in:ura_finale,unity_cup'],
            'filters.status' => ['sometimes', 'string', 'in:active,completed,abandoned'],
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
            'export_type.required' => 'Please select an export type.',
            'export_type.in' => 'Invalid export type selected.',
            'format.in' => 'Invalid export format. Please select JSON, CSV, or PDF.',
            'frequency.required' => 'Please select an export frequency.',
            'frequency.in' => 'Invalid frequency. Please select daily, weekly, or monthly.',
            'day_of_week.required_if' => 'Please select a day of the week for weekly exports.',
            'day_of_week.min' => 'Day of week must be between 0 (Sunday) and 6 (Saturday).',
            'day_of_week.max' => 'Day of week must be between 0 (Sunday) and 6 (Saturday).',
            'day_of_month.required_if' => 'Please select a day of the month for monthly exports.',
            'day_of_month.min' => 'Day of month must be between 1 and 28.',
            'day_of_month.max' => 'Day of month must be between 1 and 28.',
            'time.date_format' => 'Time must be in HH:MM format (e.g., 09:00).',
        ];
    }
}
