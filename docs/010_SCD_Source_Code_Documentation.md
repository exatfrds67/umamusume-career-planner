# Source Code Documentation (SCD)

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 11, 2026  
**Project**: UmamusumeCareerPlanner  
**Author**: Development Team  
**Updated**: Aligned with Laravel 12, modern PHP practices, and AI
integration architecture

---

## Table of Contents

1. [Introduction](#introduction)
2. [Project Structure](#2-project-structure)
3. [Architecture Overview](#3-architecture-overview)
4. [Core Components](#4-core-components)
5. [API Documentation](#5-api-documentation)

---

## Introduction

### Purpose

This Source Code Documentation (SCD) provides comprehensive
documentation for the Umamusume Career Planner codebase, including
architecture patterns, component specifications, API documentation, and
development guidelines.

### Technology Stack

#### Backend Technologies

- **Framework**: Laravel 12 with strict mode enabled
- **PHP Version**: 8.3+ with modern features
- **Database**: MySQL 8.0+ with InnoDB engine
- **Caching**: Redis 7.0+ for application and session caching
- **Queue System**: Laravel Queues with Redis driver
- **Authentication**: Laravel Sanctum with multi-factor authentication

#### Frontend Technologies

- **CSS Framework**: Tailwind CSS v4 with zero configuration
- **JavaScript**: Modern ES2023+ with Vite build system
- **Progressive Web App**: Service Workers, offline functionality
- **Real-time**: WebSocket integration with Laravel Broadcasting
- **Accessibility**: WCAG 2.2 AA compliance throughout

#### AI Integration

- **Local AI**: Ollama with Llama 3.3, Mistral, Qwen 2.5 models
- **Cloud AI**: AWS Bedrock with Claude 4.5 and Nova 2 series
- **MCP Servers**: Model Context Protocol for enhanced functionality
- **Cost Management**: Intelligent routing and budget tracking

### Code Standards

#### PHP Standards

- **PSR-12**: Extended coding style standard
- **PSR-4**: Autoloading standard
- **Strict Types**: Enabled in all PHP files
- **Type Declarations**: Required for all method parameters and return
  types
- **Documentation**: PHPDoc blocks for all public methods and classes

#### JavaScript Standards

- **ES2023+**: Modern JavaScript features
- **ESLint**: Code quality and consistency
- **Prettier**: Code formatting
- **JSDoc**: Documentation for complex functions

---

---

## 2. Project Structure

### 2.1 Laravel Application Structure

```text
umamusume-career-planner/
├── app/
│   ├── Console/
│   │   ├── Commands/
│   │   │   ├── AI/
│   │   │   ├── Cache/
│   │   │   └── Sync/
│   │   └── Kernel.php
│   ├── Events/
│   │   ├── Character/
│   │   ├── Training/
│   │   └── AI/
│   ├── Exceptions/
│   │   ├── AI/
│   │   ├── API/
│   │   └── Handler.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── API/
│   │   │   │   ├── V1/
│   │   │   │   │   ├── CharacterController.php
│   │   │   │   │   ├── TrainingController.php
│   │   │   │   │   ├── AIController.php
│   │   │   │   │   └── AnalyticsController.php
│   │   │   │   └── V2/ (future)
│   │   │   ├── Auth/
│   │   │   └── Web/
│   │   ├── Middleware/
│   │   │   ├── API/
│   │   │   ├── Auth/
│   │   │   └── Security/
│   │   ├── Requests/
│   │   │   ├── Character/
│   │   │   ├── Training/
│   │   │   └── AI/
│   │   └── Resources/
│   │       ├── Character/
│   │       ├── Training/
│   │       └── AI/
│   ├── Integrations/
│   │   ├── AI/
│   │   │   ├── Ollama/
│   │   │   ├── Bedrock/
│   │   │   └── Contracts/
│   │   ├── APIs/
│   │   │   ├── Umapyoi/
│   │   │   ├── UmamusumeDB/
│   │   │   └── Contracts/
│   │   └── MCP/
│   ├── Jobs/
│   │   ├── AI/
│   │   ├── Sync/
│   │   └── Analytics/
│   ├── Listeners/
│   │   ├── Character/
│   │   ├── Training/
│   │   └── AI/
│   ├── Models/
│   │   ├── Character/
│   │   ├── Training/
│   │   ├── AI/
│   │   └── User/
│   ├── Providers/
│   │   ├── AIServiceProvider.php
│   │   ├── IntegrationServiceProvider.php
│   │   └── AppServiceProvider.php
│   ├── Repositories/
│   │   ├── Character/
│   │   ├── Training/
│   │   └── AI/
│   └── Services/
│       ├── AI/
│       ├── Analytics/
│       ├── Cache/
│       └── Security/
├── bootstrap/
├── config/
│   ├── ai.php
│   ├── integrations.php
│   └── mcp.php
├── database/
│   ├── factories/
│   ├── migrations/
│   ├── seeders/
│   └── schema/
├── public/
│   ├── css/
│   ├── js/
│   ├── images/
│   └── sw.js (Service Worker)
├── resources/
│   ├── css/
│   │   └── app.css (Tailwind CSS v4)
│   ├── js/
│   │   ├── components/
│   │   ├── services/
│   │   └── app.js
│   ├── views/
│   │   ├── components/
│   │   ├── layouts/
│   │   └── pages/
│   └── lang/
├── routes/
│   ├── api.php
│   ├── web.php
│   ├── channels.php
│   └── console.php
├── storage/
├── tests/
│   ├── Feature/
│   │   ├── API/
│   │   ├── Integration/
│   │   └── AI/
│   ├── Unit/
│   │   ├── Models/
│   │   ├── Services/
│   │   └── Repositories/
│   └── TestCase.php
└── vendor/
```

### 2.2 Configuration Structure

#### 2.2.1 AI Configuration

```php
<?php
// config/ai.php

return [
    'default_provider' => env('AI_DEFAULT_PROVIDER', 'ollama'),
    
    'providers' => [
        'ollama' => [
            'enabled' => env('OLLAMA_ENABLED', true),
            'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
            'timeout' => env('OLLAMA_TIMEOUT', 30),
            'models' => [
                'llama3.3' => [
                    'context_length' => 128000,
                    'temperature' => 0.7,
                    'use_cases' => ['general', 'analysis', 'planning']
                ],
                'mistral' => [
                    'context_length' => 32000,
                    'temperature' => 0.6,
                    'use_cases' => ['quick_response', 'simple_calculation']
                ],
                'qwen2.5' => [
                    'context_length' => 32000,
                    'temperature' => 0.8,
                    'use_cases' => ['multilingual', 'japanese_processing']
                ]
            ]
        ],
        
        'bedrock' => [
            'enabled' => env('BEDROCK_ENABLED', false),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'timeout' => env('BEDROCK_TIMEOUT', 60),
            'budget' => [
                'daily_limit' => env('BEDROCK_DAILY_BUDGET', 2.00),
                'monthly_limit' => env('BEDROCK_MONTHLY_BUDGET', 50.00),
                'alert_threshold' => 0.8
            ],
            'models' => [
                'claude-4.5-opus' => [
                    'model_id' => 'anthropic.claude-3-5-sonnet-20241022-v2:0',
                    'input_cost_per_1k' => 5.0,
                    'output_cost_per_1k' => 25.0
                ],
                'claude-4.5-sonnet' => [
                    'model_id' => 'anthropic.claude-3-5-sonnet-20241022-v2:0',
                    'input_cost_per_1k' => 3.0,
                    'output_cost_per_1k' => 15.0
                ],
                'nova-2-lite' => [
                    'model_id' => 'amazon.nova-lite-v1:0',
                    'input_cost_per_1k' => 0.00125,
                    'output_cost_per_1k' => 0.00125
                ]
            ]
        ]
    ],
    
    'routing' => [
        'complexity_thresholds' => [
            'simple' => 3,
            'moderate' => 7,
            'complex' => 10
        ],
        'fallback_strategy' => 'local_first',
        'cache_responses' => true,
        'cache_ttl' => 300
    ]
];
```

---

## 3. Architecture Overview

### 3.1 Layered Architecture Pattern

```text
┌─────────────────────────────────────────────────────────────────┐
│                    PRESENTATION LAYER                           │
├─────────────────────────────────────────────────────────────────┤
│  Controllers, Resources, Requests, Middleware                   │
│  ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐   │
│  │   API Routes    │ │   Web Routes    │ │   WebSocket     │   │
│  │   (RESTful)     │ │   (Blade Views) │ │   (Real-time)   │   │
│  └─────────────────┘ └─────────────────┘ └─────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                    APPLICATION LAYER                            │
├─────────────────────────────────────────────────────────────────┤
│  Services, Events, Listeners, Jobs                              │
│  ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐   │
│  │   Business      │ │   Event         │ │   Queue         │   │
│  │   Services      │ │   Handlers      │ │   Jobs          │   │
│  └─────────────────┘ └─────────────────┘ └─────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                    DOMAIN LAYER                                 │
├─────────────────────────────────────────────────────────────────┤
│  Models, Repositories, Domain Services                          │
│  ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐   │
│  │   Eloquent      │ │   Repository    │ │   Domain        │   │
│  │   Models        │ │   Pattern       │ │   Logic         │   │
│  └─────────────────┘ └─────────────────┘ └─────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                    INFRASTRUCTURE LAYER                         │
├─────────────────────────────────────────────────────────────────┤
│  Database, Cache, External APIs, File System                   │
│  ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐   │
│  │   MySQL         │ │   Redis         │ │   External      │   │
│  │   Database      │ │   Cache         │ │   APIs          │   │
│  └─────────────────┘ └─────────────────┘ └─────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
```

### 3.2 Design Patterns Implementation

#### 3.2.1 Repository Pattern

```php
<?php

namespace App\Repositories\Contracts;

interface CharacterRepositoryInterface
{
    public function findByUser(User $user): Collection;
    public function findActiveByUser(User $user): Collection;
    public function create(array $data): Character;
    public function update(Character $character, array $data): Character;
    public function delete(Character $character): bool;
    public function getTrainingHistory(Character $character, int $limit = 50): Collection;
    public function getPerformanceAnalytics(Character $character): array;
}

namespace App\Repositories;

class CharacterRepository implements CharacterRepositoryInterface
{
    public function __construct(
        private Character $model,
        private CacheService $cache
    ) {}
    
    public function findByUser(User $user): Collection
    {
        $cacheKey = "user_characters:{$user->id}";
        
        return $this->cache->remember($cacheKey, 3600, function () use ($user) {
            return $this->model
                ->where('user_id', $user->id)
                ->with(['characterTemplate', 'scenario'])
                ->orderBy('updated_at', 'desc')
                ->get();
        });
    }
    
    public function findActiveByUser(User $user): Collection
    {
        return $this->findByUser($user)->where('is_active', true);
    }
    
    public function create(array $data): Character
    {
        $character = $this->model->create(array_merge($data, [
            'uuid' => Str::uuid(),
            'current_stats' => $this->getDefaultStats(),
            'is_active' => true
        ]));
        
        // Clear user cache
        $this->cache->forget("user_characters:{$character->user_id}");
        
        // Dispatch character created event
        event(new CharacterCreated($character));
        
        return $character;
    }
    
    private function getDefaultStats(): array
    {
        return [
            'speed' => 0,
            'stamina' => 0,
            'power' => 0,
            'guts' => 0,
            'wit' => 0,
            'skill_points' => 0,
            'fans' => 0
        ];
    }
}
```

#### 3.2.2 Service Layer Pattern

```php
<?php

namespace App\Services\Character;

class CharacterTrainingService
{
    public function __construct(
        private CharacterRepositoryInterface $characterRepository,
        private TrainingSessionRepositoryInterface $trainingRepository,
        private AIRecommendationService $aiService,
        private EventDispatcher $eventDispatcher
    ) {}
    
    public function executeTraining(
        Character $character, 
        TrainingAction $action
    ): TrainingResult {
        
        // Validate training action
        $this->validateTrainingAction($character, $action);
        
        // Get AI recommendation if requested
        $aiRecommendation = null;
        if ($action->requestAIRecommendation) {
            $aiRecommendation = $this->aiService->getTrainingRecommendation(
                $character, 
                $action
            );
        }
        
        // Calculate training results
        $results = $this->calculateTrainingResults($character, $action);
        
        // Update character stats
        $updatedCharacter = $this->updateCharacterStats($character, $results);
        
        // Record training session
        $session = $this->recordTrainingSession(
            $character, 
            $action, 
            $results, 
            $aiRecommendation
        );
        
        // Dispatch training completed event
        $this->eventDispatcher->dispatch(
            new TrainingCompleted($updatedCharacter, $session, $results)
        );
        
        return new TrainingResult(
            character: $updatedCharacter,
            session: $session,
            statsGained: $results->statsGained,
            eventsTriggered: $results->events,
            aiRecommendation: $aiRecommendation
        );
    }
    
    private function validateTrainingAction(Character $character, TrainingAction $action): void
    {
        if (!$character->canTrain()) {
            throw new InvalidTrainingException('Character cannot train at this time');
        }
        
        if ($character->current_turn >= $character->max_turns) {
            throw new InvalidTrainingException('Character has completed maximum turns');
        }
        
        // Additional validation logic...
    }
    
    private function calculateTrainingResults(
        Character $character, 
        TrainingAction $action
    ): TrainingCalculationResult {
        
        $calculator = new TrainingCalculator(
            $character->aptitudes,
            $character->current_stats,
            $action->supportCards
        );
        
        return $calculator->calculate($action);
    }
}
```

#### 3.2.3 Factory Pattern for AI Services

```php
<?php

namespace App\Services\AI;

class AIServiceFactory
{
    private array $providers = [];
    
    public function __construct()
    {
        $this->registerProviders();
    }
    
    public function create(string $provider = null): AIServiceInterface
    {
        $provider = $provider ?: config('ai.default_provider');
        
        if (!isset($this->providers[$provider])) {
            throw new InvalidAIProviderException("Provider {$provider} not found");
        }
        
        return $this->providers[$provider]();
    }
    
    public function createOptimal(AIRequest $request): AIServiceInterface
    {
        $router = app(HybridAIRouter::class);
        $optimalProvider = $router->selectProvider($request);
        
        return $this->create($optimalProvider);
    }
    
    private function registerProviders(): void
    {
        $this->providers['ollama'] = fn() => app(OllamaService::class);
        $this->providers['bedrock'] = fn() => app(BedrockService::class);
    }
}

// Usage in controllers
class AIController extends Controller
{
    public function __construct(
        private AIServiceFactory $aiFactory,
        private AIRequestValidator $validator
    ) {}
    
    public function generateRecommendation(AIRecommendationRequest $request): JsonResponse
    {
        $aiRequest = AIRequest::fromRequest($request);
        
        // Get optimal AI service based on request complexity
        $aiService = $this->aiFactory->createOptimal($aiRequest);
        
        $response = $aiService->generateResponse($aiRequest);
        
        return response()->json([
            'success' => true,
            'data' => [
                'content' => $response->getContent(),
                'model' => $response->getModel(),
                'processing_time' => $response->getProcessingTime(),
                'cost' => $response->getCost(),
                'confidence' => $response->getConfidence()
            ]
        ]);
    }
}
```

---

## 4. Core Components

### 4.1 Character Management System

#### 4.1.1 Character Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\HasUuid;
use App\Models\Concerns\CacheableQueries;

class Character extends Model
{
    use HasFactory, HasUuid, CacheableQueries;
    
    protected $fillable = [
        'user_id',
        'character_template_id',
        'name',
        'nickname',
        'scenario_id',
        'current_turn',
        'max_turns',
        'current_stats',
        'aptitudes',
        'growth_rates',
        'training_summary',
        'goals',
        'status',
        'is_active'
    ];
    
    protected $casts = [
        'current_stats' => 'array',
        'aptitudes' => 'array',
        'growth_rates' => 'array',
        'training_summary' => 'array',
        'goals' => 'array',
        'status' => 'array',
        'is_active' => 'boolean',
        'current_turn' => 'integer',
        'max_turns' => 'integer',
        'completed_at' => 'datetime'
    ];
    
    protected $attributes = [
        'current_stats' => '{"speed":0,"stamina":0,"power":0,"guts":0,"wit":0,"skill_points":0,"fans":0}',
        'is_active' => true,
        'current_turn' => 0,
        'max_turns' => 78
    ];
    
    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function characterTemplate(): BelongsTo
    {
        return $this->belongsTo(CharacterTemplate::class);
    }
    
    public function scenario(): BelongsTo
    {
        return $this->belongsTo(Scenario::class);
    }
    
    public function trainingSessions(): HasMany
    {
        return $this->hasMany(TrainingSession::class);
    }
    
    public function raceResults(): HasMany
    {
        return $this->hasMany(RaceResult::class);
    }
    
    public function aiConversations(): HasMany
    {
        return $this->hasMany(AIConversation::class);
    }
    
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    public function scopeInProgress($query)
    {
        return $query->active()->whereNull('completed_at');
    }
    
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('completed_at');
    }
    
    // Accessors & Mutators
    public function getCurrentStatsAttribute($value): array
    {
        $stats = is_string($value) ? json_decode($value, true) : $value;
        
        return array_merge([
            'speed' => 0,
            'stamina' => 0,
            'power' => 0,
            'guts' => 0,
            'wit' => 0,
            'skill_points' => 0,
            'fans' => 0
        ], $stats ?: []);
    }
    
    public function setCurrentStatsAttribute($value): void
    {
        $this->attributes['current_stats'] = is_array($value) 
            ? json_encode($value) 
            : $value;
    }
    
    // Business Logic Methods
    public function getTotalStats(): int
    {
        $stats = $this->current_stats;
        return $stats['speed'] + $stats['stamina'] + $stats['power'] + 
               $stats['guts'] + $stats['wit'];
    }
    
    public function getProgressPercentage(): float
    {
        return ($this->current_turn / $this->max_turns) * 100;
    }
    
    public function canTrain(): bool
    {
        return $this->is_active && 
               $this->current_turn < $this->max_turns && 
               is_null($this->completed_at);
    }
    
    public function getStatRank(string $stat): string
    {
        $value = $this->current_stats[$stat] ?? 0;
        
        return match (true) {
            $value >= 1200 => 'SS',
            $value >= 1000 => 'S',
            $value >= 800 => 'A',
            $value >= 600 => 'B',
            $value >= 400 => 'C',
            $value >= 200 => 'D',
            default => 'G'
        };
    }
    
    public function getOverallRank(): string
    {
        $total = $this->getTotalStats();
        
        return match (true) {
            $total >= 6000 => 'SS',
            $total >= 5000 => 'S',
            $total >= 4000 => 'A',
            $total >= 3000 => 'B',
            $total >= 2000 => 'C',
            $total >= 1000 => 'D',
            default => 'G'
        };
    }
    
    public function completeTraining(): void
    {
        $this->update([
            'is_active' => false,
            'completed_at' => now()
        ]);
        
        event(new CharacterTrainingCompleted($this));
    }
    
    // Cache Management
    protected function getCacheTags(): array
    {
        return ['characters', "user:{$this->user_id}", "character:{$this->id}"];
    }
}
```

#### 4.1.2 Training System

```php
<?php

namespace App\Services\Training;

class TrainingCalculator
{
    private array $aptitudes;
    private array $currentStats;
    private array $supportCards;
    private array $baseGainRates;
    
    public function __construct(array $aptitudes, array $currentStats, array $supportCards = [])
    {
        $this->aptitudes = $aptitudes;
        $this->currentStats = $currentStats;
        $this->supportCards = $supportCards;
        $this->baseGainRates = config('game.training.base_gain_rates');
    }
    
    public function calculate(TrainingAction $action): TrainingCalculationResult
    {
        $baseGains = $this->calculateBaseGains($action);
        $aptitudeModifiers = $this->calculateAptitudeModifiers($action);
        $supportCardBonuses = $this->calculateSupportCardBonuses($action);
        $randomVariation = $this->calculateRandomVariation();
        
        $finalGains = $this->applyAllModifiers(
            $baseGains,
            $aptitudeModifiers,
            $supportCardBonuses,
            $randomVariation
        );
        
        $events = $this->checkForEvents($action, $finalGains);
        $skillsLearned = $this->checkForSkillLearning($action);
        
        return new TrainingCalculationResult(
            statsGained: $finalGains,
            events: $events,
            skillsLearned: $skillsLearned,
            successRate: $this->calculateSuccessRate($action),
            efficiencyScore: $this->calculateEfficiencyScore($finalGains, $action)
        );
    }
    
    private function calculateBaseGains(TrainingAction $action): array
    {
        $trainingType = $action->getTrainingType();
        $baseRates = $this->baseGainRates[$trainingType] ?? [];
        
        return [
            'speed' => $baseRates['speed'] ?? 0,
            'stamina' => $baseRates['stamina'] ?? 0,
            'power' => $baseRates['power'] ?? 0,
            'guts' => $baseRates['guts'] ?? 0,
            'wit' => $baseRates['wit'] ?? 0,
            'skill_points' => $baseRates['skill_points'] ?? 0
        ];
    }
    
    private function calculateAptitudeModifiers(TrainingAction $action): array
    {
        $modifiers = [];
        $trainingType = $action->getTrainingType();
        
        foreach (['speed', 'stamina', 'power', 'guts', 'wit'] as $stat) {
            $aptitude = $this->aptitudes[$stat] ?? 'G';
            $modifier = $this->getAptitudeModifier($aptitude, $stat, $trainingType);
            $modifiers[$stat] = $modifier;
        }
        
        return $modifiers;
    }
    
    private function getAptitudeModifier(string $aptitude, string $stat, string $trainingType): float
    {
        $aptitudeValues = [
            'SS' => 1.2,
            'S' => 1.1,
            'A' => 1.0,
            'B' => 0.9,
            'C' => 0.8,
            'D' => 0.7,
            'E' => 0.6,
            'F' => 0.5,
            'G' => 0.4
        ];
        
        $baseModifier = $aptitudeValues[$aptitude] ?? 1.0;
        
        // Apply additional modifiers based on training type matching stat
        if ($this->isMatchingTraining($stat, $trainingType)) {
            $baseModifier *= 1.1; // 10% bonus for matching training
        }
        
        return $baseModifier;
    }
    
    private function calculateSupportCardBonuses(TrainingAction $action): array
    {
        $bonuses = array_fill_keys(['speed', 'stamina', 'power', 'guts', 'wit', 'skill_points'], 0);
        
        foreach ($this->supportCards as $card) {
            $cardBonuses = $this->calculateSingleCardBonus($card, $action);
            
            foreach ($cardBonuses as $stat => $bonus) {
                $bonuses[$stat] += $bonus;
            }
        }
        
        return $bonuses;
    }
    
    private function calculateRandomVariation(): array
    {
        // Add 0-20% random variation to make training less predictable
        return [
            'speed' => mt_rand(100, 120) / 100,
            'stamina' => mt_rand(100, 120) / 100,
            'power' => mt_rand(100, 120) / 100,
            'guts' => mt_rand(100, 120) / 100,
            'wit' => mt_rand(100, 120) / 100,
            'skill_points' => mt_rand(100, 120) / 100
        ];
    }
    
    private function applyAllModifiers(
        array $baseGains,
        array $aptitudeModifiers,
        array $supportCardBonuses,
        array $randomVariation
    ): array {
        $finalGains = [];
        
        foreach ($baseGains as $stat => $baseGain) {
            $aptitudeModifier = $aptitudeModifiers[$stat] ?? 1.0;
            $supportBonus = $supportCardBonuses[$stat] ?? 0;
            $randomMod = $randomVariation[$stat] ?? 1.0;
            
            $finalGain = (($baseGain * $aptitudeModifier) + $supportBonus) * $randomMod;
            $finalGains[$stat] = max(0, round($finalGain));
        }
        
        return $finalGains;
    }
}
```

### 4.2 AI Integration System

#### 4.2.1 AI Service Interface

```php
<?php

namespace App\Integrations\AI\Contracts;

interface AIServiceInterface
{
    public function generateResponse(AIRequest $request): AIResponse;
    public function healthCheck(): array;
    public function getAvailableModels(): array;
    public function estimateCost(AIRequest $request): float;
}

namespace App\Integrations\AI;

class AIRequest
{
    public function __construct(
        private string $prompt,
        private array $context = [],
        private int $complexity = 5,
        private array $options = []
    ) {}
    
    public static function fromRequest(Request $request): self
    {
        return new self(
            prompt: $request->input('prompt'),
            context: $request->input('context', []),
            complexity: $request->input('complexity', 5),
            options: $request->input('options', [])
        );
    }
    
    public function getPrompt(): string
    {
        return $this->prompt;
    }
    
    public function getContext(): array
    {
        return $this->context;
    }
    
    public function getComplexity(): int
    {
        return $this->complexity;
    }
    
    public function getOptions(): array
    {
        return $this->options;
    }
    
    public function requiresMultilingual(): bool
    {
        return $this->options['multilingual'] ?? false;
    }
    
    public function getMaxTokens(): int
    {
        return $this->options['max_tokens'] ?? 2048;
    }
    
    public function getTemperature(): float
    {
        return $this->options['temperature'] ?? 0.7;
    }
}

class AIResponse
{
    public function __construct(
        private string $content,
        private string $model,
        private float $processingTime,
        private float $confidence,
        private float $cost = 0.0,
        private array $metadata = []
    ) {}
    
    public function getContent(): string
    {
        return $this->content;
    }
    
    public function getModel(): string
    {
        return $this->model;
    }
    
    public function getProcessingTime(): float
    {
        return $this->processingTime;
    }
    
    public function getConfidence(): float
    {
        return $this->confidence;
    }
    
    public function getCost(): float
    {
        return $this->cost;
    }
    
    public function getMetadata(): array
    {
        return $this->metadata;
    }
    
    public function isLocalProcessing(): bool
    {
        return $this->metadata['local_processing'] ?? false;
    }
    
    public function toArray(): array
    {
        return [
            'content' => $this->content,
            'model' => $this->model,
            'processing_time' => $this->processingTime,
            'confidence' => $this->confidence,
            'cost' => $this->cost,
            'metadata' => $this->metadata
        ];
    }
}
```

---

## 5. API Documentation

### 5.1 RESTful API Endpoints

#### 5.1.1 Character Management API

```php
<?php

namespace App\Http\Controllers\API\V1;

/**
 * @group Character Management
 * 
 * APIs for managing user characters, training sessions, and performance analytics.
 */
class CharacterController extends Controller
{
    public function __construct(
        private CharacterService $characterService,
        private TrainingService $trainingService
    ) {}
    
    /**
     * Get user characters
     * 
     * Retrieve all characters belonging to the authenticated user.
     * 
     * @authenticated
     * 
     * @queryParam active boolean Filter by active status. Example: true
     * @queryParam scenario_id integer Filter by scenario ID. Example: 1
     * @queryParam limit integer Number of characters to return. Example: 10
     * 
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "uuid": "550e8400-e29b-41d4-a716-446655440000",
     *       "name": "Special Week",
     *       "nickname": "Spechan",
     *       "current_turn": 15,
     *       "max_turns": 78,
     *       "current_stats": {
     *         "speed": 450,
     *         "stamina": 380,
     *         "power": 420,
     *         "guts": 350,
     *         "wit": 400,
     *         "skill_points": 120,
     *         "fans": 5000
     *       },
     *       "progress_percentage": 19.23,
     *       "overall_rank": "C",
     *       "is_active": true,
     *       "character_template": {
     *         "id": 1,
     *         "name": "Special Week",
     *         "rarity": 3
     *       },
     *       "scenario": {
     *         "id": 1,
     *         "name": "URA Finals"
     *       },
     *       "created_at": "2026-01-11T10:00:00Z",
     *       "updated_at": "2026-01-11T15:30:00Z"
     *     }
     *   ],
     *   "meta": {
     *     "total": 5,
     *     "active": 3,
     *     "completed": 2
     *   }
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $characters = $this->characterService->getUserCharacters(
            user: $request->user(),
            filters: $request->only(['active', 'scenario_id']),
            limit: $request->input('limit', 50)
        );
        
        return response()->json([
            'success' => true,
            'data' => CharacterResource::collection($characters),
            'meta' => [
                'total' => $characters->count(),
                'active' => $characters->where('is_active', true)->count(),
                'completed' => $characters->where('is_active', false)->count()
            ]
        ]);
    }
    
    /**
     * Create new character
     * 
     * Create a new character instance for the authenticated user.
     * 
     * @authenticated
     * 
     * @bodyParam character_template_id integer required The character template ID. Example: 1
     * @bodyParam name string required Character name. Example: Special Week
     * @bodyParam nickname string Character nickname. Example: Spechan
     * @bodyParam scenario_id integer Scenario ID. Example: 1
     * @bodyParam goals array Character goals and objectives. Example: ["win_twinkle_series", "reach_1000_fans"]
     * 
     * @response 201 {
     *   "success": true,
     *   "data": {
     *     "id": 2,
     *     "uuid": "550e8400-e29b-41d4-a716-446655440001",
     *     "name": "Special Week",
     *     "nickname": "Spechan",
     *     "current_turn": 0,
     *     "max_turns": 78,
     *     "current_stats": {
     *       "speed": 0,
     *       "stamina": 0,
     *       "power": 0,
     *       "guts": 0,
     *       "wit": 0,
     *       "skill_points": 0,
     *       "fans": 0
     *     },
     *     "is_active": true,
     *     "created_at": "2026-01-11T16:00:00Z"
     *   },
     *   "message": "Character created successfully"
     * }
     */
    public function store(CreateCharacterRequest $request): JsonResponse
    {
        $character = $this->characterService->createCharacter(
            user: $request->user(),
            data: $request->validated()
        );
        
        return response()->json([
            'success' => true,
            'data' => new CharacterResource($character),
            'message' => 'Character created successfully'
        ], 201);
    }
    
    /**
     * Get character details
     * 
     * Retrieve detailed information about a specific character.
     * 
     * @authenticated
     * 
     * @urlParam character string required Character UUID. Example: 550e8400-e29b-41d4-a716-446655440000
     * 
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "uuid": "550e8400-e29b-41d4-a716-446655440000",
     *     "name": "Special Week",
     *     "current_stats": {
     *       "speed": 450,
     *       "stamina": 380,
     *       "power": 420,
     *       "guts": 350,
     *       "wit": 400
     *     },
     *     "training_history": [
     *       {
     *         "turn": 15,
     *         "action": "speed_training",
     *         "stats_gained": {"speed": 25, "power": 5},
     *         "created_at": "2026-01-11T15:30:00Z"
     *       }
     *     ],
     *     "performance_analytics": {
     *       "total_stats": 2000,
     *       "avg_gain_per_turn": 28.5,
     *       "efficiency_score": 85.2,
     *       "predicted_final_stats": 3200
     *     }
     *   }
     * }
     */
    public function show(string $uuid): JsonResponse
    {
        $character = $this->characterService->getCharacterByUuid($uuid);
        
        $this->authorize('view', $character);
        
        return response()->json([
            'success' => true,
            'data' => new DetailedCharacterResource($character)
        ]);
    }
    
    /**
     * Execute training action
     * 
     * Execute a training action for the specified character.
     * 
     * @authenticated
     * 
     * @urlParam character string required Character UUID. Example: 550e8400-e29b-41d4-a716-446655440000
     * 
     * @bodyParam action string required Training action type. Example: speed_training
     * @bodyParam support_cards array Support cards to use. Example: [1, 2, 3]
     * @bodyParam request_ai_recommendation boolean Request AI recommendation. Example: true
     * 
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "character": {
     *       "current_turn": 16,
     *       "current_stats": {
     *         "speed": 475,
     *         "stamina": 380,
     *         "power": 425,
     *         "guts": 350,
     *         "wit": 400
     *       }
     *     },
     *     "training_result": {
     *       "stats_gained": {"speed": 25, "power": 5},
     *       "events_triggered": ["great_success"],
     *       "skills_learned": [],
     *       "success_rate": 95.5,
     *       "efficiency_score": 88.2
     *     },
     *     "ai_recommendation": {
     *       "content": "Excellent choice! Speed training with your current support cards...",
     *       "confidence": 0.92,
     *       "model": "ollama_llama3.3"
     *     }
     *   }
     * }
     */
    public function train(string $uuid, TrainingRequest $request): JsonResponse
    {
        $character = $this->characterService->getCharacterByUuid($uuid);
        
        $this->authorize('train', $character);
        
        $result = $this->trainingService->executeTraining(
            character: $character,
            action: TrainingAction::fromRequest($request)
        );
        
        return response()->json([
            'success' => true,
            'data' => [
                'character' => new CharacterResource($result->character),
                'training_result' => $result->toArray(),
                'ai_recommendation' => $result->aiRecommendation?->toArray()
            ]
        ]);
    }
}
```

#### 5.1.2 AI Integration API

```php
<?php

namespace App\Http\Controllers\API\V1;

/**
 * @group AI Integration
 * 
 * APIs for AI-powered recommendations, analysis, and optimization.
 */
class AIController extends Controller
{
    public function __construct(
        private AIServiceFactory $aiFactory,
        private AIUsageTracker $usageTracker
    ) {}
    
    /**
     * Get AI recommendation
     * 
     * Generate AI-powered training recommendations for a character.
     * 
     * @authenticated
     * 
     * @bodyParam character_id integer required Character ID. Example: 1
     * @bodyParam request_type string required Type of recommendation. Example: training_optimization
     * @bodyParam context array Additional context data. Example: {"current_turn": 15, "goals": ["speed_focus"]}
     * @bodyParam complexity integer Request complexity (1-10). Example: 7
     * @bodyParam prefer_local boolean Prefer local AI processing. Example: true
     * 
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "recommendation": {
     *       "content": "Based on your character's current stats and aptitudes, I recommend focusing on speed training for the next 3 turns...",
     *       "model": "ollama_llama3.3",
     *       "confidence": 0.89,
     *       "processing_time": 1250,
     *       "cost": 0.0,
     *       "suggestions": [
     *         {
     *           "action": "speed_training",
     *           "priority": "high",
     *           "expected_gain": {"speed": 28, "power": 6},
     *           "reasoning": "Your speed aptitude is A-rank and current speed is below optimal..."
     *         }
     *       ]
     *     },
     *     "usage": {
     *       "daily_requests": 15,
     *       "daily_cost": 0.05,
     *       "remaining_budget": 1.95
     *     }
     *   }
     * }
     */
    public function getRecommendation(AIRecommendationRequest $request): JsonResponse
    {
        $aiRequest = AIRequest::fromRequest($request);
        
        // Check usage limits
        $this->usageTracker->checkLimits($request->user());
        
        // Get optimal AI service
        $aiService = $this->aiFactory->createOptimal($aiRequest);
        
        $response = $aiService->generateResponse($aiRequest);
        
        // Track usage
        $this->usageTracker->recordUsage(
            user: $request->user(),
            response: $response
        );
        
        return response()->json([
            'success' => true,
            'data' => [
                'recommendation' => $response->toArray(),
                'usage' => $this->usageTracker->getUserUsage($request->user())
            ]
        ]);
    }
    
    /**
     * Analyze character performance
     * 
     * Get AI-powered analysis of character training performance and optimization suggestions.
     * 
     * @authenticated
     * 
     * @urlParam character string required Character UUID. Example: 550e8400-e29b-41d4-a716-446655440000
     * 
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "analysis": {
     *       "overall_performance": "above_average",
     *       "efficiency_score": 85.2,
     *       "strengths": ["consistent_speed_training", "good_support_card_usage"],
     *       "weaknesses": ["neglecting_stamina", "suboptimal_race_timing"],
     *       "recommendations": [
     *         "Increase stamina training frequency by 20%",
     *         "Consider entering G3 races for better fan gain"
     *       ],
     *       "predicted_outcomes": {
     *         "final_stats_estimate": {"speed": 1200, "stamina": 800, "power": 1100},
     *         "success_probability": 0.78,
     *         "areas_for_improvement": ["stamina", "race_strategy"]
     *       }
     *     }
     *   }
     * }
     */
    public function analyzePerformance(string $uuid): JsonResponse
    {
        $character = Character::where('uuid', $uuid)->firstOrFail();
        
        $this->authorize('view', $character);
        
        $analysisRequest = new AIRequest(
            prompt: $this->buildAnalysisPrompt($character),
            context: $this->gatherAnalysisContext($character),
            complexity: 8
        );
        
        $aiService = $this->aiFactory->createOptimal($analysisRequest);
        $response = $aiService->generateResponse($analysisRequest);
        
        return response()->json([
            'success' => true,
            'data' => [
                'analysis' => json_decode($response->getContent(), true),
                'metadata' => [
                    'model_used' => $response->getModel(),
                    'confidence' => $response->getConfidence(),
                    'processing_time' => $response->getProcessingTime()
                ]
            ]
        ]);
    }
}
```

---

This completes the first major section of the Source Code Documentation. The document provides comprehensive coverage of the project structure, architecture patterns, core components, and API documentation with detailed examples and specifications aligned with Laravel 12 and modern development practices.

---

## Document Control

| Version | Date | Author | Changes |
| ------- | ---- | ------ | ------- |
| 1.0 | 2026-01-11 | Development Team | Initial source code documentation |

---

*This document provides comprehensive source code documentation for the Umamusume Pretty Derby Career Planner system, including project structure, architecture patterns, core components, and API documentation.*
