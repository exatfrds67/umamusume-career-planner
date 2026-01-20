/**
 * Analytics Charts Module
 *
 * Provides chart utilities and helper functions for visualization components.
 * Used by stat progression, performance dashboard, and trend analysis components.
 *
 * Requirements: 15.4, 25.3 (Task 5.2.3)
 */

/**
 * Chart color palette for consistent styling
 */
export const ChartColors = {
    stats: {
        speed: { line: "#3b82f6", fill: "rgba(59, 130, 246, 0.1)" },
        stamina: { line: "#f97316", fill: "rgba(249, 115, 22, 0.1)" },
        power: { line: "#ef4444", fill: "rgba(239, 68, 68, 0.1)" },
        guts: { line: "#ec4899", fill: "rgba(236, 72, 153, 0.1)" },
        wit: { line: "#22c55e", fill: "rgba(34, 197, 94, 0.1)" },
    },
    grades: {
        SS: "#fbbf24",
        S: "#a855f7",
        A: "#3b82f6",
        B: "#22c55e",
        C: "#14b8a6",
        D: "#6b7280",
        E: "#f97316",
        F: "#ef4444",
        G: "#525252",
    },
    status: {
        success: "#22c55e",
        warning: "#f59e0b",
        error: "#ef4444",
        info: "#3b82f6",
    },
};

/**
 * Get theme-aware colors for charts
 * @returns {Object} Theme colors
 */
export function getThemeColors() {
    const isDark = document.documentElement.classList.contains("dark");

    return {
        grid: isDark ? "rgba(255, 255, 255, 0.1)" : "rgba(0, 0, 0, 0.1)",
        text: isDark ? "#e5e7eb" : "#374151",
        background: isDark ? "#1f2937" : "#ffffff",
        border: isDark ? "#374151" : "#e5e7eb",
    };
}

/**
 * Create default chart options with theme support
 * @param {Object} options - Additional options to merge
 * @returns {Object} Chart.js options object
 */
export function createChartOptions(options = {}) {
    const theme = getThemeColors();

    const defaults = {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: "index",
            intersect: false,
        },
        plugins: {
            legend: {
                display: true,
                position: "bottom",
                labels: {
                    color: theme.text,
                    usePointStyle: true,
                    padding: 20,
                },
            },
            tooltip: {
                backgroundColor: theme.background,
                titleColor: theme.text,
                bodyColor: theme.text,
                borderColor: theme.border,
                borderWidth: 1,
                padding: 12,
                displayColors: true,
            },
        },
        scales: {
            x: {
                grid: { color: theme.grid },
                ticks: { color: theme.text },
            },
            y: {
                grid: { color: theme.grid },
                ticks: { color: theme.text },
            },
        },
    };

    return deepMerge(defaults, options);
}

/**
 * Calculate linear regression for trend analysis
 * @param {Array<number>} values - Array of numeric values
 * @returns {Object} Regression results with slope, intercept, and r-squared
 */
export function calculateLinearRegression(values) {
    if (!values || values.length < 2) {
        return { slope: 0, intercept: 0, rSquared: 0 };
    }

    const n = values.length;
    const xValues = Array.from({ length: n }, (_, i) => i);

    const sumX = xValues.reduce((a, b) => a + b, 0);
    const sumY = values.reduce((a, b) => a + b, 0);
    const sumXY = xValues.reduce((sum, x, i) => sum + x * values[i], 0);
    const sumX2 = xValues.reduce((sum, x) => sum + x * x, 0);
    const sumY2 = values.reduce((sum, y) => sum + y * y, 0);

    const slope = (n * sumXY - sumX * sumY) / (n * sumX2 - sumX * sumX);
    const intercept = (sumY - slope * sumX) / n;

    // Calculate R-squared
    const yMean = sumY / n;
    const ssTotal = values.reduce((sum, y) => sum + Math.pow(y - yMean, 2), 0);
    const ssResidual = values.reduce((sum, y, i) => {
        const predicted = slope * i + intercept;
        return sum + Math.pow(y - predicted, 2);
    }, 0);

    const rSquared = ssTotal > 0 ? 1 - ssResidual / ssTotal : 0;

    return { slope, intercept, rSquared };
}

/**
 * Calculate confidence interval for a dataset
 * @param {Array<number>} values - Array of numeric values
 * @param {number} confidence - Confidence level (default: 0.95)
 * @returns {Object} Confidence interval bounds
 */
export function calculateConfidenceInterval(values, confidence = 0.95) {
    if (!values || values.length < 2) {
        return { lower: 0, upper: 0, margin: 0 };
    }

    const n = values.length;
    const mean = values.reduce((a, b) => a + b, 0) / n;
    const variance =
        values.reduce((sum, val) => sum + Math.pow(val - mean, 2), 0) / (n - 1);
    const stdDev = Math.sqrt(variance);
    const stdError = stdDev / Math.sqrt(n);

    // Z-score for 95% confidence
    const zScore =
        confidence === 0.95 ? 1.96 : confidence === 0.99 ? 2.576 : 1.645;
    const margin = zScore * stdError;

    return {
        lower: mean - margin,
        upper: mean + margin,
        margin,
        mean,
        stdDev,
    };
}

