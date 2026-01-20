<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Backup Restore Request
 *
 * Validates backup restoration requests with decryption and overwrite options.
 *
 * Requirements: 23.4
 */
class BackupRestoreRequest extends FormRequest
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
            'decryption_key' => ['nullable', 'string', 'min:8'],
            'overwrite_existing' => ['nullable', 'boolean'],
            'restore_types' => ['nullable', 'array'],
            'restore_types.*' => ['string', 'in:character,career,skill,support_card,training_session'],
            'dry_run' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'decryption_key.min' => 'Decryption key must be at least 8 characters.',
            'restore_types.*.in' => 'Invalid data type. Must be one of: character, career, skill, support_card, training_session.',
        ];
    }
}
