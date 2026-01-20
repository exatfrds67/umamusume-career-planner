<?php

/**
 * @property App\Services\MCP\MCPClientService&Mockery\MockInterface $mcpClient
 * @property App\Services\AI\BedrockConfigurationService $service
 */

use App\Services\AI\BedrockConfigurationService;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Config;

/**
 * Bedrock Configuration Service Tests
 *
 * Tests AWS Bedrock configuration management, credential validation,
 * model selection, and health monitoring.
 *
 * Requirements: 56.1, 56.2, 59.1
 */
beforeEach(function () {
    // Mock MCP Client
    /** @var MCPClientService&Mockery\MockInterface $mcpClient */
    $mcpClient = Mockery::mock(MCPClientService::class);
    $this->mcpClient = $mcpClient;

    // Set up test configuration
    Config::set('aws.credentials', [
        'key' => 'test-access-key',
        'secret' => 'test-secret-key',
    ]);

    Config::set('aws.region', 'us-east-1');
    Config::set('aws.bedrock.region', 'us-east-1');

    Config::set('aws.bedrock.models', [
        'claude-3-5-sonnet' => [
            'id' => 'anthropic.claude-3-5-sonnet-20241022-v2:0',
            'name' => 'Claude 3.5 Sonnet',
            'provider' => 'anthropic',
            'input_cost' => 3.00,
            'output_cost' => 15.00,
        ],
        'claude-3-5-haiku' => [
            'id' => 'anthropic.claude-3-5-haiku-20241022-v1:0',
            'name' => 'Claude 3.5 Haiku',
            'provider' => 'anthropic',
            'input_cost' => 1.00,
            'output_cost' => 5.00,
        ],
        'nova-2-lite' => [
            'id' => 'amazon.nova-lite-v1:0',
            'name' => 'Amazon Nova 2 Lite',
            'provider' => 'amazon',
            'input_cost' => 0.00125,
            'output_cost' => 0.00125,
        ],
    ]);

    Config::set('aws.bedrock.model_preferences', [
        'claude-3-5-sonnet',
        'claude-3-5-haiku',
        'nova-2-lite',
    ]);

    $this->service = new BedrockConfigurationService($mcpClient);
});

afterEach(function () {
    Mockery::close();
});

describe('Credential Validation', function () {
    it('validates credentials successfully when configured', function () {
        $result = $this->service->validateCredentials();

        expect($result)->toHaveKeys(['valid', 'message', 'credentials_configured', 'region'])
            ->and($result['valid'])->toBeTrue()
            ->and($result['credentials_configured'])->toBeTrue()
            ->and($result['region'])->toBe('us-east-1');
    });

    it('detects missing credentials', function () {
        Config::set('aws.credentials', [
            'key' => null,
            'secret' => null,
        ]);

        $service = new BedrockConfigurationService($this->mcpClient);
        $result = $service->validateCredentials();

        expect($result['valid'])->toBeFalse()
            ->and($result['credentials_configured'])->toBeFalse()
            ->and($result['message'])->toContain('not configured');
    });

    it('detects placeholder credentials', function () {
        Config::set('aws.credentials', [
            'key' => 'your-access-key-id',
            'secret' => 'your-secret-access-key',
        ]);

        $service = new BedrockConfigurationService($this->mcpClient);
        $result = $service->validateCredentials();

        expect($result['valid'])->toBeFalse()
            ->and($result['message'])->toContain('placeholder values');
    });
});

