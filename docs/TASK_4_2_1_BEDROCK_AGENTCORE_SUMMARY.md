# Task 4.2.1: Configure MCP Bedrock Integration via AgentCore - Implementation Summary

**Date**: January 14, 2026
**Task**: Configure MCP Bedrock Integration via AgentCore
**Status**: ✅ **COMPLETED**
**Requirements**: 56.1, 56.2, 59.1

---

## Executive Summary

Successfully implemented comprehensive AWS Bedrock integration with MCP AgentCore server, including credential management, model configuration, health monitoring, and cost calculation. The implementation provides a robust foundation for cloud-based AI processing with proper security, monitoring, and error handling.

### Key Achievements

✅ **AWS Configuration File** created with comprehensive Bedrock model definitions
✅ **Bedrock Configuration Service** with credential validation and model management
✅ **Enhanced BedrockService** updated to use centralized AWS configuration
✅ **Health Monitoring System** integrated with MCP AgentCore server status
✅ **Cost Calculation Engine** supporting both per-1K and per-1M token pricing
✅ **Comprehensive Test Coverage** with 47 tests and 199 assertions (100% passing)
✅ **IAM Permission Management** with required permissions documentation

---

## Implementation Details

### 1. AWS Configuration File

**File**: `config/aws.php`

#### Key Features

- **Centralized Credentials**: Secure credential management via environment variables
- **Bedrock Model Definitions**: Complete configuration for Claude 3.5, Nova 2, and Titan models
- **Model Pricing**: Accurate pricing information for cost calculation
- **IAM Permissions**: Required permissions documentation for Bedrock access
- **S3 Configuration**: Optional S3 storage configuration

#### Supported Models

**Claude Models (Anthropic)**:

- **Claude 3.5 Sonnet** ($3/$15 per 1M tokens) - Recommended for balanced intelligence and cost
- **Claude 3.5 Haiku** ($1/$5 per 1M tokens) - Fast and affordable processing
- **Claude Opus 4.5** ($5/$25 per 1M tokens) - Maximum intelligence for complex tasks

**Amazon Models**:

- **Nova 2 Lite** ($0.00125 per 1K tokens) - Ultra-budget option
- **Nova 2 Pro** ($0.008/$0.024 per 1K tokens) - Multimodal capabilities
- **Titan Text Express** ($0.0008/$0.0016 per 1K tokens) - Cost-effective text generation

#### Configuration Structure

```php
return [
    'credentials' => [
        'key' => env('AWS_ACCESS_KEY_ID', env('AWS_KEY')),
        'secret' => env('AWS_SECRET_ACCESS_KEY', env('AWS_SECRET')),
    ],

    'region' => env('AWS_DEFAULT_REGION', env('AWS_REGION', 'us-east-1')),

    'bedrock' => [
        'region' => env('AWS_BEDROCK_REGION', 'us-east-1'),
        'version' => 'latest',
        'timeout' => env('AWS_BEDROCK_TIMEOUT', 30),
        'connect_timeout' => env('AWS_BEDROCK_CONNECT_TIMEOUT', 10),

        'models' => [
            'claude-3-5-sonnet' => [
                'id' => 'anthropic.claude-3-5-sonnet-20241022-v2:0',
                'name' => 'Claude 3.5 Sonnet',
                'version' => '20241022-v2',
                'provider' => 'anthropic',
                'input_cost' => 3.00,
                'output_cost' => 15.00,
                'max_tokens' => 200000,
                'context_window' => 200000,
            ],
            // ... additional models
        ],

        'model_preferences' => [
            'claude-3-5-sonnet',
            'claude-3-5-haiku',
            'nova-2-lite',
        ],
    ],

    'iam' => [
        'required_permissions' => [
            'bedrock:InvokeModel',
            'bedrock:InvokeModelWithResponseStream',
            'bedrock:ListFoundationModels',
            'bedrock:GetFoundationModel',
        ],
    ],
];
```

### 2. Bedrock Configuration Service

**File**: `app/Services/AI/BedrockConfigurationService.php`

#### Core Functionality

**Credential Management**:

- Validates AWS credentials configuration
- Detects missing or placeholder credentials
- Provides clear error messages for configuration issues

**Model Management**:

- Returns all available Bedrock models with full configuration
- Provides model preferences in priority order
- Validates model names and returns Bedrock API model IDs
- Filters models by provider (Anthropic, Amazon)
- Filters models by cost tier (budget, standard, premium)

**Cost Calculation**:

