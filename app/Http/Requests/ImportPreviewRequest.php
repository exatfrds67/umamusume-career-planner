<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Services\DataImportService;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Import Preview Request
 *
 * Validates import preview requests for data import functionality.
 *
 * Requirements: 23.1, 23.2
 */
class ImportPreviewRequest extends FormRequest
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
        $supportedFormats = DataImportService::SUPPORTED_FORMATS;

        return [
            'import_type' => [
                'required',
                'string',
                'in:'.implode(',', $importTypes),
            ],
            'format' => [
                'nullable',
                'string',
                'in:auto,'.implode(',', $supportedFormats),
            ],
            'content' => [
                'required_without:file',
                'nullable',
                'string',
                'max:1048576', // 1MB max for text content
            ],
            'file' => [
                'required_without:content',
                'nullable',
                'file',
                'mimes:csv,json,txt,text',
                'max:10240', // 10MB max for file upload
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
            'format.in' => 'Invalid format specified. Supported formats: auto, csv, json, txt.',
            'content.required_without' => 'Please provide either text content or upload a file.',
            'content.max' => 'Text content exceeds maximum size of 1MB.',
            'file.required_without' => 'Please provide either text content or upload a file.',
            'file.mimes' => 'File must be a CSV, JSON, or TXT file.',
            'file.max' => 'File size must not exceed 10MB.',
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
            'format' => 'file format',
            'content' => 'text content',
            'file' => 'import file',
        ];
    }
}
