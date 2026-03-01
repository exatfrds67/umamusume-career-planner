<#
.SYNOPSIS
    Start all development servers in separate PowerShell windows
    
.DESCRIPTION
    Launches Redis (WSL2), Laravel Artisan, Vite, Ollama, and Laravel Horizon (WSL2) development servers
    in separate PowerShell 7.5.4 windows for easy monitoring.
    
.NOTES
    Author: Umamusume Career Planner Team
    Date: 2026-02-26
    Requires: PowerShell 7.5+, WSL2 with Redis, Laravel project
#>

# Get the project root directory
$ProjectRoot = Split-Path -Parent $PSScriptRoot

Write-Host "🚀 Starting Umamusume Career Planner Development Environment..." -ForegroundColor Cyan
Write-Host ""

# 1. Start Redis in WSL2
Write-Host "📦 Starting Redis in WSL2..." -ForegroundColor Yellow
Start-Process pwsh -ArgumentList @(
    "-NoExit",
    "-Command",
    "Write-Host '🔴 Redis Server (WSL2)' -ForegroundColor Red; Write-Host ''; wsl sudo service redis-server start; Write-Host ''; Write-Host '✓ Redis started. Check status with: wsl sudo service redis-server status' -ForegroundColor Green; Write-Host 'Press Ctrl+C to stop Redis, or close this window.'; Write-Host ''; wsl sudo service redis-server status"
)

# Wait a moment for Redis to initialize
Start-Sleep -Seconds 2

# 2. Start Laravel Development Server
Write-Host "🎯 Starting Laravel Artisan Server..." -ForegroundColor Yellow
Start-Process pwsh -ArgumentList @(
    "-NoExit",
    "-Command",
    "cd '$ProjectRoot'; Write-Host '🔵 Laravel Development Server' -ForegroundColor Blue; Write-Host ''; Write-Host 'Starting server at http://127.0.0.1:8000' -ForegroundColor Cyan; Write-Host ''; php artisan serve"
)

# Wait a moment for Laravel to start
Start-Sleep -Seconds 2

# 3. Start Vite Development Server
Write-Host "⚡ Starting Vite Dev Server..." -ForegroundColor Yellow
Start-Process pwsh -ArgumentList @(
    "-NoExit",
    "-Command",
    "cd '$ProjectRoot'; Write-Host '🟢 Vite Development Server' -ForegroundColor Green; Write-Host ''; Write-Host 'Starting Vite with HMR (Hot Module Replacement)' -ForegroundColor Cyan; Write-Host ''; npm run dev"
)

# Wait a moment for Vite to start
Start-Sleep -Seconds 2

# 4. Start Ollama
Write-Host "🤖 Starting Ollama..." -ForegroundColor Yellow
Start-Process pwsh -ArgumentList @(
    "-NoExit",
    "-Command",
    "Write-Host '🟠 Ollama Server' -ForegroundColor DarkYellow; Write-Host ''; Write-Host 'Starting Ollama at http://127.0.0.1:11434' -ForegroundColor Cyan; Write-Host ''; ollama serve"
)

# Wait a moment for Ollama to start
Start-Sleep -Seconds 2

# 5. Start Laravel Horizon (WSL2 - requires PCNTL/POSIX, not available on Windows PHP)
Write-Host "🌄 Starting Laravel Horizon (WSL2)..." -ForegroundColor Yellow
Start-Process pwsh -ArgumentList @(
    "-NoExit",
    "-Command",
    "cd '$ProjectRoot'; Write-Host '🟣 Laravel Horizon (WSL2)' -ForegroundColor Magenta; Write-Host ''; Write-Host 'Horizon requires WSL2 — PCNTL/POSIX extensions are unavailable on Windows PHP.' -ForegroundColor Yellow; Write-Host 'Queue connection: redis (set in .env)' -ForegroundColor Cyan; Write-Host ''; wsl php artisan horizon"
)

Write-Host ""
Write-Host "✅ All development servers launched!" -ForegroundColor Green
Write-Host ""
Write-Host "📋 Server Information:" -ForegroundColor Cyan
Write-Host "   • Redis:   WSL2 (127.0.0.1:6379)" -ForegroundColor White
Write-Host "   • Laravel: http://127.0.0.1:8000" -ForegroundColor White
Write-Host "   • Vite:    http://localhost:5173" -ForegroundColor White
Write-Host "   • Ollama:  http://127.0.0.1:11434" -ForegroundColor White
Write-Host "   • Horizon: WSL2 › wsl php artisan horizon (dashboard: http://127.0.0.1:8000/horizon)" -ForegroundColor White
Write-Host ""
Write-Host "💡 Tip: Close each server window individually to stop services" -ForegroundColor Yellow
Write-Host "    Or run scripts\stop-dev-servers.ps1 to stop all at once" -ForegroundColor Yellow
Write-Host ""
