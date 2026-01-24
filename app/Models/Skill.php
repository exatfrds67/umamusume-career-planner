<?php

namespace App\Models;

use Database\Factories\SkillFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string|null $internal_id
 * @property string $skill_type
 * @property string $rarity
 * @property int $base_sp_cost
 * @property int|null $evolution_target_id
 * @property int|null $evolution_source_id
 * @property bool $can_evolve
 * @property bool $is_evolution
 * @property array<string, mixed>|null $effects
 * @property string|null $description
 * @property array<string, mixed>|null $activation_conditions
 * @property array<string, mixed>|null $stat_requirements
 * @property array<string, mixed>|null $support_card_sources
 * @property array<string, mixed>|null $event_sources
 * @property array<string, mixed>|null $inheritance_sources
 * @property string|null $meta_tier
 * @property array<string, mixed>|null $strategic_notes
 * @property array<int, string>|null $synergy_skills
 * @property bool $is_active
 * @property string|null $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @use HasFactory<SkillFactory>
 */
class Skill extends Model
{
    /** @use HasFactory<SkillFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'ucp_skills';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
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
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'base_sp_cost' => 'integer',
            'evolution_target_id' => 'integer',
            'evolution_source_id' => 'integer',
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
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the skill that this skill evolves into.
     *
     * @return BelongsTo<Skill, $this>
     */
    public function evolutionTarget(): BelongsTo
    {
        return $this->belongsTo(Skill::class, 'evolution_target_id');
    }

    /**
     * Get the skill that evolves into this skill.
     *
     * @return BelongsTo<Skill, $this>
     */
    public function evolutionSource(): BelongsTo
    {
        return $this->belongsTo(Skill::class, 'evolution_source_id');
    }

    /**
     * Get all hints for this skill.
     *
     * @return HasMany<SkillHint, $this>
     */
    public function hints(): HasMany
    {
        return $this->hasMany(SkillHint::class);
    }

    /**
     * Get all acquisitions of this skill.
     *
     * @return HasMany<SkillAcquisition, $this>
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
     *
     * @return array<int, Skill>
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
     *
     * @param  Builder<Skill>  $query
     * @return Builder<Skill>
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('skill_type', $type);
    }

    /**
     * Scope a query to only include skills of a specific rarity.
     *
     * @param  Builder<Skill>  $query
     * @return Builder<Skill>
     */
    public function scopeOfRarity(Builder $query, string $rarity): Builder
    {
        return $query->where('rarity', $rarity);
    }

    /**
     * Scope a query to only include skills that can evolve.
     *
     * @param  Builder<Skill>  $query
     * @return Builder<Skill>
     */
    public function scopeCanEvolve(Builder $query): Builder
    {
        return $query->where('can_evolve', true);
    }

    /**
     * Scope a query to only include evolved skills.
     *
     * @param  Builder<Skill>  $query
     * @return Builder<Skill>
     */
    public function scopeEvolved(Builder $query): Builder
    {
        return $query->where('is_evolution', true);
    }

    /**
     * Scope a query to only include skills of a specific meta tier.
     *
     * @param  Builder<Skill>  $query
     * @return Builder<Skill>
     */
    public function scopeOfMetaTier(Builder $query, string $tier): Builder
    {
        return $query->where('meta_tier', $tier);
    }

    /**
     * Scope a query to only include active skills.
     *
     * @param  Builder<Skill>  $query
     * @return Builder<Skill>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get skills that synergize with this skill.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getSynergySkills(): array
    {
        if (empty($this->synergy_skills) || ! is_array($this->synergy_skills)) {
            return [];
        }

        /** @var array<int, array<string, mixed>> $skills */
        $skills = self::whereIn('internal_id', $this->synergy_skills)->get()->toArray();

        return $skills;
    }

    /**
     * Check if this skill synergizes with another skill.
     */
    public function synergizesWith(Skill $otherSkill): bool
    {
        if (empty($this->synergy_skills) || ! is_array($this->synergy_skills)) {
            return false;
        }

        return in_array($otherSkill->internal_id, $this->synergy_skills);
    }
}
