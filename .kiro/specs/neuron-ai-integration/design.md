# Design Document: Neuron AI Integration

## Overview

This design document outlines the integration of the Neuron AI framework into the Uma Musume Career Planner Laravel application. Neuron AI is a PHP framework for creating and orchestrating AI Agents that will power intelligent features including training optimization, race strategy recommendations, and skill selection advice.

### Goals

1. Integrate Neuron AI framework with Laravel 12 architecture
2. Create specialized AI agents for Uma Musume game mechanics
3. Provide seamless integration with existing Laravel services and Eloquent models
4. Support multiple LLM providers for flexibility and cost optimization
5. Enable real-time streaming responses for better user experience
6. Implement robust error handling and monitoring

### Non-Goals

1. Building a general-purpose AI chatbot (focus is on Uma Musume-specific agents)
2. Training custom LLM models (we use existing provider APIs)
3. Implementing RAG systems in the initial phase (future enhancement)
4. Creating a workflow orchestration system (single-agent interactions initially)

## Architecture

### High-Level Architecture

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

### Component Layers

1. **Controller Layer**: HTTP endpoints that receive user requests
2. **Service Layer**: Business logic that orchestrates agent interactions
3. **Agent Layer**: Neuron AI agents with specialized knowledge
4. **Tool Layer**: Functions that agents can call to access data
5. **Model Layer**: Eloquent models for database access
6. **Provider Layer**: LLM provider integrations

## Components and Interfaces

### Base Agent Class

**Location**: `app/Neuron/Agents/BaseAgent.php`

**Purpose**: Abstract base class that all Uma Musume agents extend, providing common functionality.

**Key Methods**:

```php
abstract class BaseAgent extends Agent
{
    // Provider selection using Laravel facade
    protected function provider(): AIProviderInterface
    {
        return AIProvider::driver(config('neuron.default_provider'));
    }

    // Common system prompt utilities
    protected function buildSystemPrompt(array $background, array $steps, array $output): string
    {
        return (string) new SystemPrompt(
            background: $background,
            steps: $steps,
            output: $output
        );
    }

    // Chat history using EloquentChatHistory
    protected function chatHistory(): ChatHistoryInterface
    {
        return new EloquentChatHistory(
            userId: auth()->id(),
            sessionId: $this->getSessionId(),
            agentType: static::class
        );
    }

    // Tool registration
    protected function tools(): array
    {
        return [];
    }
    
    abstract protected function getSessionId(): ?int;
}
```

### Agent Service Classes

**Location**: `app/Services/Neuron/`

**Purpose**: Service classes that act as intermediaries between controllers and agents, handling data formatting and response parsing.

**Example Structure**:

```php
class TrainingAdvisorService
{
    public function __construct(
        private Character $characterModel,
        private Training $trainingModel,
        private SupportCard $supportCardModel
    ) {}

    public function getAdvice(int $characterId, array $trainingOptions): TrainingAdviceResponse
    {
        // Format data for agent
        $context = $this->formatTrainingContext($characterId, $trainingOptions);

        // Call agent
        $agent = TrainingAdvisorAgent::make();
        $response = $agent->structured(
            TrainingAdviceResponse::class,
            new UserMessage($context)
        );

        return $response;
    }

    private function formatTrainingContext(int $characterId, array $trainingOptions): string
    {
        // Transform database models into LLM-friendly format
    }
}
```

### Specialized Agents

#### Training Advisor Agent

**Location**: `app/Neuron/Agents/TrainingAdvisorAgent.php`

**Purpose**: Provides training recommendations based on character stats, aptitudes, and support cards.

**System Prompt**:

- Background: Expert in Uma Musume training mechanics
- Steps: Analyze stats → Consider aptitudes → Factor support cards → Recommend training
- Output: Structured recommendation with reasoning

**Implementation**:

```php
use NeuronAI\Agent;
use NeuronAI\SystemPrompt;
use NeuronAI\Laravel\Facades\AIProvider;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Chat\History\EloquentChatHistory;

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
            TrainingOptionsTool::make(),
            SupportCardBonusesTool::make(),
        ];
    }
}
```

