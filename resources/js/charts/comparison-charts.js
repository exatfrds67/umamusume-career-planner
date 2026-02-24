import { ChartColors, getThemeColors, createChartOptions, exportChartAsImage } from '../analytics-charts.js';

const STAT_COLORS = {
    speed: '#3B82F6',
    stamina: '#F97316',
    power: '#EF4444',
    guts: '#EC4899',
    wit: '#22C55E',
};

/**
 * Create a parallel coordinates chart for career comparison.
 *
 * @param {HTMLCanvasElement} canvas
 * @param {Object} data - { axes: string[], series: Array<{career_id, career_name, values, color}> }
 * @returns {Chart}
 */
export function createParallelCoordinatesChart(canvas, data) {
    const themeColors = getThemeColors();
    const datasets = data.series.map((career) => ({
        label: career.career_name,
        data: data.axes.map((axis) => career.values[axis] || 0),
        borderColor: career.color,
        backgroundColor: career.color + '33',
        borderWidth: 2,
        pointRadius: 5,
        pointHoverRadius: 8,
        fill: false,
        tension: 0.1,
    }));

    const options = createChartOptions({
        plugins: {
            title: {
                display: true,
                text: 'Career Stat Comparison',
            },
            tooltip: {
                mode: 'index',
                intersect: false,
            },
        },
        scales: {
            x: {
                type: 'category',
                labels: data.axes.map((a) => a.charAt(0).toUpperCase() + a.slice(1)),
            },
            y: {
                beginAtZero: true,
                max: 1200,
            },
        },
    });

    return new window.Chart(canvas, {
        type: 'line',
        data: { labels: data.axes.map((a) => a.charAt(0).toUpperCase() + a.slice(1)), datasets },
        options,
    });
}

/**
 * Create a radar chart for stat distribution comparison.
 *
 * @param {HTMLCanvasElement} canvas
 * @param {Array<{career_id, career_name, stats, color}>} careers
 * @returns {Chart}
 */
export function createStatRadarChart(canvas, careers) {
    const labels = ['Speed', 'Stamina', 'Power', 'Guts', 'Wit'];

    const datasets = careers.map((career) => ({
        label: career.career_name,
        data: [
            career.stats.speed || 0,
            career.stats.stamina || 0,
            career.stats.power || 0,
            career.stats.guts || 0,
            career.stats.wit || 0,
        ],
        borderColor: career.color,
        backgroundColor: career.color + '22',
        borderWidth: 2,
        pointRadius: 4,
    }));

    const options = createChartOptions({
        plugins: {
            title: {
                display: true,
                text: 'Stat Distribution',
            },
        },
        scales: {
            r: {
                beginAtZero: true,
                max: 1200,
                ticks: { stepSize: 200 },
            },
        },
    });

    return new window.Chart(canvas, {
        type: 'radar',
        data: { labels, datasets },
        options,
    });
}

/**
 * Create a line chart for stat progression over turns.
 *
 * @param {HTMLCanvasElement} canvas
 * @param {Object} data - { labels: number[], datasets: Array<{career_name, stat, data: number[]}> }
 * @param {string} statFilter - Optional stat to filter by (e.g., 'speed')
 * @returns {Chart}
 */
export function createProgressionChart(canvas, data, statFilter = null) {
    let filteredDatasets = data.datasets;

    if (statFilter) {
        filteredDatasets = filteredDatasets.filter((ds) => ds.stat === statFilter);
    }

    const datasets = filteredDatasets.map((ds, index) => ({
        label: `${ds.career_name} - ${ds.stat.charAt(0).toUpperCase() + ds.stat.slice(1)}`,
        data: ds.data,
        borderColor: STAT_COLORS[ds.stat] || '#888',
        backgroundColor: (STAT_COLORS[ds.stat] || '#888') + '22',
        borderWidth: 2,
        borderDash: index >= 5 ? [5, 5] : [],
        pointRadius: 0,
        pointHoverRadius: 4,
        fill: false,
        tension: 0.3,
    }));

    const options = createChartOptions({
        plugins: {
            title: {
                display: true,
                text: statFilter
                    ? `${statFilter.charAt(0).toUpperCase() + statFilter.slice(1)} Progression`
                    : 'Stat Progression',
            },
            tooltip: {
                mode: 'index',
                intersect: false,
            },
        },
        scales: {
            x: {
                title: { display: true, text: 'Turn' },
            },
            y: {
                beginAtZero: true,
                title: { display: true, text: 'Cumulative Stat Value' },
            },
        },
        interaction: {
            mode: 'nearest',
            axis: 'x',
            intersect: false,
        },
    });

    return new window.Chart(canvas, {
        type: 'line',
        data: { labels: data.labels, datasets },
        options,
    });
}

/**
 * Create a divergence indicator chart showing where careers diverged.
 *
 * @param {HTMLCanvasElement} canvas
 * @param {Array<{turn, stat, magnitude}>} divergencePoints
 * @returns {Chart}
 */
export function createDivergenceChart(canvas, divergencePoints) {
    const byTurn = {};
    divergencePoints.forEach((dp) => {
        if (!byTurn[dp.turn]) {
            byTurn[dp.turn] = {};
        }
        byTurn[dp.turn][dp.stat] = dp.magnitude;
    });

    const turns = Object.keys(byTurn).map(Number).sort((a, b) => a - b);
    const stats = ['speed', 'stamina', 'power', 'guts', 'wit'];

    const datasets = stats.map((stat) => ({
        label: stat.charAt(0).toUpperCase() + stat.slice(1),
        data: turns.map((turn) => byTurn[turn]?.[stat] || 0),
        backgroundColor: STAT_COLORS[stat] + '88',
        borderColor: STAT_COLORS[stat],
        borderWidth: 1,
    }));

    const options = createChartOptions({
        plugins: {
            title: {
                display: true,
                text: 'Divergence Points by Turn',
            },
        },
        scales: {
            x: {
                title: { display: true, text: 'Turn' },
                stacked: true,
            },
            y: {
                title: { display: true, text: 'Divergence Magnitude' },
                stacked: true,
                beginAtZero: true,
            },
        },
    });

    return new window.Chart(canvas, {
        type: 'bar',
        data: { labels: turns, datasets },
        options,
    });
}

/**
 * Create a summary bar chart for total stats comparison.
 *
 * @param {HTMLCanvasElement} canvas
 * @param {Array<{name, stats}>} careers
 * @returns {Chart}
 */
export function createTotalStatsBarChart(canvas, careers) {
    const labels = careers.map((c) => c.name);
    const stats = ['speed', 'stamina', 'power', 'guts', 'wit'];

    const datasets = stats.map((stat) => ({
        label: stat.charAt(0).toUpperCase() + stat.slice(1),
        data: careers.map((c) => c.stats[stat] || 0),
        backgroundColor: STAT_COLORS[stat] + 'CC',
        borderColor: STAT_COLORS[stat],
        borderWidth: 1,
    }));

    const options = createChartOptions({
        plugins: {
            title: {
                display: true,
                text: 'Total Stats by Career',
            },
        },
        scales: {
            x: {
                stacked: true,
            },
            y: {
                stacked: true,
                beginAtZero: true,
                title: { display: true, text: 'Total Stats' },
            },
        },
    });

    return new window.Chart(canvas, {
        type: 'bar',
        data: { labels, datasets },
        options,
    });
}

/**
 * Export a chart as a PNG image.
 *
 * @param {Chart} chart
 * @param {string} filename
 */
export function exportChart(chart, filename = 'chart') {
    exportChartAsImage(chart, filename, 'png');
}
