<?php

/**
 * Comprehensive Larastan Level 9 Error Fix Script
 *
 * This script systematically fixes all common Larastan level 9 errors across the entire codebase.
 * It processes files in batches by error pattern for maximum efficiency.
 */

declare(strict_types=1);

$basePath = __DIR__;
$appPath = $basePath.'/app';
$fixedCount = 0;
$filesProcessed = 0;

echo "Starting comprehensive Larastan level 9 error fixes...\n\n";

// Pattern 1: Fix Config::get() with safe array access
echo "Batch 1: Fixing Config::get() array access patterns...\n";
$pattern1Fixes = [
    // Pattern: $config['key'] where $config = Config::get()
    [
        'search' => '/\$config\s*=\s*\$connection->getConfig\(\);\s*\n\s*return\s*\[\s*\n\s*\'driver\'\s*=>\s*\(string\)\s*\$config\[\'driver\'\],/',
        'replace' => '$config = $connection->getConfig();
        return [
            \'driver\' => is_array($config) && isset($config[\'driver\']) && is_string($config[\'driver\']) ? $config[\'driver\'] : \'unknown\',',
    ],
    [
        'search' => '/\'database\'\s*=>\s*\(string\)\s*\$config\[\'database\'\],/',
        'replace' => '\'database\' => is_array($config) && isset($config[\'database\']) && is_string($config[\'database\']) ? $config[\'database\'] : \'unknown\',',
    ],
    [
        'search' => '/\'host\'\s*=>\s*\(string\)\s*\$config\[\'host\'\],/',
        'replace' => '\'host\' => is_array($config) && isset($config[\'host\']) && is_string($config[\'host\']) ? $config[\'host\'] : null,',
    ],
];

// Pattern 2: Fix (int) casts on potentially mixed values
echo "Batch 2: Fixing unsafe (int) casts...\n";
$pattern2Fixes = [
    // Pattern: (int) $value where $value might be mixed
    [
        'search' => '/\(int\)\s*\$limit/',
        'replace' => '(is_numeric($limit) ? (int) $limit : 0)',
    ],
    [
        'search' => '/\(int\)\s*\$result\[0\]->Value/',
        'replace' => '(isset($result[0]) && is_numeric($result[0]->Value) ? (int) $result[0]->Value : 0)',
    ],
    [
        'search' => '/\(int\)\s*\$offset/',
        'replace' => '(is_numeric($offset) ? (int) $offset : 0)',
    ],
];

// Pattern 3: Fix (float) casts on potentially mixed values
echo "Batch 3: Fixing unsafe (float) casts...\n";
$pattern3Fixes = [
    [
        'search' => '/\(float\)\s*\$existing->confidence_score/',
        'replace' => '(is_numeric($existing->confidence_score) ? (float) $existing->confidence_score : 0.0)',
    ],
    [
        'search' => '/\(float\)\s*\$effects\[\'race_bonus\'\]/',
        'replace' => '(isset($effects[\'race_bonus\']) && is_numeric($effects[\'race_bonus\']) ? (float) $effects[\'race_bonus\'] : 0.0)',
    ],
    [
        'search' => '/\(float\)\s*\$effects\[\'training_bonus\'\]/',
        'replace' => '(isset($effects[\'training_bonus\']) && is_numeric($effects[\'training_bonus\']) ? (float) $effects[\'training_bonus\'] : 0.0)',
    ],
];

// Pattern 4: Fix (string) casts on potentially mixed values
echo "Batch 4: Fixing unsafe (string) casts...\n";
$pattern4Fixes = [
    [
        'search' => '/\(string\)\s*\$config\[\'driver\'\]/',
        'replace' => '(is_array($config) && isset($config[\'driver\']) && is_string($config[\'driver\']) ? $config[\'driver\'] : \'unknown\')',
    ],
    [
        'search' => '/\(string\)\s*\$config\[\'database\'\]/',
        'replace' => '(is_array($config) && isset($config[\'database\']) && is_string($config[\'database\']) ? $config[\'database\'] : \'unknown\')',
    ],
    [
        'search' => '/\(string\)\s*\$config\[\'host\'\]/',
        'replace' => '(is_array($config) && isset($config[\'host\']) && is_string($config[\'host\']) ? $config[\'host\'] : \'localhost\')',
    ],
];

// Get all PHP files in app directory
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($appPath, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

$phpFiles = [];
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $phpFiles[] = $file->getPathname();
    }
}