**Tools**:

- `CharacterStatsTool`: Retrieve current character statistics
- `TrainingOptionsTool`: Get available training choices
- `SupportCardBonusesTool`: Calculate support card effects

#### Race Strategy Agent

**Location**: `app/Neuron/RaceStrategyAgent.php`

**Purpose**: Suggests race strategies and skill loadouts based on race conditions.

**System Prompt**:

- Background: Expert in Uma Musume race mechanics
- Steps: Analyze race → Evaluate character → Recommend skills → Suggest strategy
- Output: Structured strategy with skill recommendations

**Tools**:

- `get_race_details`: Retrieve race information
- `get_character_capabilities`: Get character stats and skills
- `get_available_skills`: List skills that can be equipped

#### Skill Recommendation Agent

**Location**: `app/Neuron/SkillRecommendationAgent.php`

**Purpose**: Recommends skills to acquire based on character build and race preferences.

**System Prompt**:

- Background: Expert in Uma Musume skill synergies
- Steps: Analyze build → Consider race types → Evaluate cost → Recommend skills
- Output: Prioritized skill list with explanations

**Tools**:

- `get_available_skills`: List skills available for acquisition
- `get_character_build`: Get character stats and existing skills
- `get_skill_synergies`: Calculate skill compatibility

### Tool Implementations

**Location**: `app/Neuron/Tools/`

**Purpose**: Provide agents with access to application data in LLM-optimized formats.

**Example Tool**:

```php
Tool::make('get_character_stats', 'Retrieve character statistics')
    ->addProperty(new ToolProperty(
        name: 'character_id',
        type: PropertyType::INTEGER,
        description: 'Character ID',
        required: true
    ))
    ->setCallable(function (int $character_id) {
        $character = Character::findOrFail($character_id);
        return [
            'name' => $character->name,
            'speed' => $character->speed,
            'stamina' => $character->stamina,
            'power' => $character->power,
            'guts' => $character->guts,
            'wisdom' => $character->wisdom,
            'aptitudes' => $character->aptitudes,
        ];
    });
```

### Structured Output Classes

**Location**: `app/Neuron/Responses/`

**Purpose**: Define schemas for agent responses to ensure type safety and validation.

**Example**:

```php
class TrainingAdviceResponse
{
    public function __construct(
        #[SchemaProperty(description: 'Recommended training type', required: true)]
        #[NotBlank]
        public string $recommendedTraining,

        #[SchemaProperty(description: 'Reasoning for recommendation', required: true)]
        #[NotBlank]
        #[StringLength(min: 20, max: 500)]
        public string $reasoning,

        #[SchemaProperty(description: 'Expected stat gains', required: true)]
        public array $expectedGains,

        #[SchemaProperty(description: 'Alternative options', required: false)]
        public array $alternatives = [],
    ) {}
}
```

### Configuration

**Location**: `config/neuron.php`

**Purpose**: Centralized configuration for Neuron AI integration (published from neuron-laravel package).

