<?php

namespace App\Models;

use Database\Factories\SkillHintFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $character_id
 * @property int $skill_id
 * @property string $source_type
 * @property string|null $source_name
 * @property int|null $source_id
 * @property int|null $turn_obtained
 * @property string|null $career_phase
 * @property bool $guaranteed_hint
 * @property string|null $training_type
 * @property array<string, mixed>|null $training_participants
 * @property bool $friendship_training
 * @property float|null $discount_percentage
 * @property bool $is_used
 * @property \Illuminate\Support\Carbon|null $used_at
 * @property array<string, mixed>|null $hint_metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @use HasFactory<SkillHintFactory>
 */
class SkillHint extends Model
{
    /** @use HasFactory<SkillHintFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'ucp_skill_hints';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'character_id',
        'skill_id',
        'source_type',
        'source_name',
        'source_id',
        'turn_obtained',
        'career_phase',
        'guaranteed_hint',
        'training_type',
        'training_participants',
        'friendship_training',
        'discount_percentage',
        'is_used',
        'used_at',
        'hint_metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'character_id' => 'integer',
            'skill_id' => 'integer',
            'source_id' => 'integer',
            'turn_obtained' => 'integer',
            'training_participants' => 'array',
            'hint_metadata' => 'array',
            'guaranteed_hint' => 'boolean',
            'friendship_training' => 'boolean',
            'is_used' => 'boolean',
            'used_at' => 'datetime',
            'discount_percentage' => 'float',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the character that owns the hint.
     *
     * @return BelongsTo<Character, $this>
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Get the skill associated with the hint.
     *
     * @return BelongsTo<Skill, $this>
     */
    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    /**
     * Mark the hint as used.
     */
    public function markAsUsed(): void
    {
        $this->update([
            'is_used' => true,
            'used_at' => now(),
        ]);
    }

    /**
     * Scope a query to only include unused hints.
     *
     * @param  Builder<SkillHint>  $query
     * @return Builder<SkillHint>
     */
    public function scopeUnused(Builder $query): Builder
    {
        return $query->where('is_used', false);
    }

    /**
     * Scope a query to only include used hints.
     *
     * @param  Builder<SkillHint>  $query
     * @return Builder<SkillHint>
     */
    public function scopeUsed(Builder $query): Builder
    {
        return $query->where('is_used', true);
    }

    /**
     * Scope a query to only include guaranteed hints.
     *
     * @param  Builder<SkillHint>  $query
     * @return Builder<SkillHint>
     */
    public function scopeGuaranteed(Builder $query): Builder
    {
        return $query->where('guaranteed_hint', true);
    }

    /**
     * Scope a query to filter by source type.
     *
     * @param  Builder<SkillHint>  $query
     * @return Builder<SkillHint>
     */
    public function scopeFromSource(Builder $query, string $sourceType): Builder
    {
        return $query->where('source_type', $sourceType);
    }
}
