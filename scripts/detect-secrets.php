<?php

declare(strict_types=1);

/**
 * Hardcoded Secret Detection Script
 *
 * Scans PHP source files for potential hardcoded secrets, API keys,
 * passwords, and tokens outside of configuration files.
 *
 * Usage: php scripts/detect-secrets.php [--fix] [--verbose]
 *
 * Exit codes:
 *   0 = No secrets found
 *   1 = Potential secrets detected
 *   2 = Script error
 *
 * Covers NFR-M-01 (Maintainability - Code Quality)
 */
$options = getopt('', ['fix', 'verbose', 'help']);

if (isset($options['help'])) {
    echo "Usage: php scripts/detect-secrets.php [--verbose]\n";
    echo "  --verbose  Show detailed match information\n";
    echo "  --help     Show this help message\n";
    exit(0);
}

$verbose = isset($options['verbose']);

$projectRoot = dirname(__DIR__);

$patterns = [
    'Hardcoded password' => '/(?:password|passwd|pwd)\s*=\s*[\'"][^\'"]{8,}[\'"]/i',
    'Hardcoded API key' => '/(?:api_?key|apikey)\s*=\s*[\'"][a-zA-Z0-9_\-]{16,}[\'"]/i',
    'Hardcoded secret' => '/(?:secret|secret_key)\s*=\s*[\'"][a-zA-Z0-9_\-]{16,}[\'"]/i',
    'Hardcoded token' => '/(?:token|auth_token|access_token)\s*=\s*[\'"][a-zA-Z0-9_\-\.]{16,}[\'"]/i',
    'AWS credentials' => '/(?:AKIA|ASIA)[A-Z0-9]{16}/i',
    'Private key content' => '/-----BEGIN (?:RSA |EC )?PRIVATE KEY-----/',
    'Base64 encoded secret' => '/(?:password|secret|key)\s*=\s*base64_decode\s*\(/i',
    'Env function outside config' => '/\benv\s*\(\s*[\'"][A-Z_]+[\'"]\s*\)/i',
];

$scanDirectories = [
    'app',
    'routes',
    'resources',
];

$excludePatterns = [
    '/vendor\//',
    '/node_modules\//',
    '/\.git\//',
    '/storage\//',
    '/tests\//',
    '/phpstan-stubs\//',
    '/\.env\.example/',
];

$allowedFiles = [
    'config/',
    '.env.example',
];

$findings = [];
$filesScanned = 0;

foreach ($scanDirectories as $dir) {
    $fullPath = $projectRoot.DIRECTORY_SEPARATOR.$dir;
    if (! is_dir($fullPath)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($fullPath, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY,
    );

    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php' && $file->getExtension() !== 'blade') {
            continue;
        }

        $relativePath = str_replace($projectRoot.DIRECTORY_SEPARATOR, '', $file->getPathname());
        $relativePath = str_replace('\\', '/', $relativePath);

        $skip = false;
        foreach ($excludePatterns as $exclude) {
            if (preg_match($exclude, $relativePath)) {
                $skip = true;
                break;
            }
        }
        if ($skip) {
            continue;
        }

        $isConfigFile = false;
        foreach ($allowedFiles as $allowed) {
            if (str_starts_with($relativePath, $allowed)) {
                $isConfigFile = true;
                break;
            }
        }

        $filesScanned++;
        $contents = file_get_contents($file->getPathname());
        $lines = explode("\n", $contents);

        foreach ($patterns as $name => $pattern) {
            if ($name === 'Env function outside config' && $isConfigFile) {
                continue;
            }

            foreach ($lines as $lineNum => $line) {
                if (str_contains($line, '// @secret-ok') || str_contains($line, '// phpcs:ignore')) {
                    continue;
                }

                if (preg_match($pattern, $line)) {
                    if (str_contains($line, 'config(') || str_contains($line, 'env(') && $isConfigFile) {
                        continue;
                    }
                    if (str_contains($line, '@param') || str_contains($line, '@var') || str_contains($line, '* ')) {
                        continue;
                    }

                    $findings[] = [
                        'file' => $relativePath,
                        'line' => $lineNum + 1,
                        'rule' => $name,
                        'content' => trim($line),
                    ];
                }
            }
        }
    }
}

echo "=== Hardcoded Secret Detection Report ===\n\n";
echo "Files scanned: {$filesScanned}\n";
echo 'Findings: '.count($findings)."\n\n";

if (count($findings) > 0) {
    foreach ($findings as $finding) {
        echo "[{$finding['rule']}] {$finding['file']}:{$finding['line']}\n";
        if ($verbose) {
            echo '  → '.substr($finding['content'], 0, 120)."\n";
        }
    }
    echo "\nTo suppress a false positive, add '// @secret-ok' to the line.\n";
    exit(1);
}

echo "No hardcoded secrets found.\n";
exit(0);
