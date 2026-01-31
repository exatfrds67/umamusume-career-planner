// External data browser page logic (Alpine.js component)

/**
 * External Data Browser Alpine.js Component
 *
 * Manages the external data browsing interface for characters, support cards, skills, and news.
 * Fetches data from individual API endpoints in parallel and provides error handling,
 * retry functionality, and partial failure resilience.
 *
 * @returns {Object} Alpine.js component object
 */
export default function externalDataBrowser() {
    return {
        // UI state
        activeTab: "characters",
        loading: false,

        // Per-endpoint loading states (3.2.1)
        loadingCharacters: false,
        loadingSupportCards: false,
        loadingSkills: false,
        loadingNews: false,

        // API availability
        apiAvailable: true,

        // Search and filtering
        searchTerm: "",
        sortBy: "id-asc",
        filters: {
            category: "",
            rarity: [],
            importStatus: [],
            skillRarity: [],
            skillType: "",
        },

        // Per-endpoint error tracking (2.1.1)
        errors: {
            characters: null,
            supportCards: null,
            skills: null,
            news: null,
        },

        // Data arrays
        characters: [],
        supportCards: [],
        skills: [],
        news: [],

        // Data source metadata (3.3.1)
        dataSource: {
            characters: null, // 'live', 'cached', or 'offline'
            supportCards: null,
            skills: null,
            news: null,
        },

        // Performance metrics (4.3.2)
        performanceMetrics: {
            characters: { responseTime: null, timestamp: null },
            supportCards: { responseTime: null, timestamp: null },
            skills: { responseTime: null, timestamp: null },
            news: { responseTime: null, timestamp: null },
        },

        // Filtered data arrays
        filteredCharacters: [],
        filteredSupportCards: [],
        filteredSkills: [],

        /**
         * Initialize the component and load data
         */
        init() {
            this.loadData();
        },

        /**
         * Fetch data from an API endpoint with proper error handling
         *
         * Implements standardized error handling, CSRF token management, and response validation.
         * Returns a consistent response format regardless of success or failure.
         * Measures and logs response time for performance monitoring (4.3.2).
         *
         * @param {string} url - The API endpoint URL to fetch from
         * @returns {Promise<{success: boolean, data: any, error: string|null, source: string|null, cached: boolean, offline_mode: boolean, responseTime: number}>} Standardized response object with performance metrics
         *
         * @example
         * const result = await this.fetchEndpoint('/api/external/characters');
         * if (result.success) {
         *     this.characters = result.data;
         *     console.log(`Response time: ${result.responseTime}ms`);
         * } else {
         *     console.error(result.error);
         * }
         */
        async fetchEndpoint(url) {
            // Start performance measurement (4.3.2)
            const startTime = performance.now();

            try {
                // Make the fetch request with CSRF token (1.2.1.2)
                const response = await fetch(url, {
                    headers: {
                        Accept: "application/json",
                        "X-CSRF-TOKEN":
                            document.querySelector('meta[name="csrf-token"]')
                                ?.content || "",
                    },
                });

                // Calculate response time (4.3.2)
                const endTime = performance.now();
                const responseTime = Math.round(endTime - startTime);

                // Handle HTTP errors (1.2.1.1)
                if (!response.ok) {
                    return {
                        success: false,
                        data: null,
                        error: `HTTP ${response.status}: ${response.statusText}`,
                        source: null,
                        cached: false,
                        offline_mode: false,
                        responseTime,
                    };
                }

                // Parse JSON response
                const data = await response.json();

                // Validate response structure (1.2.1.3)
                if (!data || typeof data !== "object") {
                    return {
                        success: false,
                        data: null,
                        error: "Invalid response format: expected object",
                        source: null,
                        cached: false,
                        offline_mode: false,
                        responseTime,
                    };
                }

                // Check if response has success property
                if (!("success" in data)) {
                    return {
                        success: false,
                        data: null,
                        error: "Invalid response format: missing success property",
                        source: null,
                        cached: false,
                        offline_mode: false,
                        responseTime,
                    };
                }

                // Log performance metrics (4.3.2)
                const dataSource = this.determineDataSourceFromResponse(data);
                console.log(
                    `[Performance] ${url}: ${responseTime}ms (${dataSource})`,
                );

                // Warn if response time exceeds targets (4.3.2.1, 4.3.2.2)
                if (data.cached && responseTime > 1000) {
                    console.warn(
                        `[Performance Warning] Cached response exceeded 1s target: ${responseTime}ms for ${url}`,
                    );
                } else if (!data.cached && responseTime > 3000) {
                    console.warn(
                        `[Performance Warning] Fresh response exceeded 3s target: ${responseTime}ms for ${url}`,
                    );
                }

                // Return standardized response format (1.2.1.4) with source metadata (3.3.1) and performance metrics (4.3.2)
                return {
                    success: data.success,
                    data: data.data || null,
                    error: data.success
                        ? null
                        : data.message || data.error || "Unknown error",
                    source: data.source || null,
                    cached: data.cached || false,
                    offline_mode: data.offline_mode || false,
                    responseTime,
                };
            } catch (error) {
                // Calculate response time even for errors (4.3.2)
                const endTime = performance.now();
                const responseTime = Math.round(endTime - startTime);

                console.error(
                    `[Performance] ${url}: ${responseTime}ms (error)`,
                );

                // Handle network errors and other exceptions (1.2.1.1)
                return {
                    success: false,
                    data: null,
                    error: error.message || "Network error occurred",
                    source: null,
                    cached: false,
                    offline_mode: false,
                    responseTime,
                };
            }
        },

        /**
         * Load data from individual API endpoints in parallel
         *
         * Makes concurrent requests to all four endpoints using Promise.all() for optimal performance.
         * Implements partial failure resilience - successful endpoints work even if others fail.
         * Updates component state with fetched data and error information.
         *
         * Error Handling Strategy:
         * - Each endpoint failure is tracked independently
         * - Failed endpoints don't block successful ones
         * - API is considered available if at least one endpoint succeeds
         * - Existing data is preserved on failure (partial data maintenance)
         *
         * @returns {Promise<void>}
         */
        async loadData() {
            this.loading = true;

            // Reset errors before loading (2.2.1.1)
            this.errors = {
                characters: null,
                supportCards: null,
                skills: null,
                news: null,
            };

            try {
                // Make parallel API calls to all four endpoints (1.1.1.1)
                const [charactersRes, supportCardsRes, skillsRes, newsRes] =
                    await Promise.all([
                        this.fetchEndpoint("/api/external/characters"), // 1.1.1.2
                        this.fetchEndpoint("/api/external/support-cards"), // 1.1.1.3
                        this.fetchEndpoint("/api/external/skills"), // 1.1.1.4
                        this.fetchEndpoint("/api/external/news"), // 1.1.1.5
                    ]);

                // Process characters response (2.2.1.3 - maintain partial data)
                if (charactersRes.success) {
                    this.characters = charactersRes.data || [];
                    // Store data source metadata (3.3.1)
                    this.dataSource.characters =
                        this.determineDataSource(charactersRes);
                    // Store performance metrics (4.3.2)
                    this.performanceMetrics.characters = {
                        responseTime: charactersRes.responseTime,
                        timestamp: new Date().toISOString(),
                    };
                } else {
                    // Store error message (2.2.1.1)
                    this.errors.characters = charactersRes.error;
                    // Log to console (2.2.1.2)
                    console.error(
                        "Characters endpoint error:",
                        charactersRes.error,
                    );
                    // Maintain partial data (2.2.1.3) - keep existing data
                    // this.characters remains unchanged if it had data
                }

                // Process support cards response
                if (supportCardsRes.success) {
                    this.supportCards = supportCardsRes.data || [];
                    this.dataSource.supportCards =
                        this.determineDataSource(supportCardsRes);
                    this.performanceMetrics.supportCards = {
                        responseTime: supportCardsRes.responseTime,
                        timestamp: new Date().toISOString(),
                    };
                } else {
                    this.errors.supportCards = supportCardsRes.error;
                    console.error(
                        "Support cards endpoint error:",
                        supportCardsRes.error,
                    );
                }

                // Process skills response
                if (skillsRes.success) {
                    this.skills = skillsRes.data || [];
                    this.dataSource.skills =
                        this.determineDataSource(skillsRes);
                    this.performanceMetrics.skills = {
                        responseTime: skillsRes.responseTime,
                        timestamp: new Date().toISOString(),
                    };
                } else {
                    this.errors.skills = skillsRes.error;
                    console.error("Skills endpoint error:", skillsRes.error);
                }

                // Process news response
                if (newsRes.success) {
                    this.news = newsRes.data || [];
                    this.dataSource.news = this.determineDataSource(newsRes);
                    this.performanceMetrics.news = {
                        responseTime: newsRes.responseTime,
                        timestamp: new Date().toISOString(),
                    };
                } else {
                    this.errors.news = newsRes.error;
                    console.error("News endpoint error:", newsRes.error);
                }

                // Determine API availability (2.2.2)
                // True if at least one endpoint succeeds (2.2.2.1)
                // False if all endpoints fail (2.2.2.2)
                this.apiAvailable =
                    charactersRes.success ||
                    supportCardsRes.success ||
                    skillsRes.success ||
                    newsRes.success;

                // Filter data after loading
                this.filterData();

                // Log performance summary (4.3.2)
                this.logPerformanceSummary();
            } catch (error) {
                console.error("Error loading data:", error);
                this.apiAvailable = false;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Filter data based on search term and active filters
         *
         * Applies client-side filtering to characters, support cards, and skills.
         * Filters are applied based on search term, category, rarity, and other criteria.
         * Automatically calls sortData() after filtering.
         *
         * @returns {void}
         */
        filterData() {
            // Filter characters
            this.filteredCharacters = this.characters.filter((char) => {
                if (
                    this.searchTerm &&
                    !char.name_en
                        ?.toLowerCase()
                        .includes(this.searchTerm.toLowerCase())
                ) {
                    return false;
                }
                if (
                    this.filters.category &&
                    char.category_label_en !== this.filters.category
                ) {
                    return false;
                }
                return true;
            });

            // Filter support cards
            this.filteredSupportCards = this.supportCards.filter((card) => {
                if (
                    this.searchTerm &&
                    !card.title_en
                        ?.toLowerCase()
                        .includes(this.searchTerm.toLowerCase())
                ) {
                    return false;
                }
                if (
                    this.filters.rarity.length > 0 &&
                    !this.filters.rarity.includes(card.rarity)
                ) {
                    return false;
                }
                return true;
            });

            // Filter skills
            this.filteredSkills = this.skills.filter((skill) => {
                if (
                    this.searchTerm &&
                    !skill.name_en
                        ?.toLowerCase()
                        .includes(this.searchTerm.toLowerCase())
                ) {
                    return false;
                }
                if (
                    this.filters.skillRarity.length > 0 &&
                    !this.filters.skillRarity.includes(skill.rarity)
                ) {
                    return false;
                }
                if (
                    this.filters.skillType &&
                    skill.type !== this.filters.skillType
                ) {
                    return false;
                }
                return true;
            });

            this.sortData();
        },

        /**
         * Sort filtered data based on current sort criteria
         *
         * Sorts characters, support cards, and skills by the selected field and direction.
         * Supports sorting by ID (numeric), name (alphabetic), and rarity (custom order).
         *
         * @returns {void}
         */
        sortData() {
            const [field, direction] = this.sortBy.split("-");
            const multiplier = direction === "asc" ? 1 : -1;

            // Define rarity order for sorting
            const rarityOrder = {
                // Support card rarities
                SSR: 3,
                SR: 2,
                R: 1,
                // Skill rarities
                unique: 3,
                rare: 2,
                normal: 1,
            };

            [
                this.filteredCharacters,
                this.filteredSupportCards,
                this.filteredSkills,
            ].forEach((arr) => {
                arr.sort((a, b) => {
                    // Numeric ID sorting
                    if (field === "id") {
                        return (a.id - b.id) * multiplier;
                    }

                    // Alphabetic name sorting
                    if (field === "name") {
                        return (
                            (a.name_en || "").localeCompare(b.name_en || "") *
                            multiplier
                        );
                    }

                    // Rarity sorting (custom order)
                    if (field === "rarity") {
                        const rarityA = rarityOrder[a.rarity] || 0;
                        const rarityB = rarityOrder[b.rarity] || 0;
                        // Note: Descending by default (higher rarity first)
                        return (rarityB - rarityA) * multiplier;
                    }

                    // Default: no sorting
                    return 0;
                });
            });
        },

        /**
         * Toggle a filter value on or off
         *
         * @param {string} filterType - The filter type (e.g., 'rarity', 'skillRarity')
         * @param {any} value - The value to toggle
         * @returns {void}
         */
        toggleFilter(filterType, value) {
            if (!this.filters[filterType].includes(value)) {
                this.filters[filterType].push(value);
            } else {
                this.filters[filterType] = this.filters[filterType].filter(
                    (v) => v !== value,
                );
            }
            this.filterData();
        },

        /**
         * Clear all active filters and search term
         *
         * @returns {void}
         */
        clearFilters() {
            this.searchTerm = "";
            this.filters = {
                category: "",
                rarity: [],
                importStatus: [],
                skillRarity: [],
                skillType: "",
            };
            this.filterData();
        },

        /**
         * Check if any filters are currently active
         *
         * @returns {boolean} True if any filters are active
         */
        hasActiveFilters() {
            return (
                this.searchTerm !== "" ||
                this.filters.category !== "" ||
                this.filters.rarity.length > 0 ||
                this.filters.importStatus.length > 0 ||
                this.filters.skillRarity.length > 0 ||
                this.filters.skillType !== ""
            );
        },

        /**
         * Get the count of active filters
         *
         * @returns {number} Number of active filters
         */
        getActiveFiltersCount() {
            let count = 0;
            if (this.searchTerm) count++;
            if (this.filters.category) count++;
            count += this.filters.rarity.length;
            count += this.filters.importStatus.length;
            count += this.filters.skillRarity.length;
            if (this.filters.skillType) count++;
            return count;
        },

        /**
         * Get unique character categories from loaded data
         *
         * @returns {string[]} Array of unique category names
         */
        getUniqueCategories() {
            return [
                ...new Set(
                    this.characters
                        .map((c) => c.category_label_en)
                        .filter(Boolean),
                ),
            ];
        },

        /**
         * Get the image URL for a support card
         *
         * Uses gametora.com CDN for support card images based on card ID.
         * Pattern: https://gametora.com/images/umamusume/supports/tex_support_card_{ID}.png
         *
         * @param {number|string} id - Support card ID
         * @returns {string} Image URL
         */
        getSupportCardImage(id) {
            if (!id) {
                return "/images/app_logo/logo.svg";
            }
            return `https://gametora.com/images/umamusume/supports/tex_support_card_${id}.png`;
        },

        /**
         * Format character name from gametora data
         *
         * @param {Object} gametora - Gametora data object
         * @returns {string} Formatted character name
         */
        formatCharacterName(gametora) {
            return gametora?.character_name || "Unknown";
        },

        /**
         * Show detailed view of a character
         *
         * @param {Object} character - Character data object
         * @returns {void}
         */
        showCharacterDetail(character) {
            console.log("Show character detail:", character);
        },

        /**
         * Show detailed view of a support card
         *
         * @param {Object} card - Support card data object
         * @returns {void}
         */
        showSupportCardDetail(card) {
            console.log("Show support card detail:", card);
        },

        /**
         * Use character data for character creation
         *
         * @param {Object} character - Character data object
         * @returns {void}
         */
        useForCharacterCreation(character) {
            console.log("Use for character creation:", character);
        },

        /**
         * Import a support card to local database
         *
         * @param {Object} card - Support card data object
         * @returns {void}
         */
        importSupportCard(card) {
            console.log("Import support card:", card);
        },

        /**
         * Retry loading data from a specific failed endpoint
         *
         * Allows users to retry individual endpoints that failed during initial load.
         * Sets endpoint-specific loading state and updates data on success.
         *
         * @param {string} endpointName - Name of the endpoint to retry ('characters', 'supportCards', 'skills', 'news')
         * @returns {Promise<void>}
         *
         * @example
         * // Retry loading characters if it failed
         * await this.retryEndpoint('characters');
         */
        async retryEndpoint(endpointName) {
            // Map endpoint names to URLs
            const endpointUrls = {
                characters: "/api/external/characters",
                supportCards: "/api/external/support-cards",
                skills: "/api/external/skills",
                news: "/api/external/news",
            };

            // Map endpoint names to loading state properties
            const loadingStates = {
                characters: "loadingCharacters",
                supportCards: "loadingSupportCards",
                skills: "loadingSkills",
                news: "loadingNews",
            };

            // Validate endpoint name (2.3.1.1)
            if (!endpointUrls[endpointName]) {
                console.error(`Invalid endpoint name: ${endpointName}`);
                return;
            }

            // Set section-specific loading state (3.2.1)
            const loadingState = loadingStates[endpointName];
            this[loadingState] = true;

            // Clear the error for this endpoint
            this.errors[endpointName] = null;

            try {
                // Retry specific failed endpoint (2.3.1.2)
                const response = await this.fetchEndpoint(
                    endpointUrls[endpointName],
                );

                // Update state on success/failure (2.3.1.3)
                if (response.success) {
                    this[endpointName] = response.data || [];
                    // Update data source metadata (3.3.1)
                    this.dataSource[endpointName] =
                        this.determineDataSource(response);
                    // Update performance metrics (4.3.2)
                    this.performanceMetrics[endpointName] = {
                        responseTime: response.responseTime,
                        timestamp: new Date().toISOString(),
                    };
                    // Update API availability if it was previously unavailable
                    if (!this.apiAvailable) {
                        this.apiAvailable = true;
                    }
                    // Re-filter data after successful retry
                    this.filterData();
                    // Log performance for this retry (4.3.2)
                    console.log(
                        `[Performance] Retry ${endpointName}: ${response.responseTime}ms`,
                    );
                } else {
                    // Store error if retry fails
                    this.errors[endpointName] = response.error;
                    console.error(
                        `${endpointName} endpoint retry failed:`,
                        response.error,
                    );
                }
            } catch (error) {
                this.errors[endpointName] = error.message;
                console.error(`Error retrying ${endpointName}:`, error);
            } finally {
                // Clear section-specific loading state (3.2.2)
                this[loadingState] = false;
            }
        },

        /**
         * Retry loading all data by calling loadData() again
         *
         * Clears all previous errors and attempts to reload all endpoints.
         * Useful when multiple endpoints failed or for a full refresh.
         *
         * @returns {Promise<void>}
         *
         * @example
         * // Retry all failed endpoints
         * await this.retryAll();
         */
        async retryAll() {
            // Call loadData() again (2.3.2.1)
            // This will clear previous errors (2.3.2.2) as loadData() resets the errors object
            await this.loadData();
        },

        /**
         * Helper methods for UI
         */

        /**
         * Determine data source type from response object
         *
         * Helper method used by fetchEndpoint to determine data source before storing in state.
         *
         * @param {Object} response - Response object with metadata
         * @returns {string} Data source type: 'live', 'cached', or 'offline'
         */
        determineDataSourceFromResponse(response) {
            if (!response) {
                return null;
            }

            // Check if offline mode (database cache fallback)
            if (response.offline_mode) {
                return "offline";
            }

            // Check if cached (memory cache or Redis)
            if (response.cached) {
                return "cached";
            }

            // Check source string for additional context
            if (response.source) {
                if (
                    response.source.includes("database_cache") ||
                    response.source.includes("offline")
                ) {
                    return "offline";
                }
                if (
                    response.source.includes("cache") ||
                    response.source.includes("redis")
                ) {
                    return "cached";
                }
                if (
                    response.source.includes("api") ||
                    response.source.includes("umapyoi")
                ) {
                    return "live";
                }
                if (response.source.includes("local database")) {
                    return "local";
                }
            }

            // Default to live if we have successful data
            return "live";
        },

        /**
         * Determine data source type from API response
         *
         * Analyzes the response metadata to determine if data is live, cached, or offline.
         *
         * @param {Object} response - API response object with source metadata
         * @returns {string} Data source type: 'live', 'cached', or 'offline'
         */
        determineDataSource(response) {
            if (!response || !response.success) {
                return null;
            }

            // Check if offline mode (database cache fallback)
            if (response.offline_mode) {
                return "offline";
            }

            // Check if cached (memory cache or Redis)
            if (response.cached) {
                return "cached";
            }

            // Check source string for additional context
            if (response.source) {
                if (
                    response.source.includes("database_cache") ||
                    response.source.includes("offline")
                ) {
                    return "offline";
                }
                if (
                    response.source.includes("cache") ||
                    response.source.includes("redis")
                ) {
                    return "cached";
                }
                if (
                    response.source.includes("api") ||
                    response.source.includes("umapyoi")
                ) {
                    return "live";
                }
                if (response.source.includes("local database")) {
                    return "local";
                }
            }

            // Default to live if we have successful data
            return "live";
        },

        /**
         * Get badge configuration for a data source
         *
         * Returns styling and text information for displaying data source badges.
         *
         * @param {string} source - Data source type ('live', 'cached', 'offline', 'local')
         * @returns {Object} Badge configuration with classes and text
         */
        getDataSourceBadge(source) {
            const badges = {
                live: {
                    text: "Live",
                    classes:
                        "bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300",
                    icon: "check-circle",
                },
                cached: {
                    text: "Cached",
                    classes:
                        "bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300",
                    icon: "clock",
                },
                offline: {
                    text: "Offline",
                    classes:
                        "bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300",
                    icon: "exclamation",
                },
                local: {
                    text: "Local",
                    classes:
                        "bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300",
                    icon: "database",
                },
            };

            return badges[source] || null;
        },

        /**
         * Check if a data source badge should be shown
         *
         * @param {string} endpointName - Name of the endpoint ('characters', 'supportCards', 'skills', 'news')
         * @returns {boolean} True if badge should be shown
         */
        shouldShowDataSourceBadge(endpointName) {
            return this.dataSource[endpointName] !== null;
        },

        /**
         * Truncate text to a maximum length
         *
         * @param {string} text - Text to truncate
         * @param {number} maxLength - Maximum length before truncation
         * @returns {string} Truncated text with ellipsis if needed
         */
        truncateText(text, maxLength) {
            if (!text) return "";
            if (text.length <= maxLength) return text;
            return text.substring(0, maxLength) + "...";
        },

        /**
         * Strip HTML tags from a string
         *
         * @param {string} html - HTML string to strip
         * @returns {string} Plain text without HTML tags
         */
        stripHtml(html) {
            if (!html) return "";
            const tmp = document.createElement("div");
            tmp.innerHTML = html;
            return tmp.textContent || tmp.innerText || "";
        },

        /**
         * Get unique skill types from loaded skills
         *
         * @returns {string[]} Array of unique skill types
         */
        getUniqueSkillTypes() {
            return [...new Set(this.skills.map((s) => s.type).filter(Boolean))];
        },

        /**
         * Get skill type icon emoji based on skill type
         *
         * @param {string} type - Skill type (speed, stamina, power, guts, wit, etc.)
         * @returns {string} Emoji icon for the skill type
         */
        getSkillTypeIcon(type) {
            const icons = {
                speed: "⚡",
                stamina: "💚",
                power: "💪",
                guts: "🔥",
                wit: "💡",
                wisdom: "💡",
                unique: "⭐",
                debuff: "💀",
                recovery: "💖",
                acceleration: "🚀",
                positioning: "📍",
                vision: "👁️",
                gate: "🚪",
                corner: "↩️",
                straight: "➡️",
                final: "🏁",
            };
            return icons[type?.toLowerCase()] || "✨";
        },

        /**
         * Get CSS classes for skill type icon background
         *
         * @param {string} type - Skill type
         * @returns {string} Tailwind CSS classes for the icon container
         */
        getSkillTypeClasses(type) {
            const classes = {
                speed: "bg-red-100 dark:bg-red-900/30",
                stamina: "bg-green-100 dark:bg-green-900/30",
                power: "bg-yellow-100 dark:bg-yellow-900/30",
                guts: "bg-orange-100 dark:bg-orange-900/30",
                wit: "bg-blue-100 dark:bg-blue-900/30",
                wisdom: "bg-blue-100 dark:bg-blue-900/30",
                unique: "bg-pink-100 dark:bg-pink-900/30",
                debuff: "bg-gray-100 dark:bg-gray-700",
                recovery: "bg-rose-100 dark:bg-rose-900/30",
                acceleration: "bg-cyan-100 dark:bg-cyan-900/30",
                positioning: "bg-indigo-100 dark:bg-indigo-900/30",
                vision: "bg-violet-100 dark:bg-violet-900/30",
            };
            return (
                classes[type?.toLowerCase()] || "bg-gray-100 dark:bg-gray-700"
            );
        },

        /**
         * Get performance summary for all endpoints (4.3.2)
         *
         * Returns a summary of response times for all endpoints, including average,
         * min, max, and whether they meet performance targets.
         *
         * @returns {Object} Performance summary with statistics
         */
        getPerformanceSummary() {
            const metrics = Object.entries(this.performanceMetrics)
                .filter(([_, metric]) => metric.responseTime !== null)
                .map(([endpoint, metric]) => ({
                    endpoint,
                    responseTime: metric.responseTime,
                    timestamp: metric.timestamp,
                    dataSource: this.dataSource[endpoint],
                }));

            if (metrics.length === 0) {
                return {
                    count: 0,
                    average: null,
                    min: null,
                    max: null,
                    allMeetTargets: null,
                    metrics: [],
                };
            }

            const responseTimes = metrics.map((m) => m.responseTime);
            const average = Math.round(
                responseTimes.reduce((a, b) => a + b, 0) / responseTimes.length,
            );
            const min = Math.min(...responseTimes);
            const max = Math.max(...responseTimes);

            // Check if all metrics meet their targets (4.3.2.1, 4.3.2.2)
            const allMeetTargets = metrics.every((m) => {
                if (m.dataSource === "cached") {
                    return m.responseTime < 1000; // Cached target: < 1s
                } else {
                    return m.responseTime < 3000; // Fresh target: < 3s
                }
            });

            return {
                count: metrics.length,
                average,
                min,
                max,
                allMeetTargets,
                metrics,
            };
        },

        /**
         * Log performance summary to console (4.3.2)
         *
         * Outputs a formatted performance report to the console for debugging
         * and performance monitoring.
         *
         * @returns {void}
         */
        logPerformanceSummary() {
            const summary = this.getPerformanceSummary();

            if (summary.count === 0) {
                console.log("[Performance Summary] No metrics available yet");
                return;
            }

            console.group("[Performance Summary]");
            console.log(`Total endpoints measured: ${summary.count}`);
            console.log(`Average response time: ${summary.average}ms`);
            console.log(`Min response time: ${summary.min}ms`);
            console.log(`Max response time: ${summary.max}ms`);
            console.log(
                `All targets met: ${summary.allMeetTargets ? "✓ Yes" : "✗ No"}`,
            );

            console.group("Individual Endpoints:");
            summary.metrics.forEach((m) => {
                const target = m.dataSource === "cached" ? 1000 : 3000;
                const meetsTarget = m.responseTime < target;
                const status = meetsTarget ? "✓" : "✗";
                console.log(
                    `${status} ${m.endpoint}: ${m.responseTime}ms (${m.dataSource}, target: <${target}ms)`,
                );
            });
            console.groupEnd();

            console.groupEnd();
        },
    };
}
