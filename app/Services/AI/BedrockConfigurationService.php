<?php

namespace App\Services\AI;

use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

/**
 * AWS Bedrock Configuration Service
 *
 * Manages AWS Bedrock configuration, credential validation, model selection,
 * and health monitoring with MCP integration.
 *
 * Requirements: 56.1, 56.2, 59.1
 */
class BedrockConfigurationService
{
    protected MCPClientService $mcpClient;

    /** @var array<string, array<string, mixed>> */
    protected array $models;

    /** @var array<string> */
    protected array $modelPreferences;

    public function __construct(MCPClientService $mcpClient)
    {
        $this->mcpClient = $mcpClient;
        $models = Config::get('aws.bedrock.models', []);
        $this->models = is_array($models) ? $models : [];
        $modelPreferences = Config::get('aws.bedrock.model_preferences', []);
        $this->modelPreferences = is_array($modelPreferences) ? $modelPreferences : [];
    }

    /**
     * Validate AWS credentials for Bedrock access
     *
     * @return array{
     *     valid: bool,
     *     message: string,
     *     credentials_configured: bool,
     *     region: string
     * }
     */
    public function validateCredentials(): array
        $credentials = Config::get('aws.credentials', []);
        $credentials = is_array($credentials) ? $credentials : [];
        $accessKey = isset($credentials['key']) && is_string($credentials['key']) ? $credentials['key'] : null;
        $secretKey = isset($credentials['secret']) && is_string($credentials['secret']) ? $credentials['secret'] : null;
        $region = (string) Config::get('aws.bedrock.region', Config::get('aws.region', 'us-east-1'));

        if (! $accessKey || ! $secretKey) {
            return [
                'valid' => false,
                'message' => 'AWS credentials not configured. Set AWS_ACCESS_KEY_ID and AWS_SECRET_ACCESS_KEY environment variables.',
                'credentials_configured' => false,
                'region' => $region,
            ];
        }

        // Check if credentials are placeholder values
        if ($accessKey === 'your-access-key-id' || $secretKey === 'your-secret-access-key') {
            return [
                'valid' => false,
                'message' => 'AWS credentials are placeholder values. Please configure valid credentials.',
                'credentials_configured' => false,
                'region' => $region,
            ];
        }

