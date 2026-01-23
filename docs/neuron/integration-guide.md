# Neuron AI Integration Guide

## Table of Contents

1. [Introduction](#introduction)
2. [Installation](#installation)
3. [Configuration](#configuration)
4. [Creating Agents](#creating-agents)
5. [Creating Tools](#creating-tools)
6. [Using Agents](#using-agents)
7. [Streaming Responses](#streaming-responses)
8. [Chat History](#chat-history)
9. [Error Handling](#error-handling)
10. [Testing](#testing)
11. [Troubleshooting](#troubleshooting)
12. [Advanced Topics](#advanced-topics)

## Introduction

Neuron AI is a PHP framework for creating and orchestrating AI Agents that power intelligent features in the Uma Musume Career Planner. This guide covers everything you need to know to work with Neuron AI in this Laravel application.

### What is Neuron AI?

Neuron AI provides:

- **Agent Framework**: Create specialized AI agents with custom instructions and tools
- **Multi-Provider Support**: Use Anthropic Claude, OpenAI GPT, AWS Bedrock, or Ollama
- **Tool Integration**: Give agents access to application data and functionality
- **Structured Outputs**: Get type-safe, validated responses from agents
- **Chat History**: Maintain conversation context across interactions
- **Streaming Support**: Real-time response generation for better UX
- **Laravel Integration**: Seamless integration with Laravel's service container and Eloquent ORM

### Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│                     Laravel Application                      │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  ┌──────────────┐      ┌──────────────┐      ┌───────────┐ │
│  │ Controllers  │─────▶│ Agent        │─────▶│ Neuron AI │ │
│  │              │      │ Services     │      │ Agents    │ │
│  └──────────────┘      └──────────────┘      └───────────┘ │
│         │                     │                      │       │
│         │                     │                      │       │
│         ▼                     ▼                      ▼       │
│  ┌──────────────┐      ┌──────────────┐      ┌───────────┐ │
│  │ Responses    │      │ Eloquent     │      │ Tools     │ │
│  │ (JSON/SSE)   │      │ Models       │      │           │ │
│  └──────────────┘      └──────────────┘      └───────────┘ │
│                                                               │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
                    ┌──────────────────┐
                    │  LLM Providers   │
                    ├──────────────────┤
                    │  • Anthropic     │
                    │  • OpenAI        │
                    │  • AWS Bedrock   │
                    │  • Ollama        │
                    └──────────────────┘
```

## Installation

### Prerequisites

- PHP 8.2 or higher
- Laravel 12
- Composer
- At least one AI provider API key (Anthropic, OpenAI, etc.)

### Step 1: Install Neuron AI Packages

Install the core Neuron AI package and Laravel integration:

```bash
composer require neuron-core/neuron-ai neuron-core/neuron-laravel
```

### Step 2: Publish Configuration

Publish the Neuron configuration file:

```bash
php artisan vendor:publish --tag=neuron-config
```

This creates `config/neuron.php` with default settings.

### Step 3: Publish and Run Migrations

Publish the chat history migration:

```bash
php artisan vendor:publish --tag=neuron-migrations
```

Run the migrations:

```bash
php artisan migrate
```

This creates the `neuron_chat_histories` table for conversation persistence.

### Step 4: Configure Environment Variables

Add your AI provider credentials to `.env`:

```env
# Default AI Provider
NEURON_AI_PROVIDER=anthropic

# Anthropic (Claude)
ANTHROPIC_KEY=your-anthropic-api-key
ANTHROPIC_MODEL=claude-3-5-sonnet-20241022

# OpenAI (GPT)
OPENAI_KEY=your-openai-api-key
OPENAI_MODEL=gpt-4

# Ollama (Local)
OLLAMA_URL=http://localhost:11434
OLLAMA_MODEL=llama2

# Optional: Inspector Monitoring
INSPECTOR_INGESTION_KEY=your-inspector-key
```

### Step 5: Verify Installation

Test that Neuron AI is properly installed:

```bash
php artisan neuron:agent --help
php artisan neuron:tool --help
```

You should see help output for these commands.

## Configuration

### Configuration File Structure

The `config/neuron.php` file contains all Neuron AI settings:

```php
return [
    // Default AI provider
    'default_provider' => env('NEURON_AI_PROVIDER', 'anthropic'),

    // AI Provider configurations
    'providers' => [
        'anthropic' => [
            'key' => env('ANTHROPIC_KEY'),
            'model' => env('ANTHROPIC_MODEL', 'claude-3-5-sonnet-20241022'),
            'parameters' => [
                'temperature' => 0.7,
                'max_tokens' => 2000,
            ],
        ],
        // ... other providers
    ],
];
```

### Provider Selection

You can configure different providers for different agents:

```php
// In your agent class
protected function provider(): AIProviderInterface
{
    // Use Anthropic for this agent
    return AIProvider::driver('anthropic');
    
    // Or use OpenAI
    return AIProvider::driver('openai');
    
    // Or use Ollama for local development
    return AIProvider::driver('ollama');
}
```

### Model Parameters

Adjust model parameters in the configuration:

- **temperature**: Controls randomness (0.0 = deterministic, 1.0 = creative)
- **max_tokens**: Maximum response length
- **top_p**: Nucleus sampling parameter
- **frequency_penalty**: Reduces repetition
- **presence_penalty**: Encourages topic diversity

## Creating Agents

### Using the Artisan Command

Create a new agent using the Artisan command:

```bash
php artisan neuron:agent TrainingAdvisorAgent
```

This creates `app/Neuron/Agents/TrainingAdvisorAgent.php` with a basic structure.

### Agent Structure

A complete agent implementation:

```php
<?php

namespace App\Neuron\Agents;

use NeuronAI\Agent;
use NeuronAI\SystemPrompt;
use NeuronAI\Laravel\Facades\AIProvider;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Chat\History\EloquentChatHistory;
use NeuronAI\Chat\History\ChatHistoryInterface;
use App\Neuron\Tools\CharacterStatsTool;

class TrainingAdvisorAgent extends Agent
{
    public function __construct(
        private int $userId,
        private ?int $sessionId = null
    ) {}

    protected function provider(): AIProviderInterface
    {
        return AIProvider::driver('anthropic');
    }

    public function instructions(): string
    {
        return (string) new SystemPrompt(
            background: [
                "You are an expert in Uma Musume training mechanics.",
                "You analyze character stats, aptitudes, and support card bonuses.",
            ],
            steps: [
                "Analyze current character statistics",
                "Consider character aptitudes for different training types",
                "Factor in support card bonuses and effects",
                "Recommend optimal training choice with reasoning",
            ],
            output: [
                "Provide a clear training recommendation",
                "Explain the reasoning behind your recommendation",
                "Include expected stat gains",
                "Suggest alternative options if applicable",
            ]
        );
    }

    protected function chatHistory(): ChatHistoryInterface
    {
        return new EloquentChatHistory(
            userId: $this->userId,
            sessionId: $this->sessionId,
            agentType: 'training_advisor'
        );
    }

    protected function tools(): array
    {
        return [
            CharacterStatsTool::make(),
        ];
    }
}
```

### System Prompts

System prompts define your agent's behavior. Use the `SystemPrompt` class for structured prompts:

```php
new SystemPrompt(
    background: [
        "Your agent's expertise and role",
        "Context about the domain",
    ],
    steps: [
        "Step 1: What to analyze first",
        "Step 2: What to consider next",
        "Step 3: How to formulate response",
    ],
    output: [
        "Format requirements",
        "What to include in response",
        "Tone and style guidelines",
    ]
)
```

### Base Agent Pattern

Create a base agent class for shared functionality:

```php
<?php

namespace App\Neuron\Agents;

use NeuronAI\Agent;
use NeuronAI\SystemPrompt;
use NeuronAI\Laravel\Facades\AIProvider;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Chat\History\EloquentChatHistory;
use NeuronAI\Chat\History\ChatHistoryInterface;

abstract class BaseAgent extends Agent
{
    protected function provider(): AIProviderInterface
    {
        return AIProvider::driver(config('neuron.default_provider'));
    }

    protected function buildSystemPrompt(
        array $background,
        array $steps,
        array $output
    ): string {
        return (string) new SystemPrompt(
            background: $background,
            steps: $steps,
            output: $output
        );
    }

    protected function chatHistory(): ChatHistoryInterface
    {
        return new EloquentChatHistory(
            userId: auth()->id(),
            sessionId: $this->getSessionId(),
            agentType: static::class
        );
    }

    abstract protected function getSessionId(): ?int;
}
```

Then extend it in your agents:

```php
class TrainingAdvisorAgent extends BaseAgent
{
    public function __construct(private ?int $sessionId = null) {}

    protected function getSessionId(): ?int
    {
        return $this->sessionId;
    }

    public function instructions(): string
    {
        return $this->buildSystemPrompt(
            background: ["You are an expert..."],
            steps: ["Analyze...", "Consider..."],
            output: ["Provide..."]
        );
    }
}
```

## Creating Tools

### Using the Artisan Command

Create a new tool using the Artisan command:

```bash
php artisan neuron:tool CharacterStatsTool
```

This creates `app/Neuron/Tools/CharacterStatsTool.php`.

### Tool Structure

A complete tool implementation:

```php
<?php

namespace App\Neuron\Tools;

use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;
use NeuronAI\Tools\PropertyType;
use App\Models\Character;

class CharacterStatsTool
{
    public static function make(): Tool
    {
        return Tool::make(
            name: 'get_character_stats',
            description: 'Retrieve character statistics including speed, stamina, power, guts, and wisdom'
        )
        ->addProperty(new ToolProperty(
            name: 'character_id',
            type: PropertyType::INTEGER,
            description: 'The ID of the character to retrieve stats for',
            required: true
        ))
        ->setCallable(function (int $character_id) {
            try {
                $character = Character::with(['aptitudes', 'supportCards'])
                    ->findOrFail($character_id);

                return [
                    'name' => $character->name,
                    'stats' => [
                        'speed' => $character->speed,
                        'stamina' => $character->stamina,
                        'power' => $character->power,
                        'guts' => $character->guts,
                        'wisdom' => $character->wisdom,
                    ],
                    'aptitudes' => $character->aptitudes->map(fn($apt) => [
                        'type' => $apt->type,
                        'grade' => $apt->grade,
                    ]),
                    'support_cards' => $character->supportCards->map(fn($card) => [
                        'name' => $card->name,
                        'type' => $card->type,
                        'rarity' => $card->rarity,
                    ]),
                ];
            } catch (\Exception $e) {
                return "Error: Character with ID {$character_id} not found.";
            }
        });
    }
}
```

### Tool Property Types

Available property types:

- `PropertyType::STRING` - Text values
- `PropertyType::INTEGER` - Whole numbers
- `PropertyType::NUMBER` - Decimal numbers
- `PropertyType::BOOLEAN` - True/false values
- `PropertyType::ARRAY` - Lists of values
- `PropertyType::OBJECT` - Structured data

### Tool Best Practices

1. **Return LLM-Friendly Data**: Format data as structured arrays or descriptive strings
2. **Handle Errors Gracefully**: Return error messages instead of throwing exceptions
3. **Include Context**: Provide enough information for the agent to make decisions
4. **Keep It Focused**: Each tool should do one thing well
5. **Document Parameters**: Use clear descriptions for all properties

Example of good tool output:

```php
// ✅ Good - structured and descriptive
return [
    'character' => 'Special Week',
    'current_stats' => [
        'speed' => 800,
        'stamina' => 700,
    ],
    'training_options' => [
        [
            'type' => 'speed',
            'expected_gain' => '+15 speed',
            'energy_cost' => 20,
        ],
        [
            'type' => 'stamina',
            'expected_gain' => '+12 stamina',
            'energy_cost' => 18,
        ],
    ],
];

// ❌ Bad - raw database objects
return Character::find($id);
```

### Registering Tools with Agents

Add tools to your agent's `tools()` method:

```php
protected function tools(): array
{
    return [
        CharacterStatsTool::make(),
        SkillDataTool::make(),
        RaceDataTool::make(),
    ];
}
```

## Using Agents

### Basic Agent Usage

Simple agent interaction:

```php
use App\Neuron\Agents\TrainingAdvisorAgent;
use NeuronAI\Messages\UserMessage;

// Create agent instance
$agent = new TrainingAdvisorAgent(
    userId: auth()->id(),
    sessionId: $sessionId
);

// Send a message
$response = $agent->chat(
    new UserMessage('What training should I do for my speed-focused character?')
);

// Get the response text
echo $response->content;
```

### Structured Outputs

Get type-safe, validated responses:

```php
use App\Neuron\Responses\TrainingAdviceResponse;

// Request structured output
$response = $agent->structured(
    TrainingAdviceResponse::class,
    new UserMessage('Recommend training for character ID 123')
);

// Access typed properties
echo $response->recommendedTraining; // string
echo $response->reasoning; // string
print_r($response->expectedGains); // array
```

### Defining Response Classes

Create response classes with validation:

```php
<?php

namespace App\Neuron\Responses;

use NeuronAI\Schema\SchemaProperty;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class TrainingAdviceResponse
{
    public function __construct(
        #[SchemaProperty(description: 'Recommended training type', required: true)]
        #[NotBlank]
        public string $recommendedTraining,

        #[SchemaProperty(description: 'Reasoning for recommendation', required: true)]
        #[NotBlank]
        #[Length(min: 20, max: 500)]
        public string $reasoning,

        #[SchemaProperty(description: 'Expected stat gains', required: true)]
        public array $expectedGains,

        #[SchemaProperty(description: 'Alternative options', required: false)]
        public array $alternatives = [],
    ) {}
}
```

### Using Service Classes

Create service classes to encapsulate agent logic:

```php
<?php

namespace App\Services\Neuron;

use App\Models\Character;
use App\Neuron\Agents\TrainingAdvisorAgent;
use App\Neuron\Responses\TrainingAdviceResponse;
use NeuronAI\Messages\UserMessage;

class TrainingAdvisorService
{
    public function __construct(
        private Character $characterModel
    ) {}

    public function getAdvice(int $characterId, ?int $sessionId = null): TrainingAdviceResponse
    {
        // Format context for agent
        $context = $this->formatTrainingContext($characterId);

        // Create and call agent
        $agent = new TrainingAdvisorAgent(
            userId: auth()->id(),
            sessionId: $sessionId
        );

        return $agent->structured(
            TrainingAdviceResponse::class,
            new UserMessage($context)
        );
    }

    private function formatTrainingContext(int $characterId): string
    {
        $character = $this->characterModel->with(['aptitudes', 'supportCards'])
            ->findOrFail($characterId);

        return sprintf(
            "Character: %s\nSpeed: %d, Stamina: %d, Power: %d\nWhat training should I do?",
            $character->name,
            $character->speed,
            $character->stamina,
            $character->power
        );
    }
}
```

### Controller Integration

Use agents in controllers:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Neuron\TrainingAdvisorService;
use Illuminate\Http\Request;

class TrainingAdvisorController extends Controller
{
    public function __construct(
        private TrainingAdvisorService $trainingAdvisorService
    ) {}

    public function getAdvice(Request $request)
    {
        $validated = $request->validate([
            'character_id' => 'required|integer|exists:characters,id',
            'session_id' => 'nullable|integer',
        ]);

        try {
            $response = $this->trainingAdvisorService->getAdvice(
                characterId: $validated['character_id'],
                sessionId: $validated['session_id'] ?? null
            );

            return response()->json([
                'recommendation' => $response->recommendedTraining,
                'reasoning' => $response->reasoning,
                'expected_gains' => $response->expectedGains,
                'alternatives' => $response->alternatives,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate training advice',
            ], 500);
        }
    }
}
```

## Streaming Responses

### Enabling Streaming

Stream responses for real-time user feedback:

```php
use NeuronAI\Messages\UserMessage;

$agent = new TrainingAdvisorAgent(
    userId: auth()->id(),
    sessionId: $sessionId
);

// Stream the response
foreach ($agent->stream(new UserMessage('Give me training advice')) as $chunk) {
    echo $chunk->content;
    flush();
}
```

### Server-Sent Events (SSE)

Implement SSE endpoint for browser streaming:

```php
public function streamAdvice(Request $request)
{
    $validated = $request->validate([
        'character_id' => 'required|integer|exists:characters,id',
        'message' => 'required|string',
    ]);

    return response()->stream(function () use ($validated) {
        $agent = new TrainingAdvisorAgent(
            userId: auth()->id(),
            sessionId: $validated['session_id'] ?? null
        );

        try {
            foreach ($agent->stream(new UserMessage($validated['message'])) as $chunk) {
                echo "data: " . json_encode([
                    'content' => $chunk->content,
                    'done' => false,
                ]) . "\n\n";
                
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            }

            // Send completion message
            echo "data: " . json_encode([
                'content' => '',
                'done' => true,
            ]) . "\n\n";
            
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();
        } catch (\Exception $e) {
            echo "data: " . json_encode([
                'error' => 'Streaming failed',
                'done' => true,
            ]) . "\n\n";
            
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();
        }
    }, 200, [
        'Content-Type' => 'text/event-stream',
        'Cache-Control' => 'no-cache',
        'X-Accel-Buffering' => 'no',
    ]);
}
```

### Frontend SSE Consumption

Connect to SSE endpoint from JavaScript:

```javascript
const eventSource = new EventSource('/api/training-advisor/stream?character_id=123');

eventSource.onmessage = (event) => {
    const data = JSON.parse(event.data);
    
    if (data.error) {
        console.error('Streaming error:', data.error);
        eventSource.close();
        return;
    }
    
    if (data.done) {
        console.log('Streaming complete');
        eventSource.close();
        return;
    }
    
    // Append chunk to UI
    document.getElementById('response').textContent += data.content;
};

eventSource.onerror = (error) => {
    console.error('SSE error:', error);
    eventSource.close();
};
```

## Chat History

### How Chat History Works

Neuron AI automatically persists conversation history using Laravel's Eloquent ORM:

- Messages are stored in the `neuron_chat_histories` table
- Each conversation is scoped by `user_id`, `session_id`, and `agent_type`
- History is automatically loaded when you create an agent with the same session

### Using Chat History

```php
// First interaction - creates new history
$agent = new TrainingAdvisorAgent(
    userId: auth()->id(),
    sessionId: 123
);

$response1 = $agent->chat(new UserMessage('What training should I do?'));

// Second interaction - loads previous history
$agent2 = new TrainingAdvisorAgent(
    userId: auth()->id(),
    sessionId: 123 // Same session ID
);

$response2 = $agent2->chat(new UserMessage('What about stamina training?'));
// Agent remembers the previous conversation
```

### Managing Sessions

Create unique session IDs for different conversations:

```php
// New training session
$trainingSessionId = $career->id;

// New race strategy session
$raceSessionId = $race->id;

// Different agents can have different sessions
$trainingAgent = new TrainingAdvisorAgent(
    userId: auth()->id(),
    sessionId: $trainingSessionId
);

$raceAgent = new RaceStrategyAgent(
    userId: auth()->id(),
    sessionId: $raceSessionId
);
```

### Clearing History

Clear history for a specific session:

```php
use Illuminate\Support\Facades\DB;

DB::table('neuron_chat_histories')
    ->where('user_id', auth()->id())
    ->where('session_id', $sessionId)
    ->where('agent_type', 'training_advisor')
    ->delete();
```

### History Pagination

For long conversations, implement pagination:

```php
use Illuminate\Support\Facades\DB;

$messages = DB::table('neuron_chat_histories')
    ->where('user_id', auth()->id())
    ->where('session_id', $sessionId)
    ->where('agent_type', 'training_advisor')
    ->orderBy('created_at', 'desc')
    ->paginate(50);
```

## Error Handling

### Provider Errors

Handle AI provider failures gracefully:

```php
use NeuronAI\Exceptions\ProviderException;
use Illuminate\Support\Facades\Log;

try {
    $response = $agent->chat($message);
} catch (ProviderException $e) {
    Log::error('AI Provider Error', [
        'provider' => 'anthropic',
        'user_id' => auth()->id(),
        'error' => $e->getMessage(),
    ]);

    return response()->json([
        'error' => 'AI service temporarily unavailable. Please try again.',
    ], 503);
}
```

### Validation Errors

Handle structured output validation failures:

```php
use NeuronAI\Exceptions\ValidationException;

try {
    $response = $agent->structured(
        TrainingAdviceResponse::class,
        $message,
        maxRetry: 3
    );
} catch (ValidationException $e) {
    Log::warning('Structured Output Validation Failed', [
        'agent' => get_class($agent),
        'errors' => $e->getErrors(),
    ]);

    // Return fallback response
    return new TrainingAdviceResponse(
        recommendedTraining: 'speed',
        reasoning: 'Unable to generate detailed advice. Default recommendation.',
        expectedGains: [],
    );
}
```

### Rate Limiting

Handle rate limit errors:

```php
use NeuronAI\Exceptions\RateLimitException;
use App\Jobs\ProcessAgentRequest;

try {
    $response = $agent->chat($message);
} catch (RateLimitException $e) {
    // Queue for later processing
    ProcessAgentRequest::dispatch(
        userId: auth()->id(),
        agentType: 'training_advisor',
        message: $message->content
    )->delay(now()->addMinutes(5));

    return response()->json([
        'message' => 'Request queued due to high demand. You will be notified when complete.',
    ], 202);
}
```

### Tool Execution Errors

Handle errors in tool callables:

```php
Tool::make('get_character_stats', 'Retrieve character statistics')
    ->addProperty(/* ... */)
    ->setCallable(function (int $character_id) {
        try {
            $character = Character::findOrFail($character_id);
            return $character->toArray();
        } catch (ModelNotFoundException $e) {
            Log::warning('Tool Error: Character not found', [
                'character_id' => $character_id,
            ]);
            
            // Return error message to agent
            return "Error: Character with ID {$character_id} not found.";
        }
    });
```

## Testing

### Mocking AI Providers

Mock LLM responses in tests:

```php
use NeuronAI\Messages\AssistantMessage;
use NeuronAI\Providers\Anthropic;

it('generates training advice', function () {
    // Mock the provider
    $mockProvider = Mockery::mock(Anthropic::class);
    $mockProvider->shouldReceive('chat')
        ->once()
        ->andReturn(new AssistantMessage('Recommend speed training'));

    // Create agent with mocked provider
    $agent = new TrainingAdvisorAgent(
        userId: 1,
        sessionId: 123
    );
    
    // Inject mock (implementation depends on your setup)
    $agent->setProvider($mockProvider);

    $response = $agent->chat(new UserMessage('What should I train?'));

    expect($response->content)->toBe('Recommend speed training');
});
```

### Testing Tools

Test tool execution:

```php
use App\Neuron\Tools\CharacterStatsTool;
use App\Models\Character;

it('retrieves character stats correctly', function () {
    $character = Character::factory()->create([
        'name' => 'Special Week',
        'speed' => 800,
        'stamina' => 700,
    ]);

    $tool = CharacterStatsTool::make();
    $callable = $tool->getCallable();
    
    $result = $callable($character->id);

    expect($result)->toBeArray()
        ->and($result['name'])->toBe('Special Week')
        ->and($result['stats']['speed'])->toBe(800)
        ->and($result['stats']['stamina'])->toBe(700);
});
```

### Testing Service Classes

Test service layer logic:

```php
use App\Services\Neuron\TrainingAdvisorService;
use App\Models\Character;

it('formats training context correctly', function () {
    $character = Character::factory()->create([
        'name' => 'Special Week',
        'speed' => 800,
    ]);

    $service = new TrainingAdvisorService(new Character());
    
    // Use reflection to test private method
    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('formatTrainingContext');
    $method->setAccessible(true);
    
    $context = $method->invoke($service, $character->id);

    expect($context)->toContain('Special Week')
        ->and($context)->toContain('800');
});
```

### Testing Chat History

Test conversation persistence:

```php
use App\Neuron\Agents\TrainingAdvisorAgent;
use NeuronAI\Messages\UserMessage;

it('persists and retrieves chat history', function () {
    $userId = 1;
    $sessionId = 123;

    // First interaction
    $agent1 = new TrainingAdvisorAgent($userId, $sessionId);
    $agent1->chat(new UserMessage('First message'));

    // Verify message was saved
    $this->assertDatabaseHas('neuron_chat_histories', [
        'user_id' => $userId,
        'session_id' => $sessionId,
        'agent_type' => 'training_advisor',
        'role' => 'user',
        'content' => 'First message',
    ]);

    // Second interaction with same session
    $agent2 = new TrainingAdvisorAgent($userId, $sessionId);
    
    // History should be loaded automatically
    // (Verify through agent behavior or database queries)
});
```

## Troubleshooting

### Common Issues

#### 1. "Provider credentials not found"

**Problem**: Missing or invalid API keys

**Solution**:

```bash
# Check your .env file
cat .env | grep ANTHROPIC_KEY
cat .env | grep OPENAI_KEY

# Verify config is loaded
php artisan config:clear
php artisan config:cache
```

#### 2. "Chat history table not found"

**Problem**: Migration not run

**Solution**:

```bash
# Publish migrations
php artisan vendor:publish --tag=neuron-migrations

# Run migrations
php artisan migrate

# Check table exists
php artisan db:show
```

#### 3. "Tool not found" or "Tool execution failed"

**Problem**: Tool not registered or callable has errors

**Solution**:

```php
// Verify tool is registered in agent
protected function tools(): array
{
    return [
        CharacterStatsTool::make(), // Make sure this is here
    ];
}

// Check tool callable for errors
Tool::make('tool_name', 'description')
    ->setCallable(function ($param) {
        try {
            // Your logic
        } catch (\Exception $e) {
            Log::error('Tool error', ['error' => $e->getMessage()]);
            return "Error: " . $e->getMessage();
        }
    });
```

#### 4. "Structured output validation failed"

**Problem**: Agent response doesn't match schema

**Solution**:

```php
// Increase retry attempts
$response = $agent->structured(
    TrainingAdviceResponse::class,
    $message,
    maxRetry: 5 // Increase from default 3
);

// Simplify your response schema
// Make optional fields truly optional
class TrainingAdviceResponse
{
    public function __construct(
        #[SchemaProperty(description: 'Recommendation', required: true)]
        public string $recommendation,
        
        // Make this optional if agent struggles
        #[SchemaProperty(description: 'Details', required: false)]
        public ?string $details = null,
    ) {}
}

// Check your system prompt is clear about output format
```

#### 5. "Rate limit exceeded"

**Problem**: Too many API calls

**Solution**:

```php
// Implement caching
use Illuminate\Support\Facades\Cache;

$cacheKey = "training_advice_{$characterId}_{$context}";

$response = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($agent, $message) {
    return $agent->chat($message);
});

// Implement queuing for non-urgent requests
ProcessAgentRequest::dispatch($userId, $message)->delay(now()->addMinutes(1));

// Use local Ollama for development
// Set NEURON_AI_PROVIDER=ollama in .env
```

#### 6. "Streaming not working"

**Problem**: Response buffering or headers

**Solution**:

```php
// Disable output buffering
if (ob_get_level() > 0) {
    ob_end_clean();
}

// Set correct headers
return response()->stream($callback, 200, [
    'Content-Type' => 'text/event-stream',
    'Cache-Control' => 'no-cache',
    'X-Accel-Buffering' => 'no', // Disable nginx buffering
]);

// Check nginx configuration
// Add to nginx.conf:
// proxy_buffering off;
```

### Debugging Tips

#### Enable Detailed Logging

```php
// In your agent or service
use Illuminate\Support\Facades\Log;

Log::debug('Agent Request', [
    'agent' => get_class($agent),
    'user_id' => auth()->id(),
    'message' => $message->content,
]);

$response = $agent->chat($message);

Log::debug('Agent Response', [
    'agent' => get_class($agent),
    'response' => $response->content,
]);
```

#### Test Provider Connection

```php
// Create a simple test route
Route::get('/test-neuron', function () {
    try {
        $provider = AIProvider::driver('anthropic');
        $response = $provider->chat([
            new UserMessage('Say hello')
        ]);
        
        return response()->json([
            'success' => true,
            'response' => $response->content,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
});
```

#### Inspect Chat History

```php
// View all messages for a session
Route::get('/debug/chat-history/{sessionId}', function ($sessionId) {
    $messages = DB::table('neuron_chat_histories')
        ->where('session_id', $sessionId)
        ->orderBy('created_at')
        ->get();
    
    return response()->json($messages);
});
```

#### Monitor Tool Calls

```php
// Add logging to tool callables
Tool::make('get_character_stats', 'description')
    ->setCallable(function (int $character_id) {
        Log::info('Tool Called', [
            'tool' => 'get_character_stats',
            'character_id' => $character_id,
        ]);
        
        $result = Character::find($character_id);
        
        Log::info('Tool Result', [
            'tool' => 'get_character_stats',
            'found' => $result !== null,
        ]);
        
        return $result;
    });
```

## Advanced Topics

### Multi-Agent Workflows

Coordinate multiple agents for complex tasks:

```php
class CareerPlanningService
{
    public function __construct(
        private TrainingAdvisorAgent $trainingAgent,
        private RaceStrategyAgent $raceAgent,
        private SkillRecommendationAgent $skillAgent
    ) {}

    public function planCareer(int $characterId, int $sessionId): array
    {
        // Get training advice
        $trainingAdvice = $this->trainingAgent->structured(
            TrainingAdviceResponse::class,
            new UserMessage("Plan training for character {$characterId}")
        );

        // Get race strategy based on training plan
        $raceStrategy = $this->raceAgent->structured(
            RaceStrategyResponse::class,
            new UserMessage("Plan races for character with {$trainingAdvice->recommendedTraining} focus")
        );

        // Get skill recommendations
        $skillAdvice = $this->skillAgent->structured(
            SkillRecommendationResponse::class,
            new UserMessage("Recommend skills for {$trainingAdvice->recommendedTraining} build")
        );

        return [
            'training' => $trainingAdvice,
            'races' => $raceStrategy,
            'skills' => $skillAdvice,
        ];
    }
}
```

### Custom Provider Configuration

Add custom provider settings:

```php
// config/neuron.php
'providers' => [
    'custom_anthropic' => [
        'driver' => 'anthropic',
        'key' => env('CUSTOM_ANTHROPIC_KEY'),
        'model' => 'claude-3-opus-20240229',
        'parameters' => [
            'temperature' => 0.9,
            'max_tokens' => 4000,
            'top_p' => 0.95,
        ],
    ],
],

// Use in agent
protected function provider(): AIProviderInterface
{
    return AIProvider::driver('custom_anthropic');
}
```

### Dynamic Tool Registration

Register tools dynamically based on context:

```php
class DynamicAgent extends BaseAgent
{
    public function __construct(
        private int $userId,
        private ?int $sessionId = null,
        private array $enabledTools = []
    ) {}

    protected function tools(): array
    {
        $tools = [];

        if (in_array('character_stats', $this->enabledTools)) {
            $tools[] = CharacterStatsTool::make();
        }

        if (in_array('skill_data', $this->enabledTools)) {
            $tools[] = SkillDataTool::make();
        }

        if (in_array('race_data', $this->enabledTools)) {
            $tools[] = RaceDataTool::make();
        }

        return $tools;
    }
}

// Usage
$agent = new DynamicAgent(
    userId: auth()->id(),
    sessionId: 123,
    enabledTools: ['character_stats', 'skill_data']
);
```

### MCP Connector Integration (Optional)

Connect to Model Context Protocol (MCP) servers to access pre-built tools without implementing them manually.

**See the [MCP Connector Guide](./mcp-connector-guide.md) for detailed documentation.**

#### Quick Example

```php
use App\Neuron\Support\McpConnectorFactory;

class TrainingAdvisorAgent extends BaseAgent
{
    protected function mcpConnectors(): array
    {
        $connectors = [];

        // Add memory server for persistent knowledge
        if (McpConnectorFactory::isServerEnabled('memory')) {
            $connectors[] = McpConnectorFactory::make('memory');
        }

        // Add umapyoi server for game data
        if (McpConnectorFactory::isServerEnabled('umapyoi')) {
            $connectors[] = McpConnectorFactory::make('umapyoi');
        }

        return $connectors;
    }
}
```

#### Configuration

Enable MCP in your `.env` file:

```env
# Enable MCP connector
NEURON_MCP_ENABLED=true

# Enable specific servers
NEURON_MCP_MEMORY_ENABLED=true
NEURON_MCP_UMAPYOI_ENABLED=true
NEURON_MCP_UMAPYOI_URL=https://api.umapyoi.net/mcp
NEURON_MCP_UMAPYOI_TOKEN=your-token-here
```

#### Benefits

- **Rapid Integration**: Connect to existing MCP servers without custom code
- **Standardized Interface**: All MCP servers follow the same protocol
- **Tool Discovery**: Automatically discover available tools
- **Flexible Filtering**: Choose which tools to expose to agents

For complete documentation, see the [MCP Connector Guide](./mcp-connector-guide.md).

### Context-Aware System Prompts

Adjust system prompts based on user preferences:

```php
class AdaptiveAgent extends BaseAgent
{
    public function instructions(): string
    {
        $user = auth()->user();
        
        $background = [
            "You are an expert in Uma Musume training mechanics.",
        ];

        // Adjust based on user experience level
        if ($user->experience_level === 'beginner') {
            $background[] = "Provide detailed explanations suitable for beginners.";
            $background[] = "Avoid advanced terminology without explanation.";
        } else {
            $background[] = "Provide concise, expert-level advice.";
            $background[] = "Use advanced terminology freely.";
        }

        return $this->buildSystemPrompt(
            background: $background,
            steps: [/* ... */],
            output: [/* ... */]
        );
    }
}
```

### Response Caching

Cache agent responses for identical requests:

```php
use Illuminate\Support\Facades\Cache;

class CachedTrainingAdvisorService
{
    public function getAdvice(int $characterId, string $context): TrainingAdviceResponse
    {
        $cacheKey = "training_advice_{$characterId}_" . md5($context);

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($characterId, $context) {
            $agent = new TrainingAdvisorAgent(
                userId: auth()->id(),
                sessionId: null // No session for cached responses
            );

            return $agent->structured(
                TrainingAdviceResponse::class,
                new UserMessage($context)
            );
        });
    }
}
```

### Monitoring with Inspector

Inspector is a real-time monitoring and debugging service that provides deep insights into your AI agent execution. It's particularly valuable for production environments where you need to track performance, debug issues, and optimize agent behavior.

#### What Inspector Monitors

Inspector automatically tracks:

- **Agent Execution Times**: How long each agent call takes
- **Tool Calls and Results**: Which tools are called and what they return
- **Token Usage**: Track API costs and optimize prompts
- **Error Rates**: Identify failing agents or tools
- **Response Quality**: Monitor validation failures and retries
- **User Interactions**: Track which users are using which agents
- **Provider Performance**: Compare response times across providers

#### Setting Up Inspector

1. **Sign up for Inspector**

   Visit [https://inspector.dev](https://inspector.dev) and create an account.

2. **Get your Ingestion Key**

   After signing up, you'll receive an ingestion key from your Inspector dashboard.

3. **Configure Environment Variable**

   Add your ingestion key to `.env`:

   ```env
   # Inspector Monitoring (optional)
   INSPECTOR_INGESTION_KEY=your-inspector-ingestion-key-here
   ```

4. **Verify Configuration**

   Inspector will automatically start tracking agent executions once the key is configured. No additional code changes are required.

#### Accessing Inspector Dashboard

Access your Inspector dashboard at: [https://app.inspector.dev](https://app.inspector.dev)

The dashboard provides:

- **Real-time Monitoring**: See agent calls as they happen
- **Performance Metrics**: Average response times, P95, P99
- **Error Tracking**: Stack traces and error context
- **Tool Analytics**: Which tools are most used
- **Cost Analysis**: Token usage and estimated API costs
- **User Insights**: Which users are most active

#### Inspector Best Practices

1. **Use in Production Only**

   Inspector adds minimal overhead but is most valuable in production. For development, consider disabling it:

   ```env
   # .env.local
   INSPECTOR_INGESTION_KEY=
   ```

2. **Set Up Alerts**

   Configure alerts in the Inspector dashboard for:
   - High error rates (> 5%)
   - Slow response times (> 10 seconds)
   - Unusual token usage spikes
   - Tool execution failures

3. **Monitor Token Usage**

   Use Inspector to identify expensive prompts and optimize them:
   - Track token usage per agent type
   - Identify prompts that consistently use max tokens
   - Compare token usage across providers

4. **Debug Production Issues**

   When users report issues, use Inspector to:
   - View the exact agent conversation
   - See which tools were called
   - Check for validation failures
   - Review error messages and stack traces

5. **Optimize Performance**

   Use Inspector metrics to:
   - Identify slow tools that need optimization
   - Compare provider response times
   - Find agents that need prompt refinement
   - Track the impact of code changes

#### Inspector in Development vs Production

**Development** (Inspector disabled):

```env
INSPECTOR_INGESTION_KEY=
```

**Production** (Inspector enabled):

```env
INSPECTOR_INGESTION_KEY=your-actual-key
```

**Staging** (Inspector enabled with separate key):

```env
INSPECTOR_INGESTION_KEY=your-staging-key
```

#### Troubleshooting Inspector

**Issue**: Inspector not tracking agent calls

**Solutions**:

- Verify `INSPECTOR_INGESTION_KEY` is set in `.env`
- Clear config cache: `php artisan config:clear`
- Check Inspector dashboard for connection status
- Verify your ingestion key is valid

**Issue**: Too much data being tracked

**Solutions**:

- Use Inspector's filtering options in the dashboard
- Configure sampling rate in Inspector settings
- Disable Inspector in development environments

**Issue**: Performance impact

**Solutions**:

- Inspector has minimal overhead (< 5ms per request)
- If concerned, enable only in production
- Use Inspector's async mode for zero blocking

#### Inspector Pricing

Inspector offers:

- **Free Tier**: Up to 10,000 transactions/month
- **Pro Tier**: Unlimited transactions with advanced features
- **Enterprise**: Custom pricing for large deployments

For most applications, the free tier is sufficient for development and small production deployments.

#### Alternative Monitoring Solutions

If Inspector doesn't fit your needs, consider:

- **Laravel Telescope**: Built-in Laravel debugging tool (development only)
- **Sentry**: General error tracking and performance monitoring
- **New Relic**: Full application performance monitoring
- **Custom Logging**: Use Laravel's logging with log aggregation services

However, Inspector is specifically designed for AI agent monitoring and provides the most relevant insights for Neuron AI applications.

### MCP Connector Integration (Optional)

Connect to Model Context Protocol servers:

```php
use NeuronAI\MCP\McpConnector;

// Connect to local MCP server
$connector = McpConnector::local()
    ->command('npx')
    ->args(['-y', '@modelcontextprotocol/server-filesystem', '/path/to/data'])
    ->connect();

// Connect to remote MCP server
$connector = McpConnector::remote()
    ->url('https://mcp-server.example.com')
    ->token(env('MCP_SERVER_TOKEN'))
    ->connect();

// Use MCP tools in agent
class McpEnabledAgent extends BaseAgent
{
    protected function tools(): array
    {
        $mcpConnector = McpConnector::local()
            ->command('npx')
            ->args(['-y', '@modelcontextprotocol/server-filesystem', storage_path('app/game-data')])
            ->connect();

        return array_merge(
            parent::tools(),
            $mcpConnector->tools()->only(['read_file', 'list_directory'])->toArray()
        );
    }
}
```

## Best Practices

### 1. Provider Selection

- **Development**: Use Ollama for free local testing
- **Production**: Use Anthropic Claude for best results
- **Cost Optimization**: Use OpenAI GPT-3.5 for simple tasks, GPT-4 for complex ones
- **Enterprise**: Use AWS Bedrock for compliance and control

### 2. System Prompt Design

- **Be Specific**: Clearly define the agent's role and expertise
- **Provide Context**: Include relevant domain knowledge
- **Define Steps**: Break down the reasoning process
- **Specify Output**: Describe the expected response format
- **Iterate**: Refine prompts based on actual responses

### 3. Tool Design

- **Single Responsibility**: Each tool should do one thing well
- **LLM-Friendly Output**: Return structured, descriptive data
- **Error Handling**: Return error messages, don't throw exceptions
- **Performance**: Cache expensive operations
- **Documentation**: Use clear descriptions for tool parameters

### 4. Error Handling

- **Graceful Degradation**: Provide fallback responses
- **User-Friendly Messages**: Don't expose technical errors to users
- **Comprehensive Logging**: Log all errors with context
- **Retry Logic**: Implement exponential backoff for transient failures
- **Monitoring**: Track error rates and patterns

### 5. Performance Optimization

- **Cache Responses**: Cache identical requests
- **Lazy Loading**: Only load data when needed
- **Streaming**: Use streaming for long responses
- **Batch Processing**: Queue non-urgent requests
- **Tool Optimization**: Minimize database queries in tools

### 6. Security

- **API Key Protection**: Never commit API keys to version control
- **User Authorization**: Verify user permissions before agent calls
- **Input Validation**: Validate all user inputs
- **Rate Limiting**: Implement rate limits on agent endpoints
- **Audit Logging**: Log all agent interactions for security review

### 7. Testing

- **Mock Providers**: Always mock LLM responses in tests
- **Test Tools Independently**: Unit test tool callables
- **Integration Tests**: Test full controller → service → agent flow
- **Property Tests**: Verify universal properties hold
- **Performance Tests**: Monitor response times

## Quick Reference

### Artisan Commands

```bash
# Create new agent
php artisan neuron:agent AgentName

# Create new tool
php artisan neuron:tool ToolName

# Publish configuration
php artisan vendor:publish --tag=neuron-config

# Publish migrations
php artisan vendor:publish --tag=neuron-migrations
```

### Common Patterns

#### Basic Agent Call

```php
$agent = new TrainingAdvisorAgent(auth()->id(), $sessionId);
$response = $agent->chat(new UserMessage('Your question'));
```

#### Structured Output

```php
$response = $agent->structured(
    ResponseClass::class,
    new UserMessage('Your question')
);
```

#### Streaming Response

```php
foreach ($agent->stream(new UserMessage('Your question')) as $chunk) {
    echo $chunk->content;
}
```

#### Tool Creation

```php
Tool::make('tool_name', 'Description')
    ->addProperty(new ToolProperty(
        name: 'param_name',
        type: PropertyType::STRING,
        description: 'Parameter description',
        required: true
    ))
    ->setCallable(function ($param_name) {
        // Tool logic
        return $result;
    });
```

### Environment Variables

```env
NEURON_AI_PROVIDER=anthropic
ANTHROPIC_KEY=sk-ant-...
ANTHROPIC_MODEL=claude-3-5-sonnet-20241022
OPENAI_KEY=sk-...
OPENAI_MODEL=gpt-4
OLLAMA_URL=http://localhost:11434
OLLAMA_MODEL=llama2
INSPECTOR_INGESTION_KEY=...
```

## Additional Resources

### Official Documentation

- [Neuron AI Documentation](https://neuron-ai.dev)
- [Neuron Laravel Package](https://github.com/neuron-core/neuron-laravel)
- [Laravel Documentation](https://laravel.com/docs)

### Provider Documentation

- [Anthropic Claude API](https://docs.anthropic.com)
- [OpenAI API](https://platform.openai.com/docs)
- [AWS Bedrock](https://docs.aws.amazon.com/bedrock)
- [Ollama](https://ollama.ai/docs)

### Related Tools

- [Inspector Monitoring](https://inspector.dev)
- [Model Context Protocol](https://modelcontextprotocol.io)
- [MCP Connector Guide](./mcp-connector-guide.md) - Learn how to integrate MCP servers with Neuron AI agents

### Community

- [Neuron AI Discord](https://discord.gg/neuron-ai)
- [Laravel Discord](https://discord.gg/laravel)

## Changelog

### Version 1.0.0 (2026-01-12)

- Initial integration guide
- Installation and configuration instructions
- Agent creation examples
- Tool creation with `php artisan neuron:tool`
- Streaming response implementation
- Chat history management
- Error handling patterns
- Testing strategies
- Troubleshooting guide
- Advanced topics and best practices

## Contributing

Found an issue or have a suggestion? Please open an issue or submit a pull request on GitHub.

## License

This documentation is part of the Uma Musume Career Planner project and is licensed under the same terms as the main application.

---

**Last Updated**: January 12, 2026  
**Neuron AI Version**: Latest  
**Laravel Version**: 12.x
