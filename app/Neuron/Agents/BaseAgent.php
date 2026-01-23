<?php

declare(strict_types=1);

namespace App\Neuron\Agents;

use NeuronAI\Agent;
use NeuronAI\Chat\History\ChatHistoryInterface;
use NeuronAI\Chat\History\EloquentChatHistory;
use NeuronAI\Laravel\Facades\AIProvider;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\SystemPrompt;

/**
 * Base Agent class for Uma Musume Career Planner AI agents.
 *
 * Provides common functionality for all specialized agents including:
 * - Provider selection using Laravel facade
 * - System prompt building utilities
 * - Chat history management with EloquentChatHistory
 * - Tool registration interface
 */
abstract class BaseAgent extends Agent
{
    /**
     * Get the AI provider instance using Laravel facade.
     *
     * Uses the default provider configured in config/neuron.php
     * unless overridden by child classes.
     */
    protected function provider(): AIProviderInterface
    {
        return AIProvider::driver(config('neuron.provider.default'));
    }

    /**
     * Build a system prompt from structured components.
     *
     * Helper method to construct system prompts using Neuron's SystemPrompt class.
     * Provides a consistent format across all agents.
     *
     * @param  array<int, string>  $background  Background context for the agent
     * @param  array<int, string>  $steps  Step-by-step instructions for the agent
     * @param  array<int, string>  $output  Output format and requirements
     */
    protected function buildSystemPrompt(array $background, array $steps, array $output): string
    {
        return (string) new SystemPrompt(
            background: $background,
            steps: $steps,
            output: $output
        );
    }

    /**
     * Get the chat history instance for this agent.
     *
     * Uses EloquentChatHistory to persist conversation context
     * across multiple interactions. Associates history with the
     * authenticated user and agent-specific session.
     */
    protected function chatHistory(): ChatHistoryInterface
    {
        return new EloquentChatHistory(
            threadId: $this->getThreadId(),
            modelClass: \App\Models\ChatMessage::class
        );
    }

    /**
     * Get the chat history instance for external consumers.
     */
    public function getChatHistory(): ChatHistoryInterface
    {
        return $this->chatHistory();
    }

    /**
     * Get the thread identifier for chat history.
     *
     * Child classes must implement this to provide appropriate
     * thread scoping. The thread ID should uniquely identify a
     * conversation context (e.g., "user_{id}_character_{id}").
     */
    abstract protected function getThreadId(): string;

    /**
     * Register tools available to this agent.
     *
     * Child classes can override this to provide agent-specific tools.
     * This method can return both custom tools and MCP connectors.
     *
     * @return array<int, mixed>
     */
    protected function tools(): array
    {
        return [];
    }

    /**
     * Get MCP server configurations for this agent.
     *
     * Child classes can override this to specify which MCP servers
     * and tools should be available to the agent. Supports filtering
     * using 'exclude' and 'only' arrays.
     *
     * Example:
     * ```php
     * return [
     *     'memory' => ['only' => ['create_entity', 'search_nodes']],
     *     'filesystem' => ['exclude' => ['delete_file']],
     * ];
     * ```
     *
     * @return array<string, array{exclude?: array<string>, only?: array<string>}>
     */
    protected function mcpServers(): array
    {
        return [];
    }

    /**
     * Get all tools including MCP tools.
     *
     * Combines custom tools with MCP connector tools based on
     * the mcpServers() configuration.
     *
     * @return array<int, mixed>
     */
    protected function getAllTools(): array
    {
        $tools = $this->tools();

        // Add MCP tools if configured
        $mcpServers = $this->mcpServers();
        if (! empty($mcpServers)) {
            $mcpTools = \App\Neuron\Support\McpToolIntegration::getTools($mcpServers);
            $tools = array_merge($tools, $mcpTools);
        }

        return $tools;
    }
}
