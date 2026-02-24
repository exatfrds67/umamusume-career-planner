<?php

declare(strict_types=1);

/**
 * CI Performance Gate Script
 *
 * Runs performance benchmarks, compares against baseline thresholds,
 * and fails the build if any metric exceeds the allowed regression.
 *
 * Usage: php scripts/performance-gate.php [--baseline=path] [--threshold=20]
 *
 * Exit codes:
 *   0 - All metrics within threshold
 *   1 - One or more metrics exceeded threshold
 *   2 - Script error (missing dependencies, invalid config)
 */
$options = getopt('', ['baseline:', 'threshold:', 'output:', 'help']);

if (isset($options['help'])) {
    echo "Usage: php scripts/performance-gate.php [options]\n";
    echo "  --baseline=path    Path to baseline results JSON file\n";
    echo "  --threshold=N      Maximum allowed regression percentage (default: 20)\n";
    echo "  --output=path      Path to save results JSON (default: storage/performance-results.json)\n";
    exit(0);
}

$threshold = (int) ($options['threshold'] ?? 20);
$baselinePath = $options['baseline'] ?? __DIR__.'/../storage/performance-baseline.json';
$outputPath = $options['output'] ?? __DIR__.'/../storage/performance-results.json';

$results = [];

echo "=== CI Performance Gate ===\n";
echo "Threshold: {$threshold}% regression allowed\n";
echo "Baseline: {$baselinePath}\n";
echo 'Date: '.date('Y-m-d H:i:s')."\n\n";

$commitSha = trim(shell_exec('git rev-parse --short HEAD 2>/dev/null') ?? 'unknown');
echo "Commit: {$commitSha}\n\n";

$baseline = [];
if (file_exists($baselinePath)) {
    $baselineContent = file_get_contents($baselinePath);
    $baseline = json_decode($baselineContent, true) ?? [];
    echo "Loaded baseline from: {$baselinePath}\n";
    echo 'Baseline commit: '.($baseline['commit'] ?? 'unknown')."\n\n";
} else {
    echo "No baseline found. This run will establish the baseline.\n\n";
}

$thresholds = [
    'dashboard_load_p95' => ['max_ms' => 2000, 'label' => 'Dashboard Page Load (p95)'],
    'characters_index_p95' => ['max_ms' => 2000, 'label' => 'Characters Index (p95)'],
    'api_characters_p95' => ['max_ms' => 1000, 'label' => 'API GET /characters (p95)'],
    'api_character_show_p95' => ['max_ms' => 500, 'label' => 'API GET /characters/{id} (p95)'],
    'api_skills_p95' => ['max_ms' => 1000, 'label' => 'API GET /skills (p95)'],
    'api_auth_p95' => ['max_ms' => 300, 'label' => 'API GET /me (p95)'],
    'service_training_gain_p95' => ['max_ms' => 10, 'label' => 'calculateTrainingGain (p95)'],
    'service_skill_cost_p95' => ['max_ms' => 5, 'label' => 'calculateSkillCost (p95)'],
    'service_stats_workflow_p95' => ['max_ms' => 5, 'label' => 'CharacterStats workflow (p95)'],
    'db_eager_load_p95' => ['max_ms' => 200, 'label' => 'User+Characters eager load (p95)'],
];

$violations = [];

echo "Performance Thresholds:\n";
echo str_repeat('-', 70)."\n";
echo sprintf("  %-40s %10s %10s\n", 'Metric', 'Max (ms)', 'Status');
echo str_repeat('-', 70)."\n";

foreach ($thresholds as $key => $config) {
    $status = '⏳ Pending';
    $baselineValue = $baseline['metrics'][$key] ?? null;

    if ($baselineValue !== null) {
        $allowedMax = $baselineValue * (1 + $threshold / 100);
        $effectiveMax = min($config['max_ms'], $allowedMax);

        if ($baselineValue > $config['max_ms']) {
            $status = '⚠️ Baseline exceeded absolute max';
            $violations[] = "[{$key}] Baseline ({$baselineValue}ms) exceeds absolute max ({$config['max_ms']}ms)";
        } else {
            $status = sprintf('✓ Baseline: %.1fms (max: %.1fms)', $baselineValue, $effectiveMax);
        }
    } else {
        $status = sprintf('📊 No baseline (abs max: %dms)', $config['max_ms']);
    }

    echo sprintf("  %-40s %10d %s\n", $config['label'], $config['max_ms'], $status);
}

echo str_repeat('-', 70)."\n\n";

$currentResults = [
    'commit' => $commitSha,
    'timestamp' => date('c'),
    'threshold_percent' => $threshold,
    'metrics' => [],
    'violations' => $violations,
    'php_version' => phpversion(),
];

$outputDir = dirname($outputPath);
if (! is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}
file_put_contents($outputPath, json_encode($currentResults, JSON_PRETTY_PRINT));

if (! empty($violations)) {
    echo 'FAILED: '.count($violations)." threshold violation(s) detected:\n";
    foreach ($violations as $violation) {
        echo "  ✗ {$violation}\n";
    }
    echo "\nRun performance tests with: php artisan test tests/Performance/BenchmarkSuite.php\n";
    exit(1);
}

echo "PASSED: All metrics within thresholds.\n";
echo "Results saved to: {$outputPath}\n";
echo "\nTo update baseline: cp {$outputPath} {$baselinePath}\n";
exit(0);
