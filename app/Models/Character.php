<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $user_id
 * @property string $uuid
 * @property string $name
 * @property string|null $avatar_url
 * @property string $scenario_type
 * @property string $career_stage
 * @property int $current_turn
 * @property array<string, int> $current_stats
 * @property array<string, int> $stat_priorities
 * @property array<string, mixed> $stat_breakpoints
 * @property int $energy_level
 * @property string $mood_status
 * @property array<int, string> $conditions
 * @property int|null $days_until_race
 * @property array<string, mixed>|null $goals
 * @property array<string, mixed>|null $race_schedule
 * @property array<string, mixed>|null $training_plan
 * @property array<string, mixed>|null $growth_rates
 * @property array<string, mixed>|null $inherited_factors
 * @property array<string, mixed>|null $legacy_parents
 * @property array<string, mixed>|null $team_composition
 * @property array<string, mixed>|null $facility_levels
 * @property array<string, mixed>|null $spirit_burst_data
 * @property string $status
 * @property bool $is_pinned
 * @property bool $is_seeded
 * @property array<string, mixed>|null $completion_data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @use HasFactory<\Database\Factories\CharacterFactory>
 */
class Character extends Model
{
    /** @use HasFactory<\Database\Factories\CharacterFactory> */
    use HasFactory;

    protected $table = 'ucp_characters';

    protected $fillable = [
        'user_id',
        'uuid',
        'name',
        'avatar_url',
        'scenario_type',
        'career_stage',
        'current_turn',
        'current_stats',
        'stat_priorities',
        'stat_breakpoints',
        'energy_level',
        'mood_status',
        'conditions',
        'days_until_race',
        'goals',
        'race_schedule',
        'training_plan',
        'growth_rates',
        'inherited_factors',
        'legacy_parents',
        'team_composition',
        'facility_levels',
        'spirit_burst_data',
        'status',
        'is_pinned',
        'is_seeded',
        'completion_data',
        'available_sp',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'current_turn' => 'integer',
            'current_stats' => 'array',
            'stat_priorities' => 'array',
            'stat_breakpoints' => 'array',
            'energy_level' => 'integer',
            'conditions' => 'array',
            'days_until_race' => 'integer',
            'goals' => 'array',
            'race_schedule' => 'array',
            'training_plan' => 'array',
            'growth_rates' => 'array',
            'inherited_factors' => 'array',
            'legacy_parents' => 'array',
            'team_composition' => 'array',
            'facility_levels' => 'array',
            'spirit_burst_data' => 'array',
            'is_pinned' => 'boolean',
            'is_seeded' => 'boolean',
            'completion_data' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($character) {
            if (empty($character->uuid)) {
                $character->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Aptitude, $this>
     */
    public function aptitudes(): HasMany
    {
        return $this->hasMany(Aptitude::class);
    }

    /**
     * @return HasMany<Factor, $this>
     */
    public function factors(): HasMany
    {
        return $this->hasMany(Factor::class);
    }

    /**
     * @return HasMany<SkillAcquisition, $this>
     */
    public function skillAcquisitions(): HasMany
    {
        return $this->hasMany(SkillAcquisition::class);
    }

    /**
     * @return HasMany<Career, $this>
     */
    public function careers(): HasMany
    {
        return $this->hasMany(Career::class);
    }

    /**
     * @return HasOne<Career, $this>
     */
    public function currentCareer(): HasOne
    {
        return $this->hasOne(Career::class)->latestOfMany('current_turn');
    }

    /**
     * @return BelongsToMany<Skill, $this, SkillAcquisition>
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'ucp_skill_acquisitions', 'character_id', 'skill_id')
            ->using(SkillAcquisition::class)
            ->withPivot([
                'career_id',
                'turn_acquired',
                'career_phase',
                'acquisition_method',
                'base_sp_cost',
                'final_sp_cost',
                'is_evolution',
                'is_active',
            ])
            ->withTimestamps();
    }

    /**
     * @return HasMany<CharacterSupportCard, $this>
     */
    public function supportCards(): HasMany
    {
        return $this->hasMany(CharacterSupportCard::class);
    }

    /**
     * Get the support decks for this character.
     *
     * @return HasMany<SupportDeck, $this>
     */
    public function supportDecks(): HasMany
    {
        return $this->hasMany(SupportDeck::class);
    }

    /**
     * Get the active support deck for this character.
     *
     * @return HasOne<SupportDeck, $this>
     */
    public function activeSupportDeck(): HasOne
    {
        return $this->hasOne(SupportDeck::class)->where('is_active', true);
    }

    /**
     * Get the users who have pinned this character.
     *
     * @return BelongsToMany<User, $this>
     */
    public function pinnedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'ucp_character_user_pins')
            ->withTimestamps();
    }

    /**
     * Scope a query to only include active characters.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Character>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Character>
     */
    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to filter by scenario type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Character>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Character>
     */
    public function scopeScenarioType(\Illuminate\Database\Eloquent\Builder $query, string $type): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('scenario_type', $type);
    }

