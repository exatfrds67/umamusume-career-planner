<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Export Report Request
 *
 * Validates export report requests with format and options validation.
 *
 * Requirements: 15.5, 25.5 (Task 5.2.4)
 */
class ExportReportRequest extends FormRequest
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
        return [
            'format' => ['required', 'string', 'in:json,csv,pdf'],
            'include_insights' => ['sometimes', 'boolean'],
            'include_recommendations' => ['sometimes', 'boolean'],
            'include_statistics' => ['sometimes', 'boolean'],
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
            'format.required' => 'Please select an export format.',
            'format.in' => 'Invalid export format. Please select JSON, CSV, or PDF.',
        ];
    }
}
