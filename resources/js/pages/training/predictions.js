/**
 * Training Predictions Page Script (WF-004 Enhanced)
 * Handles AI-powered training recommendations and facility predictions
 * with support card indicators, risk badges, efficiency ratings, and calculation breakdowns
 */

/**
 * Fetch predictions with retry logic
 * @param {string} apiUrl - API endpoint URL
 * @param {number} characterId - Character ID
 * @param {number} maxRetries - Maximum retry attempts
 * @returns {Promise<Object>} Predictions data
 */
export async function fetchPredictionsWithRetry(
    apiUrl,
    characterId,
    maxRetries = 3,
) {
    let lastError = null;

    for (let attempt = 1; attempt <= maxRetries; attempt++) {
        try {
            const response = await fetch(apiUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN":
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute("content") || "",
                },
                body: JSON.stringify({ character_id: characterId }),
            });

            if (!response.ok) {
                throw new Error(
                    `HTTP ${response.status}: ${response.statusText}`,
                );
            }

            const data = await response.json();
            return data;
        } catch (error) {
            lastError = error;
            console.error(`Attempt ${attempt}/${maxRetries} failed:`, error);

            if (attempt < maxRetries) {
                await new Promise((resolve) =>
                    setTimeout(resolve, Math.pow(2, attempt) * 1000),
                );
            }
        }
    }

    throw lastError || new Error("Failed to fetch predictions");
}

/**
 * Update the predictions UI with fetched data (WF-004 Enhanced)
 * @param {Object} predictions - Predictions data from API
 */
export function updatePredictionsUI(predictions) {
    document.getElementById("predictions-loading")?.classList.add("hidden");
    document.getElementById("predictions-error")?.classList.add("hidden");
    document.getElementById("predictions-grid")?.classList.remove("hidden");

    if (!predictions) return;

    const facilities = predictions.facilities || predictions;
    const recommendation = predictions.recommendation || {};
    const summary = predictions.summary || "";

    // Find the recommended training
    const recommendedTraining =
        recommendation.recommended_training || findBestTraining(facilities);

    // Update each facility card
    Object.entries(facilities).forEach(([facility, data]) => {
        updateFacilityCard(facility, data, facility === recommendedTraining);
    });

    // Update AI advisor banner
    updateAIAdvisor(recommendation, summary);

    // Update calculation breakdown if available
    if (recommendation.prediction?.breakdown) {
        updateCalculationBreakdown(recommendation.prediction.breakdown);
    }
}

/**
 * Update a single facility card with prediction data
 * @param {string} facility - Facility name
 * @param {Object} data - Prediction data for this facility
 * @param {boolean} isRecommended - Whether this is the AI recommended option
 */
function updateFacilityCard(facility, data, isRecommended) {
    const card = document.querySelector(
        `.training-facility[data-facility="${facility}"]`,
    );
    if (!card) return;

    // Update AI recommendation badge
    const aiBadge = card.querySelector(".ai-badge");
    if (aiBadge) {
        if (isRecommended) {
            aiBadge.classList.remove("hidden");
        } else {
            aiBadge.classList.add("hidden");
        }
    }

    // Update risk badge
    const riskBadge = card.querySelector(".risk-badge");
    if (riskBadge && data.failure_risk !== undefined) {
        const riskPercent = Math.round(data.failure_risk * 100);
        riskBadge.textContent = `${riskPercent}%`;
        riskBadge.className = `risk-badge px-2 py-1 rounded text-xs font-medium ${getRiskBadgeClass(riskPercent)}`;
    }

    // Update facility level (Unity Cup)
    const facilityLevel = card.querySelector(".facility-level");
    if (facilityLevel && data.breakdown?.facility_bonus !== undefined) {
        const bonus = data.breakdown.facility_bonus;
        const level = Math.round(bonus / 0.25) + 1;
        const multiplier = (1 + bonus).toFixed(2);
        facilityLevel.textContent = `Lv ${level} (${multiplier}×)`;
    }

    // Update stat gains
    if (data.stat_gains) {
        const statGain = card.querySelector(".stat-gain");
        const secondaryGain = card.querySelector(".secondary-gain");

        // Get primary and secondary stats
        const gains = Object.entries(data.stat_gains).sort(
            (a, b) => b[1] - a[1],
        );
        if (statGain && gains[0]) {
            statGain.textContent = `+${gains[0][1]} ${capitalize(gains[0][0])}`;
        }
        if (secondaryGain && gains[1]) {
            secondaryGain.textContent = `+${gains[1][1]} ${capitalize(gains[1][0])}`;
        }
    }

    // Update skill points
    const skillPoints = card.querySelector(".skill-points");
    if (skillPoints && data.skill_points !== undefined) {
        skillPoints.textContent = `+${data.skill_points}`;
    }

    // Update support cards section
    updateSupportCardsSection(card, data);

    // Update skill hints section
    updateSkillHintsSection(card, data);

    // Update efficiency rating
    updateEfficiencyRating(card, data);

    // Rest-specific fields
    if (facility === "rest") {
        const energyRecovery = card.querySelector(".energy-recovery");
        if (energyRecovery) {
            energyRecovery.textContent = `+${data.energy_recovery || 50}`;
        }
        const moodEffect = card.querySelector(".mood-effect");
        if (moodEffect && data.mood_effect) {
            moodEffect.textContent = data.mood_effect;
        }
    }
}

