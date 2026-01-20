<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Services\DataImportService;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Migration Validate Request
 *
 * Validates data validation requests before import.
 *
 * Requirements: 23.3, 23.4
 */
class MigrationValidateRequest extends FormRequest
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
            'data' => [
                'required',
                'array',
                'min:1',
            ],
            'data.*' => [
                'required',
                'array',
            ],
            'import_type' => [
                'required',
                'string',
                'in:'.implode(',', $importTypes),
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
            'data.required' => 'Please provide data to validate.',
            'data.array' => 'Data must be an array of records.',
            'data.min' => 'At least one record is required for validation.',
            'import_type.required' => 'Please specify the import type.',
            'import_type.in' => 'Invalid import type.',
        ];
    }
}
