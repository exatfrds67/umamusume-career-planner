# WSL Redis Setup Script
# This script configures WSL mirrored networking for Redis connectivity

Write-Host "=== WSL Redis Setup ===" -ForegroundColor Cyan
Write-Host ""

# Check WSL version
Write-Host "Checking WSL version..." -ForegroundColor Yellow
$wslVersion = wsl --version 2>&1
if ($LASTEXITCODE -eq 0) {
    Write-Host $wslVersion
    Write-Host "✅ WSL is installed" -ForegroundColor Green
} else {
    Write-Host "❌ WSL is not installed or not accessible" -ForegroundColor Red
    exit 1
}

# Create .wslconfig with mirrored networking
Write-Host ""
Write-Host "Configuring WSL mirrored networking..." -ForegroundColor Yellow
$wslConfigPath = "$env:USERPROFILE\.wslconfig"
$wslConfigContent = @"
[wsl2]
networkingMode=mirrored
"@

$wslConfigContent | Out-File -FilePath $wslConfigPath -Encoding ASCII -Force
Write-Host "✅ Created $wslConfigPath" -ForegroundColor Green

# Show the config
Write-Host ""
Write-Host "WSL Config:" -ForegroundColor Yellow
Get-Content $wslConfigPath

# Shutdown WSL
Write-Host ""
Write-Host "Shutting down WSL..." -ForegroundColor Yellow
wsl --shutdown
Write-Host "✅ WSL shutdown complete" -ForegroundColor Green

# Wait for WSL to fully shut down
Write-Host "Waiting 8 seconds for WSL to fully shut down..." -ForegroundColor Yellow
Start-Sleep -Seconds 8

# Start WSL and check Redis
Write-Host ""
Write-Host "Starting WSL and checking Redis..." -ForegroundColor Yellow
$redisStatus = wsl bash -c "sudo service redis-server status" 2>&1
if ($redisStatus -match "is running") {
    Write-Host "✅ Redis is running" -ForegroundColor Green
} else {
    Write-Host "⚠️  Redis is not running, starting it..." -ForegroundColor Yellow
    wsl bash -c "sudo service redis-server start"
    Start-Sleep -Seconds 2
    $redisStatus = wsl bash -c "sudo service redis-server status" 2>&1
    if ($redisStatus -match "is running") {
        Write-Host "✅ Redis started successfully" -ForegroundColor Green
    } else {
        Write-Host "❌ Failed to start Redis" -ForegroundColor Red
        Write-Host $redisStatus
    }
}

# Test Redis connection from WSL
Write-Host ""
Write-Host "Testing Redis connection from WSL..." -ForegroundColor Yellow
$pingResult = wsl bash -c "redis-cli ping" 2>&1
if ($pingResult -eq "PONG") {
    Write-Host "✅ Redis responds to ping from WSL" -ForegroundColor Green
} else {
    Write-Host "❌ Redis did not respond correctly" -ForegroundColor Red
    Write-Host $pingResult
}

# Test Redis connection from Windows
Write-Host ""
Write-Host "Testing Redis connection from Windows..." -ForegroundColor Yellow
try {
    $testResult = php -r "`$r = new Redis(); if (`$r->connect('127.0.0.1', 6379, 2.5)) { echo `$r->ping(); } else { echo 'FAILED'; }" 2>&1
    if ($testResult -match "PONG|1") {
        Write-Host "✅ Redis accessible from Windows at 127.0.0.1:6379" -ForegroundColor Green
    } else {
        Write-Host "❌ Redis not accessible from Windows" -ForegroundColor Red
        Write-Host "Result: $testResult"
    }
} catch {
    Write-Host "❌ Error testing Redis connection: $_" -ForegroundColor Red
}

# Update .env files
Write-Host ""
Write-Host "Updating .env files..." -ForegroundColor Yellow

# Update .env
$envPath = ".env"
if (Test-Path $envPath) {
    $envContent = Get-Content $envPath -Raw
    $envContent = $envContent -replace 'REDIS_HOST=172\.18\.205\.249', 'REDIS_HOST=127.0.0.1'
    $envContent | Out-File -FilePath $envPath -Encoding UTF8 -NoNewline
    Write-Host "✅ Updated $envPath" -ForegroundColor Green
}

# Update .env.testing
$envTestingPath = ".env.testing"
if (Test-Path $envTestingPath) {
    $envTestingContent = Get-Content $envTestingPath -Raw
    $envTestingContent = $envTestingContent -replace 'REDIS_HOST=172\.18\.205\.249', 'REDIS_HOST=127.0.0.1'
    $envTestingContent | Out-File -FilePath $envTestingPath -Encoding UTF8 -NoNewline
    Write-Host "✅ Updated $envTestingPath" -ForegroundColor Green
}

# Clear Laravel config cache
Write-Host ""
Write-Host "Clearing Laravel configuration cache..." -ForegroundColor Yellow
php artisan config:clear
Write-Host "✅ Configuration cache cleared" -ForegroundColor Green

# Final instructions
Write-Host ""
Write-Host "=== Setup Complete ===" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "1. Test Redis connection: php test-redis.php"
Write-Host "2. Run tests: php artisan test --filter=FallbackRecoveryTest --compact"
Write-Host ""
Write-Host "If Redis is not accessible from Windows, you may need to:" -ForegroundColor Yellow
Write-Host "- Restart your computer for .wslconfig changes to take full effect"
Write-Host "- Check Windows Firewall settings"
Write-Host "- Use port forwarding as an alternative (see WSL_REDIS_FIX.md)"
Write-Host ""
