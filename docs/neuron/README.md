# Neuron AI Framework Documentation

Welcome to the Neuron AI Framework documentation. This directory contains comprehensive documentation for building AI
agents in PHP.

## What is Neuron?

Neuron is a PHP framework for creating and orchestrating AI Agents. It allows you to integrate AI entities in your
existing PHP applications with a powerful and flexible architecture. The framework provides tools for the entire agentic
application development lifecycle, from LLM interfaces to data loading, multi-agent orchestration, monitoring, and
debugging.

## Key Features

- **Framework Agnostic**: Works seamlessly with Laravel, Symfony, WordPress, or any PHP application
- **Type-Safe**: Built with PHP 8's type system and passes PHPStan 100% type coverage
- **Multiple LLM Providers**: Support for Anthropic, OpenAI, Gemini, Ollama, and more
- **RAG Support**: Built-in Retrieval-Augmented Generation capabilities
- **Tools & Toolkits**: Extensible tool system for agent capabilities
- **Workflow Orchestration**: Event-driven, node-based multi-agent systems
- **Monitoring**: Built-in observability with Inspector integration

## Documentation Structure

### Getting Started

- [Installation](installation.md) - Setup and requirements
- [Creating Agents](agents.md) - Build your first AI agent
- [Streaming](streaming.md) - Real-time response streaming
- [Structured Output](structured-output.md) - Enforce output schemas

### Core Components

- [AI Providers](ai-providers.md) - LLM provider integrations
- [Tools & Toolkits](tools.md) - Extend agent capabilities
- [Chat History](chat-history.md) - Conversation memory management

### RAG (Retrieval-Augmented Generation)

- [RAG Overview](rag.md) - Introduction to RAG systems
- [Data Loaders](data-loaders.md) - Load and process documents
- [Vector Stores](vector-stores.md) - Store and search embeddings
- [Embeddings](embeddings.md) - Text embedding providers
- [Retrieval Strategies](retrieval.md) - Custom retrieval implementations
- [Pre/Post Processors](processors.md) - Optimize RAG output

### Advanced Topics

- [Workflows](workflows.md) - Multi-agent orchestration
- [Workflow State Management](workflow-state.md) - Managing workflow data

## Quick Start

```php
use NeuronAI\Agent;
use NeuronAI\SystemPrompt;
use NeuronAI\Providers\Anthropic\Anthropic;
use NeuronAI\Chat\Messages\UserMessage;

class MyAgent extends Agent
{
    protected function provider(): AIProviderInterface
    {
        return new Anthropic(
            key: 'ANTHROPIC_API_KEY',
            model: 'claude-3-5-sonnet-20241022',
        );
    }

    public function instructions(): string
    {
        return (string) new SystemPrompt(
            background: ["You are a friendly AI Agent."],
        );
    }
}

$response = MyAgent::make()->chat(
    new UserMessage("Hello!")
);

echo $response->getContent();
```text

## Requirements

- PHP ^8.1 or higher
- Composer for package management

## Installation

```bash
composer require neuron-core/neuron-ai
```text

## Resources

- **GitHub**: [neuron-core/neuron-ai](https://github.com/neuron-core/neuron-ai)
- **Official Documentation**: <https://docs.neuron-ai.dev/>
- **Monitoring**: [Inspector.dev](https://inspector.dev)
- **Community**: [GitHub Discussions](https://github.com/neuron-core/neuron-ai/discussions)

## Monitoring & Debugging

Neuron integrates with Inspector for comprehensive monitoring and debugging. Set your ingestion key in your environment:

```env
INSPECTOR_INGESTION_KEY=your_key_here
```text

This enables real-time monitoring of agent execution, tool calls, and workflow steps.

---

*Documentation source: <https://docs.neuron-ai.dev/>*

