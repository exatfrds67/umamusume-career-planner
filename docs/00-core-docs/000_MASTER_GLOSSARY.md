# Master Glossary

## Umamusume Pretty Derby Career Planner

**Document Version**: 3.2.0  
**Date**: January 28, 2026  
**Project**: UmamusumeCareerPlanner  
**Author**: Development Team  
**Status**: Current – Aligned with v2.2.0 and Global English Server Mechanics (January 2026)

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

This glossary defines all core terminology used in the Umamusume Pretty Derby Career Planner project, aligned with the v2.0.0 codebase and the **Global English server** gameplay and translations (as of January 2026). Use this glossary as the authoritative reference for all documentation, code, and user interface.

---

## 2. Game Terminology

### 2.1 Core Stats

| Stat   | Japanese | Definition                                                                                                   | Range  | Priority |
|--------|----------|-------------------------------------------------------------------------------------------------------------|--------|----------|
| Speed  | スピード | Maximum running speed; determines overall race velocity.                                                    | 0-1200 | ★★★★★   |
| Stamina| スタミナ | HP & effective stamina; enables staying power over longer distances and through race events.                | 0-1200 | ★★★★    |
| Power  | パワー   | Acceleration and ability to navigate around opponents, especially during race transitions and late bursts.   | 0-1200 | ★★★     |
| Guts   | 根性     | Affects race **position holding**, resistance to position loss in navigation battles; contributes to final sprint and recovers stamina under force-out conditions (not just "last spurt strength"). | 0-1200 | ★★      |
| Wit    | 賢さ     | Influences **skill activation rate**, likelihood to **avoid "kakari" status** (stamina penalty), and general race event triggers; good Wit is more important than pure stat for race stability. | 0-1200 | ★★★     |

**Stat Grade Scale:**

| Grade | Value Range | Effectiveness |
|-------|-------------|---------------|
| SS | 1100-1200 | Elite tier |
| S | 950-1099 | Excellent |
| A | 850-949 | Good |
| B+ | 750-849 | Above average |
| B | 650-749 | Average |
| C+ | 550-649 | Below average |
| C | 450-549 | Poor |
| D+ | 350-449 | Very poor |
| D | 250-349 | Minimal |
| E | 150-249 | Negligible |
| F | 0-149 | None |

> **Note:** Wit (Wisdom/Intelligence) not only increases skill activation chance, it directly affects the chance to avoid the "kakari" mishap (which increases stamina drain and prevents skill use during it).

### 2.2 Aptitudes

| Term                  | Japanese | In-Game Label (EN) | Description                                                            | Categories                             |
|-----------------------|----------|--------------------|------------------------------------------------------------------------|----------------------------------------|
| Aptitude              | 適性     | Aptitude           | Character compatibility for **distance, surface, and running style**.   | Distance, Surface, Style               |
| Distance Aptitude     | 距離適性 | Distance           | Preferred race lengths: Sprint, Mile, Medium, Long as per JP/EN global.| Sprint: <1400m, Mile: 1401-1800m, Medium: 1801-2400m, Long: 2401m+ |
| Surface Aptitude      | バ場適性 | Surface            | Preferred ground: Turf or Dirt                                          | Turf, Dirt                             |
| Running Style Aptitude| 脚質適性 | Style              | Preferred position in races (see below)                                 | Front, Pace, Late, End                 |

**Clarification:**  
Lower Aptitude doesn't "cap" stats, but **significantly reduces race performance and placement** when mismatched. Optimal aptitude is strongly recommended.

**Aptitude Ratings & Effectiveness:**

| Rating | Japanese | Effectiveness | Description |
|--------|----------|---------------|-------------|
| S | S | 105-110% | Maximum compatibility (S is the highest grade) |
| A | A | 100% | Good compatibility (baseline) |
| B | B | 90% | Adequate compatibility |
| C | C | 80% | Below average |
| D | D | 70% | Poor compatibility |
| E | E | 60% | Very poor |
| F | F | 50% | Minimal compatibility |
| G | G | 40% | Incompatible |