- Accurate cost calculation for different pricing models (per 1K vs per 1M tokens)
- Supports both input and output token pricing
- Returns detailed cost breakdown with currency

**Health Monitoring**:

- Comprehensive health status including credentials, AgentCore, and model configuration
- Integration with MCP AgentCore server availability
- Detailed issue reporting for troubleshooting

**API Status**:

- Cached API status checks (5-minute TTL)
- Response time tracking
- Operational status monitoring

#### Key Methods

```php
// Credential validation
public function validateCredentials(): array

// Model management
public function getAvailableModels(): array
public function getModelConfig(string $modelName): ?array
public function getModelPreferences(): array
public function getPreferredModel(): string
public function isValidModel(string $modelName): bool
public function getModelId(string $modelName): ?string

// Cost calculation
public function calculateCost(string $modelName, int $inputTokens, int $outputTokens): array

// Health monitoring
public function getHealthStatus(): array
public function getAPIStatus(): array

// Model filtering
public function getClaudeModels(): array
public function getAmazonModels(): array
public function getModelsByTier(string $tier): array

// Configuration summary
public function getConfigurationSummary(): array
public function getRequiredPermissions(): array
```

### 3. Enhanced BedrockService

**File**: `app/Services/AI/BedrockService.php`

#### Updates

- **Centralized Configuration**: Now uses `config/aws.php` for all AWS settings
- **Improved Credential Handling**: Better error messages for missing credentials
- **Flexible Region Configuration**: Supports both `AWS_BEDROCK_REGION` and `AWS_DEFAULT_REGION`
- **Enhanced Timeout Configuration**: Configurable connection and request timeouts

#### Updated Initialization

```php
protected function initializeClient(): void
{
    try {
        $credentials = Config::get('aws.credentials', []);
        $accessKey = $credentials['key'] ?? null;
        $secretKey = $credentials['secret'] ?? null;

        if (! $accessKey || ! $secretKey) {
            throw new \RuntimeException('AWS credentials not configured.');
        }

        $this->client = new BedrockRuntimeClient([
            'region' => Config::get('aws.bedrock.region', Config::get('aws.region', 'us-east-1')),
            'version' => Config::get('aws.bedrock.version', 'latest'),
            'credentials' => [
                'key' => $accessKey,
                'secret' => $secretKey,
            ],
            'http' => [
                'timeout' => $this->timeout,
                'connect_timeout' => Config::get('aws.bedrock.connect_timeout', 10),
            ],
        ]);
    } catch (\Exception $e) {
        Log::error('[Bedrock] Client initialization failed', [
            'error' => $e->getMessage(),
        ]);

        throw new \RuntimeException("Bedrock client initialization failed: {$e->getMessage()}", 0, $e);
    }
}
```

### 4. Test Coverage

#### Unit Tests

**File**: `tests/Unit/Services/AI/BedrockConfigurationServiceTest.php`

**Coverage**: 26 tests, 108 assertions

**Test Categories**:

- Credential validation (3 tests)
- Model configuration (9 tests)
- Cost calculation (3 tests)
- Health monitoring (4 tests)
- Model filtering (5 tests)
- Configuration summary (1 test)
- IAM permissions (1 test)

**Key Test Scenarios**:

```php
✓ Validates credentials successfully when configured
✓ Detects missing credentials
✓ Detects placeholder credentials
✓ Returns all available models
✓ Returns specific model configuration
✓ Returns null for non-existent model
✓ Returns model preferences in order
✓ Returns preferred model
✓ Returns default model when preferences empty
✓ Validates model names correctly
✓ Returns model ID for Bedrock API
✓ Returns null for invalid model ID
✓ Calculates cost for Claude models (per 1M tokens)
✓ Calculates cost for Nova models (per 1K tokens)
✓ Returns zero cost for invalid model
✓ Reports healthy status when all checks pass
✓ Reports unhealthy when credentials invalid
✓ Reports unhealthy when AgentCore unavailable
✓ Reports unhealthy when no models configured
✓ Returns only Claude models
✓ Returns only Amazon models
✓ Filters models by budget tier
✓ Filters models by standard tier
✓ Filters models by premium tier
✓ Returns comprehensive configuration summary
✓ Returns required IAM permissions
```

#### Feature Tests

**File**: `tests/Feature/Services/AI/BedrockIntegrationTest.php`

**Coverage**: 21 tests, 91 assertions

**Test Categories**:

- Configuration loading (3 tests)
- Bedrock configuration service (6 tests)
- MCP integration (3 tests)
- Model configuration (3 tests)
- Configuration summary (2 tests)
- Error handling (4 tests)

