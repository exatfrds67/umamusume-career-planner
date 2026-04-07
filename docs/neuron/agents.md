# Creating Agents

Easily implement LLM interactions by extending the basic Agent class.

## Overview

You can create your agent by extending the `NeuronAI\Agent` class to inherit the main features of the framework and
create fully functional agents. This class automatically manages advanced mechanisms for you such as:

- Chat history
- Tools and function calls
- RAG systems

Extending the base class makes it easier to add custom methods and behavior to the agent, and also promotes portability,
because all the moving parts are encapsulated into a single entity that you can run wherever you want in your
application, or even release as a standalone composer package.

## Example: YouTube Video Summarizer

Let's create an AI Agent that summarizes YouTube videos.

### Create the Agent Class

```bash
php vendor/bin/neuron make:agent App\\Neuron\\YouTubeAgent
```text

This creates a basic agent structure that you'll customize.

### Implement the Provider

The minimum implementation requires assigning an AI Provider that will be the language and reasoning engine of your
agent.

The only required method to implement is `provider()` returning the instance of the provider you want to use:

```php
namespace App\Neuron;

use NeuronAI\Agent;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\Anthropic\Anthropic;

class YouTubeAgent extends Agent
{
    protected function provider(): AIProviderInterface
    {
        return new Anthropic(
            key: 'ANTHROPIC_API_KEY',
            model: 'ANTHROPIC_MODEL',
        );
    }
}
```text

You can also use other providers like OpenAI, Gemini, or Ollama if you want to run the model locally. Check out the [AI
Providers](ai-providers.md) documentation.

### Add System Instructions

System instructions provide directions for making the AI act according to the task we want to achieve. They are fixed
instructions that will be sent to the LLM on every interaction.

Implement the `instructions()` method:

```php
public function instructions(): string
{
    return (string) new SystemPrompt(
        background: ["You are an AI Agent specialized in writing YouTube video summaries."],
        steps: [
            "Get the url of a YouTube video, or ask the user to provide one.",
            "Use the tools you have available to retrieve the transcription of the video.",
            "Write the summary.",
        ],
        output: [
            "Write a summary in a paragraph without using lists. Use just fluent text.",
            "After the summary add a list of three sentences as the three most important take away from the video.",
        ]
    );
}
```text

#### SystemPrompt Properties

The `SystemPrompt` class is designed to take your base instructions and build a consistent prompt for the underlying
model, reducing the effort for prompt engineering:

- **background**: Context about the agent's role and expertise
- **steps**: Step-by-step instructions for task completion
- **output**: Format and structure requirements for responses

We highly recommend using the `SystemPrompt` class to increase the quality of results. Alternatively, you can return a
simple string:

```php
public function instructions(): string
{
    return "You are an AI Agent specialized in writing YouTube video summaries.";
}
```

## Using the Agent

The agent always accepts input as a `Message` class and returns `Message` instances.

```php
use NeuronAI\Chat\Messages\UserMessage;

$response = YouTubeAgent::make()->chat(
    new UserMessage("Summarize this video: https://youtube.com/watch?v=...")
);

echo $response->getContent();
```text

As you saw in the example above, we sent a `UserMessage` instance to the agent and it responded with an
`AssistantMessage` instance. A list of assistant messages and user messages creates a chat.

## Inline Configuration

In alternative to the single class encapsulation, you can also instruct the agent inline using a fluent chain of
methods:

```php
use NeuronAI\Agent;
use NeuronAI\Providers\Anthropic\Anthropic;

$agent = Agent::make()
    ->withProvider(new Anthropic(
        key: 'ANTHROPIC_API_KEY',
        model: 'ANTHROPIC_MODEL'
    ))
    ->withInstructions("You are a helpful assistant");

$response = $agent->chat(new UserMessage("Hello!"));
```text

## Monitoring

To watch inside the agent workflow, connect your Agent to the [Inspector monitoring dashboard](https://inspector.dev) to
see the execution flow in real-time.

After you sign up, set the `INSPECTOR_INGESTION_KEY` variable in your environment file:

```env
INSPECTOR_INGESTION_KEY=your_key_here
```text

---

**Source:** <https://docs.neuron-ai.dev/getting-started/agent>