/**
 * Update support cards section in facility card
 * @param {HTMLElement} card - Facility card element
 * @param {Object} data - Prediction data
 */
function updateSupportCardsSection(card, data) {
    const countEl = card.querySelector(".support-card-count");
    const listEl = card.querySelector(".support-card-list");

    if (!countEl || !listEl) return;

    const supportCards = data.active_support_cards || [];
    const cardCount = supportCards.length;
    const bonus = cardCount * 5; // +5% per card

    countEl.textContent = `${cardCount} cards (+${bonus}%)`;
    listEl.innerHTML = "";

    supportCards.forEach((sc) => {
        const badge = document.createElement("span");
        badge.className =
            "px-2 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 flex items-center gap-1";

        let contentHtml = sc.name || sc.card_name || "Card";

        // Add friendship indicator (bond >= 80%)
        if (sc.bond_level >= 80 || sc.friendship_active) {
            contentHtml = `<svg class="w-3 h-3 text-yellow-500 inline mr-1" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" /></svg>${contentHtml}`;
        }

        // Add guaranteed hint indicator
        if (sc.has_guaranteed_hint || sc.has_red_exclamation) {
            contentHtml = `<svg class="w-3 h-3 text-red-500 inline mr-1" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>${contentHtml}`;
        }

        badge.innerHTML = contentHtml;
        listEl.appendChild(badge);
    });
}

/**
 * Update skill hints section in facility card
 * @param {HTMLElement} card - Facility card element
 * @param {Object} data - Prediction data
 */
function updateSkillHintsSection(card, data) {
    const section = card.querySelector(".skill-hints-section");
    const listEl = card.querySelector(".skill-hints-list");

    if (!section || !listEl) return;

    const hints = data.skill_hints || [];

    if (hints.length === 0) {
        section.classList.add("hidden");
        return;
    }

    section.classList.remove("hidden");
    listEl.innerHTML = "";

    hints.forEach((hint) => {
        const row = document.createElement("div");
        row.className = "flex items-center gap-2 text-xs";

        const iconSvg = hint.is_guaranteed
            ? `<svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>`
            : `<svg class="w-4 h-4 text-secondary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>`;
        const probability = hint.is_guaranteed
            ? "Guaranteed"
            : `${hint.probability}%`;

        row.innerHTML = `
            <span class="shrink-0">${iconSvg}</span>
            <span class="font-medium text-gray-700 dark:text-gray-300 ml-1">${hint.skill_name}</span>
            <span class="text-gray-500 ml-1">(${probability})</span>
            ${hint.is_guaranteed ? '<svg class="w-4 h-4 text-red-500 ml-auto" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>' : ""}
        `;

        listEl.appendChild(row);
    });
}

/**
 * Update efficiency rating display
 * @param {HTMLElement} card - Facility card element
 * @param {Object} data - Prediction data
 */