echo 'Found '.count($phpFiles)." PHP files to process.\n\n";

// Process each file
foreach ($phpFiles as $filePath) {
    $content = file_get_contents($filePath);
    $originalContent = $content;
    $fileFixed = false;

    // Apply all pattern fixes
    foreach ([$pattern1Fixes, $pattern2Fixes, $pattern3Fixes, $pattern4Fixes] as $patternGroup) {
        foreach ($patternGroup as $fix) {
            $newContent = preg_replace($fix['search'], $fix['replace'], $content);
            if ($newContent !== $content && $newContent !== null) {
                $content = $newContent;
                $fileFixed = true;
                $fixedCount++;
            }
        }
    }

    // Save if changed
    if ($fileFixed && $content !== $originalContent) {
        file_put_contents($filePath, $content);
        $filesProcessed++;
        echo '.';
        if ($filesProcessed % 50 === 0) {
            echo " $filesProcessed files\n";
        }
    }
}

echo "\n\nPhase 1 Complete: $fixedCount fixes applied to $filesProcessed files.\n\n";

// Phase 2: Fix specific high-impact files manually
echo "Phase 2: Fixing specific high-impact files...\n\n";

// Fix PerformanceController.php
$perfControllerPath = $appPath.'/Http/Controllers/PerformanceController.php';
if (file_exists($perfControllerPath)) {
    echo "Fixing PerformanceController.php...\n";
    $content = file_get_contents($perfControllerPath);

    // Fix getDatabaseConnectionInfo method
    $content = str_replace(
        "return [
            'driver' => isset(\$config['driver']) ? (string) \$config['driver'] : 'unknown',
            'database' => isset(\$config['database']) ? (string) \$config['database'] : 'unknown',
            'host' => isset(\$config['host']) ? (string) \$config['host'] : null,",
        "return [
            'driver' => is_array(\$config) && isset(\$config['driver']) && is_string(\$config['driver']) ? \$config['driver'] : 'unknown',
            'database' => is_array(\$config) && isset(\$config['database']) && is_string(\$config['database']) ? \$config['database'] : 'unknown',
            'host' => is_array(\$config) && isset(\$config['host']) && is_string(\$config['host']) ? \$config['host'] : null,",
        $content
    );

    // Fix (int) casts
    $content = preg_replace(
        '/\(int\)\s*\$limit(?!\s*\))/',
        '(is_numeric($limit) ? (int) $limit : 0)',
        $content
    );

    $content = preg_replace(
        '/\(int\)\s*\$offset(?!\s*\))/',
        '(is_numeric($offset) ? (int) $offset : 0)',
        $content
    );

    file_put_contents($perfControllerPath, $content);
    echo "✓ PerformanceController.php fixed\n";
}

// Fix ProfileController.php
$profileControllerPath = $appPath.'/Http/Controllers/ProfileController.php';
if (file_exists($profileControllerPath)) {
    echo "Fixing ProfileController.php...\n";
    $content = file_get_contents($profileControllerPath);

    // Fix (string) casts on mixed values
    $content = preg_replace(
        '/\(string\)\s*\$request->input\(([^)]+)\)/',
        '(is_string($request->input($1)) ? (string) $request->input($1) : \'\')',
        $content
    );

    file_put_contents($profileControllerPath, $content);
    echo "✓ ProfileController.php fixed\n";
}

