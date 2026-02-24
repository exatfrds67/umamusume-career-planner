<?php

namespace App\Services\Analytics;

use App\Models\Career;
use App\Models\TrainingSession;
use Illuminate\Support\Facades\Cache;

class CareerComparisonAnalyticsService
{
    private const CACHE_TTL = 3600;

    private const STAT_KEYS = ['speed', 'stamina', 'power', 'guts', 'wit'];

    /**
     * Generate parallel coordinates data for career comparison.
     *
     * @param  array<int, int>  $careerIds
     * @return array{axes: array<int, string>, series: array<int, array{career_id: int, career_name: string, values: array<string, float>, color: string}>}
     */
    public function generateParallelCoordinatesData(array $careerIds): array
    {
        $cacheKey = 'parallel_coords_'.md5(implode('_', $careerIds));

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($careerIds) {
            $careers = Career::query()
                ->whereIn('id', $careerIds)
                ->get();

            $axes = ['speed', 'stamina', 'power', 'guts', 'wit', 'sp', 'turns'];
            $colors = ['#3B82F6', '#F97316', '#EF4444', '#EC4899', '#22C55E', '#8B5CF6', '#06B6D4'];

            $series = $careers->values()->map(function (Career $career, int $index) use ($colors) {
                return [
                    'career_id' => $career->id,
                    'career_name' => $career->career_name ?? "Career #{$career->id}",
                    'values' => [
                        'speed' => (float) ($career->final_speed ?? 0),
                        'stamina' => (float) ($career->final_stamina ?? 0),
                        'power' => (float) ($career->final_power ?? 0),
                        'guts' => (float) ($career->final_guts ?? 0),
                        'wit' => (float) ($career->final_wit ?? 0),
                        'sp' => (float) ($career->final_sp ?? 0),
                        'turns' => (float) ($career->current_turn ?? 0),
                    ],
                    'color' => $colors[$index % count($colors)],
                ];
            })->toArray();

            return [
                'axes' => $axes,
                'series' => $series,
            ];
        });
    }

    /**
     * Identify divergence points where careers stat progressions diverged.
     *
     * @param  array<int, int>  $careerIds
     * @return array{divergence_points: array<int, array{turn: int, stat: string, careers: array<int, array{career_id: int, value: float}>, magnitude: float}>, summary: array{earliest_divergence: int|null, most_divergent_stat: string|null, max_magnitude: float}}
     */
    public function identifyDivergencePoints(array $careerIds): array
    {
        if (count($careerIds) < 2) {
            return [
                'divergence_points' => [],
                'summary' => [
                    'earliest_divergence' => null,
                    'most_divergent_stat' => null,
                    'max_magnitude' => 0,
                ],
            ];
        }

        $cacheKey = 'divergence_'.md5(implode('_', $careerIds));

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($careerIds) {
            $turnData = $this->buildTurnProgressionData($careerIds);
            $divergencePoints = [];
            $divergenceThreshold = 50;

            foreach ($turnData as $turn => $careerStats) {
                if (count($careerStats) < 2) {
                    continue;
                }

                foreach (self::STAT_KEYS as $stat) {
                    $values = collect($careerStats)->map(fn ($cs) => $cs[$stat] ?? 0);
                    $maxDiff = $values->max() - $values->min();

                    if ($maxDiff >= $divergenceThreshold) {
                        $divergencePoints[] = [
                            'turn' => $turn,
                            'stat' => $stat,
                            'careers' => collect($careerStats)->map(fn ($cs, $careerId) => [
                                'career_id' => (int) $careerId,
                                'value' => (float) ($cs[$stat] ?? 0),
                            ])->values()->toArray(),
                            'magnitude' => round($maxDiff, 2),
                        ];
                    }
                }
            }

            usort($divergencePoints, fn ($a, $b) => $b['magnitude'] <=> $a['magnitude']);

            $topDivergences = array_slice($divergencePoints, 0, 20);

            $earliestDivergence = ! empty($topDivergences)
                ? min(array_column($topDivergences, 'turn'))
                : null;

            $statMagnitudes = [];
            foreach ($topDivergences as $dp) {
                $statMagnitudes[$dp['stat']] = ($statMagnitudes[$dp['stat']] ?? 0) + $dp['magnitude'];
            }

            $mostDivergentStat = ! empty($statMagnitudes)
                ? array_keys($statMagnitudes, max($statMagnitudes))[0]
                : null;

            return [
                'divergence_points' => $topDivergences,
                'summary' => [
                    'earliest_divergence' => $earliestDivergence,
                    'most_divergent_stat' => $mostDivergentStat,
                    'max_magnitude' => ! empty($topDivergences) ? $topDivergences[0]['magnitude'] : 0,
                ],
            ];
        });
    }

    /**
     * Build turn-by-turn progression data for multiple careers.
     *
     * @param  array<int, int>  $careerIds
     * @return array<int, array<int, array<string, float>>>
     */
    private function buildTurnProgressionData(array $careerIds): array
    {
        $turnData = [];

        foreach ($careerIds as $careerId) {
            $sessions = TrainingSession::query()
                ->where('career_id', $careerId)
                ->orderBy('turn_number')
                ->get();

            $cumulativeStats = array_fill_keys(self::STAT_KEYS, 0.0);

            foreach ($sessions as $session) {
                $cumulativeStats['speed'] += (float) ($session->speed_gain ?? 0);
                $cumulativeStats['stamina'] += (float) ($session->stamina_gain ?? 0);
                $cumulativeStats['power'] += (float) ($session->power_gain ?? 0);
                $cumulativeStats['guts'] += (float) ($session->guts_gain ?? 0);
                $cumulativeStats['wit'] += (float) ($session->wit_gain ?? 0);

                $turnData[$session->turn_number][$careerId] = $cumulativeStats;
            }
        }

        ksort($turnData);

        return $turnData;
    }

    /**
     * Generate stat progression chart data for multiple careers.
     *
     * @param  array<int, int>  $careerIds
     * @return array{labels: array<int, int>, datasets: array<int, array{career_id: int, career_name: string, stat: string, data: array<int, float>}>}
     */
    public function generateProgressionChartData(array $careerIds): array
    {
        $cacheKey = 'progression_chart_'.md5(implode('_', $careerIds));

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($careerIds) {
            $turnData = $this->buildTurnProgressionData($careerIds);
            $careers = Career::query()->whereIn('id', $careerIds)->get()->keyBy('id');

            $labels = array_keys($turnData);
            $datasets = [];

            foreach ($careerIds as $careerId) {
                $career = $careers->get($careerId);
                $careerName = $career?->career_name ?? "Career #{$careerId}";

                foreach (self::STAT_KEYS as $stat) {
                    $data = [];
                    foreach ($labels as $turn) {
                        $data[] = $turnData[$turn][$careerId][$stat] ?? 0;
                    }

                    $datasets[] = [
                        'career_id' => $careerId,
                        'career_name' => $careerName,
                        'stat' => $stat,
                        'data' => $data,
                    ];
                }
            }

            return [
                'labels' => $labels,
                'datasets' => $datasets,
            ];
        });
    }

    /**
     * Calculate statistical summary for career comparison.
     *
     * @param  array<int, int>  $careerIds
     * @return array{careers: array<int, array{id: int, name: string, total_stats: int, stats: array<string, int>, rank: int}>, averages: array<string, float>, std_deviations: array<string, float>}
     */
    public function calculateComparisonSummary(array $careerIds): array
    {
        $careers = Career::query()
            ->whereIn('id', $careerIds)
            ->get();

        $careerData = $careers->map(function (Career $career) {
            $stats = [
                'speed' => $career->final_speed ?? 0,
                'stamina' => $career->final_stamina ?? 0,
                'power' => $career->final_power ?? 0,
                'guts' => $career->final_guts ?? 0,
                'wit' => $career->final_wit ?? 0,
            ];

            return [
                'id' => $career->id,
                'name' => $career->career_name ?? "Career #{$career->id}",
                'total_stats' => array_sum($stats),
                'stats' => $stats,
                'rank' => 0,
            ];
        })->sortByDesc('total_stats')->values();

        $careerData = $careerData->map(function ($career, $index) {
            $career['rank'] = $index + 1;

            return $career;
        });

        $averages = [];
        $stdDeviations = [];
        foreach (self::STAT_KEYS as $stat) {
            $values = $careerData->pluck("stats.{$stat}");
            $averages[$stat] = round($values->avg(), 2);
            $stdDeviations[$stat] = round($this->standardDeviation($values->toArray()), 2);
        }

        return [
            'careers' => $careerData->toArray(),
            'averages' => $averages,
            'std_deviations' => $stdDeviations,
        ];
    }

    /**
     * Calculate standard deviation.
     *
     * @param  array<int, float|int>  $values
     */
    private function standardDeviation(array $values): float
    {
        $n = count($values);
        if ($n <= 1) {
            return 0;
        }

        $mean = array_sum($values) / $n;
        $squaredDiffs = array_map(fn ($v) => pow($v - $mean, 2), $values);

        return sqrt(array_sum($squaredDiffs) / ($n - 1));
    }

    /**
     * Clear cached comparison data.
     */
    public function clearCache(): void
    {
        Cache::forget('parallel_coords_*');
        Cache::forget('divergence_*');
        Cache::forget('progression_chart_*');
    }
}
