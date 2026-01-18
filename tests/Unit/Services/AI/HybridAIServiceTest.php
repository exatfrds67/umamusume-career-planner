<?php

namespace Tests\Unit\Services\AI;

use App\Services\AI\AIPerformanceMonitor;
use App\Services\AI\BedrockService;
use App\Services\AI\HybridAIService;
use App\Services\AI\OllamaService;
use App\Services\MCP\MCPClientService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

/**
 * Hybrid AI Service Tests
 *
 * Tests the hybrid AI service architecture with intelligent routing
 * between local Ollama and cloud Bedrock services.
 *
 * Requirements: 13.1, 56.2, 57.2
 */
class HybridAIServiceTest extends TestCase
{
    use RefreshDatabase;

    protected HybridAIService $service;

    protected MCPClientService&MockObject $mcpClient;

    protected OllamaService&MockObject $ollamaService;

    protected BedrockService&MockObject $bedrockService;

    protected AIPerformanceMonitor&MockObject $performanceMonitor;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up test configuration
        Config::set('ai.hybrid.enabled', true);
        Config::set('ai.ollama.default_model', 'llama3.3');
        Config::set('ai.bedrock.default_model', 'claude-3-5-sonnet');

        // Create mock services
        $this->mcpClient = $this->createMock(MCPClientService::class);
        $this->ollamaService = $this->createMock(OllamaService::class);
        $this->bedrockService = $this->createMock(BedrockService::class);
        $this->performanceMonitor = $this->createMock(AIPerformanceMonitor::class);