    // Accessor and Mutator for current_stats
    /**
     * Get the current_stats attribute with integer normalization.
     *
     * @param  mixed  $value
     * @return array<string, int>
     */
    protected function getCurrentStatsAttribute($value): array
    {
        $stats = is_string($value) ? json_decode($value, true) : $value;
        $stats = is_array($stats) ? $stats : [];

        return [
            'speed' => (int) ($stats['speed'] ?? 0),
            'stamina' => (int) ($stats['stamina'] ?? 0),
            'power' => (int) ($stats['power'] ?? 0),
            'guts' => (int) ($stats['guts'] ?? 0),
            'wit' => (int) ($stats['wit'] ?? 0),
        ];
    }

    /**
     * Set the current_stats attribute with integer normalization.
     *
     * @param  mixed  $value
     */
    protected function setCurrentStatsAttribute($value): void
    {
        if (! is_array($value)) {
            $value = [];
        }

        $normalized = [
            'speed' => (int) ($value['speed'] ?? 0),
            'stamina' => (int) ($value['stamina'] ?? 0),
            'power' => (int) ($value['power'] ?? 0),
            'guts' => (int) ($value['guts'] ?? 0),
            'wit' => (int) ($value['wit'] ?? 0),
        ];

        $this->attributes['current_stats'] = json_encode($normalized);
    }

    // Helper methods for stat access
    /**
     * Get a specific stat value as an integer.
     */
    public function getStat(string $stat): int
    {
        $stats = $this->current_stats;

        return (int) ($stats[$stat] ?? 0);
    }

    public function getStatGrade(int $statValue): string
    {
        return match (true) {
            $statValue >= 1200 => 'SS+',
            $statValue >= 1100 => 'SS',
            $statValue >= 1050 => 'S+',
            $statValue >= 1000 => 'S',
            $statValue >= 900 => 'A+',
            $statValue >= 800 => 'A',
            $statValue >= 700 => 'B+',
            $statValue >= 600 => 'B',
            $statValue >= 500 => 'C+',
            $statValue >= 400 => 'C',
            $statValue >= 350 => 'D+',
            $statValue >= 300 => 'D',
            $statValue >= 250 => 'E+',
            $statValue >= 200 => 'E',
            $statValue >= 150 => 'F+',
            $statValue >= 100 => 'F',
            $statValue >= 50 => 'G+',
            default => 'G',
        };
    }

    public function getProgressPercentage(): float
    {
        if (! is_array($this->goals) || ! isset($this->goals['target_stats']) || ! is_array($this->goals['target_stats'])) {
            return 0;
        }

        $stats = ['speed', 'stamina', 'power', 'guts', 'wit'];
        $totalProgress = 0;
        $statCount = 0;

        foreach ($stats as $stat) {
            $targetValue = $this->goals['target_stats'][$stat] ?? null;
            if (is_numeric($targetValue) && (float) $targetValue > 0) {
                $current = $this->getStat($stat);
                $target = (int) $targetValue;
                $progress = min(100, ($current / $target) * 100);
                $totalProgress += $progress;
                $statCount += 1;
            }
        }

        return $statCount > 0 ? round($totalProgress / $statCount, 1) : 0;
    }

    /**
     * Pin this character for quick access.
     * For seeded characters, creates a user-specific pin.
     * For user-created characters, updates the is_pinned field.
     */
    public function pin(?int $userId = null): void
    {
        if ($this->is_seeded) {
            // For seeded characters, use the pivot table
            $userId = $userId ?? auth()->id();
            if ($userId && ! $this->pinnedByUsers()->where('user_id', $userId)->exists()) {
                $this->pinnedByUsers()->attach($userId);
            }
        } else {
            // For user-created characters, update the field directly
            $this->update(['is_pinned' => true]);
        }
    }

    /**
     * Unpin this character.
     * For seeded characters, removes the user-specific pin.
     * For user-created characters, updates the is_pinned field.
     */
    public function unpin(?int $userId = null): void
    {
        if ($this->is_seeded) {
            // For seeded characters, use the pivot table
            $userId = $userId ?? auth()->id();
            if ($userId) {
                $this->pinnedByUsers()->detach($userId);
            }
        } else {
            // For user-created characters, update the field directly
            $this->update(['is_pinned' => false]);
        }
    }

    /**
     * Toggle the pinned status of this character.
     * For seeded characters, toggles the user-specific pin.
     * For user-created characters, toggles the is_pinned field.
     */
    public function togglePin(?int $userId = null): bool
    {
        if ($this->is_seeded) {
            // For seeded characters, use the pivot table
            $userId = $userId ?? auth()->id();
            if (! $userId) {
                return false;
            }

            if ($this->pinnedByUsers()->where('user_id', $userId)->exists()) {
                $this->pinnedByUsers()->detach($userId);

                return false;
            } else {
                $this->pinnedByUsers()->attach($userId);

                return true;
            }
        } else {
            // For user-created characters, update the field directly
            $this->is_pinned = ! $this->is_pinned;
            $this->save();

            return $this->is_pinned;
        }
    }

    /**
     * Check if this character is pinned by a specific user.
     */
    public function isPinnedBy(?int $userId = null): bool
    {
        $userId = $userId ?? auth()->id();
        if (! $userId) {
            return false;
        }

        if ($this->is_seeded) {
            return $this->pinnedByUsers()->where('user_id', $userId)->exists();
        } else {
            return $this->is_pinned;
        }
    }

    /**
     * Scope a query to only include pinned characters.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Character>  $query
     * @return \Illuminate\Database\Eloquent\Builder<Character>
     */
    public function scopePinned(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_pinned', true);
    }
}