> **Note:** S is the maximum aptitude grade. SS does NOT exist in the game. Only S-rank provides positive bonuses; A-rank is the baseline with no bonus/penalty.

> *Race mechanics exclusive to the Japanese server, like Charge Up, Compete Before Spurt, or Stamina Limit Break, are **not implemented** in the Global English server as of January 2026.*  

### 2.3 Running Styles

| Term (JP) | Label (EN) | Standard Community Naming | In-Game Behaviour                  |
|-----------|------------|--------------------------|-------------------------------------|
| 逃げ      | Front      | "Front"                  | Lead early, stay in front           |
| 先行      | Pace       | "Pace"                   | Stay near leaders, chase from front |
| 差し      | Late       | "Late"                   | Travel mid-pack, surge near end     |
| 追込      | End        | "End"                    | Linger far back, sprint at finish   |

> **Note:** "Runaway" is a rare passive, not a base style.

### 2.4 Race Categories

| Term             | Definition                                                         |
|------------------|--------------------------------------------------------------------|
| Race Grade       | G1/G2/G3/Open/Pre-Open/Maiden (same as JP server for Global)       |
| Distance         | Sprint: <1400m, Mile: 1401-1800m, Medium: 1801-2400m, Long: 2401+  |
| Track Surface    | Turf (grass) or Dirt                                               |
| Race Condition   | Real-time weather/track status, affecting mood and performance     |

### 2.5 Career Stages

| Stage         | Japanese | Turn Range | Description                            |
|---------------|----------|------------|----------------------------------------|
| Junior Year   | ジュニア級 | 1–24   | Basic training foundation              |
| Classic Year  | クラシック級 | 25–48  | Competitive growth and new races       |
| Senior Year   | シニア級 | 49–72  | Peak and high-level racing             |
| URA Finals    | URAファイナルズ | 73–78  | Finals series                          |

### 2.6 Skill System

| Term      | Japanese | Global Mechanic (EN)                                                    |
|-----------|----------|-------------------------------------------------------------------------|
| Skill Point (SP) | スキルポイント | Earned via races/events, spent to purchase skills                    |
| Skill Hint       | ヒント      | Discount for skill SP cost; **5 hint levels with progressive discounts: Level 1 = 10%, Level 2 = 20%, Level 3 = 30%, Level 4 = 35%, Level 5 = 40% (maximum)**; applies when buying the skill |
| Skill Evolution  | 進化       | Upgrade of some (not all) Normal → Rare skills under set conditions   |
| Skill Rarity     | レアリティ    | Normal (white), Rare (gold), Unique (rainbow)                        |

**Skill Types (Community):**

- Speed, Stamina, Power, Recovery, Unique, "Effect on Opponent" (often referred to as "debuffs" in guides, but not in-game as a formal type)

> **Note:** The game lacks a formal "Debuff" skill category; many skills can apply negative effects to rivals.  
> Skill hints only affect the initial SP purchase price, not ongoing effects.

### 2.7 Training Actions

**Available at each turn (Career Mode):**

- **Train**: Increase specific stat (Speed/Stamina/Power/Guts/Wit)
- **Race**: Compete to earn fans and SP, progress story
- **Rest**: Recover energy (restores a variable % but consumes a turn)
- **Recreation**: (a.k.a. "Going Out") May improve mood, sometimes restore some stats or trigger unique events

### 2.8 Mood System

| Mood   | Japanese     | Effect                                                                               |
|--------|--------------|--------------------------------------------------------------------------------------|
| Great  | 絶好調        | +4% stat gains, best event/mood chance, reduces failure rate                         |
| Good   | 好調         | +2% stat gains, good events more likely                                              |
| Normal | 普通         | Baseline effect                                                                      |
| Bad    | 不調         | -2% stat gains, more failures, worse event odds                                      |
| Awful  | 絶不調        | -4% stat gains, highest failure rate, bad events more likely                         |

> **Note:** Better mood increases both training gains and chance for favorable events; also impacts likelihood of positive results under some race events.

### 2.9 Legacy and Bonds