function updateEfficiencyRating(card, data) {
    const starsEl = card.querySelector(".efficiency-stars");
    const scoreEl = card.querySelector(".efficiency-score");

    if (!starsEl || !scoreEl) return;

    // Calculate efficiency score (0-100)
    const score =
        data.recommendation_score ||
        data.efficiency_score ||
        calculateEfficiencyScore(data);

    // Convert to stars (1-5)
    const stars = Math.min(5, Math.max(1, Math.ceil(score / 20)));
    const filledStarSvg = `<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>`;
    const emptyStarSvg = `<svg class="w-4 h-4 text-gray-300 dark:text-gray-600" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>`;

    starsEl.innerHTML =
        filledStarSvg.repeat(stars) + emptyStarSvg.repeat(5 - stars);
    scoreEl.textContent = `(${Math.round(score)})`;
}

/**
 * Calculate efficiency score from prediction data
 * @param {Object} data - Prediction data
 * @returns {number} Efficiency score (0-100)
 */
function calculateEfficiencyScore(data) {
    if (!data.stat_gains) return 50;

    const totalGain = Object.values(data.stat_gains).reduce(
        (sum, val) => sum + val,
        0,
    );
    const riskPenalty = (data.failure_risk || 0) * 30;
    const bonusMultiplier = (data.total_bonus || 0) * 10;

    return Math.min(
        100,
        Math.max(0, totalGain + bonusMultiplier - riskPenalty),
    );
}

/**
 * Update AI advisor banner with recommendation
 * @param {Object} recommendation - Recommendation data
 * @param {string} summary - Summary text
 */
function updateAIAdvisor(recommendation, summary) {
    const messageEl = document.getElementById("ai-advisor-message");
    const confidenceBadge = document.getElementById("ai-confidence-badge");
    const summaryText = document.getElementById("ai-recommendation-text");

    if (messageEl) {
        const training = recommendation.recommended_training;
        const reason = recommendation.reason || "";
        messageEl.textContent = training
            ? `Focus on ${capitalize(training)} training! ${reason}`
            : "Analyzing training options...";
    }

    if (confidenceBadge && recommendation.confidence_score) {
        confidenceBadge.textContent = `${Math.round(recommendation.confidence_score)}% confidence`;
        confidenceBadge.classList.remove("hidden");
    }

    if (summaryText) {
        summaryText.textContent =
            summary ||
            recommendation.reason ||
            "Best option for current character state.";
    }
}

/**
 * Update calculation breakdown panel
 * @param {Object} breakdown - Calculation breakdown data
 */
function updateCalculationBreakdown(breakdown) {
    const elements = {
        base: document.querySelector(".breakdown-base"),
        growth: document.querySelector(".breakdown-growth"),
        mood: document.querySelector(".breakdown-mood"),
        support: document.querySelector(".breakdown-support"),
        friendship: document.querySelector(".breakdown-friendship"),
        facility: document.querySelector(".breakdown-facility"),
        total: document.querySelector(".breakdown-total"),
    };

    if (elements.base && breakdown.base_gains) {
        const primaryGain = Object.values(breakdown.base_gains)[0] || 0;
        elements.base.textContent = `${primaryGain}`;
    }
    if (elements.growth) {
        elements.growth.textContent = `×${(breakdown.growth_rate_multiplier || 1).toFixed(2)}`;
    }
    if (elements.mood) {
        elements.mood.textContent = `×${(breakdown.mood_multiplier || 1).toFixed(2)}`;
    }
    if (elements.support) {
        elements.support.textContent = `×${(breakdown.support_card_presence_multiplier || 1).toFixed(2)}`;
    }
    if (elements.friendship) {
        elements.friendship.textContent = `×${(breakdown.friendship_multiplier || 1).toFixed(2)}`;
    }
    if (elements.facility) {
        const facilityMultiplier = 1 + (breakdown.facility_bonus || 0);
        elements.facility.textContent = `×${facilityMultiplier.toFixed(2)}`;
    }
    if (elements.total) {
        elements.total.textContent = `×${(breakdown.total_multiplier || 1).toFixed(2)}`;
    }
}

