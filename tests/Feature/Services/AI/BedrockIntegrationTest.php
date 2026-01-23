<?php

use App\Services\AI\BedrockConfigurationService;
use App\Services\MCP\MCPClientService;
use Illuminate\Support\Facades\Config;

/**
 * Bedrock Integration Tests
 *
 * Tests AWS Bedrock integration with MCP AgentCore server,
 * credential management, and end-to-end functionality.
 *
 * Requirements: 56.1, 56.2, 59.1
 */
describe('Bedrock Integration', function () {
    beforeEach(function () {
        // Set up test AWS configuration
        Config::set('aws.credentials', [
            'key' => 'test-key',
            'secret' => 'test-secret',
        ]);

        Config::set('aws.region', 'us-east-1');
        Config::set('aws.bedrock.region', 'us-east-1');

        // Configure test models
        Config::set('aws.bedrock.models', [
            'claude-3-5-sonnet' => [
                'id' => 'anthropic.claude-3-5-sonnet-20241022-v2:0',
                'name' => 'Claude 3.5 Sonnet',
                'provider' => 'anthropic',
                'input_cost' => 3.00,
                'output_cost' => 15.00,
                'max_tokens' => 200000,
                'context_window' => 200000,
            ],
            'claude-3-5-haiku' => [
                'id' => 'anthropic.claude-3-5-haiku-20241022-v1:0',
                'name' => 'Claude 3.5 Haiku',
                'provider' => 'anthropic',
                'input_cost' => 1.00,
                'output_cost' => 5.00,
                'max_tokens' => 200000,
                'context_window' => 200000,
            ],
            'nova-2-lite' => [
                'id' => 'amazon.nova-lite-v1:0',
                'name' => 'Amazon Nova 2 Lite',
                'provider' => 'amazon',
                'input_cost' => 0.00125,
                'output_cost' => 0.00125,
                'max_tokens' => 300000,
                'context_window' => 300000,
            ],
        ]);

        Config::set('aws.bedrock.model_preferences', [
            'claude-3-5-sonnet',
            'claude-3-5-haiku',
            'nova-2-lite',
        ]);

        Config::set('ai.bedrock.enabled', true);
        Config::set('ai.bedrock.default_model', 'claude-3-5-sonnet');
    });

    describe('Configuration Loading', function () {
        it('loads AWS configuration from config file', function () {
            $credentials = Config::get('aws.credentials');

            expect($credentials)->toBeArray()
                ->and($credentials)->toHaveKeys(['key', 'secret']);
        });

        it('loads Bedrock model configuration', function () {
            $models = Config::get('aws.bedrock.models');

            expect($models)->toBeArray()
                ->and($models)->toHaveKeys(['claude-3-5-sonnet', 'claude-3-5-haiku', 'nova-2-lite'])
                ->and(count($models))->toBeGreaterThanOrEqual(3);
        });

        it('loads model preferences in correct order', function () {
            $preferences = Config::get('aws.bedrock.model_preferences');

            expect($preferences)->toBeArray()
                ->and($preferences[0])->toBe('claude-3-5-sonnet');
        });
    });

    describe('Bedrock Configuration Service', function () {
        it('initializes configuration service successfully', function () {
            $mcpClient = app(MCPClientService::class);
            $service = new BedrockConfigurationService($mcpClient);

            expect($service)->toBeInstanceOf(BedrockConfigurationService::class);
        });

        it('validates credentials configuration', function () {
            $mcpClient = app(MCPClientService::class);
            $service = new BedrockConfigurationService($mcpClient);

            $validation = $service->validateCredentials();

            expect($validation)->toHaveKeys(['valid', 'message', 'credentials_configured', 'region'])
                ->and($validation['region'])->toBe('us-east-1');
        });

        it('provides comprehensive health status', function () {
            $mcpClient = app(MCPClientService::class);
            $service = new BedrockConfigurationService($mcpClient);

            $health = $service->getHealthStatus();

            expect($health)->toHaveKeys([
                'healthy',
                'credentials_valid',
                'agentcore_available',
                'models_configured',
                'preferred_model',
                'region',
                'issues',
            ])
                ->and($health['models_configured'])->toBeGreaterThan(0)
                ->and($health['preferred_model'])->toBe('claude-3-5-sonnet');
        });

        it('calculates costs accurately for different models', function () {
            $mcpClient = app(MCPClientService::class);
            $service = new BedrockConfigurationService($mcpClient);

            // Test Claude model (per 1M tokens)
            $claudeCost = $service->calculateCost('claude-3-5-sonnet', 100000, 50000);
            expect($claudeCost['total_cost'])->toBeGreaterThan(0);

            // Test Nova model (per 1K tokens)
            $novaCost = $service->calculateCost('nova-2-lite', 1000, 500);
            expect($novaCost['total_cost'])->toBeGreaterThan(0)
                ->and($novaCost['total_cost'])->toBeLessThan($claudeCost['total_cost']);
        });

        it('filters models by provider correctly', function () {
            $mcpClient = app(MCPClientService::class);
            $service = new BedrockConfigurationService($mcpClient);

            $claudeModels = $service->getClaudeModels();
            $amazonModels = $service->getAmazonModels();

            expect($claudeModels)->toHaveCount(2)
                ->and($amazonModels)->toHaveCount(1);
        });

        it('filters models by cost tier correctly', function () {
            $mcpClient = app(MCPClientService::class);
            $service = new BedrockConfigurationService($mcpClient);

            $budgetModels = $service->getModelsByTier('budget');
            $standardModels = $service->getModelsByTier('standard');

            expect($budgetModels)->toHaveCount(1)
                ->and($standardModels)->toHaveCount(2);
        });
    });

    describe('MCP Integration', function () {
        it('checks AgentCore MCP server availability', function () {
            $mcpClient = app(MCPClientService::class);

            $available = $mcpClient->isAgentCoreAvailable();

            expect($available)->toBeIn([true, false]); // May be true or false depending on server status
        });

        it('includes AgentCore status in health check', function () {
            $mcpClient = app(MCPClientService::class);
            $service = new BedrockConfigurationService($mcpClient);

            $health = $service->getHealthStatus();

            expect($health)->toHaveKey('agentcore_available')
                ->and($health['agentcore_available'])->toBeIn([true, false]);
        });

        it('reports issues when AgentCore unavailable', function () {
            /** @var MCPClientService&Mockery\MockInterface $mcpClient */
            $mcpClient = Mockery::mock(MCPClientService::class);
            $mcpClient->shouldReceive('isAgentCoreAvailable')->andReturn(false);

            $service = new BedrockConfigurationService($mcpClient);
            $health = $service->getHealthStatus();

            expect($health['agentcore_available'])->toBeFalse()
                ->and($health['issues'])->toContain('AgentCore MCP server is not available');
        });
    });

    describe('Model Configuration', function () {
        it('provides all configured models', function () {
            $mcpClient = app(MCPClientService::class);
            $service = new BedrockConfigurationService($mcpClient);

            $models = $service->getAvailableModels();

            expect($models)->toBeArray()
                ->and(count($models))->toBeGreaterThanOrEqual(3);

            foreach ($models as $model) {
                expect($model)->toHaveKeys(['id', 'name', 'provider', 'input_cost', 'output_cost']);
            }
        });

        it('returns correct model IDs for Bedrock API', function () {
            $mcpClient = app(MCPClientService::class);
            $service = new BedrockConfigurationService($mcpClient);

            $sonnetId = $service->getModelId('claude-3-5-sonnet');
            $haikuId = $service->getModelId('claude-3-5-haiku');
            $novaId = $service->getModelId('nova-2-lite');

            expect($sonnetId)->toContain('anthropic.claude-3-5-sonnet')
                ->and($haikuId)->toContain('anthropic.claude-3-5-haiku')
                ->and($novaId)->toContain('amazon.nova-lite');
        });

        it('validates model names correctly', function () {
            $mcpClient = app(MCPClientService::class);
            $service = new BedrockConfigurationService($mcpClient);

            expect($service->isValidModel('claude-3-5-sonnet'))->toBeTrue()
                ->and($service->isValidModel('claude-3-5-haiku'))->toBeTrue()
                ->and($service->isValidModel('nova-2-lite'))->toBeTrue()
                ->and($service->isValidModel('invalid-model'))->toBeFalse();
        });
    });

    describe('Configuration Summary', function () {
        it('provides comprehensive configuration summary', function () {
            $mcpClient = app(MCPClientService::class);
            $service = new BedrockConfigurationService($mcpClient);

            $summary = $service->getConfigurationSummary();

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
                ->and($summary['region'])->toBe('us-east-1')
                ->and($summary['models'])->toBeArray()
                ->and($summary['preferred_model'])->toBe('claude-3-5-sonnet')
                ->and($summary['model_preferences'])->toBeArray()
                ->and($summary['health_status'])->toBeArray()
                ->and($summary['required_permissions'])->toBeArray();
        });

        it('includes required IAM permissions in summary', function () {
            $mcpClient = app(MCPClientService::class);
            $service = new BedrockConfigurationService($mcpClient);

            $summary = $service->getConfigurationSummary();
            $permissions = $summary['required_permissions'];

            expect($permissions)->toContain('bedrock:InvokeModel')
                ->and($permissions)->toContain('bedrock:InvokeModelWithResponseStream')
                ->and($permissions)->toContain('bedrock:ListFoundationModels')
                ->and($permissions)->toContain('bedrock:GetFoundationModel');
        });
    });

    describe('Error Handling', function () {
        it('handles missing credentials gracefully', function () {
            Config::set('aws.credentials', [
                'key' => null,
                'secret' => null,
            ]);

            $mcpClient = app(MCPClientService::class);
            $service = new BedrockConfigurationService($mcpClient);

            $validation = $service->validateCredentials();

            expect($validation['valid'])->toBeFalse()
                ->and($validation['credentials_configured'])->toBeFalse()
                ->and($validation['message'])->toContain('not configured');
        });

        it('handles placeholder credentials gracefully', function () {
            Config::set('aws.credentials', [
                'key' => 'your-access-key-id',
                'secret' => 'your-secret-access-key',
            ]);

            $mcpClient = app(MCPClientService::class);
            $service = new BedrockConfigurationService($mcpClient);

            $validation = $service->validateCredentials();

            expect($validation['valid'])->toBeFalse()
                ->and($validation['message'])->toContain('placeholder values');
        });

        it('handles missing model configuration gracefully', function () {
            Config::set('aws.bedrock.models', []);

            $mcpClient = app(MCPClientService::class);
            $service = new BedrockConfigurationService($mcpClient);

            $models = $service->getAvailableModels();

            expect($models)->toBeArray()
                ->and($models)->toBeEmpty();
        });

        it('returns zero cost for invalid model', function () {
            $mcpClient = app(MCPClientService::class);
            $service = new BedrockConfigurationService($mcpClient);

            $cost = $service->calculateCost('invalid-model', 1000, 500);

            expect($cost['total_cost'])->toBe(0.0);
        });
    });
});
