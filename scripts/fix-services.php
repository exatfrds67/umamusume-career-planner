<?php

$servicesPath = 'app/Services';
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($servicesPath),
    RecursiveIteratorIterator::LEAVES_ONLY
);

$fixedCount = 0;
$errorCount = 0;

foreach ($files as $file) {
    if ($file->getExtension() !== 'php') {
        continue;
    }

    $filePath = $file->getPathname();
    $content = file_get_contents($filePath);
    $originalContent = $content;

    // Fix 1: Add opening braces after method declarations
    $content = preg_replace(
        '/(\): [a-zA-Z\[\]<>,\s\|\?]+)\s*\n\s*(try|if|foreach|return|Log::|Cache::|Redis::|DB::|uasort|\$|\/\/)/',
        '$1 {'."\n".'        $2',
        $content
    );

    // Fix 2: Add parameters to methods with empty parentheses
    $content = preg_replace(
        '/(public|protected|private) function (\w+)\(\): (array|string|bool|int|float|void|mixed)\s*\n\s*(try|if|foreach|return|Log::|Cache::|Redis::|DB::|uasort|\$|\/\/)/',
        '$1 function $2(): $3 {'."\n".'        $4',
        $content
    );

    // Fix 3: Fix ternary operators used as assignment targets
    $content = preg_replace(
        '/\(is_array\(\$(\w+)\) && isset\(\$\1\[[\'"](\w+)[\'"]\]\) \? \$\1\[[\'"](\w+)[\'"]\] : null\) = /',
        '$$$1[\'$2\'] = ',
        $content
    );

    if ($content !== $originalContent) {
        if (file_put_contents($filePath, $content) !== false) {
            echo '✓ Fixed: '.basename($filePath)."\n";
            $fixedCount++;
        } else {
            echo '✗ Error writing: '.basename($filePath)."\n";
            $errorCount++;
        }
    }
}

echo "\nSummary:\n";
echo "Files fixed: $fixedCount\n";
echo "Errors: $errorCount\n";
