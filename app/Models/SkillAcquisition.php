<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkillAcquisition extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'ucp_skill_acquisitions';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'character_id',
        'skill_id',
        'career_id',
        'turn_acquired',
        'career_phase',
        'acquisition_method',
        'base_sp_cost',
        'hints_used',
        'total_discount_percentage',
        'final_sp_cost',
        'sp_saved',
        'is_evolution',
        'evolved_from_skill_id',
        'replaced_skill',
        'acquisition_context',
        'hint_sources',
        'priority_level',
        'races_used',
        'performance_data',
        'effectiveness_rating',
        'is_active',
        'acquisition_metadata',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'acquisition_context' => 'array',
            'hint_sources' => 'array',
            'performance_data' => 'array',
            'acquisition_metadata' => 'array',
            'is_evolution' => 'boolean',
            'replaced_skill' => 'boolean',
            'is_active' => 'boolean',
            'total_discount_percentage' => 'decimal:2',
            'effectiveness_rating' => 'decimal:2',
        ];
    }

    /**
     * Get the character that owns the acquisition.
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Get the skill that was acquired.
     */
    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    /**
     * Get the career run when the skill was acquired.
     */
    public function career(): BelongsTo
    {
        return $this->belongsTo(Career::class);
    }

    /**
     * Get the skill that evolved into this one.
     */
    public function evolvedFromSkill(): BelongsTo
    {
        return $this->belongsTo(Skill::class, 'evolved_from_skill_id');
    }

    /**
     * Calculate the SP efficiency (percentage saved).
     */
    public function getSpEfficiency(): float
    {
        if ($this->base_sp_cost === 0) {
            return 0.0;
        }

        return ($this->sp_saved / $this->base_sp_cost) * 100;
    }

    /**
     * Increment the races used counter.
     */
    public function incrementRacesUsed(): void
    {
        $this->increment('races_used');
    }

    /**
     * Update the effectiveness rating.
     */
    public function updateEffectivenessRating(float $rating): void
    {
        $this->update(['effectiveness_rating' => $rating]);
    }

    /**
     * Scope a query to only include active acquisitions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include evolved skills.
     */
    public function scopeEvolved($query)
    {
        return $query->where('is_evolution', true);
    }

    /**
     * Scope a query to filter by acquisition method.
     */
    public function scopeByMethod($query, string $method)
    {
        return $query->where('acquisition_method', $method);
    }

    /**
     * Scope a query to filter by priority level.
     */
    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority_level', $priority);
    }
}
