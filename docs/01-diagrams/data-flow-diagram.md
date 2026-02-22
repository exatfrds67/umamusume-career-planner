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

| Symbol | Meaning |
|--------|---------|
| Circle/Bubble | Process that transforms data |
| Rectangle | External entity (source/destination) |
| Open Rectangle | Data store (database, cache, file) |
| Arrow | Data flow direction |

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

| Entity | Type | Description | Data Flow |
|--------|------|-------------|-----------|
| **Player** | Human | End user interacting with system | Input: Manual data, Screenshots, Goals<br/>Output: Recommendations, Analytics |
| **Admin** | Human | System administrator managing application | Input: Config changes, User management<br/>Output: Logs, Metrics, Alerts |
| **umapyoi.net API** | System | Primary external game data source | Input: Character catalog, Skills, Support cards |
| **GameTora** | System | Secondary data source (scraping) | Input: Scraped game data |
| **Community Sources** | System | Meta rankings and strategies | Input: Tier lists, Meta data |
| **Ollama Server** | System | Local AI inference engine | Input: AI queries<br/>Output: Recommendations |
| **AWS Bedrock API** | System | Cloud AI fallback | Input: Complex queries<br/>Output: Recommendations |

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

| Process | Name | Description | Key Inputs | Key Outputs |
|---------|------|-------------|------------|-------------|
| **1.0** | Data Collection & Integration | Aggregates external game data with caching and circuit breaker | External API responses | Validated, cached game data |
| **2.0** | Character Management | Manages character lifecycle, stats, aptitudes | User input, imported data | Character records |
| **3.0** | Training Optimization | Predicts stat gains, calculates bonuses, provides recommendations | Character state, support deck | Training predictions, recommendations |
| **4.0** | Race Strategy Analysis | Analyzes race requirements, calculates readiness, recommends strategies | Character stats, race data | Race strategies, win probabilities |
| **5.0** | Skill Management | Manages skill catalog, tracks hints, optimizes SP budget | Available skills, character SP | Skill recommendations, acquisition tracking |
| **6.0** | Support Card Management | Manages card inventory, builds decks, calculates synergy | Card collection, deck configs | Optimized decks, synergy scores |
| **7.0** | AI Advisory System | Provides intelligent recommendations via hybrid AI | User queries, context data | AI-powered advice, confidence scores |
| **8.0** | Data Management & Export | Handles import, export, backup, migration | User files, system data | Exported files, imported records |
| **9.0** | Admin Panel & Monitoring | System administration, user management, log viewing, queue monitoring | Admin requests, system metrics | Dashboard views, alerts, system config |

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

| Sub-Process | Description | Algorithm | Caching |
|-------------|-------------|-----------|---------|
| **3.1 Base Stat Calculator** | Calculates raw stat gains per facility | Growth rates × Facility multipliers | No |
| **3.2 Support Bonus Calculator** | Applies support card bonuses and friendship multipliers | Bonus stacking with friendship thresholds | No |
| **3.3 Risk Assessor** | Calculates failure probability based on energy/mood/conditions | Risk score = f(energy, mood, conditions) | No |
| **3.4 Skill Hint Predictor** | Determines probability of skill hints per facility | Hint chance based on support participation | No |
| **3.5 Recommendation Ranker** | Ranks training options by expected value and alignment with goals | Multi-criteria scoring | 5 min TTL |
| **3.6 AI Training Advisor** | Provides intelligent training recommendations via Neuron agents | Neuron AI + Ollama/Bedrock | Session-based |

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

