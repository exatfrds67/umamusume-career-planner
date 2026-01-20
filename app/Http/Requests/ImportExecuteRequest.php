<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Services\DataImportService;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Import Execute Request
 *
 * Validates import execution requests for data import functionality.
 *
 * Requirements: 23.1, 23.2
 */
class ImportExecuteRequest extends FormRequest
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
        $importTypes = array_keys(DataImportService::IMPORT_TYPES);

        return [
            'import_type' => [
                'required',
                'string',
                'in:'.implode(',', $importTypes),
            ],
            'data' => [
                'required',
                'array',
                'min:1',
            ],
            'data.*' => [
                'required',
                'array',
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
            'import_type.required' => 'Please select an import type.',
            'import_type.in' => 'Invalid import type selected.',
            'data.required' => 'No data provided for import.',
            'data.array' => 'Import data must be an array of records.',
            'data.min' => 'At least one record is required for import.',
            'data.*.required' => 'Each record must contain data.',
            'data.*.array' => 'Each record must be an array of field values.',
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
            'import_type' => 'import type',
            'data' => 'import data',
            'data.*' => 'import record',
        ];
    }
}
