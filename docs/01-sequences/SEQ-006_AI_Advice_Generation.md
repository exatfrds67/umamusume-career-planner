# SEQ-006: AI Advice Generation

## Umamusume Pretty Derby Career Planner

**Document Version**: 2.2.0  
**Date**: January 28, 2026  
**Related Documents**: [PRD-006], [SPEC-006], [FLOW-006], [TECH-FLOW-006]

---

## Table of Contents

1. [Overview](#1-overview)
2. [Participants](#2-participants)
3. [Sequence Flow](#3-sequence-flow)
4. [Detailed Interactions](#4-detailed-interactions)
5. [Data Structures](#5-data-structures)
6. [Error Handling](#6-error-handling)
7. [Performance Considerations](#7-performance-considerations)
8. [Related Documentation](#8-related-documentation)

---

## 1. Overview

### 1.1 Purpose

This sequence diagram documents the AI advice generation workflow in the Umamusume Career Planner application, covering the hybrid AI architecture with local Ollama and cloud AWS Bedrock fallback, Neuron AI agent orchestration, and MCP tool integration.

### 1.2 Scope

**Covers:**

- User query submission and intent analysis
- Complexity-based provider routing (Ollama vs Bedrock)
- Neuron AI agent execution with specialized tools
- MCP server integration for context and resources
- Context building and prompt construction
- Response generation and confidence scoring
- Cost tracking and usage monitoring
- Conversation history management

**Related Artifacts:**

- PRD: [PRD-006](../prds/PRD-006_AI_Advisory.md)
- SPEC: [SPEC-006](../specs/SPEC-006_AI_Advisory_Technical.md)
- Flow: [FLOW-006](../flows/FLOW-006_AI_Advisory_System.md)
- Tech Flow: [TECH-FLOW-006](../tech-flow/TECH-FLOW-006_AI_Advisory_Flow.md)
- Wireframe: [WF-012](../wireframes/WF-012_AI_Advisor_Interface.md)
- User Flow: [UF-007](../user-flows/UF-007_AI_Advisor_Journey.md)

### 1.3 Business Context

The AI advisory system provides intelligent recommendations across all planning aspects:

- Training optimization based on current stats and goals
- Race strategy recommendations with win probability analysis
- Skill acquisition planning with SP budget optimization
- Career-wide strategic guidance for long-term progression

**Success Criteria:**

- Response generated within configured timeout (2.5s for Ollama, 4s for Bedrock)
- Confidence scores accurately reflect recommendation quality
- Cost tracking maintained for cloud AI usage
- Conversation context preserved across sessions
- Fallback mechanisms ensure availability

---

## 2. Participants

### 2.1 System Components

| Component | Type | Responsibility |
| --- | --- | --- |
| **User** | Actor | Submits AI queries and reviews recommendations |
| **Livewire Component** | Presentation | `AIAdvisorChat.php` - Chat interface |
| **AIAdvisoryController** | Application | Orchestrates AI request workflow |
| **AIAdvisoryService** | Domain Service | Main AI orchestration service |
| **AIRouterService** | Domain Service | Provider selection logic |
| **OllamaService** | Infrastructure | Local AI inference |
| **BedrockService** | Infrastructure | Cloud AI fallback |
| **NeuronOrchestrator** | AI Framework | Agent execution and coordination |
| **MCPClientService** | Integration | MCP server communication |
| **ContextBuilder** | Domain Service | Prompt context preparation |
| **CostTracker** | Domain Service | Usage and cost monitoring |
| **Database** | Infrastructure | MySQL/MariaDB persistence |
| **Cache** | Infrastructure | Redis conversation cache |

### 2.2 Component Locations

```text

app/
├── Livewire/
│   └── AI/
│       ├── AIAdvisorChat.php
│       └── RecommendationPanel.php
├── Http/
│   └── Controllers/
│       └── AIAdvisoryController.php
├── Services/
│   ├── AI/
│   │   ├── AIAdvisoryService.php
│   │   ├── AIRouterService.php
│   │   ├── OllamaService.php
│   │   ├── BedrockService.php
│   │   ├── ContextBuilder.php
│   │   └── CostTracker.php
│   └── MCP/
│       ├── MCPClientService.php
│       └── MCPMonitoringService.php
├── Neuron/
│   ├── Agents/
│   │   ├── TrainingAdvisorAgent.php
│   │   ├── RaceStrategyAgent.php
│   │   ├── SkillAdvisorAgent.php
│   │   └── CareerPlanningAgent.php
│   └── Tools/
│       ├── CharacterStatsTool.php
│       ├── TrainingPredictionTool.php
│       ├── RaceAnalysisTool.php
│       └── SkillRecommendationTool.php
└── Models/
    ├── AIConversation.php
    ├── AIRecommendation.php
    └── MCPToolUsage.php

```

---

## 3. Sequence Flow

### 3.1 High-Level Flow Diagram

```mermaid
sequenceDiagram
    actor User
    participant UI as Livewire Chat
    participant Controller as AIAdvisoryController
    participant AdvisorySvc as AIAdvisoryService
    participant Router as AIRouterService
    participant ContextBuilder
    participant Ollama as OllamaService
    participant Bedrock as BedrockService
    participant Neuron as NeuronOrchestrator
    participant MCP as MCPClientService
    participant CostTracker
    participant DB as Database
    participant Cache as Redis Cache

    User->>UI: Submit AI Query
    UI->>UI: Show "Thinking..." indicator
    UI->>Controller: POST /api/ai/advice
    Controller->>Controller: Authorize user
    Controller->>AdvisorySvc: getAdvice(query, context)
    
    AdvisorySvc->>DB: Load conversation history
    DB-->>AdvisorySvc: Recent messages
    
    AdvisorySvc->>ContextBuilder: buildContext(query, history, character)
    ContextBuilder->>DB: Load character stats
    ContextBuilder->>DB: Load active goals
    ContextBuilder->>DB: Load upcoming races
    DB-->>ContextBuilder: Context data
    ContextBuilder-->>AdvisorySvc: Context object
    
    AdvisorySvc->>Router: selectProvider(query, context)
    Router->>Router: Assess query complexity
    Router->>Router: Check Ollama availability
    
    alt Simple Query & Ollama Available
        Router-->>AdvisorySvc: Use Ollama
        AdvisorySvc->>Ollama: generate(prompt, systemPrompt)
        Ollama->>Ollama: Call local model
        Ollama-->>AdvisorySvc: Response (no cost)
        Note over AdvisorySvc,Ollama: Cost: $0.00
    else Complex Query OR Ollama Unavailable
        Router-->>AdvisorySvc: Use Bedrock
        AdvisorySvc->>Bedrock: generate(prompt, systemPrompt)
        Bedrock->>Bedrock: Call AWS Claude API
        Bedrock-->>AdvisorySvc: Response + token count
        AdvisorySvc->>CostTracker: recordUsage(tokens, model)
        CostTracker->>DB: INSERT ai_usage_logs
        Note over AdvisorySvc,Bedrock: Cost: $0.003 - $0.015
    else Specialized Agent Required
        Router-->>AdvisorySvc: Use Neuron Agent
        AdvisorySvc->>Neuron: executeAgent(agentType, context)
        
        Neuron->>MCP: Request tool execution
        MCP->>MCP: Validate tool permissions
        MCP->>MCP: Execute MCP tool
        MCP-->>Neuron: Tool result
        
        Neuron->>Neuron: Process with agent logic
        Neuron->>Router: Call provider (Ollama/Bedrock)
        Router-->>Neuron: AI response
        Neuron-->>AdvisorySvc: Agent recommendation
    end
    
    AdvisorySvc->>AdvisorySvc: Parse and format response
    AdvisorySvc->>AdvisorySvc: Calculate confidence score
    
    AdvisorySvc->>DB: INSERT ai_conversations
    AdvisorySvc->>DB: INSERT ai_recommendations
    AdvisorySvc->>Cache: Store conversation state
    
    AdvisorySvc-->>Controller: AIAdvice object
    Controller-->>UI: JSON response
    UI->>UI: Render recommendation
    UI->>UI: Display confidence badge
    UI-->>User: Show AI advice + actions
```text

### 3.2 Timeline Breakdown

| Phase | Duration | Description |
| --- | --- | --- |
| **User Input** | Variable | User types query |
| **Context Loading** | ~100ms | Load conversation history and character data |
| **Context Building** | ~200ms | Prepare prompt with full context |
| **Provider Selection** | ~50ms | Analyze complexity and route |
| **AI Processing (Ollama)** | ~1.5-2.5s | Local inference |
| **AI Processing (Bedrock)** | ~2.5-4s | Cloud API call |
| **Agent Execution** | ~3-5s | With MCP tools |
| **Response Parsing** | ~100ms | Format and score response |
| **Database Persistence** | ~150ms | Save conversation and recommendation |
| **UI Update** | ~100ms | Render advice to user |
| **Total (Ollama)** | ~2.5s | Complete workflow |
| **Total (Bedrock)** | ~4s | Complete workflow |
| **Total (Agent)** | ~5s | Complete workflow with tools |

---

## 4. Detailed Interactions

### 4.1 Context Building Phase

**Request Flow:**

```
User Query → Controller → AIAdvisoryService → ContextBuilder
```text

**Context Builder Implementation:**

```php
// ContextBuilder.php
class ContextBuilder
{
    public function buildContext(
        string $query,
        Collection $conversationHistory,
        ?Career $career = null
    ): AIContext {
        $context = [
            'query' => $query,
            'conversation_history' => $this->formatHistory($conversationHistory),
        ];
        
        if ($career) {
            $context['character'] = [
                'name' => $career->character->name,
                'current_turn' => $career->current_turn,
                'career_stage' => $career->career_stage->value,
                'stats' => [
                    'speed' => $career->speed,
                    'stamina' => $career->stamina,
                    'power' => $career->power,
                    'guts' => $career->guts,
                    'wit' => $career->wit,
                ],
                'energy' => $career->energy,
                'mood' => $career->mood->value,
            ];
            
            $context['goals'] = $career->goals->map(fn($g) => [
                'type' => $g->type,
                'target' => $g->target_value,
                'progress' => $g->progress_percentage,
            ])->toArray();
            
            $context['upcoming_races'] = $career->character
                ->upcomingRaces()
                ->limit(3)
                ->get()
                ->map(fn($r) => [
                    'name' => $r->name,
                    'grade' => $r->grade,
                    'distance' => $r->distance,
                    'days_until' => $r->days_until,
                ])
                ->toArray();
        }
        
        return new AIContext($context);
    }
    
    private function formatHistory(Collection $history): array
    {
        return $history->map(fn($msg) => [
            'role' => $msg['role'],
            'content' => $msg['content'],
            'timestamp' => $msg['timestamp'],
        ])->toArray();
    }
}
```

### 4.2 Provider Selection Logic

**Router Service:**

```php
// AIRouterService.php
class AIRouterService
{
    public function selectProvider(string $query, AIContext $context): string
    {
        $complexity = $this->assessComplexity($query, $context);
        
        // Check user preferences
        if ($this->getUserPreference() === 'cloud_only') {
            return 'bedrock';
        }
        
        // Complexity threshold check
        if ($complexity <= config('ai.local_complexity_threshold', 70)) {
            if ($this->isOllamaAvailable()) {
                return 'ollama';
            }
        }
        
        // Default to cloud
        return 'bedrock';
    }
    
    private function assessComplexity(string $query, AIContext $context): int
    {
        $complexity = 50; // Base
        
        // Query length factor
        $wordCount = str_word_count($query);
        if ($wordCount > 50) {
            $complexity += 20;
        }
        
        // Context depth factor
        if ($context->hasCharacter()) {
            $complexity += 10;
        }
        if (count($context->getGoals()) > 3) {
            $complexity += 10;
        }
        
        // Topic complexity
        if (str_contains(strtolower($query), ['strategy', 'optimize', 'plan'])) {
            $complexity += 15;
        }
        
        return min(100, $complexity);
    }
    
    private function isOllamaAvailable(): bool
    {
        try {
            $response = Http::timeout(2)->get(config('ai.providers.ollama.base_url') . '/api/tags');
            return $response->successful();
        } catch (\Exception $e) {
            Log::warning('Ollama unavailable', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
```text

### 4.3 Ollama Service Integration

**Local AI Processing:**

```php
// OllamaService.php
class OllamaService implements AIProviderInterface
{
    private string $baseUrl;
    private string $model;
    
    public function __construct()
    {
        $this->baseUrl = config('ai.providers.ollama.base_url');
        $this->model = config('ai.providers.ollama.model');
    }
    
    public function generate(string $prompt, string $systemPrompt = ''): AIResponse
    {
        $response = Http::timeout(config('ai.providers.ollama.timeout', 30))
            ->post($this->baseUrl . '/api/generate', [
                'model' => $this->model,
                'prompt' => $prompt,
                'system' => $systemPrompt,
                'stream' => false,
                'options' => [
                    'temperature' => config('ai.providers.ollama.temperature', 0.7),
                ],
            ]);
        
        if (!$response->successful()) {
            throw new OllamaException('Ollama generation failed');
        }
        
        $data = $response->json();
        
        return new AIResponse([
            'content' => $data['response'],
            'model' => $this->model,
            'provider' => 'ollama',
            'confidence' => $this->calculateConfidence($data),
            'tokens' => [
                'input' => $data['prompt_eval_count'] ?? 0,
                'output' => $data['eval_count'] ?? 0,
            ],
            'cost' => 0.00, // Free local processing
        ]);
    }
    
    private function calculateConfidence(array $data): float
    {
        // Simple heuristic based on response length and model metrics
        $responseLength = strlen($data['response']);
        $confidence = min(95, 50 + ($responseLength / 10));
        
        return round($confidence, 2);
    }
}
```

### 4.4 Bedrock Service Integration

**Cloud AI Fallback:**

```php
// BedrockService.php
class BedrockService implements AIProviderInterface
{
    private BedrockRuntimeClient $client;
    private string $model;
    
    public function __construct()
    {
        $this->client = new BedrockRuntimeClient([
            'region' => config('ai.providers.bedrock.region'),
            'version' => 'latest',
        ]);
        
        $this->model = config('ai.providers.bedrock.model');
    }
    
    public function generate(string $prompt, string $systemPrompt = ''): AIResponse
    {
        $messages = [
            ['role' => 'user', 'content' => $prompt],
        ];
        
        if ($systemPrompt) {
            array_unshift($messages, [
                'role' => 'system',
                'content' => $systemPrompt,
            ]);
        }
        
        try {
            $response = $this->client->invokeModel([
                'modelId' => $this->model,
                'contentType' => 'application/json',
                'accept' => 'application/json',
                'body' => json_encode([
                    'anthropic_version' => 'bedrock-2023-05-31',
                    'max_tokens' => config('ai.providers.bedrock.max_tokens', 4096),
                    'messages' => $messages,
                ]),
            ]);
            
            $result = json_decode($response['body']->getContents(), true);
            
            $inputTokens = $result['usage']['input_tokens'];
            $outputTokens = $result['usage']['output_tokens'];
            
            return new AIResponse([
                'content' => $result['content'][0]['text'],
                'model' => $this->model,
                'provider' => 'bedrock',
                'confidence' => $this->extractConfidence($result),
                'tokens' => [
                    'input' => $inputTokens,
                    'output' => $outputTokens,
                ],
                'cost' => $this->calculateCost($inputTokens, $outputTokens),
            ]);
        } catch (BedrockException $e) {
            Log::error('Bedrock API error', ['error' => $e->getMessage()]);
            throw new AIProviderException('Bedrock generation failed: ' . $e->getMessage());
        }
    }
    
    private function calculateCost(int $inputTokens, int $outputTokens): float
    {
        // Claude 3.5 Sonnet pricing
        $inputCostPer1M = 3.00;
        $outputCostPer1M = 15.00;
        
        $inputCost = ($inputTokens / 1_000_000) * $inputCostPer1M;
        $outputCost = ($outputTokens / 1_000_000) * $outputCostPer1M;
        
        return round($inputCost + $outputCost, 6);
    }
    
    private function extractConfidence(array $result): float
    {
        // Claude provides stop_reason which can indicate confidence
        $stopReason = $result['stop_reason'] ?? '';
        
        return match ($stopReason) {
            'end_turn' => 90.0,
            'max_tokens' => 75.0,
            default => 80.0,
        };
    }
}
```text

### 4.5 Neuron Agent Execution

**Specialized Agent Processing:**

```php
// NeuronOrchestrator.php
class NeuronOrchestrator
{
    public function executeAgent(string $agentType, AIContext $context): AgentResponse
    {
        $agent = $this->resolveAgent($agentType);
        
        // Load agent configuration
        $config = config("neuron.agents.{$agentType}");
        
        // Execute agent with tools
        $result = $agent->execute([
            'context' => $context->toArray(),
            'tools' => $config['tools'] ?? [],
            'max_iterations' => $config['max_iterations'] ?? 3,
        ]);
        
        return new AgentResponse([
            'recommendation' => $result['recommendation'],
            'reasoning' => $result['reasoning'],
            'confidence' => $result['confidence'],
            'tools_used' => $result['tools_used'],
        ]);
    }
    
    private function resolveAgent(string $type): Agent
    {
        return match ($type) {
            'training' => app(TrainingAdvisorAgent::class),
            'race' => app(RaceStrategyAgent::class),
            'skill' => app(SkillAdvisorAgent::class),
            'career' => app(CareerPlanningAgent::class),
            default => throw new \InvalidArgumentException("Unknown agent type: {$type}"),
        };
    }
}
```

### 4.6 MCP Tool Integration

**Tool Execution via MCP:**

```php
// MCPClientService.php
class MCPClientService
{
    public function executeTool(string $toolName, array $params): mixed
    {
        $server = $this->getServerForTool($toolName);
        
        if (!$server) {
            throw new MCPException("No server found for tool: {$toolName}");
        }
        
        // Check tool permissions
        if (!$this->hasPermission($toolName)) {
            throw new MCPException("Permission denied for tool: {$toolName}");
        }
        
        try {
            $result = $server->callTool($toolName, $params);
            
            // Log usage
            $this->logToolUsage($toolName, $server->getName(), true);
            
            return $result;
        } catch (\Exception $e) {
            $this->logToolUsage($toolName, $server->getName(), false);
            throw new MCPException("Tool execution failed: {$e->getMessage()}");
        }
    }
    
    private function logToolUsage(string $toolName, string $serverName, bool $success): void
    {
        MCPToolUsage::updateOrCreate(
            [
                'tool_name' => $toolName,
                'server_name' => $serverName,
                'usage_date' => now()->toDateString(),
            ],
            [
                'invocation_count' => DB::raw('invocation_count + 1'),
                'success_count' => $success ? DB::raw('success_count + 1') : DB::raw('success_count'),
                'error_count' => !$success ? DB::raw('error_count + 1') : DB::raw('error_count'),
            ]
        );
    }
}
```text

---

## 5. Data Structures

### 5.1 AI Advisory Request

```json
{
  "query": "What should I train next to prepare for the upcoming G1 race?",
  "context": {
    "career_id": 157,
    "topic": "training"
  },
  "provider_preference": "auto"
}
```

### 5.2 Context Object

```json
{
  "query": "What should I train next to prepare for the upcoming G1 race?",
  "conversation_history": [
    {
      "role": "user",
      "content": "How is my character doing?",
      "timestamp": "2026-01-24T09:00:00Z"
    },
    {
      "role": "assistant",
      "content": "Your character is progressing well...",
      "timestamp": "2026-01-24T09:00:03Z"
    }
  ],
  "character": {
    "name": "Special Week",
    "current_turn": 45,
    "career_stage": "senior",
    "stats": {
      "speed": 850,
      "stamina": 720,
      "power": 680,
      "guts": 550,
      "wit": 620
    },
    "energy": 78,
    "mood": "good"
  },
  "goals": [
    {
      "type": "stat_target",
      "target": 1000,
      "progress": 85
    },
    {
      "type": "race_win",
      "target": "G1 Race",
      "progress": 0
    }
  ],
  "upcoming_races": [
    {
      "name": "Kanto Okami Cup",
      "grade": "G1",
      "distance": 2400,
      "days_until": 8
    }
  ]
}
```text

### 5.3 AI Response Object

```json
{
  "content": "Based on your upcoming G1 race in 8 days, I recommend focusing on Speed training for the next 2-3 turns. Here's why:\n\n1. Your current Speed (850) is strong but needs ~150 more to be competitive in G1\n2. Your Stamina (720) is adequate for the 2400m distance\n3. Training Speed will also improve your running style effectiveness\n\nRisk Assessment: Your energy (78%) and mood (Good) are both optimal for intensive training. Consider one rest turn before the race to ensure peak condition.\n\nAlternative: If you want to be more conservative, alternate Speed and Stamina training to maintain balance.",
  "model": "llama3.2",
  "provider": "ollama",
  "confidence": 87.5,
  "tokens": {
    "input": 450,
    "output": 180
  },
  "cost": 0.00,
  "reasoning": [
    "Speed stat gap analysis",
    "Race distance requirements",
    "Energy and mood optimization"
  ]
}
```

### 5.4 AI Conversation Record

```json
{
  "id": 42,
  "user_id": 1,
  "context_type": "training",
  "context_id": 157,
  "messages": [
    {
      "role": "user",
      "content": "What should I train next?",
      "timestamp": "2026-01-24T10:00:00Z"
    },
    {
      "role": "assistant",
      "content": "Based on your upcoming G1 race...",
      "timestamp": "2026-01-24T10:00:03Z"
    }
  ],
  "model_used": "llama3.2",
  "provider": "ollama",
  "token_count": 630,
  "cost_usd": 0.00,
  "created_at": "2026-01-24T10:00:00Z"
}
```text

### 5.5 Agent Response

```json
{
  "recommendation": "Prioritize Speed training with secondary focus on Power",
  "reasoning": "Analysis of race requirements shows Speed gap of 150 points. Power training provides secondary benefits for acceleration in G1 competition.",
  "confidence": 92.0,
  "tools_used": [
    {
      "tool": "CharacterStatsTool",
      "result": "Current stats loaded"
    },
    {
      "tool": "RaceAnalysisTool",
      "result": "Stat requirements calculated"
    },
    {
      "tool": "TrainingPredictionTool",
      "result": "Gain projections generated"
    }
  ],
  "alternatives": [
    {
      "option": "Stamina focus",
      "confidence": 78.0,
      "reasoning": "Conservative approach for endurance"
    }
  ]
}
```

---

## 6. Error Handling

### 6.1 Validation Errors

| Error Code | Condition | HTTP Status | User Message |
| --- | --- | --- | --- |
| `AI_001` | Empty query | 422 | "Please provide a question or request" |
| `AI_002` | Query too long | 422 | "Query exceeds maximum length (2000 characters)" |
| `AI_003` | Invalid context | 422 | "Invalid context provided" |
| `AI_004` | Career not found | 404 | "Career run not found" |
| `AI_005` | Rate limit exceeded | 429 | "Too many AI requests. Please wait." |

### 6.2 Provider Error Handling

```mermaid
sequenceDiagram
    participant Service as AIAdvisoryService
    participant Ollama
    participant Bedrock
    participant Cache
    
    Service->>Ollama: Try Ollama first
    
    alt Ollama Success
        Ollama-->>Service: Response
        Service-->>Service: Return result
    else Ollama Timeout
        Ollama-->>Service: Timeout error
        Service->>Bedrock: Fallback to Bedrock
        
        alt Bedrock Success
            Bedrock-->>Service: Response
            Service-->>Service: Return result
        else Bedrock Error
            Bedrock-->>Service: API error
            Service->>Cache: Check cached response
            
            alt Cache Hit
                Cache-->>Service: Cached response
                Service-->>Service: Return cached (stale)
            else Cache Miss
                Service-->>Service: Return generic error
            end
        end
    end
```text

### 6.3 Error Recovery Strategies

| Error Type | Recovery Strategy |
| --- | --- |
| Ollama unavailable | Automatic fallback to Bedrock |
| Bedrock API error | Return cached response if available |
| MCP tool failure | Skip tool, continue with available data |
| Cost limit exceeded | Fallback to Ollama or cached responses |
| Network timeout | Retry with exponential backoff (3 attempts) |

---

## 7. Performance Considerations

### 7.1 Performance Metrics

| Operation | Target | Current | Status |
| --- | --- | --- | --- |
| Context building | <200ms | ~150ms | ✅ Met |
| Ollama response | <2.5s | ~2.2s | ✅ Met |
| Bedrock response | <4s | ~3.8s | ✅ Met |
| Agent execution | <5s | ~4.5s | ✅ Met |
| Database save | <150ms | ~120ms | ✅ Met |
| Total (Ollama) | <3s | ~2.7s | ✅ Met |
| Total (Bedrock) | <5s | ~4.5s | ✅ Met |

### 7.2 Optimization Strategies

**Implemented:**

- Conversation history limited to last 10 messages
- Context caching for repeated queries (5-minute TTL)
- Async database writes for conversation logs
- Response streaming for long-form advice (future)

**Code Example:**

```php
// Optimized context loading with caching
$cacheKey = "ai_context:{$career->id}";

$context = $this->cache->remember($cacheKey, 300, function () use ($career) {
    return $this->contextBuilder->buildContext($query, $history, $career);
});
```

### 7.3 Cost Optimization

**Provider Selection Logic:**

```php
// Cost-aware routing
if ($this->getUserCostLimit() !== null) {
    $currentSpend = $this->costTracker->getMonthlySpend($user->id);
    
    if ($currentSpend >= $user->ai_cost_limit) {
        // Force local-only when budget exceeded
        return 'ollama';
    }
}
```text

**Cost Tracking:**

| Provider | Input Cost/1M | Output Cost/1M | Avg Query Cost |
| --- | --- | --- | --- |
| Ollama | $0.00 | $0.00 | $0.00 |
| Claude 3.5 Sonnet | $3.00 | $15.00 | $0.003-$0.015 |
| Claude 3 Haiku | $1.00 | $5.00 | $0.001-$0.005 |

### 7.4 Cache Strategy

**Cache Keys:**

- Context: `ai_context:{career_id}`
- Conversation: `ai_conversation:{user_id}:{context_type}`
- TTL: 5 minutes for context, session-based for conversations

**Cache Invalidation:**

```php
// Invalidate on career update
$this->cache->forget("ai_context:{$career->id}");

// Invalidate on stat change
event(new StatsUpdated($career));
// Listener: InvalidateAICacheListener
```

---

## 8. Related Documentation

### 8.1 System Documentation

| Document | Description |
| --- | --- |
| [PRD-006](../prds/PRD-006_AI_Advisory.md) | Product requirements for AI advisory |
| [SPEC-006](../specs/SPEC-006_AI_Advisory_Technical.md) | Technical specification for AI system |
| [FLOW-006](../flows/FLOW-006_AI_Advisory_System.md) | System flow for AI operations |
| [TECH-FLOW-006](../tech-flow/TECH-FLOW-006_AI_Advisory_Flow.md) | Technical flow diagrams |

### 8.2 Related Sequences

| Sequence | Description |
| --- | --- |
| [SEQ-002](SEQ-002_Training_Block_Resolution.md) | Training predictions (AI-enhanced) |
| [SEQ-004](SEQ-004_Race_Registration_and_Outcome.md) | Race strategy (AI recommendations) |
| [SEQ-003](SEQ-003_Skill_Acquisition_and_Upgrade.md) | Skill planning (AI advisory) |

### 8.3 UI Documentation

| Document | Description |
| --- | --- |
| [WF-012](../wireframes/WF-012_AI_Advisor_Interface.md) | Wireframe specification for AI advisor |
| [UF-007](../user-flows/UF-007_AI_Advisor_Journey.md) | User flow for AI interaction |

### 8.4 Configuration Documentation

| Config File | Description |
| --- | --- |
| `config/ai.php` | AI provider configuration |
| `config/neuron.php` | Neuron agent configuration |
| `config/mcp.php` | MCP server configuration |
| `config/mcp_tools.php` | MCP tool permissions |

---

## Document Control

### Version History

| Version | Date | Author | Changes |
| --- | --- | --- | --- |
| 2.0.0 | 2026-01-24 | Development Team | Complete rewrite aligned with v2.0.0 implementation; added hybrid AI architecture, Neuron agents, MCP integration, detailed sequence flows, cost tracking, performance metrics, and aligned with current Laravel 12 architecture |
| 1.0.0 | 2026-01-14 | Development Team | Initial draft |

### Approval

| Role | Name | Signature | Date |
| --- | --- | --- | --- |
| Technical Lead | | | |
| QA Lead | | | |

### Review Schedule

- Next Review: 2026-04-24
- Review Frequency: Quarterly or on major feature changes

---

**Related Standards:**

- Laravel 12 Best Practices
- PSR-12 Coding Standards
- Mermaid Diagram Standards
- IEEE 830 SRS Format
- OpenAI API Best Practices
- AWS Bedrock Guidelines

---

*This sequence diagram reflects the current implementation of the AI advice generation workflow as of v2.0.0. For the most up-to-date information, refer to the source code in `app/Services/AI/AIAdvisoryService.php`, `app/Neuron/`, and related files.*
