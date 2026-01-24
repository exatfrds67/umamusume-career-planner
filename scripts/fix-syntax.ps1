$files = Get-ChildItem -Path "app\Services" -Recurse -Filter "*.php"

foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw
    $originalContent = $content
    
    # Fix missing opening braces after method declarations
    # Pattern: ): type\n        code (should be ): type\n    {\n        code)
    $content = $content -replace '(\): [a-zA-Z\[\]<>,\s\|\?]+)\r?\n(\s+)(\$|return|if|Log::|Cache::|Redis::|DB::)', '$1$2{$2$3'
    
    if ($content -ne $originalContent) {
        Set-Content -Path $file.FullName -Value $content -NoNewline
        Write-Host "Fixed: $($file.FullName)"
    }
}

Write-Host "Done!"
