<?php

namespace App\Models;

use Database\Factories\SkillAcquisitionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $id
 * @property int $character_id
 * @property int $skill_id
 * @property int|null $career_id
 * @property int|null $turn_acquired
 * @property string|null $career_phase
 * @property string|null $acquisition_method
 * @property int|null $base_sp_cost
 * @property int|null $hints_used
 * @property float|null $total_discount_percentage
 * @property int|null $final_sp_cost
 * @property int|null $sp_saved
 * @property bool $is_evolution
 * @property int|null $evolved_from_skill_id
 * @property bool $replaced_skill
 * @property array<string, mixed>|null $acquisition_context
 * @property array<string, mixed>|null $hint_sources
 * @property string|null $priority_level
 * @property int|null $races_used
 * @property array<string, mixed>|null $performance_data
 * @property float|null $effectiveness_rating
 * @property bool $is_active
 * @property array<string, mixed>|null $acquisition_metadata
 * @property bool $is_equipped
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @use HasFactory<SkillAcquisitionFactory>
 */
class SkillAcquisition extends Pivot
{
    /** @use HasFactory<SkillAcquisitionFactory> */
    use HasFactory;

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = true;

    /**
     * The "type" of the primary key ID.
     */
    protected $keyType = 'int';

    /**
     * The table associated with the model.
     */
    protected $table = 'ucp_skill_acquisitions';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
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
        'is_equipped',
        'hint_level',
        'sp_discount_applied',
        'sp_cost',
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
            'character_id' => 'integer',
            'skill_id' => 'integer',
            'career_id' => 'integer',
            'turn_acquired' => 'integer',
            'base_sp_cost' => 'integer',
            'hints_used' => 'integer',
            'final_sp_cost' => 'integer',
            'sp_saved' => 'integer',
            'evolved_from_skill_id' => 'integer',
            'races_used' => 'integer',
            'acquisition_context' => 'array',
            'hint_sources' => 'array',
            'performance_data' => 'array',
            'acquisition_metadata' => 'array',
            'is_evolution' => 'boolean',
            'replaced_skill' => 'boolean',
            'is_active' => 'boolean',
            'total_discount_percentage' => 'decimal:2',
            'effectiveness_rating' => 'decimal:2',
            'is_equipped' => 'boolean',
            'hint_level' => 'integer',
            'sp_discount_applied' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Whether the skill is currently equipped. Defaults to false when absent.
     *
     * @return Attribute<bool, never>
     */
    protected function isEquipped(): Attribute
    {
        return Attribute::get(fn ($value) => (bool) ($value ?? false));
    }

    /**
     * Get the character that owns the acquisition.
     *
     * @return BelongsTo<Character, $this>
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Get the skill that was acquired.
     *
     * @return BelongsTo<Skill, $this>
     */
    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    /**
     * Get the career run when the skill was acquired.
     *
     * @return BelongsTo<Career, $this>
     */
    public function career(): BelongsTo
    {
        return $this->belongsTo(Career::class);
    }

    /**
     * Get the skill that evolved into this one.
     *
     * @return BelongsTo<Skill, $this>
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
        $this->effectiveness_rating = $rating;
        $this->save();
    }

    /**
     * Scope a query to only include active acquisitions.
     *
     * @param  Builder<SkillAcquisition>  $query
     * @return Builder<SkillAcquisition>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include evolved skills.
     *
     * @param  Builder<SkillAcquisition>  $query
     * @return Builder<SkillAcquisition>
     */
    public function scopeEvolved(Builder $query): Builder
    {
        return $query->where('is_evolution', true);
    }

    /**
     * Scope a query to filter by acquisition method.
     *
     * @param  Builder<SkillAcquisition>  $query
     * @return Builder<SkillAcquisition>
     */
    public function scopeByMethod(Builder $query, string $method): Builder
    {
        return $query->where('acquisition_method', $method);
    }

    /**
     * Scope a query to filter by priority level.
     *
     * @param  Builder<SkillAcquisition>  $query
     * @return Builder<SkillAcquisition>
     */
    public function scopeByPriority(Builder $query, string $priority): Builder
    {
        return $query->where('priority_level', $priority);
    }
}
