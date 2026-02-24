// Line chart component (Chart.js wrapper with computed summary stats)
export default function lineChart(
    data = [],
    labels = [],
    colors = ["#3B82F6"],
    animated = true,
    responsive = true,
) {
    const flatData = Array.isArray(data[0]) ? data[0] : data;

    return {
        chartInstance: null,
        chartId: null,
        lastDataPoint: flatData.length > 0 ? flatData[flatData.length - 1] : 0,
        averageValue:
            flatData.length > 0
                ? flatData.reduce((a, b) => a + b, 0) / flatData.length
                : 0,
        maxValue: flatData.length > 0 ? Math.max(...flatData) : 0,

        init() {
            this.chartId =
                "chart-" + Math.random().toString(36).substr(2, 9);
            this.$nextTick(() => {
                this.initChart(data, labels, colors, animated, responsive);
            });
        },

        initChart(chartData, chartLabels, chartColors, isAnimated, isResponsive) {
            const canvas = this.$el.querySelector("canvas");
            if (!canvas || !window.Chart) {
                return;
            }

            const ctx = canvas.getContext("2d");
            const datasets = Array.isArray(chartData[0])
                ? chartData.map((dataset, i) => ({
                      label: `Dataset ${i + 1}`,
                      data: dataset,
                      borderColor: chartColors[i] || chartColors[0],
                      backgroundColor: (chartColors[i] || chartColors[0]) + "20",
                      tension: 0.3,
                      fill: true,
                  }))
                : [
                      {
                          label: "Data",
                          data: chartData,
                          borderColor: chartColors[0],
                          backgroundColor: chartColors[0] + "20",
                          tension: 0.3,
                          fill: true,
                      },
                  ];

            this.chartInstance = new Chart(ctx, {
                type: "line",
                data: { labels: chartLabels, datasets },
                options: {
                    responsive: isResponsive,
                    maintainAspectRatio: false,
                    animation: isAnimated ? {} : false,
                },
            });
        },

        updateChart(newData) {
            if (this.chartInstance) {
                this.chartInstance.data = newData;
                this.chartInstance.update();
            }
        },

        destroy() {
            if (this.chartInstance) {
                this.chartInstance.destroy();
                this.chartInstance = null;
            }
        },
    };
}
