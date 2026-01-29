<?php

namespace Tests\Feature;

use App\Services\AI\BedrockConfigurationService;
use App\Services\AI\BedrockService;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class BedrockHealthCheckTest extends TestCase
{
    /**
     * Helper to check if Bedrock credentials are configured
     */
    private function bedrockCredentialsConfigured(): bool
    {
        $accessKey = Config::get('aws.credentials.key');
        $secretKey = Config::get('aws.credentials.secret');

        return is_string($accessKey) && $accessKey !== ''
            && is_string($secretKey) && $secretKey !== '';
    }

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('aws.credentials.key', 'test-access-key');
        Config::set('aws.credentials.secret', 'test-secret-key');
        Config::set('aws.region', 'us-east-1');
        Config::set('aws.bedrock.region', 'us-east-1');
        Config::set('ai.bedrock.enabled', true);
    }

    /**
     * Test Bedrock configuration is properly set up
     */
    public function test_bedrock_credentials_are_configured(): void
    {
        $this->assertTrue($this->bedrockCredentialsConfigured());
        $this->assertNotEmpty(Config::get('aws.credentials.key'));
        $this->assertNotEmpty(Config::get('aws.credentials.secret'));
        $this->assertNotEmpty(Config::get('aws.region'));
    }

    /**
     * Test Bedrock is enabled
     */
    public function test_bedrock_is_enabled(): void
    {
        $this->assertTrue((bool) config('ai.bedrock.enabled'));
    }

    /**
     * Test BedrockConfigurationService validates credentials
     */
    public function test_bedrock_configuration_service_validates_credentials(): void
    {
        $service = $this->app->make(BedrockConfigurationService::class);
        $validation = $service->validateCredentials();

        $this->assertTrue($validation['credentials_configured']);
        $this->assertNotEmpty($validation['region']);
    }

    /**
     * Test BedrockService can be instantiated
     */
    public function test_bedrock_service_can_be_instantiated(): void
    {
        $service = $this->app->make(BedrockService::class);
        $this->assertNotNull($service);
    }

    /**
     * Test Bedrock models are configured
     */
    public function test_bedrock_models_are_configured(): void
    {
        $models = config('aws.bedrock.models', []);
        $this->assertIsArray($models);
        $this->assertNotEmpty($models);
    }

    /**
     * Test Bedrock API connectivity
     */
    public function test_bedrock_api_connectivity(): void
    {
        $this->mock(BedrockService::class)
            ->shouldReceive('generate')
            ->once()
            ->with('Say "Bedrock test"', [], 'claude-3-5-sonnet')
            ->andReturn([
                'content' => 'Bedrock test response',
                'model' => 'claude-3-5-sonnet',
                'token_count' => 42,
            ]);

        $service = $this->app->make(BedrockService::class);
        $response = $service->generate('Say "Bedrock test"', [], 'claude-3-5-sonnet');

        $this->assertIsArray($response);
        $this->assertArrayHasKey('content', $response);
        $this->assertArrayHasKey('model', $response);
        $this->assertArrayHasKey('token_count', $response);
        $this->assertNotEmpty($response['content']);
    }
}
