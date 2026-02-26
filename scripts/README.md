# Scripts Directory

This folder contains automation scripts for the Umamusume Career Planner project.

---

## Development Server Scripts

### Quick Start

**Start All Development Servers:**

```powershell
.\scripts\start-dev-servers.ps1
```

Opens **3 separate PowerShell windows**:

1. **Redis Server** (WSL2) - Red header
2. **Laravel Artisan** - Blue header
  ([http://127.0.0.1:8000](http://127.0.0.1:8000))
3. **Vite Dev Server** - Green header
  ([http://localhost:5173](http://localhost:5173))

**Stop All Servers:**

```powershell
.\scripts\stop-dev-servers.ps1
```

### Manual Commands

**Redis (WSL2):**

```powershell
wsl sudo service redis-server start
wsl sudo service redis-server status
wsl sudo service redis-server stop
```

**Laravel:**

```powershell
php artisan serve  # http://127.0.0.1:8000
```

**Vite:**

```powershell
npm run dev  # http://localhost:5173
```

### Troubleshooting

**Redis Connection After WSL2 Restart:**

```powershell
# Get new WSL2 IP
wsl hostname -I

# Update .env
REDIS_HOST=<your-wsl2-ip>

# Clear config
php artisan config:clear
```

**Port Already in Use:**

```powershell
# Laravel (8000)
Get-NetTCPConnection -LocalPort 8000 | Select-Object OwningProcess
Stop-Process -Id <process-id> -Force

# Vite (5173)
Get-Process node | Stop-Process -Force
```

---

## Claude Code Bedrock Scripts

### Available Scripts

**Setup Scripts:**

- `setup-claude-bedrock-complete.ps1` - Complete setup with persistent
  environment variables
- `setup-aws-credentials.ps1` - Configure AWS credentials for Bedrock access

**Launch Scripts:**

- `launch-claude-bedrock.ps1` - Launch Claude Code with Bedrock configuration

**Test Scripts:**

- `final-bedrock-test.ps1` - Test Bedrock connection and launch Claude Code
- `verify-claude-bedrock-setup.ps1` - Comprehensive verification of setup

### Usage Order

1. Run `setup-aws-credentials.ps1` to configure AWS credentials
2. Run `setup-claude-bedrock-complete.ps1` for complete setup
3. Use `verify-claude-bedrock-setup.ps1` to verify everything is working
4. Use `launch-claude-bedrock.ps1` or `final-bedrock-test.ps1` to start Claude
   Code

### Environment Variables Set

```powershell
CLAUDE_CODE_USE_BEDROCK=1
AWS_REGION=us-east-1
CLAUDE_CODE_MAX_OUTPUT_TOKENS=4096
MAX_THINKING_TOKENS=1024
ANTHROPIC_MODEL=global.anthropic.claude-sonnet-4-5-20250929-v1:0
ANTHROPIC_SMALL_FAST_MODEL=us.anthropic.claude-haiku-4-5-20251001-v1:0
```
