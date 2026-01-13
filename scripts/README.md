# Claude Code Bedrock Scripts

This folder contains scripts to configure and test Claude Code with AWS Bedrock.

## Available Scripts

### Setup Scripts

- **`setup-claude-bedrock-complete.ps1`** - Complete setup with persistent environment variables
- **`setup-aws-credentials.ps1`** - Configure AWS credentials for Bedrock access

### Launch Scripts

- **`launch-claude-bedrock.ps1`** - Launch Claude Code with Bedrock configuration

### Test Scripts

- **`final-bedrock-test.ps1`** - Test Bedrock connection and launch Claude Code
- **`verify-claude-bedrock-setup.ps1`** - Comprehensive verification of setup

## Usage Order

1. Run `setup-aws-credentials.ps1` to configure AWS credentials
2. Run `setup-claude-bedrock-complete.ps1` for complete setup
3. Use `verify-claude-bedrock-setup.ps1` to verify everything is working
4. Use `launch-claude-bedrock.ps1` or `final-bedrock-test.ps1` to start Claude Code

## Environment Variables Set

- `CLAUDE_CODE_USE_BEDROCK=1`
- `AWS_REGION=us-east-1`
- `CLAUDE_CODE_MAX_OUTPUT_TOKENS=4096`
- `MAX_THINKING_TOKENS=1024`
- `ANTHROPIC_MODEL=global.anthropic.claude-sonnet-4-5-20250929-v1:0`
- `ANTHROPIC_SMALL_FAST_MODEL=us.anthropic.claude-haiku-4-5-20251001-v1:0`
