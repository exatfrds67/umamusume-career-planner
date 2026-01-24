<?php

namespace App\Http\Resources\Api\V1;

use App\Models\SupportCard;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SupportCard
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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'card_type' => $this->card_type,
            'rarity' => $this->rarity,
            'meta_tier' => $this->meta_tier,
            'skill_hints_provided' => $this->skill_hints_provided,
            'character_name' => $this->character_name,
            'max_level' => $this->max_level,
            'max_limit_break' => $this->max_limit_break,
            'speed_bonus' => $this->speed_bonus,
            'stamina_bonus' => $this->stamina_bonus,
            'power_bonus' => $this->power_bonus,
            'guts_bonus' => $this->guts_bonus,
            'wit_bonus' => $this->wit_bonus,
            'friendship_bonus' => $this->friendship_bonus,
            'unique_effects' => $this->unique_effects,
            'is_limited' => $this->is_limited,
            'artwork_url' => $this->artwork_url,
        ];
    }
}
