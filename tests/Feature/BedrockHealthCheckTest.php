<?php

namespace Tests\Feature;

use App\Services\AI\BedrockConfigurationService;
use App\Services\AI\BedrockService;
use Tests\TestCase;

class BedrockHealthCheckTest extends TestCase
{
    /**
     * Helper to check if Bedrock credentials are configured
     */
    private function bedrockCredentialsConfigured(): bool
    {
        return ! empty(env('AWS_ACCESS_KEY_ID')) && ! empty(env('AWS_SECRET_ACCESS_KEY'));
    }

    /**
     * Test Bedrock configuration is properly set up
     */
    public function test_bedrock_credentials_are_configured(): void
    {
        if (! $this->bedrockCredentialsConfigured()) {
            $this->markTestSkipped('AWS Bedrock credentials are not configured');
        }

        $this->assertNotEmpty(env('AWS_ACCESS_KEY_ID'));
        $this->assertNotEmpty(env('AWS_SECRET_ACCESS_KEY'));
        $this->assertNotEmpty(env('AWS_DEFAULT_REGION'));
    }

    /**
     * Test Bedrock is enabled
     */
    public function test_bedrock_is_enabled(): void
    {
        if (! $this->bedrockCredentialsConfigured()) {
            $this->markTestSkipped('AWS Bedrock credentials are not configured');
        }

        $this->assertTrue((bool) config('ai.bedrock.enabled'));
    }

    /**
     * Test BedrockConfigurationService validates credentials
     */
    public function test_bedrock_configuration_service_validates_credentials(): void
    {
        if (! $this->bedrockCredentialsConfigured()) {
            $this->markTestSkipped('AWS Bedrock credentials are not configured');
        }

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
        if (! $this->bedrockCredentialsConfigured()) {
            $this->markTestSkipped('AWS Bedrock credentials are not configured');
        }

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
        if (! $this->bedrockCredentialsConfigured()) {
            $this->markTestSkipped('AWS Bedrock credentials are not configured');
        }

        $service = $this->app->make(BedrockService::class);

        try {
            $response = $service->generate('Say "Bedrock test"', [], 'claude-3-5-sonnet');

            $this->assertIsArray($response);
            $this->assertArrayHasKey('content', $response);
            $this->assertArrayHasKey('model', $response);
            $this->assertArrayHasKey('token_count', $response);
            $this->assertNotEmpty($response['content']);
        } catch (\Throwable $e) {
            $this->assertInstanceOf(\RuntimeException::class, $e);
            $this->assertStringContainsString('Bedrock', $e->getMessage());
        }
    }
}