- **Legacy Effect**: Bonuses granted to new trainees when selecting two legacy Uma Musume in setup (after retiring a career run). These grant bonus stats and sometimes skills to new runs.
- **Bond/Bonding**: Represents support card "friendship" (bond) level; higher bond unlocks improved training bonuses and special events. Distinct from raw support card stats.

**Bond Milestones:**

| Level | Reward |
|-------|--------|
| 20% | Small stat bonus |
| 40% | Skill hint |
| 60% | Special event |
| 80% | Friendship Training unlocked |

### 2.10 Conditions

| Term | Definition |
|------|------------|
| Condition | Temporary status effect (positive or negative) affecting stats or training |
| Positive Condition | Beneficial effect (e.g., "Focused", "Energized") |
| Negative Condition | Detrimental effect (e.g., "Fatigued", "Injured") |
| Kakari | Negative condition that increases stamina consumption and prevents skill activation; higher Wit reduces chance of this occurring |

---

## 3. Application Terminology

### 3.1 Core Entities

| Term               | Canonical Field      | Definition                                    | Storage       |
|--------------------|---------------------|-----------------------------------------------|---------------|
| Character          | `ucp_characters`    | Uma Musume trainee (player-controlled)        | Database      |
| Career Run / Plan  | `ucp_careers`       | A single "career mode" progression            | DB/localStore |
| Turn               | `turn_number`       | One in-game week; each runs a selection round | DB/localStore |
| Support Deck       | `ucp_support_decks` | Set of 6 support cards for training           | Database      |
| Legacy             | `legacy_*`          | Data from completed runs used to boost new trainees | Database      |

*See also: Factor, Bond, Skill Hint under Game Terminology.*

### 3.2 Data Tracking

| Term | Definition |
|------|------------|
| Stat Progress | Historical record of stat values per turn (`ucp_training_sessions`) |
| Training Session | Record of a training action with gains and outcomes |
| Race Result | Record of race participation and placement |
| Skill Acquisition | Record of when and how a skill was obtained |
| Goal | User-defined target (stat threshold, race win, skill count) |
| Bond Progress | Friendship level progression with support card characters |

### 3.3 User Interface

| Term | Definition |
|------|------------|
| Dashboard | Main overview page with stats, goals, and quick actions |
| Wizard | Multi-step guided interface (e.g., Character Creation Wizard) |
| Preview | Read-only view of data before confirmation |
| Toast | Temporary notification message |
| Modal | Overlay dialog for focused interactions |

---

## 4. Technical Terminology

### 4.1 Architecture Components

| Term | Abbreviation | Definition |
|------|--------------|------------|
| Eloquent Model | - | Laravel ORM model representing database table |
| Livewire Component | - | Server-driven reactive UI component |
| Service Layer | - | Business logic abstraction (e.g., `CharacterService`) |
| Form Request | - | Laravel validation class for HTTP requests |
| Repository | - | Data access pattern abstracting database queries |
| Enum | - | PHP 8.1+ enumeration for type-safe constants |

### 4.2 Technology Stack

| Technology | Version | Layer | Purpose |
|------------|---------|-------|---------|
| Laravel | 12+ | Backend Framework | Application foundation |
| PHP | 8.2+ | Runtime | Server-side execution |
| Livewire | 3 | Frontend Reactivity | Dynamic UI without JavaScript |
| Alpine.js | Latest | Client Interactivity | Lightweight JS framework |
| TailwindCSS | v4 | Styling | Utility-first CSS framework |
| Vite | 7 | Build Tool | Asset compilation and bundling |
| MySQL | 8.0+ | Database | Primary data store |
| MariaDB | 10.5+ | Database | MySQL-compatible alternative |
| SQLite | Latest | Database | Development/testing database |
| Redis | 7+ | Cache/Queue | Caching and background jobs |

### 4.3 Database Conventions

| Convention | Pattern | Example |
|------------|---------|---------|
| Table Prefix | `ucp_` | `ucp_characters`, `ucp_careers` |
| Primary Key | `id` | Integer auto-increment |
| UUID Field | `uuid` | For Local storage mode |
| Foreign Key | `{table}_id` | `character_id`, `user_id` |
| Timestamps | `created_at`, `updated_at` | Laravel standard |
| Soft Deletes | `deleted_at` | For recoverable deletions |

