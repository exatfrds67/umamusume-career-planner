<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property int $user_id
 * @property int $character_id
 * @property string|null $goal
 * @property array<string, mixed> $plan
 * @property bool $is_locked
 * @property \Illuminate\Support\Carbon|null $locked_at
 * @property int $current_turn
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @use HasFactory<\Database\Factories\CareerPlanFactory>
 */
class CareerPlan extends Model
{
    /** @use HasFactory<\Database\Factories\CareerPlanFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the primary key.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'user_id',
        'character_id',
        'goal',
        'plan',
        'is_locked',
        'locked_at',
        'current_turn',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'character_id' => 'integer',
            'plan' => 'array',
            'is_locked' => 'boolean',
            'locked_at' => 'datetime',
            'current_turn' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
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
     * @return BelongsTo<Character, $this>
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Get the current plan status.
     */
    public function status(): string
    {
        $status = $this->plan['status'] ?? null;

        return is_string($status) ? $status : 'queued';
    }

    /**
     * Determine whether the plan has completed generation.
     */
    public function isCompleted(): bool
    {
        return $this->status() === 'completed';
    }

    /**
     * Get the next timeline entry based on the current turn.
     *
     * @return array<string, mixed>|null
     */
    public function getNextAction(): ?array
    {
        $timeline = $this->plan['timeline'] ?? [];
        if (! is_array($timeline)) {
            return null;
        }

        foreach ($timeline as $turn) {
            if (! is_array($turn)) {
                continue;
            }

            if (($turn['turn'] ?? null) === $this->current_turn) {
                return $turn;
            }
        }

        return null;
    }

    /**
     * Advance the tracked turn without exceeding the planned turn count.
     */
    public function advanceTurn(): void
    {
        $totalTurnsRaw = $this->plan['total_turns'] ?? $this->current_turn;
        $totalTurns = is_numeric($totalTurnsRaw) ? (int) $totalTurnsRaw : $this->current_turn;
        $this->current_turn = min($totalTurns + 1, $this->current_turn + 1);
        $this->save();
    }

    /**
     * Persist notification preferences inside the plan payload metadata.
     *
     * @param  array<string, mixed>  $preferences
     */
    public function storeNotificationPreferences(array $preferences): void
    {
        $plan = $this->plan;
        $metadata = $plan['metadata'] ?? [];

        if (! is_array($metadata)) {
            $metadata = [];
        }

        $metadata['notification_preferences'] = $preferences;
        $plan['metadata'] = $metadata;
        $this->plan = $plan;
    }

    /**
     * @return array<string, mixed>
     */
    public function notificationPreferences(): array
    {
        $metadata = $this->plan['metadata'] ?? [];
        if (! is_array($metadata)) {
            return [];
        }

        $preferences = $metadata['notification_preferences'] ?? [];

        return is_array($preferences) ? $preferences : [];
    }
}