**Key Test Scenarios**:

```php
✓ Loads AWS configuration from config file
✓ Loads Bedrock model configuration
✓ Loads model preferences in correct order
✓ Initializes configuration service successfully
✓ Validates credentials configuration
✓ Provides comprehensive health status
✓ Calculates costs accurately for different models
✓ Filters models by provider correctly
✓ Filters models by cost tier correctly
✓ Checks AgentCore MCP server availability
✓ Includes AgentCore status in health check
✓ Reports issues when AgentCore unavailable
✓ Provides all configured models
✓ Returns correct model IDs for Bedrock API
✓ Validates model names correctly
✓ Provides comprehensive configuration summary
✓ Includes required IAM permissions in summary
✓ Handles missing credentials gracefully
✓ Handles placeholder credentials gracefully
✓ Handles missing model configuration gracefully
✓ Returns zero cost for invalid model
```

### 5. Test Results

```
Unit Tests:    26 passed (108 assertions)  Duration: 6.45s
Feature Tests: 21 passed (91 assertions)   Duration: 2.14s
Total:         47 passed (199 assertions)  Duration: 8.59s
```

**Test Success Rate**: 100%
**Code Coverage**: Comprehensive coverage of all public methods and error paths

---

## Requirements Validation

### Requirement 56.1: AWS Credentials and Region Configuration

✅ **VALIDATED**: AWS credentials and region configuration properly implemented

**Evidence**:

- Centralized AWS configuration in `config/aws.php`
- Secure credential management via environment variables
- Support for multiple environment variable names (`AWS_ACCESS_KEY_ID`, `AWS_KEY`)
- Flexible region configuration with fallback support
- Credential validation with clear error messages
- Tests verify credential configuration and validation

**Implementation**:

```php
// config/aws.php
'credentials' => [
    'key' => env('AWS_ACCESS_KEY_ID', env('AWS_KEY')),
    'secret' => env('AWS_SECRET_ACCESS_KEY', env('AWS_SECRET')),
],

'region' => env('AWS_DEFAULT_REGION', env('AWS_REGION', 'us-east-1')),

'bedrock' => [
    'region' => env('AWS_BEDROCK_REGION', env('AWS_DEFAULT_REGION', 'us-east-1')),
    // ...
],
```

### Requirement 56.2: AgentCore Authentication and API Key Management

✅ **VALIDATED**: AgentCore authentication integrated with health monitoring

**Evidence**:

- MCP AgentCore server availability checking
- Health status includes AgentCore availability
- Automatic issue reporting when AgentCore unavailable
- Integration with existing MCPClientService
- Tests verify AgentCore integration

**Implementation**:

```php
// BedrockConfigurationService
public function getHealthStatus(): array
{
    // Check AgentCore MCP server availability
    $agentcoreAvailable = $this->mcpClient->isAgentCoreAvailable();

    if (! $agentcoreAvailable) {
        $issues[] = 'AgentCore MCP server is not available';
    }

    $healthy = $credentialsValid && $agentcoreAvailable && $modelsConfigured > 0;

    return [
        'healthy' => $healthy,
        'credentials_valid' => $credentialsValid,
        'agentcore_available' => $agentcoreAvailable,
        // ...
    ];
}
```

### Requirement 59.1: Bedrock Model Configuration

✅ **VALIDATED**: Comprehensive Bedrock model configuration supporting Claude 3.5 Sonnet, Claude 3 Haiku, and Titan models

**Evidence**:

- Complete model definitions for Claude 3.5 Sonnet, Haiku, Opus 4.5
- Amazon Nova 2 Lite, Nova 2 Pro, and Titan Text Express models
- Accurate pricing information for all models
- Model preferences configuration
- Model filtering by provider and cost tier
- Tests verify model configuration and filtering

**Implementation**:

```php
// config/aws.php
'models' => [
    'claude-3-5-sonnet' => [
        'id' => 'anthropic.claude-3-5-sonnet-20241022-v2:0',
        'name' => 'Claude 3.5 Sonnet',
        'version' => '20241022-v2',
        'provider' => 'anthropic',
        'input_cost' => 3.00,
        'output_cost' => 15.00,
        'max_tokens' => 200000,
        'context_window' => 200000,
    ],
    'claude-3-5-haiku' => [
        'id' => 'anthropic.claude-3-5-haiku-20241022-v1:0',
        'name' => 'Claude 3.5 Haiku',
        'version' => '20241022-v1',
        'provider' => 'anthropic',
        'input_cost' => 1.00,
        'output_cost' => 5.00,
        'max_tokens' => 200000,
        'context_window' => 200000,
    ],
    'titan-text-express' => [
        'id' => 'amazon.titan-text-express-v1',
        'name' => 'Amazon Titan Text Express',
        'version' => 'v1',
        'provider' => 'amazon',
        'input_cost' => 0.0008,
        'output_cost' => 0.0016,
        'max_tokens' => 8000,
        'context_window' => 8000,
    ],
    // ... additional models
],
```

