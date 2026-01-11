# Test Claude Code Bedrock Connection
# This script tests if Claude Code is properly configured for AWS Bedrock
Write-Host "=== Claude Code Bedrock Connection Test ===" -ForegroundColor Green

# Kill any existing Claude processes
Get-Process -Name "node" -ErrorAction SilentlyContinue | Where-Object { $_.CommandLine -like "*claude*" } | Stop-Process -Force -ErrorAction SilentlyContinue

# Set environment variables with correct values
$env:CLAUDE_CODE_USE_BEDROCK = "true"
$env:AWS_REGION = "us-east-1"
$env:CLAUDE_CODE_MAX_OUTPUT_TOKENS = "4096"
$env:MAX_THINKING_TOKENS = "1024"

# Clear any API keys that might interfere
$env:ANTHROPIC_API_KEY = $null
$env:CLAUDE_API_KEY = $null

Write-Host "Environment configured:" -ForegroundColor Cyan
Write-Host "  CLAUDE_CODE_USE_BEDROCK: $env:CLAUDE_CODE_USE_BEDROCK"
Write-Host "  AWS_REGION: $env:AWS_REGION"
Write-Host "  ANTHROPIC_API_KEY: $(if($env:ANTHROPIC_API_KEY) { 'SET (BAD)' } else { 'NOT SET (GOOD)' })"

# Verify AWS works
Write-Host "`nTesting AWS connection..." -ForegroundColor Yellow
try {
    $identity = aws sts get-caller-identity | ConvertFrom-Json
    Write-Host "✓ AWS working - Account: $($identity.Account)" -ForegroundColor Green
} catch {
    Write-Host "✗ AWS connection failed!" -ForegroundColor Red
    exit 1
}

Write-Host "`nStarting Claude Code with Bedrock..." -ForegroundColor Green
Write-Host "Look for 'AWS Bedrock' instead of 'API Usage Billing' in the header!" -ForegroundColor Yellow
Write-Host ""

# Start Claude Code
Start-Process -FilePath "claude" -NoNewWindow -Wait