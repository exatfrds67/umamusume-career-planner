<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * OCR Upload Request
 *
 * Validates screenshot uploads for OCR processing.
 * Implements comprehensive validation for file types, sizes, and security.
 *
 * Requirements: Task 5.1.2, Requirement 23.2
 */
class OCRUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // User must be authenticated to upload screenshots
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $maxFileSize = config('services.image_processing.max_file_size', 10485760); // 10MB default
        $allowedMimes = config('services.image_processing.allowed_mimes', ['image/jpeg', 'image/png', 'image/webp']);
        $allowedExtensions = config('services.image_processing.allowed_formats', ['jpg', 'jpeg', 'png', 'webp']);

        return [
            'screenshot' => [
                'required',
                'file',
                'mimes:'.implode(',', array_map(fn ($mime) => str_replace('image/', '', $mime), $allowedMimes)),
                'max:'.($maxFileSize / 1024), // Convert bytes to KB for validation
                'dimensions:min_width='.config('services.image_processing.min_width', 320)
                    .',min_height='.config('services.image_processing.min_height', 240)
                    .',max_width='.config('services.image_processing.max_width', 4096)
                    .',max_height='.config('services.image_processing.max_height', 4096),
            ],
            'character_id' => [
                'nullable',
                'integer',
                'exists:ucp_characters,id',
            ],
            'data_type' => [
                'nullable',
                'string',
                'in:character_stats,training_session,race_result,skill_list,support_cards',
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
        $maxSizeMB = config('services.image_processing.max_file_size', 10485760) / 1048576;

        return [
            'screenshot.required' => 'Please select a screenshot to upload.',
            'screenshot.file' => 'The uploaded file must be a valid file.',
            'screenshot.mimes' => 'The screenshot must be a JPEG, PNG, or WebP image.',
            'screenshot.max' => sprintf('The screenshot must not exceed %d MB in size.', $maxSizeMB),
            'screenshot.dimensions' => 'The screenshot dimensions are invalid. Minimum: 320x240, Maximum: 4096x4096.',
            'character_id.integer' => 'The character ID must be a valid number.',
            'character_id.exists' => 'The selected character does not exist.',
            'data_type.in' => 'The data type must be one of: character_stats, training_session, race_result, skill_list, support_cards.',
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
            'screenshot' => 'screenshot image',
            'character_id' => 'character',
            'data_type' => 'data type',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default data_type if not provided
        if (! $this->has('data_type')) {
            $this->merge([
                'data_type' => 'character_stats',
            ]);
        }
    }
}
