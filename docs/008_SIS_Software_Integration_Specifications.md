# Software Integration Specifications (SIS)

## Umamusume Pretty Derby Career Planner

**Document Version**: 2.0  
**Date**: 2026-01-12  
**Project**: UmamusumeCareerPlanner  
**Author**: Development Team  
**Status**: Final  
**Updated**: Complete specifications aligned with Laravel 12, Tailwind CSS v4, Hybrid AI Processing, and MCP integration

---

## Table of Contents

1. [Introduction](#1-introduction)
2. [Integration Requirements](#2-integration-requirements)
3. [API Integration Specifications](#3-api-integration-specifications)
4. [AI Services Integration Specifications](#4-ai-services-integration-specifications)
5. [MCP Server Integration Specifications](#5-mcp-server-integration-specifications)
6. [Database Integration Specifications](#6-database-integration-specifications)
7. [Frontend Integration Specifications](#7-frontend-integration-specifications)
8. [Security Integration Specifications](#8-security-integration-specifications)

---

## 1. Introduction

### 1.1 Purpose

This Software Integration Specifications (SIS) document provides detailed technical specifications for all integration aspects of the Umamusume Career Planner system. It defines precise requirements, protocols, data formats, error handling, and performance criteria for seamless integration between system components and external services.

### 1.2 Scope

The specifications cover:

- **API Integration**: External game data APIs, community services, fallback mechanisms
- **AI Services**: Ollama local models, AWS Bedrock cloud services, Hybrid AI Processing
- **MCP Servers**: Model Context Protocol integration for enhanced functionality
- **Database Systems**: MySQL 8.0+ primary storage, Redis 7.0+ caching, data synchronization
- **Frontend Technologies**: Progressive Web App, real-time updates, accessibility
- **Security Protocols**: Authentication, authorization, data protection, privacy
- **Performance Optimization**: Caching strategies, load balancing, resource management
- **Monitoring Systems**: Application performance monitoring, logging, alerting

### 1.3 Integration Standards

#### 1.3.1 Communication Protocols

| Protocol       | Usage                                                     | Security             |
|----------------|-----------------------------------------------------------|----------------------|
| HTTP/HTTPS     | RESTful API communication                                 | TLS 1.3 required     |
| WebSocket      | Real-time bidirectional communication                     | WSS encryption       |
| gRPC           | High-performance internal service communication (future)  | mTLS                 |
| Message Queues | Asynchronous processing                                   | Redis/Laravel Queues |

#### 1.3.2 Data Formats

| Format           | Usage                                    | Validation             |
|------------------|------------------------------------------|------------------------|
| JSON             | Primary data exchange                    | JSON Schema validation |
| XML              | Legacy system compatibility              | XSD validation         |
| Protocol Buffers | High-performance binary (future)         | Proto definition       |
| MessagePack      | Performance-critical paths               | Schema validation      |

#### 1.3.3 Authentication Standards

| Standard  | Usage                                | Implementation          |
|-----------|--------------------------------------|-------------------------|
| OAuth 2.0 | External service authentication      | Authorization Code flow |
| JWT       | Stateless token-based authentication | Laravel Sanctum         |
| API Keys  | Service-to-service authentication    | Encrypted storage       |
| mTLS      | High-security integrations           | Certificate-based       |

---

## 2. Integration Requirements

### 2.1 Functional Requirements

#### 2.1.1 External API Integration

**REQ-INT-001**: External Game Data API Integration

- The system SHALL integrate with umapyoi.net API for character, skill, and support card data
- The system SHALL implement intelligent fallback to UmamusumeDB.com when primary API unavailable
- The system SHALL cache API responses using Redis 7.0+ with configurable TTL (24 hours for static data, 1 hour for dynamic)
- The system SHALL respect API rate limits (100 requests/minute for umapyoi.net) with exponential backoff

**REQ-INT-002**: Community Data Integration

- The system SHALL integrate with community meta tier databases with data validation
- The system SHOULD support bidirectional data exchange for strategy sharing (optional, user-controlled)
- The system SHALL implement conflict resolution for contradictory data from multiple sources
- The system SHALL maintain data provenance tracking for all external data sources

#### 2.1.2 AI Services Integration

**REQ-INT-003**: Hybrid AI Processing

- The system SHALL implement local Ollama processing as primary AI inference engine
- The system SHALL provide AWS Bedrock cloud fallback with Intelligent Routing based on complexity
- The system SHALL maintain conversation context across local/cloud model switches
- The system SHALL implement cost tracking and budget management for cloud AI usage

**REQ-INT-004**: MCP Server Integration

- The system SHALL integrate with AWS Bedrock MCP servers for cloud AI access
- The system SHALL utilize strands-agents MCP server for advanced agent capabilities
- The system SHALL implement secure credential management for MCP server authentication
- The system SHALL provide fallback mechanisms when MCP servers are unavailable

### 2.2 Non-Functional Requirements

#### 2.2.1 Performance Requirements

**REQ-INT-005**: Response Time Requirements

| Operation                          | Target      | SLA                       |
|------------------------------------|-------------|---------------------------|
| External API calls                 | ≤5 seconds  | 95% success rate          |
| Local AI processing (Ollama)       | ≤3 seconds  | Standard requests         |
| Cloud AI processing (AWS Bedrock)  | ≤10 seconds | Including network latency |
| Database queries                   | ≤500ms      | 95% of operations         |
| WebSocket message delivery         | ≤100ms      | Real-time features        |

**REQ-INT-006**: Throughput Requirements

| Metric                           | Target | Notes               |
|----------------------------------|--------|---------------------|
| Concurrent API requests          | 100    | Without degradation |
| AI requests per minute           | 50     | Peak usage          |
| Database operations per second   | 1000   | Combined read/write |
| WebSocket connections            | 500    | Concurrent users    |

#### 2.2.2 Reliability Requirements

**REQ-INT-007**: Availability Requirements

| Service                  | Target Uptime | Degradation Strategy           |
|--------------------------|---------------|--------------------------------|
| External API integration | 99.5%         | Graceful degradation to cache  |
| Local AI processing      | 99.9%         | Primary processing path        |
| Database integration     | 99.95%        | Automatic failover             |
| Real-time features       | 99%           | Message delivery success       |

**REQ-INT-008**: Error Handling Requirements

- The system SHALL implement Circuit Breaker Pattern for external service failures
- The system SHALL provide meaningful error messages with recovery suggestions
- The system SHALL log all integration errors with sufficient context for debugging
- The system SHALL implement automatic retry with exponential backoff for transient failures

---

## 3. API Integration Specifications

### 3.1 External Game Data APIs

#### 3.1.1 umapyoi.net API Specification

**Base Configuration**:

```yaml
base_url: https://api.umapyoi.net/api/v1/
authentication: none
rate_limit: 100 requests/minute, 1000 requests/hour
timeout: 30 seconds
retry_policy: exponential_backoff (1s, 2s, 4s, 8s, 16s)
cache_ttl: 86400 seconds (24 hours)
```

**Endpoint Specifications**:

| Endpoint         | Method | Parameters                            | Cache TTL |
|------------------|--------|---------------------------------------|-----------|
| `/characters`    | GET    | page, per_page, rarity, updated_since | 24 hours  |
| `/skills`        | GET    | category, type                        | 24 hours  |
| `/support-cards` | GET    | type, rarity                          | 24 hours  |
| `/news`          | GET    | page, per_page                        | 1 hour    |

**Implementation Specification**:

```php
<?php

namespace App\Integrations\APIs;

use App\Contracts\GameDataAPIInterface;

class UmapyoiAPIClient implements GameDataAPIInterface
{
    private const BASE_URL = 'https://api.umapyoi.net/api/v1/';
    private const RATE_LIMIT_KEY = 'umapyoi_api_rate_limit';
    private const CACHE_PREFIX = 'umapyoi_cache:';
    
    public function __construct(
        private HttpClient $client,
        private CacheManager $cache,
        private RateLimiter $rateLimiter,
        private SchemaValidator $validator,
        private Logger $logger
    ) {
        $this->client->setBaseUrl(self::BASE_URL);
        $this->client->setTimeout(30);
    }
    
    public function getCharacters(array $params = []): APIResponse
    {
        $cacheKey = self::CACHE_PREFIX . 'characters:' . md5(serialize($params));
        
        return $this->cache->remember($cacheKey, 86400, function () use ($params) {
            return $this->makeRequest('GET', 'characters', $params);
        });
    }
    
    private function makeRequest(string $method, string $endpoint, array $params = []): APIResponse
    {
        // Check rate limit
        if (!$this->rateLimiter->attempt(self::RATE_LIMIT_KEY, 100, 60)) {
            throw new RateLimitExceededException('umapyoi.net API rate limit exceeded');
        }
        
        try {
            $response = $this->client->request($method, $endpoint, [
                'query' => $params,
                'headers' => [
                    'Accept' => 'application/json',
                    'User-Agent' => 'UmamusumeCareerPlanner/1.0'
                ]
            ]);
            
            $data = $response->json();
            
            // Validate response schema
            if (!$this->validator->validate($data, $this->getSchema($endpoint))) {
                throw new InvalidResponseException('Response schema validation failed');
            }
            
            return new APIResponse(
                success: true,
                data: $data,
                statusCode: $response->getStatusCode(),
                headers: $response->getHeaders()
            );
            
        } catch (RequestException $e) {
            $this->logger->error('umapyoi.net API request failed', [
                'endpoint' => $endpoint,
                'params' => $params,
                'error' => $e->getMessage(),
                'status_code' => $e->getResponse()?->getStatusCode()
            ]);
            
            throw new APIException("API request failed: {$e->getMessage()}", $e->getCode(), $e);
        }
    }
}
```

#### 3.1.2 UmamusumeDB.com API Specification (Conditional)

**Base Configuration**:

```yaml
base_url: https://umamusumedb.com/api/v1/
authentication: api_key (to be determined)
rate_limit: TBD (requires verification)
timeout: 30 seconds
retry_policy: exponential_backoff (1s, 2s, 4s, 8s, 16s)
cache_ttl: 3600 seconds (1 hour)
verification_required: true
status: ⚠️ Requires verification during implementation
```

**Fallback Strategy Implementation**:

```php
<?php

namespace App\Integrations\APIs;

class GameDataAPIManager
{
    private array $providers;
    private CircuitBreaker $circuitBreaker;
    
    public function __construct()
    {
        $this->providers = [
            'primary' => new UmapyoiAPIClient(),
            'secondary' => new UmamusumeDBClient(),
            'fallback' => new LocalDataProvider()
        ];
    }
    
    public function getCharacters(array $params = []): APIResponse
    {
        foreach ($this->providers as $name => $provider) {
            try {
                if ($this->circuitBreaker->isAvailable($name)) {
                    return $provider->getCharacters($params);
                }
            } catch (APIException $e) {
                $this->circuitBreaker->recordFailure($name);
                Log::warning("Provider {$name} failed", ['error' => $e->getMessage()]);
                continue;
            }
        }
        
        throw new AllProvidersFailedException('All API providers are unavailable');
    }
}
```

### 3.2 Community Data Integration Specifications

#### 3.2.1 Meta Tier Data Sources

**GameWith Integration**:

```yaml
source: GameWith
method: web_scraping
url: https://gamewith.jp/uma-musume/
update_frequency: daily
data_types: [tier_lists, character_rankings, support_card_rankings]
validation: community_consensus_scoring
confidence_threshold: 0.7
```

**Reddit API Integration**:

```yaml
source: Reddit
method: api
endpoints: 
  - /r/UmaMusume/hot
  - /r/UmaMusume/search
authentication: oauth2
rate_limit: 60 requests/minute
data_types: [discussions, tier_opinions, strategy_guides]
sentiment_analysis: enabled
```

---

## 4. AI Services Integration Specifications

### 4.1 Local AI Integration (Ollama)

#### 4.1.1 Model Configuration Specifications

```yaml
ollama_models:
  llama3.3:
    context_length: 128000
    temperature: 0.7
    top_p: 0.9
    use_cases: [general_advice, strategy_planning, character_analysis]
    memory_requirement: 8GB
    performance_target: <3s response time
    priority: ★★★★★
    
  mistral:
    context_length: 32000
    temperature: 0.6
    top_p: 0.85
    use_cases: [quick_responses, simple_calculations, basic_recommendations]
    memory_requirement: 4GB
    performance_target: <1s response time
    priority: ★★★★
    
  qwen2.5:
    context_length: 32000
    temperature: 0.8
    top_p: 0.9
    use_cases: [multilingual_support, japanese_text_processing, cultural_context]
    memory_requirement: 6GB
    performance_target: <2s response time
    priority: ★★★
```

#### 4.1.2 Ollama Service Implementation

```php
<?php

namespace App\Integrations\AI;

use Cloudstudio\OllamaLaravel\Facades\OllamaLaravel;

class OllamaIntegration
{
    private array $models = [
        'llama3.3' => ['context' => 128000, 'use_case' => 'general'],
        'mistral' => ['context' => 32000, 'use_case' => 'fast_responses'],
        'qwen2.5' => ['context' => 32000, 'use_case' => 'multilingual']
    ];
    
    public function generateRecommendation(Character $character, array $context): AIRecommendation
    {
        $model = $this->selectOptimalModel($context);
        $prompt = $this->buildGameSpecificPrompt($character, $context);
        
        try {
            $startTime = microtime(true);
            
            $response = OllamaLaravel::agent()
                ->model($model)
                ->ask($prompt);
            
            $processingTime = (microtime(true) - $startTime) * 1000;
            
            return new AIRecommendation(
                content: $response,
                model: "ollama_{$model}",
                processingTime: $processingTime,
                confidence: $this->calculateConfidence($response),
                cost: 0.0 // Local processing is free
            );
            
        } catch (Exception $e) {
            Log::error('Ollama processing failed', [
                'model' => $model,
                'error' => $e->getMessage()
            ]);
            
            throw new AIProcessingException("Local AI processing failed: {$e->getMessage()}");
        }
    }
    
    private function selectOptimalModel(array $context): string
    {
        $complexity = $this->assessComplexity($context);
        
        return match (true) {
            $complexity <= 3 => 'mistral',    // Fast responses
            $complexity <= 7 => 'llama3.3',   // General purpose
            default => 'qwen2.5'              // Complex multilingual
        };
    }
    
    private function assessComplexity(array $context): int
    {
        $factors = [
            'context_length' => min(5, strlen($context['prompt'] ?? '') / 1000),
            'data_complexity' => count($context['data_points'] ?? []),
            'requires_reasoning' => ($context['requires_reasoning'] ?? false) ? 3 : 0,
            'multi_turn' => ($context['is_multi_turn'] ?? false) ? 2 : 0
        ];
        
        return array_sum($factors);
    }
}
```

### 4.2 Cloud AI Integration (AWS Bedrock) Specifications

#### 4.2.1 Model Pricing and Configuration

```yaml
aws_bedrock_models:
  claude-4.5-opus:
    model_id: anthropic.claude-4-5-opus-20250101-v1:0
    input_cost: $5.00 per 1M tokens
    output_cost: $25.00 per 1M tokens
    max_tokens: 4096
    use_cases: [complex_reasoning, advanced_analysis, premium_features]
    priority: ★★★★★
    
  claude-4.5-sonnet:
    model_id: anthropic.claude-4-5-sonnet-20250101-v1:0
    input_cost: $3.00 per 1M tokens
    output_cost: $15.00 per 1M tokens
    max_tokens: 4096
    use_cases: [standard_analysis, strategy_optimization, balanced_performance]
    priority: ★★★★
    
  claude-4.5-haiku:
    model_id: anthropic.claude-4-5-haiku-20250101-v1:0
    input_cost: $1.00 per 1M tokens
    output_cost: $5.00 per 1M tokens
    max_tokens: 4096
    use_cases: [quick_queries, simple_recommendations, cost_optimization]
    priority: ★★★
    
  nova-2-lite:
    model_id: amazon.nova-2-lite-v1:0
    input_cost: $0.00125 per 1K tokens
    output_cost: $0.00125 per 1K tokens
    max_tokens: 2048
    use_cases: [budget_processing, high_volume, basic_tasks]
    priority: ★★★★★ (cost-effective)
    
  nova-2-pro:
    model_id: amazon.nova-2-pro-v1:0
    input_cost: $0.008 per 1K tokens
    output_cost: $0.032 per 1K tokens
    max_tokens: 4096
    use_cases: [advanced_processing, complex_analysis]
    priority: ★★★★
```

#### 4.2.2 AWS Bedrock Service Implementation

```php
<?php

namespace App\Integrations\AI;

use Aws\BedrockRuntime\BedrockRuntimeClient;

class BedrockIntegration
{
    private array $modelPricing = [
        'claude-4.5-opus' => ['input' => 5.0, 'output' => 25.0],
        'claude-4.5-sonnet' => ['input' => 3.0, 'output' => 15.0],
        'claude-4.5-haiku' => ['input' => 1.0, 'output' => 5.0],
        'nova-2-lite' => ['input' => 0.00125, 'output' => 0.00125],
        'nova-2-pro' => ['input' => 0.008, 'output' => 0.032]
    ];
    
    public function __construct(
        private BedrockRuntimeClient $client,
        private CostTracker $costTracker,
        private BudgetManager $budgetManager
    ) {}
    
    public function generateRecommendation(
        Character $character, 
        array $context, 
        string $preferredModel = 'nova-2-lite'
    ): AIRecommendation {
        
        // Check budget before processing
        if (!$this->budgetManager->canAfford($preferredModel, $context)) {
            throw new BudgetExceededException('Insufficient budget for cloud AI processing');
        }
        
        try {
            $startTime = microtime(true);
            $prompt = $this->buildGameSpecificPrompt($character, $context);
            
            $response = $this->client->invokeModel([
                'modelId' => $this->getModelId($preferredModel),
                'body' => json_encode([
                    'anthropic_version' => 'bedrock-2023-05-31',
                    'max_tokens' => 2048,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt]
                    ]
                ])
            ]);
            
            $result = json_decode($response['body']->getContents(), true);
            $processingTime = (microtime(true) - $startTime) * 1000;
            
            // Calculate and track costs
            $cost = $this->calculateCost($preferredModel, $result['usage']);
            $this->costTracker->recordUsage($preferredModel, $cost);
            
            return new AIRecommendation(
                content: $result['content'][0]['text'],
                model: "bedrock_{$preferredModel}",
                processingTime: $processingTime,
                confidence: $this->calculateConfidence($result),
                cost: $cost
            );
            
        } catch (Exception $e) {
            Log::error('Bedrock processing failed', [
                'model' => $preferredModel,
                'error' => $e->getMessage()
            ]);
            
            throw new AIProcessingException("Cloud AI processing failed: {$e->getMessage()}");
        }
    }
    
    private function calculateCost(string $model, array $usage): float
    {
        $pricing = $this->modelPricing[$model];
        $inputCost = ($usage['input_tokens'] / 1000000) * $pricing['input'];
        $outputCost = ($usage['output_tokens'] / 1000000) * $pricing['output'];
        
        return $inputCost + $outputCost;
    }
    
    private function getModelId(string $model): string
    {
        return match ($model) {
            'claude-4.5-opus' => 'anthropic.claude-4-5-opus-20250101-v1:0',
            'claude-4.5-sonnet' => 'anthropic.claude-4-5-sonnet-20250101-v1:0',
            'claude-4.5-haiku' => 'anthropic.claude-4-5-haiku-20250101-v1:0',
            'nova-2-lite' => 'amazon.nova-2-lite-v1:0',
            'nova-2-pro' => 'amazon.nova-2-pro-v1:0',
            default => throw new InvalidModelException("Unknown model: {$model}")
        };
    }
}
```

### 4.3 Hybrid AI Routing Specifications

#### 4.3.1 Intelligent Routing Strategy

```yaml
routing_strategy:
  complexity_thresholds:
    local_only: 0-3
    cloud_lite: 4-7
    cloud_advanced: 8-10
    
  budget_thresholds:
    minimum_cloud_lite: $0.01
    minimum_cloud_advanced: $0.10
    
  performance_thresholds:
    local_response_time: 3000ms
    cloud_timeout: 10000ms
    
  fallback_chain:
    - ollama_llama3.3
    - ollama_mistral
    - bedrock_nova-2-lite
    - bedrock_claude-4.5-haiku
```

#### 4.3.2 Hybrid Router Implementation

```php
<?php

namespace App\Integrations\AI;

class HybridAIRouter
{
    public function __construct(
        private OllamaIntegration $ollama,
        private BedrockIntegration $bedrock,
        private CostTracker $costTracker,
        private PerformanceMonitor $monitor
    ) {}
    
    public function routeRequest(AIRequest $request): AIResponse
    {
        $complexity = $this->assessComplexity($request);
        $budget = $this->costTracker->getRemainingBudget();
        $localPerformance = $this->monitor->getLocalPerformance();
        
        $strategy = $this->selectStrategy($complexity, $budget, $localPerformance);
        
        return match ($strategy) {
            'local' => $this->processLocally($request),
            'cloud_lite' => $this->processWithCloud($request, 'nova-2-lite'),
            'cloud_advanced' => $this->processWithCloud($request, 'claude-4.5-sonnet'),
            'hybrid' => $this->processHybrid($request)
        };
    }
    
    private function selectStrategy(int $complexity, float $budget, array $performance): string
    {
        // Local processing preferred for privacy and cost
        if ($complexity <= 5 && $performance['avg_response_time'] < 3000) {
            return 'local';
        }
        
        // Budget-conscious cloud processing
        if ($complexity <= 8 && $budget > 0.01) {
            return 'cloud_lite';
        }
        
        // Advanced cloud processing for complex tasks
        if ($complexity > 8 && $budget > 0.10) {
            return 'cloud_advanced';
        }
        
        // Hybrid approach for balanced processing
        return 'hybrid';
    }
    
    private function processHybrid(AIRequest $request): AIResponse
    {
        // Try local first, fallback to cloud if needed
        try {
            $response = $this->processLocally($request);
            
            // Validate response quality
            if ($response->confidence >= 0.7) {
                return $response;
            }
            
            // Enhance with cloud processing if quality insufficient
            return $this->processWithCloud($request, 'nova-2-lite');
            
        } catch (AIProcessingException $e) {
            Log::info('Falling back to cloud processing', ['reason' => $e->getMessage()]);
            return $this->processWithCloud($request, 'nova-2-lite');
        }
    }
}
```

---

## 5. MCP Server Integration Specifications

### 5.1 Available MCP Servers

#### 5.1.1 AI and Agent Services

**strands-agents MCP Server**:

```yaml
server_name: strands-agents
description: Strands Agent SDK for building AI agents
capabilities:
  - agent_creation
  - workflow_management
  - multi_model_support
  - tool_orchestration
models_supported:
  - bedrock (Claude, Nova)
  - anthropic
  - openai
  - gemini
  - llama
use_cases:
  - training_optimization_agent
  - skill_analysis_agent
  - career_strategy_agent
```

**agentcore-mcp-server**:

```yaml
server_name: agentcore-mcp-server
description: Amazon Bedrock AgentCore agentic platform
capabilities:
  - agent_deployment
  - agent_orchestration
  - scaling_management
  - performance_monitoring
use_cases:
  - production_agent_deployment
  - multi_agent_workflows
  - enterprise_scaling
```

#### 5.1.2 AWS Infrastructure Services

**awspricing MCP Server**:

```yaml
server_name: awspricing
description: AWS pricing information and cost optimization
capabilities:
  - get_pricing
  - estimate_costs
  - compare_services
use_cases:
  - ai_cost_tracking
  - budget_management
  - cost_optimization
```

**awsknowledge MCP Server**:

```yaml
server_name: awsknowledge
description: AWS service documentation and best practices
capabilities:
  - search_documentation
  - get_best_practices
  - service_recommendations
use_cases:
  - infrastructure_guidance
  - optimization_recommendations
```

**awsapi MCP Server**:

```yaml
server_name: awsapi
description: Direct AWS API access for infrastructure management
capabilities:
  - invoke_aws_api
  - manage_resources
  - monitor_services
```

**awslabs.aws-iac-mcp-server**:

```yaml
server_name: awslabs.aws-iac-mcp-server
description: AWS infrastructure-as-code tools for CDK and CloudFormation
capabilities:
  - validate_template
  - check_compliance
  - troubleshoot_deployment
```

### 5.2 MCP Integration Implementation

#### 5.2.1 Strands Agent Integration

```php
<?php

namespace App\Integrations\MCP;

class StrandsAgentIntegration
{
    public function __construct(private MCPClient $mcpClient) {}
    
    public function createTrainingAgent(Character $character): Agent
    {
        return $this->mcpClient->call('strands-agents', 'create_agent', [
            'type' => 'training_optimizer',
            'context' => [
                'character_id' => $character->id,
                'scenario' => $character->scenario->name,
                'goals' => $character->goals,
                'current_stats' => $character->getCurrentStats()
            ],
            'model' => 'bedrock:claude-sonnet-4.5',
            'capabilities' => [
                'training_prediction',
                'skill_optimization',
                'race_strategy'
            ]
        ]);
    }
    
    public function optimizeCareerPath(Character $character, array $constraints): CareerOptimization
    {
        $agent = $this->createTrainingAgent($character);
        
        return $this->mcpClient->call('strands-agents', 'execute_workflow', [
            'agent_id' => $agent->id,
            'workflow' => 'career_optimization',
            'parameters' => [
                'character_state' => $character->toArray(),
                'constraints' => $constraints,
                'optimization_horizon' => 20 // turns ahead
            ]
        ]);
    }
}
```

#### 5.2.2 AgentCore Integration

```php
<?php

namespace App\Integrations\MCP;

class AgentCoreIntegration
{
    public function __construct(private MCPClient $mcpClient) {}
    
    public function deployOptimizationAgent(array $config): DeploymentResult
    {
        return $this->mcpClient->call('agentcore-mcp-server', 'deploy_agent', [
            'agent_config' => $config,
            'runtime' => 'bedrock',
            'scaling' => [
                'min_instances' => 1,
                'max_instances' => 3,
                'target_utilization' => 70
            ]
        ]);
    }
    
    public function monitorAgentPerformance(string $agentId): PerformanceMetrics
    {
        return $this->mcpClient->call('agentcore-mcp-server', 'get_metrics', [
            'agent_id' => $agentId,
            'metrics' => ['latency', 'throughput', 'error_rate', 'cost']
        ]);
    }
}
```

#### 5.2.3 AWS Pricing Integration

```php
<?php

namespace App\Integrations\MCP;

class AWSPricingIntegration
{
    public function __construct(private MCPClient $mcpClient) {}
    
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
    
    public function calculateMonthlyCosts(array $usage): CostEstimate
    {
        $pricing = $this->getBedrockPricing();
        
        return new CostEstimate(
            current: $this->calculateCurrentCosts($usage, $pricing),
            projected: $this->projectMonthlyCosts($usage, $pricing),
            breakdown: $this->getCostBreakdown($usage, $pricing)
        );
    }
}
```

### 5.3 MCP Configuration Management

#### 5.3.1 Configuration Structure

```json
{
  "mcpServers": {
    "strands-agents": {
      "command": "uvx",
      "args": ["strands-agents@latest"],
      "env": {
        "AWS_REGION": "us-east-1",
        "FASTMCP_LOG_LEVEL": "ERROR"
      },
      "disabled": false,
      "autoApprove": ["create_agent", "execute_workflow"]
    },
    "agentcore-mcp-server": {
      "command": "uvx",
      "args": ["agentcore-mcp-server@latest"],
      "env": {
        "AWS_REGION": "us-east-1"
      },
      "disabled": false,
      "autoApprove": []
    },
    "awspricing": {
      "command": "uvx",
      "args": ["awspricing@latest"],
      "disabled": false,
      "autoApprove": ["get_pricing"]
    },
    "awsknowledge": {
      "command": "uvx",
      "args": ["awsknowledge@latest"],
      "disabled": false,
      "autoApprove": ["search_documentation"]
    }
  }
}
```

#### 5.3.2 Configuration Manager Implementation

```php
<?php

namespace App\Services\MCP;

class MCPConfigurationManager
{
    private array $serverConfigs;
    
    public function __construct()
    {
        $this->loadConfigurations();
    }
    
    private function loadConfigurations(): void
    {
        // Load from workspace-level configuration
        $workspaceConfig = $this->loadWorkspaceConfig();
        
        // Load from user-level configuration
        $userConfig = $this->loadUserConfig();
        
        // Merge configurations with workspace taking precedence
        $this->serverConfigs = array_merge($userConfig, $workspaceConfig);
    }
    
    private function loadWorkspaceConfig(): array
    {
        $configPath = base_path('.kiro/settings/mcp.json');
        
        if (file_exists($configPath)) {
            return json_decode(file_get_contents($configPath), true)['mcpServers'] ?? [];
        }
        
        return [];
    }
    
    public function getServerConfig(string $serverName): ?array
    {
        return $this->serverConfigs[$serverName] ?? null;
    }
    
    public function isServerEnabled(string $serverName): bool
    {
        $config = $this->getServerConfig($serverName);
        return $config && !($config['disabled'] ?? false);
    }
    
    public function getAvailableServers(): array
    {
        return array_filter(
            array_keys($this->serverConfigs),
            fn($server) => $this->isServerEnabled($server)
        );
    }
}
```

---

## 6. Database Integration Specifications

### 6.1 Multi-Database Architecture

#### 6.1.1 Primary Database (MySQL 8.0+)

**Connection Configuration**:

```yaml
mysql_config:
  driver: mysql
  host: localhost
  port: 3306
  database: umamusume-career-planner
  prefix: ucp_
  charset: utf8mb4
  collation: utf8mb4_unicode_ci
  engine: InnoDB
  strict: true
  options:
    - PDO::ATTR_EMULATE_PREPARES: false
    - PDO::MYSQL_ATTR_USE_BUFFERED_QUERY: true
```

**Performance Optimization Settings**:

```sql
-- Session-level optimizations
SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO';
SET SESSION innodb_lock_wait_timeout = 50;
SET SESSION query_cache_type = ON;

-- Connection pool settings
SET GLOBAL max_connections = 150;
SET GLOBAL wait_timeout = 28800;
SET GLOBAL interactive_timeout = 28800;
```

**Connection Manager Implementation**:

```php
<?php

namespace App\Database\Connections;

class MySQLConnectionManager
{
    public function __construct(
        private DatabaseManager $db,
        private ConnectionPoolManager $poolManager
    ) {}
    
    public function getOptimizedConnection(): Connection
    {
        return $this->poolManager->getConnection('mysql', [
            'pool_size' => 10,
            'max_idle_time' => 300,
            'connection_timeout' => 30,
            'query_timeout' => 60
        ]);
    }
    
    public function configureForPerformance(): void
    {
        DB::statement('SET SESSION sql_mode = "STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO"');
        DB::statement('SET SESSION innodb_lock_wait_timeout = 50');
    }
}
```

#### 6.1.2 Caching Layer (Redis 7.0+)

**Redis Configuration**:

```yaml
redis_config:
  client: phpredis
  host: 127.0.0.1  # WSL Redis
  port: 6379
  database: 0
  prefix: umamusume-career-planner:
  
cache_strategies:
  characters:
    ttl: 3600
    tags: [user_data]
  game_data:
    ttl: 86400
    tags: [external_api]
  predictions:
    ttl: 300
    tags: [ai_results]
  meta_tiers:
    ttl: 7200
    tags: [community_data]
```

**Redis Integration Implementation**:

```php
<?php

namespace App\Database\Cache;

class RedisIntegration
{
    private array $cacheStrategies = [
        'characters' => ['ttl' => 3600, 'tags' => ['user_data']],
        'game_data' => ['ttl' => 86400, 'tags' => ['external_api']],
        'predictions' => ['ttl' => 300, 'tags' => ['ai_results']],
        'meta_tiers' => ['ttl' => 7200, 'tags' => ['community_data']]
    ];
    
    public function cacheWithStrategy(string $key, $data, string $strategy = 'default'): void
    {
        $config = $this->cacheStrategies[$strategy] ?? ['ttl' => 3600, 'tags' => []];
        
        Cache::tags($config['tags'])->put($key, $data, $config['ttl']);
    }
    
    public function invalidateByTags(array $tags): void
    {
        Cache::tags($tags)->flush();
    }
    
    public function warmCache(): void
    {
        $this->warmGameData();
        $this->warmUserData();
        $this->warmMetaData();
    }
    
    private function warmGameData(): void
    {
        // Pre-load frequently accessed game data
        $characters = Character::with(['aptitudes', 'skills'])->get();
        $this->cacheWithStrategy('all_characters', $characters, 'game_data');
        
        $skills = Skill::with('evolutions')->get();
        $this->cacheWithStrategy('all_skills', $skills, 'game_data');
    }
}
```

### 6.2 Data Synchronization Specifications

#### 6.2.1 Real-Time Sync with WebSockets

**WebSocket Channel Configuration**:

```php
<?php

namespace App\Broadcasting;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;

class DataSyncChannel extends Channel
{
    public function join(User $user, $characterId)
    {
        return $user->characters()->where('id', $characterId)->exists();
    }
}
```

**Real-Time Event Implementation**:

```php
<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CharacterDataUpdated implements ShouldBroadcast
{
    use InteractsWithSockets;
    
    public function __construct(
        public Character $character,
        public array $changes
    ) {}
    
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("character.{$this->character->id}"),
            new PrivateChannel("user.{$this->character->user_id}")
        ];
    }
    
    public function broadcastWith(): array
    {
        return [
            'character_id' => $this->character->id,
            'changes' => $this->changes,
            'timestamp' => now()->toISOString()
        ];
    }
}
```

#### 6.2.2 Background Sync with Laravel Queues

**Queue Configuration**:

```yaml
queue_config:
  default: redis
  connections:
    redis:
      driver: redis
      connection: default
      queue: default
      retry_after: 90
      block_for: null
      
  queues:
    high: [ai_processing, real_time_sync]
    default: [data_sync, notifications]
    low: [analytics, cleanup]
```

**Sync Job Implementation**:

```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncExternalGameData implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;
    
    public $tries = 3;
    public $backoff = [60, 300, 900]; // 1min, 5min, 15min
    
    public function __construct(
        private string $dataType,
        private array $options = []
    ) {}
    
    public function handle(GameDataAPIManager $apiManager): void
    {
        $data = match ($this->dataType) {
            'characters' => $apiManager->getCharacters($this->options),
            'skills' => $apiManager->getSkills($this->options),
            'support_cards' => $apiManager->getSupportCards($this->options),
            default => throw new InvalidArgumentException("Unknown data type: {$this->dataType}")
        };
        
        // Process and store data
        $this->processData($data);
        
        // Invalidate related caches
        Cache::tags(['external_api', $this->dataType])->flush();
        
        // Broadcast update event
        event(new ExternalDataUpdated($this->dataType, $data->count()));
    }
    
    public function failed(Throwable $exception): void
    {
        Log::error("External data sync failed", [
            'data_type' => $this->dataType,
            'error' => $exception->getMessage()
        ]);
        
        // Notify administrators
        Notification::send(
            User::administrators()->get(),
            new DataSyncFailedNotification($this->dataType, $exception)
        );
    }
}
```

---

## 7. Frontend Integration Specifications

### 7.1 Progressive Web App Integration

#### 7.1.1 Service Worker Configuration

```yaml
service_worker_config:
  cache_name: umamusume-career-planner-v1
  cache_strategies:
    static_assets: cache_first
    api_responses: network_first
    images: cache_first
    fonts: cache_first
  offline_fallback: /offline.html
  background_sync: enabled
  push_notifications: enabled
```

**Service Worker Implementation**:

```javascript
// service-worker.js
const CACHE_NAME = 'umamusume-career-planner-v1';
const STATIC_ASSETS = [
    '/',
    '/css/app.css',
    '/js/app.js',
    '/offline.html',
    '/images/app_logo/logo-256.png'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(STATIC_ASSETS))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);
    
    // API requests: Network first, cache fallback
    if (url.pathname.startsWith('/api/')) {
        event.respondWith(networkFirst(request));
        return;
    }
    
    // Static assets: Cache first, network fallback
    event.respondWith(cacheFirst(request));
});

async function networkFirst(request) {
    try {
        const response = await fetch(request);
        const cache = await caches.open(CACHE_NAME);
        cache.put(request, response.clone());
        return response;
    } catch (error) {
        const cached = await caches.match(request);
        return cached || new Response('Offline', { status: 503 });
    }
}

async function cacheFirst(request) {
    const cached = await caches.match(request);
    if (cached) return cached;
    
    try {
        const response = await fetch(request);
        const cache = await caches.open(CACHE_NAME);
        cache.put(request, response.clone());
        return response;
    } catch (error) {
        return caches.match('/offline.html');
    }
}
```

#### 7.1.2 Web App Manifest

```json
{
    "name": "Umamusume Career Planner",
    "short_name": "UmaPlanner",
    "description": "Advanced optimization application for Umamusume Pretty Derby",
    "start_url": "/",
    "display": "standalone",
    "background_color": "#ffffff",
    "theme_color": "#4f46e5",
    "orientation": "any",
    "icons": [
        {
            "src": "/images/app_logo/logo-128.png",
            "sizes": "128x128",
            "type": "image/png"
        },
        {
            "src": "/images/app_logo/logo-256.png",
            "sizes": "256x256",
            "type": "image/png"
        },
        {
            "src": "/images/app_logo/logo-512.png",
            "sizes": "512x512",
            "type": "image/png"
        },
        {
            "src": "/images/app_logo/logo-1024.png",
            "sizes": "1024x1024",
            "type": "image/png"
        }
    ],
    "categories": ["games", "utilities"],
    "lang": "en",
    "dir": "ltr"
}
```

### 7.2 Real-Time Updates Integration

#### 7.2.1 WebSocket Client Configuration

```javascript
// websocket-client.js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: process.env.MIX_REVERB_APP_KEY,
    wsHost: process.env.MIX_REVERB_HOST,
    wsPort: process.env.MIX_REVERB_PORT,
    wssPort: process.env.MIX_REVERB_PORT,
    forceTLS: (process.env.MIX_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

// Subscribe to character updates
function subscribeToCharacter(characterId) {
    window.Echo.private(`character.${characterId}`)
        .listen('CharacterDataUpdated', (event) => {
            handleCharacterUpdate(event);
        })
        .listen('TrainingPredictionUpdated', (event) => {
            handlePredictionUpdate(event);
        });
}

// Subscribe to AI processing updates
function subscribeToAIUpdates(userId) {
    window.Echo.private(`user.${userId}`)
        .listen('AIProcessingStarted', (event) => {
            showProcessingIndicator(event);
        })
        .listen('AIProcessingCompleted', (event) => {
            hideProcessingIndicator();
            displayAIResponse(event);
        });
}
```

### 7.3 Accessibility Integration (WCAG 2.2 AA)

#### 7.3.1 Accessibility Requirements

| Requirement           | Standard                    | Implementation                      |
|-----------------------|-----------------------------|-------------------------------------|
| Color Contrast        | 4.5:1 (normal), 3:1 (large) | Tailwind CSS v4 color system        |
| Focus Indicators      | 3:1 contrast ratio          | Custom focus ring styles            |
| Keyboard Navigation   | Full functionality          | Tab order, skip links               |
| Screen Reader Support | ARIA attributes             | Semantic HTML, ARIA labels          |
| Text Resizing         | Up to 200%                  | Fluid typography, responsive design |
| Heading Hierarchy     | H1-H6 proper order          | Semantic structure                  |

#### 7.3.2 Accessibility Implementation

```html
<!-- Skip Links -->
<a href="#main-content" class="skip-link">Skip to main content</a>
<a href="#navigation" class="skip-link">Skip to navigation</a>

<!-- Semantic Structure -->
<header role="banner">
    <nav role="navigation" aria-label="Main navigation">
        <!-- Navigation items -->
    </nav>
</header>

<main id="main-content" role="main" aria-label="Main content">
    <h1>Character Dashboard</h1>
    
    <!-- Accessible form example -->
    <form aria-labelledby="form-title">
        <h2 id="form-title">Character Stats</h2>
        
        <div class="form-group">
            <label for="speed" id="speed-label">
                Speed (0-1200)
                <span class="required" aria-hidden="true">*</span>
            </label>
            <input 
                type="number" 
                id="speed" 
                name="speed"
                min="0" 
                max="1200"
                aria-labelledby="speed-label"
                aria-describedby="speed-help"
                aria-required="true"
            >
            <span id="speed-help" class="help-text">
                Enter a value between 0 and 1200
            </span>
        </div>
    </form>
</main>

<footer role="contentinfo">
    <!-- Footer content -->
</footer>
```

```css
/* Accessibility Styles */
.skip-link {
    position: absolute;
    top: -40px;
    left: 0;
    background: #4f46e5;
    color: white;
    padding: 8px 16px;
    z-index: 100;
    transition: top 0.3s;
}

.skip-link:focus {
    top: 0;
}

/* Focus indicators with 3:1 contrast */
:focus-visible {
    outline: 3px solid #4f46e5;
    outline-offset: 2px;
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    :root {
        --text-primary: #000000;
        --bg-primary: #ffffff;
        --border-color: #000000;
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
```

---

## 8. Security Integration Specifications

### 8.1 Authentication Integration

#### 8.1.1 Laravel Sanctum Configuration

```yaml
sanctum_config:
  stateful_domains:
    - localhost
    - umamusume-career-planner.local
    - 127.0.0.1
  guard: web
  expiration: null  # Tokens don't expire by default
  token_prefix: ''
  middleware:
    encrypt_cookies: true
    verify_csrf_token: true
```

**Authentication Implementation**:

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $request->authenticate();
        
        $user = Auth::user();
        $token = $user->createToken('api-token', ['*'])->plainTextToken;
        
        return response()->json([
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ]);
    }
    
    public function logout(): JsonResponse
    {
        Auth::user()->currentAccessToken()->delete();
        
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
    
    public function user(): JsonResponse
    {
        return response()->json([
            'user' => Auth::user()->load(['characters', 'preferences'])
        ]);
    }
}
```

### 8.2 Authorization Integration

#### 8.2.1 Policy-Based Authorization

```php
<?php

namespace App\Policies;

use App\Models\Character;
use App\Models\User;

class CharacterPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // Users can view their own characters
    }
    
    public function view(User $user, Character $character): bool
    {
        return $user->id === $character->user_id;
    }
    
    public function create(User $user): bool
    {
        return true; // All authenticated users can create characters
    }
    
    public function update(User $user, Character $character): bool
    {
        return $user->id === $character->user_id;
    }
    
    public function delete(User $user, Character $character): bool
    {
        return $user->id === $character->user_id;
    }
}
```

### 8.3 Rate Limiting Integration

#### 8.3.1 Rate Limit Configuration

```php
<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class RateLimitServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Authentication endpoints: 10 requests per minute
        RateLimiter::for('auth', function ($request) {
            return Limit::perMinute(10)->by($request->ip());
        });
        
        // API endpoints: 60 requests per minute
        RateLimiter::for('api', function ($request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
        
        // AI processing: 20 requests per minute
        RateLimiter::for('ai', function ($request) {
            return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
        });
        
        // External API calls: 100 requests per minute
        RateLimiter::for('external_api', function ($request) {
            return Limit::perMinute(100)->by('external_api');
        });
    }
}
```

### 8.4 Data Protection Integration

#### 8.4.1 Encryption Configuration

```yaml
encryption_config:
  cipher: AES-256-CBC
  key: ${APP_KEY}
  
encrypted_fields:
  - user.email
  - user.password
  - api_credentials.secret
  - ai_conversations.content (optional)
  
hashing:
  driver: bcrypt
  rounds: 12
```

#### 8.4.2 Data Protection Implementation

```php
<?php

namespace App\Services\Security;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class DataProtectionService
{
    public function encryptSensitiveData(array $data, array $sensitiveFields): array
    {
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = Crypt::encryptString($data[$field]);
            }
        }
        
        return $data;
    }
    
    public function decryptSensitiveData(array $data, array $sensitiveFields): array
    {
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                try {
                    $data[$field] = Crypt::decryptString($data[$field]);
                } catch (DecryptException $e) {
                    Log::warning("Failed to decrypt field: {$field}");
                    $data[$field] = null;
                }
            }
        }
        
        return $data;
    }
    
    public function sanitizeUserInput(string $input): string
    {
        // Remove potentially dangerous characters
        $input = strip_tags($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        
        return $input;
    }
    
    public function validateDataIntegrity(array $data, string $checksum): bool
    {
        $calculatedChecksum = hash('sha256', json_encode($data));
        return hash_equals($checksum, $calculatedChecksum);
    }
}
```

### 8.5 Privacy Integration

#### 8.5.1 Local-First Data Architecture

```yaml
privacy_config:
  data_storage: local_only
  cloud_sync: opt_in
  data_sharing: user_controlled
  
local_data:
  - user_profiles
  - character_data
  - career_history
  - ai_conversations
  - preferences
  
cloud_data_optional:
  - anonymous_analytics
  - community_strategies (opt-in)
  - backup_data (opt-in)
```

#### 8.5.2 Privacy Implementation

```php
<?php

namespace App\Services\Privacy;

class PrivacyService
{
    public function getUserDataExport(User $user): array
    {
        return [
            'user' => $user->only(['id', 'name', 'email', 'created_at']),
            'characters' => $user->characters->map(fn($c) => $c->toArray()),
            'careers' => $user->careers->map(fn($c) => $c->toArray()),
            'preferences' => $user->preferences,
            'exported_at' => now()->toISOString()
        ];
    }
    
    public function deleteUserData(User $user): void
    {
        // Delete all user-related data
        $user->characters()->delete();
        $user->careers()->delete();
        $user->aiConversations()->delete();
        $user->preferences()->delete();
        
        // Anonymize user record
        $user->update([
            'name' => 'Deleted User',
            'email' => "deleted_{$user->id}@deleted.local",
            'password' => bcrypt(Str::random(32))
        ]);
        
        // Soft delete user
        $user->delete();
        
        Log::info("User data deleted", ['user_id' => $user->id]);
    }
    
    public function getPrivacySettings(User $user): array
    {
        return [
            'data_sharing_enabled' => $user->preferences->data_sharing ?? false,
            'analytics_enabled' => $user->preferences->analytics ?? false,
            'cloud_backup_enabled' => $user->preferences->cloud_backup ?? false,
            'community_sharing_enabled' => $user->preferences->community_sharing ?? false
        ];
    }
}
```

---

## 9. Performance Monitoring Integration

### 9.1 Application Performance Monitoring

#### 9.1.1 Monitoring Configuration

```yaml
monitoring_config:
  apm_enabled: true
  metrics:
    - response_time
    - throughput
    - error_rate
    - database_queries
    - cache_hit_rate
    - ai_processing_time
    - memory_usage
    
  alerting:
    response_time_threshold: 5000ms
    error_rate_threshold: 5%
    ai_timeout_threshold: 10000ms
```

#### 9.1.2 Performance Monitoring Implementation

```php
<?php

namespace App\Services\Monitoring;

class PerformanceMonitor
{
    public function recordMetric(string $name, float $value, array $tags = []): void
    {
        $metric = [
            'name' => $name,
            'value' => $value,
            'tags' => $tags,
            'timestamp' => now()->toISOString()
        ];
        
        // Store in Redis for real-time access
        Redis::lpush('metrics:' . $name, json_encode($metric));
        Redis::ltrim('metrics:' . $name, 0, 999); // Keep last 1000 entries
        
        // Check thresholds and alert if necessary
        $this->checkThresholds($name, $value);
    }
    
    public function getMetrics(string $name, int $limit = 100): array
    {
        $metrics = Redis::lrange('metrics:' . $name, 0, $limit - 1);
        return array_map(fn($m) => json_decode($m, true), $metrics);
    }
    
    public function getAverageResponseTime(int $minutes = 5): float
    {
        $metrics = $this->getMetrics('response_time', $minutes * 60);
        
        if (empty($metrics)) {
            return 0.0;
        }
        
        return array_sum(array_column($metrics, 'value')) / count($metrics);
    }
    
    private function checkThresholds(string $name, float $value): void
    {
        $thresholds = config('monitoring.thresholds');
        
        if (isset($thresholds[$name]) && $value > $thresholds[$name]) {
            event(new ThresholdExceeded($name, $value, $thresholds[$name]));
        }
    }
}
```

### 9.2 Logging Integration

#### 9.2.1 Logging Configuration

```yaml
logging_config:
  default: stack
  channels:
    stack:
      driver: stack
      channels: [daily, slack]
      
    daily:
      driver: daily
      path: storage/logs/laravel.log
      level: debug
      days: 14
      
    slack:
      driver: slack
      url: ${SLACK_WEBHOOK_URL}
      level: error
      
    ai_processing:
      driver: daily
      path: storage/logs/ai.log
      level: info
      days: 30
      
    integration:
      driver: daily
      path: storage/logs/integration.log
      level: info
      days: 14
```

---

## 10. Integration Testing Specifications

### 10.1 Test Categories

| Category          | Scope                    | Tools                          |
|-------------------|--------------------------|--------------------------------|
| Unit Tests        | Individual components    | Pest PHP                       |
| Integration Tests | Component interactions   | Pest PHP, Laravel HTTP Tests   |
| API Tests         | External API integration | Pest PHP, Mock servers         |
| E2E Tests         | Full user workflows      | Laravel Dusk                   |
| Performance Tests | Load and stress testing  | Artillery, k6                  |

### 10.2 Test Implementation Examples

```php
<?php

namespace Tests\Integration;

use Tests\TestCase;
use App\Integrations\AI\HybridAIRouter;
use App\Integrations\AI\OllamaIntegration;
use App\Integrations\AI\BedrockIntegration;

class HybridAIRouterTest extends TestCase
{
    public function test_routes_simple_requests_to_local_ollama(): void
    {
        $router = app(HybridAIRouter::class);
        
        $request = new AIRequest(
            prompt: 'Simple training recommendation',
            complexity: 2,
            requiresReasoning: false
        );
        
        $response = $router->routeRequest($request);
        
        expect($response->model)->toStartWith('ollama_');
        expect($response->cost)->toBe(0.0);
    }
    
    public function test_routes_complex_requests_to_cloud_when_budget_available(): void
    {
        $this->mockBudgetManager(['remaining' => 1.00]);
        
        $router = app(HybridAIRouter::class);
        
        $request = new AIRequest(
            prompt: 'Complex multi-turn strategy analysis',
            complexity: 9,
            requiresReasoning: true
        );
        
        $response = $router->routeRequest($request);
        
        expect($response->model)->toStartWith('bedrock_');
        expect($response->cost)->toBeGreaterThan(0);
    }
    
    public function test_falls_back_to_local_when_cloud_unavailable(): void
    {
        $this->mockBedrockUnavailable();
        
        $router = app(HybridAIRouter::class);
        
        $request = new AIRequest(
            prompt: 'Any request',
            complexity: 8
        );
        
        $response = $router->routeRequest($request);
        
        expect($response->model)->toStartWith('ollama_');
    }
}
```

---

## Document Control

| Version | Date       | Author           | Changes                                   |
|---------|------------|------------------|-------------------------------------------|
| 1.0     | 2026-01-11 | Development Team | Initial document creation                 |
| 2.0     | 2026-01-12 | Development Team | Complete specifications with all sections |

---

## Cross-References

| Document            | Relationship               |
|---------------------|----------------------------|
| 000_MASTER_GLOSSARY | Terminology definitions    |
| 004_SDS             | Architecture alignment     |
| 007_SIP             | Integration plan alignment |
| 009_DBD             | Database schema details    |
| 010_SCD             | Source code implementation |

---

*This document provides complete technical specifications for all integration aspects of the Umamusume Career Planner system. All implementations MUST comply with these specifications to ensure system reliability, security, and performance.*
