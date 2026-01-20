<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Services\DataMigrationService;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Migration Resolve Conflict Request
 *
 * Validates conflict resolution requests during data migration.
 *
 * Requirements: 23.3, 23.4
 */
class MigrationResolveConflictRequest extends FormRequest
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
        $conflictStrategies = [
            DataMigrationService::CONFLICT_STRATEGY_SKIP,
            DataMigrationService::CONFLICT_STRATEGY_OVERWRITE,
            DataMigrationService::CONFLICT_STRATEGY_MERGE,
            DataMigrationService::CONFLICT_STRATEGY_RENAME,
        ];

        return [
            'batch_id' => [
                'required',
                'string',
                'uuid',
            ],
            'conflict_index' => [
                'required',
                'integer',
                'min:0',
            ],
            'resolution' => [
                'required',
                'string',
                'in:'.implode(',', $conflictStrategies),
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
            'batch_id.required' => 'Batch ID is required.',
            'batch_id.uuid' => 'Invalid batch ID format.',
            'conflict_index.required' => 'Conflict index is required.',
            'conflict_index.integer' => 'Conflict index must be an integer.',
            'conflict_index.min' => 'Conflict index must be non-negative.',
            'resolution.required' => 'Resolution strategy is required.',
            'resolution.in' => 'Invalid resolution strategy. Use: skip, overwrite, merge, or rename.',
        ];
    }
}
