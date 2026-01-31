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

        let content = sc.name || sc.card_name || "Card";

        // Add friendship indicator (bond >= 80%)
        if (sc.bond_level >= 80 || sc.friendship_active) {
            content = `💛 ${content}`;
        }

        // Add guaranteed hint indicator
        if (sc.has_guaranteed_hint || sc.has_red_exclamation) {
            content = `🔴 ${content}`;
        }

        badge.textContent = content;
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

        const icon = hint.is_guaranteed ? "✨" : "💡";
        const probability = hint.is_guaranteed
            ? "Guaranteed"
            : `${hint.probability}%`;

        row.innerHTML = `
            <span>${icon}</span>
            <span class="font-medium text-gray-700 dark:text-gray-300">${hint.skill_name}</span>
            <span class="text-gray-500">(${probability})</span>
            ${hint.is_guaranteed ? '<span class="text-red-500">🔴</span>' : ""}
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
    const filledStars = "★".repeat(stars);
    const emptyStars = "☆".repeat(5 - stars);

    starsEl.textContent = filledStars + emptyStars;
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