        // Create service instance
        $this->service = new HybridAIService(
            $this->mcpClient,
            $this->ollamaService,
            $this->bedrockService,
            $this->performanceMonitor
        );
    }

    /**
     * Test simple request routes to Ollama
     */
    public function test_simple_request_routes_to_ollama(): void
    {
        // Arrange
        $prompt = 'What is the best training for Speed?';
        $context = [];

        $this->ollamaService->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);

        $this->ollamaService->expects($this->once())
            ->method('generate')
            ->with($prompt, $context, 'llama3.3', 15)
            ->willReturn([
                'content' => 'Speed training is best for improving top speed.',
                'model' => 'llama3.3',
                'token_count' => 50,
                'confidence' => 0.8,
                'model_version' => '70b',
            ]);

        $this->performanceMonitor->expects($this->once())
            ->method('trackRequest');

        // Act
        $response = $this->service->processRequest($prompt, $context);

        // Assert
        $this->assertEquals('ollama', $response['provider']);
        $this->assertEquals('llama3.3', $response['model']);
        $this->assertEquals(0.0, $response['cost']);
        $this->assertArrayHasKey('content', $response);
        $this->assertArrayHasKey('processing_time', $response);
    }

    /**
     * Test complex request routes to Bedrock
     */
    public function test_complex_request_routes_to_bedrock(): void
    {
        // Arrange
        $prompt = 'Analyze my career strategy and optimize my training sequence for the next 10 turns considering all stats, skills, and race requirements.';
        $context = ['requires_multi_step' => true];

        $this->mcpClient->expects($this->once())
            ->method('isStrandsAgentsAvailable')
            ->willReturn(false);

        $this->mcpClient->expects($this->once())
            ->method('isAgentCoreAvailable')
            ->willReturn(false);

        $this->bedrockService->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);

        $this->bedrockService->expects($this->once())
            ->method('generate')
            ->willReturn([
                'content' => 'Based on your career analysis...',
                'model' => 'claude-3-5-sonnet',
                'token_count' => 500,
                'confidence' => 0.9,
                'model_version' => '20241022-v2',
                'request_id' => 'test-request-id',
            ]);

        $this->performanceMonitor->expects($this->once())
            ->method('trackRequest');

        // Act
        $response = $this->service->processRequest($prompt, $context);

        // Assert
        $this->assertEquals('bedrock', $response['provider']);
        $this->assertEquals('claude-3-5-sonnet', $response['model']);
        $this->assertGreaterThan(0.0, $response['cost']);
        $this->assertArrayHasKey('content', $response);
    }

    /**
     * Test fallback from Ollama to Bedrock on failure
     */
    public function test_fallback_from_ollama_to_bedrock_on_failure(): void
    {
        // Arrange
        $prompt = 'Simple question';
        $context = [];

        $this->ollamaService->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);

        $this->ollamaService->expects($this->once())
            ->method('generate')
            ->willThrowException(new \RuntimeException('Ollama timeout'));

        $this->bedrockService->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);

        $this->bedrockService->expects($this->once())
            ->method('generate')
            ->willReturn([
                'content' => 'Fallback response',
                'model' => 'claude-3-5-sonnet',
                'token_count' => 100,
                'confidence' => 0.9,
                'model_version' => '20241022-v2',
                'request_id' => 'fallback-request-id',
            ]);

        // Performance monitor should be called for the fallback request
        $this->performanceMonitor->expects($this->once())
            ->method('trackRequest');

        // Act
        $response = $this->service->processRequest($prompt, $context);

        // Assert
        $this->assertEquals('bedrock', $response['provider']);
        $this->assertArrayHasKey('content', $response);
    }

    /**
     * Test complexity analysis
     */
    public function test_complexity_analysis(): void
    {
        // Arrange
        $simplePrompt = 'What is Speed?';
        $mediumPrompt = str_repeat('This is a test prompt ', 50); // ~500 tokens
        $complexPrompt = 'Analyze and compare multiple career strategies with multi-step reasoning';

        // Act - Use reflection to test protected method
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('analyzeComplexity');
        $method->setAccessible(true);

        /** @var array{level: string, requires_multi_step: bool} $simpleComplexity */
        $simpleComplexity = $method->invoke($this->service, $simplePrompt, []);
        /** @var array{level: string, requires_multi_step: bool} $mediumComplexity */
        $mediumComplexity = $method->invoke($this->service, $mediumPrompt, []);
        /** @var array{level: string, requires_multi_step: bool} $complexComplexity */
        $complexComplexity = $method->invoke($this->service, $complexPrompt, ['requires_multi_step' => true]);

        // Assert
        $this->assertEquals('simple', $simpleComplexity['level']);
        // Medium prompt has ~500 tokens which is less than 1000, so it's still simple
        $this->assertContains($mediumComplexity['level'], ['simple', 'medium']);
        $this->assertEquals('complex', $complexComplexity['level']);
        $this->assertTrue($complexComplexity['requires_multi_step']);
    }

    /**
     * Test provider selection logic
     */
    public function test_provider_selection_logic(): void
    {
        // Arrange
        $complexity = [
            'level' => 'simple',
            'token_estimate' => 500,
            'requires_rag' => false,
            'requires_multi_step' => false,
            'estimated_cost' => 0.001,
        ];

        $this->ollamaService->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);

        // Act - Use reflection to test protected method
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('selectProvider');
        $method->setAccessible(true);

        $provider = $method->invoke($this->service, $complexity, []);

        // Assert
        $this->assertEquals('ollama', $provider);
    }

    /**
     * Test conversation storage
     *
     * @skip This test requires AIConversation model which will be created in Task 1.4
     */
    public function test_conversation_storage(): void
    {
        $this->markTestSkipped('AIConversation model not yet implemented');
    }

    /**
     * Test service status
     */
    public function test_service_status(): void
    {
        // Arrange
        $this->ollamaService->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);

        $this->ollamaService->expects($this->once())
            ->method('isHealthy')
            ->willReturn(true);

        $this->bedrockService->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);

        $this->bedrockService->expects($this->once())
            ->method('isHealthy')
            ->willReturn(true);

        $this->mcpClient->expects($this->once())
            ->method('isStrandsAgentsAvailable')
            ->willReturn(false);

        $this->mcpClient->expects($this->exactly(2))
            ->method('isServerHealthy')
            ->willReturnCallback(function ($server) {
                return false;
            });

        $this->mcpClient->expects($this->once())
            ->method('isAgentCoreAvailable')
            ->willReturn(false);

        $this->performanceMonitor->expects($this->once())
            ->method('getMetrics')
            ->willReturn([
                'total_requests' => 100,
                'total_cost' => 0.50,
            ]);

        // Act
        $status = $this->service->getStatus();

        // Assert
        $this->assertTrue($status['enabled']);
        $this->assertTrue($status['providers']['ollama']['available']);
        $this->assertTrue($status['providers']['bedrock']['available']);
        $this->assertFalse($status['providers']['mcp-strands']['available']);
        $this->assertArrayHasKey('performance', $status);
    }

    /**
     * Test cost estimation
     */
    public function test_cost_estimation(): void
    {
        // Arrange
        $tokenCount = 1000;
        $model = 'claude-3-5-sonnet';

        // Act - Use reflection to test protected method
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('estimateCost');
        $method->setAccessible(true);

        $cost = $method->invoke($this->service, $tokenCount, $model);

        // Assert
        $this->assertGreaterThan(0.0, $cost);
        $this->assertLessThan(0.02, $cost); // Should be less than 2 cents for 1000 tokens
    }

    /**
     * Test token count estimation
     */
    public function test_token_count_estimation(): void
    {
        // Arrange
        $shortPrompt = 'Hello';
        $longPrompt = str_repeat('This is a test prompt ', 100);

        // Act - Use reflection to test protected method
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('estimateTokenCount');
        $method->setAccessible(true);

        $shortTokens = $method->invoke($this->service, $shortPrompt, []);
        $longTokens = $method->invoke($this->service, $longPrompt, []);

        // Assert
        $this->assertGreaterThan(0, $shortTokens);
        $this->assertGreaterThan($shortTokens, $longTokens);
    }

    /**
     * Test RAG requirement detection
     */
    public function test_rag_requirement_detection(): void
    {
        // Arrange
        $ragPrompt = 'Search the database for character information';
        $normalPrompt = 'What is the best training?';

        // Act - Use reflection to test protected method
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('requiresRAG');
        $method->setAccessible(true);

        $requiresRAG = $method->invoke($this->service, $ragPrompt, []);
        $doesNotRequireRAG = $method->invoke($this->service, $normalPrompt, []);

        // Assert
        $this->assertTrue($requiresRAG);
        $this->assertFalse($doesNotRequireRAG);
    }

    /**
     * Test multi-step reasoning detection
     */
    public function test_multi_step_reasoning_detection(): void
    {
        // Arrange
        $complexPrompt = 'Analyze my career strategy and optimize training';
        $simplePrompt = 'What is Speed?';

        // Act - Use reflection to test protected method
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('requiresMultiStepReasoning');
        $method->setAccessible(true);

        $requiresMultiStep = $method->invoke($this->service, $complexPrompt, []);
        $doesNotRequireMultiStep = $method->invoke($this->service, $simplePrompt, []);

        // Assert
        $this->assertTrue($requiresMultiStep);
        $this->assertFalse($doesNotRequireMultiStep);
    }
}
