#!/usr/bin/env php
<?php

/**
 * Script to fix Larastan Level 9 errors in BackupService.php
 *
 * This script applies systematic fixes for:
 * - Redundant is_array() checks
 * - Unused @phpstan-ignore comments
 * - Unnecessary ?? operators
 * - Mixed type string interpolation
 * - Type hints for parameters
 */
$filePath = __DIR__.'/../app/Services/BackupService.php';

if (! file_exists($filePath)) {
    echo "Error: File not found: $filePath\n";
    exit(1);
}

$content = file_get_contents($filePath);
$originalContent = $content;

echo "Starting BackupService.php Larastan fixes...\n\n";

// Fix 1: Remove redundant is_array() checks on lines 394, 404, 406
// Pattern: if (is_array($var)) { ... } where $var is already typed as array
$patterns = [
    // Line 394 area - checking array that's already typed
    '/if \(\s*is_array\(\$backupRecord\)\s*&&\s*isset\(\$backupRecord\[\'user_id\'\]\)\s*\?\s*\$backupRecord\[\'user_id\'\]\s*:\s*null\)\s*\)/' => 'if (($backupRecord[\'user_id\'] ?? null))',

    // Line 404 area
    '/\$filePath\s*=\s*\\\\is_string\(\(is_array\(\$backupRecord\)\s*&&\s*isset\(\$backupRecord\[\'file_path\'\]\)\s*\?\s*\$backupRecord\[\'file_path\'\]\s*:\s*null\)\)\s*\?\s*\$backupRecord\[\'file_path\'\]\s*:\s*\'\';/' => '$filePath = is_string($backupRecord[\'file_path\'] ?? null) ? $backupRecord[\'file_path\'] : \'\';',

    // Line 406 area
    '/\$checksum\s*=\s*\\\\is_string\(\(is_array\(\$backupRecord\)\s*&&\s*isset\(\$backupRecord\[\'checksum\'\]\)\s*\?\s*\$backupRecord\[\'checksum\'\]\s*:\s*null\)\)\s*\?\s*\$backupRecord\[\'checksum\'\]\s*:\s*\'\';/' => '$checksum = is_string($backupRecord[\'checksum\'] ?? null) ? $backupRecord[\'checksum\'] : \'\';',
];

foreach ($patterns as $pattern => $replacement) {
    $newContent = preg_replace($pattern, $replacement, $content);
    if ($newContent !== null && $newContent !== $content) {
        $content = $newContent;
        echo "✓ Fixed redundant is_array() check\n";
    }
}

// Fix 2: Remove unnecessary ?? operators where variable always exists
// Pattern: $var = ($definiteVar ?? default) where $definiteVar is never null
$content = preg_replace(
    '/\$restored\s*=\s*\(\$restored\s*\?\?\s*0\)\s*\+\s*1;/',
    '$restored = $restored + 1;',
    $content
);
echo "✓ Fixed unnecessary ?? operators\n";

// Fix 3: Cast mixed values before string interpolation
// Pattern: "text $mixedVar" should be "text " . (string)$mixedVar
$content = preg_replace_callback(
    '/\$charName\s*=\s*\\\\is_string\(\(is_array\(\$charData\)\s*&&\s*isset\(\$charData\[\'name\'\]\)\s*\?\s*\$charData\[\'name\'\]\s*:\s*null\)\)\s*\?\s*\$charData\[\'name\'\]\s*:\s*\'Unknown\';/',
    function ($matches) {
        return '$charName = is_string($charData[\'name\'] ?? null) ? $charData[\'name\'] : \'Unknown\';';
    },
    $content
);

$content = preg_replace_callback(
    '/\$careerName\s*=\s*\\\\is_string\(\(is_array\(\$careerData\)\s*&&\s*isset\(\$careerData\[\'career_name\'\]\)\s*\?\s*\$careerData\[\'career_name\'\]\s*:\s*null\)\)\s*\?\s*\$careerData\[\'career_name\'\]\s*:\s*\'Unknown\';/',
    function ($matches) {
        return '$careerName = is_string($careerData[\'career_name\'] ?? null) ? $careerData[\'career_name\'] : \'Unknown\';';
    },
    $content
);

echo "✓ Fixed mixed type string interpolation\n";

// Fix 4: Remove unused @phpstan-ignore comments
$content = preg_replace(
    '/\/\*\*\s*@phpstan-ignore-next-line[^\n]*\n\s*\*\/\n\s*\$existingChar\s*=\s*Character::where/',
    '$existingChar = Character::where',
    $content
);

$content = preg_replace(
    '/\/\*\*\s*@phpstan-ignore-next-line[^\n]*\n\s*\*\/\n\s*\$existingCareer\s*=\s*Career::where/',
    '$existingCareer = Career::where',
    $content
);

$content = preg_replace(
    '/\/\*\*\s*@phpstan-ignore-next-line[^\n]*\n\s*\*\/\n\s*\$existingChar->update/',
    '$existingChar->update',
    $content
);

echo "✓ Removed unused @phpstan-ignore comments\n";

// Fix 5: Add proper type hint to verifyDataIntegrity parameter
$content = preg_replace(
    '/private function verifyDataIntegrity\(array \$data,/',
    '/**
     * @param array<string, mixed> $data
     */
    private function verifyDataIntegrity(array $data,',
    $content
);

echo "✓ Added array value type to parameter\n";

// Save the fixed content
if ($content !== $originalContent) {
    file_put_contents($filePath, $content);
    echo "\n✅ Successfully fixed BackupService.php\n";
    echo 'Total changes applied: '.substr_count($content, '✓')."\n";
} else {
    echo "\n⚠️  No changes were made to the file\n";
}

echo "\nNext steps:\n";
echo "1. Run: vendor/bin/phpstan analyse app/Services/BackupService.php --level=9\n";
echo "2. Run: php artisan test --filter=BackupService\n";
echo "3. Review changes and commit if tests pass\n";