/**
 * Get CSS classes for risk badge based on risk percentage
 * @param {number} riskPercent - Risk percentage (0-100)
 * @returns {string} CSS classes
 */
function getRiskBadgeClass(riskPercent) {
    if (riskPercent < 15) {
        return "bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300";
    } else if (riskPercent <= 40) {
        return "bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300";
    } else {
        return "bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300";
    }
}

/**
 * Get CSS classes for recommendation badge
 * @param {string} recommendation - Recommendation level
 * @returns {string} CSS classes
 */
export function getRecommendationClass(recommendation) {
    switch (recommendation?.toLowerCase()) {
        case "recommended":
        case "best":
            return "bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300";
        case "good":
            return "bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300";
        case "avoid":
        case "risky":
            return "bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300";
        default:
            return "bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300";
    }
}

/**
 * Find the best training option from facilities data
 * @param {Object} facilities - Facilities prediction data
 * @returns {string} Best training type
 */
function findBestTraining(facilities) {
    let best = null;
    let bestScore = -Infinity;

    Object.entries(facilities).forEach(([facility, data]) => {
        if (facility === "rest") return;

        const score = calculateEfficiencyScore(data);
        if (score > bestScore) {
            bestScore = score;
            best = facility;
        }
    });

    return best || "speed";
}

/**
 * Capitalize first letter of string
 * @param {string} str - Input string
 * @returns {string} Capitalized string
 */
function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

/**
 * Show error state in predictions UI
 * @param {string} message - Error message to display
 */
export function showPredictionsError(message) {
    document.getElementById("predictions-loading")?.classList.add("hidden");
    document.getElementById("predictions-grid")?.classList.add("hidden");
    document.getElementById("predictions-error")?.classList.remove("hidden");

    const errorMsg = document.getElementById("predictions-error-message");
    if (errorMsg) {
        errorMsg.textContent =
            message || "An error occurred while fetching training predictions.";
    }
}

/**
 * Initialize training predictions on page load
 */
export function initTrainingPredictions() {
    const appEl = document.getElementById("training-predictions-app");
    if (!appEl || !appEl.dataset.characterId) return;

    const characterId = appEl.dataset.characterId;
    const apiUrl = appEl.dataset.apiUrl;

    // Auto-load predictions
    refreshPredictions(apiUrl, characterId);
}

/**
 * Refresh predictions
 * @param {string} apiUrl - API endpoint URL
 * @param {number} characterId - Character ID
 */
async function refreshPredictions(apiUrl, characterId) {
    document.getElementById("predictions-loading")?.classList.remove("hidden");
    document.getElementById("predictions-grid")?.classList.add("hidden");
    document.getElementById("predictions-error")?.classList.add("hidden");

    try {
        const predictions = await fetchPredictionsWithRetry(
            apiUrl,
            characterId,
        );
        updatePredictionsUI(predictions);
    } catch (error) {
        showPredictionsError(error.message);
    }
}

// Auto-initialize when DOM is ready
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initTrainingPredictions);
} else {
    initTrainingPredictions();
}

/**
 * Refresh predictions from the API.
 * Previously inline in training/predictions.blade.php
 */
window.refreshPredictions = async function () {
    const appEl = document.getElementById("training-predictions-app");
    if (!appEl) {
        return;
    }
    const characterId = appEl.dataset.characterId;
    const apiUrl = appEl.dataset.apiUrl;
    document.getElementById("predictions-loading")?.classList.remove("hidden");
    document.getElementById("predictions-grid")?.classList.add("hidden");
    document.getElementById("predictions-error")?.classList.add("hidden");
    try {
        const predictions = await fetchPredictionsWithRetry(
            apiUrl,
            characterId,
        );
        updatePredictionsUI(predictions);
    } catch (error) {
        showPredictionsError(error.message);
    }
};

/**
 * Clear the predictions cache and refresh.
 * Previously inline in training/predictions.blade.php
 */
