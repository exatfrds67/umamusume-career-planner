# Complete Claude Code Bedrock Setup Script
# This script configures Claude Code to use AWS Bedrock in Kiro

Write-Host "=== Claude Code Bedrock Setup for Kiro ===" -ForegroundColor Green
Write-Host ""

# Set environment variables for current session
Write-Host "Setting environment variables for current session..." -ForegroundColor Yellow
$env:CLAUDE_CODE_USE_BEDROCK = "1"
$env:AWS_REGION = "us-east-1"
$env:CLAUDE_CODE_MAX_OUTPUT_TOKENS = "4096"
$env:MAX_THINKING_TOKENS = "1024"
$env:ANTHROPIC_MODEL = "global.anthropic.claude-sonnet-4-5-20250929-v1:0"
$env:ANTHROPIC_SMALL_FAST_MODEL = "us.anthropic.claude-haiku-4-5-20251001-v1:0"
$env:ANTHROPIC_DEFAULT_HAIKU_MODEL = "us.anthropic.claude-haiku-4-5-20251001-v1:0"

# Set persistent environment variables
Write-Host "Setting persistent environment variables..." -ForegroundColor Yellow
[System.Environment]::SetEnvironmentVariable("CLAUDE_CODE_USE_BEDROCK", "1", "User")
[System.Environment]::SetEnvironmentVariable("AWS_REGION", "us-east-1", "User")
[System.Environment]::SetEnvironmentVariable("CLAUDE_CODE_MAX_OUTPUT_TOKENS", "4096", "User")
[System.Environment]::SetEnvironmentVariable("MAX_THINKING_TOKENS", "1024", "User")
[System.Environment]::SetEnvironmentVariable("ANTHROPIC_MODEL", "global.anthropic.claude-sonnet-4-5-20250929-v1:0", "User")
[System.Environment]::SetEnvironmentVariable("ANTHROPIC_SMALL_FAST_MODEL", "us.anthropic.claude-haiku-4-5-20251001-v1:0", "User")
[System.Environment]::SetEnvironmentVariable("ANTHROPIC_DEFAULT_HAIKU_MODEL", "us.anthropic.claude-haiku-4-5-20251001-v1:0", "User")

Write-Host "Environment variables configured successfully!" -ForegroundColor Green
Write-Host ""

# Check AWS credentials
Write-Host "Checking AWS credentials..." -ForegroundColor Yellow
try {
    $awsIdentity = aws sts get-caller-identity | ConvertFrom-Json
    Write-Host "✓ AWS credentials working - Account: $($awsIdentity.Account)" -ForegroundColor Green
} catch {
    Write-Host "✗ AWS credentials not working. Please run 'aws configure'" -ForegroundColor Red
    exit 1
}

# Check Bedrock model access
Write-Host "Checking Bedrock model access..." -ForegroundColor Yellow
try {
    aws bedrock list-inference-profiles --region us-east-1 --query 'inferenceProfileSummaries[?contains(inferenceProfileName, `claude`)]' --output table
    Write-Host "✓ Bedrock access confirmed" -ForegroundColor Green
} catch {
    Write-Host "✗ Bedrock access issue. Check IAM permissions." -ForegroundColor Red
}

Write-Host ""
Write-Host "=== Testing Claude Code Configuration ===" -ForegroundColor Cyan
Write-Host "Starting Claude Code to test configuration..."
Write-Host "You should see 'API provider: AWS Bedrock' in the status."
Write-Host ""
Write-Host "Commands to test:"
Write-Host "  /status    - Check API provider and configuration"
Write-Host "  /model     - See available models"
Write-Host "  /exit      - Exit Claude Code"
Write-Host ""

# Launch Claude Code with the new environment
claude