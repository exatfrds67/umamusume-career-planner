<?php

declare(strict_types=1);

use App\Models\Character;
use App\Models\User;

/**
 * Visualization Components Tests
 *
 * Tests for the comprehensive visualization components including:
 * - Stat progression charts
 * - Performance dashboards
 * - Comparison tables
 * - Trend analysis charts
 *
 * Requirements: 15.4, 25.3 (Task 5.2.3)
 */
beforeEach(function () {
    $this->user = User::factory()->create();
    $this->character = Character::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Test Character',
        'scenario_type' => 'ura_finale',
    ]);
});

describe('Stat Progression Chart Component', function () {
    it('renders the stat progression chart component', function () {
        $view = $this->blade(
            '<x-analytics.stat-progression-chart :data="$data" :stats="$stats" />',
            [
                'data' => [
                    ['turn' => 1, 'speed' => 100, 'stamina' => 80, 'power' => 90, 'guts' => 70, 'wit' => 85],
                    ['turn' => 10, 'speed' => 200, 'stamina' => 180, 'power' => 190, 'guts' => 170, 'wit' => 185],
                ],
                'stats' => ['speed', 'stamina', 'power', 'guts', 'wit'],
            ]
        );

        $view->assertSee('Stat Progression');
        $view->assertSee('Speed');
        $view->assertSee('Stamina');
        $view->assertSee('Power');
        $view->assertSee('Guts');
        $view->assertSee('Wit');
    });

    it('includes accessible data table for screen readers', function () {
        $view = $this->blade(
            '<x-analytics.stat-progression-chart :data="$data" />',
            [
                'data' => [
                    ['turn' => 1, 'speed' => 100, 'stamina' => 80, 'power' => 90, 'guts' => 70, 'wit' => 85],
                ],
            ]
        );

        $view->assertSee('sr-only');
        $view->assertSee('Stat progression data table');
    });

    it('shows career phase markers when enabled', function () {
        $view = $this->blade(
            '<x-analytics.stat-progression-chart :data="$data" :phases="true" />',
            [
                'data' => [
                    ['turn' => 1, 'speed' => 100],
                ],
            ]
        );

        $view->assertSee('Career Phases');
        $view->assertSee('Junior (1-24)');
        $view->assertSee('Classic (25-48)');
        $view->assertSee('Senior (49-72)');
    });

    it('handles empty data gracefully', function () {
        $view = $this->blade(
            '<x-analytics.stat-progression-chart :data="$data" />',
            ['data' => []]
        );

        $view->assertSee('No progression data available');
    });
});

describe('Performance Dashboard Component', function () {
    it('renders the performance dashboard with metrics', function () {
        $view = $this->blade(
            '<x-analytics.performance-dashboard :metrics="$metrics" />',
            [
                'metrics' => [
                    'overall_efficiency' => 75.5,
                    'success_rate' => 80.0,
                    'completion_rate' => 90.0,
                    'average_final_grade' => 'A',
                    'total_careers' => 10,
                    'completed_careers' => 9,
                ],
            ]
        );

        $view->assertSee('Performance Overview');
        $view->assertSee('Overall Efficiency');
        $view->assertSee('Success Rate');
        $view->assertSee('Completion Rate');
    });

    it('displays trend indicator when provided', function () {
        $view = $this->blade(
            '<x-analytics.performance-dashboard :metrics="$metrics" :showTrend="true" />',
            [
                'metrics' => [
                    'overall_efficiency' => 75.5,
                    'performance_trend' => [
                        'trend' => 'improving',
                        'improvement_rate' => 5.2,
                    ],
                ],
            ]
        );

        $view->assertSee('Trend');
        $view->assertSee('improving'); // lowercase in component
    });

    it('shows scenario breakdown when available', function () {
        $view = $this->blade(
            '<x-analytics.performance-dashboard :metrics="$metrics" />',
            [
                'metrics' => [
                    'metrics_by_scenario' => [
                        'ura_finale' => [
                            'count' => 5,
                            'completed' => 4,
                            'success_rate' => 80.0,
                            'avg_efficiency' => 72.5,
                        ],
                        'unity_cup' => [
                            'count' => 3,
                            'completed' => 3,
                            'success_rate' => 100.0,
                            'avg_efficiency' => 78.0,
                        ],
                    ],
                ],
            ]
        );

        $view->assertSee('Performance by Scenario');
        $view->assertSee('ura finale');
        $view->assertSee('unity cup');
    });

    it('handles missing metrics gracefully', function () {
        $view = $this->blade(
            '<x-analytics.performance-dashboard :metrics="$metrics" />',
            ['metrics' => []]
        );

        $view->assertSee('Performance Overview');
        $view->assertSee('0'); // Default values
    });
});