describe('Model Configuration', function () {
    it('returns all available models', function () {
        $models = $this->service->getAvailableModels();

        expect($models)->toBeArray()
            ->and($models)->toHaveKeys(['claude-3-5-sonnet', 'claude-3-5-haiku', 'nova-2-lite'])
            ->and(count($models))->toBe(3);
    });

    it('returns specific model configuration', function () {
        $model = $this->service->getModelConfig('claude-3-5-sonnet');

        expect($model)->toBeArray()
            ->and($model)->toHaveKeys(['id', 'name', 'provider', 'input_cost', 'output_cost'])
            ->and($model['provider'])->toBe('anthropic')
            ->and($model['input_cost'])->toBe(3.00);
    });

    it('returns null for non-existent model', function () {
        $model = $this->service->getModelConfig('non-existent-model');

        expect($model)->toBeNull();
    });

    it('returns model preferences in order', function () {
        $preferences = $this->service->getModelPreferences();

        expect($preferences)->toBeArray()
            ->and($preferences)->toHaveCount(3)
            ->and($preferences[0])->toBe('claude-3-5-sonnet');
    });

    it('returns preferred model', function () {
        $preferred = $this->service->getPreferredModel();

        expect($preferred)->toBe('claude-3-5-sonnet');
    });

    it('returns default model when preferences empty', function () {
        Config::set('aws.bedrock.model_preferences', []);

        $service = new BedrockConfigurationService($this->mcpClient);
        $preferred = $service->getPreferredModel();

        expect($preferred)->toBe('claude-3-5-sonnet');
    });

    it('validates model names correctly', function () {
        expect($this->service->isValidModel('claude-3-5-sonnet'))->toBeTrue()
            ->and($this->service->isValidModel('invalid-model'))->toBeFalse();
    });

    it('returns model ID for Bedrock API', function () {
        $modelId = $this->service->getModelId('claude-3-5-sonnet');

        expect($modelId)->toBe('anthropic.claude-3-5-sonnet-20241022-v2:0');
    });

    it('returns null for invalid model ID', function () {
        $modelId = $this->service->getModelId('invalid-model');

        expect($modelId)->toBeNull();
    });
});

describe('Cost Calculation', function () {
    it('calculates cost for Claude models (per 1M tokens)', function () {
        $cost = $this->service->calculateCost('claude-3-5-sonnet', 100000, 50000);

        expect($cost)->toHaveKeys(['input_cost', 'output_cost', 'total_cost', 'currency'])
            ->and($cost['input_cost'])->toBe(0.3) // (100000 / 1000000) * 3.00
            ->and($cost['output_cost'])->toBe(0.75) // (50000 / 1000000) * 15.00
            ->and($cost['total_cost'])->toBe(1.05)
            ->and($cost['currency'])->toBe('USD');
    });

    it('calculates cost for Nova models (per 1K tokens)', function () {
        $cost = $this->service->calculateCost('nova-2-lite', 1000, 500);

        expect($cost)->toHaveKeys(['input_cost', 'output_cost', 'total_cost', 'currency'])
            ->and($cost['input_cost'])->toBe(0.00125) // (1000 / 1000) * 0.00125
            ->and($cost['output_cost'])->toBe(0.000625) // (500 / 1000) * 0.00125
            ->and($cost['total_cost'])->toBe(0.001875)
            ->and($cost['currency'])->toBe('USD');
    });

    it('returns zero cost for invalid model', function () {
        $cost = $this->service->calculateCost('invalid-model', 1000, 500);

        expect($cost['total_cost'])->toBe(0.0);
    });
});

