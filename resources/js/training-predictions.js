/**
 * Training Predictions Module
 *
 * Handles the frontend logic for training predictions with:
 * - Agent visualization and workflow display
 * - Stat gains and energy cost predictions
 * - Spirit Burst indicators for Unity Cup
 * - Recommendation rankings with reasoning
 * - Performance metrics and caching indicators
 * - Error handling and retry logic
 */

// Training type information
const TRAINING_TYPES = {
    speed: {
        name: "Speed Training",
        icon: "⚡",
        color: "blue",
        description: "Increases top speed capability",
        priority: 5,
    },
    stamina: {
        name: "Stamina Training",
        icon: "💪",
        color: "green",
        description: "Extends duration at top speed",
        priority: 4,
    },
    power: {
        name: "Power Training",
        icon: "🔥",
        color: "red",
        description: "Improves acceleration rate",
        priority: 3,
    },
    guts: {
        name: "Guts Training",
        icon: "💎",
        color: "purple",
        description: "Enhances final phase performance",
        priority: 1,
    },
    wit: {
        name: "Wit Training",
        icon: "🧠",
        color: "yellow",
        description: "Boosts skill activation and positioning",
        priority: 2,
    },
    rest: {
        name: "Rest",
        icon: "😴",
        color: "gray",
        description: "Recovers energy and reduces failure risk",
        priority: 0,
    },
};

// Performance tracking
let performanceMetrics = {
    apiCalls: 0,
    cacheHits: 0,
    totalProcessingTime: 0,
    averageProcessingTime: 0,
};

/**
 * Initialize training predictions interface
 */
export async function initTrainingPredictions(
    characterId,
    scenarioType,
    apiUrl,
) {
    const app = document.getElementById("training-predictions-app");
    if (!app) return;

    try {
        showLoadingState(app);

        // Fetch batch predictions with retry logic
        const predictions = await fetchPredictionsWithRetry(
            characterId,
            apiUrl,
            3,
        );

        // Update performance metrics
        updatePerformanceMetrics(predictions);

        // Render the interface
        renderPredictions(app, predictions, scenarioType);

        // Initialize event listeners
        initializeEventListeners(characterId, apiUrl);
    } catch (error) {
        console.error("Error initializing training predictions:", error);
        renderError(app, error.message);
    }
}

/**
 * Show loading state
 */
function showLoadingState(container) {
    container.innerHTML = `
        <div class="glass-card rounded-xl p-12 text-center">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-primary-500"></div>
            <p class="mt-4 text-gray-700 dark:text-gray-300 transition-colors duration-300">
                Loading training predictions...
            </p>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Analyzing character state and support cards
            </p>
        </div>
    `;
}

/**
 * Fetch predictions with retry logic
 */
async function fetchPredictionsWithRetry(characterId, apiUrl, maxRetries = 3) {
    let lastError;

    for (let attempt = 1; attempt <= maxRetries; attempt++) {
        try {
            return await fetchPredictions(characterId, apiUrl);
        } catch (error) {
            lastError = error;
            console.warn(
                `Prediction fetch attempt ${attempt} failed:`,
                error.message,
            );

            if (attempt < maxRetries) {
                // Exponential backoff
                await new Promise((resolve) =>
                    setTimeout(resolve, Math.pow(2, attempt) * 1000),
                );
            }
        }
    }

    throw new Error(
        `Failed to fetch predictions after ${maxRetries} attempts: ${lastError.message}`,
    );
}

/**
 * Fetch predictions from API
 */
async function fetchPredictions(characterId, apiUrl) {
    const response = await fetch(apiUrl, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                .content,
        },
        body: JSON.stringify({
            character_id: parseInt(characterId),
            training_types: [
                "speed",
                "stamina",
                "power",
                "guts",
                "wit",
                "rest",
            ],
            include_recommendations: true,
            use_mcp: true,
        }),
    });

    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();
    return data.data;
}