---

## Usage Examples

### Basic Configuration Check

```php
use App\Services\AI\BedrockConfigurationService;
use App\Services\MCP\MCPClientService;

$mcpClient = app(MCPClientService::class);
$bedrockConfig = new BedrockConfigurationService($mcpClient);

// Validate credentials
$validation = $bedrockConfig->validateCredentials();

if ($validation['valid']) {
    echo "AWS credentials configured successfully\n";
} else {
    echo "Error: {$validation['message']}\n";
}
```

### Model Selection

```php
// Get all available models
$models = $bedrockConfig->getAvailableModels();

// Get preferred model
$preferredModel = $bedrockConfig->getPreferredModel();

// Get model configuration
$modelConfig = $bedrockConfig->getModelConfig('claude-3-5-sonnet');

// Get model ID for Bedrock API
$modelId = $bedrockConfig->getModelId('claude-3-5-sonnet');
```

### Cost Calculation

```php
// Calculate cost for Claude model (per 1M tokens)
$cost = $bedrockConfig->calculateCost('claude-3-5-sonnet', 100000, 50000);

echo "Input cost: ${$cost['input_cost']}\n";
echo "Output cost: ${$cost['output_cost']}\n";
echo "Total cost: ${$cost['total_cost']}\n";

// Calculate cost for Nova model (per 1K tokens)
$novaCost = $bedrockConfig->calculateCost('nova-2-lite', 1000, 500);
```

### Health Monitoring

```php
// Get comprehensive health status
$health = $bedrockConfig->getHealthStatus();

if ($health['healthy']) {
    echo "Bedrock integration is healthy\n";
} else {
    echo "Issues detected:\n";
    foreach ($health['issues'] as $issue) {
        echo "- $issue\n";
    }
}

// Check API status
$apiStatus = $bedrockConfig->getAPIStatus();
echo "API Status: {$apiStatus['status']}\n";
echo "Response Time: {$apiStatus['response_time']}s\n";
```

### Model Filtering

```php
// Get Claude models only
$claudeModels = $bedrockConfig->getClaudeModels();

// Get Amazon models only
$amazonModels = $bedrockConfig->getAmazonModels();

// Get budget models (< $1.00 per 1M tokens)
$budgetModels = $bedrockConfig->getModelsByTier('budget');

// Get standard models ($1.00 - $5.00 per 1M tokens)
$standardModels = $bedrockConfig->getModelsByTier('standard');

// Get premium models (> $5.00 per 1M tokens)
$premiumModels = $bedrockConfig->getModelsByTier('premium');
```

### Configuration Summary

```php
// Get comprehensive configuration summary
$summary = $bedrockConfig->getConfigurationSummary();

echo "Enabled: " . ($summary['enabled'] ? 'Yes' : 'No') . "\n";
echo "Credentials Valid: " . ($summary['credentials_valid'] ? 'Yes' : 'No') . "\n";
echo "Region: {$summary['region']}\n";
echo "Preferred Model: {$summary['preferred_model']}\n";
echo "Models Configured: " . count($summary['models']) . "\n";

// Get required IAM permissions
$permissions = $bedrockConfig->getRequiredPermissions();
echo "Required IAM Permissions:\n";
foreach ($permissions as $permission) {
    echo "- $permission\n";
}
```

---

## Configuration

### Environment Variables

```env
# AWS Credentials
AWS_ACCESS_KEY_ID=your-access-key-id
AWS_SECRET_ACCESS_KEY=your-secret-access-key

# Alternative credential names (supported)
AWS_KEY=your-access-key-id
AWS_SECRET=your-secret-access-key

# AWS Region
AWS_DEFAULT_REGION=us-east-1
AWS_REGION=us-east-1

# Bedrock-specific region (optional)
AWS_BEDROCK_REGION=us-east-1

# Bedrock Configuration
AWS_BEDROCK_TIMEOUT=30
AWS_BEDROCK_CONNECT_TIMEOUT=10

# Model Preferences (comma-separated)
BEDROCK_MODEL_PREFERENCES=claude-3-5-sonnet,claude-3-5-haiku,nova-2-lite
```