describe('Health Monitoring', function () {
    it('reports healthy status when all checks pass', function () {
        $this->mcpClient->shouldReceive('isAgentCoreAvailable')
            ->once()
            ->andReturn(true);

        $status = $this->service->getHealthStatus();

        expect($status)->toHaveKeys([
            'healthy',
            'credentials_valid',
            'agentcore_available',
            'models_configured',
            'preferred_model',
            'region',
            'issues',
        ])
            ->and($status['healthy'])->toBeTrue()
            ->and($status['credentials_valid'])->toBeTrue()
            ->and($status['agentcore_available'])->toBeTrue()
            ->and($status['models_configured'])->toBe(3)
            ->and($status['issues'])->toBeEmpty();
    });

    it('reports unhealthy when credentials invalid', function () {
        Config::set('aws.credentials', [
            'key' => null,
            'secret' => null,
        ]);

        $this->mcpClient->shouldReceive('isAgentCoreAvailable')
            ->once()
            ->andReturn(true);

        $service = new BedrockConfigurationService($this->mcpClient);
        $status = $service->getHealthStatus();

        expect($status['healthy'])->toBeFalse()
            ->and($status['credentials_valid'])->toBeFalse()
            ->and($status['issues'])->not->toBeEmpty();
    });

    it('reports unhealthy when AgentCore unavailable', function () {
        $this->mcpClient->shouldReceive('isAgentCoreAvailable')
            ->once()
            ->andReturn(false);

        $status = $this->service->getHealthStatus();

        expect($status['healthy'])->toBeFalse()
            ->and($status['agentcore_available'])->toBeFalse()
            ->and($status['issues'])->toContain('AgentCore MCP server is not available');
    });

    it('reports unhealthy when no models configured', function () {
        Config::set('aws.bedrock.models', []);

        $this->mcpClient->shouldReceive('isAgentCoreAvailable')
            ->once()
            ->andReturn(true);

        $service = new BedrockConfigurationService($this->mcpClient);
        $status = $service->getHealthStatus();

        expect($status['healthy'])->toBeFalse()
            ->and($status['models_configured'])->toBe(0)
            ->and($status['issues'])->toContain('No Bedrock models configured');
    });
});

describe('Model Filtering', function () {
    it('returns only Claude models', function () {
        $claudeModels = $this->service->getClaudeModels();

        expect($claudeModels)->toHaveCount(2)
            ->and($claudeModels)->toHaveKeys(['claude-3-5-sonnet', 'claude-3-5-haiku']);
    });

    it('returns only Amazon models', function () {
        $amazonModels = $this->service->getAmazonModels();

        expect($amazonModels)->toHaveCount(1)
            ->and($amazonModels)->toHaveKey('nova-2-lite');
    });

    it('filters models by budget tier', function () {
        $budgetModels = $this->service->getModelsByTier('budget');

        expect($budgetModels)->toHaveCount(1)
            ->and($budgetModels)->toHaveKey('nova-2-lite');
    });

    it('filters models by standard tier', function () {
        $standardModels = $this->service->getModelsByTier('standard');

        expect($standardModels)->toHaveCount(2)
            ->and($standardModels)->toHaveKeys(['claude-3-5-sonnet', 'claude-3-5-haiku']);
    });

    it('filters models by premium tier', function () {
        Config::set('aws.bedrock.models.claude-opus-4-5', [
            'id' => 'anthropic.claude-opus-4-5-20250514-v1:0',
            'name' => 'Claude Opus 4.5',
            'provider' => 'anthropic',
            'input_cost' => 5.00,
            'output_cost' => 25.00,
        ]);

        $service = new BedrockConfigurationService($this->mcpClient);
        $premiumModels = $service->getModelsByTier('premium');

        expect($premiumModels)->toHaveCount(1)
            ->and($premiumModels)->toHaveKey('claude-opus-4-5');
    });
});

describe('Configuration Summary', function () {
    it('returns comprehensive configuration summary', function () {
        $this->mcpClient->shouldReceive('isAgentCoreAvailable')
            ->once()
            ->andReturn(true);

        $summary = $this->service->getConfigurationSummary();

        expect($summary)->toHaveKeys([
            'enabled',
            'credentials_valid',
            'region',
            'models',
            'preferred_model',
            'model_preferences',
            'health_status',
            'api_status',
            'required_permissions',
        ])
            ->and($summary['enabled'])->toBeTrue()
            ->and($summary['credentials_valid'])->toBeTrue()
            ->and($summary['region'])->toBe('us-east-1')
            ->and($summary['models'])->toHaveCount(3)
            ->and($summary['preferred_model'])->toBe('claude-3-5-sonnet')
            ->and($summary['required_permissions'])->toBeArray();
    });
});

describe('IAM Permissions', function () {
    it('returns required IAM permissions', function () {
        $permissions = $this->service->getRequiredPermissions();

        expect($permissions)->toBeArray()
            ->and($permissions)->toContain('bedrock:InvokeModel')
            ->and($permissions)->toContain('bedrock:ListFoundationModels');
    });
});
