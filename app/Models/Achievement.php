<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $key
 * @property string $category
 * @property string $title
 * @property string $description
 * @property string $icon
 * @property string $rarity
 * @property int $progress
 * @property int $target
 * @property bool $is_unlocked
 * @property \Illuminate\Support\Carbon|null $unlocked_at
 * @property array<string, mixed>|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Achievement extends Model
{
    /** @use HasFactory<\Database\Factories\AchievementFactory> */
    use HasFactory;

    protected $table = 'ucp_achievements';

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'key',
        'category',
        'title',
        'description',
        'icon',
        'rarity',
        'progress',
        'target',
        'is_unlocked',
        'unlocked_at',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'progress' => 'integer',
            'target' => 'integer',
            'is_unlocked' => 'boolean',
            'unlocked_at' => 'datetime',
            'metadata' => 'array',
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
     * Get the progress percentage (0–100).
     */
    public function getProgressPercentAttribute(): int
    {
        if ($this->target <= 0) {
            return 100;
        }

        return (int) min(100, round(($this->progress / $this->target) * 100));
    }

    /**
     * Rarity display colors.
     *
     * @return array{bg: string, color: string, border: string}
     */
    public function getRarityStyleAttribute(): array
    {
        return match ($this->rarity) {
            'legendary' => ['bg' => 'linear-gradient(135deg,#F59E0B,#F97316)', 'color' => '#fff', 'border' => '#F59E0B'],
            'epic' => ['bg' => 'linear-gradient(135deg,#7C3AED,#A855F7)', 'color' => '#fff', 'border' => '#7C3AED'],
            'rare' => ['bg' => '#EDE9FE', 'color' => '#7C3AED', 'border' => '#C4B5FD'],
            default => ['bg' => '#F3F4F6', 'color' => '#374151', 'border' => '#D1D5DB'],
        };
    }
}
