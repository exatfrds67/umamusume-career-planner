<?php

/**
 * Fix Browser Tests - Replace pause() with wait()
 *
 * Pest v4 browser tests use wait() instead of pause()
 * This script converts pause(milliseconds) to wait(seconds)
 */
$file = __DIR__.'/../tests/Browser/RecommendationApplicationTest.php';
$content = file_get_contents($file);

// Replace pause(milliseconds) with wait(seconds)
$replacements = [
    '->pause(200)' => '->wait(0.2)',
    '->pause(500)' => '->wait(0.5)',
    '->pause(1000)' => '->wait(1)',
    '->pause(2000)' => '->wait(2)',
    '->pause(3000)' => '->wait(3)',
];

foreach ($replacements as $old => $new) {
    $content = str_replace($old, $new, $content);
}

// Also handle pause() in comments
$content = str_replace('// Wait for form submission and page reload', '// Wait for form submission', $content);
$content = str_replace('// Wait for API call and UI update', '// Wait for API call', $content);

file_put_contents($file, $content);

echo "✓ Fixed browser tests - replaced pause() with wait()\n";
echo "✓ Converted milliseconds to seconds\n";
echo "✓ File: tests/Browser/RecommendationApplicationTest.php\n";
