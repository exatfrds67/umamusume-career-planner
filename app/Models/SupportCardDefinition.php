<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Support Card Definition
 * Reference data for support cards in the game
 *
 * @property int $id
 * @property string $name
 * @property string|null $internal_id
 * @property string $card_type
 * @property string $rarity
 * @property int|null $max_level
 * @property int|null $max_limit_break
 * @property string|null $character_name
 * @property string|null $character_internal_id
 * @property int|null $speed_bonus
 * @property int|null $stamina_bonus
 * @property int|null $power_bonus
 * @property int|null $guts_bonus
 * @property int|null $wit_bonus
 * @property int|null $friendship_bonus
 * @property int|null $event_recovery_bonus
 * @property int|null $event_effect_bonus
 * @property int|null $training_effect_bonus
 * @property array<string, mixed>|null $unique_effects
 * @property array<string, mixed>|null $skill_hints_provided
 * @property array<string, mixed>|null $guaranteed_events
 * @property array<string, mixed>|null $special_conditions
 * @property bool $is_limited
 * @property \Illuminate\Support\Carbon|null $release_date
 * @property \Illuminate\Support\Carbon|null $availability_end
 * @property array<string, mixed>|null $acquisition_methods
 * @property string|null $meta_tier
 * @property array<string, mixed>|null $deck_synergies
 * @property array<string, mixed>|null $recommended_scenarios
 * @property array<string, mixed>|null $strategic_notes
 * @property float|null $usage_rate
 * @property float|null $win_rate_contribution
 * @property array<string, mixed>|null $performance_data
 * @property string|null $artwork_url
 * @property array<string, mixed>|null $artwork_variants
 * @property string|null $flavor_text
 * @property bool $is_active
 * @property array<string, mixed>|null $card_metadata
 * @property int|null $friendship_level
 * @property int|null $limit_break_level
 * @property string|null $specialization
 * @property array<int, array<string, mixed>>|null $skill_provision
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @use HasFactory<\Database\Factories\SupportCardDefinitionFactory>
 */
/**
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class SupportCardDefinition extends Model
{
    use HasFactory;

    protected $table = 'ucp_support_cards';

    protected $fillable = [
        'name',
        'internal_id',
        'card_type',
        'rarity',
        'max_level',
        'max_limit_break',
        'character_name',
        'character_internal_id',
        'speed_bonus',
        'stamina_bonus',
        'power_bonus',
        'guts_bonus',
        'wit_bonus',
        'friendship_bonus',
        'event_recovery_bonus',
        'event_effect_bonus',
        'training_effect_bonus',
        'unique_effects',
        'skill_hints_provided',
        'guaranteed_events',
        'special_conditions',
        'is_limited',
        'release_date',
        'availability_end',
        'acquisition_methods',
        'meta_tier',
        'deck_synergies',
        'recommended_scenarios',
        'strategic_notes',
        'usage_rate',
        'win_rate_contribution',
        'performance_data',
        'artwork_url',
        'artwork_variants',
        'flavor_text',
        'is_active',
        'card_metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'max_level' => 'integer',
            'max_limit_break' => 'integer',
            'speed_bonus' => 'integer',
            'stamina_bonus' => 'integer',
            'power_bonus' => 'integer',
            'guts_bonus' => 'integer',
            'wit_bonus' => 'integer',
            'friendship_bonus' => 'integer',
            'event_recovery_bonus' => 'integer',
            'event_effect_bonus' => 'integer',
            'training_effect_bonus' => 'integer',
            'unique_effects' => 'array',
            'skill_hints_provided' => 'array',
            'guaranteed_events' => 'array',
            'special_conditions' => 'array',
            'acquisition_methods' => 'array',
            'deck_synergies' => 'array',
            'recommended_scenarios' => 'array',
            'strategic_notes' => 'array',
            'performance_data' => 'array',
            'artwork_variants' => 'array',
            'card_metadata' => 'array',
            'is_limited' => 'boolean',
            'is_active' => 'boolean',
            'release_date' => 'date',
            'availability_end' => 'date',
            'usage_rate' => 'decimal:2',
            'win_rate_contribution' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