### 4.4 Validation Rules

| Rule Type | Example | Description |
|-----------|---------|-------------|
| Stat Range | 0-1200 | Hard max, no values above 1200 allowed |
| Turn Range | 1-78 | Valid turn numbers |
| Energy Range | 0-100 | Energy level percentage |
| Deck Size | 6 cards | Exactly 6 cards (5 owned + 1 borrowed) |
| Hint Level | 0-5 | Maximum 5 hints per skill (40% discount at level 5) |

---

## 5. AI and Integration Terminology

### 5.1 AI System

| Term | Abbreviation | Definition |
|------|--------------|------------|
| AI Provider | - | Backend service for AI inference (Ollama, AWS Bedrock) |
| Neuron AI | - | AI agent orchestration framework |
| AI Agent | - | Specialized AI for specific tasks (Training Advisor, Race Strategy) |
| AI Conversation | - | Persisted chat session (`ucp_ai_conversations`) |
| AI Cost | - | Per-token usage cost tracking (`ucp_ai_costs`) |
| AI Metrics | - | Performance and usage analytics (`ucp_ai_metrics`) |
| Hybrid AI | - | Architecture using local (Ollama) + cloud (Bedrock) fallback |

**AI Providers:**

| Provider | Type | Models | Use Case |
|----------|------|--------|----------|
| Ollama | Local | llama3.2, mistral | Primary, free inference |
| AWS Bedrock | Cloud | Claude 3.5 Sonnet, Claude 4.5 | Complex queries, fallback |

### 5.2 Neuron Agents

| Agent | Purpose | Tools |
|-------|---------|-------|
| Training Advisor Agent | Recommends optimal training selections | Character stats, training predictions, support bonuses |
| Race Strategy Agent | Analyzes race requirements and strategy | Race requirements, aptitude analysis, win probability |
| Skill Advisor Agent | Suggests skill acquisition priorities | Skill catalog, SP budget, hints, evolution paths |
| Career Planning Agent | Provides long-term strategic guidance | Goal analysis, stat progression, timeline optimization |

### 5.3 MCP (Model Context Protocol)

| Term | Abbreviation | Definition |
|------|--------------|------------|
| MCP | Model Context Protocol | Standard for AI tool/agent integration |
| MCP Server | - | Tool provider service (Memory, Filesystem, Fetch) |
| MCP Agent | - | AI agent configuration (`ucp_mcp_agents`) |
| MCP Tool | - | Executable function exposed to AI agents |
| MCP Tool Usage | - | Tracking of tool invocations (`ucp_mcp_tool_usage`) |
| MCP Health | - | Server availability monitoring (`ucp_mcp_server_health`) |

**MCP Server Types:**

| Server | Type | Purpose |
|--------|------|---------|
| Memory | Local | Conversation context persistence |
| Filesystem | Local | Document and file access |
| Fetch | Local | HTTP resource retrieval |
| Custom | Remote | Domain-specific tools (optional) |

### 5.4 External APIs

| Term | Definition |
|------|------------|
| External Data | Cached records from external APIs (`ucp_external_api_cache`) |
| Circuit Breaker | Resilience pattern preventing cascading failures |
| Fallback API | Secondary API used when primary fails |
| Cache TTL | Time-to-live for cached API responses (24 hours) |

**External API Sources:**

| API | Purpose | Status |
|-----|---------|--------|
| umapyoi.net | Primary game data (characters, support cards, news) | Active |
| UmamusumeDB.com | Fallback data (skills, races) | Active |

### 5.5 OCR System

| Term | Abbreviation | Definition |
|------|--------------|------------|
| OCR | Optical Character Recognition | Technology for extracting text from images |
| OCR Extraction | - | Result of OCR processing (`ucp_ocr_extractions`) |
| GD Library | GD | PHP image processing library for preprocessing |
| Tesseract | - | Open-source OCR engine |
| Confidence Score | - | OCR accuracy metric (0-100%) |
| Preprocessing | - | Image enhancement before OCR (resize, grayscale, threshold) |

