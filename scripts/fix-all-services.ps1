$ErrorActionPreference = "SilentlyContinue"

# Get all PHP files in app\Services
$files = Get-ChildItem -Path "app\Services" -Recurse -Filter "*.php" -File

$fixedCount = 0
$errorCount = 0

foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw
    $originalContent = $content
    
    # Fix 1: Add opening braces after method declarations with return type
    # Pattern: ): type\n        code (should be ): type\n    {\n        code)
    $content = $content -replace '(\): [a-zA-Z\[\]<>,\s\|\?]+)\r?\n(\s+)(try|if|foreach|return|Log::|Cache::|Redis::|DB::|uasort|\$|\/\/)', '$1$2{$2$3'
    
    # Fix 2: Add opening braces for methods missing parameters
    # Pattern: public function name(): type\n        code
    $content = $content -replace '(public function \w+\(\)): ([a-zA-Z\[\]<>,\s\|\?]+)\r?\n(\s+)(try|if|foreach|return|Log::|Cache::|Redis::|DB::|uasort|\$|\/\/)', '$1: $2$3{$3$4'
    
    # Fix 3: Fix ternary operators used as assignment targets (invalid PHP)
    # Replace: (is_array($x) && isset($x['key']) ? $x['key'] : null) = value
    # With: if (isset($x['key'])) { $x['key'] = value; } else { $x['key'] = value; }
    $content = $content -replace '\(is_array\(\$(\w+)\) && isset\(\$\1\[\'(\w+)\'\]\) \? \$\1\[\'(\w+)\'\] : null\) = ', '$$$1[''$2''] = '
    
    # Fix 4: Add missing opening braces for try blocks
    $content = $content -replace '(\): [a-zA-Z\[\]<>,\s\|\?]+)\r?\n(\s+)try\s', '$1$2{$2try '
    
    # Fix 5: Fix malformed method signatures with missing parameters
    $content = $content -replace 'public function (\w+)\(\): (array|string|bool|int|float|void|mixed)', 'public function $1(): $2 {'
    
    # Fix 6: Add braces for protected/private methods
    $content = $content -replace '(protected|private) function (\w+)\(\): ([a-zA-Z\[\]<>,\s\|\?]+)\r?\n(\s+)(try|if|foreach|return|Log::|Cache::|Redis::|DB::|uasort|\$|\/\/)', '$1 function $2(): $3$4{$4$5'
    
    # Fix 7: Fix incomplete method bodies
    $content = $content -replace '(\{)\r?\n(\s+)(\$\w+\s*=\s*)', '$1$2$3'
    
    if ($content -ne $originalContent) {
        try {
            Set-Content -Path $file.FullName -Value $content -NoNewline -ErrorAction Stop
            Write-Host "✓ Fixed: $($file.Name)" -ForegroundColor Green
            $fixedCount++
        } catch {
            Write-Host "✗ Error writing: $($file.Name) - $_" -ForegroundColor Red
            $errorCount++
        }
    }
}

Write-Host "`nSummary:" -ForegroundColor Cyan
Write-Host "Files fixed: $fixedCount" -ForegroundColor Green
Write-Host "Errors: $errorCount" -ForegroundColor Red
Write-Host "Done!"
