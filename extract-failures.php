<?php

$file = __DIR__.DIRECTORY_SEPARATOR.'test-results-latest.txt';
$lines = file($file, FILE_IGNORE_NEW_LINES);
$failed = [];
foreach ($lines as $l) {
    $l = trim($l);
    if (str_contains($l, 'FAILED')) {
        $clean = preg_replace('/\x1B\[[0-9;]*[mK]/', '', $l);
        $clean = preg_replace('/[^\x20-\x7E]/', '', $clean);
        $clean = trim($clean);
        if (! empty($clean)) {
            $failed[] = $clean;
        }
    }
}
$unique = array_unique($failed);
$output = 'Total unique FAILED lines: '.count($unique).PHP_EOL;
$output .= str_repeat('-', 80).PHP_EOL;
foreach ($unique as $f) {
    $output .= $f.PHP_EOL;
}
file_put_contents(__DIR__.DIRECTORY_SEPARATOR.'failed-list.txt', $output);
echo $output;