**OCR Data Types:**

| Data Type | Detection Pattern | Confidence Threshold |
|-----------|-------------------|---------------------|
| Character Stats | Stat labels + numeric values | 85% |
| Skill Names | Japanese/English text regions | 80% |
| Race Results | Placement + time format | 90% |
| Support Cards | Card frame detection | 75% |

---

## 6. Data Management Terminology

### 6.1 Storage Modes

| Term | Definition | Identifier | Offline Support |
|------|------------|------------|-----------------|
| Local Mode | Browser localStorage-based storage | UUID | Full |
| Account Mode | Database-backed cloud storage | Integer ID | Requires connectivity for save |
| Storage Badge | Visual indicator of current mode | Icon + label | N/A |

### 6.2 Import/Export

| Term | Definition |
|------|------------|
| Backup | Exportable archive of user data via `BackupService` |
| Data Import | Process of loading external data into the system |
| Data Export | Process of extracting data for external use |
| Data Migration | Conversion between storage formats or schema versions |
| Format Detection | Automatic identification of import file structure |
| Schema Version | Version identifier in exported data for compatibility |

**Export Formats:**

| Format | Extension | Use Case |
|--------|-----------|----------|
| JSON | .json | Full data backup, cross-app import |
| Excel | .xlsx | Spreadsheet analysis, sharing |
| CSV | .csv | Data processing, simple imports |
| Markdown | .md | Documentation, human-readable exports |

### 6.3 Migration and Validation

| Term | Definition |
|------|------------|
| Legacy Format | Data structure from previous application versions |
| Field Mapping | Translation between legacy and canonical field names |
| Conflict Resolution | Strategy for handling duplicate records (skip, overwrite, merge, rename) |
| Validation Layer | Three-tier validation (schema, business rules, integrity) |
| Rollback | Reverting to previous state after failed migration |

---

## 7. Status and Indicator Terminology

### 7.1 Career Run Status

| Status | Icon | Definition |
|--------|------|------------|
| In Progress | 🟢 | Career is actively being played |
| Completed | ✅ | Career has finished (URA Finals complete) |
| Archived | 📦 | Career is saved but hidden from active view |
| Abandoned | ❌ | Career was discontinued |

### 7.2 Skill Status

| Status | Icon | Definition |
|--------|------|------------|
| Acquired | ✅ | Skill purchased and owned |
| Skipped | ❌ | Deliberately not acquired |
| Suggested | 💭 | Recommended by AI or planning |
| Planned | 📋 | Marked for future acquisition |

### 7.3 Storage Mode Indicators

| Indicator | Icon | Color | Description |
|-----------|------|-------|-------------|
| Local Mode | 🟠 | Orange | Data stored in browser only |
| Account Mode | 🟣 | Purple | Data synced to cloud |
| Unsaved Changes | ⚠️ | Amber | Draft exists, not yet saved |
| Draft Auto-Saved | 💾 | Gray | Draft saved to localStorage |

### 7.4 Risk Indicators

| Risk Level | Icon | Color | Percentage | Description |
|------------|------|-------|------------|-------------|
| Low Risk | 🟢 | Green | < 15% | Safe to proceed |
| Moderate Risk | 🟡 | Amber | 15-40% | Caution advised |
| High Risk | 🔴 | Red | > 40% | Risky action |

### 7.5 Goal Status

| Status | Icon | Definition |
|--------|------|------------|
| Completed | ✅ | Goal achieved |
| On Track | 🟡 | Progress within expected range |
| At Risk | 🔴 | Behind schedule, intervention needed |
| Failed | ❌ | Goal not met within timeframe |

---

## 8. Acronyms and Abbreviations

### 8.1 General Acronyms

