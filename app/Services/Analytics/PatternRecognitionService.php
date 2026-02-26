<?php

namespace App\Services\Analytics;

use App\Models\Career;
use App\Models\Character;
use App\Models\TrainingSession;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PatternRecognitionService
{
    private const CACHE_TTL = 3600;

    private const MAX_ITERATIONS = 100;

    private const CONVERGENCE_THRESHOLD = 0.001;

    private const MIN_SUPPORT = 0.1;

    private const MIN_CONFIDENCE = 0.5;

    /**
     * Run K-means clustering on career stat distributions.
     *
     * @param  Collection<int, Career>  $careers
     * @return array{clusters: array<int, array{centroid: array<string, float>, members: array<int, int>, size: int}>, iterations: int, converged: bool}
     */
    public function clusterCareers(Collection $careers, int $k = 3): array
    {
        $cacheKey = 'pattern_clusters_'.md5($careers->pluck('id')->sort()->implode('_').'_'.$k);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($careers, $k) {
            $dataPoints = $this->extractStatVectors($careers);

            if ($dataPoints->count() < $k) {
                return [
                    'clusters' => [],
                    'iterations' => 0,
                    'converged' => false,
                ];
            }

            return $this->kMeans($dataPoints, $k);
        });
    }

    /**
     * Extract stat vectors from careers for clustering.
     *
     * @param  Collection<int, Career>  $careers
     * @return Collection<int, array{id: int, vector: array<string, float>}>
     */
    public function extractStatVectors(Collection $careers): Collection
    {
        /** @var Collection<int, array{id: int, vector: array<string, float>}> $result */
        $result = $careers->map(function (Career $career) {
            return [
                'id' => $career->id,
                'vector' => [
                    'speed' => (float) ($career->final_speed ?? 0),
                    'stamina' => (float) ($career->final_stamina ?? 0),
                    'power' => (float) ($career->final_power ?? 0),
                    'guts' => (float) ($career->final_guts ?? 0),
                    'wit' => (float) ($career->final_wit ?? 0),
                ],
            ];
        })->values();

        return $result;
    }

    /**
     * Perform K-means clustering algorithm.
     *
     * @param  Collection<int, array{id: int, vector: array<string, float>}>  $dataPoints
     * @return array{clusters: array<int, array{centroid: array<string, float>, members: array<int, int>, size: int}>, iterations: int, converged: bool}
     */
    public function kMeans(Collection $dataPoints, int $k): array
    {
        $centroids = $this->initializeCentroids($dataPoints, $k);
        $assignments = [];
        $converged = false;
        $iterations = 0;

        while ($iterations < self::MAX_ITERATIONS && ! $converged) {
            $newAssignments = $this->assignToClusters($dataPoints, $centroids);
            $newCentroids = $this->recalculateCentroids($dataPoints, $newAssignments, $k);

            $converged = $this->hasConverged($centroids, $newCentroids);
            $centroids = $newCentroids;
            $assignments = $newAssignments;
            $iterations++;
        }

        $clusters = [];
        for ($i = 0; $i < $k; $i++) {
            $members = [];
            foreach ($assignments as $pointIndex => $clusterIndex) {
                if ($clusterIndex === $i) {
                    /** @var array{id: int, vector: array<string, float>} $point */
                    $point = $dataPoints[$pointIndex];
                    $members[] = $point['id'];
                }
            }

            $clusters[$i] = [
                'centroid' => $centroids[$i],
                'members' => $members,
                'size' => count($members),
            ];
        }

        return [
            'clusters' => $clusters,
            'iterations' => $iterations,
            'converged' => $converged,
        ];
    }

    /**
     * Initialize centroids using K-means++ strategy.
     *
     * @param  Collection<int, array{id: int, vector: array<string, float>}>  $dataPoints
     * @return array<int, array<string, float>>
     */
    private function initializeCentroids(Collection $dataPoints, int $k): array
    {
        $centroids = [];
        $firstIndex = array_rand($dataPoints->toArray());
        /** @var array{id: int, vector: array<string, float>} $firstPoint */
        $firstPoint = $dataPoints[$firstIndex];
        $centroids[] = $firstPoint['vector'];

        for ($i = 1; $i < $k; $i++) {
            $distances = $dataPoints->map(function ($point) use ($centroids) {
                $minDist = PHP_FLOAT_MAX;
                foreach ($centroids as $centroid) {
                    $dist = $this->euclideanDistance($point['vector'], $centroid);
                    $minDist = min($minDist, $dist);
                }

                return $minDist * $minDist;
            });

            $totalDistance = $distances->sum();
            if ($totalDistance == 0) {
                /** @var array{id: int, vector: array<string, float>} $randPoint */
                $randPoint = $dataPoints[array_rand($dataPoints->toArray())];
                $centroids[] = $randPoint['vector'];

                continue;
            }

            $threshold = mt_rand() / mt_getrandmax() * (float) $totalDistance;
            $cumulative = 0.0;
            foreach ($distances as $index => $distance) {
                $cumulative += (float) $distance;
                if ($cumulative >= $threshold) {
                    /** @var array{id: int, vector: array<string, float>} $selectedPoint */
                    $selectedPoint = $dataPoints[$index];
                    $centroids[] = $selectedPoint['vector'];
                    break;
                }
            }
        }

        return $centroids;
    }

    /**
     * Assign each data point to the nearest centroid.
     *
     * @param  Collection<int, array{id: int, vector: array<string, float>}>  $dataPoints
     * @param  array<int, array<string, float>>  $centroids
     * @return array<int, int>
     */
    private function assignToClusters(Collection $dataPoints, array $centroids): array
    {
        $assignments = [];

        foreach ($dataPoints as $index => $point) {
            $minDistance = PHP_FLOAT_MAX;
            $closestCluster = 0;

            foreach ($centroids as $clusterIndex => $centroid) {
                $distance = $this->euclideanDistance($point['vector'], $centroid);
                if ($distance < $minDistance) {
                    $minDistance = $distance;
                    $closestCluster = $clusterIndex;
                }
            }

            $assignments[$index] = $closestCluster;
        }

        return $assignments;
    }

    /**
     * Recalculate centroids based on current assignments.
     *
     * @param  Collection<int, array{id: int, vector: array<string, float>}>  $dataPoints
     * @param  array<int, int>  $assignments
     * @return array<int, array<string, float>>
     */
    private function recalculateCentroids(Collection $dataPoints, array $assignments, int $k): array
    {
        $centroids = [];
        $statKeys = ['speed', 'stamina', 'power', 'guts', 'wit'];

        for ($i = 0; $i < $k; $i++) {
            $clusterPoints = collect($assignments)
                ->filter(fn ($cluster) => $cluster === $i)
                ->keys()
                ->map(function ($index) use ($dataPoints) {
                    /** @var array{id: int, vector: array<string, float>} $dp */
                    $dp = $dataPoints[$index];

                    return $dp['vector'];
                });

            if ($clusterPoints->isEmpty()) {
                $centroids[$i] = array_fill_keys($statKeys, 0.0);

                continue;
            }

            $centroid = [];
            foreach ($statKeys as $key) {
                $centroid[$key] = (float) $clusterPoints->avg($key);
            }
            $centroids[$i] = $centroid;
        }

        return $centroids;
    }

    /**
     * Check if centroids have converged.
     *
     * @param  array<int, array<string, float>>  $old
     * @param  array<int, array<string, float>>  $new
     */
    private function hasConverged(array $old, array $new): bool
    {
        foreach ($old as $i => $oldCentroid) {
            if (! isset($new[$i])) {
                return false;
            }

            if ($this->euclideanDistance($oldCentroid, $new[$i]) > self::CONVERGENCE_THRESHOLD) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calculate Euclidean distance between two vectors.
     *
     * @param  array<string, float>  $a
     * @param  array<string, float>  $b
     */
    public function euclideanDistance(array $a, array $b): float
    {
        $sum = 0;
        foreach ($a as $key => $value) {
            $diff = $value - ($b[$key] ?? 0);
            $sum += $diff * $diff;
        }

        return sqrt($sum);
    }

    /**
     * Mine association rules from training session patterns.
     *
     * @param  Collection<int, Career>  $careers
     * @return array{rules: array<int, array{antecedent: array<int, string>, consequent: string, support: float, confidence: float, lift: float}>, frequent_itemsets: array<int, array{items: array<int, string>, support: float}>}
     */
    public function mineAssociationRules(Collection $careers): array
    {
        $transactions = $this->buildTransactions($careers);
        $totalTransactions = count($transactions);

        if ($totalTransactions === 0) {
            return ['rules' => [], 'frequent_itemsets' => []];
        }

        $frequentItemsets = $this->findFrequentItemsets($transactions, $totalTransactions);
        $rules = $this->generateRules($frequentItemsets, $transactions, $totalTransactions);

        return [
            'rules' => $rules,
            'frequent_itemsets' => $frequentItemsets,
        ];
    }

    /**
     * Build transaction sets from career training data.
     *
     * @param  Collection<int, Career>  $careers
     * @return array<int, array<int, string>>
     */
    private function buildTransactions(Collection $careers): array
    {
        /** @var array<int, array<int, string>> $result */
        $result = $careers->map(function (Career $career) {
            $items = [];

            $items[] = 'scenario:'.($career->scenario_type ?? 'unknown');

            if ($career->final_speed >= 800) {
                $items[] = 'high_speed';
            }
            if ($career->final_stamina >= 800) {
                $items[] = 'high_stamina';
            }
            if ($career->final_power >= 800) {
                $items[] = 'high_power';
            }
            if ($career->final_guts >= 800) {
                $items[] = 'high_guts';
            }
            if ($career->final_wit >= 800) {
                $items[] = 'high_wit';
            }

            $totalStats = ($career->final_speed ?? 0)
                + ($career->final_stamina ?? 0)
                + ($career->final_power ?? 0)
                + ($career->final_guts ?? 0)
                + ($career->final_wit ?? 0);

            if ($totalStats >= 4000) {
                $items[] = 'elite_total';
            } elseif ($totalStats >= 3000) {
                $items[] = 'good_total';
            }

            if ($career->status === 'completed') {
                $items[] = 'completed';
            }

            return $items;
        })->toArray();

        return $result;
    }

    /**
     * Find frequent itemsets using the Apriori algorithm.
     *
     * @param  array<int, array<int, string>>  $transactions
     * @return array<int, array{items: array<int, string>, support: float}>
     */
    private function findFrequentItemsets(array $transactions, int $totalTransactions): array
    {
        $minSupportCount = (int) ceil(self::MIN_SUPPORT * $totalTransactions);
        $frequent = [];

        $itemCounts = [];
        foreach ($transactions as $transaction) {
            foreach ($transaction as $item) {
                $itemCounts[$item] = ($itemCounts[$item] ?? 0) + 1;
            }
        }

        $frequentItems = array_filter($itemCounts, fn ($count) => $count >= $minSupportCount);

        foreach ($frequentItems as $item => $count) {
            $frequent[] = [
                'items' => [$item],
                'support' => $count / $totalTransactions,
            ];
        }

        $currentLevel = array_keys($frequentItems);

        for ($size = 2; $size <= 3; $size++) {
            $candidates = $this->generateCandidates($currentLevel, $size);
            $nextLevel = [];

            foreach ($candidates as $candidate) {
                $count = 0;
                foreach ($transactions as $transaction) {
                    if (count(array_intersect($candidate, $transaction)) === count($candidate)) {
                        $count++;
                    }
                }

                if ($count >= $minSupportCount) {
                    $frequent[] = [
                        'items' => $candidate,
                        'support' => $count / $totalTransactions,
                    ];
                    foreach ($candidate as $item) {
                        if (! in_array($item, $nextLevel)) {
                            $nextLevel[] = $item;
                        }
                    }
                }
            }

            $currentLevel = $nextLevel;
            if (empty($currentLevel)) {
                break;
            }
        }

        return $frequent;
    }

    /**
     * Generate candidate itemsets of a given size.
     *
     * @param  array<int, string>  $items
     * @return array<int, array<int, string>>
     */
    private function generateCandidates(array $items, int $size): array
    {
        $candidates = [];
        $items = array_values($items);
        $n = count($items);

        if ($size === 2) {
            for ($i = 0; $i < $n; $i++) {
                for ($j = $i + 1; $j < $n; $j++) {
                    $candidates[] = [$items[$i], $items[$j]];
                }
            }
        } elseif ($size === 3) {
            for ($i = 0; $i < $n; $i++) {
                for ($j = $i + 1; $j < $n; $j++) {
                    for ($l = $j + 1; $l < $n; $l++) {
                        $candidates[] = [$items[$i], $items[$j], $items[$l]];
                    }
                }
            }
        }

        return $candidates;
    }

    /**
     * Generate association rules from frequent itemsets.
     *
     * @param  array<int, array{items: array<int, string>, support: float}>  $frequentItemsets
     * @param  array<int, array<int, string>>  $transactions
     * @return array<int, array{antecedent: array<int, string>, consequent: string, support: float, confidence: float, lift: float}>
     */
    private function generateRules(array $frequentItemsets, array $transactions, int $totalTransactions): array
    {
        $rules = [];

        $multiItemSets = array_filter($frequentItemsets, fn ($set) => count($set['items']) >= 2);

        foreach ($multiItemSets as $itemset) {
            foreach ($itemset['items'] as $index => $consequent) {
                $antecedent = array_values(array_filter(
                    $itemset['items'],
                    fn ($item) => $item !== $consequent
                ));

                $antecedentCount = 0;
                foreach ($transactions as $transaction) {
                    if (count(array_intersect($antecedent, $transaction)) === count($antecedent)) {
                        $antecedentCount++;
                    }
                }

                if ($antecedentCount === 0) {
                    continue;
                }

                $confidence = ($itemset['support'] * $totalTransactions) / $antecedentCount;

                $consequentCount = 0;
                foreach ($transactions as $transaction) {
                    if (in_array($consequent, $transaction)) {
                        $consequentCount++;
                    }
                }

                $consequentSupport = $consequentCount / $totalTransactions;
                $lift = $consequentSupport > 0 ? $confidence / $consequentSupport : 0;

                if ($confidence >= self::MIN_CONFIDENCE) {
                    $rules[] = [
                        'antecedent' => $antecedent,
                        'consequent' => $consequent,
                        'support' => $itemset['support'],
                        'confidence' => round($confidence, 4),
                        'lift' => round($lift, 4),
                    ];
                }
            }
        }

        usort($rules, fn ($a, $b) => $b['confidence'] <=> $a['confidence']);

        return $rules;
    }

    /**
     * Identify training patterns from training sessions.
     *
     * @return array{dominant_training_types: array<string, int>, phase_patterns: array<string, array<string, int>>, efficiency_by_type: array<string, float>}
     */
    public function identifyTrainingPatterns(Character $character): array
    {
        $sessions = TrainingSession::query()
            ->where('character_id', $character->id)
            ->orderBy('turn_number')
            ->get();

        /** @var array<string, int> $trainingTypeCounts */
        $trainingTypeCounts = $sessions->groupBy('training_type')
            ->map->count()
            ->sortDesc()
            ->toArray();

        /** @var array<string, array<string, int>> $phasePatterns */
        $phasePatterns = $sessions->groupBy('career_phase')
            ->map(fn (Collection $group) => $group->groupBy('training_type')->map->count()->toArray())
            ->toArray();

        /** @var array<string, float> $efficiencyByType */
        $efficiencyByType = $sessions->groupBy('training_type')
            ->map(fn (Collection $group) => round($group->avg('training_efficiency') ?? 0, 2))
            ->toArray();

        return [
            'dominant_training_types' => $trainingTypeCounts,
            'phase_patterns' => $phasePatterns,
            'efficiency_by_type' => $efficiencyByType,
        ];
    }

    /**
     * Clear cached pattern data.
     */
    public function clearCache(): void
    {
        Cache::forget('pattern_clusters_*');
    }
}
