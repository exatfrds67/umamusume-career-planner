<?php

/**
 * Systematic Service File Fixer
 *
 * Fixes missing method parameters by analyzing variable usage in method bodies
 */
$filesToFix = [
    'app/Services/BackupService.php',
    'app/Services/BenchmarkingService.php',
    'app/Services/CareerComparisonService.php',
    'app/Services/DataExportService.php',
    'app/Services/DataImportService.php',
    'app/Services/ExternalDataService.php',
    'app/Services/HistoricalTrackingService.php',
    'app/Services/TesseractService.php',
    'app/Services/ExternalAPI/CacheManagementAgent.php',
    'app/Services/ExternalAPI/ConflictDetectionService.php',
    'app/Services/ExternalAPI/ConflictResolutionService.php',
    'app/Services/ExternalAPI/ResponseTransformer.php',
    'app/Services/MCP/AgentCommunicationService.php',
    'app/Services/MCP/AgentContextService.php',
    'app/Services/MCP/RealTimeMonitoringService.php',
    'app/Services/MCP/Agents/CareerStrategyAgent.php',
    'app/Services/MCP/Agents/HintFarmingStrategyAgent.php',
    'app/Services/MCP/Agents/PerformanceAnalyticsAgent.php',
    'app/Services/OCR/DataTransformationService.php',
    'app/Services/OCR/DataValidationService.php',
    'app/Services/OCR/Parsers/CharacterStatsParser.php',
    'app/Services/OCR/Parsers/RaceResultParser.php',
    'app/Services/OCR/Parsers/SkillListParser.php',
];

// Common type mappings based on variable names
$typeMap = [
    'user' => 'User',
    'character' => 'Character',
    'skill' => 'Skill',
    'career' => 'Career',
    'careers' => 'Collection',
    'deck' => 'Collection',
    'supportCard' => 'SupportCard',
    'race' => 'Race',
    'session' => 'TrainingSession',
    'data' => 'array',
    'config' => 'array',
    'options' => 'array',
    'params' => 'array',
    'attributes' => 'array',
];

function fixServiceFile(string $filePath): bool
{
    global $typeMap;

    if (! file_exists($filePath)) {
        echo "❌ File not found: $filePath\n";

        return false;
    }

    $content = file_get_contents($filePath);
    $lines = explode("\n", $content);
    $modified = false;

    // Find methods with missing parameters
    foreach ($lines as $i => $line) {
        // Match method signatures
        if (preg_match('/^\s*(public|protected|private)\s+function\s+(\w+)\s*\(([^)]*)\)\s*:\s*(.+)$/', $line, $matches)) {
            $visibility = $matches[1];
            $methodName = $matches[2];
            $currentParams = trim($matches[3]);
            $returnType = trim($matches[4]);

            // Look ahead to find variables used in the method body
            $usedVars = [];
            $braceCount = 0;
            $inMethod = false;

            for ($j = $i + 1; $j < count($lines) && $j < $i + 50; $j++) {
                $bodyLine = $lines[$j];

                if (str_contains($bodyLine, '{')) {
                    $braceCount += substr_count($bodyLine, '{');
                    $inMethod = true;
                }
                if (str_contains($bodyLine, '}')) {
                    $braceCount -= substr_count($bodyLine, '}');
                }

                if ($braceCount <= 0 && $inMethod) {
                    break;
                }

                // Find variable usage like $variable or $variable->
                if (preg_match_all('/\$(\w+)(?:->|\?->|::|,|\)|\s)/', $bodyLine, $varMatches)) {
                    foreach ($varMatches[1] as $var) {
                        if ($var !== 'this' && $var !== 'cacheKey' && $var !== 'result' && $var !== 'query') {
                            $usedVars[$var] = true;
                        }
                    }
                }
            }

            // Check if any used variables are not in current parameters
            $currentParamVars = [];
            if ($currentParams) {
                if (preg_match_all('/\$(\w+)/', $currentParams, $paramMatches)) {
                    $currentParamVars = $paramMatches[1];
                }
            }

            $missingParams = [];
            foreach (array_keys($usedVars) as $var) {
                if (! in_array($var, $currentParamVars)) {
                    // Infer type from variable name
                    $type = $typeMap[$var] ?? 'mixed';
                    if ($type === 'Collection') {
                        $missingParams[] = "Collection \$$var";
                    } elseif ($type === 'User') {
                        $missingParams[] = "\\App\\Models\\User \$$var";
                    } elseif (in_array($type, ['Character', 'Skill', 'Career', 'Race', 'SupportCard', 'TrainingSession'])) {
                        $missingParams[] = "\\App\\Models\\$type \$$var";
                    } else {
                        $missingParams[] = "$type \$$var";
                    }
                }
            }

            if (! empty($missingParams)) {
                // Build new parameter list
                $newParams = $currentParams;
                if ($newParams && ! empty($missingParams)) {
                    $newParams .= ', '.implode(', ', $missingParams);
                } elseif (empty($newParams)) {
                    $newParams = implode(', ', $missingParams);
                }

                $newLine = "$visibility function $methodName($newParams): $returnType";

                echo "📝 Fixing $methodName in $filePath\n";
                echo "   Old: $line\n";
                echo "   New: $newLine\n\n";

                $lines[$i] = str_replace($line, $newLine, $lines[$i]);
                $modified = true;
            }
        }
    }

    if ($modified) {
        file_put_contents($filePath, implode("\n", $lines));
        echo "✅ Fixed $filePath\n\n";

        return true;
    }

    echo "ℹ️  No changes needed for $filePath\n";

    return false;
}

echo "🔧 Starting systematic service file repair...\n\n";

$fixedCount = 0;
foreach ($filesToFix as $file) {
    if (fixServiceFile($file)) {
        $fixedCount++;
    }
}

echo "\n✅ Complete! Fixed $fixedCount files.\n";
echo "Running syntax check on all fixed files...\n\n";

foreach ($filesToFix as $file) {
    exec("php -l $file 2>&1", $output, $returnCode);
    if ($returnCode !== 0) {
        echo "❌ Still has errors: $file\n";
        echo implode("\n", $output)."\n\n";
    }
}

echo "\n🎉 Done!\n";