| Acronym | Full Term | Context |
|---------|-----------|---------|
| AI | Artificial Intelligence | AI advisory system |
| API | Application Programming Interface | External data sources |
| APM | Application Performance Monitoring | Performance dashboards |
| CRUD | Create, Read, Update, Delete | Database operations |
| CSV | Comma-Separated Values | Data export format |
| JSON | JavaScript Object Notation | Data interchange format |
| MCP | Model Context Protocol | AI tool integration |
| OCR | Optical Character Recognition | Screenshot data extraction |
| ORM | Object-Relational Mapping | Eloquent database abstraction |
| PWA | Progressive Web App | Offline-capable web application |
| SP | Skill Point | In-game currency for skills |
| TTL | Time To Live | Cache expiration duration |
| UI | User Interface | Visual application layer |
| UUID | Universally Unique Identifier | Local mode identifier |
| UX | User Experience | User interaction design |

### 8.2 Technical Acronyms

| Acronym | Full Term | Context |
|---------|-----------|---------|
| CSS | Cascading Style Sheets | Styling technology |
| DB | Database | Data persistence layer |
| ERD | Entity Relationship Diagram | Database schema visualization |
| FK | Foreign Key | Database relationship |
| HTTP | Hypertext Transfer Protocol | Web communication |
| JS | JavaScript | Client-side programming |
| MIME | Multipurpose Internet Mail Extensions | File type identification |
| PK | Primary Key | Database unique identifier |
| PSR | PHP Standards Recommendation | Coding standards |
| SRS | Software Requirements Specifications | Requirements document |
| SDS | Software Design Specifications | Design document |
| UCP | Umamusume Career Planner | Application prefix |
| WCAG | Web Content Accessibility Guidelines | Accessibility standards |

### 8.3 Game-Specific Acronyms

| Acronym | Full Term | Japanese | Context |
|---------|-----------|----------|---------|
| URA | Uma Musume Racing Association | ウマ娘競走協会 | Final championship series |
| G1, G2, G3 | Grade 1, 2, 3 | - | Race classification tiers |
| HP | Hit Points | - | Stamina/health in-game |
| LB | Limit Break | 凸 | Support card upgrade level |

---

## 9. Document Control

### 9.1 Terminology Standards

| Standard | Description |
|----------|-------------|
| **Canonical Field Names** | Official database column names take precedence over UI labels |
| **Consistency** | Same term used consistently across docs, code, and UI |
| **Case Sensitivity** | Follow codebase conventions (camelCase for code, Title Case for UI) |
| **Deprecation** | Deprecated terms marked explicitly with replacement |

### 9.2 Version History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 3.2.0 | 2026-01-28 | Development Team | Updated to v2.2.0; corrected aptitude grades (S is maximum, SS does NOT exist); updated hint system to 5 levels (10%/20%/30%/35%/40%); aligned with game-accurate mechanics from Global English Server |
| 3.1.0 | 2026-01-23 | Development Team (with user corrections & source references)| Updated per Global (English) server mechanics; clarified Guts/Wit, updated running style English labels, added Training, Legacy, and Bond definitions, and explicitly excluded JP-version-only features per community and official docs |
| 3.0.0 | 2026-01-23 | Development Team | Comprehensive expansion aligned with v2.0.0; added AI, MCP, OCR, and external integration terminology; restructured into logical categories; added tables for visual clarity |
| 2.1 | 2026-01-23 | Development Team | Updated terms to match current codebase and configs |
| 2.0 | 2026-01-12 | Development Team | Consolidated glossary |
| 1.0 | 2026-01-03 | Development Team | Initial glossary |

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

**For Developers:**

- Use canonical field names in code and database queries
- Follow enum definitions for type-safe constants
- Reference this glossary when naming new entities or fields

**For Documentation Writers:**

- Use standardized terms consistently across all documents
- Link to this glossary when introducing new terminology
- Update glossary when introducing new concepts

**For Users:**

- Refer to this glossary for clarification of in-app terminology
- Use game terminology sections for Uma Musume-specific terms
- Check status indicators section for icon meanings

---

*This glossary is the authoritative reference for both code and gameplay terminology as used in the Umamusume Career Planner, strictly aligned to the Global English server and common usage (January 2026).*
