# Master Glossary

## Umamusume Pretty Derby Career Planner

**Document Version**: 3.4.0
**Date**: February 22, 2026
**Project**: UmamusumeCareerPlanner
**Author**: Development Team
**Status**: Current – Aligned with v2.2.0 and Global English Server Mechanics (February 2026)

---

## Table of Contents

1. [Purpose](#1-purpose)
2. [Game Terminology](#2-game-terminology)
3. [Application Terminology](#3-application-terminology)
4. [Technical Terminology](#4-technical-terminology)
5. [AI and Integration Terminology](#5-ai-and-integration-terminology)
6. [Data Management Terminology](#6-data-management-terminology)
7. [Status and Indicator Terminology](#7-status-and-indicator-terminology)
8. [Acronyms and Abbreviations](#8-acronyms-and-abbreviations)
9. [Document Control](#9-document-control)

---

## 1. Purpose

This glossary defines all core terminology used in the Umamusume Pretty Derby Career Planner project, aligned with the v2.2.0 codebase (30 models, 8 enums, 70+ services, 571 routes, 3,316+ tests) and the **Global English server** gameplay and translations (as of February 2026). Use this glossary as the authoritative reference for all documentation, code, and user interface.

---

## 2. Game Terminology

### 2.1 Core Stats

- **Stat**: Speed; **Japanese**: スピード; **Definition**: Maximum running speed; determines overall race velocity.; **Range**: 0-1200; **Priority**: ★★★★★
- **Stat**: Stamina; **Japanese**: スタミナ; **Definition**: HP & effective stamina; enables staying power over longer distances and through race events.; **Range**: 0-1200; **Priority**: ★★★★
- **Stat**: Power; **Japanese**: パワー; **Definition**: Acceleration and ability to navigate around opponents, especially during race transitions and late bursts.; **Range**: 0-1200; **Priority**: ★★★
- **Stat**: Guts; **Japanese**: 根性; **Definition**: Affects race **position holding**, resistance to position loss in navigation battles; contributes to final sprint and recovers stamina under force-out conditions (not just "last spurt strength").; **Range**: 0-1200; **Priority**: ★★
- **Stat**: Wit; **Japanese**: 賢さ; **Definition**: Influences **skill activation rate**, likelihood to **avoid "kakari" status** (stamina penalty), and general race event triggers; good Wit is more important than pure stat for race stability.; **Range**: 0-1200; **Priority**: ★★★

### Stat Grade Scale

- **Grade**: SS; **Value Range**: 1100-1200; **Effectiveness**: Elite tier
- **Grade**: S; **Value Range**: 950-1099; **Effectiveness**: Excellent
- **Grade**: A; **Value Range**: 850-949; **Effectiveness**: Good
- **Grade**: B+; **Value Range**: 750-849; **Effectiveness**: Above average
- **Grade**: B; **Value Range**: 650-749; **Effectiveness**: Average
- **Grade**: C+; **Value Range**: 550-649; **Effectiveness**: Below average
- **Grade**: C; **Value Range**: 450-549; **Effectiveness**: Poor
- **Grade**: D+; **Value Range**: 350-449; **Effectiveness**: Very poor
- **Grade**: D; **Value Range**: 250-349; **Effectiveness**: Minimal
- **Grade**: E; **Value Range**: 150-249; **Effectiveness**: Negligible
- **Grade**: F; **Value Range**: 0-149; **Effectiveness**: None

> **Note:** Wit (Wisdom/Intelligence) not only increases skill activation chance, it directly affects the chance to avoid the "kakari" mishap (which increases stamina drain and prevents skill use during it).

### 2.2 Aptitudes

- **Term**: Aptitude; **Japanese**: 適性; **In-Game Label (EN)**: Aptitude; **Description**: Character compatibility for **distance, surface, and running style**.; **Categories**: Distance, Surface, Style
- **Term**: Distance Aptitude; **Japanese**: 距離適性; **In-Game Label (EN)**: Distance; **Description**: Preferred race lengths: Sprint, Mile, Medium, Long as per JP/EN global.; **Categories**: Sprint: <1400m, Mile: 1401-1800m, Medium: 1801-2400m, Long: 2401m+
- **Term**: Surface Aptitude; **Japanese**: バ場適性; **In-Game Label (EN)**: Surface; **Description**: Preferred ground: Turf or Dirt; **Categories**: Turf, Dirt
- **Term**: Running Style Aptitude; **Japanese**: 脚質適性; **In-Game Label (EN)**: Style; **Description**: Preferred position in races (see below); **Categories**: Front, Pace, Late, End

### Clarification

Lower Aptitude doesn't "cap" stats, but **significantly reduces race performance and placement** when mismatched. Optimal aptitude is strongly recommended.

### Aptitude Ratings & Effectiveness

- **Rating**: S; **Japanese**: S; **Effectiveness**: 105-110%; **Description**: Maximum compatibility (S is the highest grade)
- **Rating**: A; **Japanese**: A; **Effectiveness**: 100%; **Description**: Good compatibility (baseline)
- **Rating**: B; **Japanese**: B; **Effectiveness**: 90%; **Description**: Adequate compatibility
- **Rating**: C; **Japanese**: C; **Effectiveness**: 80%; **Description**: Below average
- **Rating**: D; **Japanese**: D; **Effectiveness**: 70%; **Description**: Poor compatibility
- **Rating**: E; **Japanese**: E; **Effectiveness**: 60%; **Description**: Very poor
- **Rating**: F; **Japanese**: F; **Effectiveness**: 50%; **Description**: Minimal compatibility
- **Rating**: G; **Japanese**: G; **Effectiveness**: 40%; **Description**: Incompatible

> **Note:** S is the maximum aptitude grade. SS does NOT exist in the game. Only S-rank provides positive bonuses; A-rank is the baseline with no bonus/penalty.
> *Race mechanics exclusive to the Japanese server, like Charge Up, Compete Before Spurt, or Stamina Limit Break, are **not implemented** in the Global English server as of February 2026.*

### 2.3 Running Styles

- **Term (JP)**: 逃げ; **Label (EN)**: Front; **Standard Community Naming**: "Front"; **In-Game Behaviour**: Lead early, stay in front
- **Term (JP)**: 先行; **Label (EN)**: Pace; **Standard Community Naming**: "Pace"; **In-Game Behaviour**: Stay near leaders, chase from front
- **Term (JP)**: 差し; **Label (EN)**: Late; **Standard Community Naming**: "Late"; **In-Game Behaviour**: Travel mid-pack, surge near end
- **Term (JP)**: 追込; **Label (EN)**: End; **Standard Community Naming**: "End"; **In-Game Behaviour**: Linger far back, sprint at finish

> **Note:** "Runaway" is a rare passive, not a base style.

### 2.4 Race Categories

- **Term**: Race Grade; **Definition**: G1/G2/G3/Open/Pre-Open/Maiden (same as JP server for Global)
- **Term**: Distance; **Definition**: Sprint: <1400m, Mile: 1401-1800m, Medium: 1801-2400m, Long: 2401+
- **Term**: Track Surface; **Definition**: Turf (grass) or Dirt
- **Term**: Race Condition; **Definition**: Real-time weather/track status, affecting mood and performance

### 2.5 Career Stages

- **Stage**: Junior Year; **Japanese**: ジュニア級; **Turn Range**: 1–24; **Description**: Basic training foundation
- **Stage**: Classic Year; **Japanese**: クラシック級; **Turn Range**: 25–48; **Description**: Competitive growth and new races
- **Stage**: Senior Year; **Japanese**: シニア級; **Turn Range**: 49–72; **Description**: Peak and high-level racing
- **Stage**: URA Finals; **Japanese**: URAファイナルズ; **Turn Range**: 73–78; **Description**: Finals series

### 2.6 Skill System

- **Term**: Skill Point (SP); **Japanese**: スキルポイント; **Global Mechanic (EN)**: Earned via races/events, spent to purchase skills
- **Term**: Skill Hint; **Japanese**: ヒント; **Global Mechanic (EN)**: Discount for skill SP cost; **5 hint levels with progressive discounts: Level 1 = 10%, Level 2 = 20%, Level 3 = 30%, Level 4 = 35%, Level 5 = 40% (maximum)**; applies when buying the skill
- **Term**: Skill Evolution; **Japanese**: 進化; **Global Mechanic (EN)**: Upgrade of some (not all) Normal → Rare skills under set conditions
- **Term**: Skill Rarity; **Japanese**: レアリティ; **Global Mechanic (EN)**: Normal (white), Rare (gold), Unique (rainbow)

### Skill Types (Community)

- Speed, Stamina, Power, Recovery, Unique, "Effect on Opponent" (often referred to as "debuffs" in guides, but not in-game as a formal type)

> **Note:** The game lacks a formal "Debuff" skill category; many skills can apply negative effects to rivals.
> Skill hints only affect the initial SP purchase price, not ongoing effects.

### 2.7 Training Actions

### Available at each turn (Career Mode)

- **Train**: Increase specific stat (Speed/Stamina/Power/Guts/Wit)
- **Race**: Compete to earn fans and SP, progress story
- **Rest**: Recover energy (restores a variable % but consumes a turn)
- **Recreation**: (a.k.a. "Going Out") May improve mood, sometimes restore some stats or trigger unique events

### 2.8 Mood System

- **Mood**: Great; **Japanese**: 絶好調; **Effect**: +4% stat gains, best event/mood chance, reduces failure rate
- **Mood**: Good; **Japanese**: 好調; **Effect**: +2% stat gains, good events more likely
- **Mood**: Normal; **Japanese**: 普通; **Effect**: Baseline effect
- **Mood**: Bad; **Japanese**: 不調; **Effect**: -2% stat gains, more failures, worse event odds
- **Mood**: Awful; **Japanese**: 絶不調; **Effect**: -4% stat gains, highest failure rate, bad events more likely

> **Note:** Better mood increases both training gains and chance for favorable events; also impacts likelihood of positive results under some race events.

### 2.9 Legacy and Bonds

- **Legacy Effect**: Bonuses granted to new trainees when selecting two legacy Uma Musume in setup (after retiring a career run). These grant bonus stats and sometimes skills to new runs.
- **Bond/Bonding**: Represents support card "friendship" (bond) level; higher bond unlocks improved training bonuses and special events. Distinct from raw support card stats.
- **Inspiration Event**: One of three inheritance checkpoints in a career run: career start, Year 2 late March, and Year 3 late March. Parent sparks are applied at these points.
- **Spark**: Inheritance bonus category. Blue = stat bonus, Pink = skill inheritance, Green = growth-rate bonus, White = SP bonus.
- **Friendship Training**: In this application's planner logic, the boosted friendship state becomes active when 3 or more support cards simultaneously reach Bond 80 or higher.

### Bond Milestones

- **Level**: 0-79; **Reward**: Standard support-card bonuses and bond-building events
- **Level**: 80+; **Reward**: Card becomes rainbow-ready for friendship checks
- **Planner Threshold**: 3 cards at 80+; **Reward**: Friendship Training status becomes active in training predictions and AI advice

### 2.10 Conditions

- **Term**: Condition; **Definition**: Temporary status effect (positive or negative) affecting stats or training
- **Term**: Positive Condition; **Definition**: Beneficial effect (e.g., "Focused", "Energized")
- **Term**: Negative Condition; **Definition**: Detrimental effect (e.g., "Fatigued", "Injured")
- **Term**: Kakari; **Definition**: Negative condition that increases stamina consumption and prevents skill activation; higher Wit reduces chance of this occurring

---

## 3. Application Terminology

### 3.1 Core Entities

- **Term**: Character; **Canonical Field**: `ucp_characters`; **Definition**: Uma Musume trainee (player-controlled); **Storage**: Database
- **Term**: Career Run / Plan; **Canonical Field**: `ucp_careers`; **Definition**: A single "career mode" progression; **Storage**: DB/localStore
- **Term**: Turn; **Canonical Field**: `turn_number`; **Definition**: One half-month career action window (Early or Late); 72 turns span a full 3-year career; **Storage**: DB/localStore
- **Term**: Support Deck; **Canonical Field**: `ucp_support_decks`; **Definition**: Set of 6 support cards for training; **Storage**: Database
- **Term**: Support Card Definition; **Canonical Field**: `ucp_support_card_definitions`; **Definition**: Canonical support card definition from external sources; **Storage**: Database
- **Term**: Skill Build; **Canonical Field**: `ucp_skill_builds`; **Definition**: Saved skill loadout configuration for a career; **Storage**: Database
- **Term**: Run Snapshot; **Canonical Field**: `ucp_run_snapshots`; **Definition**: Point-in-time career state snapshot for undo/restore; **Storage**: Database
- **Term**: Advisory Recommendation; **Canonical Field**: `ucp_advisory_recommendations`; **Definition**: AI-generated training/race/skill recommendation; **Storage**: Database
- **Term**: Critical Alert; **Canonical Field**: `ucp_critical_alerts`; **Definition**: System-generated alert for critical career situations; **Storage**: Database
- **Term**: Prediction Accuracy; **Canonical Field**: `ucp_prediction_accuracy`; **Definition**: Tracking of AI prediction vs actual outcome; **Storage**: Database
- **Term**: Legacy; **Canonical Field**: `legacy_*`; **Definition**: Data from completed runs used to boost new trainees; **Storage**: Database

### See also: Factor, Bond, Skill Hint under Game Terminology

### 3.2 Data Tracking

- **Term**: Stat Progress; **Definition**: Historical record of stat values per turn (`ucp_training_sessions`)
- **Term**: Training Session; **Definition**: Record of a training action with gains and outcomes
- **Term**: Race Result; **Definition**: Record of race participation and placement
- **Term**: Skill Acquisition; **Definition**: Record of when and how a skill was obtained
- **Term**: Goal; **Definition**: User-defined target (stat threshold, race win, skill count)
- **Term**: Bond Progress; **Definition**: Friendship level progression with support card characters

### 3.3 User Interface

- **Term**: Dashboard; **Definition**: Main overview page with stats, goals, and quick actions
- **Term**: Wizard; **Definition**: Multi-step guided interface (e.g., Character Creation Wizard)
- **Term**: Preview; **Definition**: Read-only view of data before confirmation
- **Term**: Toast; **Definition**: Temporary notification message
- **Term**: Modal; **Definition**: Overlay dialog for focused interactions
- **Term**: Advisory Panel; **Definition**: Livewire component (`AdvisoryPanel`) providing real-time AI recommendations
- **Term**: Breadcrumb; **Definition**: Hierarchical navigation trail shown at the top of pages
- **Term**: Radar Chart; **Definition**: Chart.js radar visualization for character stat display

### 3.4 Admin Panel

- **Term**: Admin Panel; **Definition**: Protected administrative interface for system management (`Admin/` controllers)
- **Term**: Database Maintenance; **Definition**: Admin tool for database optimization and cleanup (`DatabaseMaintenanceService`)
- **Term**: Log Reader; **Definition**: Admin tool for viewing application logs (`LogReaderService`)
- **Term**: System Health; **Definition**: Admin dashboard showing system status (`SystemHealthService`)
- **Term**: Queue Monitor; **Definition**: Admin interface for monitoring background job processing (via Horizon)
- **Term**: User Management; **Definition**: Admin interface for managing user accounts and roles
- **Term**: System Settings; **Definition**: Admin interface for application-wide configuration

---

## 4. Technical Terminology

### 4.1 Architecture Components

- **Term**: Eloquent Model; **Abbreviation**: -; **Definition**: Laravel ORM model representing database table (30 models in system)
- **Term**: Livewire Component; **Abbreviation**: -; **Definition**: Server-driven reactive UI component (e.g., `AdvisoryPanel`)
- **Term**: Service Layer; **Abbreviation**: -; **Definition**: Business logic abstraction (70+ services in `app/Services/`)
- **Term**: Form Request; **Abbreviation**: -; **Definition**: Laravel validation class for HTTP requests
- **Term**: Repository; **Abbreviation**: -; **Definition**: Data access pattern abstracting database queries (e.g., `CharacterRepositoryInterface`, `EloquentCharacterRepository`)
- **Term**: Enum; **Abbreviation**: -; **Definition**: PHP 8.1+ enumeration for type-safe constants (8 enums: `AlertType`, `CareerPhase`, `Mood`, `Priority`, `RaceDistance`, `RecommendationType`, `RunningStyle`, `StorageMode`)
- **Term**: Value Object; **Abbreviation**: -; **Definition**: Immutable object representing a domain concept (in `app/ValueObjects/`)
- **Term**: Collection; **Abbreviation**: -; **Definition**: Custom Laravel collection class (in `app/Collections/`)
- **Term**: Event/Listener; **Abbreviation**: -; **Definition**: Laravel event system for decoupled processing (in `app/Events/`, `app/Listeners/`)
- **Term**: Job; **Abbreviation**: -; **Definition**: Queued background task (in `app/Jobs/`)
- **Term**: Policy; **Abbreviation**: -; **Definition**: Laravel authorization policy (in `app/Policies/`)
- **Term**: Notification; **Abbreviation**: -; **Definition**: Laravel notification class (in `app/Notifications/`)

### 4.2 Technology Stack

- **Technology**: Laravel; **Version**: 12+; **Layer**: Backend Framework; **Purpose**: Application foundation
- **Technology**: PHP; **Version**: ^8.2 (runtime 8.4.11); **Layer**: Runtime; **Purpose**: Server-side execution
- **Technology**: Livewire; **Version**: 4; **Layer**: Frontend Reactivity; **Purpose**: Dynamic UI without JavaScript
- **Technology**: Alpine.js; **Version**: 3; **Layer**: Client Interactivity; **Purpose**: Lightweight JS framework
- **Technology**: TailwindCSS; **Version**: v4; **Layer**: Styling; **Purpose**: Utility-first CSS framework
- **Technology**: Vite; **Version**: 7; **Layer**: Build Tool; **Purpose**: Asset compilation and bundling
- **Technology**: Chart.js; **Version**: 4; **Layer**: Charting; **Purpose**: Data visualization
- **Technology**: MySQL; **Version**: 8.0+; **Layer**: Database; **Purpose**: Primary data store (production)
- **Technology**: SQLite; **Version**: Latest; **Layer**: Database; **Purpose**: Development/testing database
- **Technology**: Redis; **Version**: 7+ (via WSL); **Layer**: Cache/Queue; **Purpose**: Caching and background jobs
- **Technology**: Pest; **Version**: v4; **Layer**: Testing; **Purpose**: PHP testing framework
- **Technology**: PHPUnit; **Version**: v12; **Layer**: Testing; **Purpose**: Testing engine (underlying)
- **Technology**: pest-plugin-browser; **Version**: 4.0; **Layer**: Browser Testing; **Purpose**: Browser-based test automation
- **Technology**: Playwright; **Version**: 1.58; **Layer**: E2E Testing; **Purpose**: End-to-end browser testing
- **Technology**: Larastan; **Version**: v3; **Layer**: Code Quality; **Purpose**: Static analysis for Laravel
- **Technology**: Laravel Pint; **Version**: v1; **Layer**: Code Formatting; **Purpose**: PSR-12 code style fixer
- **Technology**: Neuron AI; **Version**: v2.11; **Layer**: AI Framework; **Purpose**: AI agent orchestration
- **Technology**: neuron-laravel; **Version**: v0.3.4; **Layer**: AI Integration; **Purpose**: Laravel integration for Neuron
- **Technology**: Laravel Sanctum; **Version**: v4; **Layer**: Auth; **Purpose**: API token authentication
- **Technology**: Laravel Horizon; **Version**: v5; **Layer**: Queue Monitoring; **Purpose**: Redis queue dashboard
- **Technology**: Laravel Telescope; **Version**: Latest; **Layer**: Debugging; **Purpose**: Request/job/query monitoring
- **Technology**: Laravel Boost; **Version**: v1.8; **Layer**: Dev Tooling; **Purpose**: MCP development server

### 4.3 Database Conventions

- **Convention**: Table Prefix; **Pattern**: `ucp_`; **Example**: `ucp_characters`, `ucp_careers`
- **Convention**: Primary Key; **Pattern**: `id`; **Example**: Integer auto-increment
- **Convention**: UUID Field; **Pattern**: `uuid`; **Example**: For Local storage mode
- **Convention**: Foreign Key; **Pattern**: `{table}_id`; **Example**: `character_id`, `user_id`
- **Convention**: Timestamps; **Pattern**: `created_at`, `updated_at`; **Example**: Laravel standard
- **Convention**: Soft Deletes; **Pattern**: `deleted_at`; **Example**: For recoverable deletions

### 4.4 Validation Rules

- **Rule Type**: Stat Range; **Example**: 0-1200; **Description**: Hard max, no values above 1200 allowed
- **Rule Type**: Turn Range; **Example**: 1-78; **Description**: Valid turn numbers
- **Rule Type**: Energy Range; **Example**: 0-100; **Description**: Energy level percentage
- **Rule Type**: Deck Size; **Example**: 6 cards; **Description**: Exactly 6 cards (5 owned + 1 borrowed)
- **Rule Type**: Hint Level; **Example**: 0-5; **Description**: Maximum 5 hints per skill (40% discount at level 5)

---

## 5. AI and Integration Terminology

### 5.1 AI System

- **Term**: AI Provider; **Abbreviation**: -; **Definition**: Backend service for AI inference (Ollama, AWS Bedrock)
- **Term**: Neuron AI; **Abbreviation**: -; **Definition**: AI agent orchestration framework (v2.11 with neuron-laravel v0.3.4)
- **Term**: AI Agent; **Abbreviation**: -; **Definition**: Specialized AI for specific tasks (Training Advisor, Race Strategy)
- **Term**: AI Conversation; **Abbreviation**: -; **Definition**: Persisted chat session (`ucp_ai_conversations`)
- **Term**: AI Cost; **Abbreviation**: -; **Definition**: Per-token usage cost tracking (`ucp_ai_costs`)
- **Term**: AI Metrics; **Abbreviation**: -; **Definition**: Performance and usage analytics (`ucp_ai_metrics`)
- **Term**: Hybrid AI; **Abbreviation**: -; **Definition**: Architecture using local (Ollama) + cloud (Bedrock) fallback

### AI Providers

- **Provider**: Ollama; **Type**: Local; **Models**: llama3.2, mistral; **Use Case**: Primary, free inference
- **Provider**: AWS Bedrock; **Type**: Cloud; **Models**: Claude 4.5 Sonnet; **Use Case**: Complex queries, fallback

### 5.2 Neuron Agents

Neuron agents live in `app/Neuron/Agents/` with response types in `app/Neuron/Responses/` and support classes in `app/Neuron/Support/`. Service layer wrappers are in `app/Services/Neuron/`.

- **Agent**: Base Agent; **Class**: `BaseAgent`; **Purpose**: Abstract base class for all Neuron agents; **Response Type**: -
- **Agent**: Training Advisor Agent; **Class**: `TrainingAdvisorAgent`; **Purpose**: Recommends optimal training selections; **Response Type**: `TrainingAdviceResponse`
- **Agent**: Race Strategy Agent; **Class**: `RaceStrategyAgent`; **Purpose**: Analyzes race requirements and strategy; **Response Type**: `RaceStrategyResponse`
- **Agent**: Skill Recommendation Agent; **Class**: `SkillRecommendationAgent`; **Purpose**: Suggests skill acquisition priorities; **Response Type**: `SkillRecommendationResponse`
- **Agent**: Career Planning Agent; **Class**: `CareerPlanningAgent`; **Purpose**: Provides long-term strategic guidance; **Response Type**: `CareerPlanningResponse`
- **Agent**: MCP Demo Agent; **Class**: `McpDemoAgent`; **Purpose**: Demonstration agent for MCP tool integration; **Response Type**: -

### Neuron Support Classes

- **Class**: `McpConnectorFactory`; **Purpose**: Creates MCP connector instances for agent tool access
- **Class**: `McpToolIntegration`; **Purpose**: Integrates MCP tools with Neuron agent capabilities

### Neuron Service Layer (`app/Services/Neuron/`)

- **Service**: `NeuronAIService`; **Purpose**: Core Neuron AI orchestration and provider management
- **Service**: `TrainingAdvisorService`; **Purpose**: Wrapper for training advisor agent interactions
- **Service**: `RaceStrategyService`; **Purpose**: Wrapper for race strategy agent interactions
- **Service**: `SkillRecommendationService`; **Purpose**: Wrapper for skill recommendation agent interactions
- **Service**: `CareerPlanningService`; **Purpose**: Wrapper for career planning agent interactions

### 5.3 MCP (Model Context Protocol)

- **Term**: MCP; **Abbreviation**: Model Context Protocol; **Definition**: Standard for AI tool/agent integration
- **Term**: MCP Server; **Abbreviation**: -; **Definition**: Tool provider service (Memory, Filesystem, Fetch)
- **Term**: MCP Agent; **Abbreviation**: -; **Definition**: AI agent configuration (`ucp_mcp_agents`)
- **Term**: MCP Tool; **Abbreviation**: -; **Definition**: Executable function exposed to AI agents
- **Term**: MCP Tool Usage; **Abbreviation**: -; **Definition**: Tracking of tool invocations (`ucp_mcp_tool_usage`)
- **Term**: MCP Health; **Abbreviation**: -; **Definition**: Server availability monitoring (`ucp_mcp_server_health`)
- **Term**: Laravel MCP; **Abbreviation**: -; **Definition**: Official Laravel MCP package (`laravel/mcp v0`) for server-side tool exposure
- **Term**: MCP Monitoring; **Abbreviation**: -; **Definition**: Service for tracking MCP server health, tool usage, and agent performance

### MCP Server Types

- **Server**: Memory; **Type**: Local; **Purpose**: Conversation context persistence
- **Server**: Filesystem; **Type**: Local; **Purpose**: Document and file access
- **Server**: Fetch; **Type**: Local; **Purpose**: HTTP resource retrieval
- **Server**: Custom; **Type**: Remote; **Purpose**: Domain-specific tools (optional)

### 5.4 External APIs

- **Term**: External Data; **Definition**: Cached records from external APIs (`ucp_external_api_cache`)
- **Term**: Circuit Breaker; **Definition**: Resilience pattern preventing cascading failures
- **Term**: Fallback API; **Definition**: Secondary API used when primary fails
- **Term**: Cache TTL; **Definition**: Time-to-live for cached API responses (24 hours)

### External API Sources

- **API**: umapyoi.net; **Purpose**: Primary game data (characters, support cards, news); **Client Class**: `UmapyoiApiClient`; **Status**: Active
- **API**: UmamusumeDB.com; **Purpose**: Fallback data (skills, races); **Client Class**: `UmamusumeDBApiClient`; **Status**: Active
- **API**: GameTora; **Purpose**: Supplementary data via web scraping; **Client Class**: `GameToraScraperService`; **Status**: Active

### 5.5 OCR System

- **Term**: OCR; **Abbreviation**: Optical Character Recognition; **Definition**: Technology for extracting text from images
- **Term**: OCR Extraction; **Abbreviation**: -; **Definition**: Result of OCR processing (`ucp_ocr_extractions`)
- **Term**: GD Library; **Abbreviation**: GD; **Definition**: PHP image processing library for preprocessing
- **Term**: Tesseract; **Abbreviation**: -; **Definition**: Open-source OCR engine
- **Term**: Confidence Score; **Abbreviation**: -; **Definition**: OCR accuracy metric (0-100%)
- **Term**: Preprocessing; **Abbreviation**: -; **Definition**: Image enhancement before OCR (resize, grayscale, threshold)

### OCR Data Types

- **Data Type**: Character Stats; **Detection Pattern**: Stat labels + numeric values; **Confidence Threshold**: 85%
- **Data Type**: Skill Names; **Detection Pattern**: Japanese/English text regions; **Confidence Threshold**: 80%
- **Data Type**: Race Results; **Detection Pattern**: Placement + time format; **Confidence Threshold**: 90%
- **Data Type**: Support Cards; **Detection Pattern**: Card frame detection; **Confidence Threshold**: 75%

---

## 6. Data Management Terminology

### 6.1 Storage Modes

- **Term**: Local Mode; **Definition**: Browser localStorage-based storage; **Identifier**: UUID; **Offline Support**: Full
- **Term**: Account Mode; **Definition**: Database-backed cloud storage; **Identifier**: Integer ID; **Offline Support**: Requires connectivity for save
- **Term**: Storage Badge; **Definition**: Visual indicator of current mode; **Identifier**: Icon + label; **Offline Support**: N/A
- **Term**: StorageMode Enum; **Definition**: PHP enum (`App\Enums\StorageMode`) for type-safe storage mode handling; **Identifier**: `Local` / `Account` values; **Offline Support**: N/A

### 6.2 Import/Export

- **Term**: Backup; **Definition**: Exportable archive of user data via `BackupService`
- **Term**: Data Import; **Definition**: Process of loading external data into the system
- **Term**: Data Export; **Definition**: Process of extracting data for external use
- **Term**: Data Migration; **Definition**: Conversion between storage formats or schema versions
- **Term**: Format Detection; **Definition**: Automatic identification of import file structure
- **Term**: Schema Version; **Definition**: Version identifier in exported data for compatibility

### Export Formats

- **Format**: JSON; **Extension**: .json; **Use Case**: Full data backup, cross-app import
- **Format**: Excel; **Extension**: .xlsx; **Use Case**: Spreadsheet analysis, sharing
- **Format**: CSV; **Extension**: .csv; **Use Case**: Data processing, simple imports
- **Format**: Markdown; **Extension**: .md; **Use Case**: Documentation, human-readable exports

### 6.3 Migration and Validation

- **Term**: Legacy Format; **Definition**: Data structure from previous application versions
- **Term**: Field Mapping; **Definition**: Translation between legacy and canonical field names
- **Term**: Conflict Resolution; **Definition**: Strategy for handling duplicate records (skip, overwrite, merge, rename)
- **Term**: Validation Layer; **Definition**: Three-tier validation (schema, business rules, integrity)
- **Term**: Rollback; **Definition**: Reverting to previous state after failed migration

---

## 7. Status and Indicator Terminology

### 7.1 Career Run Status

- **Status**: In Progress; **Icon**: 🟢; **Definition**: Career is actively being played
- **Status**: Completed; **Icon**: ✅; **Definition**: Career has finished (URA Finals complete)
- **Status**: Archived; **Icon**: 📦; **Definition**: Career is saved but hidden from active view
- **Status**: Abandoned; **Icon**: ❌; **Definition**: Career was discontinued

### 7.2 Skill Status

- **Status**: Acquired; **Icon**: ✅; **Definition**: Skill purchased and owned
- **Status**: Skipped; **Icon**: ❌; **Definition**: Deliberately not acquired
- **Status**: Suggested; **Icon**: 💭; **Definition**: Recommended by AI or planning
- **Status**: Planned; **Icon**: 📋; **Definition**: Marked for future acquisition

### 7.3 Storage Mode Indicators

- **Indicator**: Local Mode; **Icon**: 🟠; **Color**: Orange; **Description**: Data stored in browser only
- **Indicator**: Account Mode; **Icon**: 🟣; **Color**: Purple; **Description**: Data synced to cloud
- **Indicator**: Unsaved Changes; **Icon**: ⚠️; **Color**: Amber; **Description**: Draft exists, not yet saved
- **Indicator**: Draft Auto-Saved; **Icon**: 💾; **Color**: Gray; **Description**: Draft saved to localStorage

### 7.4 Risk Indicators

- **Risk Level**: Low Risk; **Icon**: 🟢; **Color**: Green; **Percentage**: < 15%; **Description**: Safe to proceed
- **Risk Level**: Moderate Risk; **Icon**: 🟡; **Color**: Amber; **Percentage**: 15-40%; **Description**: Caution advised
- **Risk Level**: High Risk; **Icon**: 🔴; **Color**: Red; **Percentage**: > 40%; **Description**: Risky action

### 7.5 Goal Status

- **Status**: Completed; **Icon**: ✅; **Definition**: Goal achieved
- **Status**: On Track; **Icon**: 🟡; **Definition**: Progress within expected range
- **Status**: At Risk; **Icon**: 🔴; **Definition**: Behind schedule, intervention needed
- **Status**: Failed; **Icon**: ❌; **Definition**: Goal not met within timeframe

---

## 8. Acronyms and Abbreviations

### 8.1 General Acronyms

- **Acronym**: AI; **Full Term**: Artificial Intelligence; **Context**: AI advisory system
- **Acronym**: API; **Full Term**: Application Programming Interface; **Context**: External data sources
- **Acronym**: APM; **Full Term**: Application Performance Monitoring; **Context**: Performance dashboards
- **Acronym**: CRUD; **Full Term**: Create, Read, Update, Delete; **Context**: Database operations
- **Acronym**: CSV; **Full Term**: Comma-Separated Values; **Context**: Data export format
- **Acronym**: JSON; **Full Term**: JavaScript Object Notation; **Context**: Data interchange format
- **Acronym**: MCP; **Full Term**: Model Context Protocol; **Context**: AI tool integration
- **Acronym**: OCR; **Full Term**: Optical Character Recognition; **Context**: Screenshot data extraction
- **Acronym**: ORM; **Full Term**: Object-Relational Mapping; **Context**: Eloquent database abstraction
- **Acronym**: PWA; **Full Term**: Progressive Web App; **Context**: Offline-capable web application
- **Acronym**: SP; **Full Term**: Skill Point; **Context**: In-game currency for skills
- **Acronym**: TTL; **Full Term**: Time To Live; **Context**: Cache expiration duration
- **Acronym**: UI; **Full Term**: User Interface; **Context**: Visual application layer
- **Acronym**: UUID; **Full Term**: Universally Unique Identifier; **Context**: Local mode identifier
- **Acronym**: UX; **Full Term**: User Experience; **Context**: User interaction design

### 8.2 Technical Acronyms

- **Acronym**: CSS; **Full Term**: Cascading Style Sheets; **Context**: Styling technology
- **Acronym**: DB; **Full Term**: Database; **Context**: Data persistence layer
- **Acronym**: ERD; **Full Term**: Entity Relationship Diagram; **Context**: Database schema visualization
- **Acronym**: FK; **Full Term**: Foreign Key; **Context**: Database relationship
- **Acronym**: HTTP; **Full Term**: Hypertext Transfer Protocol; **Context**: Web communication
- **Acronym**: JS; **Full Term**: JavaScript; **Context**: Client-side programming
- **Acronym**: MIME; **Full Term**: Multipurpose Internet Mail Extensions; **Context**: File type identification
- **Acronym**: PK; **Full Term**: Primary Key; **Context**: Database unique identifier
- **Acronym**: PSR; **Full Term**: PHP Standards Recommendation; **Context**: Coding standards
- **Acronym**: SRS; **Full Term**: Software Requirements Specifications; **Context**: Requirements document
- **Acronym**: SDS; **Full Term**: Software Design Specifications; **Context**: Design document
- **Acronym**: UCP; **Full Term**: Umamusume Career Planner; **Context**: Application prefix
- **Acronym**: WCAG; **Full Term**: Web Content Accessibility Guidelines; **Context**: Accessibility standards

### 8.3 Game-Specific Acronyms

- **Acronym**: URA; **Full Term**: Uma Musume Racing Association; **Japanese**: ウマ娘競走協会; **Context**: Final championship series
- **Acronym**: G1, G2, G3; **Full Term**: Grade 1, 2, 3; **Japanese**: -; **Context**: Race classification tiers
- **Acronym**: HP; **Full Term**: Hit Points; **Japanese**: -; **Context**: Stamina/health in-game
- **Acronym**: LB; **Full Term**: Limit Break; **Japanese**: 凸; **Context**: Support card upgrade level

---

## 9. Document Control

### 9.1 Terminology Standards

- **Standard**: **Canonical Field Names**; **Description**: Official database column names take precedence over UI labels
- **Standard**: **Consistency**; **Description**: Same term used consistently across docs, code, and UI
- **Standard**: **Case Sensitivity**; **Description**: Follow codebase conventions (camelCase for code, Title Case for UI)
- **Standard**: **Deprecation**; **Description**: Deprecated terms marked explicitly with replacement

### 9.2 Version History

- **Version**: 3.4.0; **Date**: 2026-02-22; **Author**: Development Team; **Changes**: Updated to February 22, 2026; expanded core entities table with 6 new model references (SupportCardDefinition, SkillBuild, RunSnapshot, AdvisoryRecommendation, CriticalAlert, PredictionAccuracy); expanded architecture components with Repository, Enum, ValueObject, Collection, Event/Listener, Job, Policy, Notification entries; updated Neuron Agents section with actual codebase classes (BaseAgent, McpDemoAgent, response types, support classes, service layer); added GameTora to external API sources; added StorageMode enum reference; added Laravel MCP and MCP Monitoring terms; updated codebase stats (30 models, 8 enums, 70+ services, 571 routes, 3,316+ tests)
- **Version**: 3.3.0; **Date**: 2026-02-21; **Author**: Development Team; **Changes**: Updated to February 2026; updated technology stack (Livewire 4, Pest v4, PHPUnit v12, Neuron AI v2.11, Chart.js 4, Larastan v3, Laravel Pint v1, Sanctum v4, Horizon v5, Telescope, Boost v1.8, pest-plugin-browser 4.0, Playwright 1.58); updated PHP runtime to 8.4.11; updated AI provider models; removed MariaDB reference
- **Version**: 3.2.0; **Date**: 2026-01-28; **Author**: Development Team; **Changes**: Updated to v2.2.0; corrected aptitude grades (S is maximum, SS does NOT exist); updated hint system to 5 levels (10%/20%/30%/35%/40%); aligned with game-accurate mechanics from Global English Server
- **Version**: 3.1.0; **Date**: 2026-01-23; **Author**: Development Team (with user corrections & source references); **Changes**: Updated per Global (English) server mechanics; clarified Guts/Wit, updated running style English labels, added Training, Legacy, and Bond definitions, and explicitly excluded JP-version-only features per community and official docs
- **Version**: 3.0.0; **Date**: 2026-01-23; **Author**: Development Team; **Changes**: Comprehensive expansion aligned with v2.0.0; added AI, MCP, OCR, and external integration terminology; restructured into logical categories; added tables for visual clarity
- **Version**: 2.1; **Date**: 2026-01-23; **Author**: Development Team; **Changes**: Updated terms to match current codebase and configs
- **Version**: 2.0; **Date**: 2026-01-12; **Author**: Development Team; **Changes**: Consolidated glossary
- **Version**: 1.0; **Date**: 2026-01-03; **Author**: Development Team; **Changes**: Initial glossary

### 9.3 Related Documents

- [000_DOCUMENT_INDEX](000_DOCUMENT_INDEX.md) - Documentation navigation
- [003_SRS](003_SRS_Software_Requirement_Specifications.md) - Requirements specifications
- [009_DBD](009_DBD_Database_Documentation.md) - Database schema reference
- [010_SCD](010_SCD_Source_Code_Documentation.md) - Code structure reference

### 9.4 Related Sources

- [Umamusume: Pretty Derby (mobile game) - Umamusume Wiki](https://umamusu.wiki/Umamusume%3A_Pretty_Derby_%28mobile_game%29)
- [Stats Guide (Game8)](https://game8.co/games/Umamusume-Pretty-Derby/archives/535820)
- [Frontline Gaming Japan](https://www.frontlinejp.net/2024/07/24/a-quick-look-at-umamusume-pretty-derby/)
- [PCGamesN stats guide](https://www.pcgamesn.com/umamusume-pretty-derby/stats)
- [Polygon Basics Guide](https://www.polygon.com/guides/611183/uma-musume-tips-tricks-beginner-before-you-start)
- [Reddit: Global vs. JP mechanics](https://www.reddit.com//r/UmamusumeGame/comments/1q5qjze/any_accurate_resources_for_the_game_mechanics_in/)
- [Game:Career Mode - Umamusume Wiki](https://umamusu.wiki/Game%3ACareer_Mode)
- [Fandom Game page](https://umamusume.fandom.com/wiki/Game)

### 9.5 Usage Guidelines

### For Developers

- Use canonical field names in code and database queries
- Follow enum definitions for type-safe constants
- Reference this glossary when naming new entities or fields

### For Documentation Writers

- Use standardized terms consistently across all documents
- Link to this glossary when introducing new terminology
- Update glossary when introducing new concepts

### For Users

- Refer to this glossary for clarification of in-app terminology
- Use game terminology sections for Uma Musume-specific terms
- Check status indicators section for icon meanings

---

### This glossary is the authoritative reference for both code and gameplay terminology as used in the Umamusume Career Planner, strictly aligned to the Global English server and common usage (February 22, 2026)