/**
 * Render predictions interface
 */
function renderPredictions(container, predictions, scenarioType) {
    // Sort by recommendation rank
    const sorted = predictions.sort((a, b) => {
        const rankA = a.recommendation?.rank || 999;
        const rankB = b.recommendation?.rank || 999;
        return rankA - rankB;
    });

    container.innerHTML = `
        <div class="space-y-6">
            ${renderHeader(scenarioType)}
            ${renderTrainingGrid(sorted, scenarioType)}
            ${renderMetrics(predictions)}
        </div>
    `;
}

/**
 * Render header section
 */
function renderHeader(scenarioType) {
    return `
        <div class="bg-linear-to-r from-primary-500 to-primary-600 rounded-lg shadow-lg p-6 text-white">
            <h2 class="text-2xl font-bold mb-2">AI-Powered Training Recommendations</h2>
            <p class="text-primary-100">
                Based on multi-agent analysis and ${
                    scenarioType === "unity_cup"
                        ? "Unity Cup team synergy"
                        : "URA Finale optimization"
                }
            </p>
        </div>
    `;
}

/**
 * Render training options grid
 */
function renderTrainingGrid(predictions, scenarioType) {
    return `
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            ${predictions
                .map((p) => renderTrainingCard(p, scenarioType))
                .join("")}
        </div>
    `;
}

/**
 * Render individual training card
 */
function renderTrainingCard(prediction, scenarioType) {
    const isRecommended = prediction.recommendation?.is_recommended || false;
    const rank = prediction.recommendation?.rank;
    const info = TRAINING_TYPES[prediction.training_type] || {};

    return `
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border-2 ${
            isRecommended
                ? "border-primary-500"
                : "border-gray-200 dark:border-gray-700"
        } p-6 relative">
            ${isRecommended ? renderRecommendedBadge(rank) : ""}
            ${renderCardHeader(info)}
            ${renderStatGains(prediction.stat_gains)}
            ${renderCostsAndRisks(prediction)}
            ${renderBreakdown(prediction.breakdown)}
            ${
                scenarioType === "unity_cup"
                    ? renderUnityCupInfo(prediction.scenario_specific)
                    : ""
            }
            ${
                prediction.mcp_optimization
                    ? renderMCPInfo(prediction.mcp_optimization)
                    : ""
            }
            ${
                prediction.recommendation?.reason
                    ? renderReasoning(prediction.recommendation.reason)
                    : ""
            }
        </div>
    `;
}

/**
 * Render recommended badge
 */
function renderRecommendedBadge(rank) {
    return `
        <div class="absolute top-4 right-4">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                Recommended #${rank}
            </span>
        </div>
    `;
}

/**
 * Render card header
 */
function renderCardHeader(info) {
    return `
        <div class="mb-4">
            <div class="flex items-center gap-3 mb-2">
                <span class="text-3xl">${info.icon || "❓"}</span>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                    ${info.name || "Unknown"}
                </h3>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                ${info.description || ""}
            </p>
        </div>
    `;
}

/**
 * Render stat gains
 */
function renderStatGains(statGains) {
    if (!statGains) return "";

    return `
        <div class="mb-4">
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Predicted Stat Gains</h4>
            <div class="grid grid-cols-2 gap-2">
                ${Object.entries(statGains)
                    .map(
                        ([stat, gain]) => `
                    <div class="flex justify-between items-center px-3 py-2 bg-gray-50 dark:bg-gray-700 rounded">
                        <span class="text-sm text-gray-600 dark:text-gray-400 capitalize">${stat}</span>
                        <span class="text-sm font-bold ${
                            gain > 0
                                ? "text-green-600 dark:text-green-400"
                                : "text-gray-400"
                        }">
                            ${gain > 0 ? "+" : ""}${gain}
                        </span>
                    </div>
                `,
                    )
                    .join("")}
            </div>
        </div>
    `;
}

