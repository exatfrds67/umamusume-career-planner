<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property int $character_id
 * @property string $name
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @use HasFactory<\Database\Factories\SupportDeckFactory>
 */
class SupportDeck extends Model
{
    /** @use HasFactory<\Database\Factories\SupportDeckFactory> */
    use HasFactory;

    protected $table = 'ucp_support_decks';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'character_id',
        'name',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'character_id' => 'integer',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the character that owns this deck.
     *
     * @return BelongsTo<Character, $this>
     */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /**
     * Get the support cards in this deck.
     *
     * @return BelongsToMany<SupportCard, $this>
     */
    public function supportCards(): BelongsToMany
    {
        return $this->belongsToMany(SupportCard::class, 'ucp_support_deck_cards', 'support_deck_id', 'support_card_id')
            ->withPivot(['position', 'bond_level', 'is_borrowed'])
            ->withTimestamps()
            ->orderBy('ucp_support_deck_cards.position');
    }

    /**
     * Get cards with bond level >= 80 (friendship training threshold).
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, SupportCard>
     */
    public function getFriendshipCards(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->supportCards()
            ->wherePivot('bond_level', '>=', 80)
            ->get();
    }

    /**
     * Check if this deck has friendship training available.
     */
    public function hasFriendshipTraining(): bool
    {
        return $this->supportCards()
            ->wherePivot('bond_level', '>=', 80)
            ->exists();
    }

    /**
     * Get the number of cards in this deck.
     */
    public function getCardCount(): int
    {
        return $this->supportCards()->count();
    }

    /**
     * Check if deck is full (6 cards).
     */
    public function isFull(): bool
    {
        return $this->getCardCount() >= 6;
    }

    /**
     * Validate deck has exactly 6 cards.
     */
    public function isValid(): bool
    {
        return $this->getCardCount() === 6;
    }
}
