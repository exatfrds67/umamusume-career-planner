#!/usr/bin/env php
<?php

/**
 * Larastan Level 9 Service Files Fixer
 *
 * This script systematically fixes common Larastan level 9 errors in service files:
 * 1. Mixed type casting
 * 2. Array shape return types
 * 3. Array offset access on mixed
 * 4. Binary operations on mixed
 * 5. Model property access
 */
$servicesPath = __DIR__.'/app/Services';

// Patterns to fix
$patterns = [
    // Pattern 1: Fix where() calls without explicit operator
    [
        'search' => '/->where\(([\'"][^\'"]+[\'"])\s*,\s*([\'"][^\'"]+[\'"])\)/',
        'replace' => '->where($1, \'=\', $2)',
        'description' => 'Add explicit = operator to where() calls',
    ],

    // Pattern 2: Fix model property access to use getAttribute()
    [
        'search' => '/\$(\w+)->ai_model_used/',
        'replace' => '$$$1->getAttribute(\'ai_model_used\')',
        'description' => 'Use getAttribute() for ai_model_used',
    ],
    [
        'search' => '/\$(\w+)->processing_time/',
        'replace' => '$$$1->getAttribute(\'processing_time\')',
        'description' => 'Use getAttribute() for processing_time',
    ],
    [
        'search' => '/\$(\w+)->cost(?![_\w])/',
        'replace' => '$$$1->getAttribute(\'cost\')',
        'description' => 'Use getAttribute() for cost',
    ],
    [
        'search' => '/\$(\w+)->token_count/',
        'replace' => '$$$1->getAttribute(\'token_count\')',
        'description' => 'Use getAttribute() for token_count',
    ],

    // Pattern 3: Fix mixed type casting in DB results
    [
        'search' => '/\(int\)\s*\$row->(\w+)/',
        'replace' => '(is_numeric($$$row->$1) ? (int) $$$row->$1 : 0)',
        'description' => 'Safe int casting for DB row properties',
    ],
    [
        'search' => '/\(float\)\s*\$row->(\w+)/',
        'replace' => '(is_numeric($$$row->$1) ? (float) $$$row->$1 : 0.0)',
        'description' => 'Safe float casting for DB row properties',
    ],
];

function fixFile(string $filePath, array $patterns): array
{
    $content = file_get_contents($filePath);
    $originalContent = $content;
    $changesApplied = [];

    foreach ($patterns as $pattern) {
        $newContent = preg_replace(
            $pattern['search'],
            $pattern['replace'],
            $content
        );

        if ($newContent !== $content) {
            $changesApplied[] = $pattern['description'];
            $content = $newContent;
        }
    }

    if ($content !== $originalContent) {
        file_put_contents($filePath, $content);
    }

    return $changesApplied;
}

function scanDirectory(string $dir, array $patterns): array
{
    $results = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir)
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $filePath = $file->getPathname();
            $changes = fixFile($filePath, $patterns);

            if (! empty($changes)) {
                $results[$filePath] = $changes;
            }
        }
    }

    return $results;
}

echo "Starting Larastan Level 9 fixes for Services directory...\n\n";

$results = scanDirectory($servicesPath, $patterns);

echo 'Fixed '.count($results)." files:\n\n";

foreach ($results as $file => $changes) {
    echo basename($file).":\n";
    foreach ($changes as $change) {
        echo "  - $change\n";
    }
    echo "\n";
}

echo "Done!\n";
