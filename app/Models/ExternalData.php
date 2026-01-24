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
 * @property string $source
 * @property string|null $external_id
 * @property string $data_type
 * @property array<string, mixed> $data
 * @property \Illuminate\Support\Carbon|null $cached_at
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property bool $is_valid
 * @property array<string, mixed>|null $validation_errors
 * @property \Illuminate\Support\Carbon|null $last_checked_at
 * @property array<string, mixed>|null $metadata
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
        'source',
        'external_id',
        'data_type',
        'data',
        'cached_at',
        'expires_at',
        'is_valid',
        'validation_errors',
        'last_checked_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'cached_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_valid' => 'boolean',
            'validation_errors' => 'array',
            'last_checked_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function isValid(): bool
    {
        return $this->is_valid && ($this->expires_at === null || ($this->expires_at instanceof \Illuminate\Support\Carbon && $this->expires_at->isFuture()));
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<ExternalData>  $query
     * @return \Illuminate\Database\Eloquent\Builder<ExternalData>
     */
    public function scopeValid(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_valid', true)
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
        return $query->where('source', $source);
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