describe('Metric Card Component', function () {
    it('renders metric card with value and label', function () {
        $view = $this->blade(
            '<x-analytics.metric-card :value="$value" :label="$label" :unit="$unit" />',
            [
                'value' => 85.5,
                'label' => 'Test Metric',
                'unit' => '%',
            ]
        );

        $view->assertSee('Test Metric');
        $view->assertSee('85.5');
        $view->assertSee('%');
    });

    it('applies correct color based on color prop', function () {
        $view = $this->blade(
            '<x-analytics.metric-card :value="75" label="Success" color="success" />',
            []
        );

        $view->assertSee('bg-green-100');
    });

    it('shows trend indicator when provided', function () {
        $view = $this->blade(
            '<x-analytics.metric-card :value="75" label="Test" :trend="$trend" />',
            [
                'trend' => [
                    'direction' => 'up',
                    'value' => 5,
                    'unit' => '%',
                ],
            ]
        );

        $view->assertSee('+5%');
    });

    it('supports compact mode', function () {
        $view = $this->blade(
            '<x-analytics.metric-card :value="75" label="Test" :compact="true" />',
            []
        );

        $view->assertSee('p-3'); // Compact padding
    });
});

describe('Comparison Table Component', function () {
    it('renders comparison table with career data', function () {
        $view = $this->blade(
            '<x-analytics.comparison-table :careers="$careers" />',
            [
                'careers' => [
                    [
                        'career_id' => 1,
                        'career_name' => 'Career 1',
                        'scenario_type' => 'ura_finale',
                        'total_stat_points' => 3500,
                        'efficiency_rating' => 75.0,
                        'race_win_rate' => 80.0,
                        'training_failures' => 2,
                        'skills_acquired' => 15,
                        'status' => 'completed',
                    ],
                    [
                        'career_id' => 2,
                        'career_name' => 'Career 2',
                        'scenario_type' => 'unity_cup',
                        'total_stat_points' => 3800,
                        'efficiency_rating' => 82.0,
                        'race_win_rate' => 85.0,
                        'training_failures' => 1,
                        'skills_acquired' => 18,
                        'status' => 'completed',
                    ],
                ],
            ]
        );

        $view->assertSee('Career Comparison');
        $view->assertSee('Career');
        $view->assertSee('Scenario');
        $view->assertSee('Total Stats');
        $view->assertSee('Efficiency');
    });

    it('includes search and filter controls when filterable', function () {
        $view = $this->blade(
            '<x-analytics.comparison-table :careers="$careers" :filterable="true" />',
            [
                'careers' => [
                    ['career_id' => 1, 'career_name' => 'Test'],
                ],
            ]
        );

        $view->assertSee('Search careers');
        $view->assertSee('All Scenarios');
        $view->assertSee('All Status');
    });

    it('shows summary statistics', function () {
        $view = $this->blade(
            '<x-analytics.comparison-table :careers="$careers" />',
            [
                'careers' => [
                    ['career_id' => 1, 'efficiency_rating' => 75.0, 'race_win_rate' => 80.0],
                ],
            ]
        );

        $view->assertSee('Summary Statistics');
        $view->assertSee('Avg Efficiency');
        $view->assertSee('Avg Win Rate');
    });

    it('handles empty careers array', function () {
        $view = $this->blade(
            '<x-analytics.comparison-table :careers="$careers" />',
            ['careers' => []]
        );

        $view->assertSee('No careers found');
    });

    it('supports sortable columns', function () {
        $view = $this->blade(
            '<x-analytics.comparison-table :careers="$careers" :sortable="true" />',
            [
                'careers' => [
                    ['career_id' => 1, 'career_name' => 'Test'],
                ],
            ]
        );

        $view->assertSee('sortBy');
    });
});