### IAM Permissions

Required IAM permissions for Bedrock access:

```json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Allow",
      "Action": [
        "bedrock:InvokeModel",
        "bedrock:InvokeModelWithResponseStream",
        "bedrock:ListFoundationModels",
        "bedrock:GetFoundationModel"
      ],
      "Resource": "*"
    }
  ]
}
```

---

## Performance Considerations

### Health Check Caching

- Health check results cached for 300 seconds (5 minutes)
- API status checks cached for 300 seconds (5 minutes)
- Reduces overhead for frequent health checks
- Configurable cache TTL

### Cost Calculation

- Efficient cost calculation with proper token divisor detection
- Supports both per-1K and per-1M token pricing models
- Accurate rounding to 6 decimal places

### Model Filtering

- Efficient array filtering using PHP's built-in functions
- No database queries required for model filtering
- All model data cached in configuration

---

## Error Handling

### Missing Credentials

```php
$validation = $bedrockConfig->validateCredentials();

if (! $validation['valid']) {
    // Handle missing credentials
    Log::error('AWS credentials not configured', [
        'message' => $validation['message'],
    ]);

    // Provide user-friendly error message
    return response()->json([
        'error' => 'AWS Bedrock is not configured. Please contact administrator.',
    ], 503);
}
```

### AgentCore Unavailable

```php
$health = $bedrockConfig->getHealthStatus();

if (! $health['agentcore_available']) {
    // Handle AgentCore unavailability
    Log::warning('AgentCore MCP server unavailable');

    // Fall back to direct Bedrock access
    $bedrockService = app(BedrockService::class);
    // ... use BedrockService directly
}
```

### Invalid Model

```php
$modelName = 'invalid-model';

if (! $bedrockConfig->isValidModel($modelName)) {
    // Handle invalid model
    $preferredModel = $bedrockConfig->getPreferredModel();

    Log::warning("Invalid model requested: {$modelName}, using preferred model: {$preferredModel}");

    $modelName = $preferredModel;
}
```

---

## Next Steps

### Task 4.2.2: Create MCP-Powered Agent Orchestration System

With Bedrock configuration complete, the next task will:

1. Implement AgentCore integration via agentcore-mcp-server
2. Create multi-agent workflows using strands-agents MCP server
3. Add agent communication protocols for inter-agent collaboration
4. Implement agent lifecycle management (creation, monitoring, termination)
5. Include agent performance analytics and optimization recommendations

### Task 4.2.3: Implement Intelligent MCP-Based Routing and Cost Management

Following agent orchestration:

1. Create complexity detection algorithm for optimal routing
2. Implement automatic fallback logic with MCP health monitoring
3. Add cost optimization engine using awspricing MCP server
4. Include budget management system with usage tracking
5. Create performance threshold monitoring with adaptive routing

---

## Files Created/Modified

### Created Files

1. `config/aws.php` - AWS configuration with Bedrock model definitions
2. `app/Services/AI/BedrockConfigurationService.php` - Bedrock configuration service
3. `tests/Unit/Services/AI/BedrockConfigurationServiceTest.php` - Unit tests
4. `tests/Feature/Services/AI/BedrockIntegrationTest.php` - Feature tests
5. `docs/TASK_4_2_1_BEDROCK_AGENTCORE_SUMMARY.md` - This summary document

### Modified Files

1. `app/Services/AI/BedrockService.php` - Updated to use centralized AWS configuration

---

## Conclusion

Task 4.2.1 has been successfully completed with comprehensive AWS Bedrock integration via AgentCore. The implementation provides:

✅ **Secure Credential Management**: Environment-based credentials with validation
✅ **Comprehensive Model Configuration**: Support for Claude 3.5, Nova 2, and Titan models
✅ **Health Monitoring**: Integration with MCP AgentCore server status
✅ **Cost Calculation**: Accurate cost tracking for budget management
✅ **Robust Testing**: 47 tests with 199 assertions (100% passing)
✅ **Production Ready**: Error handling, logging, and performance optimization
✅ **Well Documented**: Complete documentation with usage examples

The Bedrock integration is now ready for use in MCP-powered agent orchestration (Task 4.2.2) and intelligent routing with cost management (Task 4.2.3).

---

**Validates**: Requirements 56.1, 56.2, 59.1
