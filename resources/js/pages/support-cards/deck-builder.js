/**
 * Deck Builder Page Script
 * Handles deck building functionality for support cards
 */

// Access data from window.deckBuilderData (injected by Blade)
const { deck, availableCards, characterId } = window.deckBuilderData || {};

// Initialize Alpine component
document.addEventListener("alpine:init", () => {
    Alpine.data("deckBuilder", () => ({
        // State
        deck: deck || [],
        availableCards: availableCards || [],
        characterId: characterId || null,
        searchQuery: "",
        filterType: "",
        filterTier: "",
        showEditModal: false,
        editForm: {
            slotIndex: null,
            limitBreak: 0,
            bondLevel: 0,
        },
        validationErrors: [],
        validationWarnings: [],
        draggedSlot: null,

        // Computed properties
        get deckCount() {
            return this.deck.filter((card) => card !== null).length;
        },

        get friendCardCount() {
            const friendCard = this.deck[5]; // Slot 6 (index 5)
            return friendCard && friendCard.is_friend_card ? 1 : 0;
        },

        get isDeckValid() {
            return this.deckCount === 6 && this.friendCardCount === 1;
        },

        get uniqueTypes() {
            const types = new Set();
            this.deck.forEach((card) => {
                if (card && card.supportCard) {
                    types.add(card.supportCard.card_type);
                }
            });
            return types.size;
        },

        get typeDistribution() {
            const distribution = {};
            this.deck.forEach((card) => {
                if (card && card.supportCard) {
                    const type = card.supportCard.card_type;
                    distribution[type] = (distribution[type] || 0) + 1;
                }
            });
            return distribution;
        },

        get synergyScore() {
            // Simple synergy calculation based on type diversity and card quality
            let score = 0;

            // Type diversity bonus (max 40 points)
            score += this.uniqueTypes * 8;

            // Card quality bonus (max 40 points)
            const avgLB = this.averageLimitBreak;
            score += (avgLB / 4) * 40;

            // Completion bonus (max 20 points)
            if (this.isDeckValid) {
                score += 20;
            }

            return Math.min(100, Math.round(score));
        },

        get averageBond() {
            const cards = this.deck.filter((c) => c !== null);
            if (cards.length === 0) return 0;
            const sum = cards.reduce(
                (acc, card) => acc + (card.friendship_level || 0),
                0,
            );
            return Math.round(sum / cards.length);
        },

        get averageLimitBreak() {
            const cards = this.deck.filter((c) => c !== null);
            if (cards.length === 0) return 0;
            const sum = cards.reduce(
                (acc, card) => acc + (card.limit_break_level || 0),
                0,
            );
            return (sum / cards.length).toFixed(1);
        },

        get filteredCards() {
            return this.availableCards.filter((card) => {
                // Search filter
                if (
                    this.searchQuery &&
                    !card.name
                        .toLowerCase()
                        .includes(this.searchQuery.toLowerCase())
                ) {
                    return false;
                }

                // Type filter
                if (this.filterType && card.card_type !== this.filterType) {
                    return false;
                }

                // Tier filter
                if (this.filterTier && card.meta_tier !== this.filterTier) {
                    return false;
                }

                return true;
            });
        },

        // Methods
        init() {
            this.validateDeck();
        },

        getCardAtSlot(slotIndex) {
            return this.deck[slotIndex - 1] || null;
        },

        getSlotClasses(slotIndex) {
            const card = this.getCardAtSlot(slotIndex);
            const isFriendSlot = slotIndex === 6;

            return {
                "border-primary-500 bg-primary-50 dark:bg-primary-900/20":
                    card !== null,
                "border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800/50":
                    card === null,
                "border-secondary-500 bg-secondary-50 dark:bg-secondary-900/20":
                    isFriendSlot && card !== null,
                "cursor-pointer hover:border-primary-400 dark:hover:border-primary-500": true,
            };
        },

        selectCard(cardId) {
            const card = this.availableCards.find((c) => c.id === cardId);
            if (!card) return;

            // Find first empty slot (prioritize non-friend slots)
            let targetSlot = null;
            for (let i = 0; i < 5; i++) {
                if (!this.deck[i]) {
                    targetSlot = i;
                    break;
                }
            }

            // If no regular slot available, use friend slot
            if (targetSlot === null && !this.deck[5]) {
                targetSlot = 5;
            }

            if (targetSlot !== null) {
                this.addCardToSlot(targetSlot + 1, cardId);
            }
        },

        addCardToSlot(slotIndex, cardId) {
            const card = this.availableCards.find((c) => c.id === cardId);
            if (!card) return;

            const isFriendSlot = slotIndex === 6;

            this.deck[slotIndex - 1] = {
                position_slot: slotIndex,
                support_card_id: card.id,
                is_friend_card: isFriendSlot,
                limit_break_level: 0,
                friendship_level: 0,
                supportCard: card,
            };

            this.validateDeck();
        },

        removeCard(slotIndex) {
            this.deck[slotIndex - 1] = null;
            this.validateDeck();
        },

        clearDeck() {
            if (confirm("Are you sure you want to clear the entire deck?")) {
                this.deck = Array(6).fill(null);
                this.validateDeck();
            }
        },

        openEditModal(slotIndex) {
            const card = this.getCardAtSlot(slotIndex);
            if (!card) return;

            this.editForm = {
                slotIndex: slotIndex,
                limitBreak: card.limit_break_level || 0,
                bondLevel: card.friendship_level || 0,
            };
            this.showEditModal = true;
        },

        closeEditModal() {
            this.showEditModal = false;
            this.editForm = { slotIndex: null, limitBreak: 0, bondLevel: 0 };
        },

        saveEditModal() {
            if (this.editForm.slotIndex === null) return;

            const card = this.deck[this.editForm.slotIndex - 1];
            if (card) {
                card.limit_break_level = this.editForm.limitBreak;
                card.friendship_level = this.editForm.bondLevel;
            }

            this.closeEditModal();
            this.validateDeck();
        },

        openCardSelector(slotIndex) {
            // Focus on search input in available cards section
            const searchInput = document.querySelector(
                'input[x-model="searchQuery"]',
            );
            if (searchInput) {
                searchInput.focus();
            }
        },

        handleSlotClick(slotIndex) {
            const card = this.getCardAtSlot(slotIndex);
            if (!card) {
                this.openCardSelector(slotIndex);
            }
        },

        // Drag and drop handlers
        handleDragStart(event, slotIndex) {
            this.draggedSlot = slotIndex;
            event.dataTransfer.effectAllowed = "move";
        },

        handleDragEnd() {
            this.draggedSlot = null;
        },

        handleDragOver(event, slotIndex) {
            if (this.draggedSlot !== null && this.draggedSlot !== slotIndex) {
                event.dataTransfer.dropEffect = "move";
            }
        },

        handleDrop(event, targetSlot) {
            if (this.draggedSlot === null || this.draggedSlot === targetSlot)
                return;

            // Swap cards
            const temp = this.deck[this.draggedSlot - 1];
            this.deck[this.draggedSlot - 1] = this.deck[targetSlot - 1];
            this.deck[targetSlot - 1] = temp;

            // Update position slots
            if (this.deck[this.draggedSlot - 1]) {
                this.deck[this.draggedSlot - 1].position_slot =
                    this.draggedSlot;
            }
            if (this.deck[targetSlot - 1]) {
                this.deck[targetSlot - 1].position_slot = targetSlot;
            }

            this.draggedSlot = null;
            this.validateDeck();
        },

        // Keyboard navigation
        moveCardUp(slotIndex) {
            if (slotIndex <= 1) return;
            this.handleDrop(null, slotIndex - 1);
        },

        moveCardDown(slotIndex) {
            if (slotIndex >= 6) return;
            this.handleDrop(null, slotIndex + 1);
        },

        validateDeck() {
            this.validationErrors = [];
            this.validationWarnings = [];

            // Check total cards
            if (this.deckCount < 6) {
                this.validationErrors.push(
                    `Deck incomplete: ${this.deckCount}/6 cards`,
                );
            }

            // Check friend card
            if (this.friendCardCount === 0) {
                this.validationErrors.push(
                    "Friend card slot (slot 6) must be filled",
                );
            }

            // Check type diversity
            if (this.uniqueTypes < 3) {
                this.validationWarnings.push(
                    "Consider adding more card type diversity for better synergy",
                );
            }

            // Check average limit break
            if (this.averageLimitBreak < 2) {
                this.validationWarnings.push(
                    "Low average limit break level may reduce effectiveness",
                );
            }
        },

        autoOptimize() {
            // Simple auto-optimization: select highest tier cards with type diversity
            const sortedCards = [...this.availableCards].sort((a, b) => {
                const tierOrder = { SS: 4, S: 3, A: 2, B: 1 };
                return (
                    (tierOrder[b.meta_tier] || 0) -
                    (tierOrder[a.meta_tier] || 0)
                );
            });

            const selectedTypes = new Set();
            const newDeck = Array(6).fill(null);
            let slotIndex = 0;

            // Fill with diverse types first
            for (const card of sortedCards) {
                if (slotIndex >= 5) break;
                if (!selectedTypes.has(card.card_type)) {
                    newDeck[slotIndex] = {
                        position_slot: slotIndex + 1,
                        support_card_id: card.id,
                        is_friend_card: false,
                        limit_break_level: 0,
                        friendship_level: 0,
                        supportCard: card,
                    };
                    selectedTypes.add(card.card_type);
                    slotIndex++;
                }
            }

            // Fill remaining slots
            for (const card of sortedCards) {
                if (slotIndex >= 5) break;
                if (!newDeck.some((c) => c && c.support_card_id === card.id)) {
                    newDeck[slotIndex] = {
                        position_slot: slotIndex + 1,
                        support_card_id: card.id,
                        is_friend_card: false,
                        limit_break_level: 0,
                        friendship_level: 0,
                        supportCard: card,
                    };
                    slotIndex++;
                }
            }

            // Add friend card
            if (sortedCards.length > 0) {
                newDeck[5] = {
                    position_slot: 6,
                    support_card_id: sortedCards[0].id,
                    is_friend_card: true,
                    limit_break_level: 0,
                    friendship_level: 0,
                    supportCard: sortedCards[0],
                };
            }

            this.deck = newDeck;
            this.validateDeck();
        },

        async saveDeck() {
            if (!this.isDeckValid) {
                alert(
                    "Please complete the deck before saving (6 cards required, including 1 friend card)",
                );
                return;
            }

            try {
                const response = await fetch(
                    `/api/characters/${this.characterId}/deck`,
                    {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN":
                                document.querySelector(
                                    'meta[name="csrf-token"]',
                                )?.content || "",
                        },
                        body: JSON.stringify({
                            deck: this.deck.filter((c) => c !== null),
                        }),
                    },
                );

                if (response.ok) {
                    window.dispatchEvent(
                        new CustomEvent("toast", {
                            detail: {
                                type: "success",
                                message: "Deck saved successfully!",
                            },
                        }),
                    );
                } else {
                    throw new Error("Failed to save deck");
                }
            } catch (error) {
                console.error("Save error:", error);
                window.dispatchEvent(
                    new CustomEvent("toast", {
                        detail: {
                            type: "error",
                            message: "Failed to save deck. Please try again.",
                        },
                    }),
                );
            }
        },
    }));
});
