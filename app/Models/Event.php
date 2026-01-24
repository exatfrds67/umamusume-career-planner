<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $career_id
 * @property int $character_id
 * @property int|null $source_support_card_id
 * @property array<string, mixed>|null $available_choices
 * @property array<string, mixed>|null $choice_effects
 * @property array<string, mixed>|null $skill_hints_gained
 * @property array<string, mixed>|null $skills_learned
 * @property array<string, mixed>|null $items_gained
 * @property array<string, mixed>|null $special_effects
 * @property array<string, mixed>|null $friendship_changes
 * @property array<string, mixed>|null $bond_changes
 * @property array<string, mixed>|null $relationship_effects
 * @property array<string, mixed>|null $activation_conditions
 * @property array<string, mixed>|null $character_state_before
 * @property array<string, mixed>|null $character_state_after
 * @property array<string, mixed>|null $lessons_learned
 * @property array<string, mixed>|null $event_metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Event extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ucp_events';

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'career_id' => 'integer',
            'character_id' => 'integer',
            'source_support_card_id' => 'integer',
            'available_choices' => 'array',
            'choice_effects' => 'array',
            'skill_hints_gained' => 'array',
            'skills_learned' => 'array',
            'items_gained' => 'array',
            'special_effects' => 'array',
            'friendship_changes' => 'array',
            'bond_changes' => 'array',
            'relationship_effects' => 'array',
            'activation_conditions' => 'array',
            'character_state_before' => 'array',
            'character_state_after' => 'array',
            'lessons_learned' => 'array',
            'event_metadata' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
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
     * @return BelongsTo<Character, $this>
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * @return BelongsTo<SupportCard, $this>
     */
    public function sourceSupportCard(): BelongsTo
    {
        return $this->belongsTo(SupportCard::class, 'source_support_card_id');
    }
}
