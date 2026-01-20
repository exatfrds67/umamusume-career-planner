<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExternalData extends Model
{
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
        return $this->is_valid && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    public function scopeValid($query)
    {
        return $query->where('is_valid', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function scopeSource($query, string $source)
    {
        return $query->where('source', $source);
    }

    public function scopeDataType($query, string $type)
    {
        return $query->where('data_type', $type);
    }
}