**Structure**:

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
        'openai' => [
            'key' => env('OPENAI_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4'),
            'parameters' => [
                'temperature' => 0.7,
                'max_tokens' => 2000,
            ],
        ],
        'ollama' => [
            'url' => env('OLLAMA_URL', 'http://localhost:11434'),
            'model' => env('OLLAMA_MODEL', 'llama2'),
        ],
    ],

    // Embedding provider for RAG (optional)
    'embeddings' => [
        'default' => env('NEURON_EMBEDDINGS_PROVIDER', 'openai'),
        'providers' => [
            'openai' => [
                'key' => env('OPENAI_KEY'),
                'model' => env('OPENAI_EMBEDDINGS_MODEL', 'text-embedding-3-small'),
            ],
        ],
    ],

    // Vector store for RAG (optional)
    'vector_store' => [
        'default' => env('NEURON_VECTOR_STORE', 'file'),
        'stores' => [
            'file' => [
                'path' => storage_path('app/neuron/vectors'),
            ],
        ],
    ],

    // Inspector monitoring
    'monitoring' => [
        'inspector_key' => env('INSPECTOR_INGESTION_KEY'),
    ],

    // System prompt defaults
    'system_prompt' => [
        'background' => [],
        'steps' => [],
        'output' => [],
    ],
];
```

## Data Models

### Chat History (EloquentChatHistory)

**Location**: Provided by `neuron-laravel` package

**Purpose**: Persist conversation history for context maintenance using Laravel's Eloquent ORM.

**Migration**: Published via `php artisan vendor:publish --tag=neuron-migrations`

**Schema** (from neuron-laravel package):

```php
Schema::create('neuron_chat_histories', function (Blueprint $table) {
    $table->id();
    $table->string('user_id')->index();
    $table->string('session_id')->nullable()->index();
    $table->string('agent_type')->index();
    $table->string('role'); // 'user' or 'assistant'
    $table->longText('content');
    $table->json('metadata')->nullable();
    $table->timestamps();

    $table->index(['user_id', 'session_id', 'agent_type']);
});
```

**Usage**:

```php
use NeuronAI\Chat\History\EloquentChatHistory;

$chatHistory = new EloquentChatHistory(
    userId: auth()->id(),
    sessionId: $sessionId,
    agentType: 'training_advisor'
);

// Use with agent
$agent->withChatHistory($chatHistory);
```

**Features**:

- Automatic message persistence
- Session-based conversation tracking
- Agent-type scoping
- User association
- Metadata support for tool calls and structured data

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Provider Configuration Retrieval

*For any* configured AI provider, when credentials are requested, the system should retrieve them from the configuration system (which reads from environment variables).

**Validates: Requirements 2.5**

### Property 2: Invalid Provider Configuration Exceptions

*For any* provider configuration with missing or invalid credentials, the system should throw a descriptive exception indicating which configuration is invalid.

**Validates: Requirements 2.6**

### Property 3: Chat History Persistence

*For any* agent interaction, when a message is sent or received, the system should persist that message to the database with the correct user_id, agent_type, and session_id.

**Validates: Requirements 11.1**

### Property 4: Chat History Retrieval

*For any* continuing conversation, when previous messages exist in the database, the system should load and include them in the agent's context.

**Validates: Requirements 11.2**

### Property 5: Chat History User Association

*For any* persisted chat message, the message should be associated with a user account via user_id.

**Validates: Requirements 11.3**

### Property 6: Chat History Scoping

*For any* chat history query, when filtered by agent_type and session_id, the system should return only messages matching those criteria.

**Validates: Requirements 11.4**

### Property 7: Data Formatting for Agents

*For any* raw database model data, when formatted for agent consumption, the output should be a structured string or array that includes all relevant fields in an LLM-friendly format.

**Validates: Requirements 7.3**

### Property 8: Response Parsing

*For any* agent response, when parsed by a service class, the system should extract structured data matching the expected response schema.

**Validates: Requirements 7.4**

### Property 9: Structured Output Type Safety

*For any* structured output request, when the agent returns a response, the system should return an instance of the requested class type.

**Validates: Requirements 9.2**

### Property 10: Structured Output Validation

*For any* structured output response, when validation rules are defined, the system should validate the response against those rules and reject invalid responses.

**Validates: Requirements 9.3**

### Property 11: Tool Output Format

*For any* tool execution, the tool should return data in a format that is easily consumable by an LLM (structured arrays, descriptive strings, or JSON).

**Validates: Requirements 10.5**

### Property 12: Error Logging

*For any* AI-related error (provider failures, validation errors, tool errors), the system should log the error with sufficient context (user_id, agent_type, error message, stack trace).

**Validates: Requirements 12.3**

### Property 13: Configuration Environment Overrides

*For any* configuration value, when an environment variable is set, it should override the default configuration value.

**Validates: Requirements 15.4**

### Property 14: Streaming Response Chunks

*For any* streaming response, when chunks are yielded, each chunk should be a non-empty string that can be progressively displayed.

**Validates: Requirements 8.2**

## Error Handling

### Provider Errors

**Scenario**: AI provider is unavailable or returns an error

**Handling**:

1. Catch provider-specific exceptions
2. Log error with context (provider, model, user_id)
3. Return user-friendly error message
4. Optionally retry with exponential backoff

**Example**:

```php
try {
    $response = $agent->chat($message);
} catch (ProviderException $e) {
    Log::error('AI Provider Error', [
        'provider' => $agent->getProviderName(),
        'user_id' => $userId,
        'error' => $e->getMessage(),
    ]);

    return response()->json([
        'error' => 'AI service temporarily unavailable. Please try again.',
    ], 503);
}
```

### Validation Errors

**Scenario**: Structured output fails validation

**Handling**:

1. Neuron automatically retries up to configured maximum
2. If all retries fail, catch ValidationException
3. Log validation failure
4. Return fallback response or error

**Example**:

```php
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
        reasoning: 'Unable to generate detailed advice. Default recommendation based on character type.',
        expectedGains: [],
    );
}
```

### Rate Limiting

**Scenario**: Provider rate limits are exceeded

**Handling**:

1. Detect rate limit errors from provider
2. Queue request for later processing
3. Notify user of delay
4. Implement exponential backoff

**Example**:

```php
try {
    $response = $agent->chat($message);
} catch (RateLimitException $e) {
    // Queue for later processing
    ProcessAgentRequest::dispatch($userId, $agentType, $message)
        ->delay(now()->addMinutes(5));

    return response()->json([
        'message' => 'Request queued due to high demand. You will be notified when complete.',
    ], 202);
}
```

### Tool Execution Errors

**Scenario**: Tool fails during execution

**Handling**:

1. Catch exceptions in tool callable
2. Return error message to agent
3. Agent can handle error or ask user for clarification
4. Log tool error

**Example**:

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
            return "Error: Character with ID {$character_id} not found.";
        }
    });
```

