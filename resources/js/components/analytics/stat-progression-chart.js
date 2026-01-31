/**
 * Stat Progression Chart Component
 *
 * Displays stat progression over time with interactive timeline using Chart.js.
 * Supports multiple instances on the same page.
 *
 * Requirements: 15.4, 25.3 (Task 5.2.3)
 * WCAG 2.2 AA Compliant
 */

document.addEventListener("alpine:init", () => {
    Alpine.data("statProgressionChart", (config) => ({
        chart: null,
        loading: true,
        data: config.data || [],
        stats: config.stats || [],
        statColors: config.statColors || {},
        visibleStats: [...(config.stats || [])],
        showLegend: config.showLegend,
        interactive: config.interactive,
        phases: config.phases,
        phaseMarkers: config.phaseMarkers || [],

        init() {
            this.$nextTick(() => {
                this.createChart();
                this.loading = false;
            });

            // Watch for theme changes
            window.addEventListener("theme-changed", () => {
                this.updateChartTheme();
            });
        },

        createChart() {
            const ctx = document.getElementById(config.chartId);
            if (!ctx) return;

            const isDark = document.documentElement.classList.contains("dark");
            const gridColor = isDark
                ? "rgba(255, 255, 255, 0.1)"
                : "rgba(0, 0, 0, 0.1)";
            const textColor = isDark ? "#e5e7eb" : "#374151";

            // Prepare datasets
            const datasets = this.stats.map((stat) => ({
                label: stat.charAt(0).toUpperCase() + stat.slice(1),
                data: this.data.map((d) => d[stat] || 0),
                borderColor: this.statColors[stat]?.line || "#6b7280",
                backgroundColor:
                    this.statColors[stat]?.fill || "rgba(107, 114, 128, 0.1)",
                fill: true,
                tension: 0.3,
                pointRadius: 3,
                pointHoverRadius: 6,
                hidden: !this.visibleStats.includes(stat),
            }));

            // Prepare labels (turns)
            const labels = this.data.map(
                (d) => `Turn ${d.turn || d.turn_number || 0}`,
            );

            // Phase annotations
            const annotations = this.phases
                ? this.createPhaseAnnotations(isDark)
                : {};

            this.chart = new Chart(ctx, {
                type: "line",
                data: {
                    labels,
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
                            display: this.showLegend,
                            position: "bottom",
                            labels: {
                                color: textColor,
                                usePointStyle: true,
                                padding: 20,
                            },
                        },
                        tooltip: {
                            enabled: this.interactive,
                            backgroundColor: isDark ? "#1f2937" : "#ffffff",
                            titleColor: isDark ? "#ffffff" : "#111827",
                            bodyColor: isDark ? "#e5e7eb" : "#374151",
                            borderColor: isDark ? "#374151" : "#e5e7eb",
                            borderWidth: 1,
                            padding: 12,
                            displayColors: true,
                            callbacks: {
                                title: (items) => items[0]?.label || "",
                                label: (context) => {
                                    return `${context.dataset.label}: ${context.parsed.y}`;
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
                            max: 1200,
                            grid: {
                                color: gridColor,
                            },
                            ticks: {
                                color: textColor,
                                stepSize: 200,
                            },
                        },
                    },
                },
            });
        },

        createPhaseAnnotations(isDark) {
            return {
                annotations: {
                    juniorPhase: {
                        type: "box",
                        xMin: 0,
                        xMax: 23,
                        backgroundColor: "rgba(59, 130, 246, 0.05)",
                        borderWidth: 0,
                    },
                    classicPhase: {
                        type: "box",
                        xMin: 24,
                        xMax: 47,
                        backgroundColor: "rgba(168, 85, 247, 0.05)",
                        borderWidth: 0,
                    },
                    seniorPhase: {
                        type: "box",
                        xMin: 48,
                        xMax: 71,
                        backgroundColor: "rgba(34, 197, 94, 0.05)",
                        borderWidth: 0,
                    },
                },
            };
        },

        toggleStat(stat) {
            const index = this.visibleStats.indexOf(stat);
            if (index > -1) {
                this.visibleStats.splice(index, 1);
            } else {
                this.visibleStats.push(stat);
            }

            // Update chart visibility
            if (this.chart) {
                const datasetIndex = this.stats.indexOf(stat);
                if (datasetIndex > -1) {
                    this.chart.setDatasetVisibility(
                        datasetIndex,
                        this.visibleStats.includes(stat),
                    );
                    this.chart.update();
                }
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
    }));
});
