<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Character Support Card Instance
 * Represents a support card equipped to a specific character
 *
 * @property int $id
 * @property int $character_id
 * @property int $support_card_id
 * @property int|null $limit_break_level
 * @property int|null $friendship_level
 * @property int|null $position_slot
 * @property bool $is_friend_card
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @use HasFactory<\Database\Factories\CharacterSupportCardFactory>
 */
/**
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class CharacterSupportCard extends Model
{
    /** @use HasFactory<\Database\Factories\CharacterSupportCardFactory> */
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

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'character_id' => 'integer',
            'support_card_id' => 'integer',
            'limit_break_level' => 'integer',
            'friendship_level' => 'integer',
            'position_slot' => 'integer',
            'is_friend_card' => 'boolean',
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

    /**
     * @return BelongsTo<SupportCardDefinition, $this>
     */
    public function supportCard(): BelongsTo
    {
        return $this->belongsTo(SupportCardDefinition::class, 'support_card_id');
    }
}
