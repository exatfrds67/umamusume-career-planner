document.addEventListener("alpine:init", () => {
    Alpine.data("charactersIndexPage", () => ({
        compactMode: false,
        showBackToTop: false,
        loading: false,
        viewMode: window.localStorage.getItem("characters:viewMode") ?? "list",
        filtersOpen: window.innerWidth >= 768,

        init() {
            this.compactMode =
                window.localStorage.getItem("characters:compact-mode") ===
                "true";

            window.addEventListener("scroll", this.handleScroll.bind(this), {
                passive: true,
            });

            this.handleScroll();
        },

        setLoading(value) {
            this.loading = Boolean(value);
        },

        toggleCompactMode() {
            this.compactMode = !this.compactMode;
            window.localStorage.setItem(
                "characters:compact-mode",
                String(this.compactMode),
            );
        },

        toggleViewMode(mode) {
            this.viewMode = mode;
            window.localStorage.setItem("characters:viewMode", mode);
        },

        handleScroll() {
            this.showBackToTop = window.scrollY > 500;
        },

        scrollToTop() {
            window.scrollTo({ top: 0, behavior: "smooth" });
        },
    }));

    Alpine.data("characterVariantCard", (character, defaultAvatar) => ({
        character,
        defaultAvatar,
        selectedVariantId: String(character.id),
        showAllGoals: false,
        showSecondary: false,
        statKeys: ["speed", "stamina", "power", "guts", "wit"],

        get variants() {
            return this.character.variants && this.character.variants.length > 0
                ? this.character.variants
                : [this.character];
        },

        get selectedVariant() {
            return (
                this.variants.find(
                    (variant) =>
                        String(variant.id) === String(this.selectedVariantId),
                ) || this.variants[0]
            );
        },

        get selectedAvatar() {
            const avatar =
                this.selectedVariant.avatar_processed ||
                this.selectedVariant.avatar_url ||
                this.selectedVariant.avatar_fallback_url ||
                this.defaultAvatar;

            return avatar &&
                !avatar.startsWith("http://") &&
                !avatar.startsWith("https://") &&
                !avatar.startsWith("/")
                ? `/${avatar}`
                : avatar;
        },

        get selectedScenarioLabel() {
            return String(this.selectedVariant.scenario_type || "")
                .replaceAll("_", " ")
                .toUpperCase();
        },

        selectVariant(variantId) {
            this.selectedVariantId = String(variantId);
            this.showAllGoals = false;
            this.showSecondary = false;
        },

        formatVariantLabel(variant) {
            const status = variant.status ? ` (${variant.status})` : "";
            const scenario = variant.scenario_type
                ? ` - ${String(variant.scenario_type).replaceAll("_", " ")}`
                : "";

            return `${variant.name}${scenario}${status}`;
        },

        statLabel(statKey) {
            return statKey.charAt(0).toUpperCase() + statKey.slice(1);
        },

        displayStat(statKey) {
            const value = this.selectedVariant.current_stats?.[statKey];

            return Number.isInteger(value) ? value : "—";
        },

        statPercent(statKey) {
            const value = this.selectedVariant.current_stats?.[statKey];
            if (!Number.isInteger(value)) {
                return 0;
            }

            return Math.min(100, Math.round((value / 1200) * 100));
        },

        statBarColor(percent) {
            if (percent >= 80) {
                return "bg-emerald-500";
            }

            if (percent >= 50) {
                return "bg-amber-500";
            }

            return "bg-rose-500";
        },
    }));
});
