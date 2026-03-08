<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AffinityGrade;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Parent Character Model
 *
 * Represents a parent in the Inheritance (Legacy) system.
 * Each career can have up to 2 parents that contribute sparks at Inspiration Events.
 *
 * @property int $id
 * @property int $career_id
 * @property string $slot
 * @property string $character_name
 * @property string|null $scenario_type
 * @property string|null $running_style
 * @property string|null $preferred_distance
 * @property int $final_speed
 * @property int $final_stamina
 * @property int $final_power
 * @property int $final_guts
 * @property int $final_wit
 * @property string $affinity_grade
 * @property array<string, mixed>|null $skill_pool
 * @property array<string, mixed>|null $factor_summary
 * @property array<string, mixed>|null $parent_metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class ParentCharacter extends Model
{
    /** @use HasFactory<\Database\Factories\ParentCharacterFactory> */
    use HasFactory;

    protected $table = 'ucp_parent_characters';

    protected $fillable = [
        'career_id',
        'slot',
        'character_name',
        'scenario_type',
        'running_style',
        'preferred_distance',
        'final_speed',
        'final_stamina',
        'final_power',
        'final_guts',
        'final_wit',
        'affinity_grade',
        'skill_pool',
        'factor_summary',
        'parent_metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'career_id' => 'integer',
            'final_speed' => 'integer',
            'final_stamina' => 'integer',
            'final_power' => 'integer',
            'final_guts' => 'integer',
            'final_wit' => 'integer',
            'affinity_grade' => AffinityGrade::class,
            'skill_pool' => 'array',
            'factor_summary' => 'array',
            'parent_metadata' => 'array',
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
     * @return HasMany<InheritanceEvent, $this>
     */
    public function inheritanceEvents(): HasMany
    {
        return $this->hasMany(InheritanceEvent::class);
    }

    /**
     * Get the stat value for a given stat type.
     */
    public function getStatValue(string $statType): int
    {
        $field = 'final_'.$statType;

        return (int) ($this->{$field} ?? 0);
    }

    /**
     * Get the highest stat type and value.
     *
     * @return array{stat: string, value: int}
     */
    public function getHighestStat(): array
    {
        $stats = [
            'speed' => $this->final_speed,
            'stamina' => $this->final_stamina,
            'power' => $this->final_power,
            'guts' => $this->final_guts,
            'wit' => $this->final_wit,
        ];

        $maxStat = array_keys($stats, max($stats))[0];

        return ['stat' => $maxStat, 'value' => $stats[$maxStat]];
    }
}
