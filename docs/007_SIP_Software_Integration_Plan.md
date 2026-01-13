# Software Integration Plan (SIP)

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0
**Date**: January 14, 2026
**Project**: UmamusumeCareerPlanner
**Author**: Development Team
**Updated**: Aligned with Laravel 12, Tailwind CSS v4, AI integration, and modern architecture specifications

---

## Table of Contents

1. [Introduction](#1-introduction)
2. [Integration Architecture](#2-integration-architecture)
3. [External System Integration](#3-external-system-integration)
4. [AI Services Integration](#4-ai-services-integration)
5. [MCP Server Integration](#5-mcp-server-integration)
6. [Database Integration](#6-database-integration)
7. [Frontend Integration](#7-frontend-integration)
8. [Security Integration](#8-security-integration)

---

## 1. Introduction

### 1.1 Purpose

This Software Integration Plan (SIP) defines the comprehensive integration strategy for the Umamusume Career Planner system, covering all aspects of system integration including external APIs, AI services, MCP servers, databases, frontend components, and third-party services. The plan ensures seamless integration of modern technologies while maintaining system reliability, security, and performance.

### 1.2 Scope

The integration plan covers:

- **External API Integration**: umapyoi.net, UmamusumeDB.com, community data sources
- **AI Services Integration**: Ollama local models, AWS Bedrock cloud services
- **MCP Server Integration**: Model Context Protocol servers for enhanced functionality
- **Database Integration**: MySQL primary database, Redis caching, data synchronization
- **Frontend Integration**: Laravel 12, Tailwind CSS v4, Progressive Web App features
- **Security Integration**: Authentication, authorization, data protection
- **Performance Integration**: Caching strategies, optimization techniques
- **Monitoring Integration**: Application performance monitoring, logging, alerting

### 1.3 Integration Principles

#### 1.3.1 Hybrid Architecture

- **Local-First Processing**: Primary operations using local resources
- **Cloud Fallback**: Intelligent routing to cloud services when needed
- **Privacy Protection**: No personal data transmission without consent
- **Performance Optimization**: Minimize latency through intelligent caching

#### 1.3.2 Fault Tolerance

- **Graceful Degradation**: System continues operating when services unavailable
- **Circuit Breaker Pattern**: Prevent cascading failures
- **Retry Mechanisms**: Intelligent retry with exponential backoff
- **Fallback Strategies**: Alternative data sources and processing methods

#### 1.3.3 Scalability

- **Modular Design**: Components can be scaled independently
- **Asynchronous Processing**: Non-blocking operations where possible
- **Caching Layers**: Multiple levels of caching for performance
- **Resource Optimization**: Efficient use of system resources

---

## 2. Integration Architecture

### 2.1 High-Level Integration Overview

```text
┌─────────────────────────────────────────────────────────────────┐
│                    CLIENT LAYER                                 │
├─────────────────────────────────────────────────────────────────┤
│  Progressive Web App (Tailwind CSS v4 + Service Workers)       │
│  ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐   │
│  │   UI Components │ │   State Mgmt    │ │   Offline Cache │   │
│  │   (Accessible)  │ │   (Reactive)    │ │   (Service SW)  │   │
│  └─────────────────┘ └─────────────────┘ └─────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                 APPLICATION LAYER                               │
├─────────────────────────────────────────────────────────────────┤
│  Laravel 12 Framework with Modern Features                     │
│  ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐   │
│  │   API Gateway   │ │   Service Layer │ │   Event System  │   │
│  │   (Rate Limit)  │ │   (Business)    │ │   (Async Proc)  │   │
│  └─────────────────┘ └─────────────────┘ └─────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                 INTEGRATION LAYER                               │
├─────────────────────────────────────────────────────────────────┤
│  ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐   │
│  │   AI Router     │ │   API Manager   │ │   MCP Gateway   │   │
│  │   (Hybrid AI)   │ │   (External)    │ │   (Protocols)   │   │
│  └─────────────────┘ └─────────────────┘ └─────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                 EXTERNAL SERVICES                               │
├─────────────────────────────────────────────────────────────────┤
│  ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐   │
│  │   Ollama Local  │ │   AWS Bedrock   │ │   Game APIs     │   │
│  │   (Privacy)     │ │   (Advanced)    │ │   (Community)   │   │
│  └─────────────────┘ └─────────────────┘ └─────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
```

### 2.2 Integration Patterns

#### 2.2.1 Adapter Pattern for External APIs

```php
<?php

namespace App\Integrations\Contracts;

interface GameDataProviderInterface
{
    public function getCharacters(): Collection;
    public function getSkills(): Collection;
    public function getSupportCards(): Collection;
    public function getMetaTiers(): Collection;
}

namespace App\Integrations\Adapters;

class UmapyoiAdapter implements GameDataProviderInterface
{
    public function __construct(
        private HttpClient $client,
        private CacheManager $cache,
        private RateLimiter $rateLimiter
    ) {}

    public function getCharacters(): Collection
    {
        return $this->cache->remember('umapyoi.characters', 3600, function () {
            $this->rateLimiter->attempt('umapyoi', 100, 60);

            $response = $this->client->get('/api/v1/characters');
            return collect($response['data'])->map(fn($char) => $this->transformCharacter($char));
        });
    }

    private function transformCharacter(array $data): array
    {
        return [
            'external_id' => $data['id'],
            'name' => $data['name'],
            'name_en' => $data['name_en'] ?? null,
            'rarity' => $data['rarity'],
            'aptitudes' => $this->transformAptitudes($data['aptitudes']),
            'growth_rates' => $data['growth_rates'] ?? []
        ];
    }
}
```

#### 2.2.2 Circuit Breaker Pattern for Resilience

```php
<?php

namespace App\Integrations\Patterns;

class CircuitBreaker
{
    private const FAILURE_THRESHOLD = 5;
    private const TIMEOUT_DURATION = 60; // seconds

    public function __construct(
        private CacheManager $cache,
        private Logger $logger
    ) {}

    public function call(string $service, callable $operation)
    {
        $state = $this->getCircuitState($service);

        return match ($state) {
            'open' => $this->handleOpenCircuit($service),
            'half-open' => $this->handleHalfOpenCircuit($service, $operation),
            default => $this->handleClosedCircuit($service, $operation)
        };
    }

    private function handleClosedCircuit(string $service, callable $operation)
    {
        try {
            $result = $operation();
            $this->recordSuccess($service);
            return $result;
        } catch (Exception $e) {
            $this->recordFailure($service);
            throw $e;
        }
    }

    private function recordFailure(string $service): void
    {
        $failures = $this->cache->increment("circuit_breaker.{$service}.failures");

        if ($failures >= self::FAILURE_THRESHOLD) {
            $this->openCircuit($service);
        }
    }

    private function openCircuit(string $service): void
    {
        $this->cache->put("circuit_breaker.{$service}.state", 'open', self::TIMEOUT_DURATION);
        $this->logger->warning("Circuit breaker opened for service: {$service}");
    }
}
```

#### 2.2.3 Facade Pattern for Unified API Access

```php
<?php

namespace App\Integrations\Facades;

class GameDataFacade
{
    public function __construct(
        private array $providers,
        private CircuitBreaker $circuitBreaker,
        private FallbackManager $fallbackManager
    ) {}

    public function getCharacters(): Collection
    {
        foreach ($this->providers as $provider) {
            try {
                return $this->circuitBreaker->call(
                    $provider->getName(),
                    fn() => $provider->getCharacters()
                );
            } catch (Exception $e) {
                $this->logger->warning("Provider {$provider->getName()} failed: {$e->getMessage()}");
                continue;
            }
        }

        return $this->fallbackManager->getCharacters();
    }
}
```

---

## 3. External System Integration

### 3.1 Game Data API Integration

#### 3.1.1 Primary API: umapyoi.net

**Integration Specifications**:

- **Base URL**: `https://api.umapyoi.net/api/v1/`
- **Authentication**: None required (public API)
- **Rate Limits**: 100 requests/minute, 1000 requests/hour
- **Caching Strategy**: Redis-based with 24-hour TTL for static data
- **Fallback**: Local cached data, manual input interface

**Implementation Strategy**:

```php
<?php

namespace App\Integrations\GameData;

class UmapyoiIntegration
{
    private const BASE_URL = 'https://api.umapyoi.net/api/v1/';
    private const RATE_LIMIT = 100; // per minute

    public function __construct(
        private HttpClient $client,
        private CacheManager $cache,
        private RateLimiter $rateLimiter,
        private Logger $logger
    ) {}

    public function syncCharacterData(): SyncResult
    {
        try {
            $this->rateLimiter->attempt('umapyoi', self::RATE_LIMIT, 60);

            $response = $this->client->timeout(30)->get(self::BASE_URL . 'characters');

            if ($response->successful()) {
                $characters = $response->json()['data'];
                $this->processCharacterData($characters);

                return new SyncResult(true, count($characters), 'Characters synced successfully');
            }

            throw new ApiException("API returned status: {$response->status()}");

        } catch (Exception $e) {
            $this->logger->error('umapyoi.net sync failed', ['error' => $e->getMessage()]);
            return new SyncResult(false, 0, $e->getMessage());
        }
    }

    private function processCharacterData(array $characters): void
    {
        foreach (array_chunk($characters, 50) as $batch) {
            ProcessCharacterBatch::dispatch($batch);
        }
    }
}
```

#### 3.1.2 Secondary API: UmamusumeDB.com

**Integration Specifications**:

- **Status**: Requires verification during implementation
- **Data Types**: Training calculations, meta analysis
- **Fallback Strategy**: Manual data entry, community sourcing
- **Verification Process**: Phase 1 implementation priority

```php
<?php

namespace App\Integrations\GameData;

class UmamusumeDBIntegration
{
    private bool $isVerified = false;
    private string $accessMethod = 'unknown';

    public function verifyAvailability(): bool
    {
        try {
            // Attempt API access
            $response = Http::timeout(10)->get('https://umamusumedb.com/api/status');
            if ($response->successful()) {
                $this->isVerified = true;
                $this->accessMethod = 'api';
                return true;
            }

            // Fallback to web scraping verification
            $response = Http::timeout(10)->get('https://umamusumedb.com');
            if ($response->successful() && str_contains($response->body(), 'umamusume')) {
                $this->isVerified = true;
                $this->accessMethod = 'scraping';
                return true;
            }

        } catch (Exception $e) {
            Log::warning('UmamusumeDB verification failed', ['error' => $e->getMessage()]);
        }

        $this->isVerified = false;
        return false;
    }
}
```

### 3.2 Community Data Integration

#### 3.2.1 Meta Tier Data Synchronization

```php
<?php

namespace App\Integrations\Community;

class MetaTierIntegration
{
    public function syncMetaTiers(): void
    {
        $sources = [
            'gameWith' => new GameWithScraper(),
            'reddit' => new RedditAPIClient(),
            'discord' => new DiscordWebhookListener()
        ];

        foreach ($sources as $name => $source) {
            try {
                $tierData = $source->getTierData();
                $this->validateTierData($tierData);
                $this->storeTierData($name, $tierData);
            } catch (Exception $e) {
                Log::warning("Meta tier sync failed for {$name}", ['error' => $e->getMessage()]);
            }
        }
    }

    private function validateTierData(array $data): void
    {
        $validator = Validator::make($data, [
            '*.card_id' => 'required|integer',
            '*.tier' => 'required|in:SS,S,A,B',
            '*.confidence' => 'required|numeric|between:0,1',
            '*.updated_at' => 'required|date'
        ]);

        if ($validator->fails()) {
            throw new ValidationException('Invalid tier data format');
        }
    }
}
```

---

## 4. AI Services Integration

### 4.1 Hybrid AI Architecture

#### 4.1.1 Local AI Integration (Ollama)

**Technology Stack**:

- **Package**: cloudstudio/ollama-laravel (verified Laravel 12 compatibility)
- **Models**: Llama 3.3, Mistral, Qwen 2.5
- **Use Cases**: Privacy-focused processing, fast responses, local inference

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
                cost: 0 // Local processing is free
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
            $complexity <= 7 => 'llama3.3',  // General purpose
            default => 'qwen2.5'             // Complex multilingual
        };
    }
}
```

#### 4.1.2 Cloud AI Integration (AWS Bedrock)

**Technology Stack**:

- **Service**: AWS Bedrock via MCP servers
- **Models**: Claude 4.5 series, Nova 2 series
- **Use Cases**: Complex reasoning, advanced analysis, fallback processing

```php
<?php

namespace App\Integrations\AI;

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
        private BedrockClient $client,
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
        $inputCost = ($usage['input_tokens'] / 1000) * $pricing['input'];
        $outputCost = ($usage['output_tokens'] / 1000) * $pricing['output'];

        return $inputCost + $outputCost;
    }
}
```

#### 4.1.3 Intelligent AI Routing

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
            return $this->processLocally($request);
        } catch (AIProcessingException $e) {
            Log::info('Falling back to cloud processing', ['reason' => $e->getMessage()]);
            return $this->processWithCloud($request, 'nova-2-lite');
        }
    }
}
```

---

## 5. MCP Server Integration

### 5.1 Available MCP Servers

#### 5.1.1 AI and Agent Services

**Strands Agent SDK Integration**:

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

**AgentCore Integration**:

```php
<?php

namespace App\Integrations\MCP;

class AgentCoreIntegration
{
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
}
```

#### 5.1.2 AWS Infrastructure Services

**AWS Pricing Integration**:

```php
<?php

namespace App\Integrations\MCP;

class AWSPricingIntegration
{
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

### 5.2 MCP Configuration Management

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

    private function loadUserConfig(): array
    {
        $configPath = $_SERVER['HOME'] . '/.kiro/settings/mcp.json';

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
}
```

---

## 6. Database Integration

### 6.1 Multi-Database Architecture

#### 6.1.1 Primary Database (MySQL)

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
        DB::statement('SET SESSION query_cache_type = ON');
    }
}
```

#### 6.1.2 Caching Layer (Redis)

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
        // Warm frequently accessed data
        $this->warmGameData();
        $this->warmUserData();
        $this->warmMetaData();
    }
}
```

### 6.2 Data Synchronization

#### 6.2.1 Real-Time Sync with WebSockets

```php
<?php

namespace App\Broadcasting;

class DataSyncChannel extends Channel
{
    public function join(User $user, $characterId)
    {
        return $user->characters()->where('id', $characterId)->exists();
    }
}

namespace App\Events;

class CharacterDataUpdated implements ShouldBroadcast
{
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

---

## 7. Frontend Integration

### 7.1 Progressive Web App Integration

#### 7.1.1 Service Worker Implementation

```javascript
// public/sw.js
const CACHE_NAME = 'umamusume-planner-v1';
const STATIC_ASSETS = [
    '/',
    '/css/app.css',
    '/js/app.js',
    '/images/app_logo/uma_musume_race_planner_logo_512.png'
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(STATIC_ASSETS))
    );
});

