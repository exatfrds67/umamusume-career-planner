/**
 * Skills Management Page Script
 * Handles skill inventory, acquisition, hints, evolution, and AI recommendations
 */

// Parse data from JSON data island injected by Blade
const dataElement = document.getElementById("skills-data");
const { isAdmin, preSelectedCharacterId } = dataElement
    ? JSON.parse(dataElement.textContent)
    : {};

// Initialize Alpine component
document.addEventListener("alpine:init", () => {
    Alpine.data("skillManagement", (adminMode = false, characterId = "") => ({
        // State
        isAdmin: adminMode || isAdmin || false,
        selectedCharacterId: characterId || preSelectedCharacterId || "",
        character: null,
        skills: [],
        acquiredSkills: [],
        plannedSkills: [],
        hints: [],
        evolutionOpportunities: [],
        spStats: {},
        agentPerformance: {},
        loading: false,
        activeTab: "inventory",

        // AI Recommendations
        recommendations: [],
        aiOptimization: null,

        // SP Planning
        plannedSpending: 0,
        skillsWithHints: 0,
        potentialSavings: 0,
        recentAcquisitions: [],
        totalEvolutionSavings: 0,

        // Filters
        filters: {
            skillType: "all",
            rarity: "all",
            metaTier: "all",
            searchQuery: "",
            hintLevel: "all", // New filter for hint levels
            statAffinity: "all", // New filter for stat affinity (SPD/STA/POW/GUT/WIT)
        },

        // Pagination
        currentPage: 1,
        itemsPerPage: 30,

        // Modal state
        showSkillModal: false,
        selectedSkill: null,

        // Action confirmation modal state
        showActionModal: false,
        actionModalSkill: null,

        // Remove confirmation modal state
        showRemoveModal: false,
        removeModalSkill: null,

        // Initialize
        init() {
            // Load from URL params if present
            const urlParams = new URLSearchParams(window.location.search);
            const characterIdFromUrl =
                urlParams.get("character") || this.selectedCharacterId;
            if (characterIdFromUrl) {
                this.selectedCharacterId = characterIdFromUrl;
                this.loadCharacterData();
            }
        },

        // Load character data
        async loadCharacterData() {
            if (!this.selectedCharacterId) return;

            this.loading = true;
            try {
                // Update URL
                const url = new URL(window.location);
                url.searchParams.set("character", this.selectedCharacterId);
                window.history.pushState({}, "", url);

                // Load all data in parallel
                await Promise.all([
                    this.loadCharacter(),
                    this.loadSkills(),
                    this.loadHints(),
                    this.loadEvolutionOpportunities(),
                    this.loadSPStats(),
                    this.loadAgentPerformance(),
                ]);

                // Calculate metrics after all data is loaded
                this.calculateSPMetrics();
            } catch (error) {
                console.error("Error loading character data:", error);
                this.showError("Failed to load character data");
            } finally {
                this.loading = false;
            }
        },

        // Load character
        async loadCharacter() {
            const response = await fetch(
                `/api/characters/${this.selectedCharacterId}`,
                {
                    headers: {
                        Accept: "application/json",
                        "X-CSRF-TOKEN":
                            document.querySelector('meta[name="csrf-token"]')
                                ?.content || "",
                    },
                },
            );
            if (!response.ok) throw new Error("Failed to load character");
            const data = await response.json();
            // API returns character directly, not wrapped in data property
            this.character = data.data || data;
        },

        // Load skills
        async loadSkills() {
            const response = await fetch(
                `/api/skills?character_id=${this.selectedCharacterId}`,
                {
                    headers: {
                        Accept: "application/json",
                        "X-CSRF-TOKEN":
                            document.querySelector('meta[name="csrf-token"]')
                                ?.content || "",
                    },
                },
            );
            if (!response.ok) throw new Error("Failed to load skills");
            const data = await response.json();
            this.skills = data.data;

            // Separate acquired and planned skills
            this.acquiredSkills = this.skills.filter((s) => s.is_acquired);
            this.plannedSkills = this.skills.filter(
                (s) => !s.is_acquired && s.is_planned,
            );
        },

        // Load hints
        async loadHints() {
            const response = await fetch(
                `/api/characters/${this.selectedCharacterId}/skill-hints`,
                {
                    headers: {
                        Accept: "application/json",
                        "X-CSRF-TOKEN":
                            document.querySelector('meta[name="csrf-token"]')
                                ?.content || "",
                    },
                },
            );
            if (!response.ok) throw new Error("Failed to load hints");
            const data = await response.json();
            this.hints = data.data;
        },

        // Load evolution opportunities
        async loadEvolutionOpportunities() {
            const response = await fetch(
                `/api/characters/${this.selectedCharacterId}/skill-evolution/opportunities`,
                {
                    headers: {
                        Accept: "application/json",
                        "X-CSRF-TOKEN":
                            document.querySelector('meta[name="csrf-token"]')
                                ?.content || "",
                    },
                },
            );
            if (!response.ok)
                throw new Error("Failed to load evolution opportunities");
            const data = await response.json();
            this.evolutionOpportunities = data.data;
        },

        // Load SP statistics
        async loadSPStats() {
            const response = await fetch(
                `/api/characters/${this.selectedCharacterId}/skill-hints/statistics`,
                {
                    headers: {
                        Accept: "application/json",
                        "X-CSRF-TOKEN":
                            document.querySelector('meta[name="csrf-token"]')
                                ?.content || "",
                    },
                },
            );
            if (!response.ok) throw new Error("Failed to load SP statistics");
            const data = await response.json();
            this.spStats = data.data;
        },

        // Load agent performance
        async loadAgentPerformance() {
            const response = await fetch(
                `/api/characters/${this.selectedCharacterId}/agent-performance`,
                {
                    headers: {
                        Accept: "application/json",
                        "X-CSRF-TOKEN":
                            document.querySelector('meta[name="csrf-token"]')
                                ?.content || "",
                    },
                },
            );
            if (!response.ok)
                throw new Error("Failed to load agent performance");
            const data = await response.json();
            this.agentPerformance = data.data;

            // Calculate SP planning metrics
            this.calculateSPMetrics();
        },

        // Calculate SP planning metrics
        calculateSPMetrics() {
            // Calculate skills with hints
            this.skillsWithHints = this.hints.length;

            // Calculate potential savings using progressive hint discounts
            // Level 1=10%, 2=20%, 3=30%, 4=35%, 5=40% (max)
            const hintDiscountTable = {
                1: 0.1,
                2: 0.2,
                3: 0.3,
                4: 0.35,
                5: 0.4,
            };
            this.potentialSavings = this.hints.reduce((total, hint) => {
                const skill = this.skills.find((s) => s.id === hint.skill_id);
                if (!skill) return total;

                const baseCost = skill.base_sp_cost || 0;
                const hintLevel = Math.min(
                    Math.max(hint.hint_count || 1, 1),
                    5,
                );
                const discountPercent = hintDiscountTable[hintLevel] || 0;
                const savings = baseCost * discountPercent;

                return total + savings;
            }, 0);

            // Calculate planned spending from recommendations
            this.plannedSpending = this.recommendations.reduce((total, rec) => {
                return total + (rec.final_cost || 0);
            }, 0);

            // Load recent acquisitions
            this.recentAcquisitions = this.acquiredSkills
                .sort((a, b) => (b.turn_acquired || 0) - (a.turn_acquired || 0))
                .slice(0, 5);

            // Calculate total evolution savings
            this.totalEvolutionSavings = this.evolutionOpportunities.reduce(
                (total, opp) => {
                    return total + (opp.sp_savings || 0);
                },
                0,
            );
        },

        // Refresh all data
        async refreshData() {
            if (this.selectedCharacterId) {
                await this.loadCharacterData();
                this.showSuccess("Data refreshed successfully");
            }
        },

        // Computed: Filtered skills
        get filteredSkills() {
            let filtered = this.skills;

            // Filter by skill type
            if (this.filters.skillType !== "all") {
                filtered = filtered.filter(
                    (s) => s.skill_type === this.filters.skillType,
                );
            }

            // Filter by rarity
            if (this.filters.rarity !== "all") {
                filtered = filtered.filter(
                    (s) => s.rarity === this.filters.rarity,
                );
            }

            // Filter by meta tier
            if (this.filters.metaTier !== "all") {
                filtered = filtered.filter(
                    (s) => s.meta_tier === this.filters.metaTier,
                );
            }

            // Filter by hint level
            if (this.filters.hintLevel !== "all") {
                const hintLevel = parseInt(this.filters.hintLevel);
                filtered = filtered.filter(
                    (s) => s.available_hints === hintLevel,
                );
            }

            // Filter by stat affinity
            if (this.filters.statAffinity !== "all") {
                filtered = filtered.filter((s) => {
                    const affinity = this.getStatAffinity(s);
                    return affinity === this.filters.statAffinity;
                });
            }

            // Filter by search query
            if (this.filters.searchQuery) {
                const query = this.filters.searchQuery.toLowerCase();
                filtered = filtered.filter(
                    (s) =>
                        s.name.toLowerCase().includes(query) ||
                        s.description?.toLowerCase().includes(query),
                );
            }

            // Sort: acquired first → planned second → untracked last
            const priority = (s) => {
                if (s.is_acquired) return 0;
                if (s.is_planned) return 1;
                return 2;
            };
            filtered = [...filtered].sort((a, b) => priority(a) - priority(b));

            return filtered;
        },

        // Computed: Paginated skills
        get paginatedSkills() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            return this.filteredSkills.slice(start, end);
        },

        // Computed: Total pages
        get totalPages() {
            return Math.ceil(this.filteredSkills.length / this.itemsPerPage);
        },

        // Navigate to page
        goToPage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
                // Scroll to top of skills list
                document
                    .getElementById("skills-list")
                    ?.scrollIntoView({ behavior: "smooth" });
            }
        },

        // Reset pagination when filters change
        resetPagination() {
            this.currentPage = 1;
        },

        // Acquire skill — opens the in-app action modal instead of browser confirm()
        acquireSkill(skill) {
            if (!this.selectedCharacterId) {
                this.showError("Please select a character first");
                return;
            }

            // Metadata-only skills (from career history) cannot be acquired via the catalog
            if (skill.is_metadata_only) {
                this.showError(
                    `"${skill.name}" is a career-history skill and cannot be acquired through the catalog.`,
                );
                return;
            }

            this.openActionModal(skill);
        },

        // Open the action choice modal
        openActionModal(skill) {
            this.actionModalSkill = skill;
            this.showActionModal = true;
        },

        // Close the action choice modal
        closeActionModal() {
            this.showActionModal = false;
            this.actionModalSkill = null;
        },

        // Called when the user clicks "Acquire Now" inside the action modal
        async confirmAcquire() {
            const skill = this.actionModalSkill;
            if (!skill) return;

            // Check SP availability (skip for admin)
            if (!this.isAdmin) {
                const availableSP = this.character?.available_sp || 0;
                const finalCost = skill.discounted_cost || skill.base_sp_cost;

                if (availableSP < finalCost) {
                    this.showError(
                        `Insufficient SP. Need ${finalCost} SP, have ${availableSP} SP`,
                    );
                    return;
                }
            }

            this.closeActionModal();
            this.loading = true;
            try {
                const response = await fetch("/api/skills/acquire", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-CSRF-TOKEN":
                            document.querySelector('meta[name="csrf-token"]')
                                ?.content || "",
                    },
                    body: JSON.stringify({
                        character_id: this.selectedCharacterId,
                        skill_id: skill.id,
                        turn_acquired: this.character?.current_turn || 1,
                        career_phase: this.character?.career_stage || "junior",
                    }),
                });

                if (!response.ok) {
                    const error = await response.json();
                    throw new Error(error.message || "Failed to acquire skill");
                }

                const data = await response.json();
                this.showSuccess(
                    `Successfully acquired "${skill.name}"! ${data.data.sp_saved > 0 ? `Saved ${data.data.sp_saved} SP with hints.` : ""}`,
                );

                await this.loadCharacterData();
            } catch (error) {
                console.error("Error acquiring skill:", error);
                this.showError(error.message || "Failed to acquire skill");
            } finally {
                this.loading = false;
            }
        },

        // Called when the user clicks "Mark as Planned" inside the action modal
        async confirmPlan() {
            const skill = this.actionModalSkill;
            if (!skill) return;

            this.closeActionModal();
            this.loading = true;
            try {
                const response = await fetch("/api/skills/plan", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-CSRF-TOKEN":
                            document.querySelector('meta[name="csrf-token"]')
                                ?.content || "",
                    },
                    body: JSON.stringify({
                        character_id: this.selectedCharacterId,
                        skill_id: skill.id,
                    }),
                });

                if (!response.ok) {
                    const error = await response.json();
                    throw new Error(error.message || "Failed to plan skill");
                }

                const data = await response.json();
                this.showSuccess(data.message || `"${skill.name}" added to career plan.`);

                await this.loadCharacterData();
            } catch (error) {
                console.error("Error planning skill:", error);
                this.showError(error.message || "Failed to plan skill");
            } finally {
                this.loading = false;
            }
        },

        openRemoveModal(skill) {
            this.removeModalSkill = skill;
            this.showRemoveModal = true;
        },

        closeRemoveModal() {
            this.showRemoveModal = false;
            this.removeModalSkill = null;
        },

        async confirmRemove() {
            const skill = this.removeModalSkill;
            if (!skill) return;

            this.closeRemoveModal();
            this.loading = true;
            try {
                const response = await fetch("/api/skills/remove", {
                    method: "DELETE",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-CSRF-TOKEN":
                            document.querySelector('meta[name="csrf-token"]')
                                ?.content || "",
                    },
                    body: JSON.stringify({
                        character_id: this.selectedCharacterId,
                        skill_id: skill.id,
                    }),
                });

                if (!response.ok) {
                    const error = await response.json();
                    throw new Error(error.message || "Failed to remove skill");
                }

                const data = await response.json();
                this.showSuccess(
                    data.message || `"${skill.name}" removed from career plan.`
                );

                await this.loadCharacterData();
            } catch (error) {
                console.error("Error removing skill:", error);
                this.showError(error.message || "Failed to remove skill");
            } finally {
                this.loading = false;
            }
        },

        // Get AI recommendations
        async getAIRecommendations() {
            if (!this.selectedCharacterId) return;

            this.loading = true;
            try {
                const response = await fetch(
                    `/api/characters/${this.selectedCharacterId}/skill-recommendations`,
                    {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            "X-CSRF-TOKEN":
                                document.querySelector(
                                    'meta[name="csrf-token"]',
                                )?.content || "",
                        },
                    },
                );

                if (!response.ok)
                    throw new Error("Failed to get AI recommendations");

                const data = await response.json();
                this.recommendations = data.data.recommendations || [];
                this.aiOptimization = data.data;

                this.showSuccess("AI recommendations loaded successfully");
            } catch (error) {
                console.error("Error getting AI recommendations:", error);
                this.showError("Failed to get AI recommendations");
            } finally {
                this.loading = false;
            }
        },

        // View skill details
        viewSkillDetails(skill) {
            this.selectedSkill = skill;
            this.showSkillModal = true;
        },

        // Close modal
        closeSkillModal() {
            this.showSkillModal = false;
            this.selectedSkill = null;
        },

        // Utility functions
        showSuccess(message) {
            window.dispatchEvent(
                new CustomEvent("toast", {
                    detail: { type: "success", message },
                }),
            );
        },

        showError(message) {
            window.dispatchEvent(
                new CustomEvent("toast", {
                    detail: { type: "error", message },
                }),
            );
        },

        formatNumber(num) {
            return new Intl.NumberFormat().format(num);
        },

        // Get skill grade (meta_tier to grade mapping)
        getSkillGrade(skill) {
            // Database uses S+, S, A, B, C
            return skill.meta_tier || "C";
        },

        // Get skill type display name
        getSkillTypeDisplay(skillType) {
            const typeMap = {
                speed: "Speed",
                passive: "Passive",
                recovery: "Recovery",
                debuff: "Debuff",
                unique: "Unique",
            };
            return typeMap[skillType] || skillType;
        },

        // Get skill type color
        getSkillTypeColor(skillType) {
            const colorMap = {
                speed: "blue",
                stamina: "green",
                power: "red",
                guts: "orange",
                wisdom: "purple",
                passive: "gray",
                recovery: "teal",
                debuff: "pink",
                unique: "yellow",
            };
            return colorMap[skillType] || "gray";
        },

        // Get stat affinity based on skill effects (improved classification)
        getStatAffinity(skill) {
            if (!skill.effects) return "speed";

            const effects =
                typeof skill.effects === "string"
                    ? JSON.parse(skill.effects)
                    : skill.effects;

            const effectType = effects.effect?.toLowerCase() || "";
            const activation = effects.activation?.toLowerCase() || "";
            const skillName = skill.name?.toLowerCase() || "";
            const skillType = skill.skill_type?.toLowerCase() || "";
            const description = skill.description?.toLowerCase() || "";

            // Stamina affinity - stamina recovery, conservation, endurance, HP management
            if (
                effectType.includes("stamina") ||
                effectType.includes("recovery") ||
                effectType.includes("conservation") ||
                effectType.includes("endurance") ||
                effectType.includes("hp") ||
                effectType.includes("restore") ||
                skillType === "recovery" ||
                skillName.includes("stamina") ||
                skillName.includes("recovery") ||
                skillName.includes("endurance") ||
                skillName.includes("conserve") ||
                skillName.includes("restore") ||
                description.includes("stamina") ||
                description.includes("recover")
            ) {
                return "stamina";
            }

            // Wisdom affinity - positioning, strategy, debuffs, corners, blocking, lane changes
            if (
                effectType.includes("wisdom") ||
                effectType.includes("position") ||
                effectType.includes("strategy") ||
                effectType.includes("opponent") ||
                effectType.includes("block") ||
                effectType.includes("hinder") ||
                effectType.includes("lane") ||
                effectType.includes("path") ||
                skillType === "debuff" ||
                skillType === "passive" ||
                activation.includes("corner") ||
                activation.includes("lane") ||
                skillName.includes("corner") ||
                skillName.includes("block") ||
                skillName.includes("position") ||
                skillName.includes("wisdom") ||
                skillName.includes("master") ||
                skillName.includes("expert") ||
                skillName.includes("lane") ||
                skillName.includes("path") ||
                description.includes("position") ||
                description.includes("corner")
            ) {
                return "wisdom";
            }

            // Guts affinity - late race, final stretch, determination, willpower, last spurt
            if (
                effectType.includes("guts") ||
                effectType.includes("determination") ||
                effectType.includes("willpower") ||
                effectType.includes("last") ||
                effectType.includes("spurt") ||
                effectType.includes("finish") ||
                activation.includes("late") ||
                activation.includes("final") ||
                activation.includes("last") ||
                activation.includes("spurt") ||
                activation.includes("stretch") ||
                skillName.includes("guts") ||
                skillName.includes("final") ||
                skillName.includes("last") ||
                skillName.includes("determination") ||
                skillName.includes("spurt") ||
                skillName.includes("finish") ||
                skillName.includes("stretch") ||
                description.includes("final") ||
                description.includes("last")
            ) {
                return "guts";
            }

            // Power affinity - overtaking, pushing, mid-race bursts, acceleration, surging
            if (
                effectType.includes("power") ||
                effectType.includes("overtake") ||
                effectType.includes("push") ||
                effectType.includes("burst") ||
                effectType.includes("charge") ||
                effectType.includes("surge") ||
                effectType.includes("accelerat") ||
                activation.includes("mid") ||
                activation.includes("overtake") ||
                activation.includes("surge") ||
                skillName.includes("power") ||
                skillName.includes("charge") ||
                skillName.includes("burst") ||
                skillName.includes("turbo") ||
                skillName.includes("quick") ||
                skillName.includes("surge") ||
                skillName.includes("overtake") ||
                description.includes("accelerat") ||
                description.includes("overtake")
            ) {
                return "power";
            }

            // Speed affinity - speed boosts, escape, pace (default for most)
            return "speed";
        },

        // Get stat affinity display with icon
        getStatAffinityDisplay(skill) {
            const affinity = this.getStatAffinity(skill);
            const affinityMap = {
                speed: { icon: "🏃", label: "SPD", color: "blue" },
                stamina: { icon: "💪", label: "STA", color: "green" },
                power: { icon: "⚡", label: "POW", color: "orange" },
                guts: { icon: "🔥", label: "GUT", color: "red" },
                wisdom: { icon: "🧠", label: "WIT", color: "purple" },
            };
            return affinityMap[affinity] || affinityMap.speed;
        },

        // Get hint level stars display (★★★☆☆ format)
        getHintStars(hintLevel) {
            const level = Math.min(Math.max(hintLevel || 0, 0), 5);
            let stars = "";
            for (let i = 1; i <= 5; i++) {
                stars += i <= level ? "★" : "☆";
            }
            return stars;
        },

        // Get hint level color class
        getHintLevelColor(hintLevel) {
            const level = Math.min(Math.max(hintLevel || 0, 0), 5);
            if (level === 0) return "text-gray-400 dark:text-gray-600";
            if (level === 1) return "text-amber-600 dark:text-amber-400"; // Bronze
            if (level === 2) return "text-gray-500 dark:text-gray-400"; // Silver
            if (level >= 3 && level <= 4)
                return "text-yellow-500 dark:text-yellow-400"; // Gold
            return "text-purple-500 dark:text-purple-400"; // Max (5)
        },

        // Get activation condition display
        getActivationCondition(skill) {
            if (!skill.effects) return null;

            const effects =
                typeof skill.effects === "string"
                    ? JSON.parse(skill.effects)
                    : skill.effects;

            const activation = effects.activation;
            if (!activation) return null;

            // Format activation text
            return activation
                .replace(/_/g, " ")
                .replace(/\b\w/g, (l) => l.toUpperCase());
        },

        // Get evolution info
        getEvolutionInfo(skill) {
            if (!skill.evolves_to_id && !skill.evolves_to_name) return null;

            return {
                canEvolve: true,
                targetName: skill.evolves_to_name || "Unknown Skill",
                targetId: skill.evolves_to_id,
            };
        },

        // Format SP cost with discount
        formatCostWithDiscount(skill) {
            const baseCost = skill.base_sp_cost || 0;
            const finalCost = skill.discounted_cost || baseCost;
            const savings = skill.sp_savings || 0;
            const hintLevel = skill.available_hints || 0;

            if (hintLevel === 0 || savings === 0) {
                return {
                    baseCost,
                    finalCost,
                    savings: 0,
                    percentage: 0,
                    hasDiscount: false,
                };
            }

            const percentage = Math.round((savings / baseCost) * 100);

            return {
                baseCost,
                finalCost,
                savings,
                percentage,
                hasDiscount: true,
            };
        },
    }));
});
