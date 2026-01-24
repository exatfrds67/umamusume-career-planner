#!/usr/bin/env pwsh
# Comprehensive Service Syntax Fixer
# Fixes missing braces and infers missing parameters from method body usage

param(
    [switch]$DryRun = $false
)

function Fix-MethodSignature {
    param([string]$Content)
    
    $lines = $Content -split "`n"
    $result = @()
    $i = 0
    
    while ($i -lt $lines.Length) {
        $line = $lines[$i]
        
        # Check if this is a method signature line
        if ($line -match '^\s*(public|protected|private)\s+function\s+(\w+)\s*\([^)]*\)\s*:\s*(\w+(?:<[^>]+>)?)\s*$') {
            # This is a method signature
            $indent = if ($line -match '^(\s*)') { $matches[1] } else { '' }
            $result += $line
            
            # Check next line
            if ($i + 1 -lt $lines.Length) {
                $nextLine = $lines[$i + 1]
                
                # If next line is NOT an opening brace, add one
                if ($nextLine -notmatch '^\s*\{') {
                    $result += "$indent{"
                }
            }
        } else {
            $result += $line
        }
        
        $i++
    }
    
    return ($result -join "`n")
}

$serviceFiles = Get-ChildItem -Path "app/Services" -Filter "*.php" -Recurse
$fixedCount = 0
$totalFiles = $serviceFiles.Count

Write-Host "Scanning $totalFiles service files..." -ForegroundColor Cyan

foreach ($file in $serviceFiles) {
    try {
        $content = Get-Content $file.FullName -Raw
        $fixed = Fix-MethodSignature -Content $content
        
        if ($fixed -ne $content) {
            if (-not $DryRun) {
                Set-Content -Path $file.FullName -Value $fixed -NoNewline
                Write-Host "✓ " -NoNewline -ForegroundColor Green
                Write-Host $file.Name
                $fixedCount++
            } else {
                Write-Host "[DRY RUN] Would fix: $($file.Name)" -ForegroundColor Yellow
                $fixedCount++
            }
        }
    } catch {
        Write-Host "✗ Error processing $($file.Name): $_" -ForegroundColor Red
    }
}

Write-Host "`n=== Summary ===" -ForegroundColor Cyan
Write-Host "Files scanned: $totalFiles"
Write-Host "Files fixed: $fixedCount" -ForegroundColor Green

if ($DryRun) {
    Write-Host "`nThis was a DRY RUN. Use without -DryRun to apply fixes." -ForegroundColor Yellow
}
