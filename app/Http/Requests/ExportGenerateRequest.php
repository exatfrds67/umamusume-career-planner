<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Services\DataExportService;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Export Generate Request
 *
 * Validates export generation requests with type, format, and filter validation.
 *
 * Requirements: 23.2
 */
class ExportGenerateRequest extends FormRequest
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
            'save_to_file' => ['sometimes', 'boolean'],
            'filters' => ['sometimes', 'array'],
            'filters.scenario_type' => ['sometimes', 'string', 'in:ura_finale,unity_cup'],
            'filters.status' => ['sometimes', 'string', 'in:active,completed,abandoned'],
            'filters.date_from' => ['sometimes', 'date'],
            'filters.date_to' => ['sometimes', 'date', 'after_or_equal:filters.date_from'],
            'filters.character_ids' => ['sometimes', 'array'],
            'filters.character_ids.*' => ['integer', 'exists:ucp_characters,id'],
            'filters.career_ids' => ['sometimes', 'array'],
            'filters.career_ids.*' => ['integer', 'exists:ucp_careers,id'],
            'filters.career_id' => ['sometimes', 'integer', 'exists:ucp_careers,id'],
            'filters.training_type' => ['sometimes', 'string', 'in:speed,stamina,power,guts,wit,rest'],
            'filters.turn_from' => ['sometimes', 'integer', 'min:1', 'max:78'],
            'filters.turn_to' => ['sometimes', 'integer', 'min:1', 'max:78', 'gte:filters.turn_from'],
            'filters.skill_type' => ['sometimes', 'string'],
            'filters.rarity' => ['sometimes', 'string'],
            'filters.specialization' => ['sometimes', 'string'],
            'filters.meta_tier' => ['sometimes', 'string'],
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
            'filters.date_to.after_or_equal' => 'End date must be after or equal to start date.',
            'filters.turn_to.gte' => 'End turn must be greater than or equal to start turn.',
        ];
    }
}