self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                // Return cached version or fetch from network
                return response || fetch(event.request);
            })
            .catch(() => {
                // Fallback for offline functionality
                if (event.request.destination === 'document') {
                    return caches.match('/offline.html');
                }
            })
    );
});

// Background sync for data updates
self.addEventListener('sync', event => {
    if (event.tag === 'character-data-sync') {
        event.waitUntil(syncCharacterData());
    }
});

async function syncCharacterData() {
    const pendingUpdates = await getStoredUpdates();

    for (const update of pendingUpdates) {
        try {
            await fetch('/api/characters/' + update.id, {
                method: 'PUT',
                body: JSON.stringify(update.data),
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + await getAuthToken()
                }
            });

            await removeStoredUpdate(update.id);
        } catch (error) {
            console.error('Sync failed for character:', update.id, error);
        }
    }
}
```

#### 7.1.2 Offline Functionality

```php
<?php

namespace App\Http\Controllers;

class OfflineController extends Controller
{
    public function manifest()
    {
        return response()->json([
            'name' => 'Umamusume Career Planner',
            'short_name' => 'UmaPlanner',
            'description' => 'Advanced optimization for Umamusume Pretty Derby',
            'start_url' => '/',
            'display' => 'standalone',
            'theme_color' => '#3B82F6',
            'background_color' => '#FFFFFF',
            'icons' => [
                [
                    'src' => '/images/app_logo/uma_musume_race_planner_logo_128.png',
                    'sizes' => '128x128',
                    'type' => 'image/png'
                ],
                [
                    'src' => '/images/app_logo/uma_musume_race_planner_logo_256.png',
                    'sizes' => '256x256',
                    'type' => 'image/png'
                ],
                [
                    'src' => '/images/app_logo/uma_musume_race_planner_logo_512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png'
                ]
            ]
        ])->header('Content-Type', 'application/manifest+json');
    }
}
```

### 7.2 Real-Time UI Updates

#### 7.2.1 WebSocket Integration

```javascript
// resources/js/websocket.js
class WebSocketManager {
    constructor() {
        this.connection = null;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
    }