        return [
            'valid' => true,
            'message' => 'AWS credentials configured successfully.',
            'credentials_configured' => true,
            'region' => $region,
        ];
    }

    /**
     * Get available Bedrock models with configuration
     *
     * @return array<string, array<string, mixed>>
     */
    public function getAvailableModels(): array
        return $this->models;
    }

    /**
     * Get model configuration by name
     *
     * @return array<string, mixed>|null
     */
    public function getModelConfig(string $modelName): ?array
    {
        return $this->models[$modelName] ?? null;
    }

    /**
     * Get model preferences in order
     *
     * @return array<string>
     */
    public function getModelPreferences(): array
        return $this->modelPreferences;
    }

    /**
     * Get preferred model based on configuration
     */
    public function getPreferredModel(): string
    {
        if (empty($this->modelPreferences)) {
            return 'claude-3-5-sonnet'; // Default fallback
        }

        return $this->modelPreferences[0];
    }

    /**
     * Calculate cost for a request
     *
     * @return array{
     *     input_cost: float,
     *     output_cost: float,
     *     total_cost: float,
     *     currency: string
     * }
     */
    public function calculateCost(): array
        $model = $this->getModelConfig($modelName);

        if (! $model) {
            return [
                'input_cost' => 0.0,
                'output_cost' => 0.0,
                'total_cost' => 0.0,
                'currency' => 'USD',
            ];
        }

        $inputCost = $model['input_cost'] ?? 0.0;
        $outputCost = $model['output_cost'] ?? 0.0;

        // Determine if cost is per 1K or 1M tokens
        $divisor = ($inputCost < 1.0) ? 1000 : 1000000;

        $calculatedInputCost = ($inputTokens / $divisor) * $inputCost;
        $calculatedOutputCost = ($outputTokens / $divisor) * $outputCost;

        return [
            'input_cost' => round($calculatedInputCost, 6),
            'output_cost' => round($calculatedOutputCost, 6),
            'total_cost' => round($calculatedInputCost + $calculatedOutputCost, 6),
            'currency' => 'USD',
        ];
    }

    /**
     * Get comprehensive Bedrock health status
     *
     * @return array{
     *     healthy: bool,
     *     credentials_valid: bool,
     *     agentcore_available: bool,
     *     models_configured: int,
     *     preferred_model: string,
     *     region: string,
     *     issues: array<string>
     * }
     */
    public function getHealthStatus(): array
        $issues = [];

        // Check credentials
        $credentialsCheck = $this->validateCredentials();
        $credentialsValid = $credentialsCheck['valid'];

        if (! $credentialsValid) {
            $issues[] = $credentialsCheck['message'];
        }

        // Check AgentCore MCP server availability
        $agentcoreAvailable = $this->mcpClient->isAgentCoreAvailable();

        if (! $agentcoreAvailable) {
            $issues[] = 'AgentCore MCP server is not available';
        }

        // Check model configuration
        $modelsConfigured = count($this->models);

        if ($modelsConfigured === 0) {
            $issues[] = 'No Bedrock models configured';
        }

        $healthy = $credentialsValid && $agentcoreAvailable && $modelsConfigured > 0;

        return [
            'healthy' => $healthy,
            'credentials_valid' => $credentialsValid,
            'agentcore_available' => $agentcoreAvailable,
            'models_configured' => $modelsConfigured,
            'preferred_model' => $this->getPreferredModel(),
            'region' => $credentialsCheck['region'],
            'issues' => $issues,
        ];
    }

    /**
     * Get Bedrock API status with caching
     *
     * @return array{
     *     status: string,
     *     last_check: int,
     *     response_time: float|null
     * }
     */
    public function getAPIStatus(): array
        $cacheKey = 'bedrock_api_status';

        $cached = Cache::remember($cacheKey, 300, function () {
            $startTime = microtime(true);

            try {
                // Use BedrockService to check availability
                $bedrockService = app(BedrockService::class);
                $available = $bedrockService->isAvailable();

                $responseTime = microtime(true) - $startTime;

                return [
                    'status' => $available ? 'operational' : 'unavailable',
                    'last_check' => time(),
                    'response_time' => round($responseTime, 3),
                ];
            } catch (\Exception $e) {
                Log::warning('[BedrockConfig] API status check failed', [
                    'error' => $e->getMessage(),
                ]);

                return [
                    'status' => 'error',
                    'last_check' => time(),
                    'response_time' => null,
                ];
            }
        });

        return is_array($cached) ? $cached : [
            'status' => 'error',
            'last_check' => time(),
            'response_time' => null,
        ];
    }

    /**
     * Get required IAM permissions for Bedrock
     *
     * @return array<string>
     */
    public function getRequiredPermissions(): array
        $permissions = Config::get('aws.iam.required_permissions', [
            'bedrock:InvokeModel',
            'bedrock:InvokeModelWithResponseStream',
            'bedrock:ListFoundationModels',
            'bedrock:GetFoundationModel',
        ]);

        return is_array($permissions) ? $permissions : [];
    }

    /**
     * Get comprehensive configuration summary
     *
     * @return array{
     *     enabled: bool,
     *     credentials_valid: bool,
     *     region: string,
     *     models: array<string, array<string, mixed>>,
     *     preferred_model: string,
     *     model_preferences: array<string>,
     *     health_status: array<string, mixed>,
     *     api_status: array<string, mixed>,
     *     required_permissions: array<string>
     * }
     */
    public function getConfigurationSummary(): array
        $credentialsCheck = $this->validateCredentials();

        return [
            'enabled' => (bool) Config::get('ai.bedrock.enabled', true),
            'credentials_valid' => (bool) $credentialsCheck['valid'],
            'region' => (is_string($credentialsCheck) ? (string) $credentialsCheck : '')['region'],
            'models' => $this->getAvailableModels(),
            'preferred_model' => $this->getPreferredModel(),
            'model_preferences' => $this->getModelPreferences(),
            'health_status' => $this->getHealthStatus(),
            'api_status' => $this->getAPIStatus(),
            'required_permissions' => $this->getRequiredPermissions(),
        ];
    }

    /**
     * Validate model name
     */
    public function isValidModel(string $modelName): bool
    {
        return isset($this->models[$modelName]);
    }

    /**
     * Get model ID for Bedrock API
     */
    public function getModelId(string $modelName): ?string
    {
        $model = $this->getModelConfig($modelName);

        if (! is_array($model)) {
            return null;
        }

        $id = (is_array($model) && isset($model['id']) ? $model['id'] : null);

        return is_string($id) ? $id : null;
    }

    /**
     * Get all Claude models
     *
     * @return array<string, array<string, mixed>>
     */
    public function getClaudeModels(): array
        return array_filter($this->models, fn ($model) => $model['provider'] === 'anthropic');
    }

    /**
     * Get all Amazon models (Nova, Titan)
     *
     * @return array<string, array<string, mixed>>
     */
    public function getAmazonModels(): array
        return array_filter($this->models, fn ($model) => $model['provider'] === 'amazon');
    }

    /**
     * Get models by cost tier
     *
     * @param  string  $tier  'budget', 'standard', 'premium'
     * @return array<string, array<string, mixed>>
     */
    public function getModelsByTier(): array
        return match ($tier) {
            'budget' => array_filter($this->models, fn ($model) => ($model['input_cost'] ?? 0) < 1.0),
            'standard' => array_filter($this->models, fn ($model) => ($model['input_cost'] ?? 0) >= 1.0 && ($model['input_cost'] ?? 0) < 5.0),
            'premium' => array_filter($this->models, fn ($model) => ($model['input_cost'] ?? 0) >= 5.0),
            default => [],
        };
    }
}
