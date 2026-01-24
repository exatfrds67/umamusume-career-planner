<?php

/**
 * Automated Service Syntax Fixer - Enhanced Version
 * Fixes common patterns: method signatures missing opening braces and parameters
 */
$basePath = __DIR__.'/app/Services/';
$errorFiles = file('temp_service_errors.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$stats = ['fixed' => 0, 'failed' => 0];

foreach ($errorFiles as $fileName) {
    $filePath = findFile($basePath, trim($fileName));
    if (! $filePath || ! file_exists($filePath)) {
        continue;
    }

    $content = file_get_contents($filePath);
    $lines = explode("\n", $content);
    $newLines = [];
    $modified = false;

    for ($i = 0; $i < count($lines); $i++) {
        $line = $lines[$i];
        $nextLine = $i + 1 < count($lines) ? $lines[$i + 1] : '';

        // Check if this is a method signature without opening brace
        if (preg_match('/^\s*(public|protected|private)\s+function\s+\w+\([^)]*\):\s*\S+\s*$/', $line)) {
            // Next line should be body, not another method or closing brace
            if ($nextLine && ! preg_match('/^\s*\{/', $nextLine) && preg_match('/^\s+(\$|return|try|if|foreach|while|\w+::)/', $nextLine)) {
                $indent = str_repeat(' ', strlen($line) - strlen(ltrim($line)));
                $newLines[] = $line;
                $newLines[] = $indent.'{';
                $modified = true;

                continue;
            }
        }

        $newLines[] = $line;
    }

    if ($modified) {
        file_put_contents($filePath, implode("\n", $newLines));
        echo "✅ Enhanced fix: $fileName\n";
        $stats['fixed']++;
    }
}

echo "\n=== Enhanced Pass Summary ===\n";
echo "Fixed: {$stats['fixed']}\n";

function findFile($basePath, $fileName)
{
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($basePath)
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getFilename() === $fileName) {
            return $file->getPathname();
        }
    }

    return null;
}
