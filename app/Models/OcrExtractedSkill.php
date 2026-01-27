<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * OCR Extracted Skill Model
 *
 * Stores skills extracted from OCR processing.
 *
 * @property int $id
 * @property int|null $ocr_extraction_id
 * @property string|null $skill_name
 * @property int|null $skill_id
 * @property float|null $confidence_score
 * @property array<string, mixed>|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
/**
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class OcrExtractedSkill extends Model
{
    protected $table = 'ucp_ocr_extracted_skills';

    protected $fillable = [
        'ocr_extraction_id',
        'skill_name',
        'skill_id',
        'confidence_score',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ocr_extraction_id' => 'integer',
            'skill_id' => 'integer',
            'confidence_score' => 'float',
            'metadata' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
