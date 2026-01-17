<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Support Card Definition
 * Reference data for support cards in the game
 *
 * @use HasFactory<\Database\Factories\SupportCardDefinitionFactory>
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

    protected function casts(): array
    {
        return [
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
        ];
    }
}