/**
 * Render costs and risks
 */
function renderCostsAndRisks(prediction) {
    return `
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <div class="text-xs text-gray-600 dark:text-gray-400 mb-1">Energy Cost</div>
                <div class="text-lg font-bold text-gray-900 dark:text-white">
                    ${prediction.energy_cost || 0}%
                </div>
            </div>
            <div>
                <div class="text-xs text-gray-600 dark:text-gray-400 mb-1">Failure Risk</div>
                <div class="text-lg font-bold ${
                    prediction.failure_risk > 20
                        ? "text-red-600"
                        : "text-green-600"
                }">
                    ${(prediction.failure_risk || 0).toFixed(1)}%
                </div>
            </div>
        </div>
    `;
}

/**
 * Render calculation breakdown
 */
function renderBreakdown(breakdown) {
    if (!breakdown) return "";

    return `
        <div class="mb-4">
            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Calculation Breakdown</h4>
            <div class="space-y-1 text-xs">
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Support Card Bonus</span>
                    <span class="font-medium text-gray-900 dark:text-white">+${(
                        breakdown.support_card_bonus || 0
                    ).toFixed(1)}%</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Friendship Multiplier</span>
                    <span class="font-medium text-gray-900 dark:text-white">×${(
                        breakdown.friendship_multiplier || 1
                    ).toFixed(2)}</span>
                </div>
                ${
                    breakdown.facility_bonus
                        ? `
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Facility Bonus</span>
                        <span class="font-medium text-gray-900 dark:text-white">+${(
                            breakdown.facility_bonus || 0
                        ).toFixed(1)}%</span>
                    </div>
                `
                        : ""
                }
                <div class="flex justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Growth Rate Bonus</span>
                    <span class="font-medium text-gray-900 dark:text-white">+${(
                        breakdown.growth_rate_bonus || 0
                    ).toFixed(1)}%</span>
                </div>
                <div class="flex justify-between pt-1 border-t border-gray-200 dark:border-gray-600">
                    <span class="text-gray-700 dark:text-gray-300 font-semibold">Total Multiplier</span>
                    <span class="font-bold text-gray-900 dark:text-white">×${(
                        breakdown.total_multiplier || 1
                    ).toFixed(2)}</span>
                </div>
            </div>
        </div>
    `;
}

/**
 * Render Unity Cup specific information
 */
function renderUnityCupInfo(scenarioData) {
    if (!scenarioData) return "";

    return `
        <div class="mb-4 p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg border border-purple-200 dark:border-purple-800">
            <h4 class="text-sm font-semibold text-purple-700 dark:text-purple-300 mb-2">
                🏆 Unity Cup Mechanics
            </h4>
            <div class="space-y-2 text-xs">
                ${
                    scenarioData.spirit_burst_progress !== undefined
                        ? `
                    <div class="flex justify-between items-center">
                        <span class="text-purple-600 dark:text-purple-400">Spirit Burst Progress</span>
                        <div class="flex items-center gap-1">
                            ${[1, 2, 3, 4]
                                .map(
                                    (i) => `
                                <span class="${
                                    i <=
                                    (scenarioData.spirit_burst_progress || 0)
                                        ? "text-yellow-500"
                                        : "text-gray-300"
                                }">
                                    ${
                                        i <=
                                        (scenarioData.spirit_burst_progress ||
                                            0)
                                            ? "🔥"
                                            : "○"
                                    }
                                </span>
                            `,
                                )
                                .join("")}
                        </div>
                    </div>
                `
                        : ""
                }
                ${
                    scenarioData.team_synergy_bonus
                        ? `
                    <div class="flex justify-between">
                        <span class="text-purple-600 dark:text-purple-400">Team Synergy Bonus</span>
                        <span class="font-medium text-purple-900 dark:text-purple-100">+${scenarioData.team_synergy_bonus}%</span>
                    </div>
                `
                        : ""
                }
                ${
                    scenarioData.teammates_present
                        ? `
                    <div class="flex justify-between">
                        <span class="text-purple-600 dark:text-purple-400">Teammates Present</span>
                        <span class="font-medium text-purple-900 dark:text-purple-100">${scenarioData.teammates_present}</span>
                    </div>
                `
                        : ""
                }
            </div>
        </div>
    `;
}

