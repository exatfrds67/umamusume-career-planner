# Software Integration Specifications (SIS)

## Umamusume Pretty Derby Career Planner

**Document Version**: 2.4.2
**Date**: April 7, 2026
**Project**: UmamusumeCareerPlanner
**Author**: Development Team
**Status**: Current - Aligned to codebase v2.4.2, 40 models, 67 migrations, 42 MCP tools

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

This document provides detailed technical specifications for all integration points within the
Umamusume Pretty Derby Career Planner application. It serves as the authoritative reference for
developers implementing and maintaining integrations between the Laravel 12 backend and external
services, AI providers, and internal subsystems.

### 1.1 Scope

This specification covers:

- AI provider integrations (Ollama, AWS Bedrock, Neuron AI)
- MCP (Model Context Protocol) server integrations (42 tool services, 9 orchestration agents)
- External API integrations (umapyoi.net, GameTora web scraping)
- OCR processing pipeline (Tesseract with GD preprocessing)
- Admin Panel services (database maintenance, system health, log reader)
- Security, authentication, and access control patterns

### 1.2 Related Documents

- **Document**: Software Integration Plan; **Reference**: [SIP - 007_SIP](007_SIP_Software_Integration_Plan.md)
- **Document**: Software Design Specifications; **Reference**: [SDS -
004_SDS](004_SDS_Software_Design_Specifications.md)
- **Document**: AI Advisory System Flow; **Reference**: [FLOW-006](../flows/FLOW-006_AI_Advisory_System.md)
- **Document**: External Integration Flow; **Reference**: [FLOW-007](../flows/FLOW-007_External_Integration_System.md)
- **Document**: AI Advisory Technical Spec; **Reference**: [SPEC-006](../specs/SPEC-006_AI_Advisory_Technical.md)
- **Document**: External Integration Tech Flow; **Reference**: [TECH-FLOW-007](../tech-flow/TECH-
FLOW-007_External_Integration_Flow.md)

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
```text

### 2.2 Neuron AI Integration

Neuron AI provides the agent orchestration framework for specialized AI capabilities.

**Configuration File**: `config/neuron.php`

- **Setting**: `agents.training`; **Description**: Training recommendation agent; **Default**: Enabled
- **Setting**: `agents.race_strategy`; **Description**: Race strategy optimization agent; **Default**: Enabled
- **Setting**: `agents.skill_advisor`; **Description**: Skill acquisition advisor agent; **Default**: Enabled
- **Setting**: `memory.driver`; **Description**: Memory persistence driver; **Default**: `database`
- **Setting**: `tools.enabled`; **Description**: Tool integration toggle; **Default**: `true`

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
```text

**Neuron Service Layer**: `app/Services/Neuron/`

```
app/Services/Neuron/
├── NeuronAIService.php
├── TrainingAdvisorService.php
├── RaceStrategyService.php
├── SkillRecommendationService.php
└── CareerPlanningService.php
```text

### 2.3 AI Service Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    AI SERVICE LAYER                             │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  HybridAIService (Provider orchestration)                │   │
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
```text

### 2.4 Provider Cost Tracking

- **Provider**: Ollama; **Model**: llama3.2; **Input Cost**: $0.00; **Output Cost**: $0.00; **Notes**: Local processing
- **Provider**: Bedrock; **Model**: Claude 4.5 Sonnet; **Input Cost**: $3.00/1M; **Output Cost**:
$15.00/1M; **Notes**: Recommended fallback
- **Provider**: Bedrock; **Model**: Claude 4.5 Opus; **Input Cost**: $5.00/1M; **Output Cost**:
$25.00/1M; **Notes**: Complex reasoning
- **Provider**: Bedrock; **Model**: Claude 4.5 Haiku; **Input Cost**: $1.00/1M; **Output Cost**:
$5.00/1M; **Notes**: Simple queries

---

## 3. MCP Integration

### 3.1 Server Configuration

The Model Context Protocol (MCP) integration enables tool-based AI interactions.

**Configuration Files**:

- `config/mcp.php` - Server definitions
- `config/mcp_tools.php` - Tool controls
- `config/mcp-agents.php` - Agent-specific settings

### 3.2 MCP Server Types

- **Server Type**: Memory MCP; **Location**: Local; **Purpose**: Conversation context persistence
- **Server Type**: Filesystem MCP; **Location**: Local; **Purpose**: Document and file access
- **Server Type**: Fetch MCP; **Location**: Local; **Purpose**: HTTP resource retrieval
- **Server Type**: AWS MCP; **Location**: Remote; **Purpose**: AWS API/Knowledge/Pricing tools integration
- **Server Type**: Context7 MCP; **Location**: Remote; **Purpose**: Context-aware tool services
- **Server Type**: Tool Chaining MCP; **Location**: Local; **Purpose**: Multi-step tool orchestration
- **Server Type**: Custom MCP; **Location**: Remote (optional); **Purpose**: Domain-specific tools

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
```text

