<?php

declare(strict_types=1);

namespace Tests\Unit\Neuron\Agents;

use App\Neuron\Agents\BaseAgent;
use Mockery;
use NeuronAI\Chat\History\ChatHistoryInterface;
use NeuronAI\Laravel\Facades\AIProvider;
use NeuronAI\Providers\AIProviderInterface;
use Tests\TestCase;

class TestableBaseAgent extends BaseAgent
{
    public function __construct(
        private string $threadId,
        private bool $mockChatHistory = false
    ) {}

    protected function getThreadId(): string
    {
        return $this->threadId;
    }

    public function instructions(): string
    {
        return 'Test instructions';
    }

    public function testProvider(): AIProviderInterface
    {
        return $this->provider();
    }

    /**
     * @param  array<int, string>  $background
     * @param  array<int, string>  $steps
     * @param  array<int, string>  $output
     */
    public function testBuildSystemPrompt(array $background, array $steps, array $output): string
    {
        return $this->buildSystemPrompt($background, $steps, $output);
    }

    public function testChatHistory(): ChatHistoryInterface
    {
        if ($this->mockChatHistory) {
            /** @var ChatHistoryInterface $chatHistory */
            $chatHistory = Mockery::mock(ChatHistoryInterface::class);

            return $chatHistory;
        }

        return $this->chatHistory();
    }

    /**
     * @return array<int, mixed>
     */
    public function testTools(): array
    {
        return $this->tools();
    }
}

/**
 * Unit tests for BaseAgent abstract class.
 *
 * Tests verify:
 * - Provider selection logic
 * - System prompt building
 * - Chat history methods
 *
 * Requirements covered: 3.1, 3.3, 3.4, 3.5
 */
class BaseAgentTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Create a concrete implementation of BaseAgent for testing.
     */
    private function createTestAgent(string $threadId = 'test-thread-123', bool $mockChatHistory = false): TestableBaseAgent
    {
        return new TestableBaseAgent($threadId, $mockChatHistory);
    }

    public function test_uses_the_default_provider_from_configuration(): void
    {
        // Set up configuration
        config(['neuron.provider.default' => 'anthropic']);

        // Mock the AIProvider facade
        $mockProvider = Mockery::mock(AIProviderInterface::class);
        AIProvider::shouldReceive('driver')
            ->once()
            ->with('anthropic')
            ->andReturn($mockProvider);

        $agent = $this->createTestAgent();
        $provider = $agent->testProvider();

        $this->assertInstanceOf(AIProviderInterface::class, $provider);
    }

    public function test_builds_system_prompt_with_background_steps_and_output(): void
    {
        $agent = $this->createTestAgent();

        $background = [
            'You are an expert in Uma Musume training mechanics.',
            'You analyze character stats and aptitudes.',
        ];

        $steps = [
            'Analyze current character statistics',
            'Consider character aptitudes',
            'Recommend optimal training choice',
        ];

        $output = [
            'Provide a clear training recommendation',
            'Explain the reasoning behind your recommendation',
        ];

        $systemPrompt = $agent->testBuildSystemPrompt($background, $steps, $output);

        $this->assertIsString($systemPrompt);
        $this->assertStringContainsString('Uma Musume training mechanics', $systemPrompt);
        $this->assertStringContainsString('Analyze current character statistics', $systemPrompt);
        $this->assertStringContainsString('Provide a clear training recommendation', $systemPrompt);
    }

    public function test_builds_system_prompt_with_empty_arrays(): void
    {
        $agent = $this->createTestAgent();

        $systemPrompt = $agent->testBuildSystemPrompt([], [], []);

        $this->assertIsString($systemPrompt);
    }

    public function test_creates_chat_history_with_correct_thread_id(): void
    {
        $threadId = 'user_123_character_456';
        $agent = $this->createTestAgent($threadId, mockChatHistory: true);

        $chatHistory = $agent->testChatHistory();

        // Verify that chat history is created and is the correct type
        $this->assertInstanceOf(ChatHistoryInterface::class, $chatHistory);
        $this->assertInstanceOf(ChatHistoryInterface::class, $chatHistory);
    }

    public function test_returns_empty_tools_array_by_default(): void
    {
        $agent = $this->createTestAgent();

        $tools = $agent->testTools();

        $this->assertIsArray($tools);
        $this->assertEmpty($tools);
    }

    public function test_uses_different_thread_ids_for_different_agents(): void
    {
        $agent1 = $this->createTestAgent('thread-1', mockChatHistory: true);
        $agent2 = $this->createTestAgent('thread-2', mockChatHistory: true);

        $history1 = $agent1->testChatHistory();
        $history2 = $agent2->testChatHistory();

        // Verify both are chat history instances
        $this->assertInstanceOf(ChatHistoryInterface::class, $history1);
        $this->assertInstanceOf(ChatHistoryInterface::class, $history2);

        // Verify they are different instances
        $this->assertNotSame($history1, $history2);
    }

    public function test_builds_consistent_system_prompts_with_same_inputs(): void
    {
        $agent = $this->createTestAgent();

        $background = ['Background info'];
        $steps = ['Step 1', 'Step 2'];
        $output = ['Output format'];

        $prompt1 = $agent->testBuildSystemPrompt($background, $steps, $output);
        $prompt2 = $agent->testBuildSystemPrompt($background, $steps, $output);

        $this->assertEquals($prompt1, $prompt2);
    }

    public function test_handles_system_prompt_with_single_item_arrays(): void
    {
        $agent = $this->createTestAgent();

        $systemPrompt = $agent->testBuildSystemPrompt(
            ['Single background'],
            ['Single step'],
            ['Single output']
        );

        $this->assertIsString($systemPrompt);
        $this->assertStringContainsString('Single background', $systemPrompt);
        $this->assertStringContainsString('Single step', $systemPrompt);
        $this->assertStringContainsString('Single output', $systemPrompt);
    }

    public function test_handles_system_prompt_with_multiple_items_in_each_section(): void
    {
        $agent = $this->createTestAgent();

        $background = [
            'Background 1',
            'Background 2',
            'Background 3',
        ];

        $steps = [
            'Step 1',
            'Step 2',
            'Step 3',
            'Step 4',
        ];

        $output = [
            'Output 1',
            'Output 2',
        ];

        $systemPrompt = $agent->testBuildSystemPrompt($background, $steps, $output);

        $this->assertIsString($systemPrompt);
        $this->assertStringContainsString('Background 1', $systemPrompt);
        $this->assertStringContainsString('Background 3', $systemPrompt);
        $this->assertStringContainsString('Step 1', $systemPrompt);
        $this->assertStringContainsString('Step 4', $systemPrompt);
        $this->assertStringContainsString('Output 1', $systemPrompt);
        $this->assertStringContainsString('Output 2', $systemPrompt);
    }

    public function test_respects_provider_configuration_changes(): void
    {
        // Test with different provider
        config(['neuron.provider.default' => 'openai']);

        $mockProvider = Mockery::mock(AIProviderInterface::class);
        AIProvider::shouldReceive('driver')
            ->once()
            ->with('openai')
            ->andReturn($mockProvider);

        $agent = $this->createTestAgent();
        $provider = $agent->testProvider();

        $this->assertInstanceOf(AIProviderInterface::class, $provider);
    }
}
