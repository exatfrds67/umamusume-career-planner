<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Character Support Card Instance
 * Represents a support card equipped to a specific character
 *
 * @use HasFactory<\Database\Factories\CharacterSupportCardFactory>
 */
class CharacterSupportCard extends Model
{
    use HasFactory;

    protected $table = 'character_support_cards';

    protected $fillable = [
        'character_id',
        'support_card_id',
        'limit_break_level',
        'friendship_level',
        'position_slot',
        'is_friend_card',
    ];

    protected function casts(): array
    {
        return [
            'is_friend_card' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Character, $this>
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * @return BelongsTo<SupportCardDefinition, $this>
     */
    public function supportCard(): BelongsTo
    {
        return $this->belongsTo(SupportCardDefinition::class, 'support_card_id');
    }
}
