<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * External Data Model
 *
 * Caches data from external APIs (umapyoi.net, etc.).
 *
 * @property int $id
 * @property string $data_source
 * @property string $data_type
 * @property string $data_key
 * @property string $data_version
 * @property array<string, mixed> $data_content
 * @property string|null $data_description
 * @property array<string, mixed>|null $data_schema
 * @property \Illuminate\Support\Carbon $last_fetched_at
 * @property \Illuminate\Support\Carbon|null $last_validated_at
 * @property bool $is_validated
 * @property array<string, mixed>|null $validation_errors
 * @property string|null $source_url
 * @property string|null $source_api_version
 * @property array<string, mixed>|null $fetch_metadata
 * @property int $fetch_attempt_count
 * @property array<string, mixed>|null $related_entities
 * @property bool $is_integrated
 * @property \Illuminate\Support\Carbon|null $integrated_at
 * @property array<string, mixed>|null $integration_log
 * @property float|null $confidence_score
 * @property string $data_quality
 * @property array<string, mixed>|null $quality_metrics
 * @property int $usage_count
 * @property bool $is_active
 * @property bool $is_deprecated
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property string $update_frequency
 * @property array<string, mixed>|null $transformation_rules
 * @property array<string, mixed>|null $processing_log
 * @property bool $requires_processing
 * @property \Illuminate\Support\Carbon|null $last_processed_at
 * @property array<string, mixed>|null $data_dependencies
 * @property array<string, mixed>|null $dependent_data
 * @property string|null $parent_data_id
 * @property array<string, mixed>|null $child_data_ids
 * @property array<string, mixed>|null $error_log
 * @property int $error_count
 * @property \Illuminate\Support\Carbon|null $last_error_at
 * @property bool $has_critical_errors
 * @property array<string, mixed>|null $tags
 * @property array<string, mixed>|null $custom_metadata
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @use HasFactory<\Database\Factories\ExternalDataFactory>
 */
class ExternalData extends Model
{
    /** @use HasFactory<\Database\Factories\ExternalDataFactory> */
    use HasFactory;

    protected $table = 'ucp_external_data';

    protected $fillable = [
        'data_source',
        'data_type',
        'data_key',
        'data_version',
        'data_content',
        'data_description',
        'data_schema',
        'last_fetched_at',
        'last_validated_at',
        'is_validated',
        'validation_errors',
        'source_url',
        'source_api_version',
        'fetch_metadata',
        'fetch_attempt_count',
        'related_entities',
        'is_integrated',
        'integrated_at',
        'integration_log',
        'confidence_score',
        'data_quality',
        'quality_metrics',
        'usage_count',
        'is_active',
        'is_deprecated',
        'expires_at',
        'update_frequency',
        'transformation_rules',
        'processing_log',
        'requires_processing',
        'last_processed_at',
        'data_dependencies',
        'dependent_data',
        'parent_data_id',
        'child_data_ids',
        'error_log',
        'error_count',
        'last_error_at',
        'has_critical_errors',
        'tags',
        'custom_metadata',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'data_content' => 'array',
            'data_schema' => 'array',
            'last_fetched_at' => 'datetime',
            'last_validated_at' => 'datetime',
            'is_validated' => 'boolean',
            'validation_errors' => 'array',
            'fetch_metadata' => 'array',
            'fetch_attempt_count' => 'integer',
            'related_entities' => 'array',
            'is_integrated' => 'boolean',
            'integrated_at' => 'datetime',
            'integration_log' => 'array',
            'confidence_score' => 'decimal:2',
            'quality_metrics' => 'array',
            'usage_count' => 'integer',
            'is_active' => 'boolean',
            'is_deprecated' => 'boolean',
            'expires_at' => 'datetime',
            'transformation_rules' => 'array',
            'processing_log' => 'array',
            'requires_processing' => 'boolean',
            'last_processed_at' => 'datetime',
            'data_dependencies' => 'array',
            'dependent_data' => 'array',
            'child_data_ids' => 'array',
            'error_log' => 'array',
            'error_count' => 'integer',
            'last_error_at' => 'datetime',
            'has_critical_errors' => 'boolean',
            'tags' => 'array',
            'custom_metadata' => 'array',
        ];
    }

    public function isValid(): bool
    {
        return $this->is_active &&
            ! $this->is_deprecated &&
            ($this->expires_at === null || $this->expires_at->isFuture());
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<ExternalData>  $query
     * @return \Illuminate\Database\Eloquent\Builder<ExternalData>
     */
    public function scopeValid(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_active', true)
            ->where('is_deprecated', false)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<ExternalData>  $query
     * @return \Illuminate\Database\Eloquent\Builder<ExternalData>
     */
    public function scopeSource(\Illuminate\Database\Eloquent\Builder $query, string $source): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('data_source', $source);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<ExternalData>  $query
     * @return \Illuminate\Database\Eloquent\Builder<ExternalData>
     */
    public function scopeDataType(\Illuminate\Database\Eloquent\Builder $query, string $type): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('data_type', $type);
    }
}
