<?php

declare(strict_types=1);

namespace App\Neuron\Agents;

use NeuronAI\Chat\History\ChatHistoryInterface;
use NeuronAI\Chat\History\EloquentChatHistory;
use NeuronAI\SystemPrompt;

/**
 * Demo Agent for testing MCP tool integration.
 *
 * This agent demonstrates how to integrate MCP tools with Neuron AI agents.
 * It shows automatic tool discovery, filtering with exclude() and only() methods,
 * and combining custom tools with MCP tools.
 *
 * **Validates: Requirements 17.5, 17.6**
 */
class McpDemoAgent extends BaseAgent
{
    /**
     * Create a new MCP Demo Agent instance.
     *
     * @param  int  $userId  The authenticated user ID for chat history
     */
    public function __construct(
        private int $userId
    ) {}

    /**
     * Get the agent's system instructions.
     */
    public function instructions(): string
    {
        return (string) new SystemPrompt(
            background: [
                'You are a demo agent showcasing MCP tool integration.',
                'You have access to tools from MCP servers.',
                'You can use these tools to perform various tasks.',
            ],
            steps: [
                'Understand the user\'s request',
                'Identify which tools are available',
                'Use the appropriate tools to complete the task',
                'Provide a clear response',
            ],
            output: [
                'Provide clear and concise responses',
                'Explain which tools you used',
                'Show the results of tool execution',
            ]
        );
    }

    /**
     * Get the chat history instance for this agent.
     */
    protected function chatHistory(): ChatHistoryInterface
    {
        return new EloquentChatHistory(
            threadId: $this->getThreadId(),
            modelClass: \App\Models\ChatMessage::class
        );
    }

    /**
     * Get the thread identifier for chat history.
     */
    protected function getThreadId(): string
    {
        return "mcp_demo_user_{$this->userId}";
    }

    /**
     * Configure MCP servers for this agent.
     *
     * This example shows how to:
     * - Connect to multiple MCP servers
     * - Filter tools using 'only' to include specific tools
     * - Filter tools using 'exclude' to remove unwanted tools
     *
     * @return array<string, array{exclude?: array<string>, only?: array<string>}>
     */
    protected function mcpServers(): array
    {
        return [
            // Memory server - only include specific tools
            'memory' => [
                'only' => ['create_entities', 'search_nodes', 'read_graph'],
            ],

            // Filesystem server - exclude dangerous operations
            'filesystem' => [
                'exclude' => ['delete_file', 'write_file'],
            ],

            // Fetch server - include all tools (no filtering)
            'fetch' => [],
        ];
    }
}
