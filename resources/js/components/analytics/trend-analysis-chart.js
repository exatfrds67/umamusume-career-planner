import Chart from "chart.js/auto";

export default (config) => ({
    chart: null,
    loading: true,
    data: config.data || [],
    metric: config.metric,
    metricConfig: config.config,
    showConfidence: config.showConfidence,
    showPrediction: config.showPrediction,
    displayConfidence: config.showConfidence,
    displayPrediction: config.showPrediction,

    stats: {
        current: 0,
        average: 0,
        trendDirection: "stable",
        trendValue: 0,
        confidence: 95,
    },

    insights: [],

    init() {
        this.$nextTick(() => {
            this.calculateStats();
            this.generateInsights();
            this.createChart();
            this.loading = false;
        });

        window.addEventListener("theme-changed", () => {
            this.updateChartTheme();
        });
    },

    calculateStats() {
        if (!this.data || this.data.length === 0) return;

        const values = this.data.map((d) => d.value || 0);

        this.stats.current = Math.round(values[values.length - 1] * 10) / 10;
        this.stats.average =
            Math.round(
                (values.reduce((a, b) => a + b, 0) / values.length) * 10,
            ) / 10;

        if (values.length >= 2) {
            const n = values.length;
            const sumX = (n * (n - 1)) / 2;
            const sumY = values.reduce((a, b) => a + b, 0);
            const sumXY = values.reduce((sum, y, x) => sum + x * y, 0);
            const sumX2 = (n * (n - 1) * (2 * n - 1)) / 6;

            const slope = (n * sumXY - sumX * sumY) / (n * sumX2 - sumX * sumX);

            this.stats.trendValue = Math.round(slope * 10) / 10;
            this.stats.trendDirection =
                slope > 0.5 ? "up" : slope < -0.5 ? "down" : "stable";
        }
    },

    generateInsights() {
        this.insights = [];

        if (!this.data || this.data.length < 3) {
            this.insights.push("Need more data points for detailed analysis.");
            return;
        }

        if (this.stats.trendDirection === "up") {
            this.insights.push(
                `${this.metricConfig.label} is improving by approximately ${Math.abs(this.stats.trendValue)}${this.metricConfig.unit} per career.`,
            );
        } else if (this.stats.trendDirection === "down") {
            this.insights.push(
                `${this.metricConfig.label} is declining by approximately ${Math.abs(this.stats.trendValue)}${this.metricConfig.unit} per career.`,
            );
        } else {
            this.insights.push(
                `${this.metricConfig.label} has remained stable across recent careers.`,
            );
        }

        if (this.stats.current > this.stats.average) {
            const diff =
                Math.round((this.stats.current - this.stats.average) * 10) / 10;
            this.insights.push(
                `Current performance is ${diff}${this.metricConfig.unit} above your average.`,
            );
        } else if (this.stats.current < this.stats.average) {
            const diff =
                Math.round((this.stats.average - this.stats.current) * 10) / 10;
            this.insights.push(
                `Current performance is ${diff}${this.metricConfig.unit} below your average.`,
            );
        }

        const values = this.data.map((d) => d.value || 0);
        const variance = this.calculateVariance(values);
        if (variance < 25) {
            this.insights.push("Your performance has been very consistent.");
        } else if (variance > 100) {
            this.insights.push(
                "Performance varies significantly between careers. Consider identifying what factors lead to better results.",
            );
        }
    },

    calculateVariance(values) {
        if (values.length < 2) return 0;
        const mean = values.reduce((a, b) => a + b, 0) / values.length;
        return (
            values.reduce((sum, val) => sum + Math.pow(val - mean, 2), 0) /
            values.length
        );
    },

    createChart() {
        const ctx = document.getElementById(config.chartId);
        if (!ctx) return;

        const isDark = document.documentElement.classList.contains("dark");
        const gridColor = isDark
            ? "rgba(255, 255, 255, 0.1)"
            : "rgba(0, 0, 0, 0.1)";
        const textColor = isDark ? "#e5e7eb" : "#374151";

        const labels = this.data.map((d, i) => d.label || `Career ${i + 1}`);
        const mainData = this.data.map((d) => d.value || 0);
        const upperBound = this.data.map((d) => d.upper || d.value || 0);
        const lowerBound = this.data.map((d) => d.lower || d.value || 0);

        let predictionLabels = [];
        let predictionData = [];

        if (this.showPrediction && this.data.length >= 3) {
            const lastValue = mainData[mainData.length - 1];
            const slope = this.stats.trendValue;

            for (let i = 1; i <= 3; i++) {
                predictionLabels.push(`Predicted ${i}`);
                predictionData.push(
                    Math.max(
                        0,
                        Math.min(this.metricConfig.max, lastValue + slope * i),
                    ),
                );
            }
        }

        const datasets = [
            {
                label: this.metricConfig.label,
                data: mainData,
                borderColor: this.metricConfig.color,
                backgroundColor: "transparent",
                borderWidth: 3,
                tension: 0.3,
                pointRadius: 5,
                pointHoverRadius: 8,
                pointBackgroundColor: this.metricConfig.color,
                pointBorderColor: isDark ? "#1f2937" : "#ffffff",
                pointBorderWidth: 2,
            },
        ];

        if (this.displayConfidence) {
            datasets.unshift({
                label: "Upper Bound",
                data: upperBound,
                borderColor: "transparent",
                backgroundColor: this.metricConfig.color + "20",
                fill: "+1",
                tension: 0.3,
                pointRadius: 0,
            });

            datasets.push({
                label: "Lower Bound",
                data: lowerBound,
                borderColor: "transparent",
                backgroundColor: "transparent",
                fill: false,
                tension: 0.3,
                pointRadius: 0,
            });
        }

        if (this.displayPrediction && predictionData.length > 0) {
            const extendedMain = [
                ...mainData,
                ...Array(predictionData.length).fill(null),
            ];
            datasets[0].data = extendedMain;

            const predictionLine = [
                ...Array(mainData.length - 1).fill(null),
                mainData[mainData.length - 1],
                ...predictionData,
            ];
            datasets.push({
                label: "Prediction",
                data: predictionLine,
                borderColor: this.metricConfig.color,
                backgroundColor: "transparent",
                borderWidth: 2,
                borderDash: [5, 5],
                tension: 0.3,
                pointRadius: 4,
                pointStyle: "triangle",
                pointBackgroundColor: this.metricConfig.color + "80",
            });
        }

        const allLabels = [...labels, ...predictionLabels];

        this.chart = new Chart(ctx, {
            type: "line",
            data: {
                labels: allLabels,
                datasets,
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: "index",
                    intersect: false,
                },
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        backgroundColor: isDark ? "#1f2937" : "#ffffff",
                        titleColor: isDark ? "#ffffff" : "#111827",
                        bodyColor: isDark ? "#e5e7eb" : "#374151",
                        borderColor: isDark ? "#374151" : "#e5e7eb",
                        borderWidth: 1,
                        padding: 12,
                        displayColors: true,
                        filter: (item) =>
                            item.dataset.label !== "Upper Bound" &&
                            item.dataset.label !== "Lower Bound",
                        callbacks: {
                            label: (context) => {
                                if (context.parsed.y === null) return null;
                                return `${context.dataset.label}: ${context.parsed.y}${this.metricConfig.unit}`;
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        grid: {
                            color: gridColor,
                        },
                        ticks: {
                            color: textColor,
                        },
                    },
                    y: {
                        beginAtZero: true,
                        max: this.metricConfig.max,
                        grid: {
                            color: gridColor,
                        },
                        ticks: {
                            color: textColor,
                            callback: (value) => value + this.metricConfig.unit,
                        },
                    },
                },
            },
        });
    },

    updateChart() {
        if (this.chart) {
            this.chart.destroy();
            this.createChart();
        }
    },

    updateChartTheme() {
        if (this.chart) {
            this.chart.destroy();
            this.createChart();
        }
    },

    destroy() {
        if (this.chart) {
            this.chart.destroy();
        }
    },
});
