# Launch Claude Code with Bedrock - Clean Start
Write-Host "=== Launching Claude Code with AWS Bedrock ===" -ForegroundColor Green

# Set environment variables explicitly
$env:CLAUDE_CODE_USE_BEDROCK = "1"
$env:AWS_REGION = "us-east-1"
$env:CLAUDE_CODE_MAX_OUTPUT_TOKENS = "4096"
$env:MAX_THINKING_TOKENS = "1024"
$env:ANTHROPIC_MODEL = "global.anthropic.claude-sonnet-4-5-20250929-v1:0"
$env:ANTHROPIC_SMALL_FAST_MODEL = "us.anthropic.claude-haiku-4-5-20251001-v1:0"

# Clear any existing auth
Write-Host "Clearing any cached authentication..." -ForegroundColor Yellow

# Verify AWS credentials
Write-Host "Verifying AWS credentials..." -ForegroundColor Yellow
try {
    $identity = aws sts get-caller-identity | ConvertFrom-Json
    Write-Host "✓ AWS Account: $($identity.Account)" -ForegroundColor Green
} catch {
    Write-Host "✗ AWS credentials issue!" -ForegroundColor Red
    exit 1
}

Write-Host "Environment variables:" -ForegroundColor Cyan
Write-Host "  CLAUDE_CODE_USE_BEDROCK: $env:CLAUDE_CODE_USE_BEDROCK"
Write-Host "  AWS_REGION: $env:AWS_REGION"
Write-Host "  CLAUDE_CODE_MAX_OUTPUT_TOKENS: $env:CLAUDE_CODE_MAX_OUTPUT_TOKENS"

Write-Host "`nLaunching Claude Code..." -ForegroundColor Green
Write-Host "After it starts, run '/status' to verify Bedrock usage." -ForegroundColor Cyan
Write-Host "You should see 'API provider: AWS Bedrock' instead of 'API Usage Billing'" -ForegroundColor Cyan
Write-Host ""

# Launch Claude Code
claude