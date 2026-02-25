<#
.SYNOPSIS
    Stop all development servers
    
.DESCRIPTION
    Gracefully stops Redis (WSL2), Laravel Artisan, and Vite development servers
    
.NOTES
    Author: Umamusume Career Planner Team
    Date: 2026-02-26
    Requires: PowerShell 7.5+, WSL2 with Redis
#>

Write-Host "🛑 Stopping Umamusume Career Planner Development Environment..." -ForegroundColor Cyan
Write-Host ""

# Stop PHP development server (Laravel Artisan)
Write-Host "🔵 Stopping Laravel Artisan Server..." -ForegroundColor Yellow
$stopped = $false
try {
    $laravelConnections = Get-NetTCPConnection -LocalPort 8000 -ErrorAction SilentlyContinue
    $laravelPids = $laravelConnections | Select-Object -ExpandProperty OwningProcess -Unique
    foreach ($processId in $laravelPids) {
        if ($processId) {
            Stop-Process -Id $processId -Force
            $stopped = $true
        }
    }
} catch {
    # Silently continue if port check fails
}

if ($stopped) {
    Write-Host "   ✓ Laravel server stopped" -ForegroundColor Green
} else {
    Write-Host "   • No Laravel server running on port 8000" -ForegroundColor Gray
}

# Stop Node.js development server (Vite)
Write-Host "🟢 Stopping Vite Dev Server..." -ForegroundColor Yellow
$stopped = $false
try {
    $viteConnections = Get-NetTCPConnection -LocalPort 5173 -ErrorAction SilentlyContinue
    $vitePids = $viteConnections | Select-Object -ExpandProperty OwningProcess -Unique
    foreach ($processId in $vitePids) {
        if ($processId) {
            Stop-Process -Id $processId -Force
            $stopped = $true
        }
    }
} catch {
    # Silently continue if port check fails
}

if ($stopped) {
    Write-Host "   ✓ Vite server stopped" -ForegroundColor Green
} else {
    Write-Host "   • No Vite server running on port 5173" -ForegroundColor Gray
}

# Stop Redis in WSL2
Write-Host "🔴 Stopping Redis in WSL2..." -ForegroundColor Yellow
try {
    wsl sudo service redis-server stop 2>&1 | Out-Null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "   ✓ Redis stopped" -ForegroundColor Green
    } else {
        Write-Host "   • Redis may not be running or WSL2 unavailable" -ForegroundColor Gray
    }
} catch {
    Write-Host "   • Redis may not be running or WSL2 unavailable" -ForegroundColor Gray
}

Write-Host ""
Write-Host "✅ All development servers stopped!" -ForegroundColor Green
Write-Host ""
Write-Host "💡 Tip: Run scripts\start-dev-servers.ps1 to restart" -ForegroundColor Yellow
Write-Host ""
