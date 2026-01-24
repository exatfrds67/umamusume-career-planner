#!/usr/bin/env pwsh

# Fix corrupted method signatures in service files
# Pattern: public function methodName(): Type\n    $var = ...
# Should be: public function methodName(): Type\n{\n    $var = ...

$serviceFiles = Get-ChildItem -Path "app/Services" -Filter "*.php" -Recurse

$fixCount = 0
$fileCount = 0

foreach ($file in $serviceFiles) {
    $content = Get-Content $file.FullName -Raw
    $originalContent = $content
    
    # Pattern 1: Method signature followed directly by statement (missing opening brace)
    # Matches: public/protected/private function name(...): type\n    statement
    $pattern1 = '((?:public|protected|private)\s+function\s+\w+\([^)]*\)\s*:\s*\w+(?:<[^>]+>)?)\s*\n(\s+)(\$\w+|\w+::|return|if|foreach|try)'
    $replacement1 = '$1' + "`n" + '{' + "`n" + '$2$3'
    
    $content = $content -replace $pattern1, $replacement1
    
    # Pattern 2: Method signature with no params but should have them (missing opening brace AND params)
    # This is trickier - we'll handle it per-file basis
    
    if ($content -ne $originalContent) {
        Set-Content -Path $file.FullName -Value $content -NoNewline
        $fixCount++
        Write-Host "✓ Fixed: $($file.FullName -replace '.*\\app\\', 'app/')"
        $fileCount++
    }
}

Write-Host "`n=== Summary ==="
Write-Host "Files scanned: $($serviceFiles.Count)"
Write-Host "Files fixed: $fileCount"
Write-Host "Patterns applied: $fixCount"
