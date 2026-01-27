export default (initialDeck = [], characterId = null) => ({
    currentDeck: initialDeck,
    availableCards: [],
    searchQuery: '',
    filterType: '',
    filterRarity: '',
    sortBy: 'name',
    dragOverSlot: null,
    draggedSlot: null,
    isLoading: false,
    validationErrors: [],
    validationWarnings: [],
    characterId: characterId,
    
    // Edit modal state
    showEditModal: false,
    editingSlot: null,
    editForm: {
        limitBreak: 0,
        bondLevel: 0
    },
    
    // Debounce timer for API calls
    _searchDebounce: null,

    init() {
        this.fetchAvailableCards();
        this.validateDeck();
        
        // Watch for search changes with debounce
        this.$watch('searchQuery', () => {
            clearTimeout(this._searchDebounce);
            this._searchDebounce = setTimeout(() => this.fetchAvailableCards(), 300);
        });
    },

    get deckCount() {
        return this.currentDeck.filter(c => c && c.support_card_id).length;
    },

    get friendCardCount() {
        return this.currentDeck.filter(c => c && c.is_friend_card).length;
    },

    get uniqueTypes() {
        if (!this.currentDeck.length) return 0;
        const types = new Set(
            this.currentDeck
                .filter(c => c && c.supportCard)
                .map(c => c.supportCard.card_type)
        );
        return types.size;
    },
    
    // Type distribution breakdown
    get typeDistribution() {
        const dist = {};
        this.currentDeck
            .filter(c => c && c.supportCard)
            .forEach(c => {
                const type = c.supportCard.card_type || 'unknown';
                dist[type] = (dist[type] || 0) + 1;
            });
        return dist;
    },
    
    // Calculate synergy score based on card combinations
    get synergyScore() {
        if (this.deckCount < 2) return 0;
        let score = 0;
        const cards = this.currentDeck.filter(c => c && c.supportCard);
        
        // Type synergy: bonus for balanced types
        const typeCount = this.uniqueTypes;
        if (typeCount >= 4) score += 20;
        else if (typeCount >= 3) score += 10;
        
        // Bond synergy: higher bonds = better synergy
        const avgBond = cards.reduce((sum, c) => sum + (c.friendship_level || 0), 0) / cards.length;
        score += Math.min(30, avgBond * 0.3);
        
        // Limit break synergy
        const avgLB = cards.reduce((sum, c) => sum + (c.limit_break_level || 0), 0) / cards.length;
        score += avgLB * 5;
        
        // Meta tier bonus
        cards.forEach(c => {
            const tier = c.supportCard.meta_tier;
            if (tier === 'S') score += 10;
            else if (tier === 'A') score += 7;
            else if (tier === 'B') score += 4;
        });
        
        return Math.min(100, Math.round(score));
    },
    
    // Average bond level
    get averageBond() {
        const cards = this.currentDeck.filter(c => c && c.supportCard);
        if (!cards.length) return 0;
        return Math.round(cards.reduce((sum, c) => sum + (c.friendship_level || 0), 0) / cards.length);
    },
    
    // Average limit break
    get averageLimitBreak() {
        const cards = this.currentDeck.filter(c => c && c.supportCard);
        if (!cards.length) return 0;
        return (cards.reduce((sum, c) => sum + (c.limit_break_level || 0), 0) / cards.length).toFixed(1);
    },

    get isDeckValid() {
        return this.deckCount === 6 && this.friendCardCount <= 1 && this.validationErrors.length === 0;
    },

    async fetchAvailableCards() {
        this.isLoading = true;
        try {
            // Using the existing API for support cards, filtered by active
            const response = await axios.get('/api/support-cards?is_active=1');
            this.availableCards = response.data.data;
        } catch (error) {
            console.error('Failed to fetch cards:', error);
            // Fallback or toast error
        } finally {
            this.isLoading = false;
        }
    },

    get filteredCards() {
        let cards = this.availableCards.filter(card => {
            const matchesSearch = card.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                                (card.character_name && card.character_name.toLowerCase().includes(this.searchQuery.toLowerCase()));
            const matchesType = this.filterType === '' || card.card_type === this.filterType;
            const matchesRarity = this.filterRarity === '' || card.rarity === this.filterRarity;
            // Exclude cards already in deck (unless friend card logic permits, sticking to simple exclusion for owned)
            const isInDeck = this.currentDeck.some(c => c.support_card_id === card.id && !c.is_friend_card);
            
            return matchesSearch && matchesType && matchesRarity && !isInDeck;
        });
        
        // Apply sorting
        return cards.sort((a, b) => {
            switch (this.sortBy) {
                case 'rarity':
                    const rarityOrder = { 'SSR': 0, 'SR': 1, 'R': 2 };
                    return (rarityOrder[a.rarity] || 99) - (rarityOrder[b.rarity] || 99);
                case 'type':
                    return (a.card_type || '').localeCompare(b.card_type || '');
                case 'tier':
                    const tierOrder = { 'S': 0, 'A': 1, 'B': 2, 'C': 3, 'D': 4 };
                    return (tierOrder[a.meta_tier] || 99) - (tierOrder[b.meta_tier] || 99);
                case 'name':
                default:
                    return (a.name || '').localeCompare(b.name || '');
            }
        });
    },

    addCard(cardId, isFriend = false) {
        // Find first empty slot
        let slot = 0;
        // Friend card usually goes to slot 6, or specific logic
        if (isFriend) {
            slot = 6;
        } else {
            // Find first empty slot from 1-5
            for (let i = 1; i <= 5; i++) {
                if (!this.currentDeck.find(c => c.position_slot === i)) {
                    slot = i;
                    break;
                }
            }
        }

        if (!slot) {
            alert('No empty slot available for this card type.');
            return;
        }

        // Check availability in data
        const card = this.availableCards.find(c => c.id === cardId);
        if (!card) return;

        // Add to local state
        this.currentDeck = this.currentDeck.filter(c => c.position_slot !== slot); // Remove existing in slot if any
        this.currentDeck.push({
            position_slot: slot,
            support_card_id: cardId,
            is_friend_card: isFriend,
            limit_break_level: 0, // Default
            friendship_level: 0, // Default
            supportCard: card // Embed full object for display
        });

        this.validateDeck();
    },

    removeCard(slot) {
        this.currentDeck = this.currentDeck.filter(c => c.position_slot !== slot);
        this.validateDeck();
    },

    handleDragStart(event, slot) {
        this.draggedSlot = slot;
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', slot);
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
        const sourceSlot = parseInt(event.dataTransfer.getData('text/plain'));
        if (sourceSlot === targetSlot) return;

        // Perform swap or move
        const sourceCardIndex = this.currentDeck.findIndex(c => c.position_slot === sourceSlot);
        const targetCardIndex = this.currentDeck.findIndex(c => c.position_slot === targetSlot);

        if (sourceCardIndex > -1) {
            this.currentDeck[sourceCardIndex].position_slot = targetSlot;
        }
        if (targetCardIndex > -1) {
            this.currentDeck[targetCardIndex].position_slot = sourceSlot;
        }
        
        // Force reactivity update if needed (Alpine usually handles array mutation)
        this.currentDeck = [...this.currentDeck]; 
        this.validateDeck();
    },

    moveCardUp(slot) {
        // Logic to move card to lower index slot (visual "Up" usually means lower index number in list, or strictly slot-1)
        if (slot <= 1) return;
        this.swapSlots(slot, slot - 1);
    },

    moveCardDown(slot) {
        if (slot >= 6) return;
        this.swapSlots(slot, slot + 1);
    },

    swapSlots(slotA, slotB) {
        const indexA = this.currentDeck.findIndex(c => c.position_slot === slotA);
        const indexB = this.currentDeck.findIndex(c => c.position_slot === slotB);

        if (indexA > -1) this.currentDeck[indexA].position_slot = slotB;
        if (indexB > -1) this.currentDeck[indexB].position_slot = slotA;

        this.currentDeck = [...this.currentDeck];
    },

    async saveDeck() {
        if (!this.characterId) return;
        this.isLoading = true;

        try {
            // Prepare payload matching UpdateDeckRequest/Store logic
            const payload = {
                cards: this.currentDeck.map(c => ({
                    support_card_id: c.support_card_id,
                    position_slot: c.position_slot,
                    is_friend_card: c.is_friend_card,
                    limit_break_level: c.limit_break_level
                }))
            };

            await axios.post(`/api/v1/characters/${this.characterId}/deck/save`, payload);
            
            // Show success toast (assuming generic toast capability or alert)
            alert('Deck saved successfully!');
            
        } catch (error) {
            console.error('Save failed', error);
            alert('Failed to save deck: ' + (error.response?.data?.message || error.message));
        } finally {
            this.isLoading = false;
        }
    },

    async clearDeck() {
        if (!confirm('Are you sure you want to clear the deck?')) return;
        this.currentDeck = [];
        this.validateDeck();
    },

    async autoOptimize() {
        if (!this.characterId) return;
        this.isLoading = true;
        try {
            // Call optimize endpoint
            const response = await axios.post(`/api/v1/characters/${this.characterId}/deck/optimize`);
            // Assuming endpoint returns a full deck structure
            if (response.data.data && Array.isArray(response.data.data)) {
                // Map response to local state structure (assuming response is array of cards)
                this.currentDeck = response.data.data; 
                this.validateDeck();
            }
        } catch (error) {
             console.error('Optimization failed', error);
             alert('Optimization failed.');
        } finally {
            this.isLoading = false;
        }
    },

    validateDeck() {
        this.validationErrors = [];
        this.validationWarnings = [];

        // Card count validation
        if (this.deckCount !== 6) {
            this.validationErrors.push(`Deck must have exactly 6 cards (currently ${this.deckCount}).`);
        }
        
        // Friend card validation
        if (this.friendCardCount > 1) {
            this.validationErrors.push('Only 1 friend card is allowed.');
        }
        if (this.friendCardCount === 0 && this.deckCount > 0) {
            this.validationWarnings.push('Consider adding a friend card for slot 6.');
        }
        
        // Duplicate detection
        const cardIds = this.currentDeck
            .filter(c => c && c.support_card_id && !c.is_friend_card)
            .map(c => c.support_card_id);
        const duplicates = cardIds.filter((id, idx) => cardIds.indexOf(id) !== idx);
        if (duplicates.length > 0) {
            this.validationErrors.push('Duplicate cards detected in owned slots.');
        }
        
        // Type coverage warnings
        const types = Object.keys(this.typeDistribution);
        if (this.deckCount >= 4 && types.length < 3) {
            this.validationWarnings.push('Consider diversifying card types for better coverage.');
        }
        
        // Meta tier warning
        const cards = this.currentDeck.filter(c => c && c.supportCard);
        const lowTierCount = cards.filter(c => ['C', 'D'].includes(c.supportCard?.meta_tier)).length;
        if (lowTierCount >= 3) {
            this.validationWarnings.push('Multiple low-tier cards may impact performance.');
        }
        
        // Bond level warning
        const lowBondCount = cards.filter(c => (c.friendship_level || 0) < 50).length;
        if (lowBondCount >= 3) {
            this.validationWarnings.push('Several cards have low bond levels (<50).');
        }
    },
    
    // Open edit modal with current card data
    openEditModal(slot, lb, bond) {
        const card = this.currentDeck.find(c => c.position_slot === slot);
        if (!card) return;
        
        this.editingSlot = slot;
        this.editForm.limitBreak = lb || card.limit_break_level || 0;
        this.editForm.bondLevel = bond || card.friendship_level || 0;
        this.showEditModal = true;
    },
    
    // Save edit modal changes
    saveEditModal() {
        if (this.editingSlot === null) return;
        
        const cardIndex = this.currentDeck.findIndex(c => c.position_slot === this.editingSlot);
        if (cardIndex > -1) {
            this.currentDeck[cardIndex].limit_break_level = Math.max(0, Math.min(4, parseInt(this.editForm.limitBreak) || 0));
            this.currentDeck[cardIndex].friendship_level = Math.max(0, Math.min(100, parseInt(this.editForm.bondLevel) || 0));
            this.currentDeck = [...this.currentDeck]; // Trigger reactivity
        }
        
        this.closeEditModal();
        this.validateDeck();
    },
    
    // Close edit modal
    closeEditModal() {
        this.showEditModal = false;
        this.editingSlot = null;
        this.editForm = { limitBreak: 0, bondLevel: 0 };
    },

    openCardSelector(slot, isFriend) {
        // Scroll to library or open modal
        document.querySelector('.card-library')?.scrollIntoView({ behavior: 'smooth' });
    }
});
