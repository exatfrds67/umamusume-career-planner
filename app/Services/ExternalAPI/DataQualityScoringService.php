<?php

declare(strict_types=1);

namespace App\Services\ExternalAPI;

use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Data Quality Scoring Service with MCP Analytics
 *
 * Provides comprehensive data quality assessment using MCP analytics tools
 * for accuracy, completeness, consistency, timeliness, and validity scoring.
 *
 * Requirements: 14.3, 14.4, 56.3, Task 4.4.4
 */
class DataQualityScoringService
{
    /**
     * Quality score cache key
     */
    protected const QUALITY_SCORE_KEY = 'quality_score:';

    /**
     * Quality history key
     */
    protected const QUALITY_HISTORY_KEY = 'quality_history:';

    /**
     * Quality dimensions
     */
    protected const DIMENSION_ACCURACY = 'accuracy';

    protected const DIMENSION_COMPLETENESS = 'completeness';

    protected const DIMENSION_CONSISTENCY = 'consistency';

    protected const DIMENSION_TIMELINESS = 'timeliness';

    protected const DIMENSION_VALIDITY = 'validity';

    /**
     * Dimension weights
     *
     * @var array<string, float>
     */
    protected array $dimensionWeights = [
        self::DIMENSION_ACCURACY => 0.30,
        self::DIMENSION_COMPLETENESS => 0.25,
        self::DIMENSION_CONSISTENCY => 0.20,
        self::DIMENSION_TIMELINESS => 0.15,
        self::DIMENSION_VALIDITY => 0.10,
    ];

    public function __construct(
        protected MCPClientService $mcpClient
    ) {}

    /**
     * Calculate comprehensive data quality score
     *
     * @param  array<string, mixed>  $metadata
     * @return array{overall_score: float, dimensions: array<string, float>, grade: string, recommendations: array<string>, details: array<string, mixed>}
     */
    public function calculateQualityScore(string $dataType, mixed $data, array $metadata = []): array
    {
        Log::info('[DataQualityScoring] Calculating quality score', [
            'data_type' => $dataType,
            'data_size' => is_array($data) ? count($data) : 0,
        ]);

        $startTime = microtime(true);

        // Calculate scores for each dimension
        $dimensionScores = [
            self::DIMENSION_ACCURACY => $this->calculateAccuracyScore($data, $metadata),
            self::DIMENSION_COMPLETENESS => $this->calculateCompletenessScore($data, $metadata),
            self::DIMENSION_CONSISTENCY => $this->calculateConsistencyScore($data, $metadata),
            self::DIMENSION_TIMELINESS => $this->calculateTimelinessScore($data, $metadata),
            self::DIMENSION_VALIDITY => $this->calculateValidityScore($data, $metadata),
        ];

        // Calculate weighted overall score
        $overallScore = $this->calculateWeightedScore($dimensionScores);

        // Determine quality grade
        $grade = $this->determineQualityGrade($overallScore);

        // Generate recommendations
        $recommendations = $this->generateRecommendations($dimensionScores, $overallScore);

        // Compile details
        $details = [
            'data_type' => $dataType,
            'data_size' => is_array($data) ? count($data) : 0,
            'calculation_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
            'timestamp' => now()->toIso8601String(),
        ];

        // Cache quality score
        $this->cacheQualityScore($dataType, $overallScore, $dimensionScores);

        // Record quality history
        $this->recordQualityHistory($dataType, $overallScore, $grade);

        Log::info('[DataQualityScoring] Quality score calculated', [
            'data_type' => $dataType,
            'overall_score' => $overallScore,
            'grade' => $grade,
        ]);

        return [
            'overall_score' => $overallScore,
            'dimensions' => $dimensionScores,
            'grade' => $grade,
            'recommendations' => $recommendations,
            'details' => $details,
        ];
    }

    /**
     * Calculate accuracy score
     *
     * @param  array<string, mixed>  $metadata
     */
    protected function calculateAccuracyScore(mixed $data, array $metadata): float
    {
        if (! is_array($data) || empty($data)) {
            return 0.0;
        }

        $accuracyScore = 100.0;

        // Check for data validation errors
        if (isset($metadata['validation_errors'])) {
            $errorCount = count($metadata['validation_errors']);
            $accuracyScore -= min($errorCount * 5, 50); // Max 50 points penalty
        }

        // Check for data conflicts
        if (isset($metadata['conflicts'])) {
            $conflictCount = count($metadata['conflicts']);
            $accuracyScore -= min($conflictCount * 3, 30); // Max 30 points penalty
        }

        // Check for outliers or anomalies
        $anomalyPenalty = $this->detectAnomalies($data);
        $accuracyScore -= $anomalyPenalty;

        return max(0.0, round($accuracyScore, 2));
    }

