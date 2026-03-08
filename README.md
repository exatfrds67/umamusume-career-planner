# Umamusume Pretty Derby Career Planner

[![Laravel Logo](https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg)](https://laravel.com)

[![Build Status](https://github.com/laravel/framework/workflows/tests/badge.svg)](https://github.com/laravel/framework/actions)
[![Total Downloads](https://img.shields.io/packagist/dt/laravel/framework)](https://packagist.org/packages/laravel/framework)
[![Latest Stable Version](https://img.shields.io/packagist/v/laravel/framework)](https://packagist.org/packages/laravel/framework)
[![License](https://img.shields.io/packagist/l/laravel/framework)](https://packagist.org/packages/laravel/framework)

---

## About This Project

The **Umamusume Pretty Derby Career Planner** is a comprehensive
local-first web application for optimizing gameplay in the Umamusume
Pretty Derby mobile game. The system helps players achieve A+ grade
character ratings consistently through data-driven decision making
powered by AI.

### Key Features

- **AI-Powered Recommendations**: Training, race strategy, and skill
  recommendations via Neuron AI agents with hybrid local (Ollama) and
  cloud (AWS Bedrock) providers, plus real-time streaming chat, Wit
  adequacy checks, inheritance timing awareness, and race calendar context
- **Character Career Tracking**: Turn-by-turn stat progression (Speed,
  Stamina, Power, Guts, Wit) with Chart.js visualization
- **Skill Management**: 182+ curated skills with full descriptions, SP cost
  calculations, hint tracking, evolution paths, and plan/acquire/remove workflow
- **Support Card Deck Building**: Build and validate six-card decks with
  synergy scoring, bond tracking, friendship training status, and deck
  optimization
- **Race Strategy Planning**: Race preparation analysis with readiness
  scoring, competitor evaluation, 72-turn career calendar planning, and
  outcome advisory
- **Dual Storage Modes**: Local (browser localStorage) and Account
  (database) storage with seamless conversion
- **Import/Export**: JSON, CSV, and Excel export with schema versioning
  and legacy format migration
- **OCR Data Intake**: Screenshot processing for automated data extraction
- **Admin Panel**: User management, database tools, queue monitor, log
  viewer, and system settings
- **Privacy & Data Control**: Consent management, deletion requests, and
  GDPR-aligned user data handling
- **Snapshot & History**: Point-in-time career snapshots, comparison, and
  historical analytics
- **Offline Support**: Connectivity monitoring with graceful degradation
  and draft preservation
- **Performance Monitoring**: APM dashboards, cache monitoring, and cost
  tracking for AI services

---

## Table of Contents

1. [System Requirements](#system-requirements)
2. [Technology Stack](#technology-stack)
3. [Installation](#installation)
4. [Configuration](#configuration)
5. [Architecture Overview](#architecture-overview)
6. [Core Modules](#core-modules)
7. [AI Integration](#ai-integration)
8. [Data Management](#data-management)
9. [Testing](#testing)
10. [Documentation](#documentation)
11. [Contributing](#contributing)
12. [License](#license)

---

## System Requirements

| Requirement | Version |
| ----------- | ------- |
| PHP | 8.4.11 |
| Node.js | 18+ |
| MySQL/MariaDB | 8.0+ / 10.5+ |
| SQLite | 3.35+ (for testing) |
| Redis (WSL) | 6.0+ (optional, for caching) |
| Composer | 2.0+ |

### Browser Support

- Chrome (last 2 versions)
- Firefox (last 2 versions)
- Safari (last 2 versions)
- Edge (last 2 versions)
- Progressive Web App (PWA) installable

---

## Technology Stack

### Backend

| Component | Technology | Version |
| --------- | ---------- | ------- |
| Framework | Laravel | v12 |
| Frontend Reactivity | Livewire | v4 |
| PHP Runtime | PHP | 8.4.11 |
| Database | MySQL/MariaDB/SQLite | 8.0+ / 10.5+ / 3.35+ |
| Cache | Redis (WSL) | 6.0+ |
| Authentication | Laravel Sanctum | v4 |
| Queue Management | Laravel Horizon | v5 |
| Debugging | Laravel Telescope | v5 |

### Frontend

| Component | Technology | Version |
| --------- | ---------- | ------- |
| Client Interactivity | Alpine.js | v3 |
| State Persistence | @alpinejs/persist | v3 |
| Collapse Plugin | @alpinejs/collapse | v3 |
| Styling | TailwindCSS | v4 |
| Build Tool | Vite | v7 |
| Charts | Chart.js | v4 |
| Icons | Heroicons | Latest |

### AI & Integration

| Component | Technology | Version |
| --------- | ---------- | ------- |
| Local AI | Ollama | Latest |
| Cloud AI | AWS Bedrock | Claude 3.5 Sonnet, Nova |
| Agent Framework | Custom Neuron Implementation | - |
| MCP Integration | Memory, Filesystem, Fetch, GitKraken,  | Latest |
|  | Chrome DevTools |  |
| OCR | Tesseract + OpenCV + GD | 5+ |
| External APIs | umapyoi.net, UmamusumeDB.com | - |

### Testing & Quality

| Type | Tool | Version |
| ---- | ---- | ------- |
| Backend Unit/Feature | Pest | v4 |
| Browser Testing | Pest Browser Plugin | v4 |
| Static Analysis | Larastan | v3 |
| Code Formatting | Laravel Pint | v1 |
| E2E/Traversal | Playwright | v1.58 |
| Accessibility | axe-core | Latest |

---

## Installation

### Quick Start

```bash
# Clone the repository
git clone https://github.com/your-org/umamusume-career-planner.git
cd umamusume-career-planner

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate --seed

# Build assets
npm run build

# Start all development services (server, queue, logs, vite)
composer run dev

# Or start services individually:
# php artisan serve
# php artisan queue:listen --tries=1
# php artisan pail --timeout=0
# npm run dev
```

### Development Commands

```bash
# Run all services (server, queue, vite)
composer run dev

# Run all tests
composer test
# or
php artisan test --compact

# Run specific test suites
composer test:unit
composer test:feature
composer test:integration
composer test:architecture

# Run tests in parallel
composer test:parallel

# Code coverage
composer test:coverage
composer test:coverage-html

# Code formatting
vendor/bin/pint
vendor/bin/pint --dirty

# Static analysis
vendor/bin/phpstan analyse

# Cache management
php artisan cache:clear
php artisan cache:warm

# Redis health check (WSL)
wsl bash -c "redis-cli ping"
php artisan redis:health --detailed
```

---

## Configuration

### Environment Variables

Key configuration options in `.env`:

```env
# Application
APP_NAME="Umamusume Career Planner"
APP_ENV=local
APP_DEBUG=true

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=umamusume_planner

# Cache & Queue
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# AI Configuration
AI_PRIMARY_PROVIDER=ollama
AI_FALLBACK_PROVIDER=bedrock
OLLAMA_HOST=http://localhost:11434
OLLAMA_MODEL=llama3.2

# AWS Bedrock
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=us-east-1
AWS_BEDROCK_MODEL=anthropic.claude-3-5-sonnet-20241022-v2:0

# MCP Configuration
MCP_ENABLED=true
MCP_MEMORY_ENABLED=true
MCP_FILESYSTEM_ENABLED=true
MCP_FETCH_ENABLED=true

# External APIs
UMAPYOI_API_BASE_URL=https://api.umapyoi.net
UMAPYOI_API_TIMEOUT=30
UMAMUSUMEDB_API_URL=https://umamusumedb.com/api

# Performance Monitoring
APM_ENABLED=true
TELESCOPE_ENABLED=true
HORIZON_ENABLED=true
```

### Configuration Files

| File | Purpose |
| ---- | ------- |
| `config/ai.php` | AI provider settings and routing |
| `config/ai_agents.php` | AI agent configurations |
| `config/neuron.php` | Neuron framework settings |
| `config/mcp.php` | MCP server settings |
| `config/mcp_tools.php` | MCP tool controls |
| `config/mcp-agents.php` | MCP agent configurations |
| `config/external-apis.php` | External API configuration |
| `config/cache-management.php` | Cache warming and invalidation |
| `config/apm.php` | Application performance monitoring |
| `config/query-optimization.php` | Database query optimization |
| `config/api-performance.php` | API performance tracking |

---

## Architecture Overview

```text
┌─────────────────────────────────────────────────────────────────┐
│                        Browser Layer                             │
├─────────────────────────────────────────────────────────────────┤
│  Alpine.js Components          │  Livewire Components           │
│  - Dropdowns, Modals           │  - PlanList, PlanEditor        │
│  - Tabs, Tooltips              │  - SkillsEditor, TurnsEditor   │
│  - Dark Mode Toggle            │  - Dashboard, CharacterList    │
├─────────────────────────────────────────────────────────────────┤
│                     localStorage Layer                           │
│  - Local runs (full plan data)                                  │
│  - Draft autosave (form state)                                  │
│  - User preferences (dark mode, settings)                       │
├─────────────────────────────────────────────────────────────────┤
│                        Laravel Backend                           │
│  - Controllers (API routes)                                     │
│  - Livewire Components (server-side)                            │
│  - Services (AI, MCP, OCR, Data Management)                     │
│  - Neuron Agents (Training, Race, Skills)                       │
│  - Models (Character, Career, Skills, Support Cards)            │
├─────────────────────────────────────────────────────────────────┤
│                     Database (MySQL/MariaDB)                     │
│  - Account runs (career_runs table)                             │
│  - Reference data (characters, skills, support cards)           │
│  - AI conversations and recommendations                         │
│  - MCP tool usage and monitoring                                │
└─────────────────────────────────────────────────────────────────┘
```

### Directory Structure

```text
umamusume-career-planner/
├── app/
│   ├── Collections/          # Custom collection classes
│   ├── Console/
│   │   └── Commands/
│   ├── Enums/
│   │   ├── AlertType.php
│   │   ├── CareerPhase.php
│   │   ├── ConsentType.php
│   │   ├── DeletionStatus.php
│   │   ├── Mood.php
│   │   ├── Priority.php
│   │   ├── RaceDistance.php
│   │   ├── RecommendationType.php
│   │   ├── RunningStyle.php
│   │   └── StorageMode.php
│   ├── Events/
│   │   └── GameVersionUpdated.php
│   ├── Helpers/
│   │   └── ImageOptimizationHelper.php
│   ├── Http/
│   │   ├── Controllers/      # 64 controllers (web + api + admin)
│   │   ├── Middleware/
│   │   ├── Requests/
│   │   └── Resources/
│   ├── Jobs/
│   │   ├── SyncExternalDataJob.php
│   │   └── WarmCacheJob.php
│   ├── Listeners/
│   │   └── InvalidateCacheOnGameUpdate.php
│   ├── Livewire/
│   │   ├── Admin/            # APM dashboard & admin panels
│   │   ├── Analytics/
│   │   ├── Privacy/
│   │   ├── Settings/
│   │   ├── Simulation/
│   │   ├── AdvisoryPanel.php
│   │   └── NotificationDropdown.php
│   ├── MCP/
│   │   └── SubagentCoordinationService.php
│   ├── Models/
│   │   ├── AdvisoryRecommendation.php
│   │   ├── AIConversation.php
│   │   ├── AiCost.php
│   │   ├── Aptitude.php
│   │   ├── Career.php
│   │   ├── Character.php
│   │   ├── CharacterSupportCard.php
│   │   ├── ChatMessage.php
│   │   ├── ConsentRecord.php
│   │   ├── ConversationMessage.php
│   │   ├── CriticalAlert.php
│   │   ├── DeletionRequest.php
│   │   ├── Event.php
│   │   ├── ExternalData.php
│   │   ├── Factor.php
│   │   ├── GameCharacter.php
│   │   ├── GameRace.php
│   │   ├── MCPAgent.php
│   │   ├── MCPServer.php
│   │   ├── MCPToolUsage.php
│   │   ├── OcrExtractedSkill.php
│   │   ├── OCRExtraction.php
│   │   ├── PredictionAccuracy.php
│   │   ├── PushSubscription.php
│   │   ├── Race.php
│   │   ├── RunSnapshot.php
│   │   ├── Skill.php
│   │   ├── SkillAcquisition.php
│   │   ├── SkillBuild.php
│   │   ├── SkillHint.php
│   │   ├── SupportCard.php
│   │   ├── SupportCardDefinition.php
│   │   ├── SupportDeck.php
│   │   ├── TrainingPrediction.php
│   │   ├── TrainingSession.php
│   │   ├── User.php
│   │   └── UserPreference.php
│   ├── Neuron/
│   │   ├── Agents/           # TrainingAdvisor, RaceStrategy, SkillRecommendation,
│   │   │                     # CareerPlanning, McpDemo
│   │   ├── Responses/
│   │   └── Support/
│   ├── Policies/
│   │   └── CharacterPolicy.php
│   ├── Repositories/
│   │   ├── CharacterRepositoryInterface.php
│   │   └── EloquentCharacterRepository.php
│   ├── Services/             # 184 service classes across subdirectories
│   │   ├── Agents/
│   │   ├── AI/
│   │   ├── Analytics/
│   │   ├── BladeAssetExtraction/
│   │   ├── ExternalAPI/
│   │   ├── MCP/
│   │   ├── Neuron/           # Agent-specific service wrappers
│   │   ├── OCR/
│   │   ├── Offline/          # Offline mode handling
│   │   ├── Privacy/          # GDPR-aligned data management
│   │   ├── Share/
│   │   ├── Simulation/
│   │   ├── Training/
│   │   └── [core service files]
│   ├── ValueObjects/
│   └── View/
│       └── Components/
├── config/                   # 20+ configuration files
├── database/
│   ├── migrations/           # 63 migrations
│   ├── factories/            # 32 model factories
│   └── seeders/              # 14 seeders incl. curated skill catalog
├── resources/
│   ├── views/                # Blade templates (40+ page modules)
│   ├── css/
│   └── js/
│       └── pages/            # Alpine.js page modules
├── routes/
│   ├── web.php
│   └── api.php
├── tests/
│   ├── Feature/              # 187 feature test files
│   ├── Unit/                 # 93 unit test files
│   └── Browser/              # 17 browser/E2E test files
└── docs/                     # Full documentation suite
```

---

## Core Modules

### Character Management (SPEC-001)

Track character progression with comprehensive stat management:

- **Stats**: Speed, Stamina, Power, Guts, Wit (0-1200 range)
- **Aptitudes**: Distance, Surface, Running Style grades (G through S)
- **Factors**: Blue (stat), Red (aptitude), Green (unique skill),
  White (normal skill)
- **Goals**: Training objectives with progress tracking
- **Conditions**: Positive/negative status effects
- **Avatar Management**: Character images with optimization
- **SP Tracking**: Available skill points calculation
- **Character Prefill**: Auto-populate stats from external API data

### Training Optimization (SPEC-002)

AI-powered training recommendations:

- Stat gain calculation with support card bonuses
- Skill hint tracking and SP cost reduction
- Training option ranking algorithm
- Scenario-specific mechanics (URA Finale, Unity Cup)
- Training loop simulation with turn-by-turn prediction
- Training prediction accuracy tracking

### Race Strategy (SPEC-003)

Comprehensive race preparation:

- Stat requirement analysis
- Running style optimization (Front Runner, Pace Chaser, Late Surger, End Closer)
- Weather impact calculation
- Skill recommendation engine
- Win probability prediction
- Race outcome advisory feedback

### Skill Management (SPEC-004)

Complete skill lifecycle management:

- **Curated skill catalog**: 182+ skills with full descriptions, categories and SP costs
- Skill categories: Normal, Rare, Unique Inherit, Scenario-specific
- Hint-based SP cost reduction (5 levels: 10%/20%/30%/35%/40% max)
- Evolution system (Normal → Rare)
- SP optimization strategies
- Per-character skill planning with acquire/plan/remove workflow
- Skill analysis and build recommendation

### Support Card Management (SPEC-005)

Deck building and optimization:

- Six-card deck composition and validation
- Limit break multiplier system (★-★★★★★)
- Meta tier rankings (SS, S, A, B)
- Synergy scoring and recommendations
- Bond level tracking (1-5)
- Support deck persistence and management
- External API integration for card data (umapyoi.net)
- Deck optimization algorithms

### AI Advisory & Chat

Conversational AI assistant with context-aware recommendations:

- Real-time AI chat with streaming responses
- Multi-model routing (Ollama local / AWS Bedrock cloud)
- Training, race strategy, and skill advice endpoints
- Critical situation detection and alerts
- AI cost tracking and performance metrics
- Advisory recommendation persistence
- Model selection (Claude, Nova, local Ollama models)

### Admin Panel

Full administrative control panel:

- **User Management**: View, edit, toggle admin roles, remove users
- **Database Tools**: Migrations, optimization, backups, seeders, fresh database
- **Queue Monitor**: View/retry/delete queued jobs, restart workers, flush queues
- **Logs Viewer**: Application log streaming, download, clear
- **System Settings**: Cache clear/optimize/warm operations
- **APM Dashboard**: Application performance monitoring via Livewire

### Data Privacy & Consent

GDPR-aligned data management:

- Consent record tracking per user
- Data deletion request workflows (`DeletionStatus` enum)
- Push notification subscription management
- User preference persistence
- Privacy settings panel (Livewire component)

### Snapshot & History

Point-in-time career state management:

- Career run snapshots (`RunSnapshot` model)
- Snapshot creation, restore, and diff
- Data operation history tracking
- Historical analytics and career comparison
- Career comparison service across multiple runs

### Simulation

Career scenario simulation:

- Training scenario simulation with configurable parameters
- Career outcome projections
- Simulation Livewire component for interactive modeling

### Offline / Connectivity

Resilient offline behavior:

- Connectivity monitoring with real-time status indicator
- Graceful offline degradation for Account mode
- Local draft preservation during connectivity loss
- Fallback recovery service for failed operations

---

## AI Integration

### Hybrid AI Architecture

The system uses a hybrid approach with local-first AI:

```text
┌─────────────────────────────────────────────────────────────────┐
│                      AI Request Flow                             │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  User Query ──► HybridAIService ──► Route Decision               │
│                                           │                      │
│                     ┌─────────────────────┴─────────────────┐    │
│                     │                                       │    │
│                     ▼                                       ▼    │
│              ┌─────────────┐                       ┌─────────────┐
│              │   Ollama    │                       │  Bedrock    │
│              │   (Local)   │                       │  (Cloud)    │
│              └─────────────┘                       └─────────────┘
│                     │                                       │    │
│                     └─────────────────┬─────────────────────┘    │
│                                       ▼                          │
│                              AI Response                         │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### Neuron AI Agents

| Agent | Purpose | Status |
| ----- | ------- | ------ |
| TrainingAdvisorAgent | Training recommendations | ✅ Implemented |
| RaceStrategyAgent | Race preparation | ✅ Implemented |
| SkillRecommendationAgent | Skill build planning | ✅ Implemented |
| CareerPlanningAgent | Long-term career strategy | ✅ Implemented |
| McpDemoAgent | MCP tool demonstration | ✅ Implemented |

### MCP Integration

Model Context Protocol servers provide additional capabilities:

- **Memory Server**: Conversation context and knowledge graph
- **Filesystem Server**: Local file access for exports and imports
- **Fetch Server**: External API integration and web scraping
- **GitKraken Server**: Git operations and repository management
- **Chrome DevTools Server**: Browser automation and testing
- **Sequential Thinking Server**: Advanced step-by-step reasoning

### MCP Monitoring

- Real-time tool usage tracking with `MCPToolUsage` model
- Server health monitoring
- Performance metrics and cost tracking by agent/server
- Error logging, alerting, and MCP dashboard (`/mcp`)

---

## Data Management

### Import/Export

Supported formats:

| Format | Import | Export | Notes |
| ------ | ------ | ------ | ----- |
| JSON | ✓ | ✓ | Full data with relationships, schema versioned |
| CSV | ✓ | ✓ | Summary data only |
| Excel (.xlsx) | - | ✓ | Formatted spreadsheet |
| Legacy JSON | ✓ | - | Migration from older versions |

### Migration Workflow

```text
Source Data ──► Detect Format ──► Validate Schema ──► Transform
──► Resolve Conflicts ──► Import
```

### Backup & Restore

- Automatic backup creation before migrations
- Batch tracking with per-record status
- Rollback capability within 30 days
- Historical tracking of data operations
- Snapshot and restore functionality

---

## Testing

### Test Coverage

| Suite | Files | Notes |
| ----- | ----- | ----- |
| Feature (Pest) | 187 | HTTP, Livewire, service, API, UI |
| Unit (Pest) | 93 | Business logic and calculation validation |
| Browser (Pest Browser) | 17 | E2E, traversal, accessibility, visual regression |

### Test Coverage Targets

| Type | Coverage Target |
| ---- | --------------- |
| Unit Tests | 90%+ |
| Feature Tests | 80%+ |
| E2E Tests | Critical paths 100% |
| Accessibility | WCAG AA 100% |

### Running Tests

```bash
# All PHP tests
php artisan test

# Specific test suite
php artisan test --testsuite=Feature

# JavaScript tests
npm run test

# E2E tests with Playwright
npm run playwright:test

# Comprehensive Browser Traversal (tests ALL routes)
php artisan test:traversal

# Opens HTML report after completion
php artisan test:traversal --open-report

# Accessibility audit
npm run a11y:test
```

### Comprehensive Application Traversal

The application includes an automated browser testing system that
visits **every route** in the application:

```bash
# Test all routes (public + authenticated + admin)
php artisan test:traversal --open-report

# Test specific scope
php artisan test:traversal --scope=public
php artisan test:traversal --scope=auth
php artisan test:traversal --scope=admin
```

**Reports**: Generated in `storage/app/test-reports/` with:

- ✅ Coverage statistics (100+ routes)
- ⚡ Performance metrics (load times)
- 🐛 JavaScript error detection
- 📊 Visual HTML report with tables and charts

See [tests/Browser/README.md](tests/Browser/README.md) for details.

### Critical User Flows

1. Create character → upload image → save
2. Create career run → configure aptitudes → set goals
3. Add training sessions → track stat progression → visualize growth
4. Add skills via autocomplete → manage hints → track SP costs
5. Build support deck → select 6 cards → validate composition
6. Request AI advice → receive recommendations → apply suggestions
7. Export data (JSON/CSV/Excel) → preview → download
8. Import data → detect format → validate → resolve conflicts → confirm
9. Dark mode toggle → refresh → persists
10. Convert local run to account after login
11. OCR screenshot → extract data → validate → import

---

## Recent Changes

### v2.1.0 — March 1, 2026

#### Bug Fixes

- **Skill delete modal** — Fixed Alpine.js order-of-execution bug where `closeSkillModal()` was
  called before `openRemoveModal(selectedSkill)`, causing `selectedSkill` to be `null` when the
  confirmation modal opened. The "Yes, Remove" button now correctly removes the targeted skill.
- **Skill plan/remove double-encoding** — `SkillManagementController` was manually calling
  `json_encode()` before writing to `career_metadata`, causing double-encoding since the `Career`
  model already has an `'array'` cast. Fixed by assigning the PHP array directly and letting
  Eloquent's cast handle serialization.

#### Enhancements

- **Skill catalog descriptions** — All 182 curated skills have been given full, accurate
  in-game descriptions covering passive skills, unique skills, inherit skills, and scenario-specific
  skills (URA Finale, Unity Cup).
- **Livewire upgraded to v4** — Frontend reactivity framework updated from v3 with improved
  performance and new lifecycle hooks.
- **Pest upgraded to v4** — Testing framework updated including the `pest-plugin-browser`
  integration for in-process browser testing.
- **Chart.js added** — v4 charting library integrated for stat progression visualization.
- **Admin panel expanded** — Database maintenance tools, queue monitor, log viewer, and system
  settings pages added to the admin area.
- **CareerPlanningAgent implemented** — Long-term career strategy Neuron agent is now fully
  implemented.
- **McpDemoAgent added** — Demonstration agent for MCP tool orchestration.
- **Privacy & consent system** — GDPR-aligned consent tracking, data deletion request handling,
  and user preference management added.
- **Simulation module** — Career outcome scenario simulation with interactive Livewire component.
- **Offline resilience** — Connectivity monitoring, graceful offline degradation, and fallback
  recovery service for failed operations.
- **Snapshot system** — `RunSnapshot` model + service for point-in-time career state, restore,
  and diff workflows.

---

## Documentation

### Core Documentation

| Document | Description |
| -------- | ----------- |
| [001_SDP](docs/00-core-docs/001_SDP_Software_Development_Plan.md) | Development phases and timeline |
| [002_BRS](docs/00-core-docs/002_BRS_Business_Requirements_Specifications.md) | Business objectives and scope |
| [003_SRS](docs/00-core-docs/003_SRS_Software_Requirement_Specifications.md) | Functional and non-functional requirements |
| [004_SDS](docs/00-core-docs/004_SDS_Software_Design_Specifications.md) | Architecture and component design |
| [009_DBD](docs/00-core-docs/009_DBD_Database_Documentation.md) | Schema and relationships |
| [010_SCD](docs/00-core-docs/010_SCD_Source_Code_Documentation.md) | Code structure and conventions |
| [017_SUM](docs/00-core-docs/017_SUM_Software_User_Manual.md) | User guide and tutorials |

### Technical Documentation

| Document | Description |
| -------- | ----------- |
| [PRDs](docs/02-prds/) | Product requirement documents (7 modules) |
| [SPECs](docs/02-specs/) | Technical specifications (7 modules) |
| [Flows](docs/01-flows/) | System flow diagrams |
| [Tech Flows](docs/01-tech-flow/) | Technical flow documentation |
| [User Flows](docs/01-user-flows/) | User journey diagrams |
| [Wireframes](docs/01-wireframes/) | UI specifications (12 screens) |
| [Sequences](docs/01-sequences/) | Sequence diagrams (15 flows) |
| [Diagrams](docs/01-diagrams/) | ERD, DFD, and process flows |

### Gameplay Guides

| Guide | Description |
| ----- | ----------- |
| [Skill System Guide](docs/guides/SKILL_SYSTEM_GUIDE.md) | Wit activation, skill phases, hint discounts, and build planning |
| [Inheritance and Legacy System Guide](docs/guides/INHERITANCE_LEGACY_GUIDE.md) | 3-generation inheritance, spark types, affinity, and parent planning |
| [Race Calendar and Career Scheduling Guide](docs/guides/RACE_CALENDAR_GUIDE.md) | 72-turn planning, race tiers, seasonal camps, and fan milestones |
| [Support Card Strategy Guide](docs/guides/SUPPORT_CARD_STRATEGY_GUIDE.md) | Bond progression, friendship activation, support bonuses, and deck strategy |

### API Documentation

| Endpoint Group | Methods | Description |
| -------------- | ------- | ----------- |
| `/api/characters` | GET, POST | Character management |
| `/api/characters/{id}` | GET, PUT, DELETE | Character CRUD + prefill |
| `/api/characters/{id}/skill-hints` | GET | Per-character skill hints |
| `/api/careers` | GET, POST | Career run management |
| `/api/skills` | GET | Skill listing with character filter |
| `/api/skills/search` | GET | Skill autocomplete |
| `/api/skills/plan` | POST | Add skill to career plan |
| `/api/skills/acquire` | POST | Mark skill as acquired |
| `/api/skills/remove` | DELETE | Remove skill from plan |
| `/api/skills/hints` | GET, POST, DELETE | Skill hint management |
| `/api/skills/recommendations` | POST | AI skill build advice |
| `/api/support-cards` | GET | Support card catalog |
| `/api/support-decks` | GET, POST, PUT, DELETE | Support deck management |
| `/api/training/predictions` | POST | Training predictions |
| `/api/advisory/training/recommendations` | POST | AI training advice |
| `/api/advisory/race/strategy` | POST | AI race strategy |
| `/api/advisory/skills/advice` | POST | AI skill advice |
| `/api/advisory/critical/detect` | POST | Critical situation detection |
| `/api/ai/chat/message` | POST | AI chat (standard + stream) |
| `/api/ai/dashboard/*` | GET | AI costs, agents, performance |
| `/api/cache/*` | GET, POST | Cache management & monitoring |
| `/api/backup/*` | GET, POST, DELETE | Backup create/restore/schedule |
| `/api/ocr/extract` | POST | OCR screenshot processing |
| `/api/external-data/*` | GET, POST | External API sync & browse |
| `/api/local-storage/*` | GET, POST | Local storage sync |
| `/api/snapshot/*` | GET, POST | Career run snapshots |
| `/api/notifications` | GET, POST | Push notification management |
| `/api/connectivity/check` | GET | Connectivity status probe |

For detailed API documentation, see [openapi.yaml](docs/deployment/openapi.yaml)

---

## Contributing

### Development Workflow

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes following coding standards
4. Write tests for new functionality
5. Ensure all tests pass (`php artisan test && npm run test`)
6. Run code formatting (`vendor/bin/pint && npm run prettier:fix`)
7. Commit your changes (`git commit -m 'Add amazing feature'`)
8. Push to the branch (`git push origin feature/amazing-feature`)
9. Open a Pull Request

### Coding Standards

- PHP: PSR-12 via Laravel Pint
- JavaScript: ESLint + Prettier
- CSS: TailwindCSS conventions
- Tests: Pest PHP conventions

### Definition of Done

- [ ] Code follows PSR-12 standards (verified with `vendor/bin/pint`)
- [ ] Static analysis passes (verified with `vendor/bin/phpstan analyse`)
- [ ] Unit/feature tests pass with >80% coverage
- [ ] Works in both Local and Account storage modes (if applicable)
- [ ] Validation rules are present and tested
- [ ] All interactive elements have proper accessibility attributes
- [ ] Playwright E2E coverage exists for key actions
- [ ] axe-core accessibility scan passes (WCAG 2.2 AA)
- [ ] Works in both dark and light modes
- [ ] Responsive on mobile devices (320px - 2560px)
- [ ] Documentation updated (code comments, README, relevant docs)
- [ ] No N+1 query issues (verified with Telescope)

---

## License

This project is open-sourced software licensed under the [MIT license](LICENSE).

---

## Acknowledgments

- [Laravel](https://laravel.com) - The PHP framework for web artisans
- [Livewire](https://livewire.laravel.com) - Full-stack framework for Laravel
- [Alpine.js](https://alpinejs.dev) - Lightweight JavaScript framework
- [TailwindCSS](https://tailwindcss.com) - Utility-first CSS framework
- [Pest](https://pestphp.com) - Elegant PHP testing framework
- [Vite](https://vitejs.dev) - Next generation frontend tooling
- [AWS Bedrock](https://aws.amazon.com/bedrock/) - Managed AI service
- [Ollama](https://ollama.ai) - Local AI model runtime
- [umapyoi.net](https://api.umapyoi.net) - Uma Musume game data API
- [UmamusumeDB.com](https://umamusumedb.com) - Community calculator and tools

---

## Support

For support, please:

1. Check the [documentation](docs/)
2. Search [existing issues](https://github.com/your-org/umamusume-career-planner/issues)
3. Create a new issue with detailed information

---

**Version**: 2.1.0  
**Last Updated**: March 1, 2026  
**PHP**: 8.4.11  
**Laravel**: v12  
**Status**: Active Development
