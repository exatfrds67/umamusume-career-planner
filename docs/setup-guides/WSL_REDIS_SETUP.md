# WSL Redis Setup Guide

## Problem Summary

Your Windows 10 version (19045.6466) **does not support WSL mirrored networking**, which requires Windows 11. Therefore,
we need to use **port forwarding** to make Redis accessible from Windows.

## Solution: Port Forwarding

### Quick Setup (Recommended)

Run the automated setup script as Administrator:

```powershell
# Right-click PowerShell -> Run as Administrator
cd C:\XAMPP\htdocs\umamusume-career-planner
.\scripts\setup-redis-portforward.ps1
```text

This script will:

1. Get the current WSL IP address
2. Configure port forwarding from `127.0.0.1:6379` to WSL Redis
3. Add Windows Firewall rule
4. Test the connection

## Manual Setup

If you prefer to set up manually:

1. **Get WSL IP address**:

```powershell
wsl hostname -I
# Example output: 172.18.205.249
```text

1. **Add port forwarding** (as Administrator):

```powershell
$wslIP = "172.18.205.249"  # Replace with your WSL IP
netsh interface portproxy add v4tov4 listenport=6379 listenaddress=127.0.0.1 connectport=6379 connectaddress=$wslIP
```text

1. **Add firewall rule** (as Administrator):

```powershell
New-NetFirewallRule -DisplayName "WSL Redis" -Direction Inbound -LocalPort 6379 -Protocol TCP -Action Allow
```

1. **Verify port forwarding**:

```powershell
netsh interface portproxy show all
```text

## Configuration Files

Your `.env` and `.env.testing` files are already configured correctly:

```env
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```text

## Testing

After setup, test the connection:

```bash
# Test Redis connection
php scripts/test-redis.php

# Expected output:
# Attempting to connect to 127.0.0.1:6379...
# Connected successfully!
# Ping response: PONG
# Set test_key
# Get test_key: Hello from Windows with mirrored networking!
```text

## Running Tests

Once Redis is accessible:

```bash
# Clear config cache
php artisan config:clear

# Run Redis-dependent tests
php artisan test --filter=FallbackRecoveryTest --compact

# Run all tests
php artisan test --compact
```

## Troubleshooting

### Port forwarding not working

1. **Check if running as Administrator**:
   - Port forwarding requires Administrator privileges

2. **Verify WSL IP hasn't changed**:

```powershell
wsl hostname -I
```text

- If IP changed, run the setup script again

1. **Check port forwarding is active**:

```powershell
netsh interface portproxy show all
```text

1. **Test port connectivity**:

```powershell
Test-NetConnection -ComputerName 127.0.0.1 -Port 6379
```text

### Redis not running in WSL

```bash
# Check Redis status
wsl sudo service redis-server status

# Start Redis if not running
wsl sudo service redis-server start

# Test from WSL
wsl redis-cli ping
```

## Firewall blocking connection

```powershell
# Check firewall rule
Get-NetFirewallRule -DisplayName "WSL Redis"

# Recreate firewall rule
Remove-NetFirewallRule -DisplayName "WSL Redis"
New-NetFirewallRule -DisplayName "WSL Redis" -Direction Inbound -LocalPort 6379 -Protocol TCP -Action Allow
```text

## Important Notes

### WSL IP Stability

⚠️ **WSL IP addresses change when**:

- WSL is restarted
- Windows is restarted
- Network configuration changes

**Solution**: Run `.\scripts\setup-redis-portforward.ps1` again after WSL restarts.

### Automatic Setup on Boot

To automatically configure port forwarding on Windows startup:

1. Create a scheduled task:

```powershell
$action = New-ScheduledTaskAction -Execute "PowerShell.exe" -Argument "-ExecutionPolicy Bypass -File C:\XAMPP\htdocs\umamusume-career-planner\scripts\setup-redis-portforward.ps1"
$trigger = New-ScheduledTaskTrigger -AtStartup
$principal = New-ScheduledTaskPrincipal -UserId "SYSTEM" -LogonType ServiceAccount -RunLevel Highest
Register-ScheduledTask -TaskName "WSL Redis Port Forward" -Action $action -Trigger $trigger -Principal $principal
```text

1. Or add to Windows startup folder (requires UAC prompt):
   - Create shortcut to `setup-redis-portforward.ps1`
   - Place in: `C:\Users\<YourUsername>\AppData\Roaming\Microsoft\Windows\Start Menu\Programs\Startup`

## Why Mirrored Networking Doesn't Work

**Error message**:

```text
wsl: Mirrored networking mode is not supported: Windows version 19045.6466 does not have the required features.
Falling back to NAT networking.
```

**Explanation**:

- WSL mirrored networking requires Windows 11 build 22H2 or later
- Your Windows 10 version (19045.6466) uses NAT networking
- NAT networking requires manual port forwarding

**Upgrade path** (optional):

- Upgrade to Windows 11 to use mirrored networking
- Mirrored networking eliminates the need for port forwarding
- All WSL services automatically accessible on `127.0.0.1`

## Alternative: Run Redis on Windows

If port forwarding is problematic, you can run Redis natively on Windows:

1. **Download Redis for Windows**:
   - <https://github.com/tporadowski/redis/releases>
   - Or use Chocolatey: `choco install redis-64`

2. **Install and start Redis**:

```powershell
# Redis will start automatically after installation
redis-server
```text

1. **No configuration changes needed** - already set to `127.0.0.1:6379`

## Summary

| Solution            | Complexity | Reliability | Recommended           |
| ------------------- | ---------- | ----------- | --------------------- |
| Port Forwarding     | Medium     | Medium      | ✅ Yes (Windows 10)    |
| Mirrored Networking | Low        | High        | ❌ Requires Windows 11 |
| Windows Redis       | Low        | High        | Alternative           |

## Files Created

- `scripts/setup-redis-portforward.ps1` - Automated port forwarding setup
- `scripts/test-redis.php` - Redis connection test
- `docs/setup-guides/WSL_REDIS_SETUP.md` - This guide
- `.env.testing` - Test environment configuration

## Next Steps

1. **Run the setup script as Administrator**:

```powershell
.\scripts\setup-redis-portforward.ps1
```text

1. **Test the connection**:

```bash
php scripts/test-redis.php
```text

1. **Run the tests**:

```bash
php artisan test --filter=FallbackRecoveryTest --compact
```

1. **If successful**, all Redis-dependent tests should pass! 🎉

## Support

If you encounter issues:

1. Check the troubleshooting section above
2. Review `docs/redis/REDIS_DIAGNOSIS.md` for detailed technical analysis
3. Review `docs/redis/WSL_REDIS_FIX.md` for alternative solutions
