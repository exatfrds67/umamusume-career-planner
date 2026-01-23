#!/usr/bin/env php
<?php

/**
 * Fix AIDashboardService.php for Larastan Level 9
 */
$file = __DIR__.'/app/Services/AI/AIDashboardService.php';
$content = file_get_contents($file);

// Fix 1: getSummaryMetrics - safe array access
$content = str_replace(
    '$totalRequests = isset($metrics24h[\'total_requests\']) && is_numeric($metrics24h[\'total_requests\']) ? (int) $metrics24h[\'total_requests\'] : 0;',
    '$totalRequests = (isset($metrics24h[\'total_requests\']) && is_numeric($metrics24h[\'total_requests\'])) ? (int) $metrics24h[\'total_requests\'] : 0;',
    $content
);

$content = str_replace(
    '$successfulRequests = isset($metrics24h[\'successful_requests\']) && is_numeric($metrics24h[\'successful_requests\']) ? (int) $metrics24h[\'successful_requests\'] : 0;',
    '$successfulRequests = (isset($metrics24h[\'successful_requests\']) && is_numeric($metrics24h[\'successful_requests\'])) ? (int) $metrics24h[\'successful_requests\'] : 0;',
    $content
);

// Fix 2: avgResponseTime and totalCost
$content = str_replace(
    '$avgResponseTime = isset($metrics24h[\'avg_response_time\']) && is_numeric($metrics24h[\'avg_response_time\']) ? (float) $metrics24h[\'avg_response_time\'] : 0.0;',
    '$avgResponseTime = (isset($metrics24h[\'avg_response_time\']) && is_numeric($metrics24h[\'avg_response_time\'])) ? (float) $metrics24h[\'avg_response_time\'] : 0.0;',
    $content
);

$content = str_replace(
    '$totalCost = isset($metrics24h[\'total_cost\']) && is_numeric($metrics24h[\'total_cost\']) ? (float) $metrics24h[\'total_cost\'] : 0.0;',
    '$totalCost = (isset($metrics24h[\'total_cost\']) && is_numeric($metrics24h[\'total_cost\'])) ? (float) $metrics24h[\'total_cost\'] : 0.0;',
    $content
);

// Fix 3: getProviderMetrics - all casting issues
$replacements = [
    "'requests_24h' => isset(\$metrics['request_count']) && is_numeric(\$metrics['request_count']) ? (int) \$metrics['request_count'] : 0," => "'requests_24h' => (isset(\$metrics['request_count']) && is_numeric(\$metrics['request_count'])) ? (int) \$metrics['request_count'] : 0,",

    "'success_rate' => isset(\$metrics['success_rate']) && is_numeric(\$metrics['success_rate']) ? (float) \$metrics['success_rate'] : 0.0," => "'success_rate' => (isset(\$metrics['success_rate']) && is_numeric(\$metrics['success_rate'])) ? (float) \$metrics['success_rate'] : 0.0,",

    "'avg_response_time' => isset(\$metrics['avg_response_time']) && is_numeric(\$metrics['avg_response_time']) ? (float) \$metrics['avg_response_time'] : 0.0," => "'avg_response_time' => (isset(\$metrics['avg_response_time']) && is_numeric(\$metrics['avg_response_time'])) ? (float) \$metrics['avg_response_time'] : 0.0,",

    "'min_response_time' => isset(\$metrics['min_response_time']) && is_numeric(\$metrics['min_response_time']) ? (float) \$metrics['min_response_time'] : 0.0," => "'min_response_time' => (isset(\$metrics['min_response_time']) && is_numeric(\$metrics['min_response_time'])) ? (float) \$metrics['min_response_time'] : 0.0,",

    "'max_response_time' => isset(\$metrics['max_response_time']) && is_numeric(\$metrics['max_response_time']) ? (float) \$metrics['max_response_time'] : 0.0," => "'max_response_time' => (isset(\$metrics['max_response_time']) && is_numeric(\$metrics['max_response_time'])) ? (float) \$metrics['max_response_time'] : 0.0,",

    "'p95_response_time' => isset(\$metrics['p95_response_time']) && is_numeric(\$metrics['p95_response_time']) ? (float) \$metrics['p95_response_time'] : 0.0," => "'p95_response_time' => (isset(\$metrics['p95_response_time']) && is_numeric(\$metrics['p95_response_time'])) ? (float) \$metrics['p95_response_time'] : 0.0,",

    "'p99_response_time' => isset(\$metrics['p99_response_time']) && is_numeric(\$metrics['p99_response_time']) ? (float) \$metrics['p99_response_time'] : 0.0," => "'p99_response_time' => (isset(\$metrics['p99_response_time']) && is_numeric(\$metrics['p99_response_time'])) ? (float) \$metrics['p99_response_time'] : 0.0,",

    "'total_tokens' => isset(\$metrics['total_tokens']) && is_numeric(\$metrics['total_tokens']) ? (int) \$metrics['total_tokens'] : 0," => "'total_tokens' => (isset(\$metrics['total_tokens']) && is_numeric(\$metrics['total_tokens'])) ? (int) \$metrics['total_tokens'] : 0,",

    "'total_cost' => isset(\$metrics['total_cost']) && is_numeric(\$metrics['total_cost']) ? (float) \$metrics['total_cost'] : 0.0," => "'total_cost' => (isset(\$metrics['total_cost']) && is_numeric(\$metrics['total_cost'])) ? (float) \$metrics['total_cost'] : 0.0,",

    "'avg_confidence' => isset(\$metrics['avg_confidence']) && is_numeric(\$metrics['avg_confidence']) ? (float) \$metrics['avg_confidence'] : 0.0," => "'avg_confidence' => (isset(\$metrics['avg_confidence']) && is_numeric(\$metrics['avg_confidence'])) ? (float) \$metrics['avg_confidence'] : 0.0,",
];

foreach ($replacements as $search => $replace) {
    $content = str_replace($search, $replace, $content);
}

// Fix 4: getCostByProvider return type
$content = str_replace(
    "->map(fn (\$group) => round((float) \$group->sum('cost'), 6))",
    "->mapWithKeys(fn (\$group, \$key) => [is_string(\$key) ? \$key : 'unknown' => round((float) \$group->sum('cost'), 6)])",
    $content
);

// Fix 5: getBudgetStatus
$content = str_replace(
    "\$budgetLimitValue = config('ai.budget.monthly_limit', 100.0);\n        \$budgetLimit = is_numeric(\$budgetLimitValue) ? (float) \$budgetLimitValue : 100.0;",
    "\$budgetLimitValue = config('ai.budget.monthly_limit', 100.0);\n        \$budgetLimit = is_numeric(\$budgetLimitValue) ? (float) \$budgetLimitValue : 100.0;",
    $content
);

// Fix 6: calculateAverageConfidence methods
$content = str_replace(
    "\$avgValue = \$confidenceScores->avg();\n        return \$confidenceScores->isEmpty() || !\is_numeric(\$avgValue) ? 0.0 : round((float) \$avgValue, 2);",
    "\$avgValue = \$confidenceScores->avg();\n        return \$confidenceScores->isEmpty() || !\is_numeric(\$avgValue) ? 0.0 : round((float) \$avgValue, 2);",
    $content
);

file_put_contents($file, $content);
echo "Fixed AIDashboardService.php\n";