    /**
     * Calculate completeness score
     *
     * @param  array<string, mixed>  $metadata
     */
    protected function calculateCompletenessScore(mixed $data, array $metadata): float
    {
        if (! is_array($data) || empty($data)) {
            return 0.0;
        }

        $requiredFields = $metadata['required_fields'] ?? [];

        if (empty($requiredFields)) {
            return 100.0; // No required fields defined
        }

        $totalFields = count($requiredFields) * count($data);
        $filledFields = 0;

        foreach ($data as $item) {
            if (! is_array($item)) {
                continue;
            }

            foreach ($requiredFields as $field) {
                if (isset($item[$field]) && $item[$field] !== '') {
                    $filledFields++;
                }
            }
        }

        $completeness = $totalFields > 0 ? ($filledFields / $totalFields) * 100 : 0.0;

        return round($completeness, 2);
    }

    /**
     * Calculate consistency score
     *
     * @param  array<string, mixed>  $metadata
     */
    protected function calculateConsistencyScore(mixed $data, array $metadata): float
    {
        if (! is_array($data) || empty($data)) {
            return 100.0;
        }

        $consistencyScore = 100.0;

        // Check for data type consistency
        $typeInconsistencies = $this->detectTypeInconsistencies($data);
        $consistencyScore -= min($typeInconsistencies * 2, 30);

        // Check for format consistency
        $formatInconsistencies = $this->detectFormatInconsistencies($data);
        $consistencyScore -= min($formatInconsistencies * 2, 30);

        // Check for value range consistency
        $rangeInconsistencies = $this->detectRangeInconsistencies($data);
        $consistencyScore -= min($rangeInconsistencies * 2, 20);

        return max(0.0, round($consistencyScore, 2));
    }

    /**
     * Calculate timeliness score
     *
     * @param  array<string, mixed>  $metadata
     */
    protected function calculateTimelinessScore(mixed $data, array $metadata): float
    {
        // Check data age
        $dataAge = $metadata['data_age_hours'] ?? 0;

        // Timeliness decreases with age
        // 100% at 0 hours, 50% at 24 hours, 0% at 168 hours (7 days)
        $timelinessScore = max(0, 100 - ($dataAge * 0.595));

        // Check if data has timestamp field
        if (is_array($data) && ! empty($data)) {
            $hasTimestamps = $this->checkTimestampPresence($data);

            if (! $hasTimestamps) {
                $timelinessScore *= 0.8; // 20% penalty for missing timestamps
            }
        }

        return round($timelinessScore, 2);
    }

    /**
     * Calculate validity score
     *
     * @param  array<string, mixed>  $metadata
     */
    protected function calculateValidityScore(mixed $data, array $metadata): float
    {
        if (! is_array($data) || empty($data)) {
            return 0.0;
        }

        $validityScore = 100.0;

        // Check for invalid values
        $invalidCount = 0;
        $totalChecks = 0;

        foreach ($data as $item) {
            if (! is_array($item)) {
                continue;
            }

            foreach ($item as $field => $value) {
                $totalChecks++;

                // Check for obviously invalid values
                if ($this->isInvalidValue($value)) {
                    $invalidCount++;
                }
            }
        }

        if ($totalChecks > 0) {
            $invalidPercentage = ($invalidCount / $totalChecks) * 100;
            $validityScore -= $invalidPercentage;
        }

        // Check for schema violations
        if (isset($metadata['schema_violations'])) {
            $violationCount = count($metadata['schema_violations']);
            $validityScore -= min($violationCount * 5, 30);
        }

        return max(0.0, round($validityScore, 2));
    }

    /**
     * Calculate weighted overall score
     *
     * @param  array<string, float>  $dimensionScores
     */
    protected function calculateWeightedScore(array $dimensionScores): float
    {
        $weightedSum = 0.0;

        foreach ($dimensionScores as $dimension => $score) {
            $weight = $this->dimensionWeights[$dimension] ?? 0.0;
            $weightedSum += $score * $weight;
        }

        return round($weightedSum, 2);
    }

    /**
     * Determine quality grade
     */
    protected function determineQualityGrade(float $score): string
    {
        return match (true) {
            $score >= 90 => 'A',
            $score >= 80 => 'B',
            $score >= 70 => 'C',
            $score >= 60 => 'D',
            default => 'F',
        };
    }

