/**
 * Deck Management JavaScript
 * Handles drag-and-drop, card selection, and deck operations
 */

function deckManagement() {
    return {
        // State
        deck: [],
        selectedSlot: null,
        isFriendSlot: false,
        searchQuery: "",
        filterType: "",
        filterTier: "",
        filterRarity: "",
        sortBy: "meta_tier",
        validationErrors: [],
        validationWarnings: [],
        draggedCard: null,
        draggedSlot: null,
        isLoading: false,

        // Computed properties
        get deckCount() {
            return this.deck.length;
        },

        get friendCardCount() {
            return this.deck.filter((c) => c.is_friend_card).length;
        },

        get uniqueTypes() {
            const types = new Set(
                this.deck.map((c) => c.card?.card_type).filter(Boolean),
            );
            return types.size;
        },

        get isDeckValid() {
            return (
                this.deckCount === 6 &&
                this.friendCardCount <= 1 &&
                this.validationErrors.length === 0
            );
        },

        // Initialization
        init() {
            this.loadDeck();
            this.setupDragAndDrop();
        },

        // Load deck from server
        async loadDeck() {
            try {
                const characterId = document.querySelector(
                    "[data-character-id]",
                )?.dataset.characterId;
                if (!characterId) return;

                const response = await fetch(
                    `/api/v1/characters/${characterId}/deck`,
                    {
                        headers: {
                            Accept: "application/json",
                            "X-CSRF-TOKEN": document.querySelector(
                                'meta[name="csrf-token"]',
                            ).content,
                        },
                    },
                );

                if (response.ok) {
                    const data = await response.json();
                    this.deck = data.data.deck;
                    this.validateDeck();
                }
            } catch (error) {
                console.error("Failed to load deck:", error);
            }
        },

        // Drag and drop setup
        setupDragAndDrop() {
            // This will be enhanced with actual drag-and-drop library integration
            console.log("Drag and drop initialized");
        },

        // Card filtering
        filterCard(card) {
            if (
                this.searchQuery &&
                !card.name
                    .toLowerCase()
                    .includes(this.searchQuery.toLowerCase()) &&
                !card.character_name
                    .toLowerCase()
                    .includes(this.searchQuery.toLowerCase())
            ) {
                return false;
            }
            if (this.filterType && card.card_type !== this.filterType) {
                return false;
            }
            if (this.filterTier && card.meta_tier !== this.filterTier) {
                return false;
            }
            if (this.filterRarity && card.rarity !== this.filterRarity) {
                return false;
            }
            return true;
        },

        // Card selection
        openCardSelector(slot, isFriend) {
            this.selectedSlot = slot;
            this.isFriendSlot = isFriend;
        },

        async selectCard(cardId) {
            if (!this.selectedSlot) {
                alert("Please select a deck slot first");
                return;
            }

            this.isLoading = true;

            try {
                const characterId = document.querySelector(
                    "[data-character-id]",
                )?.dataset.characterId;
                const response = await fetch(
                    `/api/v1/characters/${characterId}/deck/cards`,
                    {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            "X-CSRF-TOKEN": document.querySelector(
                                'meta[name="csrf-token"]',
                            ).content,
                        },
                        body: JSON.stringify({
                            support_card_id: cardId,
                            position_slot: this.selectedSlot,
                            is_friend_card: this.isFriendSlot,
                            limit_break_level: 0,
                        }),
                    },
                );

                if (response.ok) {
                    const data = await response.json();
                    this.deck = data.data.deck;
                    this.selectedSlot = null;
                    this.validateDeck();
                    window.location.reload(); // Reload to show updated deck
                } else {
                    const error = await response.json();
                    alert(error.message || "Failed to add card to deck");
                }
            } catch (error) {
                console.error("Failed to add card:", error);
                alert("An error occurred while adding the card");
            } finally {
                this.isLoading = false;
            }
        },

        async removeCard(slot) {
            if (!confirm("Remove this card from the deck?")) {
                return;
            }

            this.isLoading = true;

            try {
                const characterId = document.querySelector(
                    "[data-character-id]",
                )?.dataset.characterId;
                const response = await fetch(
                    `/api/v1/characters/${characterId}/deck/cards`,
                    {
                        method: "DELETE",
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            "X-CSRF-TOKEN": document.querySelector(
                                'meta[name="csrf-token"]',
                            ).content,
                        },
                        body: JSON.stringify({
                            position_slot: slot,
                        }),
                    },
                );

                if (response.ok) {
                    const data = await response.json();
                    this.deck = data.data.deck;
                    this.validateDeck();
                    window.location.reload();
                } else {
                    alert("Failed to remove card from deck");
                }
            } catch (error) {
                console.error("Failed to remove card:", error);
                alert("An error occurred while removing the card");
            } finally {
                this.isLoading = false;
            }
        },

        async swapCards(position1, position2) {
            this.isLoading = true;

            try {
                const characterId = document.querySelector(
                    "[data-character-id]",
                )?.dataset.characterId;
                const response = await fetch(
                    `/api/v1/characters/${characterId}/deck/cards/swap`,
                    {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            "X-CSRF-TOKEN": document.querySelector(
                                'meta[name="csrf-token"]',
                            ).content,
                        },
                        body: JSON.stringify({
                            position1: position1,
                            position2: position2,
                        }),
                    },
                );

                if (response.ok) {
                    const data = await response.json();
                    this.deck = data.data.deck;
                    window.location.reload();
                } else {
                    alert("Failed to swap cards");
                }
            } catch (error) {
                console.error("Failed to swap cards:", error);
                alert("An error occurred while swapping cards");
            } finally {
                this.isLoading = false;
            }
        },

        async clearDeck() {
            if (
                !confirm(
                    "Are you sure you want to clear the entire deck? This cannot be undone.",
                )
            ) {
                return;
            }

            this.isLoading = true;

            try {
                const characterId = document.querySelector(
                    "[data-character-id]",
                )?.dataset.characterId;
                const response = await fetch(
                    `/api/v1/characters/${characterId}/deck/clear`,
                    {
                        method: "DELETE",
                        headers: {
                            Accept: "application/json",
                            "X-CSRF-TOKEN": document.querySelector(
                                'meta[name="csrf-token"]',
                            ).content,
                        },
                    },
                );

                if (response.ok) {
                    this.deck = [];
                    this.validateDeck();
                    window.location.reload();
                } else {
                    alert("Failed to clear deck");
                }
            } catch (error) {
                console.error("Failed to clear deck:", error);
                alert("An error occurred while clearing the deck");
            } finally {
                this.isLoading = false;
            }
        },

        // Deck validation
        validateDeck() {
            this.validationErrors = [];
            this.validationWarnings = [];

            if (this.deckCount !== 6) {
                this.validationErrors.push("Deck must contain exactly 6 cards");
            }

            if (this.friendCardCount > 1) {
                this.validationErrors.push("Deck can only have 1 friend card");
            }

            // Check for duplicates
            const cardIds = this.deck
                .filter((c) => !c.is_friend_card)
                .map((c) => c.support_card_id);
            if (cardIds.length !== new Set(cardIds).size) {
                this.validationErrors.push(
                    "Deck cannot contain duplicate cards",
                );
            }

            // Warnings
            if (this.uniqueTypes < 3) {
                this.validationWarnings.push(
                    "Consider using at least 3 different card types for balanced training",
                );
            }

            if (this.friendCardCount === 0 && this.deckCount > 0) {
                this.validationWarnings.push(
                    "No friend card in deck. Consider adding one for additional bonuses.",
                );
            }
        },

        // Auto-optimize deck
        async autoOptimize() {
            if (
                !confirm(
                    "Auto-optimize will replace your current deck with recommended cards. Continue?",
                )
            ) {
                return;
            }

            this.isLoading = true;

            try {
                const characterId = document.querySelector(
                    "[data-character-id]",
                )?.dataset.characterId;
                const response = await fetch(
                    `/api/v1/characters/${characterId}/deck/recommendations`,
                    {
                        headers: {
                            Accept: "application/json",
                            "X-CSRF-TOKEN": document.querySelector(
                                'meta[name="csrf-token"]',
                            ).content,
                        },
                    },
                );

                if (response.ok) {
                    const data = await response.json();
                    // Apply recommendations
                    alert(
                        "Recommendations loaded. Please review and apply manually.",
                    );
                    console.log("Recommendations:", data.data);
                } else {
                    alert("Failed to get recommendations");
                }
            } catch (error) {
                console.error("Failed to get recommendations:", error);
                alert("An error occurred while getting recommendations");
            } finally {
                this.isLoading = false;
            }
        },
    };
}