    connect() {
        this.connection = new WebSocket(`ws://localhost:6001/app/${window.Laravel.pusherKey}`);

        this.connection.onopen = () => {
            console.log('WebSocket connected');
            this.reconnectAttempts = 0;
        };

        this.connection.onmessage = (event) => {
            const data = JSON.parse(event.data);
            this.handleMessage(data);
        };

        this.connection.onclose = () => {
            this.handleReconnect();
        };

        this.connection.onerror = (error) => {
            console.error('WebSocket error:', error);
        };
    }

    handleMessage(data) {
        switch (data.event) {
            case 'character-updated':
                this.updateCharacterUI(data.data);
                break;
            case 'prediction-ready':
                this.updatePredictions(data.data);
                break;
            case 'ai-response':
                this.displayAIResponse(data.data);
                break;
        }
    }

    handleReconnect() {
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            setTimeout(() => {
                this.reconnectAttempts++;
                this.connect();
            }, Math.pow(2, this.reconnectAttempts) * 1000);
        }
    }
}
```

---

## 8. Security Integration

### 8.1 Authentication Integration

#### 8.1.1 Laravel Sanctum Configuration

```php
<?php

namespace App\Http\Middleware;

class SanctumAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user()) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // Rate limiting per user
        $rateLimiter = app(RateLimiter::class);
        $key = 'api:' . $request->user()->id;

        if (!$rateLimiter->attempt($key, 60, 60)) {
            return response()->json(['error' => 'Rate limit exceeded'], 429);
        }

        return $next($request);
    }
}
```

#### 8.1.2 API Security

```php
<?php