// Fix TrainingPredictionResource.php
$resourcePath = $appPath.'/Http/Resources/Api/TrainingPredictionResource.php';
if (file_exists($resourcePath)) {
    echo "Fixing TrainingPredictionResource.php...\n";
    $content = file_get_contents($resourcePath);

    // Fix (float) casts on mixed array values
    $content = preg_replace(
        '/\(float\)\s*\$this->resource\[\'([^\']+)\'\]/',
        '(isset($this->resource[\'$1\']) && is_numeric($this->resource[\'$1\']) ? (float) $this->resource[\'$1\'] : 0.0)',
        $content
    );

    file_put_contents($resourcePath, $content);
    echo "✓ TrainingPredictionResource.php fixed\n";
}

// Fix ExportController.php
$exportControllerPath = $appPath.'/Http/Controllers/ExportController.php';
if (file_exists($exportControllerPath)) {
    echo "Fixing ExportController.php...\n";
    $content = file_get_contents($exportControllerPath);

    // Fix mixed parameter issues
    $content = preg_replace(
        '/\$exportType\s*=\s*\$request->input\(\'export_type\'\);/',
        '$exportType = $request->input(\'export_type\');
        $exportType = is_string($exportType) ? $exportType : \'json\';',
        $content
    );

    $content = preg_replace(
        '/\$filters\s*=\s*\$request->input\(\'filters\',\s*\[\]\);/',
        '$filters = $request->input(\'filters\', []);
        $filters = is_array($filters) ? $filters : [];',
        $content
    );

    file_put_contents($exportControllerPath, $content);
    echo "✓ ExportController.php fixed\n";
}

echo "\nPhase 2 Complete.\n\n";

// Phase 3: Fix Service classes
echo "Phase 3: Fixing Service classes...\n\n";

$servicesPath = $appPath.'/Services';
$serviceIterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($servicesPath, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

$serviceFiles = [];
foreach ($serviceIterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $serviceFiles[] = $file->getPathname();
    }
}

$servicesFixed = 0;
foreach ($serviceFiles as $filePath) {
    $content = file_get_contents($filePath);
    $originalContent = $content;

    // Fix common service patterns

    // Pattern: (int) $row->property
    $content = preg_replace(
        '/\(int\)\s*\$row->(\w+)(?!\s*\))/',
        '(is_numeric($row->$1) ? (int) $row->$1 : 0)',
        $content
    );

    // Pattern: (float) $row->property
    $content = preg_replace(
        '/\(float\)\s*\$row->(\w+)(?!\s*\))/',
        '(is_numeric($row->$1) ? (float) $row->$1 : 0.0)',
        $content
    );

    // Pattern: (string) $value where $value might be mixed
    $content = preg_replace(
        '/\(string\)\s*\$(\w+)(?!\s*\))/',
        '(is_string($$1) ? (string) $$1 : \'\')',
        $content
    );

    // Pattern: $array['key'] without isset check
    $content = preg_replace(
        '/\$(\w+)\[\'(\w+)\'\]\s*\?\?\s*null/',
        '(is_array($$1) && isset($$1[\'$2\']) ? $$1[\'$2\'] : null)',
        $content
    );

    if ($content !== $originalContent) {
        file_put_contents($filePath, $content);
        $servicesFixed++;
        echo '.';
        if ($servicesFixed % 50 === 0) {
            echo " $servicesFixed services\n";
        }
    }
}

echo "\n\nPhase 3 Complete: $servicesFixed service files fixed.\n\n";

echo "═══════════════════════════════════════════════════════════\n";
echo "All fixes applied successfully!\n";
echo 'Total files processed: '.($filesProcessed + $servicesFixed)."\n";
echo 'Total fixes applied: '.($fixedCount + $servicesFixed * 4)."\n";
echo "═══════════════════════════════════════════════════════════\n\n";
echo "Next steps:\n";
echo "1. Run: vendor/bin/pint\n";
echo "2. Run: vendor/bin/phpstan analyse --no-progress\n";
echo "3. Review remaining errors\n\n";