describe('Trend Analysis Chart Component', function () {
    it('renders trend analysis chart with data', function () {
        $view = $this->blade(
            '<x-analytics.trend-analysis-chart :data="$data" metric="efficiency" />',
            [
                'data' => [
                    ['label' => 'Career 1', 'value' => 70, 'lower' => 65, 'upper' => 75],
                    ['label' => 'Career 2', 'value' => 75, 'lower' => 70, 'upper' => 80],
                    ['label' => 'Career 3', 'value' => 78, 'lower' => 73, 'upper' => 83],
                ],
            ]
        );

        $view->assertSee('Performance Trend Analysis');
        $view->assertSee('Training Efficiency');
    });

    it('displays statistics summary', function () {
        $view = $this->blade(
            '<x-analytics.trend-analysis-chart :data="$data" />',
            [
                'data' => [
                    ['value' => 70],
                    ['value' => 75],
                    ['value' => 80],
                ],
            ]
        );

        $view->assertSee('Current');
        $view->assertSee('Average');
        $view->assertSee('Trend');
        $view->assertSee('Confidence');
    });

    it('shows confidence interval toggle when enabled', function () {
        $view = $this->blade(
            '<x-analytics.trend-analysis-chart :data="$data" :showConfidence="true" />',
            [
                'data' => [['value' => 70]],
            ]
        );

        $view->assertSee('Confidence Interval');
    });

    it('shows prediction toggle when enabled', function () {
        $view = $this->blade(
            '<x-analytics.trend-analysis-chart :data="$data" :showPrediction="true" />',
            [
                'data' => [['value' => 70]],
            ]
        );

        $view->assertSee('Prediction');
    });

    it('includes accessible data table', function () {
        $view = $this->blade(
            '<x-analytics.trend-analysis-chart :data="$data" />',
            [
                'data' => [
                    ['label' => 'Career 1', 'value' => 70, 'lower' => 65, 'upper' => 75],
                ],
            ]
        );

        $view->assertSee('sr-only');
        $view->assertSee('trend data');
    });

    it('handles insufficient data gracefully', function () {
        $view = $this->blade(
            '<x-analytics.trend-analysis-chart :data="$data" />',
            ['data' => []]
        );

        $view->assertSee('Insufficient data for trend analysis');
    });

    it('displays insights panel when insights are available', function () {
        $view = $this->blade(
            '<x-analytics.trend-analysis-chart :data="$data" />',
            [
                'data' => [
                    ['value' => 70],
                    ['value' => 75],
                    ['value' => 80],
                ],
            ]
        );

        $view->assertSee('Trend Insights');
    });
});

describe('Component Accessibility', function () {
    it('stat progression chart has proper ARIA attributes', function () {
        $view = $this->blade(
            '<x-analytics.stat-progression-chart :data="$data" />',
            [
                'data' => [['turn' => 1, 'speed' => 100]],
            ]
        );

        // Check for role attribute (HTML entities are escaped in assertSee)
        $view->assertSee('role="figure"', false);
        $view->assertSee('aria-label', false);
    });

    it('comparison table has proper table semantics', function () {
        $view = $this->blade(
            '<x-analytics.comparison-table :careers="$careers" />',
            [
                'careers' => [['career_id' => 1]],
            ]
        );

        $view->assertSee('role="grid"', false);
        $view->assertSee('aria-label="Career comparison table"', false);
    });

    it('metric card has proper group role', function () {
        $view = $this->blade(
            '<x-analytics.metric-card :value="75" label="Test" />',
            []
        );

        $view->assertSee('role="group"', false);
        $view->assertSee('aria-label="Test"', false);
    });

    it('trend chart has proper figure role', function () {
        $view = $this->blade(
            '<x-analytics.trend-analysis-chart :data="$data" />',
            [
                'data' => [['value' => 70]],
            ]
        );

        $view->assertSee('role="figure"', false);
    });
});

describe('Component Responsiveness', function () {
    it('performance dashboard uses responsive grid', function () {
        $view = $this->blade(
            '<x-analytics.performance-dashboard :metrics="$metrics" />',
            ['metrics' => []]
        );

        $view->assertSee('grid-cols-2');
        $view->assertSee('md:grid-cols-3');
    });

    it('comparison table is horizontally scrollable', function () {
        $view = $this->blade(
            '<x-analytics.comparison-table :careers="$careers" />',
            [
                'careers' => [['career_id' => 1]],
            ]
        );

        $view->assertSee('overflow-x-auto');
    });
});

describe('Dark Mode Support', function () {
    it('stat progression chart supports dark mode', function () {
        $view = $this->blade(
            '<x-analytics.stat-progression-chart :data="$data" />',
            [
                'data' => [['turn' => 1, 'speed' => 100]],
            ]
        );

        $view->assertSee('dark:');
    });

    it('performance dashboard supports dark mode', function () {
        $view = $this->blade(
            '<x-analytics.performance-dashboard :metrics="$metrics" />',
            ['metrics' => []]
        );

        $view->assertSee('dark:text-white');
        $view->assertSee('dark:bg-gray-800');
    });

    it('comparison table supports dark mode', function () {
        $view = $this->blade(
            '<x-analytics.comparison-table :careers="$careers" />',
            [
                'careers' => [['career_id' => 1]],
            ]
        );

        $view->assertSee('dark:bg-gray-900');
        $view->assertSee('dark:border-gray-700');
    });
});
