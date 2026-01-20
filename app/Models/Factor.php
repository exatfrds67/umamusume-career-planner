<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
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

    protected function casts(): array
    {
        return [
            'skill_effects' => 'array',
            'race_bonuses' => 'array',
            'factor_metadata' => 'array',
            'inheritance_rate' => 'decimal:2',
            'affinity_compatible' => 'boolean',
            'is_active' => 'boolean',
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
