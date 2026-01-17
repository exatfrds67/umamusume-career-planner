<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkillHint extends Model
{
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
     */
    protected function casts(): array
    {
        return [
            'training_participants' => 'array',
            'hint_metadata' => 'array',
            'guaranteed_hint' => 'boolean',
            'friendship_training' => 'boolean',
            'is_used' => 'boolean',
            'used_at' => 'datetime',
            'discount_percentage' => 'float',
        ];
    }

    /**
     * Get the character that owns the hint.
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Get the skill associated with the hint.
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
     */
    public function scopeUnused($query)
    {
        return $query->where('is_used', false);
    }

    /**
     * Scope a query to only include used hints.
     */
    public function scopeUsed($query)
    {
        return $query->where('is_used', true);
    }

    /**
     * Scope a query to only include guaranteed hints.
     */
    public function scopeGuaranteed($query)
    {
        return $query->where('guaranteed_hint', true);
    }

    /**
     * Scope a query to filter by source type.
     */
    public function scopeFromSource($query, string $sourceType)
    {
        return $query->where('source_type', $sourceType);
    }
}
