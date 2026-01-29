import "./bootstrap";
import Alpine from "alpinejs";
import persist from "@alpinejs/persist";
import axios from "axios";
import connectivityMonitor from "./core/connectivity-monitor.js";
import planWizard from "./components/plan-wizard.js";

// Phase 4: Training & SP Management Components
import { trainingTimeline } from "./components/training-timeline.js";
import { spAllocator } from "./components/sp-allocator.js";

// Phase 5: Race Planning & Analytics Components
import { raceCalendar } from "./components/race-calendar.js";

// Register Alpine plugins early
Alpine.plugin(persist);

// --- Deck Builder Component (Fully Consolidated) ---
const deckBuilder = () => ({
    // Data Models
    deckData: [],
    availableCards: [],
    characterId: null,

    // UI State
    searchQuery: "",
    filterType: "",
    filterTier: "",
    sortBy: "name", // Options: name, rarity, type, tier
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
        } else {
            // Fallback: Fetch if no window data provided
            this.fetchAvailableCards();
        }

        // 2. Initial Validation
        this.validateDeck();

        // 3. Watchers
        this.$watch("searchQuery", () => {
            clearTimeout(this._searchDebounce);
            // Re-evaluating filteredCards handled by Alpine reactivity,
            // but we might want to fetch server-side if list is too large
            this._searchDebounce = setTimeout(() => {
                // Optional: Fetch from API if client-side list is incomplete
                // this.fetchAvailableCards();
            }, 300);
        });
    },

    // --- Computed Properties: Stats & Counts ---

    get deckCount() {
        return this.deckData.filter((c) => c && c.support_card_id).length;
    },

    get friendCardCount() {
        return this.deckData.filter((c) => c && c.is_friend_card).length;
    },

    get isDeckValid() {
        return (
            this.deckCount === 6 &&
            this.friendCardCount === 1 &&
            this.validationErrors.length === 0
        );
    },

    get uniqueTypes() {
        if (!this.deckData.length) return 0;
        const types = new Set(
            this.deckData
                .filter((c) => c && c.supportCard)
                .map((c) => c.supportCard.card_type),
        );
        return types.size;
    },

    get typeDistribution() {
        const dist = {};
        this.deckData
            .filter((c) => c && c.supportCard)
            .forEach((c) => {
                const type = c.supportCard.card_type || "unknown";
                dist[type] = (dist[type] || 0) + 1;
            });
        return dist;
    },

    get averageBond() {
        const cards = this.deckData.filter((c) => c && c.supportCard);
        if (!cards.length) return 0;
        const total = cards.reduce(
            (sum, c) => sum + (c.friendship_level || 0),
            0,
        );
        return Math.round(total / cards.length);
    },

    get averageLimitBreak() {
        const cards = this.deckData.filter((c) => c && c.supportCard);
        if (!cards.length) return 0;
        const total = cards.reduce(
            (sum, c) => sum + (c.limit_break_level || 0),
            0,
        );
        return (total / cards.length).toFixed(1);
    },

    get synergyScore() {
        if (this.deckCount < 2) return 0;
        let score = 0;
        const cards = this.deckData.filter((c) => c && c.supportCard);

        // 1. Diversity Bonus
        const typeCount = this.uniqueTypes;
        if (typeCount >= 4) score += 20;
        else if (typeCount >= 3) score += 10;

        // 2. Bond Synergy
        const avgBond = this.averageBond;
        score += Math.min(30, avgBond * 0.3);

        // 3. Limit Break Synergy
        const avgLB = parseFloat(this.averageLimitBreak);
        score += avgLB * 5;

        // 4. Meta Tier Bonus
        cards.forEach((c) => {
            const tier = c.supportCard.meta_tier;
            if (tier === "S" || tier === "SS") score += 10;
            else if (tier === "A") score += 7;
            else if (tier === "B") score += 4;
        });

        return Math.min(100, Math.round(score));
    },

    get filteredCards() {
        let cards = this.availableCards.filter((card) => {
            // Search Text
            const searchLower = this.searchQuery.toLowerCase();
            const matchesSearch =
                card.name.toLowerCase().includes(searchLower) ||
                (card.character_name &&
                    card.character_name.toLowerCase().includes(searchLower));

            // Dropdown Filters
            const matchesType =
                this.filterType === "" || card.card_type === this.filterType;
            const matchesTier =
                this.filterTier === "" || card.meta_tier === this.filterTier;

            // Exclude Owned Cards (Allow Friend card duplicates usually, but exclude owned for now)
            const isInDeck = this.deckData.some(
                (c) => c.support_card_id === card.id && !c.is_friend_card,
            );

            return matchesSearch && matchesType && matchesTier && !isInDeck;
        });

        // Sorting
        return cards.sort((a, b) => {
            switch (this.sortBy) {
                case "rarity":
                    const rarityOrder = { SSR: 0, SR: 1, R: 2 };
                    return (
                        (rarityOrder[a.rarity] || 99) -
                        (rarityOrder[b.rarity] || 99)
                    );
                case "type":
                    return (a.card_type || "").localeCompare(b.card_type || "");
                case "tier":
                    const tierOrder = { SS: 0, S: 1, A: 2, B: 3, C: 4, D: 5 };
                    return (
                        (tierOrder[a.meta_tier] || 99) -
                        (tierOrder[b.meta_tier] || 99)
                    );
                case "name":
                default:
                    return (a.name || "").localeCompare(b.name || "");
            }
        });
    },

    // --- View Helpers ---

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

    // --- Actions: Add/Remove/Move ---

    selectCard(cardId) {
        const takenSlots = this.deckData.map((c) => c.position_slot);
        let targetSlot = null;

        // Try to fill 1-5 first
        for (let i = 1; i <= 5; i++) {
            if (!takenSlots.includes(i)) {
                targetSlot = i;
                break;
            }
        }
        // If full, check friend slot (6)
        if (!targetSlot && !takenSlots.includes(6)) {
            targetSlot = 6;
        }

        if (!targetSlot) {
            alert("Deck is full! Remove a card first.");
            return;
        }

        this.addCardToSlot(cardId, targetSlot);
    },

    addCardToSlot(cardId, slot) {
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

    moveCardUp(slot) {
        // Logic: swap with slot-1
        if (slot <= 1) return;
        this.swapSlots(slot, slot - 1);
    },

    moveCardDown(slot) {
        // Logic: swap with slot+1
        if (slot >= 6) return;
        this.swapSlots(slot, slot + 1);
    },

    swapSlots(slotA, slotB) {
        const indexA = this.deckData.findIndex(
            (c) => c.position_slot === slotA,
        );
        const indexB = this.deckData.findIndex(
            (c) => c.position_slot === slotB,
        );

        if (indexA > -1) this.deckData[indexA].position_slot = slotB;
        if (indexB > -1) this.deckData[indexB].position_slot = slotA;

        // Force Alpine reactivity
        this.deckData = [...this.deckData];
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
            // Update friend status based on slot (Slot 6 is Friend)
            this.deckData[sourceIndex].is_friend_card = targetSlot === 6;
        }

        if (targetIndex > -1) {
            this.deckData[targetIndex].position_slot = sourceSlot;
            this.deckData[targetIndex].is_friend_card = sourceSlot === 6;
        }

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
            this.deckData = [...this.deckData]; // Trigger reactivity
        }

        this.closeEditModal();
        this.validateDeck();
    },

    closeEditModal() {
        this.showEditModal = false;
        this.editingSlot = null;
    },

    openCardSelector(slot) {
        const library = document.querySelector("aside"); // Assuming aside is library
        if (library) library.scrollIntoView({ behavior: "smooth" });
    },

    // --- Backend Sync ---

    async fetchAvailableCards() {
        this.isLoading = true;
        try {
            const response = await axios.get(
                "/api/v1/support-cards?is_active=1",
            );
            this.availableCards = response.data.data;
        } catch (error) {
            console.error("Failed to fetch cards:", error);
        } finally {
            this.isLoading = false;
        }
    },

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
        if (!this.characterId) return;
        this.isLoading = true;
        try {
            const response = await axios.post(
                `/api/v1/characters/${this.characterId}/deck/optimize`,
            );
            if (response.data.data) {
                this.deckData = response.data.data;
                this.validateDeck();
                alert("Deck optimized!");
            }
        } catch (error) {
            alert("Optimization failed or endpoint not implemented yet.");
        } finally {
            this.isLoading = false;
        }
    },

    validateDeck() {
        this.validationErrors = [];
        this.validationWarnings = [];

        // 1. Errors
        if (this.deckCount < 6) {
            this.validationErrors.push(
                `Deck incomplete: ${this.deckCount}/6 cards.`,
            );
        }
        if (this.friendCardCount > 1) {
            this.validationErrors.push("Only 1 Friend Card is allowed.");
        }

        // Duplicate Check
        const ids = this.deckData.map((c) => c.support_card_id);
        const hasDuplicates = ids.some((id, idx) => ids.indexOf(id) !== idx);
        if (hasDuplicates) {
            this.validationErrors.push("Duplicate cards detected.");
        }

        // 2. Warnings
        if (this.friendCardCount === 0 && this.deckCount > 0) {
            this.validationWarnings.push(
                "Consider adding a Friend Card to slot 6.",
            );
        }

        // Type Diversity Warning
        const types = Object.keys(this.typeDistribution);
        if (this.deckCount >= 4 && types.length < 3) {
            this.validationWarnings.push(
                "Consider diversifying card types for better coverage.",
            );
        }

        // Low Bond Warning
        const lowBondCount = this.deckData.filter(
            (c) => (c.friendship_level || 0) < 50,
        ).length;
        if (lowBondCount >= 3) {
            this.validationWarnings.push(
                "Several cards have low bond levels (<50).",
            );
        }
    },
});