## Testing Strategy

### Unit Testing

**Focus**: Individual components in isolation

**Test Cases**:

1. **Configuration Tests**
   - Verify config file structure
   - Test environment variable overrides
   - Validate provider configuration loading

2. **Tool Tests**
   - Test tool registration
   - Verify tool callable execution
   - Test tool parameter validation
   - Verify tool output format

3. **Service Tests**
   - Test data formatting methods
   - Test response parsing methods
   - Verify dependency injection

4. **Model Tests**
   - Test chat history relationships
   - Test query scopes
   - Verify data persistence

### Property-Based Testing

**Focus**: Universal properties across all inputs

**Configuration**:

- Minimum 100 iterations per test
- Use Pest with appropriate data generators
- Tag tests with feature and property references

**Property Tests**:

1. **Property Test 1: Provider Configuration Retrieval**
   - Generate random provider names from config
   - Verify credentials are retrieved correctly
   - **Feature: neuron-ai-integration, Property 1: Provider Configuration Retrieval**

2. **Property Test 2: Invalid Provider Configuration Exceptions**
   - Generate invalid configurations (missing keys, wrong types)
   - Verify exceptions are thrown
   - **Feature: neuron-ai-integration, Property 2: Invalid Provider Configuration Exceptions**

3. **Property Test 3: Chat History Persistence**
   - Generate random messages with user_id, agent_type, session_id
   - Verify messages are saved to database
   - **Feature: neuron-ai-integration, Property 3: Chat History Persistence**

4. **Property Test 4: Chat History Retrieval**
   - Create random chat histories
   - Verify retrieval includes all previous messages
   - **Feature: neuron-ai-integration, Property 4: Chat History Retrieval**

5. **Property Test 5: Chat History User Association**
   - Generate random chat messages
   - Verify each has valid user_id
   - **Feature: neuron-ai-integration, Property 5: Chat History User Association**