/**
 * Render MCP optimization information
 */
function renderMCPInfo(mcpData) {
    return `
        <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
            <h4 class="text-sm font-semibold text-blue-700 dark:text-blue-300 mb-2">
                🤖 Agent Analysis
            </h4>
            <div class="space-y-2 text-xs">
                ${
                    mcpData.agent_workflow
                        ? `
                    <div>
                        <span class="text-blue-600 dark:text-blue-400 font-medium">Workflow:</span>
                        <span class="text-blue-900 dark:text-blue-100 ml-1">${mcpData.agent_workflow}</span>
                    </div>
                `
                        : ""
                }
                ${
                    mcpData.confidence_score !== undefined
                        ? `
                    <div class="flex justify-between">
                        <span class="text-blue-600 dark:text-blue-400">Confidence Score</span>
                        <span class="font-medium text-blue-900 dark:text-blue-100">${(
                            mcpData.confidence_score * 100
                        ).toFixed(0)}%</span>
                    </div>
                `
                        : ""
                }
                ${
                    mcpData.agents_consulted
                        ? `
                    <div>
                        <span class="text-blue-600 dark:text-blue-400 font-medium">Agents Consulted:</span>
                        <div class="mt-1 flex flex-wrap gap-1">
                            ${mcpData.agents_consulted
                                .map(
                                    (agent) => `
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-blue-100 dark:bg-blue-800 text-blue-800 dark:text-blue-200">
                                    ${agent}
                                </span>
                            `,
                                )
                                .join("")}
                        </div>
                    </div>
                `
                        : ""
                }
            </div>
        </div>
    `;
}

/**
 * Render AI reasoning
 */
function renderReasoning(reason) {
    return `
        <div class="mt-4 p-3 bg-primary-50 dark:bg-primary-900/20 rounded-lg border border-primary-200 dark:border-primary-800">
            <div class="text-xs font-semibold text-primary-700 dark:text-primary-300 mb-1">AI Reasoning</div>
            <div class="text-sm text-primary-900 dark:text-primary-100">
                ${reason}
            </div>
        </div>
    `;
}

/**
 * Render performance metrics
 */
function renderMetrics(predictions) {
    const avgTime =
        predictions.reduce((sum, p) => sum + (p.processing_time_ms || 0), 0) /
        predictions.length;
    const cachedCount = predictions.filter((p) => p.cached).length;

    return `
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                Performance Metrics
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                        ${avgTime.toFixed(0)}ms
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        Avg Processing Time
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                        ${cachedCount}/${predictions.length}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        Cached Predictions
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                        ${predictions.length}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        Training Options
                    </div>
                </div>
            </div>
        </div>
    `;
}

/**
 * Render error message
 */
function renderError(container, message) {
    container.innerHTML = `
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6 text-center">
            <div class="text-red-600 dark:text-red-400 mb-2">
                <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
            </div>
            <h3 class="text-lg font-semibold text-red-900 dark:text-red-100 mb-2">
                Error Loading Predictions
            </h3>
            <p class="text-red-700 dark:text-red-300">
                ${message}
            </p>
        </div>
    `;
}

// Auto-initialize if app element exists
document.addEventListener("DOMContentLoaded", function () {
    const app = document.getElementById("training-predictions-app");
    if (!app) return;

    const characterId = app.dataset.characterId;
    const scenarioType = app.dataset.scenarioType;
    const apiUrl = app.dataset.apiUrl;

    if (characterId && apiUrl) {
        initTrainingPredictions(characterId, scenarioType, apiUrl);
    }
});
