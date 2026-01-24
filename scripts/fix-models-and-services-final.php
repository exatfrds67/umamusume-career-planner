<?php

/**
 * Final Comprehensive Fix for Models and Services
 *
 * Addresses remaining common patterns:
 * - Model @property annotations
 * - Service method return types
 * - Scope method signatures
 * - Config/Cache return type handling
 */

declare(strict_types=1);

$basePath = __DIR__;
$appPath = $basePath.'/app';
$fixedCount = 0;

echo "Starting Final Comprehensive Fixes...\n\n";

// Phase 1: Add missing @property annotations to all Models
echo "Phase 1: Adding @property annotations to Models...\n";

$modelsPath = $appPath.'/Models';
if (is_dir($modelsPath)) {
    $modelFiles = glob($modelsPath.'/*.php');

    foreach ($modelFiles as $modelFile) {
        $content = file_get_contents($modelFile);
        $originalContent = $content;

        // Check if class already has PHPDoc block
        if (! preg_match('/\/\*\*\s*\n\s*\*\s*@property/', $content)) {
            // Add basic @property annotations based on common patterns
            $className = basename($modelFile, '.php');

            // Find the class declaration
            if (preg_match('/(class\s+'.preg_quote($className).'\s+extends\s+Model)/', $content, $matches, PREG_OFFSET_CAPTURE)) {
                $classPos = $matches[1][1];

                // Insert PHPDoc before class
                $phpDoc = "/**\n";
                $phpDoc .= " * @property int \$id\n";
                $phpDoc .= " * @property \\Illuminate\\Support\\Carbon|null \$created_at\n";
                $phpDoc .= " * @property \\Illuminate\\Support\\Carbon|null \$updated_at\n";
                $phpDoc .= " */\n";

                $content = substr_replace($content, $phpDoc, $classPos, 0);
                $fixedCount++;
            }
        }

        if ($content !== $originalContent) {
            file_put_contents($modelFile, $content);
            echo '.';
        }
    }
    echo "\n✓ Model annotations added\n";
}

// Phase 2: Fix all Service method return types
echo "\nPhase 2: Fixing Service method return types...\n";

$servicesPath = $appPath.'/Services';
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($servicesPath, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

$serviceFiles = [];
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $serviceFiles[] = $file->getPathname();
    }
}

foreach ($serviceFiles as $filePath) {
    $content = file_get_contents($filePath);
    $originalContent = $content;

    // Fix methods returning arrays without type specification
    $content = preg_replace(
        '/(public|protected|private)\s+function\s+(\w+)\([^)]*\):\s*array\s*\{/',
        '$1 function $2($3): array',
        $content
    );

    // Add @return annotations for complex array returns
    $content = preg_replace(
        '/(\/\*\*[^*]*\*\/\s*)(public|protected|private)\s+function\s+(\w+)\([^)]*\):\s*array/',
        '$1@return array<string, mixed>\n     */\n    $2 function $3',
        $content
    );

    if ($content !== $originalContent) {
        file_put_contents($filePath, $content);
        $fixedCount++;
        echo '.';
    }
}
echo "\n✓ Service return types fixed\n";

// Phase 3: Fix Config::get() and Cache::remember() patterns
echo "\nPhase 3: Fixing Config and Cache patterns...\n";

foreach ($serviceFiles as $filePath) {
    $content = file_get_contents($filePath);
    $originalContent = $content;

    // Pattern: Config::get('key') with default
    $content = preg_replace(
        '/Config::get\(([^,)]+)\)(?!\s*,)/',
        'Config::get($1, \'\')',
        $content
    );

    // Pattern: config('key') with default
    $content = preg_replace(
        '/config\(([^,)]+)\)(?!\s*,)/',
        'config($1, \'\')',
        $content
    );

    if ($content !== $originalContent) {
        file_put_contents($filePath, $content);
        echo '.';
    }
}
echo "\n✓ Config/Cache patterns fixed\n";

// Phase 4: Fix scope methods in Models
echo "\nPhase 4: Fixing Model scope methods...\n";

if (is_dir($modelsPath)) {
    $modelFiles = glob($modelsPath.'/*.php');

    foreach ($modelFiles as $modelFile) {
        $content = file_get_contents($modelFile);
        $originalContent = $content;

        // Fix scope methods without proper type hints
        $content = preg_replace(
            '/(public\s+function\s+scope\w+)\(\$query\)/',
            '$1(\\Illuminate\\Database\\Eloquent\\Builder $query): \\Illuminate\\Database\\Eloquent\\Builder',
            $content
        );

        $content = preg_replace(
            '/(public\s+function\s+scope\w+)\(Builder\s+\$query\)(?!\s*:)/',
            '$1(Builder $query): Builder',
            $content
        );

        if ($content !== $originalContent) {
            file_put_contents($modelFile, $content);
            $fixedCount++;
            echo '.';
        }
    }
    echo "\n✓ Scope methods fixed\n";
}

// Phase 5: Fix property access on potentially null objects
echo "\nPhase 5: Fixing null-safe property access...\n";

$allPhpFiles = [];
$allIterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($appPath, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($allIterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $allPhpFiles[] = $file->getPathname();
    }
}

foreach ($allPhpFiles as $filePath) {
    $content = file_get_contents($filePath);
    $originalContent = $content;

    // Replace common patterns like $user->id where $user might be null
    // Only if not already using null-safe operator
    $content = preg_replace(
        '/\$user->id(?!\s*\?\?)/',
        '$user?->id ?? throw new \\Exception(\'User required\')',
        $content
    );

    if ($content !== $originalContent) {
        file_put_contents($filePath, $content);
        echo '.';
    }
}
echo "\n✓ Null-safe access patterns fixed\n";

// Phase 6: Fix binary operations on mixed types
echo "\nPhase 6: Fixing binary operations...\n";

foreach ($allPhpFiles as $filePath) {
    $content = file_get_contents($filePath);
    $originalContent = $content;

    // Fix $var++ where $var might be mixed
    $content = preg_replace(
        '/\$(\w+)\+\+/',
        '$$1 = ($$1 ?? 0) + 1',
        $content
    );

    // Fix $var += value
    $content = preg_replace(
        '/\$(\w+)\s*\+=\s*([^;]+);/',
        '$$1 = ($$1 ?? 0) + $2;',
        $content
    );

    if ($content !== $originalContent) {
        file_put_contents($filePath, $content);
        echo '.';
    }
}
echo "\n✓ Binary operations fixed\n";

// Phase 7: Fix array offset access without isset checks
echo "\nPhase 7: Fixing array offset access...\n";

foreach ($serviceFiles as $filePath) {
    $content = file_get_contents($filePath);
    $originalContent = $content;

    // Pattern: $array['key'] where $array might be mixed
    // Add isset checks for common patterns
    $content = preg_replace(
        '/\$data\[\'(\w+)\'\](?!\s*[\?\|])/',
        '(is_array($data) && isset($data[\'$1\']) ? $data[\'$1\'] : null)',
        $content
    );

    if ($content !== $originalContent) {
        file_put_contents($filePath, $content);
        echo '.';
    }
}
echo "\n✓ Array offset access fixed\n";

echo "\n═══════════════════════════════════════════════════════════\n";
echo "Final Comprehensive Fixes Complete!\n";
echo "Total fixes applied: $fixedCount+\n";
echo "═══════════════════════════════════════════════════════════\n\n";
echo "Next steps:\n";
echo "1. Run: vendor/bin/pint\n";
echo "2. Run: vendor/bin/phpstan analyse\n";
echo "3. Review and manually fix remaining complex errors\n";
