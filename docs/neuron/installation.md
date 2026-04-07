# Installation

Step by step instructions on how to install Neuron in your application and create an Agent.

## Requirements

- **PHP**: ^8.1 or higher

## Install

Run the composer command below to install the latest version:

```bash
composer require neuron-core/neuron-ai
```text

## Create an Agent

You can easily create your first agent with the command below:

**Linux/Mac:**

```bash
php vendor/bin/neuron make:agent App\\Neuron\\MyAgent
```text

**Windows:**

```bash
php .\vendor\bin\neuron make:agent App\Neuron\MyAgent
```text

This will generate a basic agent class:

```php
namespace App\Neuron;

use NeuronAI\Agent;
use NeuronAI\SystemPrompt;
use NeuronAI\Providers\Anthropic\Anthropic;
use NeuronAI\Providers\AIProviderInterface;

class MyAgent extends Agent
{
    protected function provider(): AIProviderInterface
    {
        // return an AI provider (Anthropic, OpenAI, Ollama, Gemini, etc.)
        return new Anthropic(
            key: 'ANTHROPIC_API_KEY',
            model: 'ANTHROPIC_MODEL',
        );
    }

    public function instructions(): string
    {
        return (string) new SystemPrompt(
            background: ["You are a friendly AI Agent created with Neuron framework."],
        );
    }
}
```

## Talk to the Agent

Send a prompt to the agent to get a response from the underlying LLM:

```php
use NeuronAI\Chat\Messages\UserMessage;

$response = MyAgent::make()->chat(
    new UserMessage("Hi, Who are you?")
);

echo $response->getContent();

// I'm a friendly AI Agent built with Neuron, how can I help you today?
```text

## Monitoring & Debugging

Many of the applications you build with Neuron will contain multiple steps with multiple invocations of LLM calls,
tools, external memory systems, etc. As these applications get more and more complex, it becomes crucial to be able to
inspect what exactly is going on inside your agentic system.

The best way to do this is with [Inspector](https://inspector.dev).

After you sign up at the link above, make sure to set the `INSPECTOR_INGESTION_KEY` variable in the application
environment file to start monitoring:

```env
INSPECTOR_INGESTION_KEY=nwse877auxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```text

## Video Tutorial On A Laravel Application

Check out the official video tutorials for detailed walkthroughs of integrating Neuron with Laravel applications.

---

**Source:** <https://docs.neuron-ai.dev/the-basics>
