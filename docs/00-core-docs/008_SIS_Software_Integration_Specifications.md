# Software Integration Specifications (SIS)

## Umamusume Pretty Derby Career Planner

**Document Version**: 2.4.0  
**Date**: February 22, 2026  
**Project**: UmamusumeCareerPlanner  
**Author**: Development Team  
**Status**: Current - Aligned to codebase v2.4.0, 30 models, 51 migrations, 42 MCP tools

---

## Table of Contents

1. [Introduction](#1-introduction)
2. [AI Provider Integration](#2-ai-provider-integration)
3. [MCP Integration](#3-mcp-integration)
4. [External API Integration](#4-external-api-integration)
5. [OCR Integration](#5-ocr-integration)
6. [Security and Access](#6-security-and-access)
7. [Integration Architecture](#7-integration-architecture)
8. [Data Flow Specifications](#8-data-flow-specifications)

---

## 1. Introduction

This document provides detailed technical specifications for all integration points within the Umamusume Pretty Derby Career Planner application. It serves as the authoritative reference for developers implementing and maintaining integrations between the Laravel 12 backend and external services, AI providers, and internal subsystems.

### 1.1 Scope

This specification covers:

- AI provider integrations (Ollama, AWS Bedrock, Neuron AI)
- MCP (Model Context Protocol) server integrations (42 tool services, 9 orchestration agents)
- External API integrations (umapyoi.net, GameTora web scraping)
- OCR processing pipeline (Tesseract with GD preprocessing)
- Admin Panel services (database maintenance, system health, log reader)
- Security, authentication, and access control patterns

### 1.2 Related Documents

| Document | Reference |
|----------|-----------|
| Software Integration Plan | [SIP - 007_SIP](007_SIP_Software_Integration_Plan.md) |
| Software Design Specifications | [SDS - 004_SDS](004_SDS_Software_Design_Specifications.md) |
| AI Advisory System Flow | [FLOW-006](../flows/FLOW-006_AI_Advisory_System.md) |
| External Integration Flow | [FLOW-007](../flows/FLOW-007_External_Integration_System.md) |
| AI Advisory Technical Spec | [SPEC-006](../specs/SPEC-006_AI_Advisory_Technical.md) |
| External Integration Tech Flow | [TECH-FLOW-007](../tech-flow/TECH-FLOW-007_External_Integration_Flow.md) |

---

## 2. AI Provider Integration

### 2.1 Hybrid AI Configuration

The application implements a hybrid AI architecture with local-first processing and cloud fallback capabilities.

**Configuration File**: `config/ai.php`

```php
return [
    'default_provider' => env('AI_DEFAULT_PROVIDER', 'ollama'),
    
    'providers' => [
        'ollama' => [
            'enabled' => env('OLLAMA_ENABLED', true),
            'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
            'model' => env('OLLAMA_MODEL', 'llama3.2'),
            'timeout' => env('OLLAMA_TIMEOUT', 30),
            'temperature' => env('OLLAMA_TEMPERATURE', 0.7),
        ],
        'bedrock' => [
            'enabled' => env('BEDROCK_ENABLED', false),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'model' => env('BEDROCK_MODEL', 'anthropic.claude-3-sonnet'),
            'max_tokens' => env('BEDROCK_MAX_TOKENS', 4096),
        ],
    ],
    
    'routing' => [
        'cost_threshold' => env('AI_COST_THRESHOLD', 0.10),
        'fallback_enabled' => env('AI_FALLBACK_ENABLED', true),
        'retry_attempts' => env('AI_RETRY_ATTEMPTS', 3),
    ],
];
```

### 2.2 Neuron AI Integration

Neuron AI provides the agent orchestration framework for specialized AI capabilities.

**Configuration File**: `config/neuron.php`

| Setting | Description | Default |
|---------|-------------|---------|
| `agents.training` | Training recommendation agent | Enabled |
| `agents.race_strategy` | Race strategy optimization agent | Enabled |
| `agents.skill_advisor` | Skill acquisition advisor agent | Enabled |
| `memory.driver` | Memory persistence driver | `database` |
| `tools.enabled` | Tool integration toggle | `true` |

**Agent Classes Location**: `app/Neuron/Agents/`

```
app/Neuron/
├── Agents/
│   ├── BaseAgent.php
│   ├── TrainingAdvisorAgent.php
│   ├── RaceStrategyAgent.php
│   ├── SkillRecommendationAgent.php
│   ├── CareerPlanningAgent.php
│   ├── McpDemoAgent.php
│   └── Tools/
│       ├── CharacterStatsTool.php
│       ├── RaceDataTool.php
│       └── SkillDataTool.php
├── Responses/
└── Support/
    ├── McpConnectorFactory.php
    └── McpToolIntegration.php
```

**Neuron Service Layer**: `app/Services/Neuron/`

```
app/Services/Neuron/
├── NeuronAIService.php
├── TrainingAdvisorService.php
├── RaceStrategyService.php
├── SkillRecommendationService.php
└── CareerPlanningService.php
```

### 2.3 AI Service Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    AI SERVICE LAYER                             │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  AIAdvisoryService (Orchestration)                        │   │
│  │  • Route requests to appropriate provider                 │   │
│  │  • Handle fallback logic                                  │   │
│  │  • Track costs and usage                                  │   │
│  └──────────────────────────────────────────────────────────┘   │
│                           ↓                                      │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐  │
│  │  OllamaService  │  │  BedrockService │  │  NeuronAgents   │  │
│  │  (Local First)  │  │  (Cloud Fallback)│  │  (Specialized)  │  │
│  └─────────────────┘  └─────────────────┘  └─────────────────┘  │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 2.4 Provider Cost Tracking

| Provider | Model | Input Cost | Output Cost | Notes |
|----------|-------|------------|-------------|-------|
| Ollama | llama3.2 | $0.00 | $0.00 | Local processing |
| Bedrock | Claude Sonnet 4 | $3.00/1M | $15.00/1M | Recommended fallback |
| Bedrock | Claude Opus 4 | $5.00/1M | $25.00/1M | Complex reasoning |
| Bedrock | Claude Haiku 4.5 | $1.00/1M | $5.00/1M | Simple queries |

---

## 3. MCP Integration

### 3.1 Server Configuration

The Model Context Protocol (MCP) integration enables tool-based AI interactions.

**Configuration Files**:

- `config/mcp.php` - Server definitions
- `config/mcp_tools.php` - Tool controls
- `config/mcp-agents.php` - Agent-specific settings

### 3.2 MCP Server Types

| Server Type | Location | Purpose |
|-------------|----------|---------|
| Memory | Local | Conversation context persistence |
| Filesystem | Local | Document and file access |
| Fetch | Local | HTTP resource retrieval |
| AWS API | Remote | AWS service API integration |
| AWS Knowledge | Remote | AWS knowledge base queries |
| AWS Pricing | Remote | AWS cost/pricing lookups |
| Context7 | Remote | Context-aware tool services |
| Tool Chaining | Local | Multi-step tool orchestration |
| Umapyoi | Remote (optional) | Game data API integration |

### 3.3 MCP Service Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    MCP INTEGRATION LAYER                        │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  MCPOrchestrator                                          │   │
│  │  • Server lifecycle management                            │   │
│  │  • Tool routing and execution (42 tools)                  │   │
│  │  • Response aggregation                                   │   │
│  │  • 9 orchestration agents                                 │   │
│  └──────────────────────────────────────────────────────────┘   │
│                           ↓                                      │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐  │
│  │  MCPMonitoring  │  │  MCPHealthDash  │  │  MCPToolUsage   │  │
│  │  Service        │  │  boardService   │  │  Model          │  │
│  └─────────────────┘  └─────────────────┘  └─────────────────┘  │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 3.4 Monitoring Integration

**Service**: `MCPMonitoringService`

| Metric | Description | Storage |
|--------|-------------|---------|
| Tool invocations | Count per tool per session | `mcp_tool_usage` table |
| Response times | Latency per server | Redis cache |
| Error rates | Failures by server/tool | Database |
| Token usage | Input/output token counts | Database |

---

## 4. External API Integration

### 4.1 Primary APIs

The application integrates with external game data sources for character, skill, and meta information.

**Configuration File**: `config/external-apis.php`

| API | Client Class | Purpose | Cache TTL |
|-----|--------------|---------|-----------|
| umapyoi.net | `UmapyoiApiClient` | Primary game data source (characters, support cards) | 24 hours |
| GameTora | `GameToraScraperService` | Skill, race data via web scraping | 24 hours |

```
app/Services/ExternalAPI/
├── Contracts/
│   └── (interfaces)
├── Clients/
│   ├── UmapyoiApiClient.php
│   └── UmamusumeDBApiClient.php
├── ExternalAPIService.php
├── ExternalAPIFacade.php
├── CircuitBreaker (pattern - via services)
├── CacheManagerService.php
├── APIHealthMonitorService.php
├── APIAlertingService.php
├── APIPerformanceMetricsService.php
├── AutomatedUpdateDetectionService.php
├── BackgroundSyncService.php
├── ConflictDetectionService.php
├── ConflictResolutionService.php
├── ConnectivityMonitorService.php
├── DataQualityScoringService.php
├── DataValidationService.php
├── GracefulDegradationService.php
├── PerformanceOptimizationService.php
├── ResponseTransformer.php
└── ResponseValidator.php
```

### 4.3 Resilience Patterns

**Circuit Breaker States**:

| State | Description | Behavior |
|-------|-------------|----------|
| CLOSED | Normal operation | Requests pass through |
| OPEN | Failure threshold exceeded | Return cached/fallback immediately |
| HALF_OPEN | Recovery check period | Allow limited test requests |

**Configuration**:

```php
'circuit_breaker' => [
    'failure_threshold' => 5,
    'recovery_timeout' => 60, // seconds
    'sample_window' => 120, // seconds
],
```

### 4.4 Data Sync Workflow

```
External API Request Flow:
                                                                    
 Request ──► Circuit ──► Rate ──► API ──► Response ──► Cache
            Breaker    Limiter   Call     Parser      Store
               │                            │
               │ (OPEN)                     │ (Parse Error)
               ▼                            ▼
            Cached                       Fallback
           Response                       Data
```

---

## 5. OCR Integration

### 5.1 Processing Pipeline

The OCR integration enables screenshot-based data import for character stats and race results.

**Service Classes**:

- `ImageProcessingService` - Preprocessing with GD library
- `TesseractService` / `TesseractServiceEnhanced` - OCR text extraction
- `OCR/DataExtractionService` - Structured data extraction
- `OCR/DataValidationService` - Data validation and correction
- `OCR/DataTransformationService` - Data transformation pipeline
- `OCR/DataIntegrationService` - Import OCR results to models
- `OCR/ScreenTypeDetector` - Auto-detect screenshot type
- `OCR/ParserFactory` + `OCR/Parsers/` - Modular parsing system

### 5.2 Pipeline Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    OCR PROCESSING PIPELINE                      │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌───────────┐   ┌───────────┐   ┌───────────┐   ┌───────────┐ │
│  │  Upload   │──►│ Preprocess│──►│  Tesseract │──►│  Parser   │ │
│  │  Handler  │   │  (GD)     │   │  OCR      │   │  Service  │ │
│  └───────────┘   └───────────┘   └───────────┘   └───────────┘ │
│                                                        │        │
│                                                        ▼        │
│                                              ┌───────────────┐  │
│                                              │  Validation   │  │
│                                              │  & Correction │  │
│                                              └───────────────┘  │
│                                                        │        │
│                                                        ▼        │
│                                              ┌───────────────┐  │
│                                              │  Data Import  │  │
│                                              │  Service      │  │
│                                              └───────────────┘  │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 5.3 Preprocessing Operations

| Operation | Purpose | Configuration |
|-----------|---------|---------------|
| Resize | Normalize dimensions | Max 2000px width |
| Grayscale | Improve contrast | GD `imagefilter()` |
| Threshold | Binary conversion | Adaptive threshold |
| Deskew | Correct rotation | Angle detection |
| Denoise | Remove artifacts | Median filter |

### 5.4 Supported Data Types

| Data Type | Detection Pattern | Confidence Threshold |
|-----------|-------------------|---------------------|
| Character stats | Stat labels + numeric values | 85% |
| Skill names | Japanese/English text regions | 80% |
| Race results | Placement + time format | 90% |
| Support cards | Card frame detection | 75% |

---

## 6. Security and Access

### 6.1 Authentication

**Method**: Laravel Sanctum token-based authentication

| Route Type | Authentication | Rate Limit |
|------------|----------------|------------|
| Web routes | Session-based | 60/minute |
| API routes | Sanctum tokens | 100/minute |
| AI endpoints | Authenticated + cost tracking | 30/minute |

### 6.2 Request Validation

All integrations implement Laravel Form Request validation:

```php
// Example: AI Advisory Request
class AIAdvisoryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'query' => 'required|string|max:2000',
            'context' => 'nullable|array',
            'context.character_id' => 'nullable|exists:ucp_characters,id',
            'provider' => 'nullable|in:ollama,bedrock,auto',
        ];
    }
}
```

### 6.3 Rate Limiting Configuration

**Middleware Definition**: `app/Http/Middleware/`

| Limiter | Limit | Window | Applied To |
|---------|-------|--------|------------|
| `api` | 100 | 1 minute | All API routes |
| `ai` | 30 | 1 minute | AI advisory endpoints |
| `ocr` | 10 | 1 minute | OCR upload endpoints |
| `external` | 60 | 1 minute | External API proxies |

### 6.4 Data Sanitization

| Input Source | Sanitization Method | Notes |
|--------------|---------------------|-------|
| Form inputs | Laravel validation + Eloquent escaping | Automatic |
| File uploads | MIME validation + virus scan | Strict type checking |
| API responses | JSON schema validation | External data |
| OCR output | Pattern matching + range validation | Manual review option |

---

## 7. Integration Architecture

### 7.1 Service Layer Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                    INTEGRATION SERVICES                         │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  app/Services/                                                  │
│  ├── AI/                                                        │
│  │   ├── AIAdvisoryService.php      (Orchestration)            │
│  │   ├── AIDashboardService.php     (Dashboard analytics)      │
│  │   ├── HybridAIService.php        (Provider routing)         │
│  │   ├── OllamaService.php          (Local AI)                 │
│  │   ├── BedrockService.php         (Cloud AI)                 │
│  │   ├── BedrockConfigurationService.php (Config)              │
│  │   ├── CostTrackingService.php    (Usage metrics)            │
│  │   ├── ConversationManagementService.php (Chat history)     │
│  │   ├── ConversationAnalyticsService.php (Analytics)         │
│  │   ├── VectorStoreService.php     (Embeddings)              │
│  │   └── WorkflowExportService.php  (Export workflows)         │
│  │                                                              │
│  ├── MCP/                                                       │
│  │   ├── MCPClientService.php       (Client integration)       │
│  │   ├── AgentOrchestrationService.php (Agent management)     │
│  │   ├── MCPMonitoringService.php   (Health tracking)          │
│  │   ├── MCPHealthDashboardService.php (Dashboard data)        │
│  │   ├── AgentLifecycleManager.php  (Lifecycle)                │
│  │   ├── AgentRoutingService.php    (Routing)                  │
│  │   ├── AgentMemoryService.php     (Memory)                   │
│  │   ├── CostManagementService.php  (Cost tracking)            │
│  │   ├── RealTimeMonitoringService.php (Real-time)            │
│  │   ├── Tools/ (6 tool services)                                │
│  │   └── Agents/ (9 orchestration agents)                        │
│  │                                                              │
│  ├── ExternalAPI/                                               │
│  │   ├── ExternalAPIService.php     (Unified interface)        │
│  │   ├── UmapyoiApiClient.php       (Primary source)           │
│  │   ├── UmamusumeDBApiClient.php   (Fallback source)          │
│  │   ├── APIHealthMonitorService.php                             │
│  │   └── GracefulDegradationService.php (Resilience)           │
│  │                                                              │
│  ├── OCR/                                                       │
│  │   ├── DataExtractionService.php  (Text extraction)          │
│  │   ├── DataValidationService.php  (Quality control)          │
│  │   ├── DataTransformationService.php (Transform)             │
│  │   ├── DataIntegrationService.php (Import)                   │
│  │   ├── ScreenTypeDetector.php     (Auto-detect)              │
│  │   └── ParserFactory.php + Parsers/ (Modular parsing)        │
│  │                                                              │
│  ├── Admin/                                                     │
│  │   ├── DatabaseMaintenanceService.php (Backups, migrations)  │
│  │   ├── SystemHealthService.php    (System optimization)      │
│  │   └── LogReaderService.php       (Log viewing)              │
│  │                                                              │
│  └── Neuron/                                                    │
│      ├── NeuronAIService.php        (Core Neuron integration) │
│      ├── TrainingAdvisorService.php                              │
│      ├── RaceStrategyService.php                                 │
│      ├── SkillRecommendationService.php                          │
│      └── CareerPlanningService.php                               │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 7.2 Dependency Injection

All integration services are registered in service providers:

```php
// app/Providers/IntegrationServiceProvider.php

public function register(): void
{
    $this->app->singleton(AIAdvisoryService::class);
    $this->app->singleton(MCPOrchestrator::class);
    $this->app->singleton(ExternalAPIService::class);
    $this->app->singleton(TesseractService::class);
}
```

### 7.3 Configuration Priority

| Priority | Source | Override Capability |
|----------|--------|---------------------|
| 1 (Highest) | Environment variables | Runtime |
| 2 | Config files | Deployment |
| 3 | Database settings | User-configurable |
| 4 (Lowest) | Code defaults | Static |

---

## 8. Data Flow Specifications

### 8.1 AI Advisory Request Flow

```
User Query ──► Controller ──► AIAdvisoryService
                                    │
                    ┌───────────────┼───────────────┐
                    ▼               ▼               ▼
               OllamaService  NeuronAgent    BedrockService
               (Primary)      (Specialized)  (Fallback)
                    │               │               │
                    └───────────────┼───────────────┘
                                    ▼
                            Response Formatter
                                    │
                                    ▼
                            Cost Tracking
                                    │
                                    ▼
                              JSON Response
```

### 8.2 External Data Sync Flow

```
Scheduler Trigger ──► ExternalAPIService
                            │
                    ┌───────┴───────┐
                    ▼               ▼
             CircuitBreaker    RateLimiter
                    │               │
                    └───────┬───────┘
                            ▼
                      API Request
                            │
              ┌─────────────┼─────────────┐
              ▼             ▼             ▼
           Success       Timeout        Error
              │             │             │
              ▼             ▼             ▼
         Parse Data    Retry Logic   Fallback API
              │             │             │
              └─────────────┼─────────────┘
                            ▼
                      Cache Update
                            │
                            ▼
                    Database Sync
```

### 8.3 OCR Processing Flow

```
Image Upload ──► Validation ──► Storage
                                   │
                                   ▼
                          ImageProcessingService
                          (Resize, Grayscale, Threshold)
                                   │
                                   ▼
                           TesseractService
                           (Text Extraction)
                                   │
                                   ▼
                           OCRParserService
                           (Pattern Matching)
                                   │
                                   ▼
                         OCRValidationService
                         (Confidence Scoring)
                                   │
                    ┌──────────────┼──────────────┐
                    ▼              ▼              ▼
               High Conf     Medium Conf     Low Conf
               (Auto-import) (Review UI)    (Manual Entry)
```

---

## Document Control

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 2.4.0 | 2026-02-22 | Development Team | Updated service layer (AI/MCP/ExternalAPI/OCR/Admin/Neuron), 42 MCP tools, 9 agents, GameTora scraper, Admin Panel services |
| 2.3.0 | 2026-02-21 | Development Team | Updated Neuron agent tree, Bedrock model names (Claude 4.x), version alignment to v2.3.0 |
| 2.1.0 | 2026-01-23 | Development Team | Updated specs to match configured integrations; added architecture diagrams |
| 2.0.0 | 2026-01-14 | Development Team | Major revision with Neuron AI and MCP integration |
| 1.0.0 | 2026-01-03 | Development Team | Initial specification |

---

## Appendices

### A. Environment Variables Reference

| Variable | Service | Default | Description |
|----------|---------|---------|-------------|
| `AI_DEFAULT_PROVIDER` | AI | `ollama` | Primary AI provider |
| `OLLAMA_BASE_URL` | AI | `http://localhost:11434` | Ollama API endpoint |
| `BEDROCK_ENABLED` | AI | `false` | Enable AWS Bedrock fallback |
| `MCP_ENABLED` | MCP | `true` | Enable MCP integration |
| `EXTERNAL_API_CACHE_TTL` | External | `86400` | Cache duration in seconds |
| `OCR_CONFIDENCE_THRESHOLD` | OCR | `80` | Minimum confidence percentage |

### B. Error Codes

| Code | Service | Description |
|------|---------|-------------|
| `AI_001` | AI | Provider unavailable |
| `AI_002` | AI | Rate limit exceeded |
| `AI_003` | AI | Cost threshold exceeded |
| `MCP_001` | MCP | Server connection failed |
| `MCP_002` | MCP | Tool execution error |
| `EXT_001` | External | API request timeout |
| `EXT_002` | External | Circuit breaker open |
| `OCR_001` | OCR | Invalid image format |
| `OCR_002` | OCR | Extraction confidence too low |

---

*This specification reflects the integration details of the current implementation and serves as the authoritative reference for all integration-related development.*
