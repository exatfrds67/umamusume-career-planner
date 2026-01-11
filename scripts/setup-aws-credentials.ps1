# AWS Credentials Setup Script for Claude Code Bedrock
# Run this script to configure AWS credentials for Claude Code

Write-Host "=== AWS Credentials Setup for Claude Code Bedrock ===" -ForegroundColor Green
Write-Host ""

# Check if AWS CLI is installed
$awsInstalled = Get-Command aws -ErrorAction SilentlyContinue
if (-not $awsInstalled) {
    Write-Host "AWS CLI not found. You have several options:" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Option 1: Install AWS CLI"
    Write-Host "  Download from: https://aws.amazon.com/cli/"
    Write-Host "  Then run: aws configure"
    Write-Host ""
    Write-Host "Option 2: Set environment variables manually"
    Write-Host "  Run the commands below with your actual credentials:"
    Write-Host ""
    Write-Host '  $env:AWS_ACCESS_KEY_ID = "your-access-key-id"'
    Write-Host '  $env:AWS_SECRET_ACCESS_KEY = "your-secret-access-key"'
    Write-Host ""
    Write-Host "Option 3: Use Bedrock API Keys (Simplest)"
    Write-Host "  Get a Bedrock API key from AWS Console and run:"
    Write-Host '  $env:AWS_BEARER_TOKEN_BEDROCK = "your-bedrock-api-key"'
    Write-Host ""
} else {
    Write-Host "AWS CLI found. Checking configuration..." -ForegroundColor Green
    try {
        aws sts get-caller-identity
        Write-Host "AWS credentials are configured and working!" -ForegroundColor Green
    } catch {
        Write-Host "AWS credentials not configured. Run: aws configure" -ForegroundColor Yellow
    }
}

Write-Host ""
Write-Host "=== Current Claude Code Environment Variables ===" -ForegroundColor Cyan
Write-Host "CLAUDE_CODE_USE_BEDROCK: $env:CLAUDE_CODE_USE_BEDROCK"
Write-Host "AWS_REGION: $env:AWS_REGION"
Write-Host "CLAUDE_CODE_MAX_OUTPUT_TOKENS: $env:CLAUDE_CODE_MAX_OUTPUT_TOKENS"
Write-Host "AWS_ACCESS_KEY_ID: $(if($env:AWS_ACCESS_KEY_ID) { '***SET***' } else { 'NOT SET' })"
Write-Host "AWS_SECRET_ACCESS_KEY: $(if($env:AWS_SECRET_ACCESS_KEY) { '***SET***' } else { 'NOT SET' })"
Write-Host "AWS_BEARER_TOKEN_BEDROCK: $(if($env:AWS_BEARER_TOKEN_BEDROCK) { '***SET***' } else { 'NOT SET' })"

Write-Host ""
Write-Host "=== Next Steps ===" -ForegroundColor Green
Write-Host "1. Set up AWS credentials using one of the options above"
Write-Host "2. Restart Kiro to pick up the new environment variables"
Write-Host "3. Test Claude Code with: claude"
Write-Host "4. Verify Bedrock usage with: /status"
Write-Host ""
Write-Host "Expected /status output:"
Write-Host "  - API provider: AWS Bedrock"
Write-Host "  - AWS region: us-east-1"
Write-Host "  - Model: global.anthropic.claude-haiku-4-5-20251001-v1:0"