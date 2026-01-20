<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Services\DataImportService;
use App\Services\DataMigrationService;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Migration Batch Request
 *
 * Validates batch import requests for data migration.
 *
 * Requirements: 23.3, 23.4
 */
class MigrationBatchRequest extends FormRequest
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
        $conflictStrategies = [
            DataMigrationService::CONFLICT_STRATEGY_SKIP,
            DataMigrationService::CONFLICT_STRATEGY_OVERWRITE,
            DataMigrationService::CONFLICT_STRATEGY_MERGE,
            DataMigrationService::CONFLICT_STRATEGY_RENAME,
        ];

        return [
            'data' => [
                'required',
                'array',
                'min:1',
                'max:10000', // Maximum 10,000 records per batch
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
            'conflict_strategy' => [
                'nullable',
                'string',
                'in:'.implode(',', $conflictStrategies),
            ],
            'batch_size' => [
                'nullable',
                'integer',
                'min:1',
                'max:500',
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
            'data.required' => 'Please provide data to import.',
            'data.array' => 'Data must be an array of records.',
            'data.min' => 'At least one record is required.',
            'data.max' => 'Maximum 10,000 records per batch.',
            'import_type.required' => 'Please specify the import type.',
            'import_type.in' => 'Invalid import type.',
            'conflict_strategy.in' => 'Invalid conflict resolution strategy.',
            'batch_size.min' => 'Batch size must be at least 1.',
            'batch_size.max' => 'Batch size cannot exceed 500.',
        ];
    }
}