/**
 * Generate prediction data points based on trend
 * @param {Array<number>} values - Historical values
 * @param {number} count - Number of predictions to generate
 * @param {number} max - Maximum allowed value
 * @returns {Array<Object>} Prediction data points
 */
export function generatePredictions(values, count = 3, max = 100) {
    if (!values || values.length < 3) {
        return [];
    }

    const { slope, intercept } = calculateLinearRegression(values);
    const lastIndex = values.length - 1;
    const predictions = [];

    for (let i = 1; i <= count; i++) {
        const predictedValue = slope * (lastIndex + i) + intercept;
        const boundedValue = Math.max(0, Math.min(max, predictedValue));

        // Calculate confidence bounds (wider for further predictions)
        const ci = calculateConfidenceInterval(values);
        const uncertaintyFactor = 1 + i * 0.2; // Increase uncertainty for further predictions

        predictions.push({
            index: lastIndex + i,
            value: Math.round(boundedValue * 10) / 10,
            lower: Math.max(
                0,
                Math.round(
                    (boundedValue - ci.margin * uncertaintyFactor) * 10,
                ) / 10,
            ),
            upper: Math.min(
                max,
                Math.round(
                    (boundedValue + ci.margin * uncertaintyFactor) * 10,
                ) / 10,
            ),
        });
    }

    return predictions;
}

/**
 * Format number for display
 * @param {number} value - Value to format
 * @param {number} decimals - Number of decimal places
 * @returns {string} Formatted number
 */
export function formatNumber(value, decimals = 0) {
    if (value == null || isNaN(value)) return "0";
    return new Intl.NumberFormat("en-US", {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    }).format(value);
}

/**
 * Format percentage for display
 * @param {number} value - Value to format
 * @param {number} decimals - Number of decimal places
 * @returns {string} Formatted percentage
 */
export function formatPercentage(value, decimals = 1) {
    if (value == null || isNaN(value)) return "0%";
    return formatNumber(value, decimals) + "%";
}

/**
 * Get color based on value threshold
 * @param {number} value - Value to evaluate
 * @param {Object} thresholds - Threshold configuration
 * @returns {string} Color class or hex value
 */
export function getValueColor(value, thresholds = { good: 70, warning: 50 }) {
    if (value >= thresholds.good) return ChartColors.status.success;
    if (value >= thresholds.warning) return ChartColors.status.warning;
    return ChartColors.status.error;
}

/**
 * Deep merge two objects
 * @param {Object} target - Target object
 * @param {Object} source - Source object
 * @returns {Object} Merged object
 */
function deepMerge(target, source) {
    const output = { ...target };

    if (isObject(target) && isObject(source)) {
        Object.keys(source).forEach((key) => {
            if (isObject(source[key])) {
                if (!(key in target)) {
                    Object.assign(output, { [key]: source[key] });
                } else {
                    output[key] = deepMerge(target[key], source[key]);
                }
            } else {
                Object.assign(output, { [key]: source[key] });
            }
        });
    }

    return output;
}

/**
 * Check if value is a plain object
 * @param {*} item - Value to check
 * @returns {boolean} True if plain object
 */
function isObject(item) {
    return item && typeof item === "object" && !Array.isArray(item);
}

/**
 * Export chart as image
 * @param {Chart} chart - Chart.js instance
 * @param {string} filename - Filename for download
 * @param {string} type - Image type (png, jpeg)
 */
export function exportChartAsImage(chart, filename = "chart", type = "png") {
    if (!chart) return;

    const link = document.createElement("a");
    link.download = `${filename}.${type}`;
    link.href = chart.toBase64Image(`image/${type}`, 1);
    link.click();
}

/**
 * Create accessible data table from chart data
 * @param {Object} chartData - Chart.js data object
 * @returns {HTMLTableElement} Accessible table element
 */
export function createAccessibleTable(chartData) {
    const table = document.createElement("table");
    table.className = "sr-only";

    const caption = document.createElement("caption");
    caption.textContent = "Chart data table";
    table.appendChild(caption);

    const thead = document.createElement("thead");
    const headerRow = document.createElement("tr");

    // Add label column header
    const labelHeader = document.createElement("th");
    labelHeader.textContent = "Label";
    headerRow.appendChild(labelHeader);

    // Add dataset headers
    chartData.datasets.forEach((dataset) => {
        const th = document.createElement("th");
        th.textContent = dataset.label;
        headerRow.appendChild(th);
    });

    thead.appendChild(headerRow);
    table.appendChild(thead);

    const tbody = document.createElement("tbody");

    chartData.labels.forEach((label, index) => {
        const row = document.createElement("tr");

        const labelCell = document.createElement("td");
        labelCell.textContent = label;
        row.appendChild(labelCell);

        chartData.datasets.forEach((dataset) => {
            const cell = document.createElement("td");
            cell.textContent = dataset.data[index] ?? "N/A";
            row.appendChild(cell);
        });

        tbody.appendChild(row);
    });

    table.appendChild(tbody);

    return table;
}

// Export default object for convenience
export default {
    ChartColors,
    getThemeColors,
    createChartOptions,
    calculateLinearRegression,
    calculateConfidenceInterval,
    generatePredictions,
    formatNumber,
    formatPercentage,
    getValueColor,
    exportChartAsImage,
    createAccessibleTable,
};
