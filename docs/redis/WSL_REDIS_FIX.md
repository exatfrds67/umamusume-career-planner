# WSL2 Redis Connectivity Fix

## Problem Summary

**Root Cause**: WSL2 uses a virtualized network adapter that prevents Windows from directly connecting to WSL's IP address (`172.18.205.249`). While Redis is running correctly in WSL and accessible from within WSL, Windows PHP cannot establish a TCP connection to the WSL IP.

**Evidence**:

- ✅ Redis running in WSL: `wsl redis-cli -h 172.18.205.249 ping` → PONG
- ❌ Windows to WSL connection: `Test-NetConnection -ComputerName 172.18.205.249 -Port 6379` → Timeout
- ❌ PHP connection: `new Redis()->connect('172.18.205.249', 6379)` → Connection timeout

## Solution Options

### Option 1: Use Windows Port Forwarding (Recommended)

Forward port 6379 from Windows localhost to WSL Redis.

#### Step 1: Create port forwarding script

Create `setup-redis-forwarding.ps1`:

```powershell
# Run as Administrator
$wslIP = (wsl hostname -I).Trim()
$port = 6379

# Remove existing forwarding if it exists
netsh interface portproxy delete v4tov4 listenport=$port listenaddress=127.0.0.1

# Add new forwarding
netsh interface portproxy add v4tov4 listenport=$port listenaddress=127.0.0.1 connectport=$port connectaddress=$wslIP

# Add firewall rule
New-NetFirewallRule -DisplayName "WSL Redis" -Direction Inbound -LocalPort $port -Protocol TCP -Action Allow -ErrorAction SilentlyContinue

Write-Host "Port forwarding configured: 127.0.0.1:$port -> $wslIP:$port"
Write-Host "Verify with: netsh interface portproxy show all"
```text

#### Step 2: Run the script as Administrator

```powershell
powershell -ExecutionPolicy Bypass -File setup-redis-forwarding.ps1
```

#### Step 3: Update configuration (Option 1)

Update `.env`:

```env
REDIS_HOST=127.0.0.1
```text

Update `.env.testing`:

```env
REDIS_HOST=127.0.0.1
```

#### Step 4: Test (Option 1)

```bash
php test-redis.php
php artisan test --filter=FallbackRecoveryTest
```text

#### Pros (Option 1)

- Works reliably
- Uses standard localhost address
- Survives WSL restarts (but needs to be re-run if WSL IP changes)

#### Cons (Option 1)

- Requires Administrator privileges
- Needs to be re-run if WSL IP changes
- Requires manual setup

---

### Option 2: Use socat for Port Forwarding (Alternative)

Use `socat` inside WSL to forward connections.

#### Step 1: Install socat in WSL

```bash
wsl sudo apt-get update
wsl sudo apt-get install -y socat
```

#### Step 2: Create forwarding service

Create `/etc/systemd/system/redis-forward.service` in WSL:

```ini
[Unit]
Description=Redis Port Forwarding
After=network.target redis-server.service

[Service]
Type=simple
ExecStart=/usr/bin/socat TCP-LISTEN:6380,fork,reuseaddr TCP:127.0.0.1:6379
Restart=always

[Install]
WantedBy=multi-user.target
```text

#### Step 3: Enable service (Option 2)

```bash
wsl sudo systemctl enable redis-forward
wsl sudo systemctl start redis-forward
```

#### Step 4: Update configuration (Option 2)

```env
REDIS_HOST=127.0.0.1
REDIS_PORT=6380
```text

#### Pros (Option 2)

- Automatic on WSL start
- No Windows configuration needed

#### Cons (Option 2)

- Requires systemd in WSL
- Uses different port (6380)
- More complex setup

---

### Option 3: Run Redis on Windows (Simplest)

Install Redis directly on Windows instead of using WSL.

#### Step 1: Download Redis for Windows

- Download from: <https://github.com/tporadowski/redis/releases>
- Or use Chocolatey: `choco install redis-64`

#### Step 2: Install and start Redis

```powershell
# If using installer, Redis will start automatically
# Or start manually:
redis-server
```

#### Step 3: Update configuration (Option 3)

```env
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```text

