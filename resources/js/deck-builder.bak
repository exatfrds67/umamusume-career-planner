import axios from "axios";

export default () => ({
    // Data Models
    deckData: [],
    availableCards: [],
    characterId: null,

    // UI State
    searchQuery: "",
    filterType: "",
    filterTier: "",
    sortBy: "name",
    dragOverSlot: null,
    draggedSlot: null,
    isLoading: false,

    // Validation
    validationErrors: [],
    validationWarnings: [],

    // Edit Modal State
    showEditModal: false,
    editingSlot: null,
    editForm: {
        limitBreak: 0,
        bondLevel: 0,
    },

    // Timer
    _searchDebounce: null,

    init() {
        // 1. Load data injected from Blade
        if (window.deckBuilderData) {
            this.deckData = window.deckBuilderData.deck || [];
            this.availableCards = window.deckBuilderData.availableCards || [];
            this.characterId = window.deckBuilderData.characterId;
        }

        // 2. Initial Setup
        this.validateDeck();

        // 3. Watchers
        this.$watch("searchQuery", () => {
            clearTimeout(this._searchDebounce);
            this._searchDebounce = setTimeout(() => this.filterCards(), 300);
        });
    },

    // --- Computed Properties ---

    get deckCount() {
        return this.deckData.filter((c) => c && c.support_card_id).length;
    },

    get friendCardCount() {
        return this.deckData.filter((c) => c && c.is_friend_card).length;
    },

    get isDeckValid() {
        return this.deckCount === 6 && this.friendCardCount === 1;
    },

    get filteredCards() {
        let cards = this.availableCards.filter((card) => {
            // 1. Search Text
            const searchLower = this.searchQuery.toLowerCase();
            const matchesSearch =
                card.name.toLowerCase().includes(searchLower) ||
                (card.character_name &&
                    card.character_name.toLowerCase().includes(searchLower));

            // 2. Dropdown Filters
            const matchesType =
                this.filterType === "" || card.card_type === this.filterType;
            const matchesTier =
                this.filterTier === "" || card.meta_tier === this.filterTier;

            // 3. Exclude Owned Cards (allow Friend card duplicates if needed, but usually unique)
            const isInDeck = this.deckData.some(
                (c) => c.support_card_id === card.id && !c.is_friend_card,
            );

            return matchesSearch && matchesType && matchesTier && !isInDeck;
        });

        // 4. Sorting
        return cards.sort((a, b) => {
            // Priority: Name
            return (a.name || "").localeCompare(b.name || "");
        });
    },

    get synergyScore() {
        if (this.deckCount < 2) return 0;
        let score = 0;

        // Count unique types
        const types = new Set(
            this.deckData
                .filter((c) => c.supportCard)
                .map((c) => c.supportCard.card_type),
        );

        // Diversity Bonus
        if (types.size >= 4) score += 20;
        else if (types.size >= 3) score += 10;

        // Stats Bonus
        const cards = this.deckData.filter((c) => c.supportCard);
        const avgBond =
            cards.reduce((sum, c) => sum + (c.friendship_level || 0), 0) /
            cards.length;
        const avgLB =
            cards.reduce((sum, c) => sum + (c.limit_break_level || 0), 0) /
            cards.length;

        score += Math.min(30, avgBond * 0.3);
        score += avgLB * 5;

        return Math.min(100, Math.round(score));
    },

    // --- View Helper Methods (For Blade) ---

    getCardAtSlot(slot) {
        return this.deckData.find((c) => c.position_slot === slot);
    },

    getSlotClasses(i) {
        const hasCard = this.getCardAtSlot(i);
        const isDragOver = this.dragOverSlot === i;

        return {
            "border-primary-300 bg-primary-50 dark:bg-primary-900/20": hasCard,
            "border-dashed border-gray-300 bg-gray-50": !hasCard,
            "ring-2 ring-primary-500 ring-offset-2": isDragOver,
        };
    },

    handleSlotClick(slot) {
        if (!this.getCardAtSlot(slot)) {
            this.openCardSelector(slot);
        }
    },

    // --- Actions ---

    selectCard(cardId) {
        // Find first empty slot logic
        const takenSlots = this.deckData.map((c) => c.position_slot);
        let targetSlot = null;

        // Try to fill 1-5 first
        for (let i = 1; i <= 5; i++) {
            if (!takenSlots.includes(i)) {
                targetSlot = i;
                break;
            }
        }
        // If full, check friend slot
        if (!targetSlot && !takenSlots.includes(6)) {
            targetSlot = 6;
        }

        if (!targetSlot) {
            alert("Deck is full! Remove a card first.");
            return;
        }

        this.addCardToSlot(cardId, targetSlot);
    },

    async addCardToSlot(cardId, slot) {
        const card = this.availableCards.find((c) => c.id === cardId);
        if (!card) return;

        // Optimistic UI Update
        const newEntry = {
            position_slot: slot,
            support_card_id: card.id,
            is_friend_card: slot === 6,
            limit_break_level: 0,
            friendship_level: 0,
            supportCard: card,
        };

        this.deckData.push(newEntry);
        this.validateDeck();

        // Optional: Sync with backend immediately
        // this.persistCard(newEntry);
    },

    removeCard(slot) {
        this.deckData = this.deckData.filter((c) => c.position_slot !== slot);
        this.validateDeck();
    },

    clearDeck() {
        if (confirm("Clear all cards?")) {
            this.deckData = [];
            this.validateDeck();
        }
    },

    // --- Drag and Drop ---

    handleDragStart(event, slot) {
        this.draggedSlot = slot;
        event.dataTransfer.effectAllowed = "move";
        event.dataTransfer.setData("text/plain", slot);
    },

    handleDragOver(event, slot) {
        if (this.draggedSlot === slot) return;
        this.dragOverSlot = slot;
    },

    handleDragEnd(event) {
        this.dragOverSlot = null;
        this.draggedSlot = null;
    },

    handleDrop(event, targetSlot) {
        this.dragOverSlot = null;
        const sourceSlot = parseInt(event.dataTransfer.getData("text/plain"));

        if (sourceSlot === targetSlot) return;

        const sourceIndex = this.deckData.findIndex(
            (c) => c.position_slot === sourceSlot,
        );
        const targetIndex = this.deckData.findIndex(
            (c) => c.position_slot === targetSlot,
        );

        // Swap Logic
        if (sourceIndex > -1) {
            this.deckData[sourceIndex].position_slot = targetSlot;
            // Update friend status based on slot
            this.deckData[sourceIndex].is_friend_card = targetSlot === 6;
        }

        if (targetIndex > -1) {
            this.deckData[targetIndex].position_slot = sourceSlot;
            this.deckData[targetIndex].is_friend_card = sourceSlot === 6;
        }

        // Force Alpine Reactivity
        this.deckData = [...this.deckData];
        this.validateDeck();
    },

    // --- Modal Logic ---

    openEditModal(slot) {
        const card = this.getCardAtSlot(slot);
        if (!card) return;

        this.editingSlot = slot;
        this.editForm.limitBreak = card.limit_break_level || 0;
        this.editForm.bondLevel = card.friendship_level || 0;
        this.showEditModal = true;
    },

    saveEditModal() {
        if (this.editingSlot === null) return;

        const index = this.deckData.findIndex(
            (c) => c.position_slot === this.editingSlot,
        );
        if (index > -1) {
            this.deckData[index].limit_break_level = parseInt(
                this.editForm.limitBreak,
            );
            this.deckData[index].friendship_level = parseInt(
                this.editForm.bondLevel,
            );
            // Trigger reactivity
            this.deckData = [...this.deckData];
        }

        this.closeEditModal();
        this.validateDeck();
    },

    closeEditModal() {
        this.showEditModal = false;
        this.editingSlot = null;
    },

    openCardSelector(slot) {
        // UI Interaction to focus library
        const library = document.querySelector("aside"); // assuming aside is library
        if (library) library.scrollIntoView({ behavior: "smooth" });
    },

    // --- Backend Sync ---

    async saveDeck() {
        if (!this.characterId) return;
        this.isLoading = true;

        try {
            await axios.post(
                `/api/v1/characters/${this.characterId}/deck/save`,
                {
                    cards: this.deckData,
                },
            );
            alert("Deck saved successfully!");
        } catch (error) {
            alert(
                "Failed to save: " +
                    (error.response?.data?.message || error.message),
            );
        } finally {
            this.isLoading = false;
        }
    },

    async autoOptimize() {
        alert("Optimization requested (Endpoint logic needed)");
    },

    validateDeck() {
        this.validationErrors = [];
        // Required: 6 cards
        if (this.deckCount < 6) {
            this.validationErrors.push(
                `Deck incomplete: ${this.deckCount}/6 cards.`,
            );
        }
        // Required: 1 Friend Card
        if (this.friendCardCount !== 1) {
            this.validationErrors.push("Deck must have exactly 1 Friend Card.");
        }
    },
});
