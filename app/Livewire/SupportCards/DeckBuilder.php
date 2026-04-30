<?php

declare(strict_types=1);

namespace App\Livewire\SupportCards;

use App\Models\Character;
use App\Models\CharacterSupportCard;
use App\Models\SupportCardDefinition;
use App\Services\DeckManagementService;
use App\Services\SupportCardDeckService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class DeckBuilder extends Component
{
    // Character selection
    public ?int $characterId = null;

    // Limit break modal
    public bool $showLimitBreakModal = false;

    public ?int $limitBreakSlot = null;

    public int $newLimitBreakLevel = 0;

    // Card picker
    public bool $showCardPicker = false;

    public ?int $pickerSlot = null;

    public string $pickerSearch = '';

    public string $pickerTypeFilter = '';

    // Toast
    public ?string $toastMessage = null;

    public string $toastType = 'success';

    /**
     * Mount with optional character pre-selection.
     */
    public function mount(?int $characterId = null): void
    {
        if ($characterId) {
            $this->characterId = $characterId;
        } else {
            $character = Character::where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->first();
            $this->characterId = $character?->id;
        }
    }

    /**
     * Get the active character.
     */
    #[Computed]
    public function character(): ?Character
    {
        if (! $this->characterId) {
            return null;
        }

        return Character::where('id', $this->characterId)
            ->where('user_id', Auth::id())
            ->first();
    }

    /**
     * Get all characters for the selector.
     *
     * @return Collection<int, Character>
     */
    #[Computed]
    public function characters(): Collection
    {
        return Character::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get(['id', 'name']);
    }

    /**
     * Get the current deck (6 slots, null for empty).
     *
     * @return array<int, CharacterSupportCard|null>
     */
    #[Computed]
    public function deckSlots(): array
    {
        if (! $this->characterId) {
            return array_fill(1, 6, null);
        }

        $cards = CharacterSupportCard::where('character_id', $this->characterId)
            ->with('supportCard')
            ->orderBy('position_slot')
            ->get()
            ->keyBy('position_slot');

        $slots = [];
        for ($i = 1; $i <= 6; $i++) {
            $slots[$i] = $cards->get($i);
        }

        return $slots;
    }

    /**
     * Synergy score for the current deck.
     */
    #[Computed]
    public function synergyScore(): float
    {
        $character = $this->character;
        if (! $character) {
            return 0.0;
        }

        $service = app(SupportCardDeckService::class);

        return $service->calculateSynergyScore($character);
    }

    /**
     * Average bond percentage across deck.
     */
    #[Computed]
    public function avgBond(): float
    {
        $slots = array_filter($this->deckSlots);
        if (empty($slots)) {
            return 0.0;
        }

        $total = array_sum(array_map(fn ($c) => $c->friendship_level ?? 0, $slots));

        return round($total / \count($slots), 1);
    }

    /**
     * Count of cards at friendship threshold (≥80%).
     */
    #[Computed]
    public function friendshipActiveCount(): int
    {
        return \count(array_filter($this->deckSlots, fn ($c) => $c && ($c->friendship_level ?? 0) >= 80));
    }

    /**
     * Count of SSR cards in deck.
     */
    #[Computed]
    public function ssrCount(): int
    {
        return \count(array_filter($this->deckSlots, fn ($c) => $c && ($c->supportCard?->rarity === 'SSR')));
    }

    /**
     * Deck validation result.
     *
     * @return array{is_valid: bool, errors: list<string>, warnings: list<string>}
     */
    #[Computed]
    public function validation(): array
    {
        if (! $this->characterId) {
            return ['is_valid' => false, 'errors' => ['No character selected.'], 'warnings' => []];
        }

        $service = app(DeckManagementService::class);

        return $service->validateDeck($this->characterId);
    }

    /**
     * Get available cards for the picker.
     *
     * @return Collection<int, SupportCardDefinition>
     */
    #[Computed]
    public function availableCards(): Collection
    {
        $query = SupportCardDefinition::where('is_active', true);

        if ($this->pickerSearch !== '') {
            $search = $this->pickerSearch;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('character_name', 'like', "%{$search}%");
            });
        }

        if ($this->pickerTypeFilter !== '') {
            $query->where('card_type', $this->pickerTypeFilter);
        }

        return $query->orderByRaw("CASE meta_tier WHEN 'S+' THEN 1 WHEN 'S' THEN 2 WHEN 'A' THEN 3 WHEN 'B' THEN 4 ELSE 5 END")
            ->orderByRaw("CASE rarity WHEN 'SSR' THEN 1 WHEN 'SR' THEN 2 WHEN 'R' THEN 3 ELSE 4 END")
            ->limit(50)
            ->get();
    }

    /**
     * Open the card picker for a slot.
     */
    public function openCardPicker(int $slot): void
    {
        $this->pickerSlot = $slot;
        $this->pickerSearch = '';
        $this->pickerTypeFilter = '';
        $this->showCardPicker = true;
    }

    /**
     * Close the card picker.
     */
    public function closeCardPicker(): void
    {
        $this->showCardPicker = false;
        $this->pickerSlot = null;
    }

    /**
     * Select a card for the current picker slot.
     */
    public function selectCard(int $cardId, DeckManagementService $deckService): void
    {
        if (! $this->characterId || ! $this->pickerSlot) {
            return;
        }

        $slot = $this->pickerSlot;
        $existing = CharacterSupportCard::where('character_id', $this->characterId)
            ->where('position_slot', $slot)
            ->first();

        try {
            if ($existing) {
                $deckService->replaceCard($this->characterId, $slot, $cardId, 0);
            } else {
                $isFriend = ($slot === 6);
                $deckService->addCardToDeck($this->characterId, $cardId, $slot, $isFriend, 0);
            }

            $this->closeCardPicker();
            $this->showToast('Card added to deck!', 'success');
            unset($this->deckSlots, $this->synergyScore, $this->avgBond, $this->friendshipActiveCount, $this->ssrCount, $this->validation);
        } catch (\Exception $e) {
            $this->showToast($e->getMessage(), 'error');
        }
    }

    /**
     * Remove a card from a slot.
     */
    public function removeCard(int $slot, DeckManagementService $deckService): void
    {
        if (! $this->characterId) {
            return;
        }

        $deckService->removeCardFromDeck($this->characterId, $slot);
        $this->showToast('Card removed.', 'success');
        unset($this->deckSlots, $this->synergyScore, $this->avgBond, $this->friendshipActiveCount, $this->ssrCount, $this->validation);
    }

    /**
     * Open the limit break modal for a slot.
     */
    public function openLimitBreakModal(int $slot): void
    {
        $card = $this->deckSlots[$slot] ?? null;
        if (! $card) {
            return;
        }

        $this->limitBreakSlot = $slot;
        $this->newLimitBreakLevel = $card->limit_break_level ?? 0;
        $this->showLimitBreakModal = true;
    }

    /**
     * Close the limit break modal.
     */
    public function closeLimitBreakModal(): void
    {
        $this->showLimitBreakModal = false;
        $this->limitBreakSlot = null;
    }

    /**
     * Confirm limit break upgrade.
     */
    public function confirmLimitBreak(DeckManagementService $deckService): void
    {
        if (! $this->characterId || ! $this->limitBreakSlot) {
            return;
        }

        $card = $this->deckSlots[$this->limitBreakSlot] ?? null;
        if (! $card) {
            return;
        }

        $result = $deckService->updateCardDetails(
            $this->characterId,
            $this->limitBreakSlot,
            $this->newLimitBreakLevel,
            $card->friendship_level ?? 0
        );

        if ($result) {
            $this->showToast("⬆️ Limit Break updated to Lv.{$this->newLimitBreakLevel}!", 'success');
            $this->closeLimitBreakModal();
            unset($this->deckSlots, $this->synergyScore, $this->validation);
        } else {
            $this->showToast('Failed to update limit break.', 'error');
        }
    }

    /**
     * Update bond level for a card.
     */
    public function updateBond(int $slot, int $bondLevel, DeckManagementService $deckService): void
    {
        if (! $this->characterId) {
            return;
        }

        $card = $this->deckSlots[$slot] ?? null;
        if (! $card) {
            return;
        }

        $deckService->updateCardDetails(
            $this->characterId,
            $slot,
            $card->limit_break_level ?? 0,
            $bondLevel
        );

        unset($this->deckSlots, $this->avgBond, $this->friendshipActiveCount, $this->synergyScore);
    }

    /**
     * Show a toast notification.
     */
    protected function showToast(string $message, string $type = 'success'): void
    {
        $this->toastMessage = $message;
        $this->toastType = $type;
    }

    /**
     * Dismiss the toast.
     */
    public function dismissToast(): void
    {
        $this->toastMessage = null;
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.support-cards.deck-builder');
    }
}
