<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Skill extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'ucp_skills';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'internal_id',
        'skill_type',
        'rarity',
        'base_sp_cost',
        'evolution_target_id',
        'evolution_source_id',
        'can_evolve',
        'is_evolution',
        'effects',
        'description',
        'activation_conditions',
        'stat_requirements',
        'support_card_sources',
        'event_sources',
        'inheritance_sources',
        'meta_tier',
        'strategic_notes',
        'synergy_skills',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'effects' => 'array',
            'activation_conditions' => 'array',
            'stat_requirements' => 'array',
            'support_card_sources' => 'array',
            'event_sources' => 'array',
            'inheritance_sources' => 'array',
            'strategic_notes' => 'array',
            'synergy_skills' => 'array',
            'can_evolve' => 'boolean',
            'is_evolution' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the skill that this skill evolves into.
     */
    public function evolutionTarget(): BelongsTo
    {
        return $this->belongsTo(Skill::class, 'evolution_target_id');
    }

    /**
     * Get the skill that evolves into this skill.
     */
    public function evolutionSource(): BelongsTo
    {
        return $this->belongsTo(Skill::class, 'evolution_source_id');
    }

    /**
     * Get all hints for this skill.
     */
    public function hints(): HasMany
    {
        return $this->hasMany(SkillHint::class);
    }

    /**
     * Get all acquisitions of this skill.
     */
    public function acquisitions(): HasMany
    {
        return $this->hasMany(SkillAcquisition::class);
    }

    /**
     * Calculate the final SP cost with hint discounts.
     */
    public function calculateFinalCost(int $hintCount): int
    {
        // Maximum 40% discount (2 hints)
        $maxHints = 2;
        $effectiveHints = min($hintCount, $maxHints);

        // 20% discount per hint
        $discountPercentage = $effectiveHints * 20;

        // Calculate final cost
        $discount = ($this->base_sp_cost * $discountPercentage) / 100;

        return (int) ($this->base_sp_cost - $discount);
    }

    /**
     * Get the discount percentage for a given number of hints.
     */
    public function getDiscountPercentage(int $hintCount): float
    {
        $maxHints = 2;
        $effectiveHints = min($hintCount, $maxHints);

        return $effectiveHints * 20.0;
    }

    /**
     * Get the SP saved with hints.
     */
    public function getSpSaved(int $hintCount): int
    {
        return $this->base_sp_cost - $this->calculateFinalCost($hintCount);
    }

    /**
     * Check if this skill can evolve.
     */
    public function canEvolve(): bool
    {
        return $this->can_evolve && $this->evolution_target_id !== null;
    }

    /**
     * Check if this is an evolved skill.
     */
    public function isEvolved(): bool
    {
        return $this->is_evolution && $this->evolution_source_id !== null;
    }

    /**
     * Get the evolution chain (source -> target).
     */
    public function getEvolutionChain(): array
    {
        $chain = [];

        if ($this->evolutionSource) {
            $chain[] = $this->evolutionSource;
        }

        $chain[] = $this;

        if ($this->evolutionTarget) {
            $chain[] = $this->evolutionTarget;
        }

        return $chain;
    }

    /**
     * Scope a query to only include skills of a specific type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('skill_type', $type);
    }

    /**
     * Scope a query to only include skills of a specific rarity.
     */
    public function scopeOfRarity($query, string $rarity)
    {
        return $query->where('rarity', $rarity);
    }

    /**
     * Scope a query to only include skills that can evolve.
     */
    public function scopeCanEvolve($query)
    {
        return $query->where('can_evolve', true);
    }

    /**
     * Scope a query to only include evolved skills.
     */
    public function scopeEvolved($query)
    {
        return $query->where('is_evolution', true);
    }

    /**
     * Scope a query to only include skills of a specific meta tier.
     */
    public function scopeOfMetaTier($query, string $tier)
    {
        return $query->where('meta_tier', $tier);
    }

    /**
     * Scope a query to only include active skills.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get skills that synergize with this skill.
     */
    public function getSynergySkills(): array
    {
        if (empty($this->synergy_skills)) {
            return [];
        }

        return self::whereIn('internal_id', $this->synergy_skills)->get()->toArray();
    }

    /**
     * Check if this skill synergizes with another skill.
     */
    public function synergizesWith(Skill $otherSkill): bool
    {
        if (empty($this->synergy_skills)) {
            return false;
        }

        return in_array($otherSkill->internal_id, $this->synergy_skills);
    }
}