### 3.4 Monitoring Integration

**Service**: `MCPMonitoringService`

- **Metric**: Tool invocations; **Description**: Count per tool per session; **Storage**: `mcp_tool_usage` table
- **Metric**: Response times; **Description**: Latency per server; **Storage**: Redis cache
- **Metric**: Error rates; **Description**: Failures by server/tool; **Storage**: Database
- **Metric**: Token usage; **Description**: Input/output token counts; **Storage**: Database

---

## 4. External API Integration

### 4.1 Primary APIs

The application integrates with external game data sources for character, skill, and meta information.

**Configuration File**: `config/external-apis.php`

- **API**: umapyoi.net; **Client Class**: `UmapyoiApiClient`; **Purpose**: Primary game data source
(characters, support cards); **Cache TTL**: 24 hours
- **API**: GameTora; **Client Class**: `GameToraScraperService`; **Purpose**: Skill, race data via
web scraping; **Cache TTL**: 24 hours

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
```text

### 4.3 Resilience Patterns

**Circuit Breaker States**:

- **State**: CLOSED; **Description**: Normal operation; **Behavior**: Requests pass through
- **State**: OPEN; **Description**: Failure threshold exceeded; **Behavior**: Return cached/fallback immediately
- **State**: HALF_OPEN; **Description**: Recovery check period; **Behavior**: Allow limited test requests

**Configuration**:

```php
'circuit_breaker' => [
    'failure_threshold' => 5,
    'recovery_timeout' => 60, // seconds
    'sample_window' => 120, // seconds
],
```

### 4.4 Data Sync Workflow

```text
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

```text
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

- **Operation**: Resize; **Purpose**: Normalize dimensions; **Configuration**: Max 2000px width
- **Operation**: Grayscale; **Purpose**: Improve contrast; **Configuration**: GD `imagefilter()`
- **Operation**: Threshold; **Purpose**: Binary conversion; **Configuration**: Adaptive threshold
- **Operation**: Deskew; **Purpose**: Correct rotation; **Configuration**: Angle detection
- **Operation**: Denoise; **Purpose**: Remove artifacts; **Configuration**: Median filter

### 5.4 Supported Data Types

- **Data Type**: Character stats; **Detection Pattern**: Stat labels + numeric values; **Confidence Threshold**: 85%
- **Data Type**: Skill names; **Detection Pattern**: Japanese/English text regions; **Confidence Threshold**: 80%
- **Data Type**: Race results; **Detection Pattern**: Placement + time format; **Confidence Threshold**: 90%
- **Data Type**: Support cards; **Detection Pattern**: Card frame detection; **Confidence Threshold**: 75%

---

## 6. Security and Access

### 6.1 Authentication

**Method**: Laravel Sanctum token-based authentication

- **Route Type**: Web routes; **Authentication**: Session-based; **Rate Limit**: 60/minute
- **Route Type**: API routes; **Authentication**: Sanctum tokens; **Rate Limit**: 100/minute
- **Route Type**: AI endpoints; **Authentication**: Authenticated + cost tracking; **Rate Limit**: 30/minute

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
```text

### 6.3 Rate Limiting Configuration

**Middleware Definition**: `app/Http/Middleware/`

- **Limiter**: `api`; **Limit**: 100; **Window**: 1 minute; **Applied To**: All API routes
- **Limiter**: `ai`; **Limit**: 30; **Window**: 1 minute; **Applied To**: AI advisory endpoints
- **Limiter**: `ocr`; **Limit**: 10; **Window**: 1 minute; **Applied To**: OCR upload endpoints
- **Limiter**: `external`; **Limit**: 60; **Window**: 1 minute; **Applied To**: External API proxies

### 6.4 Data Sanitization

