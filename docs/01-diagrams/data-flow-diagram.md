# Umamusume Career Planner - Data Flow Diagram (DFD)

**Document Version**: 2.4.0
**Date**: February 22, 2026
**Project**: UmamusumeCareerPlanner
**Author**: Development Team
**Status**: Current - Aligned with codebase v2.4.0 (571 routes, 3,316+ tests, 11,563+ assertions)

---

## Table of Contents

1. [Overview](#1-overview)
2. [Context Diagram (Level 0)](#2-context-diagram-level-0)
3. [Level 1 DFD - Major System Processes](#3-level-1-dfd---major-system-processes)
4. [Level 2 DFD - Training Optimization Engine](#4-level-2-dfd---training-optimization-engine)
5. [Level 2 DFD - AI Advisory System](#5-level-2-dfd---ai-advisory-system)
6. [Level 2 DFD - External Integration](#6-level-2-dfd---external-integration)
7. [Level 2 DFD - Data Management](#7-level-2-dfd---data-management)
8. [Level 2 DFD - Performance Monitoring (NEW)](#8-level-2-dfd---performance-monitoring-new)
9. [Data Store Specifications](#9-data-store-specifications)
10. [Data Flow Summary](#10-data-flow-summary)

---

## 1. Overview

### 1.1 Purpose

This document presents the Data Flow Diagrams for the Umamusume Pretty Derby Career Planner application, showing how data flows through the system from external sources, user inputs, and internal processes to provide optimization recommendations and analytics.

### 1.2 System Architecture

The system is built with:

- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Livewire 4, Alpine.js 3, TailwindCSS v4
- **AI Integration**: Neuron AI v2.11 agents, Ollama (local), AWS Bedrock (cloud)
- **MCP Integration**: Model Context Protocol servers (30+ services)
- **Admin Panel**: Database, Log, Queue, SystemSettings, User controllers
- **External APIs**: umapyoi.net (primary), GameTora (scraping)
- **OCR Processing**: Tesseract with GD preprocessing

### 1.3 DFD Notation

```mermaid
flowchart LR
    subgraph Legend[DFD Notation]
        Process[Process<br/>Bubble]
        ExternalEntity[External Entity<br/>Rectangle]
        DataStore[(Data Store<br/>Open Rectangle)]
        DataFlow1[Data Flow 1] -->|Arrow| DataFlow2[Data Flow 2]
    end

    style Process fill:#e3f2fd
    style ExternalEntity fill:#fff3e0
    style DataStore fill:#e8f5e9
```

- **Symbol**: Circle/Bubble; **Meaning**: Process that transforms data
- **Symbol**: Rectangle; **Meaning**: External entity (source/destination)
- **Symbol**: Open Rectangle; **Meaning**: Data store (database, cache, file)
- **Symbol**: Arrow; **Meaning**: Data flow direction

---

## 2. Context Diagram (Level 0)

### 2.1 System Boundary

```mermaid
flowchart TB
    subgraph External[External Entities]
        Player[Player<br/>Manual Input, Screenshots]
        Admin[Admin User<br/>System Management]
        UmapyoiAPI[umapyoi.net API<br/>Game Data]
        GameToraAPI[GameTora<br/>Scraping Source]
        Community[Community Sources<br/>Meta Data, Tiers]
        OllamaServer[Ollama Server<br/>Local AI]
        BedrockAPI[AWS Bedrock API<br/>Cloud AI]
    end

    subgraph System[Umamusume Career Planner System]
        Core[Career Planning<br/>& Optimization<br/>System]
    end

    subgraph Outputs[System Outputs]
        Recommendations[Training Recommendations<br/>Race Strategies<br/>Skill Builds]
        Analytics[Performance Analytics<br/>Career Reports<br/>Stat Progression]
        Exports[Data Exports<br/>Backups<br/>Shared Builds]
    end

    Player -->|Character data, Goals, Actions| Core
    Admin -->|System config, User mgmt| Core
    UmapyoiAPI -->|Characters, Skills, Cards| Core
    GameToraAPI -->|Scraped game data| Core
    Community -->|Meta rankings, Strategies| Core

    Core -->|AI queries| OllamaServer
    Core -->|Complex queries| BedrockAPI
    OllamaServer -->|Responses| Core
    BedrockAPI -->|Responses| Core

    Core -->|Recommendations| Recommendations
    Core -->|Reports| Analytics
    Core -->|Files| Exports

    Recommendations -->|Display| Player
    Analytics -->|Display| Player
    Exports -->|Download| Player
    Core -->|Logs, Metrics, Alerts| Admin

    style Core fill:#f3e5f5
    style External fill:#fff3e0
    style Outputs fill:#e8f5e9
```

### 2.2 External Entity Descriptions

- **Entity**: **Player**; **Type**: Human; **Description**: End user interacting with system; **Data Flow**: Input: Manual data, Screenshots, Goals<br/>Output: Recommendations, Analytics
- **Entity**: **Admin**; **Type**: Human; **Description**: System administrator managing application; **Data Flow**: Input: Config changes, User management<br/>Output: Logs, Metrics, Alerts
- **Entity**: **umapyoi.net API**; **Type**: System; **Description**: Primary external game data source; **Data Flow**: Input: Character catalog, Skills, Support cards
- **Entity**: **GameTora**; **Type**: System; **Description**: Secondary data source (scraping); **Data Flow**: Input: Scraped game data
- **Entity**: **Community Sources**; **Type**: System; **Description**: Meta rankings and strategies; **Data Flow**: Input: Tier lists, Meta data
- **Entity**: **Ollama Server**; **Type**: System; **Description**: Local AI inference engine; **Data Flow**: Input: AI queries<br/>Output: Recommendations
- **Entity**: **AWS Bedrock API**; **Type**: System; **Description**: Cloud AI fallback; **Data Flow**: Input: Complex queries<br/>Output: Recommendations

---

## 3. Level 1 DFD - Major System Processes

### 3.1 Top-Level Processes

```mermaid
flowchart TB
    subgraph External[External Entities]
        Player[Player]
        Admin[Admin User]
        ExternalAPIs[External APIs<br/>umapyoi.net<br/>GameTora]
        AIProviders[AI Providers<br/>Ollama/Bedrock]
    end

    subgraph Processes[System Processes]
        P1[1.0<br/>Data Collection<br/>& Integration]
        P2[2.0<br/>Character<br/>Management]
        P3[3.0<br/>Training<br/>Optimization]
        P4[4.0<br/>Race Strategy<br/>Analysis]
        P5[5.0<br/>Skill<br/>Management]
        P6[6.0<br/>Support Card<br/>Management]
        P7[7.0<br/>AI Advisory<br/>System]
        P8[8.0<br/>Data Management<br/>& Export]
        P9[9.0<br/>Admin Panel<br/>& Monitoring]
    end

    subgraph DataStores[Data Stores]
        D1[(D1: Characters)]
        D2[(D2: Careers)]
        D3[(D3: Skills)]
        D4[(D4: Support Cards)]
        D5[(D5: External Cache)]
        D6[(D6: AI Conversations)]
    end

    Player -->|Character input| P2
    Player -->|Training actions| P3
    Player -->|Race entries| P4
    Player -->|Skill selections| P5
    Player -->|Deck configurations| P6
    Player -->|AI queries| P7
    Player -->|Import/Export requests| P8
    Admin -->|System config| P9
    Admin -->|User management| P9

    ExternalAPIs -->|Game data| P1
    P1 -->|Validated data| D5
    D5 -->|Cached data| P2
    D5 -->|Cached data| P3
    D5 -->|Cached data| P5
    D5 -->|Cached data| P6

    P2 -->|Character records| D1
    D1 -->|Character state| P3
    D1 -->|Character state| P4

    P3 -->|Training sessions| D2
    D2 -->|Career history| P4

    P5 -->|Skill data| D3
    D3 -->|Available skills| P3

    P6 -->|Deck configs| D4
    D4 -->|Active decks| P3

    P3 -->|Optimization requests| P7
    P4 -->|Strategy requests| P7
    P5 -->|Recommendation requests| P7

    P7 -->|AI queries| AIProviders
    AIProviders -->|AI responses| P7
    P7 -->|Conversation logs| D6

    P2 -->|Export data| P8
    P3 -->|Export data| P8
    P4 -->|Export data| P8
    P5 -->|Export data| P8

    P8 -->|Import data| P2
    P8 -->|Files| Player

    P3 -->|Recommendations| Player
    P4 -->|Strategies| Player
    P5 -->|Skill builds| Player
    P7 -->|Advice| Player

    P9 -->|Logs, Alerts| Admin
    P9 -->|System metrics| Admin

    style Processes fill:#e3f2fd
    style DataStores fill:#e8f5e9
    style External fill:#fff3e0
```

### 3.2 Process Descriptions

- **Process**: **1.0**; **Name**: Data Collection & Integration; **Description**: Aggregates external game data with caching and circuit breaker; **Key Inputs**: External API responses; **Key Outputs**: Validated, cached game data
- **Process**: **2.0**; **Name**: Character Management; **Description**: Manages character lifecycle, stats, aptitudes; **Key Inputs**: User input, imported data; **Key Outputs**: Character records
- **Process**: **3.0**; **Name**: Training Optimization; **Description**: Predicts stat gains, calculates bonuses, provides recommendations; **Key Inputs**: Character state, support deck; **Key Outputs**: Training predictions, recommendations
- **Process**: **4.0**; **Name**: Race Strategy Analysis; **Description**: Analyzes race requirements, calculates readiness, recommends strategies; **Key Inputs**: Character stats, race data; **Key Outputs**: Race strategies, win probabilities
- **Process**: **5.0**; **Name**: Skill Management; **Description**: Manages skill catalog, tracks hints, optimizes SP budget; **Key Inputs**: Available skills, character SP; **Key Outputs**: Skill recommendations, acquisition tracking
- **Process**: **6.0**; **Name**: Support Card Management; **Description**: Manages card inventory, builds decks, calculates synergy; **Key Inputs**: Card collection, deck configs; **Key Outputs**: Optimized decks, synergy scores
- **Process**: **7.0**; **Name**: AI Advisory System; **Description**: Provides intelligent recommendations via hybrid AI; **Key Inputs**: User queries, context data; **Key Outputs**: AI-powered advice, confidence scores
- **Process**: **8.0**; **Name**: Data Management & Export; **Description**: Handles import, export, backup, migration; **Key Inputs**: User files, system data; **Key Outputs**: Exported files, imported records
- **Process**: **9.0**; **Name**: Admin Panel & Monitoring; **Description**: System administration, user management, log viewing, queue monitoring; **Key Inputs**: Admin requests, system metrics; **Key Outputs**: Dashboard views, alerts, system config

---

## 4. Level 2 DFD - Training Optimization Engine

### 4.1 Training Optimization Decomposition

```mermaid
flowchart TB
    subgraph Inputs[Inputs]
        CharacterState[Character State<br/>Stats, Energy, Mood]
        SupportDeck[Support Deck<br/>6 Cards]
        CareerContext[Career Context<br/>Turn, Stage, Goals]
    end

    subgraph Processes[Training Optimization Processes]
        P31[3.1<br/>Base Stat<br/>Calculator]
        P32[3.2<br/>Support Bonus<br/>Calculator]
        P33[3.3<br/>Risk<br/>Assessor]
        P34[3.4<br/>Skill Hint<br/>Predictor]
        P35[3.5<br/>Recommendation<br/>Ranker]
        P36[3.6<br/>AI Training<br/>Advisor]
    end

    subgraph DataStores[Data Stores]
        D2[(D2: Careers<br/>Training Sessions)]
        D4[(D4: Support Cards)]
        D7[(D7: Prediction<br/>Cache)]
    end

    subgraph Outputs[Outputs]
        Predictions[Training<br/>Predictions]
        Recommendations[Ranked<br/>Recommendations]
        AIAdvice[AI Training<br/>Advice]
    end

    CharacterState -->|Current stats| P31
    CharacterState -->|Energy/Mood| P33

    SupportDeck -->|Card bonuses| P32
    D4 -->|Card effects| P32

    CareerContext -->|Turn context| P31
    CareerContext -->|Goals| P35

    P31 -->|Base gains| P32
    P32 -->|Adjusted gains| P33
    P33 -->|Risk-adjusted gains| P34
    P34 -->|Hint probabilities| P35

    P35 -->|Ranked options| P36
    CharacterState -->|Context| P36
    CareerContext -->|Goals| P36

    P31 -->|Prediction data| D7
    D7 -->|Cached predictions| P35

    P33 -->|Risk scores| Predictions
    P34 -->|Hint chances| Predictions
    P35 -->|Rankings| Recommendations
    P36 -->|AI insights| AIAdvice

    P31 -->|Session logs| D2
    D2 -->|Historical data| P35

    style Processes fill:#e3f2fd
    style DataStores fill:#e8f5e9
    style Inputs fill:#fff3e0
    style Outputs fill:#c8e6c9
```

### 4.2 Training Process Details

- **Sub-Process**: **3.1 Base Stat Calculator**; **Description**: Calculates raw stat gains per facility; **Algorithm**: Growth rates × Facility multipliers; **Caching**: No
- **Sub-Process**: **3.2 Support Bonus Calculator**; **Description**: Applies support card bonuses and friendship multipliers; **Algorithm**: Bonus stacking with friendship thresholds; **Caching**: No
- **Sub-Process**: **3.3 Risk Assessor**; **Description**: Calculates failure probability based on energy/mood/conditions; **Algorithm**: Risk score = f(energy, mood, conditions); **Caching**: No
- **Sub-Process**: **3.4 Skill Hint Predictor**; **Description**: Determines probability of skill hints per facility; **Algorithm**: Hint chance based on support participation; **Caching**: No
- **Sub-Process**: **3.5 Recommendation Ranker**; **Description**: Ranks training options by expected value and alignment with goals; **Algorithm**: Multi-criteria scoring; **Caching**: 5 min TTL
- **Sub-Process**: **3.6 AI Training Advisor**; **Description**: Provides intelligent training recommendations via Neuron agents; **Algorithm**: Neuron AI + Ollama/Bedrock; **Caching**: Session-based

---

## 5. Level 2 DFD - AI Advisory System

### 5.1 AI Advisory Decomposition

```mermaid
flowchart TB
    subgraph Inputs[Inputs]
        UserQuery[User Query<br/>Text]
        Context[Context Data<br/>Character, Career]
    end

    subgraph Processes[AI Advisory Processes]
        P71[7.1<br/>Query<br/>Analyzer]
        P72[7.2<br/>Context<br/>Builder]
        P73[7.3<br/>Provider<br/>Router]
        P74[7.4<br/>Ollama<br/>Service]
        P75[7.5<br/>Bedrock<br/>Service]
        P76[7.6<br/>Response<br/>Synthesizer]
        P77[7.7<br/>Cost<br/>Tracker]
    end

    subgraph DataStores[Data Stores]
        D1[(D1: Characters)]
        D2[(D2: Careers)]
        D6[(D6: AI<br/>Conversations)]
        D8[(D8: AI Costs)]
    end

    subgraph External[External Services]
        Ollama[Ollama Server<br/>Local AI]
        Bedrock[AWS Bedrock<br/>Cloud AI]
    end

    subgraph Outputs[Outputs]
        Response[AI Response<br/>with Confidence]
        Metrics[Cost &<br/>Performance<br/>Metrics]
    end

    UserQuery -->|Raw query| P71
    P71 -->|Analyzed intent| P72

    Context -->|Character state| P72
    D1 -->|Character data| P72
    D2 -->|Career history| P72

    P72 -->|Enriched context| P73
    P73 -->|Simple queries| P74
    P73 -->|Complex queries| P75

    P74 -->|API request| Ollama
    Ollama -->|AI response| P74

    P75 -->|API request| Bedrock
    Bedrock -->|AI response| P75

    P74 -->|Local response| P76
    P75 -->|Cloud response| P76

    P76 -->|Formatted response| Response
    P76 -->|Conversation| D6

    P75 -->|Token usage| P77
    P77 -->|Cost data| D8
    P77 -->|Metrics| Metrics

    style Processes fill:#e3f2fd
    style DataStores fill:#e8f5e9
    style Inputs fill:#fff3e0
    style External fill:#fff9c4
    style Outputs fill:#c8e6c9
```

### 5.2 AI Advisory Process Details

- **Sub-Process**: **7.1 Query Analyzer**; **Description**: Analyzes user query intent and complexity; **Implementation**: Pattern matching, keyword extraction
- **Sub-Process**: **7.2 Context Builder**; **Description**: Enriches query with character and career context; **Implementation**: Data aggregation from multiple stores
- **Sub-Process**: **7.3 Provider Router**; **Description**: Routes query to appropriate AI provider based on complexity; **Implementation**: Ollama (simple) vs Bedrock (complex)
- **Sub-Process**: **7.4 Ollama Service**; **Description**: Processes queries using local Ollama models; **Implementation**: HTTP API client, timeout 30s
- **Sub-Process**: **7.5 Bedrock Service**; **Description**: Processes queries using AWS Bedrock Claude models; **Implementation**: AWS SDK, Claude Sonnet/Haiku
- **Sub-Process**: **7.6 Response Synthesizer**; **Description**: Formats and enriches AI responses with confidence scores; **Implementation**: JSON formatting, confidence calculation
- **Sub-Process**: **7.7 Cost Tracker**; **Description**: Tracks token usage and calculates costs for cloud AI; **Implementation**: Database persistence, budget monitoring

---

## 6. Level 2 DFD - External Integration

### 6.1 External Integration Decomposition

```mermaid
flowchart TB
    subgraph Triggers[Triggers]
        Scheduler[Laravel<br/>Scheduler]
        UserRequest[User Request<br/>Manual Sync]
        OCRUpload[Screenshot<br/>Upload]
    end

    subgraph Processes[External Integration Processes]
        P11[1.1<br/>Circuit<br/>Breaker]
        P12[1.2<br/>API Client<br/>Manager]
        P13[1.3<br/>Response<br/>Parser]
        P14[1.4<br/>Cache<br/>Manager]
        P15[1.5<br/>OCR<br/>Processor]
    end

    subgraph External[External Services]
        Umapyoi[umapyoi.net<br/>API]
        UmamusumeDB[UmamusumeDB.com<br/>API]
        Tesseract[Tesseract OCR<br/>Engine]
    end

    subgraph DataStores[Data Stores]
        D5[(D5: External<br/>API Cache)]
        D9[(D9: OCR<br/>Extractions)]
    end

    subgraph Outputs[Outputs]
        ValidatedData[Validated<br/>Game Data]
        ExtractedData[Extracted<br/>Character Data]
    end

    Scheduler -->|Sync trigger| P11
    UserRequest -->|Manual sync| P11

    P11 -->|Check status| P12
    P11 -->|Circuit open| D5

    P12 -->|Primary request| Umapyoi
    P12 -->|Fallback request| UmamusumeDB

    Umapyoi -->|Response| P13
    UmamusumeDB -->|Response| P13

    P13 -->|Parsed data| P14
    P14 -->|Cached data| D5
    P14 -->|Fresh data| ValidatedData

    OCRUpload -->|Image| P15
    P15 -->|OCR request| Tesseract
    Tesseract -->|Text| P15
    P15 -->|Parsed data| D9
    P15 -->|Validated data| ExtractedData

    D5 -->|Cached response| ValidatedData

    style Processes fill:#e3f2fd
    style DataStores fill:#e8f5e9
    style Triggers fill:#fff3e0
    style External fill:#fff9c4
    style Outputs fill:#c8e6c9
```

### 6.2 Integration Process Details

- **Sub-Process**: **1.1 Circuit Breaker**; **Description**: Monitors API health and opens circuit on failure threshold; **Resilience Pattern**: Open after 5 failures in 120s window
- **Sub-Process**: **1.2 API Client Manager**; **Description**: Manages connections to external APIs with fallback logic; **Resilience Pattern**: Primary → Fallback → Cached
- **Sub-Process**: **1.3 Response Parser**; **Description**: Parses and validates API responses against schema; **Resilience Pattern**: JSON schema validation
- **Sub-Process**: **1.4 Cache Manager**; **Description**: Stores and retrieves cached responses with TTL management; **Resilience Pattern**: 24-hour TTL, Redis-backed
- **Sub-Process**: **1.5 OCR Processor**; **Description**: Processes screenshots with GD preprocessing and Tesseract OCR; **Resilience Pattern**: GD grayscale → threshold → OCR

---

## 7. Level 2 DFD - Data Management

### 7.1 Data Management Decomposition

```mermaid
flowchart TB
    subgraph Inputs[Inputs]
        ImportFile[Import File<br/>JSON/CSV/XLSX]
        ExportRequest[Export Request<br/>Format Selection]
        BackupRequest[Backup Request]
    end

    subgraph Processes[Data Management Processes]
        P81[8.1<br/>Format<br/>Detector]
        P82[8.2<br/>Data<br/>Validator]
        P83[8.3<br/>Conflict<br/>Resolver]
        P84[8.4<br/>Data<br/>Importer]
        P85[8.5<br/>Data<br/>Exporter]
        P86[8.6<br/>Backup<br/>Manager]
    end

    subgraph DataStores[Data Stores]
        D1[(D1: Characters)]
        D2[(D2: Careers)]
        D3[(D3: Skills)]
        D10[(D10: Migration<br/>History)]
        D11[(D11: Backups)]
    end

    subgraph Outputs[Outputs]
        ImportResult[Import Result<br/>Success/Errors]
        ExportFile[Export File<br/>JSON/CSV/XLSX]
        BackupArchive[Backup Archive<br/>ZIP]
    end

    ImportFile -->|Raw data| P81
    P81 -->|Detected format| P82
    P82 -->|Validation result| P83
    P83 -->|Conflict strategy| P84

    P84 -->|Character data| D1
    P84 -->|Career data| D2
    P84 -->|Skill data| D3
    P84 -->|Migration log| D10
    P84 -->|Result| ImportResult

    ExportRequest -->|Format spec| P85
    D1 -->|Character records| P85
    D2 -->|Career records| P85
    D3 -->|Skill records| P85
    P85 -->|Generated file| ExportFile

    BackupRequest -->|Backup trigger| P86
    D1 -->|All characters| P86
    D2 -->|All careers| P86
    D3 -->|All skills| P86
    P86 -->|Backup record| D11
    P86 -->|Archive| BackupArchive

    style Processes fill:#e3f2fd
    style DataStores fill:#e8f5e9
    style Inputs fill:#fff3e0
    style Outputs fill:#c8e6c9
```

### 7.2 Data Management Process Details

- **Sub-Process**: **8.1 Format Detector**; **Description**: Detects import file format and schema version; **Supported Formats**: JSON v1.0, CSV, XLSX, Legacy formats
- **Sub-Process**: **8.2 Data Validator**; **Description**: Validates imported data against business rules; **Supported Formats**: Stat ranges, enum values, relationships
- **Sub-Process**: **8.3 Conflict Resolver**; **Description**: Resolves duplicate/conflict records using user strategy; **Supported Formats**: Skip, Overwrite, Merge, Rename
- **Sub-Process**: **8.4 Data Importer**; **Description**: Imports validated records into database with transaction management; **Supported Formats**: Batch processing, rollback on error
- **Sub-Process**: **8.5 Data Exporter**; **Description**: Exports data to requested format with schema versioning; **Supported Formats**: JSON, CSV, XLSX with metadata
- **Sub-Process**: **8.6 Backup Manager**; **Description**: Creates full backup archives with restore capability; **Supported Formats**: ZIP with manifest, incremental support

---

## 8. Level 2 DFD - Performance Monitoring (NEW)

### 8.1 Overview

The Performance Monitoring subsystem collects, aggregates, and analyzes application performance metrics to enable proactive optimization and alerting.

### 8.2 APM Data Flow Diagram

```mermaid
flowchart TB
    subgraph Collection[Data Collection Layer]
        Middleware[Performance Middleware]
        QueryListener[Query Listener]
        CacheListener[Cache Listener]
        AIListener[AI Latency Listener]
    end

    subgraph Processing[Processing Layer]
        P1[8.1 Metrics Collector<br/>Aggregate raw metrics]
        P2[8.2 Query Analyzer<br/>Analyze query performance]
        P3[8.3 Cache Analyzer<br/>Analyze cache patterns]
        P4[8.4 Alert Engine<br/>Check thresholds]
        P5[8.5 Aggregator<br/>Create time-based aggregates]
    end

    subgraph Storage[Storage Layer]
        D1[(Redis<br/>Hot Metrics)]
        D2[(MySQL<br/>Historical)]
        D3[(Aggregates<br/>Tiered Retention)]
    end

    subgraph Output[Output Layer]
        Dashboard[Dashboard API]
        Alerts[Alert Notifications]
        Reports[Optimization Reports]
    end

    Middleware --> P1
    QueryListener --> P2
    CacheListener --> P3
    AIListener --> P1

    P1 --> D1
    P2 --> D1
    P3 --> D1

    D1 --> P4
    P4 -->|Threshold Exceeded| Alerts

    D1 --> P5
    P5 --> D2
    P5 --> D3

    D2 --> Dashboard
    D3 --> Reports
    P2 --> Reports
    P3 --> Reports

    style Collection fill:#e3f2fd
    style Processing fill:#f3e5f5
    style Storage fill:#e8f5e9
    style Output fill:#fff3e0
```

### 8.3 Process Specifications

- **Process**: **8.1 Metrics Collector**; **Description**: Collects request-level metrics from middleware; **Key Inputs**: HTTP request data, timing info; **Key Outputs**: Raw metrics records
- **Process**: **8.2 Query Analyzer**; **Description**: Analyzes database queries for optimization; **Key Inputs**: SQL queries, EXPLAIN plans; **Key Outputs**: Query scores, suggestions
- **Process**: **8.3 Cache Analyzer**; **Description**: Analyzes cache hit/miss patterns; **Key Inputs**: Cache operations; **Key Outputs**: Hit rates, TTL suggestions
- **Process**: **8.4 Alert Engine**; **Description**: Compares metrics against thresholds; **Key Inputs**: Aggregated metrics, thresholds; **Key Outputs**: Alert notifications
- **Process**: **8.5 Aggregator**; **Description**: Creates time-based metric aggregates; **Key Inputs**: Raw metrics; **Key Outputs**: Minute/hour/day aggregates

### 8.4 Data Flows

- **Flow**: **DF8.1**; **Source → Destination**: Middleware → Metrics Collector; **Data Content**: Request ID, endpoint, start time
- **Flow**: **DF8.2**; **Source → Destination**: Query Listener → Query Analyzer; **Data Content**: SQL, duration, bindings
- **Flow**: **DF8.3**; **Source → Destination**: Cache Listener → Cache Analyzer; **Data Content**: Key, operation, hit/miss, latency
- **Flow**: **DF8.4**; **Source → Destination**: Metrics Collector → Redis; **Data Content**: JSON metrics record
- **Flow**: **DF8.5**; **Source → Destination**: Query Analyzer → Redis; **Data Content**: Query performance score
- **Flow**: **DF8.6**; **Source → Destination**: Redis → Alert Engine; **Data Content**: Aggregated metrics
- **Flow**: **DF8.7**; **Source → Destination**: Alert Engine → Notifications; **Data Content**: Alert payload (Slack, Email)
- **Flow**: **DF8.8**; **Source → Destination**: Aggregator → MySQL; **Data Content**: Minute/hour/day aggregates

### 8.5 Performance Monitoring Services

- **Service**: `ApmService`; **Function**: Core APM coordination and metrics storage
- **Service**: `ApiPerformanceMonitoringService`; **Function**: API endpoint performance tracking
- **Service**: `QueryOptimizationService`; **Function**: Database query analysis
- **Service**: `PerformanceAlertingService`; **Function**: Alert generation and delivery
- **Service**: `RedisCacheOptimizationService`; **Function**: Cache analytics and optimization
- **Service**: `ApiResponseCachingService`; **Function**: Response caching strategies
- **Service**: `PerformanceRegressionService`; **Function**: Regression detection
- **Service**: `HistoricalTrackingService`; **Function**: Long-term metrics storage

---

## 9. Data Store Specifications

### 9.1 Primary Data Stores

```mermaid
erDiagram
    D1_Characters ||--o{ D2_Careers : "has"
    D2_Careers ||--o{ TrainingSessions : "contains"
    D2_Careers ||--o{ RaceResults : "logs"
    D2_Careers }o--|| D4_SupportDecks : "uses"
    D1_Characters }o--o{ D3_Skills : "acquires"
    D4_SupportDecks ||--|{ SupportCards : "contains"

    D1_Characters {
        bigint id PK
        string name
        json current_stats
        enum scenario_type
        int energy_level
        enum mood_status
    }

    D2_Careers {
        bigint id PK
        uuid uuid
        bigint character_id FK
        enum status
        int current_turn
        json goals
    }

    D3_Skills {
        bigint id PK
        string name
        int base_sp_cost
        enum rarity
        json effects
    }

    D4_SupportDecks {
        bigint id PK
        bigint character_id FK
        json card_assignments
        float synergy_score
    }
```

### 8.2 Data Store Catalog

- **Store ID**: **D1**; **Name**: Characters; **Type**: MySQL; **Persistence**: Permanent; **Description**: Character records with stats and aptitudes
- **Store ID**: **D2**; **Name**: Careers; **Type**: MySQL; **Persistence**: Permanent; **Description**: Career run tracking and progression
- **Store ID**: **D3**; **Name**: Skills; **Type**: MySQL; **Persistence**: Permanent; **Description**: Skill catalog and acquisition history
- **Store ID**: **D4**; **Name**: Support Cards; **Type**: MySQL; **Persistence**: Permanent; **Description**: Support card inventory and deck configurations
- **Store ID**: **D5**; **Name**: External API Cache; **Type**: Redis; **Persistence**: 24h TTL; **Description**: Cached responses from external APIs
- **Store ID**: **D6**; **Name**: AI Conversations; **Type**: MySQL; **Persistence**: 90 days; **Description**: AI conversation history and context
- **Store ID**: **D7**; **Name**: Prediction Cache; **Type**: Redis; **Persistence**: 5 min TTL; **Description**: Training prediction results
- **Store ID**: **D8**; **Name**: AI Costs; **Type**: MySQL; **Persistence**: Permanent; **Description**: Token usage and cost tracking
- **Store ID**: **D9**; **Name**: OCR Extractions; **Type**: MySQL; **Persistence**: Permanent; **Description**: OCR processing results
- **Store ID**: **D10**; **Name**: Migration History; **Type**: MySQL; **Persistence**: Permanent; **Description**: Data migration audit trail
- **Store ID**: **D11**; **Name**: Backups; **Type**: File System; **Persistence**: User-managed; **Description**: Backup archives

### 8.3 Cache Strategy

```mermaid
flowchart LR
    subgraph Caching[Cache Layers]
        L1[L1: Application<br/>Array Cache]
        L2[L2: Redis<br/>Shared Cache]
        L3[L3: Database<br/>Persistent Storage]
    end

    Request[Request] --> Check1{In L1?}
    Check1 -->|Yes| Return1[Return from L1]
    Check1 -->|No| Check2{In L2?}
    Check2 -->|Yes| Store1[Store in L1]
    Store1 --> Return2[Return from L2]
    Check2 -->|No| Query[Query L3]
    Query --> Store2[Store in L2]
    Store2 --> Store1

    style Caching fill:#e8f5e9
```

- **Cache Level**: **L1 Application**; **Technology**: PHP Array; **Use Case**: Request-scoped data; **TTL**: Request lifetime
- **Cache Level**: **L2 Shared**; **Technology**: Redis; **Use Case**: Cross-request data, predictions; **TTL**: 5 min - 24 hours
- **Cache Level**: **L3 Database**; **Technology**: MySQL; **Use Case**: Persistent data; **TTL**: Permanent

---

## 10. Data Flow Summary

### 9.1 Critical Data Flows

```mermaid
flowchart TD
    subgraph CriticalFlows[Critical Data Flows]
        F1[User Input → Character State]
        F2[Character State → Training Predictions]
        F3[Training Predictions → AI Recommendations]
        F4[AI Recommendations → User Display]
        F5[External APIs → Cached Game Data]
        F6[Cached Data → System Processes]
    end

    F1 -->|Real-time| F2
    F2 -->|Cached 5min| F3
    F3 -->|Session-based| F4
    F5 -->|24h TTL| F6
    F6 -->|On-demand| F2

    style CriticalFlows fill:#e3f2fd
```

### 9.2 Data Flow Volumes

- **Flow**: User input → Character update; **Volume**: 1 record; **Frequency**: Per action; **Latency Target**: < 200ms
- **Flow**: Training predictions request; **Volume**: 6-8 options; **Frequency**: Per turn; **Latency Target**: < 1.2s
- **Flow**: AI advisory request; **Volume**: 1 query; **Frequency**: As needed; **Latency Target**: < 2.5s
- **Flow**: External API sync; **Volume**: 100-500 records; **Frequency**: Daily; **Latency Target**: < 30s
- **Flow**: OCR processing; **Volume**: 1 image; **Frequency**: Manual; **Latency Target**: < 10s
- **Flow**: Data export; **Volume**: 1-100 careers; **Frequency**: Manual; **Latency Target**: < 60s

### 9.3 Data Transformation Points

- **Transformation**: **External API Response**; **Input Format**: JSON (external schema); **Output Format**: JSON (internal schema); **Process**: Schema mapping, validation
- **Transformation**: **OCR Text Extraction**; **Input Format**: Image (PNG/JPG); **Output Format**: Structured text; **Process**: GD preprocessing → Tesseract → Parsing
- **Transformation**: **Training Calculation**; **Input Format**: Character state + Deck; **Output Format**: Prediction array; **Process**: Multi-step calculation pipeline
- **Transformation**: **AI Query**; **Input Format**: Natural language; **Output Format**: Structured response; **Process**: Context enrichment → LLM → Formatting
- **Transformation**: **Export**; **Input Format**: Database records; **Output Format**: JSON/CSV/XLSX; **Process**: Serialization with schema versioning

### 9.4 Data Quality Controls

- **Control Point**: **User Input**; **Validation**: Laravel validation rules; **Error Handling**: Return 422 with errors
- **Control Point**: **External API**; **Validation**: JSON schema validation; **Error Handling**: Fallback to cache or alternate API
- **Control Point**: **OCR Extraction**; **Validation**: Confidence threshold ≥ 80%; **Error Handling**: Manual correction UI
- **Control Point**: **AI Response**; **Validation**: Format and content validation; **Error Handling**: Retry with fallback provider
- **Control Point**: **Import Data**; **Validation**: Business rule validation; **Error Handling**: Preview + error report

---

## Document Control

- **Version**: 2.4.0; **Date**: 2026-02-22; **Author**: Development Team; **Changes**: Added Admin Panel data paths; updated external API references (GameTora); added Neuron AI v2.11 version; updated stats (571 routes, 3,316+ tests)
- **Version**: 2.3.0; **Date**: 2026-02-21; **Author**: Development Team; **Changes**: Updated version/date metadata; Livewire 3→4, Alpine.js→Alpine.js 3; aligned with 30 current Eloquent models
- **Version**: 2.2.0; **Date**: 2026-01-27; **Author**: Development Team; **Changes**: Added §8 Level 2 DFD - Performance Monitoring with 8 APM services; renumbered sections
- **Version**: 2.0.0; **Date**: 2026-01-23; **Author**: Development Team; **Changes**: Complete rewrite aligned with v2.0.0 implementation; added AI, MCP, external integration, and OCR flows; updated all diagrams and specifications
- **Version**: 1.0.0; **Date**: 2026-01-03; **Author**: Development Team; **Changes**: Initial draft

---

## Related Documents

- [002_BRS - Business Requirements Specifications](002_BRS_Business_Requirements_Specifications.md)
- [003_SRS - Software Requirements Specifications](003_SRS_Software_Requirement_Specifications.md)
- [004_SDS - Software Design Specifications](004_SDS_Software_Design_Specifications.md)
- [007_SIP - Software Integration Plan](007_SIP_Software_Integration_Plan.md)
- [008_SIS - Software Integration Specifications](008_SIS_Software_Integration_Specifications.md)
- [009_DBD - Database Documentation](009_DBD_Database_Documentation.md)
- [SPEC-006 - AI Advisory Technical Specification](../specs/SPEC-006_AI_Advisory_Technical.md)
- [SPEC-007 - External Integration Technical Specification](../specs/SPEC-007_External_Integration_Technical.md)
- [FLOW-006 - AI Advisory System Flow](../flows/FLOW-006_AI_Advisory_System.md)
- [FLOW-007 - External Integration System Flow](../flows/FLOW-007_External_Integration_System.md)

---

### This DFD document provides comprehensive data flow analysis for the Umamusume Pretty Derby Career Planner v2.4.0, reflecting the current implementation architecture and integration patterns