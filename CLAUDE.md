# Claude AI Configuration & Technical Documentation

## Project Context

This project uses Claude AI as the primary development assistant with persistent memory capabilities through the Memory MCP Server. The configuration focuses on AWS Bedrock integration for enhanced performance and cost management.

## AWS Bedrock Configuration

### Overview

This project is configured to use Claude via AWS Bedrock instead of Anthropic's direct API to avoid credit limitations and provide better performance for development workflows.

### Prerequisites

- AWS account with Bedrock access enabled
- Access to Claude models (Claude Sonnet 4.5) in Bedrock
- AWS CLI installed and configured (optional)
- Appropriate IAM permissions

### Initial Setup

#### 1. Submit Use Case Details (First-time users)

1. Navigate to [Amazon Bedrock console](https://console.aws.amazon.com/bedrock/)
2. Select **Chat/Text playground**
3. Choose any Anthropic model and fill out the use case form (required once per account)

#### 2. AWS Credentials Configuration

Choose one of these authentication methods:

##### Option A: AWS CLI Configuration (Recommended)

```bash
aws configure
# Enter your AWS Access Key ID and Secret Access Key
# Region: us-east-1
# Output format: json
```

##### Option B: Environment Variables (Access Key)

```powershell
# PowerShell (Windows)
$env:AWS_ACCESS_KEY_ID = "your-access-key-id"
$env:AWS_SECRET_ACCESS_KEY = "your-secret-access-key"
$env:AWS_SESSION_TOKEN = "your-session-token"  # if using temporary credentials
```

##### Option C: Bedrock API Keys (Simplest)

```powershell
$env:AWS_BEARER_TOKEN_BEDROCK = "your-bedrock-api-key"
```

##### Option D: SSO Profile

```bash
aws sso login --profile=your-profile-name
export AWS_PROFILE=your-profile-name
```

## Environment Variables Configuration

### Required Claude Code Variables

```powershell
# Enable Bedrock integration
$env:CLAUDE_CODE_USE_BEDROCK = "1"
$env:AWS_REGION = "us-east-1"  # Required - Claude Code doesn't read from .aws config

# Optional: Override region for small/fast model (Haiku)
$env:ANTHROPIC_SMALL_FAST_MODEL_AWS_REGION = "us-west-2"
```

### Model Configuration (Optional)

```powershell
# Default models (these are already set by default):
# Primary: global.anthropic.claude-sonnet-4-5-20250929-v1:0
# Small/Fast: us.anthropic.claude-haiku-4-5-20251001-v1:0

# To customize models:
$env:ANTHROPIC_MODEL = "global.anthropic.claude-sonnet-4-5-20250929-v1:0"
$env:ANTHROPIC_SMALL_FAST_MODEL = "us.anthropic.claude-haiku-4-5-20251001-v1:0"

# For Haiku 4.5 (manual upgrade required):
$env:ANTHROPIC_DEFAULT_HAIKU_MODEL = "us.anthropic.claude-haiku-4-5-20251001-v1:0"

# Optional: Disable prompt caching if needed
$env:DISABLE_PROMPT_CACHING = "1"
```

### Performance Optimization Settings

```powershell
# Recommended for Bedrock (prevents burndown throttling issues)
$env:CLAUDE_CODE_MAX_OUTPUT_TOKENS = "4096"
$env:MAX_THINKING_TOKENS = "1024"
```

### Setting Persistent Environment Variables

```powershell
[System.Environment]::SetEnvironmentVariable("CLAUDE_CODE_USE_BEDROCK", "1", "User")
[System.Environment]::SetEnvironmentVariable("AWS_REGION", "us-east-1", "User")
[System.Environment]::SetEnvironmentVariable("CLAUDE_CODE_MAX_OUTPUT_TOKENS", "4096", "User")
[System.Environment]::SetEnvironmentVariable("MAX_THINKING_TOKENS", "1024", "User")
```

## IAM Policy Configuration

### Required IAM Policy JSON

```json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Sid": "AllowModelAndInferenceProfileAccess",
      "Effect": "Allow",
      "Action": [
        "bedrock:InvokeModel",
        "bedrock:InvokeModelWithResponseStream",
        "bedrock:ListInferenceProfiles"
      ],
      "Resource": [
        "arn:aws:bedrock:*:*:inference-profile/*",
        "arn:aws:bedrock:*:*:application-inference-profile/*",
        "arn:aws:bedrock:*:*:foundation-model/*"
      ]
    },
    {
      "Sid": "AllowMarketplaceSubscription",
      "Effect": "Allow",
      "Action": [
        "aws-marketplace:ViewSubscriptions",
        "aws-marketplace:Subscribe"
      ],
      "Resource": "*",
      "Condition": {
        "StringEquals": {
          "aws:CalledViaLast": "bedrock.amazonaws.com"
        }
      }
    }
  ]
}
```

### Applying IAM Policy

1. Go to [AWS IAM Console](https://console.aws.amazon.com/iam/)
2. Navigate to **Policies** → **Create Policy**
3. Choose **JSON** tab and paste the policy above
4. Name it `Claude_Code_IAM_Policy`
5. Attach this policy to your IAM user or role

## Configuration Verification

### Testing Your Setup

1. Launch Claude Code: `claude`
2. Run `/status` command
3. Should show:
   - API provider: AWS Bedrock
   - AWS region: us-east-1
   - Model: your configured model

### Important Configuration Notes

- `/login` and `/logout` commands are disabled when using Bedrock
- `AWS_REGION` is required - Claude Code doesn't read from `.aws` config
- Claude Code uses Bedrock Invoke API, not Converse API
- Prompt caching may not be available in all regions

## Technical Troubleshooting

### AWS Bedrock Authentication Issues

#### "Credit balance too low" Error

**Issue:** This error indicates Claude Code is still using Anthropic's direct API instead of Bedrock.

**Diagnostic Steps:**

1. **Verify Environment Variables:**

```powershell
# Check if variables are set
echo $env:CLAUDE_CODE_USE_BEDROCK
echo $env:AWS_REGION
```

1. **Set Variables in Same Session:**

```powershell
$env:CLAUDE_CODE_USE_BEDROCK = "1"
$env:AWS_REGION = "us-east-1"
# Launch Claude Code from same session
claude
```

1. **Verify AWS Credentials:**

```bash
aws sts get-caller-identity
```

1. **Check Model Availability:**

```bash
aws bedrock list-inference-profiles --region us-east-1
```

**Common Resolution Patterns:**

- **Region Issues:** Switch to supported region (`us-east-1`, `us-west-2`)
- **On-demand throughput error:** Use inference profile IDs instead of model ARNs
- **Continuous "thinking":** Usually indicates authentication issues

**Working Solution Steps:**

1. Set environment variables in PowerShell
2. Launch Claude Code from the same session: `claude`
3. Verify with `/status` - should show "API provider: AWS Bedrock"
4. If still showing Anthropic API, restart terminal and try again

### Tool Execution Errors

#### String Replacement Tool Errors

##### Error: "No path provided"

**When it happens:**
This error occurs when using the `strReplace` tool without providing the required `path` parameter. Common causes:

1. Copy-pasting incomplete function calls
2. Accidentally submitting empty or incomplete strReplace calls
3. System glitches that clear parameters before submission

**Resolution:**
Always ensure the `strReplace` call includes all required parameters:

- `path`: The file path to modify
- `oldStr`: The exact text to replace
- `newStr`: The replacement text

**Example of correct usage:**

```text
strReplace(
  path="docs/example.md",
  oldStr="**Bold Text**",
  newStr="### Bold Text"
)
```

**Best Practices:**

- Double-check all parameters before submitting
- Use specific context when replacing text that appears multiple times
- Test with small, unique text patterns first

## Development Environment Issues

### Cross-Platform Command Execution (Windows/WSL)

#### PowerShell/WSL Command Chain Errors

##### Error: "grep: The term 'grep' is not recognized"

**When it happens:**
This error occurs when trying to use Unix commands directly in PowerShell instead of within WSL context.

**Incorrect usage:**

```powershell
wsl ps aux | grep horizon  # This fails because grep runs in PowerShell context
```

**Correct usage:**

```powershell
wsl bash -c "ps aux | grep horizon"  # This works because grep runs in WSL context
```

**Command Execution Rules:**

- Always wrap Unix command chains in `wsl bash -c "command1 | command2"`
- Never pipe WSL output directly to Unix commands in PowerShell
- Use PowerShell equivalents when working in Windows context:
  - `grep` → `Select-String`
  - `ps aux` → `Get-Process`
  - `kill` → `Stop-Process`

**Error Prevention:**

- Test command syntax before execution
- Use proper WSL context wrapping for Unix command chains
- Document working command patterns for future reference

### Laravel Horizon on Windows/WSL

#### Problem Statement

Laravel Horizon requires PCNTL and POSIX PHP extensions for queue processing and monitoring, which are not available on Windows PHP installations.

#### Solution: WSL-Based Horizon Installation

Use Windows Subsystem for Linux (WSL) to run Laravel Horizon while keeping the main Laravel application on Windows.

#### WSL Setup Prerequisites

- Windows with WSL2 installed
- Redis running in WSL
- Laravel 12 project on Windows

#### Implementation Steps

##### 1. Verify WSL PHP and Extensions

```bash
# Check WSL PHP version
wsl php --version

# Verify required extensions are available
wsl bash -c "php -m | grep -E '(pcntl|posix)'"
```

##### 2. Upgrade WSL PHP to Match Project Requirements

```bash
# Update package lists
wsl sudo apt update

# Install PHP 8.4 and required extensions
wsl sudo apt install -y php8.4-cli php8.4-common php8.4-mysql php8.4-xml php8.4-curl php8.4-mbstring php8.4-zip php8.4-bcmath php8.4-intl php8.4-redis

# Verify installation
wsl php --version
# Should show PHP 8.4.x
```

##### 3. Install Laravel Horizon via WSL

```bash
# Install Horizon using WSL composer
wsl composer require laravel/horizon --dev

# Publish Horizon configuration
wsl php artisan horizon:install
```

##### 4. Configure Environment for Redis Queues

Update your `.env` file to use Redis for queues:

```env
# Cache Configuration
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Horizon Configuration
HORIZON_NAME="YourAppName"
HORIZON_PATH=horizon
```

#### Usage Instructions

**Web Application (Windows):**

```bash
php artisan serve
```

**Queue Processing (WSL):**

```bash
wsl php artisan horizon
```

**Horizon Dashboard:**

- Access at: `http://your-app.local/horizon`
- Monitor queues, failed jobs, and performance metrics

#### Troubleshooting Horizon Issues

**PHP Version Mismatch:**

- Ensure WSL PHP version matches project requirements
- Check composer dependencies for minimum PHP version

**Redis Connection Issues:**

```bash
# Start Redis in WSL if not running
wsl sudo service redis-server start

# Check Redis status
wsl sudo service redis-server status
```

**Permission Issues:**

```bash
# Fix Laravel storage permissions
wsl chmod -R 775 storage bootstrap/cache
```

**Horizon Not Starting:**

```bash
# Clear configuration cache
wsl php artisan config:clear

# Check Horizon status
wsl php artisan horizon:status
```

This solution enables full Laravel Horizon functionality on Windows development environments while maintaining the existing XAMPP setup for web serving.
