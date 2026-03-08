<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SparkType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Inheritance Event Model
 *
 * Represents one of the 3 Inspiration Events in a career run.
 * Each event produces a spark from a parent character that grants bonuses.
 *
 * @property int $id
 * @property int $career_id
 * @property int|null $parent_character_id
 * @property int $event_number
 * @property \App\Enums\SparkType $spark_type
 * @property int $star_level
 * @property string|null $target_stat
 * @property int|null $stat_bonus
 * @property float|null $growth_rate_bonus
 * @property int|null $sp_bonus
 * @property string|null $inherited_skill_name
 * @property array<string, mixed>|null $inherited_skill_data
 * @property array<string, mixed>|null $choices_available
 * @property array<string, mixed>|null $choice_made
 * @property bool $is_applied
 * @property array<string, mixed>|null $event_metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class InheritanceEvent extends Model
{
    /** @use HasFactory<\Database\Factories\InheritanceEventFactory> */
    use HasFactory;

    protected $table = 'ucp_inheritance_events';

    protected $fillable = [
        'career_id',
        'parent_character_id',
        'event_number',
        'spark_type',
        'star_level',
        'target_stat',
        'stat_bonus',
        'growth_rate_bonus',
        'sp_bonus',
        'inherited_skill_name',
        'inherited_skill_data',
        'choices_available',
        'choice_made',
        'is_applied',
        'event_metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'career_id' => 'integer',
            'parent_character_id' => 'integer',
            'event_number' => 'integer',
            'spark_type' => SparkType::class,
            'star_level' => 'integer',
            'stat_bonus' => 'integer',
            'growth_rate_bonus' => 'decimal:2',
            'sp_bonus' => 'integer',
            'inherited_skill_data' => 'array',
            'choices_available' => 'array',
            'choice_made' => 'array',
            'is_applied' => 'boolean',
            'event_metadata' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Career, $this>
     */
    public function career(): BelongsTo
    {
        return $this->belongsTo(Career::class);
    }

    /**
     * @return BelongsTo<ParentCharacter, $this>
     */
    public function parentCharacter(): BelongsTo
    {
        return $this->belongsTo(ParentCharacter::class);
    }

    /**
     * Check if this is a Blue (stat bonus) spark.
     */
    public function isStatBonus(): bool
    {
        return $this->spark_type === SparkType::Blue;
    }

    /**
     * Check if this is a Green (growth rate) spark.
     */
    public function isGrowthRateBonus(): bool
    {
        return $this->spark_type === SparkType::Green;
    }

    /**
     * Check if this is a Pink (skill inheritance) spark.
     */
    public function isSkillInheritance(): bool
    {
        return $this->spark_type === SparkType::Pink;
    }

    /**
     * Check if this is a White (SP bonus) spark.
     */
    public function isSpBonus(): bool
    {
        return $this->spark_type === SparkType::White;
    }
}