6. **Property Test 6: Chat History Scoping**
   - Create mixed chat histories with different agent_types and session_ids
   - Verify filtering returns correct subset
   - **Feature: neuron-ai-integration, Property 6: Chat History Scoping**

7. **Property Test 7: Data Formatting for Agents**
   - Generate random model data
   - Verify formatted output is LLM-friendly
   - **Feature: neuron-ai-integration, Property 7: Data Formatting for Agents**

8. **Property Test 8: Response Parsing**
   - Generate random agent responses
   - Verify parsing extracts expected structure
   - **Feature: neuron-ai-integration, Property 8: Response Parsing**

9. **Property Test 9: Structured Output Type Safety**
   - Request structured output with random data
   - Verify return type matches requested class
   - **Feature: neuron-ai-integration, Property 9: Structured Output Type Safety**

10. **Property Test 10: Structured Output Validation**
    - Generate invalid structured responses
    - Verify validation catches errors
    - **Feature: neuron-ai-integration, Property 10: Structured Output Validation**

11. **Property Test 11: Tool Output Format**
    - Execute tools with random inputs
    - Verify outputs are LLM-consumable
    - **Feature: neuron-ai-integration, Property 11: Tool Output Format**

12. **Property Test 12: Error Logging**
    - Trigger random AI errors
    - Verify errors are logged with context
    - **Feature: neuron-ai-integration, Property 12: Error Logging**

13. **Property Test 13: Configuration Environment Overrides**
    - Set random environment variables
    - Verify config values are overridden
    - **Feature: neuron-ai-integration, Property 13: Configuration Environment Overrides**

14. **Property Test 14: Streaming Response Chunks**
    - Stream random responses
    - Verify chunks are non-empty strings
    - **Feature: neuron-ai-integration, Property 14: Streaming Response Chunks**

### Feature Testing

**Focus**: Integration between components

**Test Cases**:

1. **Agent Service Integration**
   - Test controller → service → agent flow
   - Verify data flows correctly through layers
   - Mock LLM responses

2. **Streaming Responses**
   - Test SSE endpoint
   - Verify chunks are sent progressively
   - Test error handling during streaming

3. **Tool Integration**
   - Test agent tool calls
   - Verify tools access correct data
   - Test tool error handling

4. **Chat History Flow**
   - Test conversation continuity
   - Verify history is loaded and saved
   - Test history scoping

### Mocking Strategy

**LLM Provider Mocking**:

```php
// Mock Anthropic provider
$mockProvider = Mockery::mock(Anthropic::class);
$mockProvider->shouldReceive('chat')
    ->andReturn(new AssistantMessage('Mocked response'));

// Inject into agent
$agent = new TrainingAdvisorAgent();
$agent->setProvider($mockProvider);
```

**Database Mocking**:

- Use factories for model creation
- Use in-memory SQLite for fast tests
- Reset database between tests

### Test Organization

```
tests/
├── Unit/
│   ├── Neuron/
│   │   ├── BaseAgentTest.php
│   │   ├── Tools/
│   │   │   ├── CharacterStatsToolTest.php
│   │   │   └── SkillDataToolTest.php
│   │   └── Responses/
│   │       └── TrainingAdviceResponseTest.php
│   └── Services/
│       └── Neuron/
│           └── TrainingAdvisorServiceTest.php
├── Feature/
│   ├── Neuron/
│   │   ├── TrainingAdvisorAgentTest.php
│   │   ├── RaceStrategyAgentTest.php
│   │   ├── SkillRecommendationAgentTest.php
│   │   └── ChatHistoryTest.php
│   └── Http/
│       └── Controllers/
│           └── AgentControllerTest.php
└── Property/
    └── Neuron/
        ├── ConfigurationPropertiesTest.php
        ├── ChatHistoryPropertiesTest.php
        ├── DataFormattingPropertiesTest.php
        └── StructuredOutputPropertiesTest.php
```
