<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AlertType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Critical Alert Model
 *
 * Stores high-priority warnings that require immediate player attention.
 * Tracks alert history, dismissals, and resolution status.
 *
 * @property int $id
 * @property int $career_id
 * @property int $turn_number
 * @property AlertType $alert_type
 * @property string $message
 * @property array<string> $action_items
 * @property int|null $turns_until_critical
 * @property bool $was_dismissed
 * @property \Illuminate\Support\Carbon|null $dismissed_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Career $career
 */
class CriticalAlert extends Model
{
    /** @use HasFactory<\Database\Factories\CriticalAlertFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ucp_critical_alerts';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'career_id',
        'turn_number',
        'alert_type',
        'message',
        'action_items',
        'turns_until_critical',
        'was_dismissed',
        'dismissed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'career_id' => 'integer',
            'turn_number' => 'integer',
            'alert_type' => AlertType::class,
            'action_items' => 'array',
            'turns_until_critical' => 'integer',
            'was_dismissed' => 'boolean',
            'dismissed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the career that owns this alert.
     *
     * @return BelongsTo<Career, $this>
     */
    public function career(): BelongsTo
    {
        return $this->belongsTo(Career::class);
    }

    /**
     * Scope a query to only include alerts for a specific career.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     */
    public function scopeForCareer($query, int $careerId): void
    {
        $query->where('career_id', $careerId);
    }

    /**
     * Scope a query to only include alerts for a specific turn.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     */
    public function scopeForTurn($query, int $turnNumber): void
    {
        $query->where('turn_number', $turnNumber);
    }

    /**
     * Scope a query to only include alerts of a specific type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     */
    public function scopeOfType($query, AlertType $type): void
    {
        $query->where('alert_type', $type);
    }

    /**
     * Scope a query to only include active (not dismissed) alerts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     */
    public function scopeActive($query): void
    {
        $query->where('was_dismissed', false);
    }

    /**
     * Scope a query to only include dismissed alerts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     */
    public function scopeDismissed($query): void
    {
        $query->where('was_dismissed', true);
    }

    /**
     * Scope a query to only include immediate critical alerts (0 turns).
     *
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     */
    public function scopeImmediateCritical($query): void
    {
        $query->where('turns_until_critical', 0);
    }

    /**
     * Scope a query to only include approaching critical alerts (1-3 turns).
     *
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     */
    public function scopeApproachingCritical($query): void
    {
        $query->whereBetween('turns_until_critical', [1, 3]);
    }

    /**
     * Scope a query to order by urgency (most urgent first).
     *
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     */
    public function scopeOrderByUrgency($query): void
    {
        $query->orderBy('turns_until_critical', 'asc')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Scope a query to order by turn number.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<self>  $query
     */
    public function scopeOrderByTurn($query, string $direction = 'asc'): void
    {
        $query->orderBy('turn_number', $direction);
    }

    /**
     * Dismiss this alert.
     */
    public function dismiss(): bool
    {
        return $this->update([
            'was_dismissed' => true,
            'dismissed_at' => now(),
        ]);
    }

    /**
     * Reactivate a dismissed alert.
     */
    public function reactivate(): bool
    {
        return $this->update([
            'was_dismissed' => false,
            'dismissed_at' => null,
        ]);
    }

    /**
     * Check if this alert is active (not dismissed).
     */
    public function isActive(): bool
    {
        return ! $this->was_dismissed;
    }

    /**
     * Check if this alert is dismissed.
     */
    public function isDismissed(): bool
    {
        return $this->was_dismissed;
    }

    /**
     * Check if this alert is immediately critical (0 turns).
     */
    public function isImmediateCritical(): bool
    {
        return $this->turns_until_critical === 0;
    }

    /**
     * Check if this alert is approaching critical (1-3 turns).
     */
    public function isApproachingCritical(): bool
    {
        return $this->turns_until_critical !== null
            && $this->turns_until_critical > 0
            && $this->turns_until_critical <= 3;
    }

    /**
     * Get urgency level as a string.
     */
    public function getUrgencyLevel(): string
    {
        if ($this->turns_until_critical === null) {
            return 'Unknown';
        }

        return match (true) {
            $this->turns_until_critical === 0 => 'Immediate',
            $this->turns_until_critical <= 3 => 'Urgent',
            $this->turns_until_critical <= 5 => 'Soon',
            default => 'Upcoming',
        };
    }

    /**
     * Get a formatted urgency message.
     */
    public function getUrgencyMessage(): string
    {
        if ($this->turns_until_critical === null) {
            return 'Urgency unknown';
        }

        if ($this->turns_until_critical === 0) {
            return 'Requires immediate attention';
        }

        if ($this->turns_until_critical === 1) {
            return 'Critical in 1 turn';
        }

        return "Critical in {$this->turns_until_critical} turns";
    }

    /**
     * Get a summary of this alert.
     */
    public function getSummary(): string
    {
        $urgency = $this->getUrgencyLevel();
        $type = $this->alert_type->label();

        return "[{$urgency}] {$type}: {$this->message}";
    }

    /**
     * Get action items as a formatted string.
     */
    public function getActionItemsText(): string
    {
        if (empty($this->action_items)) {
            return 'No specific actions recommended';
        }

        return '• '.implode("\n• ", $this->action_items);
    }
}
