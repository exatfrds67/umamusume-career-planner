<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Services\BackupService;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Backup Create Request
 *
 * Validates backup creation requests with type, compression, and encryption options.
 *
 * Requirements: 23.4
 */
class BackupCreateRequest extends FormRequest
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
            'type' => ['nullable', 'string', 'in:'.implode(',', [
                BackupService::TYPE_FULL,
                BackupService::TYPE_INCREMENTAL,
                BackupService::TYPE_SELECTIVE,
            ])],
            'compress' => ['nullable', 'boolean'],
            'encrypt' => ['nullable', 'boolean'],
            'encryption_key' => ['nullable', 'string', 'min:8', 'required_if:encrypt,true'],
            'include_types' => ['nullable', 'array'],
            'include_types.*' => ['string', 'in:character,career,skill,support_card,training_session'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.in' => 'Invalid backup type. Must be full, incremental, or selective.',
            'encryption_key.required_if' => 'Encryption key is required when encryption is enabled.',
            'encryption_key.min' => 'Encryption key must be at least 8 characters.',
            'include_types.*.in' => 'Invalid data type. Must be one of: character, career, skill, support_card, training_session.',
            'description.max' => 'Description cannot exceed 500 characters.',
        ];
    }
}
