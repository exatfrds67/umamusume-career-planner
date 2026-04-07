/**
 * Support Card Manager
 * Handles support card browsing, filtering, and external API import functionality
 */

export function supportCardManager() {
    return {
        showExternalImport: false,

        // View mode: 'grid' or 'list'
        viewMode: localStorage.getItem("sc:viewMode") ?? "grid",

        // Filter panel visibility (open on desktop, closed on mobile by default)
        filtersOpen: window.innerWidth >= 768,

        // Count of active filter params (for mobile badge)
        activeFilterCount: 0,

        // External API state
        externalCards: [],
        externalLoading: false,
        externalError: null,
        externalFilters: {
            rarity: "",
            importStatus: "",
        },
        importedCards: new Set(),

        init() {
            // Load external cards when panel opens
            this.$watch("showExternalImport", (value) => {
                if (value && this.externalCards.length === 0) {
                    this.loadExternalCards();
                }
            });
            this.updateActiveFilterCount();
        },

        toggleViewMode(mode) {
            this.viewMode = mode;
            localStorage.setItem("sc:viewMode", mode);
        },

        updateActiveFilterCount() {
            const urlParams = new URLSearchParams(window.location.search);
            const filterKeys = [
                "type",
                "rarity",
                "tier",
                "bond_level",
                "limit_break",
                "search",
            ];
            this.activeFilterCount = filterKeys.filter(
                (k) => urlParams.has(k) && urlParams.get(k) !== "",
            ).length;
        },

        async loadExternalCards() {
            this.externalLoading = true;
            this.externalError = null;

            try {
                const response = await fetch("/api/external/support-cards", {
                    headers: {
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    this.externalCards = data.data || [];
                } else {
                    throw new Error(
                        data.message || "Failed to fetch support cards",
                    );
                }
            } catch (error) {
                console.error("External API error:", error);
                this.externalError =
                    error.message || "Failed to load external support cards";
            } finally {
                this.externalLoading = false;
            }
        },

        async importCard(card) {
            if (this.importedCards.has(card.id)) {
                return; // Already imported
            }

            try {
                // Construct image URL from card ID (gametora.com pattern)
                const imageUrl = card.id
                    ? `https://gametora.com/images/umamusume/supports/tex_support_card_${card.id}.png`
                    : null;

                const response = await fetch(
                    "/api/support-cards/import-external",
                    {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            "X-Requested-With": "XMLHttpRequest",
                            "X-CSRF-TOKEN": document.querySelector(
                                'meta[name="csrf-token"]',
                            ).content,
                        },
                        body: JSON.stringify({
                            external_id: card.id,
                            title_en: card.title_en || card.name,
                            chara_id: card.chara_id,
                            gametora: card.gametora,
                            rarity: card.rarity,
                            image_url: imageUrl,
                            source: "umapyoi.net",
                        }),
                    },
                );

                const result = await response.json();

                if (result.success) {
                    this.importedCards.add(card.id);

                    // Show success notification
                    this.showNotification(
                        "success",
                        result.message || "Card imported successfully!",
                    );

                    // Reload page after a short delay to show the new card
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    throw new Error(result.message || "Import failed");
                }
            } catch (error) {
                console.error("Import error:", error);
                this.showNotification(
                    "error",
                    error.message || "Failed to import card",
                );
            }
        },

        isImported(cardId) {
            return this.importedCards.has(cardId);
        },

        filteredExternalCards() {
            let filtered = this.externalCards;

            if (this.externalFilters.rarity) {
                filtered = filtered.filter(
                    (card) => card.rarity === this.externalFilters.rarity,
                );
            }

            if (this.externalFilters.importStatus === "imported") {
                filtered = filtered.filter((card) => this.isImported(card.id));
            } else if (this.externalFilters.importStatus === "not_imported") {
                filtered = filtered.filter((card) => !this.isImported(card.id));
            }

            return filtered;
        },

        showNotification(type, message) {
            // Simple notification - you can enhance this with a toast library
            const color = type === "success" ? "green" : "red";
            const notification = document.createElement("div");
            notification.className = `fixed top-4 right-4 bg-${color}-100 border border-${color}-400 text-${color}-700 px-4 py-3 rounded shadow-lg z-50`;
            notification.textContent = message;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.remove();
            }, 3000);
        },
    };
}