#### Pros (Option 3)

- No WSL networking issues
- Simple configuration
- Reliable

#### Cons (Option 3)

- Requires Windows Redis installation
- Different Redis version than WSL

---

### Option 4: Use .wslconfig with mirrored networking (WSL 2.0+)

Configure WSL to use mirrored networking mode.

#### Step 1: Create/edit `.wslconfig`

Create `C:\Users\<YourUsername>\.wslconfig`:

```ini
[wsl2]
networkingMode=mirrored
```

#### Step 2: Restart WSL

```powershell
wsl --shutdown
wsl
```text

#### Step 3: Update configuration (Option 4)

```env
REDIS_HOST=127.0.0.1
```

#### Pros (Option 4)

- Native WSL solution
- Works for all services
- No port forwarding needed

#### Cons (Option 4)

- Requires WSL 2.0+ (you have 2.6.1.0, so this works!)
- Experimental feature
- May affect other WSL networking

---

## Recommended Solution

**Use Option 4: WSL Mirrored Networking** (since you have WSL 2.6.1.0)

This is the cleanest solution and will work for all WSL services.

### Implementation Steps

1. **Create `.wslconfig`**:

```powershell
@"
[wsl2]
networkingMode=mirrored
"@ | Out-File -FilePath "$env:USERPROFILE\.wslconfig" -Encoding ASCII
```text

1. **Restart WSL**:

```powershell
wsl --shutdown
# Wait 8 seconds for WSL to fully shut down
Start-Sleep -Seconds 8
wsl echo "WSL restarted"
```

1. **Verify Redis is running**:

```powershell
wsl sudo service redis-server status
# If not running:
wsl sudo service redis-server start
```text

1. **Update configuration files**:

`.env`:

```env
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

`.env.testing`:

```env
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_DB=15
```text

1. **Test connection**:

```bash
php test-redis.php
```

1. **Run tests**:

```bash
php artisan config:clear
php artisan test --filter=FallbackRecoveryTest --compact
```text

---

## Verification Commands

After implementing the solution:

```powershell
# Test Redis from Windows
php -r "$r = new Redis(); $r->connect('127.0.0.1', 6379); echo $r->ping();"

# Test with redis-cli
redis-cli ping

# Test Laravel connection
php artisan tinker
>>> Redis::connection()->ping()

# Run tests
php artisan test --filter="API Health Monitoring" --compact
```

---

## Troubleshooting

### If mirrored networking doesn't work

1. **Check WSL version**:

```powershell
wsl --version
```text

Should show WSL version 2.0.0 or higher.

1. **Check .wslconfig syntax**:

```powershell
Get-Content "$env:USERPROFILE\.wslconfig"
```

1. **Check WSL networking mode**:

```bash
wsl ip addr show eth0
```text

### If port forwarding doesn't work

1. **Check existing port proxies**:

```powershell
netsh interface portproxy show all
```

1. **Check firewall rules**:

```powershell
Get-NetFirewallRule -DisplayName "WSL Redis"
```text

1. **Test port availability**:

```powershell
Test-NetConnection -ComputerName 127.0.0.1 -Port 6379
```

---

## Why Direct WSL IP Connection Fails

WSL2 uses Hyper-V virtualization with a virtual network adapter. The WSL IP (`172.18.205.249`) is:

- Only accessible from within WSL
- Not directly routable from Windows
- Changes on each WSL restart

This is different from WSL1, which used a shared network stack.

**Microsoft's documentation**:
> "WSL 2 uses a virtualized ethernet adapter. This means that you will need to use the IP address of your host machine to connect to Linux servers from Windows."

---

## Summary

| Solution | Complexity | Reliability | Recommended |
| -------- | --------- | ----------- | ----------- |
| Mirrored Networking | Low | High | ✅ Yes (WSL 2.0+) |
| Port Forwarding | Medium | Medium | If mirrored fails |
| Windows Redis | Low | High | Alternative |
| socat | High | Medium | Not recommended |

Next Steps:

1. Implement mirrored networking (Option 4)
2. Update `.env` and `.env.testing` to use `127.0.0.1`
3. Test connection with `php test-redis.php`
4. Run tests with `php artisan test`