window.clearCache = async function () {
    const appEl = document.getElementById("training-predictions-app");
    if (!appEl) {
        return;
    }
    const characterId = appEl.dataset.characterId;
    try {
        const response = await fetch(
            `/api/training-predictions/cache/${characterId}`,
            {
                method: "DELETE",
                headers: {
                    Accept: "application/json",
                    "X-CSRF-TOKEN":
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute("content") || "",
                },
            },
        );
        if (response.ok) {
            window.dispatchEvent(
                new CustomEvent("toast", {
                    detail: {
                        type: "success",
                        message: "Cache cleared successfully",
                    },
                }),
            );
            await window.refreshPredictions();
        } else {
            throw new Error("Failed to clear cache");
        }
    } catch (error) {
        window.dispatchEvent(
            new CustomEvent("toast", {
                detail: {
                    type: "error",
                    message: "Failed to clear cache: " + error.message,
                },
            }),
        );
    }
};

/**
 * Toggle/show the AI analysis drilldown panel.
 * Previously inline in training/predictions.blade.php
 */
window.showAIDetails = function () {
    const panel = document.getElementById("ai-analysis-panel");
    if (panel) {
        panel.classList.toggle("hidden");
        return;
    }

    const banner = document.getElementById("ai-advisor-banner");
    if (!banner) {
        return;
    }

    const advisorMessage =
        document.getElementById("ai-advisor-message")?.textContent?.trim() ||
        "No analysis available";
    const confidenceBadge = document.getElementById("ai-confidence-badge");
    const confidence = confidenceBadge
        ? confidenceBadge.textContent.trim()
        : "N/A";
    const recommendationText =
        document
            .getElementById("ai-recommendation-text")
            ?.textContent?.trim() || "";

    const breakdownEl = document.getElementById("calculation-breakdown");
    const baseGain =
        breakdownEl?.querySelector(".breakdown-base")?.textContent?.trim() ||
        "--";
    const growthRate =
        breakdownEl?.querySelector(".breakdown-growth")?.textContent?.trim() ||
        "--";
    const moodMod =
        breakdownEl?.querySelector(".breakdown-mood")?.textContent?.trim() ||
        "--";

    const drilldown = document.createElement("div");
    drilldown.id = "ai-analysis-panel";
    drilldown.className =
        "card rounded-xl p-6 mb-6 border border-primary-200 dark:border-primary-800 animate-fade-in";
    drilldown.setAttribute("role", "region");
    drilldown.setAttribute("aria-label", "AI Analysis Details");
    drilldown.innerHTML = `
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
                AI Analysis Drilldown
            </h3>
            <button onclick="document.getElementById('ai-analysis-panel').classList.add('hidden')"
                class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors"
                aria-label="Close analysis panel">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div class="p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Recommendation</p>
                <p class="text-sm text-gray-900 dark:text-white">${advisorMessage}</p>
            </div>
            <div class="p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Confidence</p>
                <p class="text-sm font-semibold text-primary-600 dark:text-primary-400">${confidence}</p>
            </div>
        </div>

        ${
            recommendationText
                ? `
            <div class="p-3 bg-primary-50 dark:bg-primary-900/20 rounded-lg mb-4">
                <p class="text-xs font-medium text-primary-600 dark:text-primary-400 uppercase tracking-wider mb-1">Summary</p>
                <p class="text-sm text-gray-900 dark:text-white">${recommendationText}</p>
            </div>`
                : ""
        }

        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Calculation Breakdown</p>
            <div class="grid grid-cols-3 gap-3">
                <div class="text-center p-2 bg-gray-50 dark:bg-gray-800/50 rounded">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Base Gain</p>
                    <p class="text-sm font-mono font-semibold text-gray-900 dark:text-white">${baseGain}</p>
                </div>
                <div class="text-center p-2 bg-gray-50 dark:bg-gray-800/50 rounded">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Growth Rate</p>
                    <p class="text-sm font-mono font-semibold text-gray-900 dark:text-white">${growthRate}</p>
                </div>
                <div class="text-center p-2 bg-gray-50 dark:bg-gray-800/50 rounded">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Mood Modifier</p>
                    <p class="text-sm font-mono font-semibold text-gray-900 dark:text-white">${moodMod}</p>
                </div>
            </div>
        </div>
    `;

    banner.insertAdjacentElement("afterend", drilldown);
};