| Sub-Process | Description | Implementation |
|-------------|-------------|----------------|
| **7.1 Query Analyzer** | Analyzes user query intent and complexity | Pattern matching, keyword extraction |
| **7.2 Context Builder** | Enriches query with character and career context | Data aggregation from multiple stores |
| **7.3 Provider Router** | Routes query to appropriate AI provider based on complexity | Ollama (simple) vs Bedrock (complex) |
| **7.4 Ollama Service** | Processes queries using local Ollama models | HTTP API client, timeout 30s |
| **7.5 Bedrock Service** | Processes queries using AWS Bedrock Claude models | AWS SDK, Claude Sonnet/Haiku |
| **7.6 Response Synthesizer** | Formats and enriches AI responses with confidence scores | JSON formatting, confidence calculation |
| **7.7 Cost Tracker** | Tracks token usage and calculates costs for cloud AI | Database persistence, budget monitoring |

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

| Sub-Process | Description | Resilience Pattern |
|-------------|-------------|--------------------|
| **1.1 Circuit Breaker** | Monitors API health and opens circuit on failure threshold | Open after 5 failures in 120s window |
| **1.2 API Client Manager** | Manages connections to external APIs with fallback logic | Primary → Fallback → Cached |
| **1.3 Response Parser** | Parses and validates API responses against schema | JSON schema validation |
| **1.4 Cache Manager** | Stores and retrieves cached responses with TTL management | 24-hour TTL, Redis-backed |
| **1.5 OCR Processor** | Processes screenshots with GD preprocessing and Tesseract OCR | GD grayscale → threshold → OCR |

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

| Sub-Process | Description | Supported Formats |
|-------------|-------------|-------------------|
| **8.1 Format Detector** | Detects import file format and schema version | JSON v1.0, CSV, XLSX, Legacy formats |
| **8.2 Data Validator** | Validates imported data against business rules | Stat ranges, enum values, relationships |
| **8.3 Conflict Resolver** | Resolves duplicate/conflict records using user strategy | Skip, Overwrite, Merge, Rename |
| **8.4 Data Importer** | Imports validated records into database with transaction management | Batch processing, rollback on error |
| **8.5 Data Exporter** | Exports data to requested format with schema versioning | JSON, CSV, XLSX with metadata |
| **8.6 Backup Manager** | Creates full backup archives with restore capability | ZIP with manifest, incremental support |

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

| Process | Description | Key Inputs | Key Outputs |
|---------|-------------|------------|-------------|
| **8.1 Metrics Collector** | Collects request-level metrics from middleware | HTTP request data, timing info | Raw metrics records |
| **8.2 Query Analyzer** | Analyzes database queries for optimization | SQL queries, EXPLAIN plans | Query scores, suggestions |
| **8.3 Cache Analyzer** | Analyzes cache hit/miss patterns | Cache operations | Hit rates, TTL suggestions |
| **8.4 Alert Engine** | Compares metrics against thresholds | Aggregated metrics, thresholds | Alert notifications |
| **8.5 Aggregator** | Creates time-based metric aggregates | Raw metrics | Minute/hour/day aggregates |

### 8.4 Data Flows

| Flow | Source → Destination | Data Content |
|------|---------------------|--------------|
| **DF8.1** | Middleware → Metrics Collector | Request ID, endpoint, start time |
| **DF8.2** | Query Listener → Query Analyzer | SQL, duration, bindings |
| **DF8.3** | Cache Listener → Cache Analyzer | Key, operation, hit/miss, latency |
| **DF8.4** | Metrics Collector → Redis | JSON metrics record |
| **DF8.5** | Query Analyzer → Redis | Query performance score |
| **DF8.6** | Redis → Alert Engine | Aggregated metrics |
| **DF8.7** | Alert Engine → Notifications | Alert payload (Slack, Email) |
| **DF8.8** | Aggregator → MySQL | Minute/hour/day aggregates |

### 8.5 Performance Monitoring Services

| Service | Function |
|---------|----------|
| `ApmService` | Core APM coordination and metrics storage |
| `ApiPerformanceMonitoringService` | API endpoint performance tracking |
| `QueryOptimizationService` | Database query analysis |
| `PerformanceAlertingService` | Alert generation and delivery |
| `RedisCacheOptimizationService` | Cache analytics and optimization |
| `ApiResponseCachingService` | Response caching strategies |
| `PerformanceRegressionService` | Regression detection |
| `HistoricalTrackingService` | Long-term metrics storage |

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

