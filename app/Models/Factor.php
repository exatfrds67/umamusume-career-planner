<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $character_id
 * @property string $factor_type
 * @property string|null $factor_name
 * @property int|null $star_level
 * @property string|null $stat_type
 * @property int|null $stat_bonus
 * @property string|null $aptitude_type
 * @property string|null $grade_improvement
 * @property string|null $unique_skill_name
 * @property array<string, mixed>|null $skill_effects
 * @property string|null $normal_skill_name
 * @property array<string, mixed>|null $race_bonuses
 * @property string|null $source_parent
 * @property string|null $source_character_name
 * @property float|null $inheritance_rate
 * @property bool $affinity_compatible
 * @property bool $is_active
 * @property array<string, mixed>|null $factor_metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @use HasFactory<\Database\Factories\FactorFactory>
 */
class Factor extends Model
{
    use HasFactory;

    protected $table = 'ucp_factors';

    protected $fillable = [
        'character_id',
        'factor_type',
        'factor_name',
        'star_level',
        'stat_type',
        'stat_bonus',
        'aptitude_type',
        'grade_improvement',
        'unique_skill_name',
        'skill_effects',
        'normal_skill_name',
        'race_bonuses',
        'source_parent',
        'source_character_name',
        'inheritance_rate',
        'affinity_compatible',
        'is_active',
        'factor_metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'character_id' => 'integer',
            'star_level' => 'integer',
            'stat_bonus' => 'integer',
            'skill_effects' => 'array',
            'race_bonuses' => 'array',
            'factor_metadata' => 'array',
            'inheritance_rate' => 'decimal:2',
            'affinity_compatible' => 'boolean',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Character, $this>
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }
}