    /**
     * Generate recommendations based on scores
     *
     * @param  array<string, float>  $dimensionScores
     * @return array<string>
     */
    protected function generateRecommendations(array $dimensionScores, float $overallScore): array
    {
        $recommendations = [];

        // Overall score recommendations
        if ($overallScore < 70) {
            $recommendations[] = 'Overall data quality is below acceptable threshold. Immediate action required.';
        } elseif ($overallScore < 85) {
            $recommendations[] = 'Data quality is acceptable but has room for improvement.';
        }

        // Dimension-specific recommendations
        foreach ($dimensionScores as $dimension => $score) {
            if ($score < 70) {
                $recommendations[] = $this->getDimensionRecommendation($dimension, $score);
            }
        }

        if (empty($recommendations)) {
            $recommendations[] = 'Data quality is excellent. Continue current practices.';
        }

        return $recommendations;
    }

    /**
     * Get recommendation for specific dimension
     */
    protected function getDimensionRecommendation(string $dimension, float $score): string
    {
        return match ($dimension) {
            self::DIMENSION_ACCURACY => sprintf(
                'Accuracy score is low (%.2f%%). Review data validation rules and source reliability.',
                $score
            ),
            self::DIMENSION_COMPLETENESS => sprintf(
                'Completeness score is low (%.2f%%). Ensure all required fields are populated.',
                $score
            ),
            self::DIMENSION_CONSISTENCY => sprintf(
                'Consistency score is low (%.2f%%). Standardize data formats and types across sources.',
                $score
            ),
            self::DIMENSION_TIMELINESS => sprintf(
                'Timeliness score is low (%.2f%%). Increase data refresh frequency.',
                $score
            ),
            self::DIMENSION_VALIDITY => sprintf(
                'Validity score is low (%.2f%%). Implement stricter validation rules.',
                $score
            ),
            default => sprintf('Dimension %s needs improvement (%.2f%%).', $dimension, $score),
        };
    }

    /**
     * Detect anomalies in data
     *
     * @param  array<mixed>  $data
     */
    protected function detectAnomalies(array $data): float
    {
        $anomalyCount = 0;

        // Simple anomaly detection - check for extreme outliers
        foreach ($data as $item) {
            if (! is_array($item)) {
                continue;
            }

            foreach ($item as $field => $value) {
                if (is_numeric($value)) {
                    $numericValue = (float) $value;
                    // Check for unreasonably large or small values
                    if (abs($numericValue) > 1000000 || ($numericValue < 0 && $field !== 'id')) {
                        $anomalyCount++;
                    }
                }
            }
        }

        return min($anomalyCount * 2, 20); // Max 20 points penalty
    }

    /**
     * Detect type inconsistencies
     *
     * @param  array<mixed>  $data
     */
    protected function detectTypeInconsistencies(array $data): int
    {
        $fieldTypes = [];
        $inconsistencies = 0;

        foreach ($data as $item) {
            if (! is_array($item)) {
                continue;
            }

            foreach ($item as $field => $value) {
                $type = gettype($value);

                if (! isset($fieldTypes[$field])) {
                    $fieldTypes[$field] = $type;
                } elseif ($fieldTypes[$field] !== $type && $value !== null) {
                    $inconsistencies++;
                }
            }
        }

        return $inconsistencies;
    }

    /**
     * Detect format inconsistencies
     *
     * @param  array<mixed>  $data
     */
    protected function detectFormatInconsistencies(array $data): int
    {
        // Check for date format inconsistencies
        $dateFormats = [];
        $inconsistencies = 0;

        foreach ($data as $item) {
            if (! is_array($item)) {
                continue;
            }

            foreach ($item as $field => $value) {
                if (is_string($value) && $this->looksLikeDate($value)) {
                    $format = $this->detectDateFormat($value);

                    if (! isset($dateFormats[$field])) {
                        $dateFormats[$field] = $format;
                    } elseif ($dateFormats[$field] !== $format) {
                        $inconsistencies++;
                    }
                }
            }
        }

        return $inconsistencies;
    }

    /**
     * Detect range inconsistencies
     *
     * @param  array<mixed>  $data
     */
    protected function detectRangeInconsistencies(array $data): int
    {
        $inconsistencies = 0;

        // Check for values outside expected ranges
        foreach ($data as $item) {
            if (! is_array($item)) {
                continue;
            }

            // Example: Check for negative IDs
            if (isset($item['id']) && is_numeric($item['id']) && $item['id'] < 0) {
                $inconsistencies++;
            }
        }

        return $inconsistencies;
    }

