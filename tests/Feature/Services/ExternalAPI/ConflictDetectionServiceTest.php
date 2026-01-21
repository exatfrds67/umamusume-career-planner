<?php

declare(strict_types=1);

use App\Services\ExternalAPI\ConflictDetectionService;

beforeEach(function () {
    $this->service = new ConflictDetectionService;
});

describe('ConflictDetectionService', function () {
    describe('detectConflicts', function () {
        it('returns no conflicts when only one source is provided', function () {
            $sources = [
                'umapyoi' => [
                    'data' => ['id' => 1, 'name' => 'Special Week'],
                    'source' => 'umapyoi',
                ],
            ];

            $result = $this->service->detectConflicts($sources);

            expect($result['has_conflicts'])->toBeFalse();
            expect($result['conflicts'])->toBeEmpty();
            expect($result['summary']['total_conflicts'])->toBe(0);
        });

        it('returns no conflicts when sources have identical data', function () {
            $sources = [
                'umapyoi' => [
                    'data' => ['id' => 1, 'name' => 'Special Week', 'rarity' => 'SSR'],
                    'source' => 'umapyoi',
                ],
                'umamusumedb' => [
                    'data' => ['id' => 1, 'name' => 'Special Week', 'rarity' => 'SSR'],
                    'source' => 'umamusumedb',
                ],
            ];

            $result = $this->service->detectConflicts($sources);

            expect($result['has_conflicts'])->toBeFalse();
            expect($result['conflicts'])->toBeEmpty();
        });

        it('detects field value conflicts between sources', function () {
            $sources = [
                'umapyoi' => [
                    'data' => ['id' => 1, 'name' => 'Special Week', 'speed' => 100],
                    'source' => 'umapyoi',
                ],
                'umamusumedb' => [
                    'data' => ['id' => 1, 'name' => 'Special Week', 'speed' => 105],
                    'source' => 'umamusumedb',
                ],
            ];

            $result = $this->service->detectConflicts($sources);

            expect($result['has_conflicts'])->toBeTrue();
            expect($result['conflicts'])->not->toBeEmpty();
        });

        it('detects conflicts in list data with different values', function () {
            $sources = [
                'umapyoi' => [
                    'data' => [
                        ['id' => 1, 'name' => 'Special Week', 'speed' => 100],
                        ['id' => 2, 'name' => 'Silence Suzuka', 'speed' => 110],
                    ],
                    'source' => 'umapyoi',
                ],
                'umamusumedb' => [
                    'data' => [
                        ['id' => 1, 'name' => 'Special Week', 'speed' => 105],
                        ['id' => 2, 'name' => 'Silence Suzuka', 'speed' => 110],
                    ],
                    'source' => 'umamusumedb',
                ],
            ];

            $result = $this->service->detectConflicts($sources);

            expect($result['has_conflicts'])->toBeTrue();
            expect($result['summary']['total_conflicts'])->toBeGreaterThan(0);
        });

        it('detects missing items between sources', function () {
            $sources = [
                'umapyoi' => [
                    'data' => [
                        ['id' => 1, 'name' => 'Special Week'],
                        ['id' => 2, 'name' => 'Silence Suzuka'],
                    ],
                    'source' => 'umapyoi',
                ],
                'umamusumedb' => [
                    'data' => [
                        ['id' => 1, 'name' => 'Special Week'],
                    ],
                    'source' => 'umamusumedb',
                ],
            ];

            $result = $this->service->detectConflicts($sources);

            expect($result['has_conflicts'])->toBeTrue();

            // Check for missing items conflict in the nested structure
            $hasMissingConflict = false;
            foreach ($result['conflicts'] as $pairKey => $pairConflicts) {
                foreach ($pairConflicts as $conflictKey => $conflict) {
                    if (($conflict['type'] ?? '') === 'missing_items') {
                        $hasMissingConflict = true;
                        break 2;
                    }
                }
            }
            expect($hasMissingConflict)->toBeTrue();
        });
    });

    describe('calculateConfidence', function () {
        it('returns correct confidence for known sources', function () {
            $sourceData = ['data' => [], 'source' => 'umapyoi'];

            expect($this->service->calculateConfidence('umapyoi', $sourceData))->toBe(0.95);
            expect($this->service->calculateConfidence('umamusumedb', $sourceData))->toBe(0.85);
            expect($this->service->calculateConfidence('umalator', $sourceData))->toBe(0.80);
            expect($this->service->calculateConfidence('cache', $sourceData))->toBe(0.70);
        });

        it('returns default confidence for unknown sources', function () {
            $sourceData = ['data' => [], 'source' => 'unknown_source'];

            expect($this->service->calculateConfidence('unknown_source', $sourceData))->toBe(0.50);
        });

        it('adjusts confidence based on data freshness', function () {
            $freshData = [
                'data' => [],
                'source' => 'umapyoi',
                'timestamp' => now()->toIso8601String(),
            ];

            $staleData = [
                'data' => [],
                'source' => 'umapyoi',
                'timestamp' => now()->subDays(30)->toIso8601String(),
            ];

            $freshConfidence = $this->service->calculateConfidence('umapyoi', $freshData);
            $staleConfidence = $this->service->calculateConfidence('umapyoi', $staleData);

            expect($freshConfidence)->toBeGreaterThan($staleConfidence);
        });
    });

    describe('resolveConflicts', function () {
        it('resolves conflicts using highest confidence strategy', function () {
            $conflicts = [
                'umapyoi_vs_umamusumedb' => [
                    'type' => 'field_mismatch',
                    'source1' => 'umapyoi',
                    'source2' => 'umamusumedb',
                    'source1_confidence' => 0.95,
                    'source2_confidence' => 0.85,
                    'fields' => ['speed' => ['value1' => 100, 'value2' => 105]],
                    'severity' => 'medium',
                ],
            ];

            $sources = [
                'umapyoi' => [
                    'data' => ['id' => 1, 'name' => 'Special Week', 'speed' => 100],
                    'source' => 'umapyoi',
                ],
                'umamusumedb' => [
                    'data' => ['id' => 1, 'name' => 'Special Week', 'speed' => 105],
                    'source' => 'umamusumedb',
                ],
            ];

            $result = $this->service->resolveConflicts(
                $conflicts,
                $sources,
                ConflictDetectionService::STRATEGY_HIGHEST_CONFIDENCE
            );

            expect($result['primary_source'])->toBe('umapyoi');
            expect($result['strategy_used'])->toBe(ConflictDetectionService::STRATEGY_HIGHEST_CONFIDENCE);
        });

        it('resolves conflicts using priority strategy', function () {
            $conflicts = [];
            $sources = [
                'umamusumedb' => [
                    'data' => ['id' => 1, 'name' => 'Special Week'],
                    'source' => 'umamusumedb',
                ],
                'umapyoi' => [
                    'data' => ['id' => 1, 'name' => 'Special Week'],
                    'source' => 'umapyoi',
                ],
            ];

            $result = $this->service->resolveConflicts(
                $conflicts,
                $sources,
                ConflictDetectionService::STRATEGY_PRIORITY
            );

            expect($result['primary_source'])->toBe('umapyoi');
        });

        it('resolves conflicts using newest strategy', function () {
            $conflicts = [];
            $sources = [
                'umapyoi' => [
                    'data' => ['id' => 1, 'name' => 'Special Week'],
                    'source' => 'umapyoi',
                    'timestamp' => now()->subHours(2)->toIso8601String(),
                ],
                'umamusumedb' => [
                    'data' => ['id' => 1, 'name' => 'Special Week'],
                    'source' => 'umamusumedb',
                    'timestamp' => now()->toIso8601String(),
                ],
            ];

            $result = $this->service->resolveConflicts(
                $conflicts,
                $sources,
                ConflictDetectionService::STRATEGY_NEWEST
            );

            expect($result['primary_source'])->toBe('umamusumedb');
        });
    });

    describe('validateAndResolve', function () {
        it('returns data from highest confidence source when no conflicts', function () {
            $sources = [
                'umapyoi' => [
                    'data' => ['id' => 1, 'name' => 'Special Week'],
                    'source' => 'umapyoi',
                ],
                'umamusumedb' => [
                    'data' => ['id' => 1, 'name' => 'Special Week'],
                    'source' => 'umamusumedb',
                ],
            ];

            $result = $this->service->validateAndResolve($sources);

            expect($result['has_conflicts'])->toBeFalse();
            expect($result['data'])->toBe(['id' => 1, 'name' => 'Special Week']);
            expect($result['resolution']['primary_source'])->toBe('umapyoi');
        });

        it('resolves conflicts and returns merged data', function () {
            $sources = [
                'umapyoi' => [
                    'data' => ['id' => 1, 'name' => 'Special Week', 'speed' => 100],
                    'source' => 'umapyoi',
                ],
                'umamusumedb' => [
                    'data' => ['id' => 1, 'name' => 'Special Week', 'speed' => 105],
                    'source' => 'umamusumedb',
                ],
            ];

            $result = $this->service->validateAndResolve($sources);

            expect($result['has_conflicts'])->toBeTrue();
            expect($result['data'])->not->toBeEmpty();
            expect($result['resolution'])->not->toBeEmpty();
        });
    });

    describe('getConflictStatistics', function () {
        it('returns empty statistics when no conflicts detected', function () {
            $stats = $this->service->getConflictStatistics();

            expect($stats['total'])->toBe(0);
            expect($stats['detection_count'])->toBe(0);
        });

        it('tracks conflict statistics after detection', function () {
            $sources = [
                'umapyoi' => [
                    'data' => ['id' => 1, 'name' => 'Special Week', 'speed' => 100],
                    'source' => 'umapyoi',
                ],
                'umamusumedb' => [
                    'data' => ['id' => 1, 'name' => 'Special Week', 'speed' => 105],
                    'source' => 'umamusumedb',
                ],
            ];

            $this->service->detectConflicts($sources);
            $stats = $this->service->getConflictStatistics();

            expect($stats['detection_count'])->toBe(1);
        });
    });

    describe('getConflictHistory', function () {
        it('returns empty history initially', function () {
            $history = $this->service->getConflictHistory();

            expect($history)->toBeEmpty();
        });

        it('stores conflict history after detection', function () {
            $sources = [
                'umapyoi' => [
                    'data' => ['id' => 1, 'name' => 'Special Week', 'speed' => 100],
                    'source' => 'umapyoi',
                ],
                'umamusumedb' => [
                    'data' => ['id' => 1, 'name' => 'Special Week', 'speed' => 105],
                    'source' => 'umamusumedb',
                ],
            ];

            $this->service->detectConflicts($sources);
            $history = $this->service->getConflictHistory();

            expect($history)->not->toBeEmpty();
            expect($history[0])->toHaveKeys(['timestamp', 'sources', 'conflict_count']);
        });
    });

    describe('clearHistory', function () {
        it('clears conflict history', function () {
            $sources = [
                'umapyoi' => [
                    'data' => ['id' => 1, 'speed' => 100],
                    'source' => 'umapyoi',
                ],
                'umamusumedb' => [
                    'data' => ['id' => 1, 'speed' => 105],
                    'source' => 'umamusumedb',
                ],
            ];

            $this->service->detectConflicts($sources);
            expect($this->service->getConflictHistory())->not->toBeEmpty();

            $this->service->clearHistory();
            expect($this->service->getConflictHistory())->toBeEmpty();
        });
    });

    describe('getSourceConfidence', function () {
        it('returns correct confidence for each source', function () {
            expect($this->service->getSourceConfidence('umapyoi'))->toBe(0.95);
            expect($this->service->getSourceConfidence('umamusumedb'))->toBe(0.85);
            expect($this->service->getSourceConfidence('umalator'))->toBe(0.80);
            expect($this->service->getSourceConfidence('cache'))->toBe(0.70);
            expect($this->service->getSourceConfidence('manual'))->toBe(1.00);
            expect($this->service->getSourceConfidence('unknown'))->toBe(0.50);
        });
    });

    describe('getAllSourceConfidences', function () {
        it('returns all source confidence scores', function () {
            $confidences = $this->service->getAllSourceConfidences();

            expect($confidences)->toHaveKeys(['umapyoi', 'umamusumedb', 'umalator', 'cache', 'manual', 'unknown']);
            expect($confidences['umapyoi'])->toBe(0.95);
        });
    });
});
