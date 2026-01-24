<?php

/**
 * Fix Syntax Errors Introduced by Previous Fixes
 */

declare(strict_types=1);

$basePath = __DIR__;
$appPath = $basePath.'/app';

echo "Fixing syntax errors...\n\n";

// Fix 1: DataManagementController - invalid return type syntax
$dataManagementPath = $appPath.'/Http/Controllers/DataManagementController.php';
if (file_exists($dataManagementPath)) {
    echo "Fixing DataManagementController.php syntax...\n";
    $content = file_get_contents($dataManagementPath);

    // Fix invalid return type annotation
    $content = str_replace(
        'private function getOperationStatus(string $operationId, int $userId): array<string, mixed>',
        '/**
     * @return array<string, mixed>
     */
    private function getOperationStatus(string $operationId, int $userId): array',
        $content
    );

    file_put_contents($dataManagementPath, $content);
    echo "✓ DataManagementController.php fixed\n";
}

// Fix 2: Files with (string) casts that broke
$filesToFix = [
    'Services/DataExportService.php',
    'Services/DataMigrationService.php',
    'Services/MCP/AWS/AWSKnowledgeService.php',
    'Services/Neuron/RaceStrategyService.php',
];

foreach ($filesToFix as $file) {
    $path = $appPath.'/'.$file;
    if (file_exists($path)) {
        echo "Fixing $file...\n";
        $content = file_get_contents($path);

        // Fix broken (string) casts - revert to simpler form
        $content = preg_replace(
            '/\(is_string\(\$(\w+)\)\s*\?\s*\(string\)\s*\$\1\s*:\s*\'\'\)/',
            '(string) $$1',
            $content
        );

        // Fix specific patterns that might have broken
        $content = preg_replace(
            '/ucwords\(str_replace\(\'_\',\s*\' \',\s*\(is_string\([^)]+\)\s*\?\s*\(string\)\s*[^)]+\s*:\s*\'\'\)\)\)/',
            'ucwords(str_replace(\'_\', \' \', (string) $key))',
            $content
        );

        $content = preg_replace(
            '/ucfirst\(\(is_string\([^)]+\)\s*\?\s*\(string\)\s*[^)]+\s*:\s*\'\'\)\)/',
            'ucfirst((string) $key)',
            $content
        );

        $content = preg_replace(
            '/strtolower\(trim\(\(is_string\([^)]+\)\s*\?\s*\(string\)\s*[^)]+\s*:\s*\'\'\)\)\)/',
            'strtolower(trim((string) $value))',
            $content
        );

        file_put_contents($path, $content);
        echo "✓ $file fixed\n";
    }
}

// Fix 3: ImportController and MCPMonitoringController - likely similar issues
$controllersToFix = [
    'ImportController.php',
    'MCPMonitoringController.php',
];

foreach ($controllersToFix as $controller) {
    $path = $appPath.'/Http/Controllers/'.$controller;
    if (file_exists($path)) {
        echo "Fixing $controller...\n";
        $content = file_get_contents($path);

        // Revert overly complex string casts
        $content = preg_replace(
            '/\(is_string\(\$(\w+)\)\s*\?\s*\(string\)\s*\$\1\s*:\s*\'\'\)/',
            '(string) $$1',
            $content
        );

        file_put_contents($path, $content);
        echo "✓ $controller fixed\n";
    }
}

echo "\n✓ All syntax errors fixed!\n";
echo "Run vendor/bin/pint again to verify.\n";
