# WSL Redis Port Forwarding Setup
# Run as Administrator

Write-Host "=== WSL Redis Port Forwarding Setup ===" -ForegroundColor Cyan
Write-Host ""

# Check if running as Administrator
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)

if (-not $isAdmin) {
    Write-Host "❌ This script must be run as Administrator" -ForegroundColor Red
    Write-Host ""
    Write-Host "Right-click PowerShell and select 'Run as Administrator', then run this script again." -ForegroundColor Yellow
    exit 1
}

# Get WSL IP address
Write-Host "Getting WSL IP address..." -ForegroundColor Yellow
$wslIP = (wsl hostname -I 2>&1 | Out-String).Trim()

if ($LASTEXITCODE -ne 0 -or [string]::IsNullOrWhiteSpace($wslIP)) {
    Write-Host "❌ Failed to get WSL IP address" -ForegroundColor Red
    Write-Host "Make sure WSL is running: wsl" -ForegroundColor Yellow
    exit 1
}

Write-Host "WSL IP: $wslIP" -ForegroundColor Green
Write-Host ""

# Remove existing port forwarding if it exists
Write-Host "Removing existing port forwarding (if any)..." -ForegroundColor Yellow
netsh interface portproxy delete v4tov4 listenport=6379 listenaddress=127.0.0.1 2>&1 | Out-Null

# Add new port forwarding
Write-Host "Adding port forwarding: 127.0.0.1:6379 -> ${wslIP}:6379" -ForegroundColor Yellow
$result = netsh interface portproxy add v4tov4 listenport=6379 listenaddress=127.0.0.1 connectport=6379 connectaddress=$wslIP 2>&1

if ($LASTEXITCODE -eq 0) {
    Write-Host "✅ Port forwarding configured successfully" -ForegroundColor Green
} else {
    Write-Host "❌ Failed to configure port forwarding" -ForegroundColor Red
    Write-Host $result
    exit 1
}

# Add firewall rule
Write-Host ""
Write-Host "Configuring Windows Firewall..." -ForegroundColor Yellow
$firewallRule = Get-NetFirewallRule -DisplayName "WSL Redis" -ErrorAction SilentlyContinue

if ($firewallRule) {
    Write-Host "Firewall rule already exists, updating..." -ForegroundColor Yellow
    Remove-NetFirewallRule -DisplayName "WSL Redis" -ErrorAction SilentlyContinue
}

New-NetFirewallRule -DisplayName "WSL Redis" -Direction Inbound -LocalPort 6379 -Protocol TCP -Action Allow -ErrorAction SilentlyContinue | Out-Null

if ($?) {
    Write-Host "✅ Firewall rule configured" -ForegroundColor Green
} else {
    Write-Host "⚠️  Could not configure firewall rule (may already exist)" -ForegroundColor Yellow
}

# Show current port forwarding
Write-Host ""
Write-Host "Current port forwarding configuration:" -ForegroundColor Yellow
netsh interface portproxy show all

# Test connection
Write-Host ""
Write-Host "Testing Redis connection..." -ForegroundColor Yellow
try {
    $testResult = Test-NetConnection -ComputerName 127.0.0.1 -Port 6379 -WarningAction SilentlyContinue
    if ($testResult.TcpTestSucceeded) {
        Write-Host "✅ Port 6379 is accessible on 127.0.0.1" -ForegroundColor Green
    } else {
        Write-Host "⚠️  Port 6379 is not accessible yet" -ForegroundColor Yellow
        Write-Host "This may take a few seconds to activate" -ForegroundColor Yellow
    }
} catch {
    Write-Host "⚠️  Could not test connection: $_" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "=== Setup Complete ===" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "1. Test Redis: php scripts/test-redis.php"
Write-Host "2. Run tests: php artisan test --filter=FallbackRecoveryTest --compact"
Write-Host ""
Write-Host "Note: If WSL IP changes (after restart), run this script again." -ForegroundColor Yellow
Write-Host ""
