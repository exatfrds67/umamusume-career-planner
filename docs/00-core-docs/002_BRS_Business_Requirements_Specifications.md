# Business Requirements Specifications (BRS)

## Umamusume Pretty Derby Career Planner

**Document Version**: 2.4.0
**Date**: February 22, 2026
**Project**: UmamusumeCareerPlanner
**Author**: Development Team
**Status**: Current - Aligned with codebase v2.4.0 and game-accurate mechanics

---

## Table of Contents

1. [Introduction](#1-introduction)
2. [Business Context](#2-business-context)
3. [Stakeholder Analysis](#3-stakeholder-analysis)
4. [Business Requirements](#4-business-requirements)
5. [Business Rules](#5-business-rules)
6. [Business Process Flows](#6-business-process-flows)
7. [Success Metrics](#7-success-metrics)
8. [Constraints and Assumptions](#8-constraints-and-assumptions)
9. [Dependencies](#9-dependencies)
10. [Document Control](#10-document-control)

---

## 1. Introduction

### 1.1 Purpose

This document defines the business objectives and current scope for the Umamusume Pretty Derby Career Planner application. It establishes the business context, stakeholder needs, and high-level requirements that guide the technical implementation.

### 1.2 Scope

The Umamusume Career Planner is a comprehensive web application built with **Laravel 12** (released February 24, 2025), **Livewire 4**, **Alpine.js 3**, **Tailwind CSS v4** (released January 22, 2025), and integrates with **AWS Bedrock Claude 4.5** models and **Ollama** for AI capabilities via **Neuron AI v2.11**. The system enables players of Uma Musume: Pretty Derby to track, manage, and optimize their career progression through intelligent recommendations and analytics.

### 1.3 Definitions and Acronyms

- **Term**: Plan/Career Run; **Definition**: A career run record tracking an Uma Musume character's training progression
- **Term**: Uma Musume; **Definition**: A horse girl character from the Uma Musume: Pretty Derby game
- **Term**: SP (Skill Points); **Definition**: Points earned from races and events, spent to purchase skills
- **Term**: Stat Soft Cap; **Definition**: Soft cap at 1200 - stats above 1200 count for 50% value (diminishing returns)
- **Term**: URA Finale; **Definition**: The final race series at the end of Senior Year
- **Term**: OCR; **Definition**: Optical Character Recognition for screenshot data extraction
- **Term**: MCP; **Definition**: Model Context Protocol for AI integration

---

## 2. Business Context

### 2.1 Business Problem

Players of Uma Musume: Pretty Derby currently lack a comprehensive, unified tool to:

- Track character training progression across multiple career runs
- Plan skill acquisitions and race strategies with AI-powered recommendations
- Analyze training effectiveness and outcomes with predictive analytics
- Export and share training records
- Access data across devices or offline
- Receive intelligent advisory for optimal training decisions

### 2.2 Business Opportunity

By providing a modern, AI-enhanced planning platform, we can:

- Deliver superior user experience with modern web technologies (Laravel 12, Livewire 4, Tailwind CSS v4)
- Enable offline-first usage for players without reliable connectivity
- Support cross-device access for authenticated users
- Improve accessibility for users with disabilities (WCAG AA compliance)
- Provide AI-powered training optimization and race strategy recommendations
- Integrate external game data sources for accurate planning

### 2.3 Business Objectives

- **Objective**: User Adoption; **Success Metric**: Active users tracking plans
- **Objective**: Data Migration; **Success Metric**: Successful import of legacy data
- **Objective**: Accessibility; **Success Metric**: WCAG AA compliance
- **Objective**: Performance; **Success Metric**: Page load < 2 seconds
- **Objective**: Reliability; **Success Metric**: 99% uptime for Account mode
- **Objective**: AI Advisory; **Success Metric**: Response times within configured timeouts

### 2.4 Value Proposition

```mermaid
mindmap
  root((Umamusume Career Planner))
    Core Platform
      Laravel 12 Backend
      Livewire 4 + Alpine.js 3
      Tailwind CSS v4
      PWA Capabilities
    AI Integration
      Neuron AI v2.11 Agents
      Hybrid Ollama/Bedrock
      Training Optimization
      Race Strategy
    Data Management
      Import/Export Workflows
      OCR Processing
      Backup/Restore
      Migration Support
    External Integration
      umapyoi.net API
      UmamusumeDB API
      Community Sources
      Real-time Updates
```text

---

## 3. Stakeholder Analysis

### 3.1 Primary Stakeholders

#### 3.1.1 Players (End Users)

### Needs

- Quick and easy plan creation with AI recommendations
- Offline access to data via PWA
- Cross-device synchronization
- Data export for sharing/backup
- Accessible interface (WCAG AA compliant)
- Intelligent training and race strategy guidance

### Pain Points

- Current tools are fragmented
- No AI-powered optimization
- Poor mobile experience
- Data locked in single application

#### 3.1.2 Developers/Maintainers

### Needs - Developers

- Single codebase to maintain
- Modern, well-documented architecture
- Comprehensive test coverage
- Clear development standards
- Monitoring and observability

### 3.2 Stakeholder Map

```mermaid
quadrantChart
    title Stakeholder Influence vs Interest
    x-axis Low Interest --> High Interest
    y-axis Low Influence --> High Influence
    quadrant-1 Keep Satisfied
    quadrant-2 Manage Closely
    quadrant-3 Monitor
    quadrant-4 Keep Informed
    Players: [0.9, 0.7]
    Developers: [0.8, 0.9]
    Content Creators: [0.6, 0.3]
    Community: [0.5, 0.4]
```

---

## 4. Business Requirements

### 4.1 Character Management [BR-1]

**Business Need:** Players need to manage their Uma Musume character roster with complete information.

- **ID**: BR-1.1; **Requirement**: Create, view, update, and delete character records; **Priority**: P0; **Status**: Implemented
- **ID**: BR-1.2; **Requirement**: Store character images with visual preview; **Priority**: P1; **Status**: Implemented
- **ID**: BR-1.3; **Requirement**: Track aptitude grades for terrain, distance, and style; **Priority**: P0; **Status**: Implemented
- **ID**: BR-1.4; **Requirement**: Track growth rate bonuses for all five stats; **Priority**: P0; **Status**: Implemented
- **ID**: BR-1.5; **Requirement**: Factor inheritance system with stat/aptitude bonuses; **Priority**: P0; **Status**: Implemented
- **ID**: BR-1.6; **Requirement**: Goal management and progress tracking; **Priority**: P1; **Status**: Implemented

### Related Artifacts

- PRD: [PRD-001](prds/PRD-001_Character_Management.md)
- SPEC: [SPEC-001](specs/SPEC-001_Character_Management_Technical.md)
- Flow: [FLOW-001](flows/FLOW-001_Character_Management_System.md)
- Wireframes: [WF-002](wireframes/WF-002_Character_Creation_Wizard.md), [WF-003](wireframes/WF-003_Character_Detail_Management.md)

### 4.2 Training Optimization [BR-2]

**Business Need:** Players need intelligent training recommendations and predictions.

- **ID**: BR-2.1; **Requirement**: Training prediction engine with stat gain calculations; **Priority**: P0; **Status**: Implemented
- **ID**: BR-2.2; **Requirement**: Support card bonus integration; **Priority**: P0; **Status**: Implemented
- **ID**: BR-2.3; **Requirement**: Skill hint tracking and SP cost reduction; **Priority**: P0; **Status**: Implemented
- **ID**: BR-2.4; **Requirement**: AI-powered training recommendations; **Priority**: P0; **Status**: Implemented
- **ID**: BR-2.5; **Requirement**: Training session history and analytics; **Priority**: P1; **Status**: Implemented

### Related Artifacts - Training Optimization

- PRD: [PRD-002](prds/PRD-002_Training_Optimization.md)
- SPEC: [SPEC-002](specs/SPEC-002_Training_Optimization_Technical.md)
- Flow: [FLOW-002](flows/FLOW-002_Training_Optimization_System.md)
- Wireframes: [WF-004](wireframes/WF-004_Training_Selection_Interface.md), [WF-005](wireframes/WF-005_Training_Result_Screen.md)

### 4.3 Race Strategy [BR-3]

**Business Need:** Players need race preparation guidance and strategy optimization.

- **ID**: BR-3.1; **Requirement**: Race calendar with requirements and readiness scoring; **Priority**: P0; **Status**: Implemented
- **ID**: BR-3.2; **Requirement**: Running style optimization (4 styles); **Priority**: P0; **Status**: Implemented
- **ID**: BR-3.3; **Requirement**: Win probability calculation; **Priority**: P1; **Status**: Implemented
- **ID**: BR-3.4; **Requirement**: AI-powered race strategy recommendations; **Priority**: P0; **Status**: Implemented
- **ID**: BR-3.5; **Requirement**: Race history and performance analytics; **Priority**: P1; **Status**: Implemented

### Related Artifacts - Race Strategy

- PRD: [PRD-003](prds/PRD-003_Race_Strategy.md)
- SPEC: [SPEC-003](specs/SPEC-003_Race_Strategy_Technical.md)
- Flow: [FLOW-003](flows/FLOW-003_Race_Strategy_System.md)
- Wireframes: [WF-006](wireframes/WF-006_Race_Calendar_View.md), [WF-007](wireframes/WF-007_Race_Preparation_Screen.md)

### 4.4 Skill Management [BR-4]

**Business Need:** Players need to plan and track skill acquisitions efficiently.

- **ID**: BR-4.1; **Requirement**: Skill catalog with search (English and Japanese); **Priority**: P0; **Status**: Implemented
- **ID**: BR-4.2; **Requirement**: Hint-based SP cost reduction (5 levels: 10%/20%/30%/35%/40% max); **Priority**: P0; **Status**: Implemented
- **ID**: BR-4.3; **Requirement**: Skill evolution system (Normal → Rare); **Priority**: P0; **Status**: Implemented
- **ID**: BR-4.4; **Requirement**: SP budget optimization; **Priority**: P1; **Status**: Implemented
- **ID**: BR-4.5; **Requirement**: AI skill build recommendations; **Priority**: P1; **Status**: Implemented

### Related Artifacts - Skill Management

- PRD: [PRD-004](prds/PRD-004_Skill_Management.md)
- SPEC: [SPEC-004](specs/SPEC-004_Skill_Management_Technical.md)
- Flow: [FLOW-004](flows/FLOW-004_Skill_Management_System.md)
- Wireframes: [WF-008](wireframes/WF-008_Skill_Shop_Interface.md), [WF-009](wireframes/WF-009_Skill_Loadout_Manager.md)

### 4.5 Support Card Management [BR-5]

**Business Need:** Players need to optimize support card decks for training.

- **ID**: BR-5.1; **Requirement**: Support card database (200+ cards with meta tiers); **Priority**: P0; **Status**: Implemented
- **ID**: BR-5.2; **Requirement**: Deck composition validator (6-card deck); **Priority**: P0; **Status**: Implemented
- **ID**: BR-5.3; **Requirement**: Bond level and limit break tracking; **Priority**: P0; **Status**: Implemented
- **ID**: BR-5.4; **Requirement**: Deck synergy scoring and recommendations; **Priority**: P1; **Status**: Implemented
- **ID**: BR-5.5; **Requirement**: Meta tier synchronization from external sources; **Priority**: P1; **Status**: Implemented

### Related Artifacts - Support Card Management

- PRD: [PRD-005](prds/PRD-005_Support_Card_Management.md)
- SPEC: [SPEC-005](specs/SPEC-005_Support_Card_Management_Technical.md)
- Flow: [FLOW-005](flows/FLOW-005_Support_Card_Management_System.md)
- Wireframes: [WF-010](wireframes/WF-010_Support_Card_Collection.md), [WF-011](wireframes/WF-011_Support_Deck_Builder.md)

### 4.6 AI Advisory System [BR-6]

**Business Need:** Players need intelligent recommendations across all planning aspects.

- **ID**: BR-6.1; **Requirement**: Hybrid AI architecture (Ollama local + AWS Bedrock fallback); **Priority**: P0; **Status**: Implemented
- **ID**: BR-6.2; **Requirement**: Training, race, and skill advisory capabilities; **Priority**: P0; **Status**: Implemented
- **ID**: BR-6.3; **Requirement**: Conversation history management; **Priority**: P1; **Status**: Implemented
- **ID**: BR-6.4; **Requirement**: Cost tracking and budget management for cloud AI; **Priority**: P1; **Status**: Implemented
- **ID**: BR-6.5; **Requirement**: Confidence scoring for recommendations; **Priority**: P1; **Status**: Implemented

### Related Artifacts - AI Advisory

- PRD: [PRD-006](prds/PRD-006_AI_Advisory.md)
- SPEC: [SPEC-006](specs/SPEC-006_AI_Advisory_Technical.md)
- Flow: [FLOW-006](flows/FLOW-006_AI_Advisory_System.md)
- Wireframes: [WF-012](wireframes/WF-012_AI_Advisor_Interface.md)

### 4.7 External Integration [BR-7]

**Business Need:** Players need accurate, up-to-date game data from external sources.

- **ID**: BR-7.1; **Requirement**: External API integration (umapyoi.net, UmamusumeDB); **Priority**: P0; **Status**: Implemented
- **ID**: BR-7.2; **Requirement**: Circuit breaker pattern for resilience; **Priority**: P0; **Status**: Implemented
- **ID**: BR-7.3; **Requirement**: OCR screenshot processing; **Priority**: P1; **Status**: Implemented
- **ID**: BR-7.4; **Requirement**: WebSocket real-time updates (Laravel Reverb); **Priority**: P1; **Status**: Implemented
- **ID**: BR-7.5; **Requirement**: Community data sharing; **Priority**: P2; **Status**: Implemented

### Related Artifacts - External Integration

- PRD: [PRD-007](prds/PRD-007_External_Integration.md)
- SPEC: [SPEC-007](specs/SPEC-007_External_Integration_Technical.md)
- Flow: [FLOW-007](flows/FLOW-007_External_Integration_System.md)

### 4.8 Data Management [BR-8]

**Business Need:** Players need reliable data import, export, and backup capabilities.

- **ID**: BR-8.1; **Requirement**: JSON import/export with schema versioning; **Priority**: P0; **Status**: Implemented
- **ID**: BR-8.2; **Requirement**: Excel export (.xlsx); **Priority**: P1; **Status**: Implemented
- **ID**: BR-8.3; **Requirement**: Backup and restore workflows; **Priority**: P0; **Status**: Implemented
- **ID**: BR-8.4; **Requirement**: Data migration between storage modes; **Priority**: P1; **Status**: Implemented
- **ID**: BR-8.5; **Requirement**: OCR-based data capture and validation; **Priority**: P1; **Status**: Implemented

### 4.9 Dual Storage Mode [BR-9]

**Business Need:** Players need flexibility in how their data is stored and accessed.

- **ID**: BR-9.1; **Requirement**: Local storage mode (browser localStorage); **Priority**: P0; **Status**: Implemented
- **ID**: BR-9.2; **Requirement**: Account storage mode (database); **Priority**: P0; **Status**: Implemented
- **ID**: BR-9.3; **Requirement**: Clear visual indication of storage mode; **Priority**: P0; **Status**: Implemented
- **ID**: BR-9.4; **Requirement**: Full offline functionality for Local runs; **Priority**: P0; **Status**: Implemented
- **ID**: BR-9.5; **Requirement**: Convert Local runs to Account runs; **Priority**: P0; **Status**: Implemented
- **ID**: BR-9.6; **Requirement**: Local data management interface; **Priority**: P1; **Status**: Implemented

### 4.10 Performance and Reliability [BR-10]

**Business Need:** System must be performant and reliable for power users.

- **ID**: BR-10.1; **Requirement**: APM and performance dashboards; **Priority**: P1; **Status**: In Progress
- **ID**: BR-10.2; **Requirement**: Cache monitoring and invalidation; **Priority**: P1; **Status**: In Progress
- **ID**: BR-10.3; **Requirement**: Fallback and degradation workflows; **Priority**: P1; **Status**: In Progress
- **ID**: BR-10.4; **Requirement**: Page load < 2 seconds; **Priority**: P0; **Status**: Implemented

### 4.11 User Experience and Accessibility [BR-11]

**Business Need:** Application must be accessible and provide excellent UX.

- **ID**: BR-11.1; **Requirement**: PWA offline route coverage; **Priority**: P1; **Status**: In Progress
- **ID**: BR-11.2; **Requirement**: Accessibility pages and keyboard shortcuts; **Priority**: P1; **Status**: In Progress
- **ID**: BR-11.3; **Requirement**: Dark/Light mode toggle with persistence; **Priority**: P0; **Status**: Implemented
- **ID**: BR-11.4; **Requirement**: Responsive design (320px to 2560px); **Priority**: P0; **Status**: Implemented
- **ID**: BR-11.5; **Requirement**: WCAG AA accessibility compliance; **Priority**: P0; **Status**: Implemented

### 4.12 MCP Integration [BR-12]

**Business Need:** Development team needs AI orchestration, tool management, and monitoring capabilities.

- **ID**: BR-12.1; **Requirement**: Laravel MCP server with custom tools; **Priority**: P1; **Status**: Implemented
- **ID**: BR-12.2; **Requirement**: MCP agent configuration and orchestration; **Priority**: P1; **Status**: Implemented
- **ID**: BR-12.3; **Requirement**: MCP tool usage monitoring and dashboards; **Priority**: P1; **Status**: Implemented
- **ID**: BR-12.4; **Requirement**: MCP server management interface; **Priority**: P2; **Status**: Implemented

### 4.13 Admin Panel [BR-13]

**Business Need:** Administrators need system management and monitoring capabilities.

- **ID**: BR-13.1; **Requirement**: Database management interface; **Priority**: P1; **Status**: Implemented
- **ID**: BR-13.2; **Requirement**: Application log viewer; **Priority**: P1; **Status**: Implemented
- **ID**: BR-13.3; **Requirement**: Queue monitor dashboard; **Priority**: P1; **Status**: Implemented
- **ID**: BR-13.4; **Requirement**: User management; **Priority**: P1; **Status**: Implemented
- **ID**: BR-13.5; **Requirement**: System settings configuration; **Priority**: P2; **Status**: Implemented

### 4.14 Requirements Priority Matrix

```mermaid
pie title Requirements by Priority
    "P0 - Critical" : 32
    "P1 - High" : 33
    "P2 - Medium" : 11
```text

---

## 5. Business Rules

### 5.1 Data Validation Rules

- **Rule ID**: BV-1; **Rule Description**: Plan title is required and cannot be empty
- **Rule ID**: BV-2; **Rule Description**: Stat values have soft cap at 1200 (50% effectiveness above), practical max ~1600
- **Rule ID**: BV-3; **Rule Description**: Turn numbers must be between 1 and 78
- **Rule ID**: BV-4; **Rule Description**: Skill status "Acquired" requires turn_acquired value
- **Rule ID**: BV-5; **Rule Description**: Energy level must be between 0 and 100
- **Rule ID**: BV-6; **Rule Description**: Support deck must contain exactly 6 cards (5 owned + 1 borrowed)
- **Rule ID**: BV-7; **Rule Description**: Skill hint levels cap at 5 (40% maximum discount: 10%/20%/30%/35%/40%)

### 5.2 Calculation Rules

- **Rule ID**: BC-1; **Rule Description**: Stat soft cap at 1200 (values above count for 50%, practical max ~1600)
- **Rule ID**: BC-2; **Rule Description**: Acquired SP = sum of sp_cost where status = acquired
- **Rule ID**: BC-3; **Rule Description**: Mood modifiers: Great +4%, Good +2%, Normal 0%, Bad -2%, Awful -4%
- **Rule ID**: BC-4; **Rule Description**: Aptitude effectiveness: S=+5% (max), A=0% (baseline), B=-10%, C=-20%, D=-30%/-40%, E=-50%/-60%, F=-70%/-80%, G=-90%
- **Rule ID**: BC-5; **Rule Description**: Skill hint discount: Level 1=10%, Level 2=20%, Level 3=30%, Level 4=35%, Level 5=40% (max)
- **Rule ID**: BC-6; **Rule Description**: Factor inheritance: ★☆☆=+5, ★★☆=+12, ★★★=+21 bonus

### 5.3 Storage Rules

- **Rule ID**: BS-1; **Rule Description**: Local runs use UUID identifiers
- **Rule ID**: BS-2; **Rule Description**: Account runs use database integer IDs
- **Rule ID**: BS-3; **Rule Description**: Local runs are fully functional offline
- **Rule ID**: BS-4; **Rule Description**: Account runs require network connectivity to save
- **Rule ID**: BS-5; **Rule Description**: Drafts are always saved to localStorage regardless of storage mode
- **Rule ID**: BS-6; **Rule Description**: External API data cached for 24 hours
- **Rule ID**: BS-7; **Rule Description**: Training predictions cached for 5 minutes

### 5.4 AI Advisory Rules

- **Rule ID**: BA-1; **Rule Description**: Local Ollama model used as primary for simple queries
- **Rule ID**: BA-2; **Rule Description**: AWS Bedrock used as fallback for complex decisions or local unavailability
- **Rule ID**: BA-3; **Rule Description**: AI responses include confidence scores
- **Rule ID**: BA-4; **Rule Description**: Token usage tracked for cost management
- **Rule ID**: BA-5; **Rule Description**: Conversation context maintained per session

---

## 6. Business Process Flows

### 6.1 Career Planning Flow

```mermaid
flowchart TD
    Start([User Starts New Career]) --> SelectTrainee[Select Trainee]
    SelectTrainee --> PickScenario[Pick Scenario]
    PickScenario --> PickParents[Pick Parents A/B]
    PickParents --> PreviewFactors[Preview Factor Bonuses]
    PreviewFactors --> BuildDeck[Build Support Deck]
    BuildDeck --> ValidateDeck{Deck Valid?}
    ValidateDeck -->|No| FixDeck[Fix type/rarity issues]
    FixDeck --> ValidateDeck
    ValidateDeck -->|Yes| InitializeStats[Initialize Stats/Mood/Energy]
    InitializeStats --> SaveRun[Persist Run + Seed]
    SaveRun --> Dashboard[Show Dashboard Day 1]
    Dashboard --> TrainingLoop{Training Phase}
    TrainingLoop --> GetPredictions[Get AI Training Predictions]
    GetPredictions --> SelectTraining[Select Training Option]
    SelectTraining --> ExecuteTraining[Execute Training]
    ExecuteTraining --> UpdateStats[Update Stats/Mood/Energy]
    UpdateStats --> CheckRace{Race Week?}
    CheckRace -->|Yes| RacePrep[Race Preparation]
    RacePrep --> ExecuteRace[Execute Race]
    ExecuteRace --> TrainingLoop
    CheckRace -->|No| TrainingLoop
```

### 6.2 AI Advisory Flow

```mermaid
flowchart TD
    Query([User Submits Query]) --> Analyze[Analyze Intent/Complexity]
    Analyze --> CheckOllama{Ollama Available?}
    CheckOllama -->|Yes & Simple| RouteOllama[Route to Local Ollama]
    CheckOllama -->|No or Complex| RouteCloud[Route to AWS Bedrock]
    RouteOllama --> BuildPrompt[Build Prompt + Context]
    RouteCloud --> BuildPrompt
    BuildPrompt --> CallModel[Call AI Model]
    CallModel --> Receive[Receive Response]
    Receive --> ScoreConfidence[Score Confidence]
    ScoreConfidence --> TrackCost[Track Token Usage/Cost]
    TrackCost --> ReturnUser[Return to User]
```text

### 6.3 External Data Sync Flow

```mermaid
flowchart TD
    Trigger([Sync Triggered]) --> CheckCircuit{Circuit Breaker Status?}
    CheckCircuit -->|Closed| FetchPrimary[Fetch from umapyoi.net]
    CheckCircuit -->|Open| UseCached[Use Cached Data]
    FetchPrimary --> Success{Success?}
    Success -->|Yes| UpdateCache[Update Cache - 24hr TTL]
    Success -->|No| IncrementFailure[Increment Failure Count]
    IncrementFailure --> CheckThreshold{Threshold Exceeded?}
    CheckThreshold -->|Yes| OpenCircuit[Open Circuit Breaker]
    CheckThreshold -->|No| TryFallback[Try Fallback API]
    OpenCircuit --> UseCached
    TryFallback --> FallbackSuccess{Success?}
    FallbackSuccess -->|Yes| UpdateCache
    FallbackSuccess -->|No| UseCached
    UpdateCache --> NotifyUpdate[Notify Subscribers via WebSocket]
    UseCached --> Complete([Sync Complete])
    NotifyUpdate --> Complete
```

### 6.4 OCR Data Capture Flow

```mermaid
flowchart TD
    Upload([User Uploads Screenshot]) --> Preprocess[Preprocess Image]
    Preprocess --> OCRExtract[OCR Text Extraction]
    OCRExtract --> ParseData[Parse Game Data]
    ParseData --> Validate{Validation Pass?}
    Validate -->|Yes| AutoPopulate[Auto-populate Form Fields]
    Validate -->|No| ManualCorrection[Show Manual Correction UI]
    ManualCorrection --> UserEdit[User Edits Values]
    UserEdit --> Validate
    AutoPopulate --> UserReview[User Reviews Data]
    UserReview --> Confirm{Confirm Import?}
    Confirm -->|Yes| SaveData[Save to Career Run]
    Confirm -->|No| Discard[Discard Changes]
```text

---

## 7. Success Metrics

### 7.1 User Experience Metrics

- **Metric**: Time to create first plan; **Target**: < 30 seconds; **Measurement Method**: User testing
- **Metric**: Task completion rate; **Target**: > 95%; **Measurement Method**: Analytics
- **Metric**: User satisfaction; **Target**: > 4/5 stars; **Measurement Method**: Surveys
- **Metric**: AI recommendation acceptance rate; **Target**: > 70%; **Measurement Method**: Analytics

### 7.2 Technical Metrics

- **Metric**: Page load time; **Target**: < 2 seconds; **Measurement Method**: Performance monitoring
- **Metric**: First Contentful Paint; **Target**: < 1.5 seconds; **Measurement Method**: Lighthouse
- **Metric**: Accessibility score; **Target**: 100% AA; **Measurement Method**: axe-core
- **Metric**: Error rate; **Target**: < 1%; **Measurement Method**: Error logging
- **Metric**: AI response time; **Target**: < 3 seconds; **Measurement Method**: APM monitoring
- **Metric**: API fallback success rate; **Target**: > 95%; **Measurement Method**: Circuit breaker metrics

### 7.3 Data Quality Metrics

- **Metric**: Successful data imports; **Target**: > 90%; **Measurement Method**: Import logs
- **Metric**: OCR accuracy rate; **Target**: > 85%; **Measurement Method**: Validation logs
- **Metric**: External API sync success; **Target**: > 95%; **Measurement Method**: Sync logs
- **Metric**: Backup completion rate; **Target**: 100%; **Measurement Method**: Backup logs

---

## 8. Constraints and Assumptions

### 8.1 Constraints

1. **Browser Storage Limits**: localStorage typically limited to 5-10MB
2. **Offline Limitations**: Account runs cannot be saved without connectivity
3. **Browser Support**: Modern browsers only (Chrome, Firefox, Safari, Edge)
4. **AI Cost**: AWS Bedrock usage incurs per-token costs
5. **External API Availability**: Dependent on umapyoi.net and UmamusumeDB uptime

### 8.2 Assumptions

1. Users have access to modern web browsers
2. Users understand basic Uma Musume game mechanics
3. Users have sufficient browser storage for Local runs
4. English is the primary interface language (Japanese skill names supported)
5. External APIs remain available and maintain current data formats
6. Local Ollama installation available for primary AI recommendations

---

## 9. Dependencies

### 9.1 External Dependencies

- **Dependency**: Browser localStorage API; **Description**: Local run storage; **Risk Level**: Low
- **Dependency**: Livewire connection; **Description**: Account run operations; **Risk Level**: Medium
- **Dependency**: Database availability; **Description**: Account data persistence; **Risk Level**: Medium
- **Dependency**: umapyoi.net API; **Description**: Primary external game data; **Risk Level**: Medium
- **Dependency**: UmamusumeDB API; **Description**: Fallback external game data; **Risk Level**: Low
- **Dependency**: AWS Bedrock; **Description**: Cloud AI fallback; **Risk Level**: Medium
- **Dependency**: Ollama; **Description**: Local AI primary; **Risk Level**: Low

### 9.2 Internal Dependencies

- **Dependency**: Character data; **Description**: Required before creating career runs
- **Dependency**: Skill reference database; **Description**: Required for skill autocomplete and management
- **Dependency**: Support card database; **Description**: Required for deck building
- **Dependency**: Authentication (optional); **Description**: Required for Account mode
- **Dependency**: AI services; **Description**: Required for training/race recommendations

### 9.3 Dependency Graph

```mermaid
flowchart BT
    A[Character Data] --> B[Career Run]
    C[Skill Database] --> D[Skill Management]
    D --> B
    E[Support Card Database] --> F[Deck Management]
    F --> B
    G[Authentication] -.->|Optional| H[Account Mode]
    H --> B
    I[localStorage] --> J[Local Mode]
    J --> B
    K[AI Services] --> L[Training Advisor]
    K --> M[Race Advisor]
    L --> B
    M --> B
    N[External APIs] --> O[Game Data Sync]
    O --> A
    O --> C
    O --> E
```

---

## 10. Document Control

### 10.1 Related Documents

- [SDP - Software Development Plan](001_SDP_Software_Development_Plan.md)
- [SRS - System Requirements Specifications](003_SRS_Software_Requirement_Specifications.md)
- [SDS - System Design Specifications](004_SDS_System_Design_Specifications.md)
- [PRD Index](prds/000_PRDS_INDEX.md)
- [SPEC Index](specs/000_SPECS_INDEX.md)
- [Data Flow Diagram](diagrams/data-flow-diagram.md)

### 10.2 Revision History

- **Version**: 2.4.0; **Date**: 2026-02-22; **Author**: Development Team; **Changes**: Added MCP Integration (BR-12) and Admin Panel (BR-13) business requirements; updated priority matrix counts
- **Version**: 2.3.0; **Date**: 2026-02-21; **Author**: Development Team; **Changes**: Updated tech stack references (Livewire 4, Neuron AI v2.11); replaced TypeScript with Livewire 4 + Alpine.js 3; aligned with v2.3.0 architecture
- **Version**: 2.2.0; **Date**: 2026-01-28; **Author**: Development Team; **Changes**: Aligned with codebase v2.2.0
- **Version**: 2.1.0; **Date**: 2026-01-23; **Author**: Development Team; **Changes**: Updated scope and requirements to match implementation; added AI integration requirements; added external API integration; aligned with v2.0 architecture
- **Version**: 1.0; **Date**: 2026-01-14; **Author**: Development Team; **Changes**: Initial draft

---

### This BRS describes the current business scope as implemented in version 2.4.0