- **Input Source**: Form inputs; **Sanitization Method**: Laravel validation + Eloquent escaping; **Notes**: Automatic
- **Input Source**: File uploads; **Sanitization Method**: MIME validation + virus scan; **Notes**: Strict type checking
- **Input Source**: API responses; **Sanitization Method**: JSON schema validation; **Notes**: External data
- **Input Source**: OCR output; **Sanitization Method**: Pattern matching + range validation;
**Notes**: Manual review option

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
│  │   ├── HybridAIService.php        (Provider orchestration)   │
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
│  │   ├── RealTimeMonitoringService.php (Operational monitoring)│
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
```text

### 7.2 Dependency Injection

All integration services are registered in service providers:

```php
// app/Providers/IntegrationServiceProvider.php

public function register(): void
{
    $this->app->singleton(HybridAIService::class);
    $this->app->singleton(MCPHealthDashboardService::class);
    $this->app->singleton(ExternalAPIService::class);
    $this->app->singleton(TesseractService::class);
}
```

### 7.3 Configuration Priority

- **Priority**: 1 (Highest); **Source**: Environment variables; **Override Capability**: Runtime
- **Priority**: 2; **Source**: Config files; **Override Capability**: Deployment
- **Priority**: 3; **Source**: Database settings; **Override Capability**: User-configurable
- **Priority**: 4 (Lowest); **Source**: Code defaults; **Override Capability**: Static

---

## 8. Data Flow Specifications

### 8.1 AI Advisory Request Flow

```text
User Query ──► Controller ──► HybridAIService
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

```text
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

```text
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

- **Version**: 2.4.0; **Date**: 2026-02-22; **Author**: Development Team; **Changes**: Updated
service layer (AI/MCP/ExternalAPI/OCR/Admin/Neuron), 42 MCP tools, 9 agents, GameTora scraper, Admin
Panel services
- **Version**: 2.3.0; **Date**: 2026-02-21; **Author**: Development Team; **Changes**: Updated
Neuron agent tree, Bedrock model names (Claude 4.x), version alignment to v2.3.0
- **Version**: 2.1.0; **Date**: 2026-01-23; **Author**: Development Team; **Changes**: Updated specs
to match configured integrations; added architecture diagrams
- **Version**: 2.0.0; **Date**: 2026-01-14; **Author**: Development Team; **Changes**: Major
revision with Neuron AI and MCP integration
- **Version**: 1.0.0; **Date**: 2026-01-03; **Author**: Development Team; **Changes**: Initial specification

---

## Appendices

### A. Environment Variables Reference

- **Variable**: `AI_DEFAULT_PROVIDER`; **Service**: AI; **Default**: `ollama`; **Description**: Primary AI provider
- **Variable**: `OLLAMA_BASE_URL`; **Service**: AI; **Default**: `http://localhost:11434`;
**Description**: Ollama API endpoint
- **Variable**: `BEDROCK_ENABLED`; **Service**: AI; **Default**: `false`; **Description**: Enable AWS Bedrock fallback
- **Variable**: `MCP_ENABLED`; **Service**: MCP; **Default**: `true`; **Description**: Enable MCP integration
- **Variable**: `EXTERNAL_API_CACHE_TTL`; **Service**: External; **Default**: `86400`;
**Description**: Cache duration in seconds
- **Variable**: `OCR_CONFIDENCE_THRESHOLD`; **Service**: OCR; **Default**: `80`; **Description**:
Minimum confidence percentage

### B. Error Codes

- **Code**: `AI_001`; **Service**: AI; **Description**: Provider unavailable
- **Code**: `AI_002`; **Service**: AI; **Description**: Rate limit exceeded
- **Code**: `AI_003`; **Service**: AI; **Description**: Cost threshold exceeded
- **Code**: `MCP_001`; **Service**: MCP; **Description**: Server connection failed
- **Code**: `MCP_002`; **Service**: MCP; **Description**: Tool execution error
- **Code**: `EXT_001`; **Service**: External; **Description**: API request timeout
- **Code**: `EXT_002`; **Service**: External; **Description**: Circuit breaker open
- **Code**: `OCR_001`; **Service**: OCR; **Description**: Invalid image format
- **Code**: `OCR_002`; **Service**: OCR; **Description**: Extraction confidence too low

---

### This specification reflects the integration details of the current implementation and serves as
the authoritative reference for all integration-related development