namespace App\Http\Middleware;

class APISecurityMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // CORS headers
        $response = $next($request);
        $response->headers->set('Access-Control-Allow-Origin', config('app.frontend_url'));
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');

        // Security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        return $response;
    }
}
```

### 8.2 Data Protection

#### 8.2.1 Encryption Integration

```php
<?php

namespace App\Services\Security;

class DataEncryptionService
{
    public function encryptSensitiveData(array $data): array
    {
        $sensitiveFields = ['notes', 'personal_settings', 'ai_conversations'];

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = encrypt($data[$field]);
            }
        }

        return $data;
    }

    public function decryptSensitiveData(array $data): array
    {
        $sensitiveFields = ['notes', 'personal_settings', 'ai_conversations'];

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                try {
                    $data[$field] = decrypt($data[$field]);
                } catch (DecryptException $e) {
                    Log::warning("Failed to decrypt field: {$field}");
                    $data[$field] = null;
                }
            }
        }

        return $data;
    }
}
```

---

## Document Control

| Version | Date | Author | Changes |
| ------- | ---- | ------ | ------- |
| 1.0 | 2026-01-11 | Development Team | Initial software integration plan |

---

*This document provides the comprehensive software integration plan for the Umamusume Pretty Derby Career Planner system, including integration architecture, external API integration, AI service integration, and security integration.*
