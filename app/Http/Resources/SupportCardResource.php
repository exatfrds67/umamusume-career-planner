<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupportCardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'internal_id' => $this->internal_id,
            'card_type' => $this->card_type,
            'rarity' => $this->rarity,
            'meta_tier' => $this->meta_tier,
            'character_name' => $this->character_name,
            'character_internal_id' => $this->character_internal_id,
            'bonuses' => [
                'speed' => $this->speed_bonus,
                'stamina' => $this->stamina_bonus,
                'power' => $this->power_bonus,
                'guts' => $this->guts_bonus,
                'wit' => $this->wit_bonus,
                'friendship' => $this->friendship_bonus,
                'training_effect' => $this->training_effect_bonus,
                'event_recovery' => $this->event_recovery_bonus,
                'event_effect' => $this->event_effect_bonus,
            ],
            'skill_hints' => $this->skill_hints_provided,
            'unique_effects' => $this->unique_effects,
            'guaranteed_events' => $this->guaranteed_events,
            'is_limited' => $this->is_limited,
            'usage_rate' => $this->usage_rate,
            'win_rate_contribution' => $this->win_rate_contribution,
            'artwork_url' => $this->artwork_url,
            'flavor_text' => $this->flavor_text,
            'recommended_scenarios' => $this->recommended_scenarios,
            'deck_synergies' => $this->deck_synergies,
            'max_level' => $this->max_level,
            'max_limit_break' => $this->max_limit_break,
        ];
    }
}