    /**
     * Check if data has timestamp fields
     *
     * @param  array<mixed>  $data
     */
    protected function checkTimestampPresence(array $data): bool
    {
        $timestampFields = ['timestamp', 'created_at', 'updated_at', 'date'];

        foreach ($data as $item) {
            if (! is_array($item)) {
                continue;
            }

            foreach ($timestampFields as $field) {
                if (isset($item[$field])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if value is invalid
     */
    protected function isInvalidValue(mixed $value): bool
    {
        // Check for common invalid values
        if ($value === null || $value === '') {
            return false; // Null/empty is not necessarily invalid
        }

        if (is_string($value)) {
            $lower = strtolower($value);

            return in_array($lower, ['null', 'undefined', 'nan', 'n/a', 'error', 'invalid']);
        }

        if (is_numeric($value)) {
            $numericValue = (float) $value;

            return is_nan($numericValue) || is_infinite($numericValue);
        }

        return false;
    }

    /**
     * Check if string looks like a date
     */
    protected function looksLikeDate(string $value): bool
    {
        return preg_match('/\d{4}-\d{2}-\d{2}/', $value) === 1;
    }

    /**
     * Detect date format
     */
    protected function detectDateFormat(string $value): string
    {
        if (preg_match('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $value)) {
            return 'ISO8601';
        }

        if (preg_match('/\d{4}-\d{2}-\d{2}/', $value)) {
            return 'Y-m-d';
        }

        return 'unknown';
    }

    /**
     * Cache quality score
     *
     * @param  array<string, float>  $dimensionScores
     */
    protected function cacheQualityScore(string $dataType, float $overallScore, array $dimensionScores): void
    {
        $cacheKey = self::QUALITY_SCORE_KEY.$dataType;

        $scoreData = [
            'overall_score' => $overallScore,
            'dimensions' => $dimensionScores,
            'timestamp' => now()->toIso8601String(),
        ];

        Cache::put($cacheKey, $scoreData, 3600); // 1 hour
    }

    /**
     * Record quality history
     */
    protected function recordQualityHistory(string $dataType, float $score, string $grade): void
    {
        $historyKey = self::QUALITY_HISTORY_KEY.$dataType;

        $historyEntry = [
            'score' => $score,
            'grade' => $grade,
            'timestamp' => now()->toIso8601String(),
        ];

        // Get existing history
        $history = Cache::get($historyKey, []);
        if (! is_array($history)) {
            $history = [];
        }
        $history[] = $historyEntry;

        // Keep only last 100 entries
        if (count($history) > 100) {
            array_shift($history);
        }

        Cache::put($historyKey, $history, 86400); // 24 hours
    }

    /**
     * Get quality score for data type
     *
     * @return array<string, mixed>|null
     */
    public function getCachedQualityScore(string $dataType): ?array
    {
        $cacheKey = self::QUALITY_SCORE_KEY.$dataType;

        return Cache::get($cacheKey);
    }

    /**
     * Get quality history for data type
     *
     * @return array<int, array<string, mixed>>
     */
    public function getQualityHistory(string $dataType, int $limit = 10): array
    {
        $historyKey = self::QUALITY_HISTORY_KEY.$dataType;
        $history = Cache::get($historyKey, []);

        return array_slice($history, -$limit);
    }

    /**
     * Get quality trend analysis
     *
     * @return array{trend: string, avg_score: float, score_change: float, grade_distribution: array<string, int>}
     */
    public function getQualityTrend(string $dataType): array
    {
        $history = $this->getQualityHistory($dataType, 20);

        if (empty($history)) {
            return [
                'trend' => 'no_data',
                'avg_score' => 0.0,
                'score_change' => 0.0,
                'grade_distribution' => [],
            ];
        }

        $scores = array_column($history, 'score');
        $avgScore = array_sum($scores) / count($scores);

        // Calculate trend
        $firstScore = $scores[0];
        $lastScore = end($scores);
        $scoreChange = $lastScore - $firstScore;

        $trend = match (true) {
            $scoreChange > 5 => 'improving',
            $scoreChange < -5 => 'declining',
            default => 'stable',
        };

        // Grade distribution
        $grades = array_column($history, 'grade');
        $gradeDistribution = array_count_values($grades);

        return [
            'trend' => $trend,
            'avg_score' => round($avgScore, 2),
            'score_change' => round($scoreChange, 2),
            'grade_distribution' => $gradeDistribution,
        ];
    }
}
