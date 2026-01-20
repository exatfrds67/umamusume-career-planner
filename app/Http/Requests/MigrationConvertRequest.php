<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Services\DataImportService;
use App\Services\DataMigrationService;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Migration Convert Request
 *
 * Validates legacy data format conversion requests.
 *
 * Requirements: 23.3, 23.4
 */
class MigrationConvertRequest extends FormRequest
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
        $legacyFormats = array_keys(DataMigrationService::LEGACY_FORMATS);
        $importTypes = array_keys(DataImportService::IMPORT_TYPES);

        return [
            'content' => [
                'required',
                'string',
                'max:5242880', // 5MB max
            ],
            'source_format' => [
                'nullable',
                'string',
                'in:auto,'.implode(',', $legacyFormats),
            ],
            'target_type' => [
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
            'content.required' => 'Please provide the legacy data content to convert.',
            'content.max' => 'Content exceeds maximum size of 5MB.',
            'source_format.in' => 'Invalid source format. Use "auto" for automatic detection.',
            'target_type.required' => 'Please specify the target data type.',
            'target_type.in' => 'Invalid target data type.',
        ];
    }
}