// Register Components
Alpine.data("connectivityMonitor", connectivityMonitor);
Alpine.data("planWizard", planWizard);
Alpine.data("deckBuilder", deckBuilder);

// Phase 4: Training & SP Management
Alpine.data("trainingTimeline", trainingTimeline);
Alpine.data("spAllocator", spAllocator);

// Phase 5: Race Planning & Analytics
Alpine.data("raceCalendar", raceCalendar);

// Initialize Alpine.js immediately for faster interactivity
window.Alpine = Alpine;

// Defer non-critical module loading
const loadNonCriticalModules = () => {
    // Import character validation module
    import("./character-validation.js");

    // Import training predictions module
    import("./training-predictions.js");

    // Import settings module
    import("./settings.js");

    // Import AI Chat module
    import("./ai-chat.js");

    // Import core modules
    import("./core/EventBus.js");
    import("./core/ResponsiveSystem.js");
    import("./core/AccessibilitySettings.js");
    import("./core/AccessibilitySystem.js");
    import("./core/ThemeSystem.js");

    // Import performance optimization modules
    import("./core/ImageOptimization.js");
    import("./core/PerformanceMonitor.js");
};

// Start Alpine immediately for better INP
Alpine.start();

// Load non-critical modules after Alpine starts
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", loadNonCriticalModules);
} else {
    // Use requestIdleCallback for better performance
    if ("requestIdleCallback" in window) {
        requestIdleCallback(loadNonCriticalModules, { timeout: 2000 });
    } else {
        setTimeout(loadNonCriticalModules, 1);
    }
}

