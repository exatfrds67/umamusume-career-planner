<?php

namespace App\Http\Resources;

use App\Models\SupportCardDefinition;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SupportCardDefinition
 */
class SupportCardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var SupportCardDefinition $card */
        $card = $this->resource;

        return [
            'id' => $card->id,
            'name' => $card->name,
            'internal_id' => $card->internal_id,
            'card_type' => $card->card_type,
            'rarity' => $card->rarity,
            'meta_tier' => $card->meta_tier,
            'character_name' => $card->character_name,
            'character_internal_id' => $card->character_internal_id,
            'bonuses' => [
                'speed' => $card->speed_bonus,
                'stamina' => $card->stamina_bonus,
                'power' => $card->power_bonus,
                'guts' => $card->guts_bonus,
                'wit' => $card->wit_bonus,
                'friendship' => $card->friendship_bonus,
                'training_effect' => $card->training_effect_bonus,
                'event_recovery' => $card->event_recovery_bonus,
                'event_effect' => $card->event_effect_bonus,
            ],
            'skill_hints' => $card->skill_hints_provided,
            'unique_effects' => $card->unique_effects,
            'guaranteed_events' => $card->guaranteed_events,
            'is_limited' => $card->is_limited,
            'usage_rate' => $card->usage_rate,
            'win_rate_contribution' => $card->win_rate_contribution,
            'artwork_url' => $card->artwork_url,
            'flavor_text' => $card->flavor_text,
            'recommended_scenarios' => $card->recommended_scenarios,
            'deck_synergies' => $card->deck_synergies,
            'max_level' => $card->max_level,
            'max_limit_break' => $card->max_limit_break,
        ];
    }
}
