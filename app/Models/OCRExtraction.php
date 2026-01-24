<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * OCR Extraction Model
 *
 * Stores OCR extraction results from game screenshots.
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $image_path
 * @property string|null $image_hash
 * @property string|null $extracted_text
 * @property array<string, mixed>|null $parsed_data
 * @property float|null $confidence_score
 * @property string|null $data_type
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $processed_at
 * @property string|null $error_message
 * @property array<string, mixed>|null $processing_metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @use HasFactory<\Database\Factories\OCRExtractionFactory>
 */
/**
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class OCRExtraction extends Model
{
    /** @use HasFactory<\Database\Factories\OCRExtractionFactory> */
    use HasFactory;

    protected $table = 'ucp_ocr_extractions';

    protected $fillable = [
        'user_id',
        'image_path',
        'image_hash',
        'extracted_text',
        'parsed_data',
        'confidence_score',
        'data_type',
        'status',
        'processed_at',
        'error_message',
        'processing_metadata',
    ];

    protected function casts(): array
    {
        return [
            'extracted_text' => 'string',
            'parsed_data' => 'array',
            'confidence_score' => 'decimal:2',
            'processed_at' => 'datetime',
            'processing_metadata' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param  Builder<OCRExtraction>  $query
     * @return Builder<OCRExtraction>
     */
    public function scopeProcessed(Builder $query): Builder
    {
        return $query->where('status', 'processed');
    }

    /**
     * @param  Builder<OCRExtraction>  $query
     * @return Builder<OCRExtraction>
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }

    /**
     * @param  Builder<OCRExtraction>  $query
     * @return Builder<OCRExtraction>
     */
    public function scopeDataType(Builder $query, string $type): Builder
    {
        return $query->where('data_type', $type);
    }
}