// Service Worker Management with advanced caching
if ("serviceWorker" in navigator) {
    window.addEventListener("load", async () => {
        try {
            // First, unregister all existing service workers to clear bad cache
            const registrations =
                await navigator.serviceWorker.getRegistrations();

            // If there are old registrations, unregister them
            if (registrations.length > 0) {
                console.log("[SW] Found old service workers, unregistering...");
                await Promise.all(
                    registrations.map((registration) =>
                        registration.unregister(),
                    ),
                );
                console.log("[SW] Old service workers unregistered");

                // Clear all caches
                if ("caches" in window) {
                    const cacheKeys = await caches.keys();
                    await Promise.all(
                        cacheKeys.map((key) => caches.delete(key)),
                    );
                    console.log("[SW] All caches cleared");
                }
            }

            // Register the new service worker
            const registration = await navigator.serviceWorker.register(
                "/sw.js",
                {
                    updateViaCache: "none", // Don't cache the service worker file itself
                },
            );

            console.log("[SW] Service Worker registered:", registration.scope);

            // Check for updates immediately
            registration.update();

            // Handle updates
            registration.addEventListener("updatefound", () => {
                const newWorker = registration.installing;

                newWorker.addEventListener("statechange", () => {
                    if (
                        newWorker.state === "installed" &&
                        navigator.serviceWorker.controller
                    ) {
                        // New service worker available, reload to activate
                        console.log("[SW] New version available, reloading...");
                        window.location.reload();
                    }
                });
            });

            // Listen for controller change (new service worker activated)
            navigator.serviceWorker.addEventListener("controllerchange", () => {
                console.log("[SW] New service worker activated");
            });

            // Listen for messages from service worker
            navigator.serviceWorker.addEventListener("message", (event) => {
                const { type, payload } = event.data || {};

                switch (type) {
                    case "CACHE_CLEARED":
                        console.log("[SW] Cache cleared successfully");
                        break;
                    case "API_CACHE_CLEARED":
                        console.log("[SW] API cache cleared successfully");
                        break;
                    case "CACHE_STATUS":
                        console.log("[SW] Cache status:", payload);
                        break;
                }
            });
        } catch (error) {
            console.error("[SW] Service Worker registration failed:", error);
        }
    });
}

// Utility functions for service worker communication
window.ServiceWorkerUtils = {
    /**
     * Clear all service worker caches
     */
    clearCache() {
        if (navigator.serviceWorker.controller) {
            navigator.serviceWorker.controller.postMessage({
                type: "CLEAR_CACHE",
            });
        }
    },

    /**
     * Clear API cache only
     */
    clearApiCache() {
        if (navigator.serviceWorker.controller) {
            navigator.serviceWorker.controller.postMessage({
                type: "CLEAR_API_CACHE",
            });
        }
    },

    /**
     * Precache additional assets
     * @param {string[]} urls - URLs to precache
     */
    precacheAssets(urls) {
        if (navigator.serviceWorker.controller) {
            navigator.serviceWorker.controller.postMessage({
                type: "PRECACHE_ASSETS",
                payload: { urls },
            });
        }
    },

    /**
     * Get cache status
     */
    getCacheStatus() {
        if (navigator.serviceWorker.controller) {
            navigator.serviceWorker.controller.postMessage({
                type: "GET_CACHE_STATUS",
            });
        }
    },

    /**
     * Force service worker update
     */
    async forceUpdate() {
        const registration = await navigator.serviceWorker.getRegistration();
        if (registration) {
            await registration.update();
        }
    },
};