| Store ID | Name | Type | Persistence | Description |
|----------|------|------|-------------|-------------|
| **D1** | Characters | MySQL | Permanent | Character records with stats and aptitudes |
| **D2** | Careers | MySQL | Permanent | Career run tracking and progression |
| **D3** | Skills | MySQL | Permanent | Skill catalog and acquisition history |
| **D4** | Support Cards | MySQL | Permanent | Support card inventory and deck configurations |
| **D5** | External API Cache | Redis | 24h TTL | Cached responses from external APIs |
| **D6** | AI Conversations | MySQL | 90 days | AI conversation history and context |
| **D7** | Prediction Cache | Redis | 5 min TTL | Training prediction results |
| **D8** | AI Costs | MySQL | Permanent | Token usage and cost tracking |
| **D9** | OCR Extractions | MySQL | Permanent | OCR processing results |
| **D10** | Migration History | MySQL | Permanent | Data migration audit trail |
| **D11** | Backups | File System | User-managed | Backup archives |

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

| Cache Level | Technology | Use Case | TTL |
|-------------|-----------|----------|-----|
| **L1 Application** | PHP Array | Request-scoped data | Request lifetime |
| **L2 Shared** | Redis | Cross-request data, predictions | 5 min - 24 hours |
| **L3 Database** | MySQL | Persistent data | Permanent |

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

| Flow | Volume | Frequency | Latency Target |
|------|--------|-----------|----------------|
| User input → Character update | 1 record | Per action | < 200ms |
| Training predictions request | 6-8 options | Per turn | < 1.2s |
| AI advisory request | 1 query | As needed | < 2.5s |
| External API sync | 100-500 records | Daily | < 30s |
| OCR processing | 1 image | Manual | < 10s |
| Data export | 1-100 careers | Manual | < 60s |

### 9.3 Data Transformation Points

| Transformation | Input Format | Output Format | Process |
|----------------|--------------|---------------|---------|
| **External API Response** | JSON (external schema) | JSON (internal schema) | Schema mapping, validation |
| **OCR Text Extraction** | Image (PNG/JPG) | Structured text | GD preprocessing → Tesseract → Parsing |
| **Training Calculation** | Character state + Deck | Prediction array | Multi-step calculation pipeline |
| **AI Query** | Natural language | Structured response | Context enrichment → LLM → Formatting |
| **Export** | Database records | JSON/CSV/XLSX | Serialization with schema versioning |

### 9.4 Data Quality Controls

| Control Point | Validation | Error Handling |
|---------------|------------|----------------|
| **User Input** | Laravel validation rules | Return 422 with errors |
| **External API** | JSON schema validation | Fallback to cache or alternate API |
| **OCR Extraction** | Confidence threshold ≥ 80% | Manual correction UI |
| **AI Response** | Format and content validation | Retry with fallback provider |
| **Import Data** | Business rule validation | Preview + error report |

---

## Document Control

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 2.4.0 | 2026-02-22 | Development Team | Added Admin Panel data paths; updated external API references (GameTora); added Neuron AI v2.11 version; updated stats (571 routes, 3,316+ tests) |
| 2.3.0 | 2026-02-21 | Development Team | Updated version/date metadata; Livewire 3→4, Alpine.js→Alpine.js 3; aligned with 30 current Eloquent models |
| 2.2.0 | 2026-01-27 | Development Team | Added §8 Level 2 DFD - Performance Monitoring with 8 APM services; renumbered sections |
| 2.0.0 | 2026-01-23 | Development Team | Complete rewrite aligned with v2.0.0 implementation; added AI, MCP, external integration, and OCR flows; updated all diagrams and specifications |
| 1.0.0 | 2026-01-03 | Development Team | Initial draft |

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

*This DFD document provides comprehensive data flow analysis for the Umamusume Pretty Derby Career Planner v2.4.0, reflecting the current implementation architecture and integration patterns.*
