<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    use HasFactory;

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
