<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DeletionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Deletion Request Model
 *
 * Tracks account deletion requests with a 30-day grace period.
 * Users can cancel during the grace period.
 *
 * @property int $id
 * @property int $user_id
 * @property DeletionStatus $status
 * @property string|null $reason
 * @property \Illuminate\Support\Carbon $grace_period_ends_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $cancelled_at
 * @property string|null $deletion_log
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $user
 */
class DeletionRequest extends Model
{
    /** @use HasFactory<\Database\Factories\DeletionRequestFactory> */
    use HasFactory;

    protected $table = 'ucp_deletion_requests';

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'status',
        'reason',
        'grace_period_ends_at',
        'completed_at',
        'cancelled_at',
        'deletion_log',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'status' => DeletionStatus::class,
            'grace_period_ends_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
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
     * Check if the deletion request is still within the grace period.
     */
    public function isWithinGracePeriod(): bool
    {
        return $this->status === DeletionStatus::Pending
            && $this->grace_period_ends_at->isFuture();
    }

    /**
     * Check if the grace period has expired and deletion should proceed.
     */
    public function isGracePeriodExpired(): bool
    {
        return $this->status === DeletionStatus::Pending
            && $this->grace_period_ends_at->isPast();
    }
}
