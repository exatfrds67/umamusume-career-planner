# Verify Claude Code Bedrock Setup
Write-Host "=== Claude Code Bedrock Setup Verification ===" -ForegroundColor Green

Write-Host "`n1. Checking Claude Code installation..." -ForegroundColor Cyan
try {
    $version = claude --version
    Write-Host "✓ Claude Code installed: $version" -ForegroundColor Green
} catch {
    Write-Host "✗ Claude Code not found!" -ForegroundColor Red
    exit 1
}

Write-Host "`n2. Checking AWS CLI configuration..." -ForegroundColor Cyan
try {
    $identity = aws sts get-caller-identity | ConvertFrom-Json
    Write-Host "✓ AWS CLI configured - Account: $($identity.Account)" -ForegroundColor Green
} catch {
    Write-Host "✗ AWS CLI not configured!" -ForegroundColor Red
    exit 1
}

Write-Host "`n3. Checking Claude Code configuration..." -ForegroundColor Cyan
if (Test-Path ".kiro/settings/claude-code.json") {
    $config = Get-Content ".kiro/settings/claude-code.json" | ConvertFrom-Json
    $bedrock = $config.claudeCode.environmentVariables | Where-Object { $_.name -eq "CLAUDE_CODE_USE_BEDROCK" }
    $region = $config.claudeCode.environmentVariables | Where-Object { $_.name -eq "AWS_REGION" }
    
    if ($bedrock.value -eq "1" -and $region.value -eq "us-east-1") {
        Write-Host "✓ Claude Code configured for Bedrock" -ForegroundColor Green
        Write-Host "  - Bedrock enabled: $($bedrock.value)" -ForegroundColor Gray
        Write-Host "  - AWS Region: $($region.value)" -ForegroundColor Gray
        Write-Host "  - Login prompt disabled: $($config.claudeCode.disableLoginPrompt)" -ForegroundColor Gray
    } else {
        Write-Host "✗ Claude Code configuration incomplete!" -ForegroundColor Red
    }
} else {
    Write-Host "✗ Claude Code configuration file not found!" -ForegroundColor Red
}

Write-Host "`n4. Testing Bedrock connection..." -ForegroundColor Cyan
Write-Host "Note: This may fail with rate limits if you've exceeded daily quota" -ForegroundColor Yellow

# Set environment for test
$env:CLAUDE_CODE_USE_BEDROCK = "1"
$env:AWS_REGION = "us-east-1"
$env:ANTHROPIC_API_KEY = $null

# Quick test
$result = claude --print "Test" 2>&1
if ($result -like "*API Error: 429*") {
    Write-Host "✓ Bedrock connection working (rate limited)" -ForegroundColor Green
    Write-Host "  You've hit the daily token limit, but authentication is working!" -ForegroundColor Yellow
} elseif ($result -like "*API Error: 403*") {
    Write-Host "✗ Authentication failed!" -ForegroundColor Red
} elseif ($result -like "*Test*" -or $result -like "*Hello*") {
    Write-Host "✓ Bedrock connection fully working!" -ForegroundColor Green
} else {
    Write-Host "? Unexpected response: $result" -ForegroundColor Yellow
}

Write-Host "`n=== Setup Summary ===" -ForegroundColor Green
Write-Host "Claude Code is configured to use Amazon Bedrock with:" -ForegroundColor White
Write-Host "• Model: global.anthropic.claude-sonnet-4-5-20250929-v1:0" -ForegroundColor Gray
Write-Host "• Region: us-east-1" -ForegroundColor Gray
Write-Host "• Authentication: AWS CLI credentials" -ForegroundColor Gray
Write-Host "• Max output tokens: 4096" -ForegroundColor Gray
Write-Host "• Max thinking tokens: 1024" -ForegroundColor Gray

Write-Host "`nTo use Claude Code:" -ForegroundColor Cyan
Write-Host "• In terminal: Run 'claude' command" -ForegroundColor Gray
Write-Host "• In VS Code: Install Claude Code extension and it will use these settings" -ForegroundColor Gray
Write-Host "• Check status: Run 'claude' then type '/status' to verify Bedrock is active" -ForegroundColor Gray