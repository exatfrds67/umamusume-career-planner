<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OCRExtraction extends Model
{
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeDataType($query, string $type)
    {
        return $query->where('data_type', $type);
    }
}
