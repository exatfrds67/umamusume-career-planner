<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ConsentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Consent Record Model
 *
 * Tracks user consent for various data processing activities.
 * Supports granular consent management and audit trail.
 *
 * @property int $id
 * @property int $user_id
 * @property ConsentType $consent_type
 * @property bool $granted
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Illuminate\Support\Carbon|null $granted_at
 * @property \Illuminate\Support\Carbon|null $revoked_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $user
 */
class ConsentRecord extends Model
{
    /** @use HasFactory<\Database\Factories\ConsentRecordFactory> */
    use HasFactory;

    protected $table = 'ucp_consent_records';

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'consent_type',
        'granted',
        'ip_address',
        'user_agent',
        'granted_at',
        'revoked_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'consent_type' => ConsentType::class,
            'granted' => 'boolean',
            'granted_at' => 'datetime',
            'revoked_at' => 'datetime',
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
     * Check if consent is currently active.
     */
    public function isActive(): bool
    {
        return $this->granted && $this->revoked_at === null;
    }
}
