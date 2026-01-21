# Software Design Specification (SDS)

## Umamusume Pretty Derby Career Planner

**Document Version**: 2.0
**Date**: January 14, 2026
**Project**: UmamusumeCareerPlanner
**Author**: Development Team
**Updated**: Aligned with spec requirements, MCP server integration, and modern architecture

---

## Table of Contents

1. [Introduction](#1-introduction)
2. [System Architecture](#2-system-architecture)
3. [AI Integration Architecture](#4-ai-integration-architecture)
4. [Database Design](#5-database-design)
5. [User Interface Design](#6-user-interface-design)
6. [API Design](#7-api-design)

---

## 1. Introduction

### 1.1 Purpose

This Software Design Specification (SDS) document provides detailed technical design for the **Umamusume Pretty Derby Career Planner** system. It translates the requirements from the SRS into a comprehensive technical blueprint for implementation, covering architecture, database design, user interfaces, APIs, and implementation strategies.

### 1.2 Scope

The design covers all aspects of the system implementation:

- **System Architecture**: High-level structure and component relationships
- **Database Design**: Complete schema with 15+ tables and relationships
- **User Interface Design**: Responsive web interface with accessibility compliance
- **API Design**: RESTful endpoints and external integrations
- **Security Design**: Authentication, authorization, and data protection
- **Performance Design**: Optimization strategies and scalability considerations

### 1.3 Design Principles

#### 1.3.1 Local-First Architecture

- All personal data stored locally with MySQL database
- Optional cloud integration for AI processing and community data
- Privacy-focused design with user-controlled data sharing

#### 1.3.2 Hybrid AI Processing

- Ollama local models as primary inference engine
- AWS Bedrock cloud fallback for complex processing
- Intelligent routing based on complexity and performance

#### 1.3.3 Performance Optimization

- Redis caching for frequently accessed data
- Database optimization with proper indexing
- Lazy loading and progressive enhancement

#### 1.3.4 Accessibility First

- WCAG 2.2 AA compliance throughout the application
- Keyboard navigation and screen reader support
- Responsive design for all device types

### 1.4 Technology Stack

#### 1.4.1 Verified Technologies (January 11, 2026)

- **Framework**: Laravel 12 (Released February 24, 2025) with strict mode and asynchronous caching
- **Frontend**: Tailwind CSS v4 (Released January 22, 2025) with zero configuration and 5x faster builds
- **AI Integration**:
  - **Local**: cloudstudio/ollama-laravel (verified Laravel 12 compatible) with Llama 3.3, Mistral, Qwen 2.5
  - **Cloud**: AWS Bedrock via MCP servers (Claude 4.5 series, Nova 2 series)
- **Database**: MySQL 8.0+ with Redis 7.0+ (WSL) for caching and queues
- **External APIs**:
  - **Primary**: umapyoi.net (verified active, replaces deprecated SimpleSandman/UmaMusumeAPI)
  - **Secondary**: UmamusumeDB.com (verification pending - community tools)
- **MCP Integration**:
  - **AI Services**: strands-agents, agentcore-mcp-server
  - **AWS Infrastructure**: awspricing, awsknowledge, awsapi, awslabs.aws-iac-mcp-server
  - **Utilities**: context7, fetch, figma (optional)

#### 1.4.2 Architecture Patterns

- **Repository Pattern**: Data access abstraction with interface-based design
- **Service Layer**: Business logic separation with dependency injection
- **CQRS**: Command/Query separation for complex operations
- **Event-Driven**: Laravel Events/Listeners for decoupled components
- **MCP Integration**: Model Context Protocol for enhanced AI and infrastructure capabilities

---

## 2. System Architecture

### 2.1 High-Level Architecture

```text
┌─────────────────────────────────────────────────────────────────┐
│                 PRESENTATION LAYER                              │
├─────────────────────────────────────────────────────────────────┤
│  Web Browser (Chrome 90+, Firefox 88+, Safari 14+, Edge 90+)   │
│  ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐   │
│  │   JavaScript    │ │   Tailwind CSS  │ │   Service       │   │
│  │   ES2024+       │ │   v4 Styling    │ │   Workers       │   │
│  │   Components    │ │   Responsive    │ │   PWA Features  │   │
│  └─────────────────┘ └─────────────────┘ └─────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                 APPLICATION LAYER                               │
├─────────────────────────────────────────────────────────────────┤
│  Laravel 12 Framework (PHP 8.3+)                               │
│  ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐   │
│  │   Controllers   │ │   Middleware    │ │   Services      │   │
│  │   (Single       │ │   (Auth, CORS,  │ │   (Business     │   │
│  │   Action)       │ │   Rate Limit)   │ │   Logic)        │   │
│  └─────────────────┘ └─────────────────┘ └─────────────────┘   │
│  ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐   │
│  │   Repositories  │ │   Events &      │ │   Queue Jobs    │   │
│  │   (Data Access) │ │   Listeners     │ │   (Background   │   │
│  │                 │ │                 │ │   Processing)   │   │
│  └─────────────────┘ └─────────────────┘ └─────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                 DATA LAYER                                      │
├─────────────────────────────────────────────────────────────────┤
│  ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐   │
│  │   MySQL 8.0+    │ │   Redis (WSL)   │ │   File Storage  │   │
│  │   (Primary      │ │   (Cache,       │ │   (Backups,     │   │
│  │   Database)     │ │   Sessions,     │ │   Uploads)      │   │
│  │                 │ │   Queues)       │ │                 │   │
│  └─────────────────┘ └─────────────────┘ └─────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                 EXTERNAL INTEGRATION LAYER                      │
├─────────────────────────────────────────────────────────────────┤
│  ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐   │
│  │   AI Services   │ │   Game APIs     │ │   OCR Services  │   │
│  │   Ollama Local  │ │   umapyoi.net   │ │   Tesseract +   │   │
│  │   AWS Bedrock   │ │   UmamusumeDB   │ │   OpenCV        │   │
│  │   (via MCP)     │ │   Community     │ │   Processing    │   │
│  └─────────────────┘ └─────────────────┘ └─────────────────┘   │
│  ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐   │
│  │   MCP Servers   │ │   Infrastructure│ │   Design System │   │
│  │   Bedrock MCP   │ │   AWS Tools     │ │   Figma MCP     │   │
│  │   Strands Agent │ │   Pricing API   │ │   (Optional)    │   │
│  │   AgentCore     │ │   Knowledge     │ │                 │   │
│  └─────────────────┘ └─────────────────┘ └─────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
```

### 2.4 MCP Server Integration Architecture

#### 2.4.1 Available MCP Servers

The system leverages Model Context Protocol (MCP) servers for enhanced functionality:

**AI and Agent Services**:

- **strands-agents**: Strands Agent SDK for building AI agents with Bedrock, Anthropic, OpenAI, Gemini, Llama models
- **agentcore-mcp-server**: Amazon Bedrock AgentCore agentic platform for agent development and deployment

**AWS Infrastructure Services**:

- **awspricing**: AWS pricing information and cost optimization
- **awsknowledge**: AWS service documentation and best practices
- **awsapi**: Direct AWS API access for infrastructure management
- **awslabs.aws-iac-mcp-server**: AWS infrastructure-as-code tools for CDK and CloudFormation

**Utility Services**:

- **context7**: Context management and data processing
- **fetch**: HTTP client for external API integration
- **figma**: Design system integration (optional for UI consistency)

#### 2.4.2 MCP Integration Patterns

```php
<?php

namespace App\Services\MCP;

/**
 * MCP Bedrock Service
 * Integrates with AWS Bedrock via MCP server for AI processing
 */
class MCPBedrockService
{
    public function __construct(
        private MCPClient $mcpClient,
        private CostTracker $costTracker,
        private Logger $logger
    ) {}

    public function generateRecommendation(Character $character, array $context): AIRecommendation
    {
        $prompt = $this->buildGameSpecificPrompt($character, $context);

        // Use MCP Bedrock server for processing
        $response = $this->mcpClient->call('bedrock', 'invoke_claude_sonnet', [
            'prompt' => $prompt,
            'maxTokens' => 2048
        ]);

        // Track costs for budget management
        $this->costTracker->recordUsage('claude-sonnet', $response['usage']);

        return new AIRecommendation(
            content: $response['content'],
            model: 'claude-sonnet-4.5',
            confidence: $this->calculateConfidence($response),
            cost: $response['cost']
        );
    }

    public function analyzeGameScreenshot(UploadedFile $screenshot): ScreenshotAnalysis
    {
        // Use Bedrock for OCR and game state analysis
        $response = $this->mcpClient->call('bedrock', 'analyze_image', [
            'image' => base64_encode($screenshot->getContent()),
            'prompt' => 'Extract Umamusume game state data from this screenshot'
        ]);

        return new ScreenshotAnalysis(
            extractedData: $response['extracted_data'],
            confidence: $response['confidence'],
            gameState: $this->parseGameState($response['extracted_data'])
        );
    }
}

/**
 * MCP Infrastructure Service
 * Manages AWS infrastructure via MCP servers
 */
class MCPInfrastructureService
{
    public function __construct(private MCPClient $mcpClient) {}

    public function getAWSPricing(string $service, string $region = 'us-east-1'): array
    {
        return $this->mcpClient->call('awspricing', 'get_pricing', [
            'service' => $service,
            'region' => $region
        ]);
    }

    public function validateInfrastructure(array $config): ValidationResult
    {
        return $this->mcpClient->call('awslabs.aws-iac-mcp-server', 'validate_template', [
            'template' => $config
        ]);
    }
}

/**
 * MCP Agent Service
 * Creates and manages AI agents via Strands Agent SDK
 */
class MCPAgentService
{
    public function __construct(private MCPClient $mcpClient) {}

    public function createTrainingAgent(Character $character): Agent
    {
        return $this->mcpClient->call('strands-agents', 'create_agent', [
            'type' => 'training_optimizer',
            'context' => [
                'character_id' => $character->id,
                'scenario' => $character->scenario->name,
                'goals' => $character->goals
            ],
            'model' => 'bedrock:claude-sonnet-4.5'
        ]);
    }
}
```

#### 2.4.3 MCP Configuration Management

```php
<?php

namespace App\Services\MCP;

class MCPConfigurationManager
{
    private array $serverConfigs = [
        'bedrock' => [
            'server' => 'agentcore-mcp-server',
            'capabilities' => ['text_generation', 'image_analysis', 'cost_tracking'],
            'models' => [
                'claude-opus-4.5' => ['cost_per_1k_input' => 5.0, 'cost_per_1k_output' => 25.0],
                'claude-sonnet-4.5' => ['cost_per_1k_input' => 3.0, 'cost_per_1k_output' => 15.0],
                'claude-haiku-4.5' => ['cost_per_1k_input' => 1.0, 'cost_per_1k_output' => 5.0],
                'nova-2-lite' => ['cost_per_1k_input' => 0.00125, 'cost_per_1k_output' => 0.00125]
            ]
        ],
        'aws_infrastructure' => [
            'servers' => ['awspricing', 'awsknowledge', 'awsapi', 'awslabs.aws-iac-mcp-server'],
            'capabilities' => ['pricing', 'documentation', 'api_access', 'infrastructure_validation']
        ],
        'agents' => [
            'server' => 'strands-agents',
            'capabilities' => ['agent_creation', 'workflow_management', 'multi_model_support']
        ]
    ];

    public function getServerConfig(string $service): array
    {
        return $this->serverConfigs[$service] ?? [];
    }

    public function isServerAvailable(string $server): bool
    {
        // Check if MCP server is configured and available
        return $this->mcpClient->ping($server);
    }
}
```

### 2.5 Hybrid AI Processing with MCP Integration

#### 2.5.1 Intelligent Routing Strategy

```php
<?php

namespace App\Services;

class HybridAIRouter
{
    public function __construct(
        private OllamaService $ollama,
        private MCPBedrockService $bedrockMCP,
        private MCPAgentService $agentMCP,
        private CostTracker $costTracker
    ) {}

    public function routeRequest(AIRequest $request): AIResponse
    {
        $complexity = $this->assessComplexity($request);
        $budget = $this->costTracker->getRemainingBudget();

        return match (true) {
            // Simple requests - use local Ollama
            $complexity <= 3 => $this->ollama->process($request),

            // Medium complexity - use cost-effective cloud model
            $complexity <= 7 && $budget > 0.01 => $this->bedrockMCP->processWithModel($request, 'nova-2-lite'),

            // High complexity - use advanced model if budget allows
            $complexity > 7 && $budget > 0.10 => $this->bedrockMCP->processWithModel($request, 'claude-sonnet-4.5'),

            // Complex agent tasks - use Strands Agent SDK
            $request->requiresAgent() => $this->agentMCP->processWithAgent($request),

            // Fallback to local processing
            default => $this->ollama->process($request)
        };
    }

    private function assessComplexity(AIRequest $request): int
    {
        $factors = [
            'context_length' => min(5, strlen($request->context) / 1000),
            'data_complexity' => count($request->data_points),
            'requires_reasoning' => $request->requiresReasoning() ? 3 : 0,
            'multi_turn' => $request->isMultiTurn() ? 2 : 0
        ];

        return array_sum($factors);
    }
}
```

---

## 4. AI Integration Architecture

### 4.1 Hybrid AI Processing System

The application implements a sophisticated hybrid AI system combining local Ollama models with cloud-based AWS Bedrock services via MCP integration, providing optimal balance between privacy, performance, and cost.

#### 4.1.1 AI Service Architecture

```php
<?php

namespace App\Services\AI;

/**
 * Central AI Service Coordinator
 * Manages hybrid AI processing with intelligent routing
 */
class AIServiceCoordinator
{
    public function __construct(
        private OllamaService $ollamaService,
        private MCPBedrockService $bedrockService,
        private MCPAgentService $agentService,
        private AIRoutingEngine $routingEngine,
        private CostManager $costManager,
        private PerformanceMonitor $performanceMonitor
    ) {}

    public function processRequest(AIRequest $request): AIResponse
    {
        // Analyze request complexity and requirements
        $analysis = $this->routingEngine->analyzeRequest($request);

        // Select optimal processing strategy
        $strategy = $this->routingEngine->selectStrategy($analysis);

        // Execute with performance monitoring
        $startTime = microtime(true);

        try {
            $response = match ($strategy->type) {
                'local' => $this->processLocally($request, $strategy),
                'cloud' => $this->processInCloud($request, $strategy),
                'agent' => $this->processWithAgent($request, $strategy),
                'hybrid' => $this->processHybrid($request, $strategy)
            };

            // Record performance metrics
            $this->performanceMonitor->recordExecution([
                'strategy' => $strategy->type,
                'duration' => microtime(true) - $startTime,
                'tokens' => $response->tokenCount,
                'cost' => $response->cost,
                'success' => true
            ]);

            return $response;

        } catch (AIProcessingException $e) {
            $this->handleProcessingError($e, $request, $strategy);
            throw $e;
        }
    }

    private function processLocally(AIRequest $request, ProcessingStrategy $strategy): AIResponse
    {
        return $this->ollamaService->process($request, [
            'model' => $strategy->model,
            'temperature' => $strategy->temperature,
            'max_tokens' => $strategy->maxTokens
        ]);
    }

    private function processInCloud(AIRequest $request, ProcessingStrategy $strategy): AIResponse
    {
        // Check budget constraints
        if (!$this->costManager->canAfford($strategy->estimatedCost)) {
            throw new InsufficientBudgetException();
        }

        return $this->bedrockService->process($request, [
            'model' => $strategy->model,
            'parameters' => $strategy->parameters
        ]);
    }

    private function processWithAgent(AIRequest $request, ProcessingStrategy $strategy): AIResponse
    {
        return $this->agentService->processWithAgent($request, [
            'agent_type' => $strategy->agentType,
            'capabilities' => $strategy->requiredCapabilities
        ]);
    }
}
```

#### 4.1.2 Intelligent Routing Engine

```php
<?php

namespace App\Services\AI;

class AIRoutingEngine
{
    private array $routingRules = [
        // Privacy-sensitive operations - always local
        'user_data_analysis' => ['strategy' => 'local', 'model' => 'llama3.3:70b'],
        'personal_recommendations' => ['strategy' => 'local', 'model' => 'mistral:7b'],

        // Complex reasoning - cloud with cost optimization
        'training_optimization' => ['strategy' => 'cloud', 'model' => 'claude-sonnet-4.5'],
        'race_strategy_analysis' => ['strategy' => 'cloud', 'model' => 'claude-haiku-4.5'],

        // Multi-step workflows - agent-based
        'career_planning' => ['strategy' => 'agent', 'agent_type' => 'career_planner'],
        'scenario_simulation' => ['strategy' => 'agent', 'agent_type' => 'simulator'],

        // Hybrid processing for balanced workloads
        'content_generation' => ['strategy' => 'hybrid']
    ];

    public function analyzeRequest(AIRequest $request): RequestAnalysis
    {
        return new RequestAnalysis([
            'complexity_score' => $this->calculateComplexity($request),
            'privacy_level' => $this->assessPrivacyRequirements($request),
            'performance_requirements' => $this->analyzePerformanceNeeds($request),
            'cost_sensitivity' => $this->evaluateCostConstraints($request),
            'required_capabilities' => $this->identifyRequiredCapabilities($request)
        ]);
    }

    public function selectStrategy(RequestAnalysis $analysis): ProcessingStrategy
    {
        // Apply routing rules based on request type
        if (isset($this->routingRules[$analysis->requestType])) {
            $baseStrategy = $this->routingRules[$analysis->requestType];
        } else {
            $baseStrategy = $this->determineOptimalStrategy($analysis);
        }

        // Adjust strategy based on current conditions
        return $this->optimizeStrategy($baseStrategy, $analysis);
    }

    private function calculateComplexity(AIRequest $request): int
    {
        $factors = [
            'context_length' => min(10, strlen($request->context) / 500),
            'data_points' => min(5, count($request->dataPoints) / 10),
            'reasoning_depth' => $request->requiresReasoning() ? 5 : 0,
            'multi_modal' => $request->hasImages() ? 3 : 0,
            'real_time' => $request->isRealTime() ? 2 : 0
        ];

        return array_sum($factors);
    }

    private function assessPrivacyRequirements(AIRequest $request): string
    {
        if ($request->containsPersonalData()) return 'high';
        if ($request->containsGameProgress()) return 'medium';
        return 'low';
    }
}
```

### 4.2 Local AI Processing with Ollama

#### 4.2.1 Ollama Service Implementation

```php
<?php

namespace App\Services\AI;

class OllamaService
{
    private array $availableModels = [
        'llama3.3:70b' => [
            'use_case' => 'complex_reasoning',
            'memory_requirement' => '40GB',
            'performance' => 'high'
        ],
        'llama3.3:8b' => [
            'use_case' => 'general_purpose',
            'memory_requirement' => '8GB',
            'performance' => 'medium'
        ],
        'mistral:7b' => [
            'use_case' => 'fast_responses',
            'memory_requirement' => '4GB',
            'performance' => 'fast'
        ],
        'qwen2.5:14b' => [
            'use_case' => 'multilingual',
            'memory_requirement' => '9GB',
            'performance' => 'medium'
        ]
    ];

    public function __construct(
        private HttpClient $httpClient,
        private ModelManager $modelManager,
        private Logger $logger
    ) {}

    public function process(AIRequest $request, array $options = []): AIResponse
    {
        $model = $options['model'] ?? $this->selectOptimalModel($request);

        // Ensure model is available
        $this->ensureModelAvailable($model);

        $payload = [
            'model' => $model,
            'prompt' => $this->buildPrompt($request),
            'options' => [
                'temperature' => $options['temperature'] ?? 0.7,
                'top_p' => $options['top_p'] ?? 0.9,
                'max_tokens' => $options['max_tokens'] ?? 2048
            ],
            'stream' => $request->isStreaming()
        ];

        $response = $this->httpClient->post('http://localhost:11434/api/generate', $payload);

        return new AIResponse([
            'content' => $response['response'],
            'model' => $model,
            'tokens_used' => $response['eval_count'] ?? 0,
            'processing_time' => $response['total_duration'] / 1000000, // Convert to ms
            'cost' => 0, // Local processing is free
            'provider' => 'ollama'
        ]);
    }

    private function selectOptimalModel(AIRequest $request): string
    {
        $complexity = $this->calculateComplexity($request);
        $memoryAvailable = $this->getAvailableMemory();

        return match (true) {
            $complexity > 8 && $memoryAvailable > 35 => 'llama3.3:70b',
            $complexity > 5 && $memoryAvailable > 8 => 'qwen2.5:14b',
            $request->requiresFastResponse() => 'mistral:7b',
            default => 'llama3.3:8b'
        };
    }

    private function ensureModelAvailable(string $model): void
    {
        if (!$this->modelManager->isModelPulled($model)) {
            $this->logger->info("Pulling Ollama model: {$model}");
            $this->modelManager->pullModel($model);
        }
    }
}
```

### 4.3 Cloud AI Processing via MCP Bedrock

#### 4.3.1 Advanced Bedrock Integration

```php
<?php

namespace App\Services\AI;

class MCPBedrockService
{
    private array $modelCapabilities = [
        'claude-opus-4.5' => [
            'max_tokens' => 200000,
            'strengths' => ['complex_reasoning', 'code_generation', 'analysis'],
            'cost_tier' => 'premium'
        ],
        'claude-sonnet-4.5' => [
            'max_tokens' => 200000,
            'strengths' => ['balanced_performance', 'general_purpose'],
            'cost_tier' => 'standard'
        ],
        'claude-haiku-4.5' => [
            'max_tokens' => 200000,
            'strengths' => ['speed', 'simple_tasks'],
            'cost_tier' => 'economy'
        ],
        'nova-2-lite' => [
            'max_tokens' => 300000,
            'strengths' => ['cost_effective', 'high_throughput'],
            'cost_tier' => 'budget'
        ]
    ];

    public function __construct(
        private MCPClient $mcpClient,
        private CostManager $costManager,
        private Logger $logger
    ) {}

    public function process(AIRequest $request, array $options = []): AIResponse
    {
        $model = $options['model'] ?? $this->selectOptimalModel($request);

        // Pre-flight cost check
        $estimatedCost = $this->estimateCost($request, $model);
        if (!$this->costManager->canAfford($estimatedCost)) {
            throw new InsufficientBudgetException("Estimated cost: ${estimatedCost}");
        }

        // Build MCP request
        $mcpRequest = $this->buildMCPRequest($request, $model, $options);

        try {
            $response = $this->mcpClient->call('bedrock', $this->getModelEndpoint($model), $mcpRequest);

            // Track actual costs
            $actualCost = $this->calculateActualCost($response, $model);
            $this->costManager->recordUsage($model, $actualCost, $response['usage']);

            return new AIResponse([
                'content' => $response['content'],
                'model' => $model,
                'tokens_used' => $response['usage']['total_tokens'],
                'processing_time' => $response['processing_time'],
                'cost' => $actualCost,
                'provider' => 'aws_bedrock',
                'confidence' => $response['confidence'] ?? null
            ]);

        } catch (MCPException $e) {
            $this->logger->error("Bedrock processing failed", [
                'model' => $model,
                'error' => $e->getMessage(),
                'request_id' => $request->getId()
            ]);
            throw new AIProcessingException("Cloud processing failed: " . $e->getMessage());
        }
    }

    private function selectOptimalModel(AIRequest $request): string
    {
        $budget = $this->costManager->getRemainingBudget();
        $complexity = $this->calculateComplexity($request);

        return match (true) {
            $complexity > 9 && $budget > 1.0 => 'claude-opus-4.5',
            $complexity > 6 && $budget > 0.1 => 'claude-sonnet-4.5',
            $complexity > 3 && $budget > 0.01 => 'claude-haiku-4.5',
            $budget > 0.001 => 'nova-2-lite',
            default => throw new InsufficientBudgetException()
        };
    }

    private function getModelEndpoint(string $model): string
    {
        return match ($model) {
            'claude-opus-4.5' => 'invoke_claude_opus',
            'claude-sonnet-4.5' => 'invoke_claude_sonnet',
            'claude-haiku-4.5' => 'invoke_claude_haiku',
            default => 'invoke_claude_sonnet'
        };
    }
}
```

### 4.4 Agent-Based Processing with Strands SDK

#### 4.4.1 MCP Agent Service Implementation

```php
<?php

namespace App\Services\AI;

class MCPAgentService
{
    private array $agentTypes = [
        'career_planner' => [
            'capabilities' => ['long_term_planning', 'goal_optimization', 'scenario_analysis'],
            'model_preference' => 'claude-sonnet-4.5'
        ],
        'training_optimizer' => [
            'capabilities' => ['stat_optimization', 'skill_analysis', 'efficiency_calculation'],
            'model_preference' => 'claude-haiku-4.5'
        ],
        'race_strategist' => [
            'capabilities' => ['tactical_analysis', 'competitor_assessment', 'real_time_decisions'],
            'model_preference' => 'claude-opus-4.5'
        ],
        'scenario_simulator' => [
            'capabilities' => ['outcome_prediction', 'probability_analysis', 'risk_assessment'],
            'model_preference' => 'nova-2-lite'
        ]
    ];

    public function processWithAgent(AIRequest $request, array $options = []): AIResponse
    {
        $agentType = $options['agent_type'] ?? $this->determineAgentType($request);
        $agentConfig = $this->agentTypes[$agentType];

        // Create or retrieve agent instance
        $agent = $this->getOrCreateAgent($agentType, $agentConfig);

        // Execute agent workflow
        $workflow = $this->buildAgentWorkflow($request, $agentConfig);
        $result = $this->mcpClient->call('strands-agents', 'execute_workflow', [
            'agent_id' => $agent->id,
            'workflow' => $workflow,
            'context' => $request->getContext()
        ]);

        return new AIResponse([
            'content' => $result['output'],
            'model' => $agentConfig['model_preference'],
            'agent_type' => $agentType,
            'workflow_steps' => $result['steps_executed'],
            'confidence' => $result['confidence'],
            'cost' => $result['cost'],
            'provider' => 'strands_agent'
        ]);
    }

    private function getOrCreateAgent(string $type, array $config): Agent
    {
        // Check if agent already exists for this session
        $existingAgent = $this->findExistingAgent($type);

        if ($existingAgent && $existingAgent->isActive()) {
            return $existingAgent;
        }

        // Create new agent via MCP
        $agentData = $this->mcpClient->call('strands-agents', 'create_agent', [
            'type' => $type,
            'capabilities' => $config['capabilities'],
            'model' => $config['model_preference'],
            'configuration' => [
                'max_iterations' => 10,
                'timeout' => 300,
                'memory_enabled' => true
            ]
        ]);

        return new Agent($agentData);
    }
}
```

### 4.5 Cost Management and Budget Control

#### 4.5.1 Comprehensive Cost Tracking

```php
<?php

namespace App\Services\AI;

class CostManager
{
    private array $budgetLimits = [
        'daily' => 10.00,
        'weekly' => 50.00,
        'monthly' => 200.00
    ];

    private array $modelCosts = [
        'claude-opus-4.5' => ['input' => 0.005, 'output' => 0.025],
        'claude-sonnet-4.5' => ['input' => 0.003, 'output' => 0.015],
        'claude-haiku-4.5' => ['input' => 0.001, 'output' => 0.005],
        'nova-2-lite' => ['input' => 0.00125, 'output' => 0.00125]
    ];

    public function canAfford(float $estimatedCost): bool
    {
        $currentUsage = $this->getCurrentUsage();

        return ($currentUsage['daily'] + $estimatedCost) <= $this->budgetLimits['daily'] &&
               ($currentUsage['weekly'] + $estimatedCost) <= $this->budgetLimits['weekly'] &&
               ($currentUsage['monthly'] + $estimatedCost) <= $this->budgetLimits['monthly'];
    }

    public function recordUsage(string $model, float $cost, array $usage): void
    {
        DB::table('ai_usage_logs')->insert([
            'model' => $model,
            'cost' => $cost,
            'input_tokens' => $usage['input_tokens'],
            'output_tokens' => $usage['output_tokens'],
            'total_tokens' => $usage['total_tokens'],
            'created_at' => now()
        ]);

        // Update cached usage statistics
        Cache::forget('ai_usage_current');

        // Check for budget alerts
        $this->checkBudgetAlerts();
    }

    private function checkBudgetAlerts(): void
    {
        $usage = $this->getCurrentUsage();

        foreach ($this->budgetLimits as $period => $limit) {
            $percentage = ($usage[$period] / $limit) * 100;

            if ($percentage >= 90) {
                event(new BudgetAlertTriggered($period, $percentage, $usage[$period], $limit));
            }
        }
    }
}
```

---

## 5. Database Design

### 5.1 Database Architecture Overview

The application employs a sophisticated multi-tier database architecture optimized for performance, scalability, and data integrity, supporting the complex requirements of the Umamusume Career Planner system.

#### 5.1.1 Database Technology Stack

```yaml
Primary Database:
  Development Default: SQLite (database.sqlite) - zero configuration
  Production: MySQL 8.0+ with InnoDB
  Storage: InnoDB with optimized configuration (production)
  Features: JSON columns, full-text search, partitioning
  Configuration: .env DB_CONNECTION setting (sqlite|mysql)

Caching Layer:
  Development: File-based cache (Laravel default)
  Production: Redis 7.0+ cluster (WSL-compatible)
  Session Storage: File-based (development) or Redis (production)
  Query Cache: Multi-level TTL strategy

Queue Processing:
  Development: Database driver (async via queue:work)
  Production: Redis driver (WSL-compatible cluster)
  Configuration: .env QUEUE_CONNECTION setting (database|redis)

ORM Framework:
  Laravel Eloquent: Strict mode enabled
  Connection Pooling: Optimized for high concurrency
  Query Builder: Performance-optimized queries

Search Engine:
  Primary: MySQL Full-Text Search (production)
  Development: Database queries with LIKE clauses
  Future: Elasticsearch integration for advanced search
```

**Configuration Guide**:

- **Development (XAMPP Default)**:
  - Database: SQLite (`database.sqlite`)
  - Cache: File-based
  - Queue: Database driver
  - Set via `.env`: `DB_CONNECTION=sqlite`, `CACHE_DRIVER=file`, `QUEUE_CONNECTION=database`

- **Production (MySQL + Redis)**:
  - Database: MySQL 8.0+ with InnoDB
  - Cache: Redis cluster
  - Queue: Redis cluster
  - Set via `.env`: `DB_CONNECTION=mysql`, `CACHE_DRIVER=redis`, `QUEUE_CONNECTION=redis`
  - Prefix: Table prefix configured via `DB_PREFIX` (default: empty string)

#### 5.1.2 Database Schema Architecture

```php
<?php

namespace App\Database\Schema;

/**
 * Core Database Schema Manager
 * Handles schema versioning and migration coordination
 */
class DatabaseSchemaManager
{
    private array $coreEntities = [
        'users' => UserSchema::class,
        'characters' => CharacterSchema::class,
        'character_templates' => CharacterTemplateSchema::class,
        'training_sessions' => TrainingSessionSchema::class,
        'race_results' => RaceResultSchema::class,
        'ai_conversations' => AIConversationSchema::class,
        'scenarios' => ScenarioSchema::class,
        'support_cards' => SupportCardSchema::class,
        'races' => RaceSchema::class,
        'skills' => SkillSchema::class
    ];

    public function getSchemaVersion(): string
    {
        return '2.1.0'; // Aligned with Laravel 12 and modern practices
    }

    public function validateSchemaIntegrity(): SchemaValidationResult
    {
        $results = [];

        foreach ($this->coreEntities as $table => $schemaClass) {
            $validator = new $schemaClass();
            $results[$table] = $validator->validate();
        }

        return new SchemaValidationResult($results);
    }
}
```

### 5.2 Core Entity Schema Design

#### 5.2.1 User Management Schema

```sql
-- Enhanced user management with security features
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    avatar_url VARCHAR(500) NULL,
    timezone VARCHAR(50) DEFAULT 'UTC',
    locale VARCHAR(10) DEFAULT 'en',

    -- User preferences and settings
    preferences JSON NULL COMMENT 'UI preferences, notification settings',
    accessibility_settings JSON NULL COMMENT 'WCAG 2.2 AA compliance settings',

    -- Security features
    mfa_settings JSON NULL COMMENT 'Multi-factor authentication configuration',
    security_questions JSON NULL COMMENT 'Encrypted security questions',

    -- Activity tracking
    last_login_at TIMESTAMP NULL,
    last_login_ip VARCHAR(45) NULL,
    login_count INT UNSIGNED DEFAULT 0,

    -- Account status
    is_active BOOLEAN DEFAULT TRUE,
    is_verified BOOLEAN DEFAULT FALSE,
    subscription_tier ENUM('free', 'premium', 'pro') DEFAULT 'free',

    -- Notification preferences
    email_notifications BOOLEAN DEFAULT TRUE,
    push_notifications BOOLEAN DEFAULT TRUE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    -- Optimized indexes
    INDEX idx_email (email),
    INDEX idx_uuid (uuid),
    INDEX idx_active_verified (is_active, is_verified),
    INDEX idx_subscription (subscription_tier),
    INDEX idx_last_login (last_login_at),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User sessions with enhanced tracking
CREATE TABLE user_sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    device_fingerprint VARCHAR(255) NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,

    INDEX idx_user_id (user_id),
    INDEX idx_last_activity (last_activity),
    INDEX idx_device_fingerprint (device_fingerprint),

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 5.2.2 Character Management Schema

```sql
-- Character instances (user-owned characters)
CREATE TABLE characters (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    user_id BIGINT UNSIGNED NOT NULL,
    character_template_id BIGINT UNSIGNED NOT NULL,
    scenario_id BIGINT UNSIGNED NULL,

    -- Character identification
    name VARCHAR(100) NOT NULL,
    nickname VARCHAR(100) NULL,

    -- Career progress
    current_turn SMALLINT UNSIGNED DEFAULT 0,
    max_turns SMALLINT UNSIGNED DEFAULT 78,
    career_phase ENUM('junior', 'classic', 'senior') DEFAULT 'junior',

    -- Dynamic stats (current state)
    current_stats JSON NOT NULL DEFAULT '{}' COMMENT 'Speed, Stamina, Power, Guts, Wisdom',

    -- Base character data
    aptitudes JSON NOT NULL DEFAULT '{}' COMMENT 'Distance, Surface, Running Style aptitudes',
    base_stats JSON NOT NULL DEFAULT '{}' COMMENT 'Starting stats from template',

    -- Growth and training data
    growth_rates JSON NULL COMMENT 'Stat growth rate modifiers',
    training_history JSON NULL COMMENT 'Summarized training performance',

    -- Goals and objectives
    goals JSON NULL COMMENT 'User-defined and AI-suggested goals',
    target_races JSON NULL COMMENT 'Planned race schedule',

    -- Current status and conditions
    status JSON NULL COMMENT 'Conditions, injuries, motivation',
    support_deck JSON NULL COMMENT 'Currently equipped support cards',

    -- AI integration
    ai_personality JSON NULL COMMENT 'AI-generated personality traits',
    ai_recommendations JSON NULL COMMENT 'Latest AI recommendations',

    -- Completion tracking
    is_active BOOLEAN DEFAULT TRUE,
    is_completed BOOLEAN DEFAULT FALSE,
    completed_at TIMESTAMP NULL,
    completion_rank ENUM('UG', 'G3', 'G2', 'G1') NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Performance indexes
    INDEX idx_user_characters (user_id, is_active),
    INDEX idx_character_template (character_template_id),
    INDEX idx_scenario (scenario_id),
    INDEX idx_career_progress (current_turn, career_phase),
    INDEX idx_completion (is_completed, completion_rank),
    INDEX idx_uuid (uuid),

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (character_template_id) REFERENCES character_templates(id),
    FOREIGN KEY (scenario_id) REFERENCES scenarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Character templates (game data from external APIs)
CREATE TABLE character_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    external_id VARCHAR(50) NULL,

    -- Character names (multilingual support)
    name VARCHAR(100) NOT NULL,
    name_en VARCHAR(100) NULL,
    name_jp VARCHAR(100) NULL,

    -- Character properties
    rarity TINYINT UNSIGNED NOT NULL,
    character_type ENUM('speed', 'stamina', 'power', 'guts', 'wisdom') NULL,

    -- Base game data
    aptitudes JSON NOT NULL DEFAULT '{}' COMMENT 'Base aptitude ratings A-G',
    base_stats JSON NOT NULL DEFAULT '{}' COMMENT 'Starting stat values',
    growth_modifiers JSON NULL COMMENT 'Stat growth rate modifiers',

    -- Available content
    available_skills JSON NULL COMMENT 'Learnable skills and conditions',
    unique_skills JSON NULL COMMENT 'Character-specific unique skills',

    -- Visual and metadata
    image_urls JSON NULL COMMENT 'Character artwork URLs',
    metadata JSON NULL COMMENT 'Additional character information',

    -- Data source tracking
    data_source VARCHAR(50) NOT NULL DEFAULT 'umapyoi',
    data_version VARCHAR(20) NULL,
    data_quality_score DECIMAL(3,2) DEFAULT 1.00,
    last_synced_at TIMESTAMP NULL,
    sync_status ENUM('pending', 'synced', 'error') DEFAULT 'pending',

    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Optimized indexes
    INDEX idx_external_id (external_id),
    INDEX idx_name (name),
    INDEX idx_rarity_type (rarity, character_type),
    INDEX idx_data_source (data_source, sync_status),
    INDEX idx_last_synced (last_synced_at),
    INDEX idx_quality_score (data_quality_score),

    UNIQUE KEY uk_external_source (external_id, data_source)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 5.2.3 Training and Performance Schema

```sql
-- Detailed training session tracking
CREATE TABLE training_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    character_id BIGINT UNSIGNED NOT NULL,
    turn_number SMALLINT UNSIGNED NOT NULL,

    -- Session type and context
    session_type ENUM('training', 'race', 'rest', 'event', 'special') NOT NULL,
    training_facility ENUM('speed', 'stamina', 'power', 'guts', 'wisdom', 'rest') NULL,

    -- Pre-session state
    stats_before JSON NOT NULL COMMENT 'Character stats before session',
    conditions_before JSON NULL COMMENT 'Status conditions before session',
    motivation_before TINYINT UNSIGNED NULL,

    -- Actions and decisions
    primary_action VARCHAR(100) NOT NULL,
    secondary_actions JSON NULL COMMENT 'Additional actions taken',
    support_cards_used JSON NULL COMMENT 'Support cards that activated',

    -- Session results
    stats_gained JSON NOT NULL COMMENT 'Stat points gained',
    stats_after JSON NOT NULL COMMENT 'Character stats after session',
    conditions_after JSON NULL COMMENT 'Status conditions after session',
    motivation_after TINYINT UNSIGNED NULL,

    -- Events and outcomes
    events_triggered JSON NULL COMMENT 'Random events that occurred',
    skills_learned JSON NULL COMMENT 'Skills acquired during session',
    items_gained JSON NULL COMMENT 'Items or rewards received',

    -- AI integration
    ai_recommendation JSON NULL COMMENT 'AI-suggested action',
    ai_confidence DECIMAL(3,2) NULL COMMENT 'AI confidence in recommendation',
    user_followed_ai BOOLEAN NULL COMMENT 'Whether user followed AI advice',
    ai_model_used VARCHAR(50) NULL COMMENT 'AI model that generated recommendation',

    -- Performance metrics
    success_rate DECIMAL(5,2) NULL COMMENT 'Training success percentage',
    efficiency_score DECIMAL(5,2) NULL COMMENT 'Stat gain efficiency rating',
    risk_level TINYINT UNSIGNED NULL COMMENT 'Risk level of chosen action',

    -- Timing and metadata
    session_duration_seconds INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Performance indexes
    INDEX idx_character_sessions (character_id, turn_number),
    INDEX idx_session_type (session_type),
    INDEX idx_training_facility (training_facility),
    INDEX idx_ai_usage (ai_model_used, user_followed_ai),
    INDEX idx_performance (efficiency_score, success_rate),
    INDEX idx_uuid (uuid),

    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,
    UNIQUE KEY uk_character_turn (character_id, turn_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Race participation and results
CREATE TABLE race_results (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    character_id BIGINT UNSIGNED NOT NULL,
    race_id BIGINT UNSIGNED NOT NULL,
    turn_number SMALLINT UNSIGNED NOT NULL,

    -- Race identification
    race_name VARCHAR(200) NOT NULL,
    race_grade ENUM('G1', 'G2', 'G3', 'OP', 'Pre-OP', 'Debut', 'URA') NOT NULL,

    -- Race conditions
    distance SMALLINT UNSIGNED NOT NULL,
    surface ENUM('turf', 'dirt') NOT NULL,
    track_condition ENUM('good', 'slightly_heavy', 'heavy', 'bad') DEFAULT 'good',
    weather ENUM('sunny', 'cloudy', 'rainy', 'snowy') DEFAULT 'sunny',

    -- Performance results
    finish_position TINYINT UNSIGNED NOT NULL,
    total_runners TINYINT UNSIGNED NOT NULL,
    finish_time DECIMAL(6,3) NULL COMMENT 'Race time in seconds',
    margin VARCHAR(50) NULL COMMENT 'Winning/losing margin',

    -- Character state at race
    stats_at_race JSON NOT NULL COMMENT 'Character stats during race',
    skills_active JSON NULL COMMENT 'Skills that activated during race',
    running_style ENUM('escape', 'leading', 'insert', 'chase') NOT NULL,

    -- Race rewards and consequences
    fan_gain INT DEFAULT 0,
    skill_points_gain SMALLINT DEFAULT 0,
    prize_money INT DEFAULT 0,
    prestige_gain SMALLINT DEFAULT 0,

    -- Performance analysis
    pace_analysis JSON NULL COMMENT 'Race pace breakdown',
    position_changes JSON NULL COMMENT 'Position changes during race',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Performance indexes
    INDEX idx_character_races (character_id, turn_number),
    INDEX idx_race_performance (race_id, finish_position),
    INDEX idx_race_grade (race_grade),
    INDEX idx_race_conditions (distance, surface, track_condition),
    INDEX idx_performance_metrics (finish_position, total_runners),
    INDEX idx_uuid (uuid),

    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,
    FOREIGN KEY (race_id) REFERENCES races(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 5.2.4 AI Integration Schema

```sql
-- AI conversation and recommendation tracking
CREATE TABLE ai_conversations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    user_id BIGINT UNSIGNED NOT NULL,
    character_id BIGINT UNSIGNED NULL,

    -- Conversation metadata
    conversation_type ENUM('recommendation', 'analysis', 'planning', 'general', 'optimization') NOT NULL,
    conversation_title VARCHAR(200) NULL,

    -- AI model information
    ai_provider ENUM('ollama', 'bedrock', 'agent') NOT NULL,
    ai_model VARCHAR(100) NOT NULL COMMENT 'Specific model used',
    model_version VARCHAR(50) NULL,

    -- Request and response
    user_prompt TEXT NOT NULL,
    ai_response TEXT NOT NULL,
    context_data JSON NULL COMMENT 'Character/game context provided to AI',

    -- Quality metrics
    confidence_score DECIMAL(3,2) NULL,
    response_time_ms INT UNSIGNED NULL,
    token_count INT UNSIGNED NULL,
    cost_usd DECIMAL(8,4) NULL DEFAULT 0,

    -- User feedback
    user_rating TINYINT UNSIGNED NULL COMMENT '1-5 star rating',
    user_feedback TEXT NULL,
    was_helpful BOOLEAN NULL,

    -- Processing metadata
    processing_strategy VARCHAR(50) NULL COMMENT 'local/cloud/hybrid routing decision',
    fallback_used BOOLEAN DEFAULT FALSE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Performance indexes
    INDEX idx_user_conversations (user_id, created_at),
    INDEX idx_character_conversations (character_id, conversation_type),
    INDEX idx_ai_model (ai_provider, ai_model),
    INDEX idx_conversation_type (conversation_type),
    INDEX idx_user_rating (user_rating, was_helpful),
    INDEX idx_cost_tracking (cost_usd, created_at),
    INDEX idx_uuid (uuid),

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- AI cost tracking and budget management
CREATE TABLE ai_usage_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    conversation_id BIGINT UNSIGNED NULL,

    -- Usage details
    ai_provider ENUM('ollama', 'bedrock', 'agent') NOT NULL,
    ai_model VARCHAR(100) NOT NULL,
    operation_type VARCHAR(50) NOT NULL,

    -- Token usage
    input_tokens INT UNSIGNED DEFAULT 0,
    output_tokens INT UNSIGNED DEFAULT 0,
    total_tokens INT UNSIGNED DEFAULT 0,

    -- Cost tracking
    cost_usd DECIMAL(8,4) NOT NULL DEFAULT 0,
    billing_period DATE NOT NULL,

    -- Performance metrics
    processing_time_ms INT UNSIGNED NULL,
    success BOOLEAN DEFAULT TRUE,
    error_message TEXT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Cost analysis indexes
    INDEX idx_user_billing (user_id, billing_period),
    INDEX idx_model_costs (ai_model, cost_usd),
    INDEX idx_daily_usage (created_at, ai_provider),
    INDEX idx_conversation_costs (conversation_id, cost_usd),

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (conversation_id) REFERENCES ai_conversations(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 5.3 Game Data Schema

#### 5.3.1 Reference Data Tables

```sql
-- Scenarios (game scenarios/campaigns)
CREATE TABLE scenarios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    external_id VARCHAR(50) NULL,
    name VARCHAR(200) NOT NULL,
    name_en VARCHAR(200) NULL,
    name_jp VARCHAR(200) NULL,

    -- Scenario properties
    scenario_type ENUM('ura', 'aoharu', 'climax', 'grand_masters') NOT NULL,
    difficulty_level TINYINT UNSIGNED DEFAULT 1,
    max_turns SMALLINT UNSIGNED DEFAULT 78,

    -- Scenario-specific data
    special_rules JSON NULL COMMENT 'Scenario-specific mechanics',
    available_facilities JSON NULL COMMENT 'Training facilities available',
    unique_events JSON NULL COMMENT 'Scenario-specific events',

    -- Metadata
    description TEXT NULL,
    release_date DATE NULL,
    is_active BOOLEAN DEFAULT TRUE,

    -- Data source tracking
    data_source VARCHAR(50) NOT NULL DEFAULT 'umapyoi',
    last_synced_at TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_scenario_type (scenario_type),
    INDEX idx_external_id (external_id),
    INDEX idx_active (is_active),
    UNIQUE KEY uk_external_source (external_id, data_source)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Support cards
CREATE TABLE support_cards (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    external_id VARCHAR(50) NULL,

    -- Card identification
    name VARCHAR(200) NOT NULL,
    name_en VARCHAR(200) NULL,
    name_jp VARCHAR(200) NULL,

    -- Card properties
    rarity TINYINT UNSIGNED NOT NULL,
    support_type ENUM('speed', 'stamina', 'power', 'guts', 'wisdom', 'friend') NOT NULL,
    character_id BIGINT UNSIGNED NULL COMMENT 'Associated character if any',

    -- Card effects and abilities
    base_effects JSON NOT NULL DEFAULT '{}' COMMENT 'Base stat bonuses and effects',
    limit_break_effects JSON NULL COMMENT 'Effects at different limit break levels',
    unique_effects JSON NULL COMMENT 'Special card-specific effects',

    -- Training bonuses
    training_bonuses JSON NULL COMMENT 'Bonuses to training facilities',
    event_bonuses JSON NULL COMMENT 'Event-related bonuses',

    -- Metadata
    description TEXT NULL,
    image_urls JSON NULL,

    -- Data source tracking
    data_source VARCHAR(50) NOT NULL DEFAULT 'umapyoi',
    data_quality_score DECIMAL(3,2) DEFAULT 1.00,
    last_synced_at TIMESTAMP NULL,

    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_support_type (support_type),
    INDEX idx_rarity (rarity),
    INDEX idx_character_cards (character_id),
    INDEX idx_external_id (external_id),
    UNIQUE KEY uk_external_source (external_id, data_source)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Races (available races in the game)
CREATE TABLE races (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    external_id VARCHAR(50) NULL,

    -- Race identification
    name VARCHAR(200) NOT NULL,
    name_en VARCHAR(200) NULL,
    name_jp VARCHAR(200) NULL,

    -- Race properties
    grade ENUM('G1', 'G2', 'G3', 'OP', 'Pre-OP', 'Debut', 'URA') NOT NULL,
    distance SMALLINT UNSIGNED NOT NULL,
    surface ENUM('turf', 'dirt') NOT NULL,
    direction ENUM('right', 'left', 'straight') DEFAULT 'right',

    -- Race conditions and requirements
    age_restrictions JSON NULL COMMENT 'Age and career phase restrictions',
    entry_requirements JSON NULL COMMENT 'Stats or achievement requirements',

    -- Race rewards
    base_rewards JSON NULL COMMENT 'Fan gain, skill points, prize money',
    victory_conditions JSON NULL COMMENT 'Special victory bonuses',

    -- Scheduling
    available_turns JSON NULL COMMENT 'Turns when race is available',
    season ENUM('spring', 'summer', 'autumn', 'winter') NULL,

    -- Metadata
    description TEXT NULL,
    track_name VARCHAR(200) NULL,

    -- Data source tracking
    data_source VARCHAR(50) NOT NULL DEFAULT 'umapyoi',
    last_synced_at TIMESTAMP NULL,

    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_race_grade (grade),
    INDEX idx_race_conditions (distance, surface),
    INDEX idx_external_id (external_id),
    INDEX idx_season (season),
    UNIQUE KEY uk_external_source (external_id, data_source)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 5.4 Performance Optimization Schema

#### 5.4.1 Indexing Strategy

```sql
-- Composite indexes for common query patterns
ALTER TABLE characters
ADD INDEX idx_user_active_scenario (user_id, is_active, scenario_id),
ADD INDEX idx_completion_stats (is_completed, completion_rank, created_at);

ALTER TABLE training_sessions
ADD INDEX idx_character_turn_type (character_id, turn_number, session_type),
ADD INDEX idx_ai_performance (ai_model_used, efficiency_score, created_at);

ALTER TABLE race_results
ADD INDEX idx_performance_analysis (race_grade, finish_position, distance),
ADD INDEX idx_character_performance (character_id, finish_position, race_grade);

ALTER TABLE ai_conversations
ADD INDEX idx_user_type_date (user_id, conversation_type, created_at),
ADD INDEX idx_cost_analysis (ai_provider, cost_usd, created_at);

-- Full-text search indexes
ALTER TABLE character_templates
ADD FULLTEXT INDEX ft_character_names (name, name_en, name_jp);

ALTER TABLE races
ADD FULLTEXT INDEX ft_race_names (name, name_en, name_jp);

ALTER TABLE support_cards
ADD FULLTEXT INDEX ft_card_names (name, name_en, name_jp);
```

#### 5.4.2 Partitioning Strategy

```sql
-- Partition large tables by date for better performance
ALTER TABLE training_sessions
PARTITION BY RANGE (YEAR(created_at)) (
    PARTITION p2024 VALUES LESS THAN (2025),
    PARTITION p2025 VALUES LESS THAN (2026),
    PARTITION p2026 VALUES LESS THAN (2027),
    PARTITION p_future VALUES LESS THAN MAXVALUE
);

ALTER TABLE ai_conversations
PARTITION BY RANGE (YEAR(created_at)) (
    PARTITION p2024 VALUES LESS THAN (2025),
    PARTITION p2025 VALUES LESS THAN (2026),
    PARTITION p2026 VALUES LESS THAN (2027),
    PARTITION p_future VALUES LESS THAN MAXVALUE
);

ALTER TABLE ai_usage_logs
PARTITION BY RANGE (YEAR(created_at)) (
    PARTITION p2024 VALUES LESS THAN (2025),
    PARTITION p2025 VALUES LESS THAN (2026),
    PARTITION p2026 VALUES LESS THAN (2027),
    PARTITION p_future VALUES LESS THAN MAXVALUE
);
```

---

## 6. User Interface Design

### 6.1 Modern Web Application Architecture

The application implements a cutting-edge responsive web interface built with Laravel 12's new starter kit architecture, featuring TypeScript, Tailwind CSS v4, and modern component-based design with comprehensive WCAG 2.2 AA accessibility compliance.

#### 6.1.1 Frontend Technology Stack

```yaml
Core Framework:
  Laravel 12: New starter kit with TypeScript integration
  Tailwind CSS v4: Zero configuration, 5x faster builds
  Alpine.js: Lightweight reactive framework
  Livewire 3: Real-time server-side rendering

Progressive Web App:
  Service Workers: Offline functionality and caching
  Web App Manifest: Installable app experience
  Background Sync: Data synchronization when offline
  Push Notifications: Real-time updates and alerts

Accessibility Compliance:
  WCAG 2.2 AA: Full compliance with latest standards
  Screen Reader Support: NVDA, JAWS, VoiceOver compatibility
  Keyboard Navigation: Complete keyboard accessibility
  Color Contrast: 4.5:1 normal text, 3:1 large text, 3:1 focus indicators

Performance Optimization:
  Core Web Vitals: LCP < 2.5s, INP < 200ms, CLS < 0.1
  Lazy Loading: Progressive content loading
  Code Splitting: Optimized bundle sizes
  Service Worker Caching: Intelligent caching strategies
```

#### 6.1.2 Component Architecture

```php
<?php

namespace App\View\Components;

/**
 * Base UI Component with Accessibility Features
 * Provides foundation for all UI components with WCAG 2.2 AA compliance
 */
abstract class AccessibleComponent extends Component
{
    protected array $accessibilityAttributes = [
        'role' => null,
        'aria-label' => null,
        'aria-describedby' => null,
        'aria-expanded' => null,
        'aria-controls' => null,
        'tabindex' => null
    ];

    protected array $wcagRequirements = [
        'contrast_ratio' => 4.5, // 4.5:1 for normal text
        'large_text_contrast' => 3.0, // 3:1 for large text
        'focus_indicator_contrast' => 3.0, // 3:1 for focus indicators
        'touch_target_size' => 44, // 44px minimum touch target
        'text_spacing' => true // Proper line height and spacing
    ];

    public function __construct(
        protected string $id = '',
        protected array $accessibility = [],
        protected bool $keyboardNavigable = true,
        protected string $semanticRole = ''
    ) {
        $this->id = $id ?: 'component-' . uniqid();
        $this->accessibility = array_merge($this->accessibilityAttributes, $accessibility);
    }

    protected function getAccessibilityAttributes(): array
    {
        $attributes = [];

        foreach ($this->accessibility as $key => $value) {
            if ($value !== null) {
                $attributes[$key] = $value;
            }
        }

        if ($this->keyboardNavigable && !isset($attributes['tabindex'])) {
            $attributes['tabindex'] = '0';
        }

        if ($this->semanticRole) {
            $attributes['role'] = $this->semanticRole;
        }

        return $attributes;
    }

    protected function validateWCAGCompliance(): array
    {
        return [
            'has_proper_contrast' => $this->validateContrastRatio(),
            'keyboard_accessible' => $this->keyboardNavigable,
            'screen_reader_friendly' => !empty($this->accessibility['aria-label']),
            'semantic_markup' => !empty($this->semanticRole),
            'focus_indicators' => $this->hasFocusIndicators()
        ];
    }
}
```

### 6.2 Responsive Design System

#### 6.2.1 Tailwind CSS v4 Implementation

```css
/* Custom Tailwind CSS v4 Configuration */
@import "tailwindcss";

/* WCAG 2.2 AA Compliant Color System */
@theme {
  --color-primary-50: #f0f9ff;
  --color-primary-500: #3b82f6; /* 4.5:1 contrast ratio */
  --color-primary-600: #2563eb; /* Enhanced contrast */
  --color-primary-700: #1d4ed8; /* High contrast */

  --color-success-500: #10b981; /* 4.5:1 contrast ratio */
  --color-warning-500: #f59e0b; /* 4.5:1 contrast ratio */
  --color-error-500: #ef4444; /* 4.5:1 contrast ratio */

  /* Focus indicators with 3:1 contrast ratio */
  --color-focus: #2563eb;
  --focus-ring-width: 2px;
  --focus-ring-offset: 2px;

  /* Typography scale for accessibility */
  --font-size-xs: 0.75rem; /* 12px */
  --font-size-sm: 0.875rem; /* 14px */
  --font-size-base: 1rem; /* 16px - minimum for body text */
  --font-size-lg: 1.125rem; /* 18px - large text threshold */
  --font-size-xl: 1.25rem; /* 20px */

  /* Line height for readability */
  --line-height-tight: 1.25;
  --line-height-normal: 1.5; /* WCAG recommended */
  --line-height-relaxed: 1.625;

  /* Spacing scale */
  --spacing-xs: 0.25rem; /* 4px */
  --spacing-sm: 0.5rem; /* 8px */
  --spacing-md: 1rem; /* 16px */
  --spacing-lg: 1.5rem; /* 24px */
  --spacing-xl: 2rem; /* 32px */

  /* Touch target sizes (minimum 44px) */
  --touch-target-sm: 2.75rem; /* 44px */
  --touch-target-md: 3rem; /* 48px */
  --touch-target-lg: 3.5rem; /* 56px */
}

/* Accessibility-focused utility classes */
.focus-visible {
  @apply outline-none ring-2 ring-focus ring-offset-2 ring-offset-white;
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

.skip-link {
  @apply sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4
         bg-primary-600 text-white px-4 py-2 rounded-md z-50;
}

/* High contrast mode support */
@media (prefers-contrast: high) {
  :root {
    --color-primary-500: #000080;
    --color-text: #000000;
    --color-background: #ffffff;
  }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}

/* Dark mode with proper contrast ratios */
@media (prefers-color-scheme: dark) {
  :root {
    --color-background: #0f172a;
    --color-text: #f8fafc;
    --color-primary-500: #60a5fa; /* Adjusted for dark mode contrast */
  }
}
```

#### 6.2.2 Responsive Layout Components

```blade
{{-- resources/views/components/layout/app.blade.php --}}
<x-layout.base>
    {{-- Skip Links for Accessibility --}}
    <div class="skip-links">
        <a href="#main-content" class="skip-link">Skip to main content</a>
        <a href="#navigation" class="skip-link">Skip to navigation</a>
        <a href="#search" class="skip-link">Skip to search</a>
    </div>

    {{-- Main Application Header --}}
    <header class="bg-white shadow-sm border-b border-gray-200" role="banner">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                {{-- Logo and Brand --}}
                <div class="flex items-center">
                    <x-ui.logo
                        class="h-8 w-auto"
                        alt="Umamusume Career Planner"
                        aria-label="Return to homepage"
                    />
                    <h1 class="ml-3 text-xl font-semibold text-gray-900 sr-only">
                        Umamusume Career Planner
                    </h1>
                </div>

                {{-- Main Navigation --}}
                <nav id="navigation" role="navigation" aria-label="Main navigation">
                    <x-ui.navigation.main />
                </nav>

                {{-- User Menu and Actions --}}
                <div class="flex items-center space-x-4">
                    <x-ui.search.global id="search" />
                    <x-ui.notifications />
                    <x-ui.user-menu />
                </div>
            </div>
        </div>
    </header>

    {{-- Main Content Area --}}
    <main id="main-content" role="main" class="flex-1">
        {{-- Breadcrumb Navigation --}}
        <x-ui.breadcrumb class="bg-gray-50 border-b border-gray-200" />

        {{-- Page Content --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            {{ $slot }}
        </div>
    </main>

    {{-- Footer --}}
    <footer class="bg-gray-50 border-t border-gray-200" role="contentinfo">
        <x-ui.footer />
    </footer>

    {{-- Accessibility Announcements --}}
    <div id="aria-live-region" aria-live="polite" aria-atomic="true" class="sr-only"></div>
    <div id="aria-live-assertive" aria-live="assertive" aria-atomic="true" class="sr-only"></div>
</x-layout.base>
```

### 6.3 Interactive Components

#### 6.3.1 Training Interface Components

```blade
{{-- Character Training Dashboard --}}
<x-ui.card class="training-dashboard" role="region" aria-labelledby="training-title">
    <x-slot:header>
        <h2 id="training-title" class="text-lg font-semibold text-gray-900">
            Training Dashboard
        </h2>
        <x-ui.help-button
            aria-describedby="training-help"
            content="Manage your character's training schedule and view AI recommendations"
        />
    </x-slot:header>

    {{-- Character Status Panel --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <x-training.character-status
            :character="$character"
            class="lg:col-span-2"
            aria-labelledby="character-status-title"
        />

        <x-training.quick-actions
            :character="$character"
            aria-labelledby="quick-actions-title"
        />
    </div>

    {{-- Training Facilities Grid --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
        @foreach($trainingFacilities as $facility)
            <x-training.facility-card
                :facility="$facility"
                :character="$character"
                @click="selectFacility"
                role="button"
                tabindex="0"
                aria-describedby="facility-{{ $facility->id }}-description"
                @keydown.enter="selectFacility"
                @keydown.space.prevent="selectFacility"
            />
        @endforeach
    </div>

    {{-- AI Recommendations Panel --}}
    <x-ai.recommendations-panel
        :character="$character"
        :turn="$currentTurn"
        class="mb-6"
        aria-labelledby="ai-recommendations-title"
    />

    {{-- Training History --}}
    <x-training.history-table
        :sessions="$recentSessions"
        aria-labelledby="training-history-title"
        role="region"
    />
</x-ui.card>
```

#### 6.3.2 AI Chat Interface

```blade
{{-- AI Assistant Chat Interface --}}
<div class="ai-chat-container"
     role="region"
     aria-labelledby="ai-chat-title"
     x-data="aiChat()"
     x-init="initializeChat()">

    <header class="chat-header bg-primary-50 p-4 border-b">
        <h3 id="ai-chat-title" class="text-lg font-semibold text-primary-900">
            AI Career Assistant
        </h3>
        <div class="flex items-center space-x-2 mt-2">
            <x-ui.badge :variant="$aiStatus" size="sm">
                {{ $aiProvider }} - {{ $aiModel }}
            </x-ui.badge>
            <span class="text-sm text-gray-600" id="ai-status">
                {{ $aiStatus === 'online' ? 'Ready to help' : 'Connecting...' }}
            </span>
        </div>
    </header>

    {{-- Chat Messages Area --}}
    <div class="chat-messages flex-1 overflow-y-auto p-4 space-y-4"
         role="log"
         aria-live="polite"
         aria-label="Chat conversation"
         x-ref="messagesContainer">

        <template x-for="message in messages" :key="message.id">
            <div class="message"
                 :class="message.sender === 'user' ? 'message-user' : 'message-ai'"
                 role="article"
                 :aria-label="`Message from ${message.sender}`">

                <div class="message-content bg-white rounded-lg p-3 shadow-sm border">
                    <div class="message-header flex items-center justify-between mb-2">
                        <span class="font-medium text-sm"
                              :class="message.sender === 'user' ? 'text-primary-600' : 'text-gray-700'">
                            <span x-text="message.sender === 'user' ? 'You' : 'AI Assistant'"></span>
                        </span>
                        <time class="text-xs text-gray-500"
                              :datetime="message.timestamp"
                              x-text="formatTime(message.timestamp)">
                        </time>
                    </div>

                    <div class="message-text prose prose-sm max-w-none"
                         x-html="formatMessage(message.content)">
                    </div>

                    {{-- AI Message Metadata --}}
                    <div x-show="message.sender === 'ai' && message.metadata"
                         class="message-metadata mt-3 pt-3 border-t border-gray-100">
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>
                                Model: <span x-text="message.metadata?.model"></span>
                                <template x-if="message.metadata?.confidence">
                                    | Confidence: <span x-text="message.metadata.confidence"></span>%
                                </template>
                            </span>
                            <span x-show="message.metadata?.cost">
                                Cost: $<span x-text="message.metadata.cost"></span>
                            </span>
                        </div>
                    </div>

                    {{-- Message Actions --}}
                    <div class="message-actions mt-3 flex items-center space-x-2">
                        <button type="button"
                                class="text-xs text-gray-500 hover:text-primary-600 focus:outline-none focus:text-primary-600"
                                @click="copyMessage(message.content)"
                                aria-label="Copy message">
                            Copy
                        </button>
                        <template x-if="message.sender === 'ai'">
                            <button type="button"
                                    class="text-xs text-gray-500 hover:text-primary-600 focus:outline-none focus:text-primary-600"
                                    @click="rateMessage(message.id)"
                                    aria-label="Rate this response">
                                Rate
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Chat Input Area --}}
    <div class="chat-input bg-gray-50 p-4 border-t">
        <form @submit.prevent="sendMessage()" class="flex items-end space-x-3">
            <div class="flex-1">
                <label for="chat-input" class="sr-only">Type your message</label>
                <textarea id="chat-input"
                         x-model="currentMessage"
                         placeholder="Ask about training strategies, character optimization, or any career planning questions..."
                         class="w-full resize-none border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500"
                         rows="2"
                         maxlength="1000"
                         @keydown.enter.prevent="handleEnterKey($event)"
                         :disabled="isProcessing"
                         aria-describedby="chat-input-help">
                </textarea>
                <div id="chat-input-help" class="mt-1 text-xs text-gray-500">
                    Press Enter to send, Shift+Enter for new line
                </div>
            </div>

            <button type="submit"
                    class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                    :disabled="!currentMessage.trim() || isProcessing"
                    aria-label="Send message">
                <template x-if="!isProcessing">
                    <x-heroicon-o-paper-airplane class="w-5 h-5" />
                </template>
                <template x-if="isProcessing">
                    <x-ui.spinner class="w-5 h-5" />
                </template>
            </button>
        </form>

        {{-- Processing Indicator --}}
        <div x-show="isProcessing"
             class="mt-3 flex items-center space-x-2 text-sm text-gray-600"
             role="status"
             aria-live="polite">
            <x-ui.spinner class="w-4 h-4" />
            <span x-text="processingStatus">AI is thinking...</span>
        </div>
    </div>
</div>
```

### 6.4 Progressive Web App Implementation

#### 6.4.1 Service Worker Configuration

```javascript
// public/sw.js - Service Worker for PWA functionality
const CACHE_NAME = 'umamusume-planner-v1.0.0';
const STATIC_CACHE = 'static-v1.0.0';
const DYNAMIC_CACHE = 'dynamic-v1.0.0';

// Assets to cache immediately
const STATIC_ASSETS = [
    '/',
    '/css/app.css',
    '/js/app.js',
    '/images/app-icon-192.png',
    '/images/app-icon-512.png',
    '/offline.html'
];

// API endpoints to cache
const API_CACHE_PATTERNS = [
    /^\/api\/characters/,
    /^\/api\/training-facilities/,
    /^\/api\/support-cards/,
    /^\/api\/races/
];

// Install event - cache static assets
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then(cache => cache.addAll(STATIC_ASSETS))
            .then(() => self.skipWaiting())
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys()
            .then(cacheNames => {
                return Promise.all(
                    cacheNames
                        .filter(cacheName =>
                            cacheName !== STATIC_CACHE &&
                            cacheName !== DYNAMIC_CACHE
                        )
                        .map(cacheName => caches.delete(cacheName))
                );
            })
            .then(() => self.clients.claim())
    );
});

// Fetch event - implement caching strategies
self.addEventListener('fetch', event => {
    const { request } = event;

    // Handle API requests with network-first strategy
    if (isApiRequest(request)) {
        event.respondWith(networkFirstStrategy(request));
        return;
    }

    // Handle static assets with cache-first strategy
    if (isStaticAsset(request)) {
        event.respondWith(cacheFirstStrategy(request));
        return;
    }

    // Handle navigation requests with network-first, fallback to offline page
    if (isNavigationRequest(request)) {
        event.respondWith(navigationStrategy(request));
        return;
    }

    // Default: network-first strategy
    event.respondWith(networkFirstStrategy(request));
});

// Network-first strategy for dynamic content
async function networkFirstStrategy(request) {
    try {
        const networkResponse = await fetch(request);

        if (networkResponse.ok) {
            const cache = await caches.open(DYNAMIC_CACHE);
            cache.put(request, networkResponse.clone());
        }

        return networkResponse;
    } catch (error) {
        const cachedResponse = await caches.match(request);
        return cachedResponse || new Response('Offline', { status: 503 });
    }
}

// Cache-first strategy for static assets
async function cacheFirstStrategy(request) {
    const cachedResponse = await caches.match(request);

    if (cachedResponse) {
        return cachedResponse;
    }

    try {
        const networkResponse = await fetch(request);
        const cache = await caches.open(STATIC_CACHE);
        cache.put(request, networkResponse.clone());
        return networkResponse;
    } catch (error) {
        return new Response('Asset not available offline', { status: 503 });
    }
}

// Navigation strategy with offline fallback
async function navigationStrategy(request) {
    try {
        const networkResponse = await fetch(request);
        return networkResponse;
    } catch (error) {
        const cachedResponse = await caches.match(request);
        return cachedResponse || caches.match('/offline.html');
    }
}

// Background sync for offline actions
self.addEventListener('sync', event => {
    if (event.tag === 'training-session-sync') {
        event.waitUntil(syncTrainingSessions());
    }

    if (event.tag === 'ai-conversation-sync') {
        event.waitUntil(syncAIConversations());
    }
});

// Push notification handling
self.addEventListener('push', event => {
    const options = {
        body: event.data ? event.data.text() : 'New update available',
        icon: '/images/app-icon-192.png',
        badge: '/images/badge-icon.png',
        vibrate: [200, 100, 200],
        data: {
            url: '/'
        },
        actions: [
            {
                action: 'open',
                title: 'Open App',
                icon: '/images/open-icon.png'
            },
            {
                action: 'close',
                title: 'Close',
                icon: '/images/close-icon.png'
            }
        ]
    };

    event.waitUntil(
        self.registration.showNotification('Umamusume Career Planner', options)
    );
});

// Notification click handling
self.addEventListener('notificationclick', event => {
    event.notification.close();

    if (event.action === 'open' || !event.action) {
        event.waitUntil(
            clients.openWindow(event.notification.data.url || '/')
        );
    }
});

// Helper functions
function isApiRequest(request) {
    return API_CACHE_PATTERNS.some(pattern => pattern.test(request.url));
}

function isStaticAsset(request) {
    return request.destination === 'style' ||
           request.destination === 'script' ||
           request.destination === 'image';
}

function isNavigationRequest(request) {
    return request.mode === 'navigate';
}

async function syncTrainingSessions() {
    // Sync offline training sessions when back online
    const offlineSessions = await getOfflineTrainingSessions();

    for (const session of offlineSessions) {
        try {
            await fetch('/api/training-sessions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': await getCSRFToken()
                },
                body: JSON.stringify(session)
            });

            await removeOfflineSession(session.id);
        } catch (error) {
            console.error('Failed to sync training session:', error);
        }
    }
}
```

#### 6.4.2 Web App Manifest

```json
{
  "name": "Umamusume Career Planner",
  "short_name": "Uma Planner",
  "description": "AI-powered career planning tool for Umamusume Pretty Derby",
  "start_url": "/",
  "display": "standalone",
  "background_color": "#ffffff",
  "theme_color": "#3b82f6",
  "orientation": "portrait-primary",
  "scope": "/",
  "lang": "en",
  "dir": "ltr",

  "icons": [
    {
      "src": "/images/app-icon-72.png",
      "sizes": "72x72",
      "type": "image/png",
      "purpose": "any"
    },
    {
      "src": "/images/app-icon-96.png",
      "sizes": "96x96",
      "type": "image/png",
      "purpose": "any"
    },
    {
      "src": "/images/app-icon-128.png",
      "sizes": "128x128",
      "type": "image/png",
      "purpose": "any"
    },
    {
      "src": "/images/app-icon-144.png",
      "sizes": "144x144",
      "type": "image/png",
      "purpose": "any"
    },
    {
      "src": "/images/app-icon-152.png",
      "sizes": "152x152",
      "type": "image/png",
      "purpose": "any"
    },
    {
      "src": "/images/app-icon-192.png",
      "sizes": "192x192",
      "type": "image/png",
      "purpose": "any maskable"
    },
    {
      "src": "/images/app-icon-384.png",
      "sizes": "384x384",
      "type": "image/png",
      "purpose": "any"
    },
    {
      "src": "/images/app-icon-512.png",
      "sizes": "512x512",
      "type": "image/png",
      "purpose": "any maskable"
    }
  ],

  "screenshots": [
    {
      "src": "/images/screenshot-desktop-1.png",
      "sizes": "1280x720",
      "type": "image/png",
      "form_factor": "wide",
      "label": "Training Dashboard"
    },
    {
      "src": "/images/screenshot-mobile-1.png",
      "sizes": "375x667",
      "type": "image/png",
      "form_factor": "narrow",
      "label": "Mobile Training Interface"
    }
  ],

  "categories": ["games", "productivity", "utilities"],
  "shortcuts": [
    {
      "name": "New Character",
      "short_name": "New Character",
      "description": "Start a new character career",
      "url": "/characters/create",
      "icons": [
        {
          "src": "/images/shortcut-new-character.png",
          "sizes": "96x96"
        }
      ]
    },
    {
      "name": "AI Assistant",
      "short_name": "AI Chat",
      "description": "Open AI career assistant",
      "url": "/ai-chat",
      "icons": [
        {
          "src": "/images/shortcut-ai-chat.png",
          "sizes": "96x96"
        }
      ]
    }
  ],

  "related_applications": [],
  "prefer_related_applications": false,

  "protocol_handlers": [
    {
      "protocol": "web+umamusume",
      "url": "/import?data=%s"
    }
  ]
}
```

---

## 7. API Design

### 7.1 RESTful API Architecture

The application implements a comprehensive RESTful API following Laravel 12 best practices, with proper resource management, authentication, and external service integration.

#### 7.1.1 API Structure and Versioning

```php
<?php

namespace App\Http\Controllers\Api\V1;

/**
 * Base API Controller with Common Functionality
 * Provides standardized responses and error handling
 */
abstract class BaseApiController extends Controller
{
    protected int $defaultPerPage = 15;
    protected int $maxPerPage = 100;

    protected function successResponse($data = null, string $message = 'Success', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => now()->toISOString(),
            'version' => 'v1'
        ], $status);
    }

    protected function errorResponse(string $message, int $status = 400, array $errors = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'timestamp' => now()->toISOString(),
            'version' => 'v1'
        ], $status);
    }

    protected function paginatedResponse($data, string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data->items(),
            'pagination' => [
                'current_page' => $data->currentPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'last_page' => $data->lastPage(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
                'has_more_pages' => $data->hasMorePages()
            ],
            'timestamp' => now()->toISOString(),
            'version' => 'v1'
        ]);
    }
}
```

#### 7.1.2 Character Management API

```php
<?php

namespace App\Http\Controllers\Api\V1;

/**
 * Character Management API Controller
 * Handles CRUD operations for user characters
 */
class CharacterController extends BaseApiController
{
    public function __construct(
        private CharacterService $characterService,
        private AIRecommendationService $aiService
    ) {
        $this->middleware('auth:sanctum');
        $this->middleware('throttle:60,1');
    }

    /**
     * GET /api/v1/characters
     * List user's characters with filtering and pagination
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'scenario_id' => 'nullable|exists:scenarios,id',
            'is_active' => 'nullable|boolean',
            'is_completed' => 'nullable|boolean',
            'per_page' => 'nullable|integer|min:1|max:' . $this->maxPerPage,
            'sort_by' => 'nullable|in:name,created_at,updated_at,current_turn',
            'sort_direction' => 'nullable|in:asc,desc'
        ]);

        $characters = $this->characterService->getUserCharacters(
            user: $request->user(),
            filters: $request->only(['scenario_id', 'is_active', 'is_completed']),
            perPage: $request->get('per_page', $this->defaultPerPage),
            sortBy: $request->get('sort_by', 'updated_at'),
            sortDirection: $request->get('sort_direction', 'desc')
        );

        return $this->paginatedResponse(
            CharacterResource::collection($characters),
            'Characters retrieved successfully'
        );
    }

    /**
     * POST /api/v1/characters
     * Create a new character
     */
    public function store(CreateCharacterRequest $request): JsonResponse
    {
        try {
            $character = $this->characterService->createCharacter(
                user: $request->user(),
                data: $request->validated()
            );

            // Generate initial AI recommendations
            $recommendations = $this->aiService->generateInitialRecommendations($character);

            return $this->successResponse(
                new CharacterResource($character->load('template', 'scenario')),
                'Character created successfully',
                201
            );

        } catch (CharacterCreationException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    /**
     * GET /api/v1/characters/{character}
     * Get character details with related data
     */
    public function show(Character $character): JsonResponse
    {
        $this->authorize('view', $character);

        $character->load([
            'template',
            'scenario',
            'trainingSessions' => fn($q) => $q->latest()->limit(10),
            'raceResults' => fn($q) => $q->latest()->limit(5),
            'aiConversations' => fn($q) => $q->latest()->limit(3)
        ]);

        return $this->successResponse(
            new CharacterDetailResource($character),
            'Character details retrieved successfully'
        );
    }

    /**
     * PUT /api/v1/characters/{character}
     * Update character information
     */
    public function update(UpdateCharacterRequest $request, Character $character): JsonResponse
    {
        $this->authorize('update', $character);

        try {
            $updatedCharacter = $this->characterService->updateCharacter(
                character: $character,
                data: $request->validated()
            );

            return $this->successResponse(
                new CharacterResource($updatedCharacter),
                'Character updated successfully'
            );

        } catch (CharacterUpdateException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    /**
     * DELETE /api/v1/characters/{character}
     * Delete character (soft delete)
     */
    public function destroy(Character $character): JsonResponse
    {
        $this->authorize('delete', $character);

        $this->characterService->deleteCharacter($character);

        return $this->successResponse(
            null,
            'Character deleted successfully'
        );
    }

    /**
     * POST /api/v1/characters/{character}/training
     * Process training session
     */
    public function processTraining(ProcessTrainingRequest $request, Character $character): JsonResponse
    {
        $this->authorize('update', $character);

        try {
            $result = $this->characterService->processTrainingSession(
                character: $character,
                trainingData: $request->validated()
            );

            return $this->successResponse(
                new TrainingResultResource($result),
                'Training session processed successfully'
            );

        } catch (TrainingProcessingException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    /**
     * GET /api/v1/characters/{character}/recommendations
     * Get AI recommendations for character
     */
    public function getRecommendations(Character $character): JsonResponse
    {
        $this->authorize('view', $character);

        try {
            $recommendations = $this->aiService->generateRecommendations(
                character: $character,
                context: ['current_turn', 'goals', 'recent_performance']
            );

            return $this->successResponse(
                AIRecommendationResource::collection($recommendations),
                'Recommendations generated successfully'
            );

        } catch (AIServiceException $e) {
            return $this->errorResponse(
                'Failed to generate recommendations: ' . $e->getMessage(),
                503
            );
        }
    }
}
```

#### 7.1.3 AI Integration API

```php
<?php

namespace App\Http\Controllers\Api\V1;

/**
 * AI Integration API Controller
 * Handles AI conversations and recommendations
 */
class AIController extends BaseApiController
{
    public function __construct(
        private AIServiceCoordinator $aiCoordinator,
        private ConversationService $conversationService
    ) {
        $this->middleware('auth:sanctum');
        $this->middleware('throttle:30,1'); // More restrictive for AI endpoints
    }

    /**
     * POST /api/v1/ai/chat
     * Send message to AI assistant
     */
    public function chat(AIChatRequest $request): JsonResponse
    {
        try {
            $aiRequest = new AIRequest([
                'prompt' => $request->input('message'),
                'context' => $request->input('context', []),
                'character_id' => $request->input('character_id'),
                'conversation_type' => $request->input('type', 'general'),
                'user_id' => $request->user()->id
            ]);

            $response = $this->aiCoordinator->processRequest($aiRequest);

            // Save conversation
            $conversation = $this->conversationService->saveConversation(
                user: $request->user(),
                request: $aiRequest,
                response: $response
            );

            return $this->successResponse([
                'conversation_id' => $conversation->uuid,
                'message' => $response->content,
                'model_used' => $response->model,
                'provider' => $response->provider,
                'confidence' => $response->confidence,
                'cost' => $response->cost,
                'processing_time' => $response->processingTime,
                'suggestions' => $response->suggestions ?? []
            ], 'AI response generated successfully');

        } catch (AIProcessingException $e) {
            return $this->errorResponse(
                'AI processing failed: ' . $e->getMessage(),
                503
            );
        } catch (InsufficientBudgetException $e) {
            return $this->errorResponse(
                'AI budget exceeded. Please try again later or upgrade your plan.',
                429
            );
        }
    }

    /**
     * GET /api/v1/ai/conversations
     * Get user's AI conversation history
     */
    public function conversations(Request $request): JsonResponse
    {
        $request->validate([
            'character_id' => 'nullable|exists:characters,id',
            'type' => 'nullable|in:recommendation,analysis,planning,general,optimization',
            'per_page' => 'nullable|integer|min:1|max:50'
        ]);

        $conversations = $this->conversationService->getUserConversations(
            user: $request->user(),
            filters: $request->only(['character_id', 'type']),
            perPage: $request->get('per_page', 10)
        );

        return $this->paginatedResponse(
            AIConversationResource::collection($conversations),
            'Conversations retrieved successfully'
        );
    }

    /**
     * POST /api/v1/ai/analyze-screenshot
     * Analyze game screenshot with AI
     */
    public function analyzeScreenshot(AnalyzeScreenshotRequest $request): JsonResponse
    {
        try {
            $analysis = $this->aiCoordinator->analyzeScreenshot(
                screenshot: $request->file('screenshot'),
                context: $request->input('context', [])
            );

            return $this->successResponse([
                'extracted_data' => $analysis->extractedData,
                'game_state' => $analysis->gameState,
                'confidence' => $analysis->confidence,
                'suggestions' => $analysis->suggestions,
                'cost' => $analysis->cost
            ], 'Screenshot analyzed successfully');

        } catch (ScreenshotAnalysisException $e) {
            return $this->errorResponse(
                'Screenshot analysis failed: ' . $e->getMessage(),
                422
            );
        }
    }

    /**
     * GET /api/v1/ai/usage
     * Get AI usage statistics and budget information
     */
    public function usage(Request $request): JsonResponse
    {
        $usage = $this->conversationService->getUserUsageStats($request->user());

        return $this->successResponse([
            'current_period' => [
                'daily' => $usage['daily'],
                'weekly' => $usage['weekly'],
                'monthly' => $usage['monthly']
            ],
            'budget_limits' => [
                'daily' => $usage['daily_limit'],
                'weekly' => $usage['weekly_limit'],
                'monthly' => $usage['monthly_limit']
            ],
            'remaining_budget' => [
                'daily' => max(0, $usage['daily_limit'] - $usage['daily']),
                'weekly' => max(0, $usage['weekly_limit'] - $usage['weekly']),
                'monthly' => max(0, $usage['monthly_limit'] - $usage['monthly'])
            ],
            'model_breakdown' => $usage['model_usage'],
            'total_conversations' => $usage['total_conversations']
        ], 'Usage statistics retrieved successfully');
    }
}
```

### 7.2 External API Integration

#### 7.2.1 Game Data API Service

```php
<?php

namespace App\Services\External;

/**
 * External Game Data API Service
 * Manages integration with multiple game data sources
 */
class GameDataAPIService
{
    private array $apiSources = [
        'primary' => 'umapyoi',
        'fallback' => ['umamusumedb', 'umalator']
    ];

    public function __construct(
        private HttpClient $httpClient,
        private CacheManager $cache,
        private Logger $logger
    ) {}

    /**
     * Fetch character data from external APIs
     */
    public function fetchCharacterData(string $characterId = null): Collection
    {
        $cacheKey = "characters_data_" . ($characterId ?? 'all');

        return $this->cache->remember($cacheKey, 3600, function () use ($characterId) {
            return $this->fetchWithFallback('characters', $characterId);
        });
    }

    /**
     * Fetch support card data from external APIs
     */
    public function fetchSupportCardData(string $cardId = null): Collection
    {
        $cacheKey = "support_cards_data_" . ($cardId ?? 'all');

        return $this->cache->remember($cacheKey, 3600, function () use ($cardId) {
            return $this->fetchWithFallback('support_cards', $cardId);
        });
    }

    /**
     * Fetch race data from external APIs
     */
    public function fetchRaceData(): Collection
    {
        return $this->cache->remember('races_data', 7200, function () {
            return $this->fetchWithFallback('races');
        });
    }

    /**
     * Fetch with intelligent fallback mechanism
     */
    private function fetchWithFallback(string $endpoint, string $id = null): Collection
    {
        $primarySource = $this->apiSources['primary'];

        try {
            $data = $this->fetchFromSource($primarySource, $endpoint, $id);

            if ($data->isNotEmpty()) {
                $this->logger->info("Successfully fetched {$endpoint} from primary source: {$primarySource}");
                return $data;
            }
        } catch (ExternalAPIException $e) {
            $this->logger->warning("Primary source {$primarySource} failed for {$endpoint}: " . $e->getMessage());
        }

        // Try fallback sources
        foreach ($this->apiSources['fallback'] as $fallbackSource) {
            try {
                $data = $this->fetchFromSource($fallbackSource, $endpoint, $id);

                if ($data->isNotEmpty()) {
                    $this->logger->info("Successfully fetched {$endpoint} from fallback source: {$fallbackSource}");
                    return $data;
                }
            } catch (ExternalAPIException $e) {
                $this->logger->warning("Fallback source {$fallbackSource} failed for {$endpoint}: " . $e->getMessage());
                continue;
            }
        }

        // If all sources fail, return cached data if available
        $staleData = $this->cache->get("stale_{$endpoint}_" . ($id ?? 'all'));
        if ($staleData) {
            $this->logger->warning("All API sources failed, returning stale data for {$endpoint}");
            return collect($staleData);
        }

        throw new AllAPISourcesFailedException("All API sources failed for endpoint: {$endpoint}");
    }

    /**
     * Fetch data from specific source
     */
    private function fetchFromSource(string $source, string $endpoint, string $id = null): Collection
    {
        $config = config("external_apis.{$source}");

        if (!$config || !$config['enabled']) {
            throw new ExternalAPIException("API source {$source} is not configured or disabled");
        }

        $url = $this->buildApiUrl($config, $endpoint, $id);

        $response = $this->httpClient->timeout(10)->get($url, [
            'headers' => $config['headers'] ?? [],
            'query' => $config['default_params'] ?? []
        ]);

        if (!$response->successful()) {
            throw new ExternalAPIException("API request failed with status: " . $response->status());
        }

        $data = $response->json();

        // Validate and normalize data
        $normalizedData = $this->normalizeApiData($source, $endpoint, $data);

        // Cache stale data for fallback
        $this->cache->put("stale_{$endpoint}_" . ($id ?? 'all'), $normalizedData, 86400);

        return collect($normalizedData);
    }

    /**
     * Build API URL based on source configuration
     */
    private function buildApiUrl(array $config, string $endpoint, string $id = null): string
    {
        $baseUrl = rtrim($config['base_url'], '/');
        $endpointPath = $config['endpoints'][$endpoint] ?? $endpoint;

        if ($id) {
            $endpointPath = str_replace('{id}', $id, $endpointPath);
        }

        return "{$baseUrl}/{$endpointPath}";
    }

    /**
     * Normalize data from different API sources
     */
    private function normalizeApiData(string $source, string $endpoint, array $data): array
    {
        $normalizer = match ($source) {
            'umapyoi' => new UmapyoiNormalizer(),
            'umamusumedb' => new UmamusumeDBNormalizer(),
            'umalator' => new UmalatorNormalizer(),
            default => new DefaultNormalizer()
        };

        return $normalizer->normalize($endpoint, $data);
    }
}
```

#### 7.2.2 API Configuration

```php
<?php

// config/external_apis.php
return [
    'umapyoi' => [
        'enabled' => env('UMAPYOI_ENABLED', true),
        'base_url' => 'https://api.umapyoi.net/api/v1',
        'timeout' => 10,
        'retry_attempts' => 3,
        'headers' => [
            'Accept' => 'application/json',
            'User-Agent' => 'UmamusumeCareerPlanner/1.0'
        ],
        'endpoints' => [
            'characters' => 'characters/{id?}',
            'support_cards' => 'support-cards/{id?}',
            'races' => 'races',
            'skills' => 'skills/{id?}'
        ],
        'rate_limit' => [
            'requests_per_minute' => 60,
            'burst_limit' => 10
        ]
    ],

    'umamusumedb' => [
        'enabled' => env('UMAMUSUMEDB_ENABLED', true),
        'base_url' => 'https://umamusumedb.com/api',
        'timeout' => 15,
        'retry_attempts' => 2,
        'headers' => [
            'Accept' => 'application/json'
        ],
        'endpoints' => [
            'characters' => 'characters/{id?}',
            'support_cards' => 'cards/{id?}',
            'training_calculator' => 'calculator/training'
        ],
        'rate_limit' => [
            'requests_per_minute' => 30,
            'burst_limit' => 5
        ]
    ],

    'umalator' => [
        'enabled' => env('UMALATOR_ENABLED', false), // Requires verification
        'base_url' => 'https://umalator.com/api',
        'timeout' => 10,
        'retry_attempts' => 2,
        'endpoints' => [
            'race_simulation' => 'simulate/race',
            'training_optimization' => 'optimize/training'
        ]
    ]
];
```

### 7.3 API Security and Rate Limiting

#### 7.3.1 Authentication and Authorization

```php
<?php

namespace App\Http\Middleware;

/**
 * API Rate Limiting Middleware
 * Implements sophisticated rate limiting for different API endpoints
 */
class APIRateLimiter
{
    private array $rateLimits = [
        'ai_endpoints' => ['requests' => 30, 'window' => 60], // 30 requests per minute
        'character_endpoints' => ['requests' => 60, 'window' => 60], // 60 requests per minute
        'general_endpoints' => ['requests' => 100, 'window' => 60], // 100 requests per minute
        'external_api_proxy' => ['requests' => 20, 'window' => 60] // 20 requests per minute
    ];

    public function handle(Request $request, Closure $next, string $category = 'general_endpoints'): Response
    {
        $user = $request->user();
        $limits = $this->rateLimits[$category];

        $key = $this->buildRateLimitKey($user, $category, $request);
        $current = Cache::get($key, 0);

        if ($current >= $limits['requests']) {
            return response()->json([
                'success' => false,
                'message' => 'Rate limit exceeded',
                'retry_after' => $limits['window'],
                'limit' => $limits['requests'],
                'remaining' => 0
            ], 429);
        }

        Cache::put($key, $current + 1, $limits['window']);

        $response = $next($request);

        // Add rate limit headers
        $response->headers->set('X-RateLimit-Limit', $limits['requests']);
        $response->headers->set('X-RateLimit-Remaining', max(0, $limits['requests'] - $current - 1));
        $response->headers->set('X-RateLimit-Reset', now()->addSeconds($limits['window'])->timestamp);

        return $response;
    }

    private function buildRateLimitKey($user, string $category, Request $request): string
    {
        $identifier = $user ? $user->id : $request->ip();
        return "rate_limit:{$category}:{$identifier}:" . now()->format('Y-m-d-H-i');
    }
}
```

#### 7.3.2 API Documentation Structure

```yaml
# API Documentation (OpenAPI 3.0)
openapi: 3.0.3
info:
  title: Umamusume Career Planner API
  description: AI-powered career planning API for Umamusume Pretty Derby
  version: 1.0.0
  contact:
    name: API Support
    email: api-support@umamusume-planner.com
  license:
    name: MIT
    url: https://opensource.org/licenses/MIT

servers:
  - url: https://api.umamusume-planner.com/v1
    description: Production server
  - url: https://staging-api.umamusume-planner.com/v1
    description: Staging server

security:
  - BearerAuth: []

paths:
  /characters:
    get:
      summary: List user characters
      tags: [Characters]
      parameters:
        - name: scenario_id
          in: query
          schema:
            type: integer
        - name: is_active
          in: query
          schema:
            type: boolean
        - name: per_page
          in: query
          schema:
            type: integer
            minimum: 1
            maximum: 100
            default: 15
      responses:
        '200':
          description: Characters retrieved successfully
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/CharacterListResponse'
        '401':
          $ref: '#/components/responses/Unauthorized'
        '429':
          $ref: '#/components/responses/RateLimited'

    post:
      summary: Create new character
      tags: [Characters]
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/CreateCharacterRequest'
      responses:
        '201':
          description: Character created successfully
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/CharacterResponse'
        '422':
          $ref: '#/components/responses/ValidationError'

  /ai/chat:
    post:
      summary: Send message to AI assistant
      tags: [AI]
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/AIChatRequest'
      responses:
        '200':
          description: AI response generated successfully
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/AIChatResponse'
        '429':
          $ref: '#/components/responses/RateLimited'
        '503':
          $ref: '#/components/responses/ServiceUnavailable'

components:
  securitySchemes:
    BearerAuth:
      type: http
      scheme: bearer
      bearerFormat: JWT

  schemas:
    Character:
      type: object
      properties:
        id:
          type: integer
        uuid:
          type: string
          format: uuid
        name:
          type: string
        current_turn:
          type: integer
        current_stats:
          type: object
        is_active:
          type: boolean
        created_at:
          type: string
          format: date-time

    CreateCharacterRequest:
      type: object
      required:
        - character_template_id
        - name
      properties:
        character_template_id:
          type: integer
        name:
          type: string
          minLength: 1
          maxLength: 100
        scenario_id:
          type: integer
        goals:
          type: object

    AIChatRequest:
      type: object
      required:
        - message
      properties:
        message:
          type: string
          minLength: 1
          maxLength: 1000
        character_id:
          type: integer
        context:
          type: object
        type:
          type: string
          enum: [recommendation, analysis, planning, general, optimization]

  responses:
    Unauthorized:
      description: Authentication required
      content:
        application/json:
          schema:
            type: object
            properties:
              success:
                type: boolean
                example: false
              message:
                type: string
                example: "Unauthenticated"

    RateLimited:
      description: Rate limit exceeded
      headers:
        X-RateLimit-Limit:
          schema:
            type: integer
        X-RateLimit-Remaining:
          schema:
            type: integer
        X-RateLimit-Reset:
          schema:
            type: integer
      content:
        application/json:
          schema:
            type: object
            properties:
              success:
                type: boolean
                example: false
              message:
                type: string
                example: "Rate limit exceeded"
              retry_after:
                type: integer
```

---

#### 2.2.1 Repository Pattern Implementation

```php
<?php

namespace App\Repositories\Contracts;

interface CharacterRepositoryInterface
{
    public function findById(int $id): ?Character;
    public function findByUserId(int $userId): Collection;
    public function store(Character $character): Character;
    public function update(Character $character): Character;
    public function delete(int $id): bool;
    public function getWithRelations(int $id, array $relations = []): ?Character;
}

namespace App\Repositories;

class EloquentCharacterRepository implements CharacterRepositoryInterface
{
    public function __construct(private Character $model) {}

    public function findById(int $id): ?Character
    {
        return $this->model->with(['aptitudes', 'factors', 'skills'])->find($id);
    }

    public function findByUserId(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)
            ->with(['aptitudes', 'currentStats'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function store(Character $character): Character
    {
        $character->save();
        return $character->load(['aptitudes', 'factors']);
    }

    public function update(Character $character): Character
    {
        $character->save();
        event(new CharacterUpdated($character));
        return $character;
    }
}
```

#### 2.2.2 Service Layer Pattern

```php
<?php

namespace App\Services;

class TrainingOptimizationService
{
    public function __construct(
        private CharacterRepositoryInterface $characterRepo,
        private TrainingPredictionEngine $predictionEngine,
        private CacheManager $cache,
        private EventDispatcher $events
    ) {}

    public function optimizeTrainingSequence(
        Character $character,
        TrainingGoals $goals
    ): TrainingRecommendation {
        $cacheKey = "training_optimization_{$character->id}_{$goals->hash()}";

        return $this->cache->remember($cacheKey, 300, function () use ($character, $goals) {
            $recommendation = $this->predictionEngine->calculateOptimalSequence($character, $goals);

            $this->events->dispatch(new TrainingRecommendationGenerated($character, $recommendation));

            return $recommendation;
        });
    }

    public function predictTrainingOutcome(
        Character $character,
        TrainingOption $option,
        array $supportCards = []
    ): TrainingPrediction {
        return $this->predictionEngine->predict($character, $option, $supportCards);
    }
}
```

#### 2.2.3 CQRS Pattern Implementation

```php
<?php

namespace App\Commands;

class UpdateCharacterStatsCommand
{
    public function __construct(
        public readonly int $characterId,
        public readonly array $stats,
        public readonly string $source,
        public readonly ?int $turnNumber = null
    ) {}
}

namespace App\Handlers\Commands;

class UpdateCharacterStatsHandler
{
    public function __construct(
        private CharacterRepositoryInterface $characterRepo,
        private EventDispatcher $events
    ) {}

    public function handle(UpdateCharacterStatsCommand $command): void
    {
        $character = $this->characterRepo->findById($command->characterId);

        if (!$character) {
            throw new CharacterNotFoundException($command->characterId);
        }

        $character->updateStats($command->stats, $command->turnNumber);
        $this->characterRepo->update($character);

        $this->events->dispatch(new CharacterStatsUpdated(
            $character,
            $command->source,
            $command->stats
        ));
    }
}

namespace App\Queries;

class GetCharacterProgressQuery
{
    public function __construct(
        public readonly int $characterId,
        public readonly ?int $careerId = null
    ) {}
}

namespace App\Handlers\Queries;

class GetCharacterProgressHandler
{
    public function __construct(
        private CharacterRepositoryInterface $characterRepo,
        private CareerRepositoryInterface $careerRepo
    ) {}

    public function handle(GetCharacterProgressQuery $query): CharacterProgress
    {
        $character = $this->characterRepo->getWithRelations(
            $query->characterId,
            ['careers', 'stats', 'goals']
        );

        return new CharacterProgress($character, $query->careerId);
    }
}
```

### 2.3 Component Architecture

#### 2.3.1 Core Components

```php
<?php

namespace App\Components;

/**
 * Training Optimization Engine
 * Handles all training prediction and optimization logic
 */
class TrainingOptimizationEngine
{
    public function __construct(
        private StatCalculationService $statCalculator,
        private SupportCardService $supportCardService,
        private SkillHintService $skillHintService,
        private ScenarioService $scenarioService
    ) {}

    public function calculateOptimalTraining(Character $character): TrainingRecommendation
    {
        $availableOptions = $this->getAvailableTrainingOptions($character);
        $predictions = [];

        foreach ($availableOptions as $option) {
            $predictions[] = $this->predictTrainingOutcome($character, $option);
        }

        return $this->rankPredictions($predictions);
    }

    private function predictTrainingOutcome(Character $character, TrainingOption $option): TrainingPrediction
    {
        $baseStats = $character->getCurrentStats();
        $supportEffects = $this->supportCardService->calculateEffects($character->getSupportCards());
        $scenarioEffects = $this->scenarioService->getActiveEffects($character);

        return new TrainingPrediction(
            option: $option,
            predictedStats: $this->statCalculator->calculateGains($baseStats, $option, $supportEffects),
            skillHints: $this->skillHintService->predictHints($character, $option),
            riskFactors: $this->calculateRiskFactors($character, $option),
            efficiency: $this->calculateEfficiency($option, $supportEffects)
        );
    }

    private function rankPredictions(array $predictions): TrainingRecommendation
    {
        usort($predictions, fn($a, $b) => $b->efficiency <=> $a->efficiency);

        return new TrainingRecommendation(
            primary: $predictions[0],
            alternatives: array_slice($predictions, 1, 3),
            reasoning: $this->generateRecommendationReasoning($predictions[0])
        );
    }
}

/**
 * Character Management Component
 * Handles character creation, updates, and lifecycle management
 */
class CharacterManagementComponent
{
    public function __construct(
        private CharacterRepositoryInterface $characterRepo,
        private FactorService $factorService,
        private AptitudeService $aptitudeService,
        private ValidationService $validator
    ) {}

    public function createCharacter(CreateCharacterRequest $request): Character
    {
        $this->validator->validate($request);

        $character = new Character([
            'user_id' => $request->userId,
            'name' => $request->name,
            'base_character_id' => $request->baseCharacterId,
            'scenario_id' => $request->scenarioId
        ]);

        $character = $this->characterRepo->store($character);

        // Initialize character data
        $this->initializeCharacterAptitudes($character, $request->aptitudes);
        $this->initializeCharacterFactors($character, $request->factors);
        $this->initializeCharacterStats($character);

        return $character;
    }

    private function initializeCharacterAptitudes(Character $character, array $aptitudes): void
    {
        foreach ($aptitudes as $type => $rank) {
            $character->aptitudes()->create([
                'type' => $type,
                'rank' => $rank
            ]);
        }
    }
}

/**
 * AI Integration Component with MCP Server Support
 * Manages hybrid AI processing with Ollama and AWS Bedrock via MCP servers
 */
class AIIntegrationComponent
{
    public function __construct(
        private OllamaService $ollama,
        private MCPBedrockService $bedrockMCP,
        private AIRoutingService $router,
        private CacheManager $cache
    ) {}

    public function processTrainingRecommendation(Character $character, array $context): AIRecommendation
    {
        $complexity = $this->assessComplexity($context);
        $service = $this->router->selectService($complexity);

        $cacheKey = "ai_recommendation_{$character->id}_" . md5(serialize($context));

        return $this->cache->remember($cacheKey, 600, function () use ($service, $character, $context) {
            if ($service === 'bedrock') {
                // Use MCP Bedrock server for cloud processing
                return $this->bedrockMCP->generateRecommendation($character, $context);
            }

            // Use local Ollama for simple processing
            return $this->ollama->generateRecommendation($character, $context);
        });
    }

    public function analyzeScreenshot(UploadedFile $screenshot): ScreenshotAnalysis
    {
        // Always use cloud service for OCR processing via MCP
        return $this->bedrockMCP->analyzeGameScreenshot($screenshot);
    }

    private function assessComplexity(array $context): string
    {
        $factors = [
            'character_count' => count($context['characters'] ?? []),
            'scenario_complexity' => $context['scenario']['complexity'] ?? 1,
            'support_cards' => count($context['support_cards'] ?? []),
            'goals_count' => count($context['goals'] ?? [])
        ];

        $score = array_sum($factors);

        return match (true) {
            $score <= 5 => 'local',
            $score <= 15 => 'hybrid',
            default => 'bedrock'
        };
    }
}

---

## 3. MCP Server Integration

### 3.1 MCP Architecture Overview

The system leverages Model Context Protocol (MCP) servers to enhance functionality through standardized interfaces for AI services, AWS infrastructure, and development tools.

```

┌─────────────────────────────────────────────────────────────────┐
│                    MCP CLIENT LAYER                             │
├─────────────────────────────────────────────────────────────────┤
│  Laravel Application with MCP Client Integration                │
│  ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐   │
│  │   MCP Manager   │ │   Server Pool   │ │   Tool Router   │   │
│  │   (Config)      │ │   (Connections) │ │   (Dispatch)    │   │
│  └─────────────────┘ └─────────────────┘ └─────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                    MCP SERVER LAYER                             │
├─────────────────────────────────────────────────────────────────┤
│  ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐   │
│  │   AI Services   │ │   AWS Services  │ │   Utilities     │   │
│  │   strands-agents│ │   awspricing    │ │   context7      │   │
│  │   agentcore-mcp │ │   awsknowledge  │ │   fetch         │   │
│  └─────────────────┘ └─────────────────┘ └─────────────────┘   │
└─────────────────────────────────────────────────────────────────┘

```text

### 3.2 Available MCP Servers

#### 3.2.1 AI and Agent Services

**Strands Agent SDK (strands-agents)**:
- **Purpose**: Build AI agents with multiple model support
- **Models**: Bedrock, Anthropic, OpenAI, Gemini, Llama
- **Use Cases**: Training optimization agents, career planning agents, multi-turn conversations
- **Integration**: Character-specific agent creation, workflow management

**AgentCore MCP Server (agentcore-mcp-server)**:
- **Purpose**: Amazon Bedrock AgentCore platform integration
- **Capabilities**: Agent deployment, scaling, monitoring
- **Use Cases**: Production AI agent management, performance optimization
- **Integration**: Enterprise-grade agent operations

#### 3.2.2 AWS Infrastructure Services

**AWS Pricing (awspricing)**:
- **Purpose**: Real-time AWS service pricing information
- **Use Cases**: Bedrock cost tracking, budget management, cost optimization
- **Integration**: AI usage cost calculation, spending alerts

**AWS Knowledge (awsknowledge)**:
- **Purpose**: AWS service documentation and best practices
- **Use Cases**: Infrastructure guidance, service recommendations
- **Integration**: Deployment optimization, architecture decisions

**AWS API (awsapi)**:
- **Purpose**: Direct AWS service API access
- **Use Cases**: Resource management, service configuration
- **Integration**: Infrastructure automation, monitoring

**AWS IAC MCP Server (awslabs.aws-iac-mcp-server)**:
- **Purpose**: Infrastructure-as-Code tools for CDK and CloudFormation
- **Use Cases**: Template validation, resource deployment, compliance checking
- **Integration**: Deployment automation, infrastructure management

#### 3.2.3 Utility Services

**Context7 (context7)**:
- **Purpose**: Context management and data processing
- **Use Cases**: Conversation context, data transformation
- **Integration**: AI conversation management, data pipeline processing

**Fetch (fetch)**:
- **Purpose**: HTTP client for external API integration
- **Use Cases**: External API calls, data synchronization
- **Integration**: Game data APIs, community service integration

**Figma (figma)** (Optional):
- **Purpose**: Design system integration
- **Use Cases**: UI consistency, design token management
- **Integration**: Component library maintenance, design system updates

### 3.3 MCP Integration Implementation

#### 3.3.1 MCP Client Service

```php
<?php

namespace App\Services\MCP;

class MCPClientService
{
    private array $serverConnections = [];
    private array $serverConfigs;

    public function __construct()
    {
        $this->serverConfigs = config('mcp.servers');
        $this->initializeConnections();
    }

    public function call(string $server, string $tool, array $arguments = []): mixed
    {
        if (!$this->isServerAvailable($server)) {
            throw new MCPServerUnavailableException("Server {$server} is not available");
        }

        $connection = $this->getConnection($server);

        try {
            $response = $connection->callTool($tool, $arguments);

            $this->logToolCall($server, $tool, $arguments, $response);

            return $response;

        } catch (Exception $e) {
            $this->handleToolCallError($server, $tool, $e);
            throw new MCPToolCallException("Tool call failed: {$e->getMessage()}", 0, $e);
        }
    }

    public function isServerAvailable(string $server): bool
    {
        return isset($this->serverConnections[$server]) &&
               $this->serverConnections[$server]->isConnected();
    }

    private function initializeConnections(): void
    {
        foreach ($this->serverConfigs as $name => $config) {
            if ($config['enabled'] ?? true) {
                try {
                    $this->serverConnections[$name] = new MCPConnection($config);
                } catch (Exception $e) {
                    Log::warning("Failed to initialize MCP server: {$name}", [
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
    }
}
```

#### 3.3.2 AI Services Integration via MCP

```php
<?php

namespace App\Services\AI;

class MCPAIService
{
    public function __construct(
        private MCPClientService $mcpClient,
        private CostTracker $costTracker
    ) {}

    public function createTrainingAgent(Character $character): Agent
    {
        return $this->mcpClient->call('strands-agents', 'create_agent', [
            'type' => 'training_optimizer',
            'context' => [
                'character_id' => $character->id,
                'scenario' => $character->scenario->name,
                'current_stats' => $character->current_stats,
                'goals' => $character->goals,
                'support_cards' => $character->supportCards->toArray()
            ],
            'model' => 'bedrock:claude-sonnet-4.5',
            'capabilities' => [
                'training_prediction',
                'skill_optimization',
                'race_strategy',
                'career_planning'
            ]
        ]);
    }

    public function generateRecommendation(Character $character, array $context): AIRecommendation
    {
        // Use AgentCore for advanced processing
        $response = $this->mcpClient->call('agentcore-mcp-server', 'invoke_agent', [
            'agent_type' => 'career_advisor',
            'input' => [
                'character_state' => $character->toArray(),
                'training_context' => $context,
                'optimization_goals' => $character->goals
            ],
            'model' => 'claude-4.5-sonnet'
        ]);

        // Track costs
        $this->costTracker->recordUsage('claude-sonnet', $response['usage']);

        return new AIRecommendation(
            content: $response['recommendation'],
            reasoning: $response['reasoning'],
            confidence: $response['confidence'],
            model: 'agentcore:claude-sonnet-4.5',
            cost: $response['cost']
        );
    }

    public function optimizeCareerPath(Character $character, int $turnsAhead = 10): CareerOptimization
    {
        return $this->mcpClient->call('strands-agents', 'execute_workflow', [
            'workflow_type' => 'career_optimization',
            'agent_id' => $character->training_agent_id,
            'parameters' => [
                'character_state' => $character->toArray(),
                'turns_ahead' => $turnsAhead,
                'optimization_criteria' => [
                    'stat_targets' => $character->goals['stats'] ?? [],
                    'race_objectives' => $character->goals['races'] ?? [],
                    'skill_priorities' => $character->goals['skills'] ?? []
                ]
            ]
        ]);
    }
}
```

#### 3.3.3 AWS Infrastructure Integration

```php
<?php

namespace App\Services\Infrastructure;

class AWSInfrastructureService
{
    public function __construct(private MCPClientService $mcpClient) {}

    public function getBedrockPricing(string $region = 'us-east-1'): array
    {
        return $this->mcpClient->call('awspricing', 'get_pricing', [
            'service' => 'bedrock',
            'region' => $region,
            'filters' => [
                'productFamily' => 'Machine Learning'
            ]
        ]);
    }

    public function validateInfrastructure(array $template): ValidationResult
    {
        return $this->mcpClient->call('awslabs.aws-iac-mcp-server', 'validate_template', [
            'template_type' => 'cloudformation',
            'template_content' => $template,
            'validation_rules' => [
                'security_compliance',
                'cost_optimization',
                'best_practices'
            ]
        ]);
    }

    public function getServiceRecommendations(array $requirements): array
    {
        return $this->mcpClient->call('awsknowledge', 'get_recommendations', [
            'use_case' => 'web_application',
            'requirements' => $requirements,
            'optimization_goals' => ['cost', 'performance', 'security']
        ]);
    }

    public function monitorCosts(): CostReport
    {
        $pricing = $this->getBedrockPricing();
        $usage = $this->getCurrentUsage();

        return new CostReport(
            current_spend: $usage['total_cost'],
            projected_monthly: $this->projectMonthlyCosts($usage),
            recommendations: $this->getCostOptimizationTips($pricing, $usage)
        );
    }
}
```

### 3.4 MCP Configuration Management

#### 3.4.1 Configuration Structure

```php
<?php

// config/mcp.php
return [
    'servers' => [
        'strands-agents' => [
            'enabled' => env('MCP_STRANDS_ENABLED', true),
            'command' => 'uvx',
            'args' => ['strands-agents@latest'],
            'capabilities' => [
                'agent_creation',
                'workflow_management',
                'multi_model_support'
            ],
            'models' => [
                'bedrock:claude-opus-4.5',
                'bedrock:claude-sonnet-4.5',
                'bedrock:claude-haiku-4.5',
                'bedrock:nova-2-lite',
                'bedrock:nova-2-pro'
            ],
            'timeout' => 30,
            'retry_attempts' => 3
        ],

        'agentcore-mcp-server' => [
            'enabled' => env('MCP_AGENTCORE_ENABLED', true),
            'command' => 'uvx',
            'args' => ['agentcore-mcp-server@latest'],
            'capabilities' => [
                'agent_deployment',
                'performance_monitoring',
                'scaling_management'
            ],
            'timeout' => 60,
            'retry_attempts' => 2
        ],

        'awspricing' => [
            'enabled' => env('MCP_AWS_PRICING_ENABLED', true),
            'command' => 'uvx',
            'args' => ['awspricing@latest'],
            'capabilities' => [
                'pricing_lookup',
                'cost_calculation',
                'service_comparison'
            ],
            'timeout' => 15,
            'retry_attempts' => 3
        ],

        'awsknowledge' => [
            'enabled' => env('MCP_AWS_KNOWLEDGE_ENABLED', true),
            'command' => 'uvx',
            'args' => ['awsknowledge@latest'],
            'capabilities' => [
                'documentation_search',
                'best_practices',
                'service_recommendations'
            ],
            'timeout' => 20,
            'retry_attempts' => 2
        ],

        'awslabs.aws-iac-mcp-server' => [
            'enabled' => env('MCP_AWS_IAC_ENABLED', false), // Optional
            'command' => 'uvx',
            'args' => ['awslabs.aws-iac-mcp-server@latest'],
            'capabilities' => [
                'template_validation',
                'resource_deployment',
                'compliance_checking'
            ],
            'timeout' => 45,
            'retry_attempts' => 2
        ]
    ],

    'fallback_strategy' => 'graceful_degradation',
    'health_check_interval' => 300, // 5 minutes
    'connection_timeout' => 10,
    'max_concurrent_calls' => 5
];
```

#### 3.4.2 Health Monitoring

```php
<?php

namespace App\Services\MCP;

class MCPHealthMonitor
{
    public function __construct(
        private MCPClientService $mcpClient,
        private CacheManager $cache
    ) {}

    public function checkServerHealth(): array
    {
        $servers = config('mcp.servers');
        $healthStatus = [];

        foreach ($servers as $name => $config) {
            if (!($config['enabled'] ?? true)) {
                $healthStatus[$name] = ['status' => 'disabled'];
                continue;
            }

            $cacheKey = "mcp_health:{$name}";

            $status = $this->cache->remember($cacheKey, 300, function () use ($name) {
                return $this->performHealthCheck($name);
            });

            $healthStatus[$name] = $status;
        }

        return $healthStatus;
    }

    private function performHealthCheck(string $server): array
    {
        try {
            $startTime = microtime(true);

            // Attempt a simple ping or health check call
            $response = $this->mcpClient->call($server, 'health_check', []);

            $responseTime = (microtime(true) - $startTime) * 1000;

            return [
                'status' => 'healthy',
                'response_time' => $responseTime,
                'last_check' => now()->toISOString(),
                'capabilities' => $response['capabilities'] ?? []
            ];

        } catch (Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'last_check' => now()->toISOString()
            ];
        }
    }

    public function getServerMetrics(string $server): array
    {
        return [
            'total_calls' => $this->cache->get("mcp_metrics:{$server}:calls", 0),
            'success_rate' => $this->calculateSuccessRate($server),
            'avg_response_time' => $this->getAverageResponseTime($server),
            'last_error' => $this->cache->get("mcp_metrics:{$server}:last_error")
        ];
    }
}
```

---

## Conclusion

This Software Design Specification provides a comprehensive blueprint for the Uma Musume Career Planner application. The design emphasizes:

- **Scalable Architecture**: Modular design supporting growth and feature expansion
- **AI Integration**: Hybrid processing with local and cloud-based AI services
- **MCP Integration**: Standardized interfaces for enhanced functionality
- **Performance**: Optimized database design and caching strategies
- **Security**: Comprehensive authentication and authorization framework
- **User Experience**: Modern, responsive interface design

The implementation should follow the guidelines and patterns outlined in this document to ensure consistency, maintainability, and optimal performance.

---

## Document Control

| Version | Date | Author | Changes |
| ------- | ---- | ------ | ------- |
| 1.0 | 2026-01-10 | Development Team | Initial SDS document creation |
| 2.0 | 2026-01-11 | Development Team | Aligned with spec requirements, MCP server integration, and modern architecture |

---

*This document provides the comprehensive technical design for the Umamusume Pretty Derby Career Planner system, covering architecture, database design, user interfaces, APIs, and implementation strategies.*
