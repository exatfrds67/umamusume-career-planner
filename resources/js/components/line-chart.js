// Line chart component (Chart.js wrapper with unique ID handling)
export default function lineChart() {
    return {
        chartInstance: null,
        chartId: null,

        init() {
            this.chartId = "chart-" + Math.random().toString(36).substr(2, 9);
            this.$nextTick(() => {
                this.initChart();
            });
        },

        initChart() {
            const canvas = this.$el.querySelector("canvas");
            if (!canvas) return;

            const ctx = canvas.getContext("2d");
            const data = this.getChartData();
            const options = this.getChartOptions();

            if (window.Chart) {
                this.chartInstance = new Chart(ctx, {
                    type: "line",
                    data: data,
                    options: options,
                });
            }
        },

        getChartData() {
            // Override this method to provide chart data
            return {
                labels: [],
                datasets: [],
            };
        },

        getChartOptions() {
            // Override this method to provide chart options
            return {
                responsive: true,
                maintainAspectRatio: false,
            };
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
