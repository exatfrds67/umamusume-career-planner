# Software Integration Plan (SIP)

## Umamusume Pretty Derby Career Planner

**Document Version**: 2.4.0
**Date**: February 22, 2026
**Project**: UmamusumeCareerPlanner
**Author**: Development Team
**Status**: Current - Aligned to codebase v2.4.0, 571 routes, 3,316+ tests, 11,563+ assertions

---

## Table of Contents

1. [Purpose](#1-purpose)
2. [Integration Targets](#2-integration-targets)
3. [Integration Architecture](#3-integration-architecture)
4. [Integration Strategy](#4-integration-strategy)
5. [Integration Points](#5-integration-points)
6. [Data Flow Integration](#6-data-flow-integration)
7. [Integration Test Environment](#7-integration-test-environment)
8. [Integration Scenarios](#8-integration-scenarios)
9. [Integration Management](#9-integration-management)
10. [Sign-off Criteria](#10-sign-off-criteria)

---

## 1. Purpose

This Software Integration Plan defines the integration strategy for external services, internal
subsystems, and AI components within the Umamusume Pretty Derby Career Planner. It ensures seamless
coordination between the Laravel 12 backend, Neuron AI agents, MCP servers (42 tool services),
external APIs, the OCR pipeline, and the Admin Panel.

---

## 2. Integration Targets

### 2.1 AI Providers

| Provider | Type | Purpose | Status |
| --- | --- | --- | --- |
| Ollama | Local | Primary AI recommendations | Implemented |
| AWS Bedrock | Cloud | Fallback AI (Claude models) | Implemented |
| Neuron AI | Framework | Agent orchestration | Implemented |

### 2.2 MCP Servers

| Server | Type | Purpose | Status |
| --- | --- | --- | --- |
| Memory MCP | Local | Conversation context | Implemented |
| Filesystem MCP | Local | File operations | Implemented |
| Fetch MCP | Local | HTTP requests | Implemented |
| AWS MCP | Remote | AWS API/Knowledge/Pricing tools | Implemented |
| Context7 MCP | Remote | Context-aware tool services | Implemented |
| Tool Chaining MCP | Local | Multi-step tool orchestration | Implemented |
| Custom MCP | Remote | Domain-specific tools | Optional |

### 2.3 MCP Agent Orchestration

| Agent | Purpose | Status |
| --- | --- | --- |
| CareerStrategyAgent | Career path optimization | Implemented |
| HintFarmingStrategyAgent | Hint acquisition planning | Implemented |
| LongTermDevelopmentAgent | Multi-career progression | Implemented |
| PerformanceAnalyticsAgent | Stats analysis and trending | Implemented |
| ResourceManagementAgent | SP/energy budget optimization | Implemented |
| SkillBuildPlanningAgent | Skill set composition | Implemented |
| SPBudgetManagementAgent | SP allocation strategy | Implemented |
| SummerCampOptimizationAgent | Summer camp event strategy | Implemented |
| TrainingOptimizationAgent | Training session optimization | Implemented |

### 2.3 External APIs

| API | Purpose | Status |
| --- | --- | --- |
| umapyoi.net | Character and support card data | Implemented |
| GameTora | Skill and race data (web scraping) | Implemented |

### 2.4 Admin Panel

| Component | Purpose | Status |
| --- | --- | --- |
| DatabaseMaintenanceService | Database backups, migrations, seeding | Implemented |
| SystemHealthService | System optimization and health checks | Implemented |
| LogReaderService | Log viewing and analysis | Implemented |

### 2.4 OCR Engine

| Component | Technology | Purpose | Status |
| --- | --- | --- | --- |
| OCR Service | Tesseract + GD | Screenshot parsing | Implemented |
| Preprocessing | GD Library | Image enhancement | Implemented |
| Parser | Custom (ParserFactory + Parsers/) | Data extraction | Implemented |
| Screen Type Detector | Custom | Auto-detect screenshot type | Implemented |
| Data Integration | Custom | Import OCR results to models | Implemented |

---

## 3. Integration Architecture

### 3.1 System Architecture Overview

```mermaid
flowchart TB
    subgraph Frontend["Frontend Layer"]
        Blade["Blade Templates"]
        Alpine["Alpine.js"]
        Livewire["Livewire Components"]
    end

    subgraph Backend["Laravel 12 Backend"]
        Controllers["Controllers"]
        Services["Service Layer"]
        Neuron["Neuron AI Agents"]
    end

    subgraph AI["AI Integration Layer"]
        OllamaService["Ollama Service"]
        BedrockService["Bedrock Service"]
        HybridAI["Hybrid AI Router"]
    end

    subgraph MCP["MCP Layer"]
        MCPClient["MCP Client"]
        MemoryServer["Memory Server"]
        FilesystemServer["Filesystem Server"]
        FetchServer["Fetch Server"]
    end

    subgraph External["External Services"]
        Umapyoi["umapyoi.net API"]
        GameTora["GameTora Scraper"]
        OCR["Tesseract OCR"]
    end

    subgraph Storage["Data Layer"]
        MySQL[(MySQL Database)]
        Redis[(Redis Cache)]
        FileStorage["File Storage"]
    end

    subgraph Admin["Admin Panel"]
        DBMaintenance["Database Maintenance"]
        SystemHealth["System Health"]
        LogReader["Log Reader"]
    end

    Frontend --> Backend
    Backend --> AI
    Backend --> MCP
    Backend --> External
    Backend --> Storage
    Backend --> Admin
    AI --> Neuron
    MCP --> MCPClient
    MCPClient --> MemoryServer
    MCPClient --> FilesystemServer
    MCPClient --> FetchServer
```text

### 3.2 Component Dependencies

```mermaid
flowchart LR
    subgraph Core["Core Application"]
        Laravel["Laravel 12"]
        Livewire4["Livewire 4"]
        AlpineJS["Alpine.js"]
    end

    subgraph AILayer["AI Services"]
        NeuronAI["Neuron AI"]
        Ollama["Ollama"]
        Bedrock["AWS Bedrock"]
    end

    subgraph Integration["Integration Services"]
        ExternalAPI["External API Service"]
        TesseractService["Tesseract Service"]
        MCPService["MCP Service"]
    end

    Laravel --> NeuronAI
    Laravel --> ExternalAPI
    Laravel --> TesseractService
    Laravel --> MCPService
    Livewire4 --> Laravel
    NeuronAI --> Ollama
    NeuronAI --> Bedrock
```

---

## 4. Integration Strategy

### 4.1 Integration Phases

```mermaid
gantt
    title Integration Implementation Timeline
    dateFormat YYYY-MM-DD
    section Phase 1: Foundation
    Database Integration       :done, p1a, 2026-01-06, 5d
    Service Layer Setup        :done, p1b, after p1a, 5d
    section Phase 2: AI Integration
    Ollama Integration         :done, p2a, after p1b, 5d
    Bedrock Fallback           :done, p2b, after p2a, 5d
    Neuron Agents              :done, p2c, after p2b, 7d
    section Phase 3: External Services
    External API Integration   :done, p3a, after p2c, 5d
    OCR Pipeline               :done, p3b, after p3a, 5d
    section Phase 4: MCP Integration
    MCP Server Setup           :done, p4a, after p3b, 5d
    MCP Client Integration     :done, p4b, after p4a, 5d
    MCP Agent Orchestration    :done, p4c, after p4b, 7d
    section Phase 5: Admin Panel
    Admin Services             :done, p5a, after p4c, 5d
    Database Maintenance       :done, p5b, after p5a, 3d
    section Phase 6: Testing
    Integration Testing        :done, p6a, after p5b, 7d
    Performance Testing        :active, p6b, after p6a, 5d
```text

### 4.2 Integration Approach

| Phase | Focus | Deliverables |
| --- | --- | --- |
| Phase 1 | Foundation | Database, models, repositories |
| Phase 2 | AI Integration | Ollama, Bedrock, Neuron agents |
| Phase 3 | External Services | API clients, OCR pipeline, GameTora scraper |
| Phase 4 | MCP Integration | MCP servers, client integration, 9 orchestration agents |
| Phase 5 | Admin Panel | Database maintenance, system health, log reader |
| Phase 6 | Testing | Integration and performance tests (3,316+ tests) |

---

## 5. Integration Points

### 5.1 AI Provider Integration

```mermaid
sequenceDiagram
    participant User
    participant Controller
    participant HybridAI as Hybrid AI Service
    participant Ollama as Ollama Service
    participant Bedrock as Bedrock Service

    User->>Controller: Request AI recommendation
    Controller->>HybridAI: getRecommendation(context)
    HybridAI->>Ollama: tryLocalModel()
    alt Ollama Available
        Ollama-->>HybridAI: Response
    else Ollama Unavailable
        HybridAI->>Bedrock: fallbackToCloud()
        Bedrock-->>HybridAI: Response
    end
    HybridAI-->>Controller: AI Response
    Controller-->>User: Recommendation
```

### 5.2 Neuron AI Agent Integration

```mermaid
flowchart TD
    subgraph Agents["Neuron AI Agents"]
        TrainingAgent["Training Agent"]
        RaceAgent["Race Strategy Agent"]
        SkillAgent["Skill Advisor Agent"]
        CareerAgent["Career Advisor Agent"]
    end

    subgraph Tools["Agent Tools"]
        CharacterTool["Character Data Tool"]
        TrainingTool["Training Prediction Tool"]
        RaceTool["Race Analysis Tool"]
        SkillTool["Skill Recommendation Tool"]
    end

    subgraph Providers["AI Providers"]
        Ollama["Ollama (Local)"]
        Bedrock["AWS Bedrock (Cloud)"]
    end

    TrainingAgent --> CharacterTool
    TrainingAgent --> TrainingTool
    RaceAgent --> RaceTool
    SkillAgent --> SkillTool
    CareerAgent --> CharacterTool

    Agents --> Providers
```text

### 5.3 MCP Server Integration

```mermaid
sequenceDiagram
    participant Agent as Neuron Agent
    participant MCPClient as MCP Client
    participant Memory as Memory Server
    participant Filesystem as Filesystem Server
    participant Fetch as Fetch Server

    Agent->>MCPClient: Execute tool
    MCPClient->>Memory: Store context
    Memory-->>MCPClient: Stored
    MCPClient->>Filesystem: Read config
    Filesystem-->>MCPClient: Config data
    MCPClient->>Fetch: External request
    Fetch-->>MCPClient: Response
    MCPClient-->>Agent: Tool result
```

### 5.4 External API Integration

```mermaid
flowchart TD
    subgraph ExternalAPI["External API Service"]
        Client["API Client"]
        Cache["Response Cache"]
        Fallback["Fallback Handler"]
        CircuitBreaker["Circuit Breaker"]
    end

    subgraph APIs["External APIs"]
        Umapyoi["umapyoi.net"]
        GameTora["GameTora (scraping)"]
    end

    subgraph Data["Data Processing"]
        Parser["Response Parser"]
        Validator["Data Validator"]
        Transformer["Data Transformer"]
    end

    Client --> CircuitBreaker
    CircuitBreaker --> APIs
    APIs --> Cache
    Cache --> Parser
    Parser --> Validator
    Validator --> Transformer
    CircuitBreaker --> Fallback
```text

### 5.5 OCR Pipeline Integration

```mermaid
flowchart LR
    subgraph Upload["Upload Phase"]
        Image["Screenshot Upload"]
        Validation["File Validation"]
    end

    subgraph Processing["Processing Phase"]
        Preprocessing["GD Preprocessing"]
        OCR["Tesseract OCR"]
        Extraction["Text Extraction"]
    end

    subgraph Parsing["Parsing Phase"]
        Parser["Data Parser"]
        Validator["Validation"]
        Mapper["Field Mapper"]
    end

    subgraph Output["Output Phase"]
        Preview["User Preview"]
        Confirmation["Confirmation"]
        Import["Data Import"]
    end

    Upload --> Processing
    Processing --> Parsing
    Parsing --> Output
```

---

## 6. Data Flow Integration

### 6.1 AI Recommendation Flow

```mermaid
sequenceDiagram
    participant User
    participant UI as Blade/Livewire
    participant Controller
    participant Agent as Neuron Agent
    participant AI as AI Service
    participant DB as Database

    User->>UI: Request training advice
    UI->>Controller: POST /ai/training-advice
    Controller->>DB: Load character context
    DB-->>Controller: Character data
    Controller->>Agent: getTrainingAdvice(context)
    Agent->>AI: Generate recommendation
    AI-->>Agent: AI response
    Agent-->>Controller: Structured advice
    Controller-->>UI: JSON response
    UI-->>User: Display recommendation
```text

### 6.2 External Data Sync Flow

```mermaid
sequenceDiagram
    participant Scheduler as Laravel Scheduler
    participant Service as External API Service
    participant API as External API
    participant Cache as Redis Cache
    participant DB as Database

    Scheduler->>Service: Trigger sync job
    Service->>Cache: Check cache validity
    alt Cache Valid
        Cache-->>Service: Cached data
    else Cache Invalid
        Service->>API: Fetch fresh data
        API-->>Service: API response
        Service->>Cache: Update cache
        Service->>DB: Update records
    end
    Service-->>Scheduler: Sync complete
```

### 6.3 OCR Import Flow

```mermaid
sequenceDiagram
    participant User
    participant Controller
    participant OCR as OCR Service
    participant Parser as Data Parser
    participant Validator
    participant DB as Database

    User->>Controller: Upload screenshot
    Controller->>OCR: Process image
    OCR->>OCR: Preprocess (GD)
    OCR->>OCR: Extract text (Tesseract)
    OCR-->>Controller: Raw text
    Controller->>Parser: Parse extracted text
    Parser-->>Controller: Structured data
    Controller->>Validator: Validate data
    Validator-->>Controller: Validation result
    alt Valid
        Controller->>DB: Save data
        Controller-->>User: Success + preview
    else Invalid
        Controller-->>User: Errors + manual correction
    end
```text

---

## 7. Integration Test Environment

### 7.1 Environment Configuration

| Environment | Database | Cache | AI Provider | Purpose |
| --- | --- | --- | --- | --- |
| Local Dev | SQLite | Array | Ollama Mock | Developer testing |
| CI/CD | MySQL 8.0 | Redis | Mocked | Automated testing |
| Staging | MySQL 8.0 | Redis | Ollama | UAT testing |
| Production | MySQL 8.0 | Redis | Ollama + Bedrock | Live system |

### 7.2 Test Architecture

```mermaid
flowchart TD
    subgraph Tests["Test Suites"]
        Unit["Unit Tests"]
        Feature["Feature Tests"]
        Integration["Integration Tests"]
        E2E["E2E Tests"]
    end

    subgraph Targets["Test Targets"]
        AI["AI Services"]
        MCP["MCP Integration"]
        API["External APIs"]
        OCR["OCR Pipeline"]
    end

    subgraph Tools["Testing Tools"]
        Pest["Pest PHP"]
        Playwright["Playwright"]
        Mocks["Service Mocks"]
    end

    Tests --> Targets
    Tests --> Tools
```

### 7.3 Mock Strategy

| Service | Mock Approach | Test Data |
| --- | --- | --- |
| Ollama | Response fixtures | Predefined recommendations |
| Bedrock | AWS SDK mock | Sample completions |
| External APIs | HTTP mock | Golden file responses |
| OCR | Image fixtures | Known text outputs |
| MCP Servers | In-memory mock | Test context data |

---

## 8. Integration Scenarios

### 8.1 AI Fallback Scenario

```mermaid
stateDiagram-v2
    [*] --> OllamaCheck: Request AI
    OllamaCheck --> OllamaCall: Available
    OllamaCheck --> BedrockCall: Unavailable
    OllamaCall --> Success: Response OK
    OllamaCall --> BedrockCall: Error/Timeout
    BedrockCall --> Success: Response OK
    BedrockCall --> CachedResponse: Error
    CachedResponse --> Success: Cache hit
    CachedResponse --> Fallback: Cache miss
    Success --> [*]
    Fallback --> [*]: Graceful degradation
```text

### 8.2 MCP Tool Execution Scenario

```mermaid
stateDiagram-v2
    [*] --> AgentRequest: Agent needs tool
    AgentRequest --> MCPLookup: Find MCP server
    MCPLookup --> ServerFound: Server available
    MCPLookup --> ServerNotFound: Server missing
    ServerFound --> ToolExecution: Execute tool
    ToolExecution --> ToolSuccess: Success
    ToolExecution --> ToolError: Error
    ToolSuccess --> ResultReturn: Return result
    ToolError --> RetryLogic: Retry
    RetryLogic --> ToolExecution: Retry attempt
    RetryLogic --> FallbackLogic: Max retries
    ServerNotFound --> FallbackLogic: Use fallback
    FallbackLogic --> ResultReturn: Fallback result
    ResultReturn --> [*]
```

### 8.3 External API Resilience Scenario

```mermaid
flowchart TD
    Request["API Request"]
    CircuitBreaker{"Circuit Breaker State"}

    Request --> CircuitBreaker
    CircuitBreaker -->|Closed| APICall["Make API Call"]
    CircuitBreaker -->|Open| CacheCheck["Check Cache"]
    CircuitBreaker -->|Half-Open| ProbeCall["Probe Request"]

    APICall -->|Success| UpdateCache["Update Cache"]
    APICall -->|Failure| IncrementFailure["Increment Failures"]

    IncrementFailure -->|Threshold| OpenCircuit["Open Circuit"]
    IncrementFailure -->|Below| ReturnError["Return Error"]

    CacheCheck -->|Hit| ReturnCached["Return Cached"]
    CacheCheck -->|Miss| ReturnFallback["Return Fallback"]

    ProbeCall -->|Success| CloseCircuit["Close Circuit"]
    ProbeCall -->|Failure| KeepOpen["Keep Open"]

    UpdateCache --> ReturnResponse["Return Response"]
```text

---

## 9. Integration Management

### 9.1 Dependency Management

```mermaid
flowchart LR
    subgraph PHP["PHP Dependencies"]
        Composer["Composer"]
        Laravel["Laravel 12"]
        NeuronPkg["Neuron AI Package"]
        AWSSDk["AWS SDK"]
    end

    subgraph JS["JS Dependencies"]
        NPM["NPM"]
        Alpine["Alpine.js"]
        Tailwind["TailwindCSS v4"]
    end

    subgraph External["External Dependencies"]
        Ollama["Ollama Server"]
        Tesseract["Tesseract OCR"]
        Redis["Redis Server"]
    end

    Composer --> Laravel
    Composer --> NeuronPkg
    Composer --> AWSSDk
    NPM --> Alpine
    NPM --> Tailwind
```

### 9.2 Configuration Management

| Configuration | Location | Purpose |
| --- | --- | --- |
| AI Settings | `config/ai.php` | Provider selection, model config |
| AI Agents | `config/ai_agents.php` | Agent-specific configurations |
| Advisory Prompts | `config/advisory_prompts.php` | AI prompt templates |
| Neuron Config | `config/neuron.php` | Agent definitions, tools |
| MCP Config | `config/mcp.php` | Server definitions, connections |
| MCP Tools | `config/mcp_tools.php` | Tool controls and settings |
| MCP Agents | `config/mcp-agents.php` | Agent-specific MCP settings |
| External APIs | `config/external-apis.php` | API endpoints, credentials |
| API Performance | `config/api-performance.php` | Performance thresholds |
| APM Config | `config/apm.php` | Application performance monitoring |
| Cache Mgmt | `config/cache-management.php` | Cache strategy and TTLs |
| Query Optimization | `config/query-optimization.php` | Query tuning settings |
| AWS Config | `config/aws.php` | AWS/Bedrock credentials and regions |

### 9.3 Monitoring and Observability

```mermaid
flowchart TD
    subgraph Monitoring["Monitoring Layer"]
        APM["APM Dashboard"]
        Logs["Log Aggregation"]
        Metrics["Metrics Collection"]
    end

    subgraph Services["Monitored Services"]
        AIService["AI Service"]
        MCPService["MCP Service"]
        ExternalService["External API Service"]
        TesseractService["Tesseract Service"]
    end

    subgraph Alerts["Alert System"]
        Latency["Latency Alerts"]
        Errors["Error Alerts"]
        Availability["Availability Alerts"]
    end

    Services --> Monitoring
    Monitoring --> Alerts
```text

---

## 10. Sign-off Criteria

### 10.1 Integration Checklist

| Category | Criterion | Status |
| --- | --- | --- |
| AI Integration | Ollama service operational | ✅ Complete |
| AI Integration | Bedrock fallback functional | ✅ Complete |
| AI Integration | Neuron agents responding | ✅ Complete |
| AI Integration | AI Dashboard service | ✅ Complete |
| MCP Integration | Memory server connected | ✅ Complete |
| MCP Integration | Filesystem server connected | ✅ Complete |
| MCP Integration | Fetch server connected | ✅ Complete |
| MCP Integration | 9 orchestration agents deployed | ✅ Complete |
| MCP Integration | 42 MCP tool services registered | ✅ Complete |
| External APIs | umapyoi.net integration | ✅ Complete |
| External APIs | GameTora scraper integration | ✅ Complete |
| External APIs | Circuit breaker functional | ✅ Complete |
| OCR Pipeline | Image upload working | ✅ Complete |
| OCR Pipeline | Text extraction accurate | ✅ Complete |
| OCR Pipeline | Data parsing validated | ✅ Complete |
| Admin Panel | Database maintenance service | ✅ Complete |
| Admin Panel | System health monitoring | ✅ Complete |
| Testing | Unit tests passing (3,316+ tests) | ✅ Complete |
| Testing | Integration tests passing | ✅ Complete |
| Testing | E2E tests passing | 🔄 In Progress |

### 10.2 Acceptance Criteria

```mermaid
flowchart TD
    subgraph Criteria["Sign-off Criteria"]
        C1["✅ All AI providers operational with fallback"]
        C2["✅ MCP servers connected and 42 tools functional"]
        C3["✅ External APIs integrated with caching"]
        C4["✅ OCR pipeline processing screenshots"]
        C5["✅ Integration tests >80% coverage"]
        C6["🔄 Performance benchmarks met"]
    end

    C1 --> Complete
    C2 --> Complete
    C3 --> Complete
    C4 --> Complete
    C5 --> Complete
    C6 --> InProgress["In Progress"]

    Complete["Integration Complete"]
    InProgress --> Complete
```

---

## Document Control

| Version | Date | Author | Changes |
| --- | --- | --- | --- |
| 2.4.0 | 2026-02-22 | Development Team | Added 9 MCP agents, Admin Panel integration, GameTora scraper, 42 MCP tools, updated sign-off criteria, 3,316+ tests |
| 2.3.0 | 2026-02-21 | Development Team | Updated Livewire 3→4, version alignment, codebase v2.3.0 sync |
| 2.1.0 | 2026-01-23 | Development Team | Updated to reflect current AI, MCP, and external integrations |
| 2.0.0 | 2026-01-14 | Development Team | Prior revision with initial integration plan |

---

## Related Documents

- [PRD-006: AI Advisory](./prds/PRD-006_AI_Advisory.md)
- [PRD-007: External Integration](./prds/PRD-007_External_Integration.md)
- [SPEC-006: AI Advisory Technical](./specs/SPEC-006_AI_Advisory_Technical.md)
- [SPEC-007: External Integration Technical](./specs/SPEC-007_External_Integration_Technical.md)
- [FLOW-006: AI Advisory System](./flows/FLOW-006_AI_Advisory_System.md)
- [FLOW-007: External Integration System](./flows/FLOW-007_External_Integration_System.md)
- [TECH-FLOW-006: AI Advisory Flow](./tech-flow/TECH-FLOW-006_AI_Advisory_Flow.md)
- [TECH-FLOW-007: External Integration Flow](./tech-flow/TECH-FLOW-007_External_Integration_Flow.md)
- [SEQ-006: AI Advice Generation](./sequences/SEQ-006_AI_Advice_Generation.md)
- [SEQ-007: External Data Sync](./sequences/SEQ-007_External_Data_Sync.md)

---

*This plan reflects current integration architecture and implementation status.*
