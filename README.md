# Umamusume Pretty Derby Career Planner

<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
  </a>
</p>

<p align="center">
  <a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
  <a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
  <a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
  <a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

---

## About This Project

The **Umamusume Pretty Derby Career Planner** is a comprehensive local-first web application for optimizing gameplay in the Umamusume Pretty Derby mobile game. The system helps players achieve A+ grade character ratings consistently through data-driven decision making powered by AI.

### Key Features

- **AI-Powered Recommendations**: Training, race strategy, and skill recommendations via Neuron AI agents with hybrid local (Ollama) and cloud (AWS Bedrock) providers
- **Character Career Tracking**: Turn-by-turn stat progression (Speed, Stamina, Power, Guts, Wit) with visualization
- **Skill Management**: Search, track, and manage skills with SP cost calculations and evolution paths
- **Support Card Deck Building**: Build and validate six-card decks with synergy scoring
- **Race Strategy Planning**: Race preparation analysis with readiness scoring and competitor evaluation
- **Dual Storage Modes**: Local (browser localStorage) and Account (database) storage with seamless conversion
- **Import/Export**: JSON, CSV, and Excel export with schema versioning and legacy format migration
- **OCR Data Intake**: Screenshot processing for automated data extraction
- **Performance Monitoring**: APM dashboards, cache monitoring, and cost tracking for AI services

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
|-------------|---------|
| PHP | 8.2+ |
| Node.js | 18+ |
| MySQL/MariaDB | 8.0+ / 10.5+ |
| Redis | 6.0+ (optional, for caching) |
| Composer | 2.0+ |

### Browser Support

- Chrome (last 2 versions)
- Firefox (last 2 versions)
- Safari (last 2 versions)
- Edge (last 2 versions)

---

## Technology Stack

### Backend

| Component | Technology | Version |
|-----------|------------|---------|
| Framework | Laravel | 12+ |
| Frontend Reactivity | Livewire | 3 |
| PHP Runtime | PHP | 8.2+ |
| Database | MySQL/MariaDB/SQLite | - |
| Cache | Redis | 6.0+ |

### Frontend

| Component | Technology | Version |
|-----------|------------|---------|
| Client Interactivity | Alpine.js | Latest |
| Styling | TailwindCSS | v4 |
| Build Tool | Vite | 7 |

### AI & Integration

| Component | Technology |
|-----------|------------|
| Local AI | Ollama |
| Cloud AI | AWS Bedrock (Claude 4.5) |
| Agent Framework | Neuron AI |
| MCP Integration | Memory, Filesystem, Fetch servers |
| OCR | Tesseract with GD preprocessing |

### Testing

| Type | Tool |
|------|------|
| Backend Unit/Feature | Pest |
| JavaScript Unit | Vitest |
| E2E | Playwright |
| Accessibility | axe-core |

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

# Start development server
composer run dev
```

### Development Commands

```bash
# Run all services (server, queue, vite)
composer run dev

# Run tests
php artisan test
npm run test

# Run E2E tests
npm run playwright:test

# Code formatting
vendor/bin/pint --dirty
npm run prettier:fix

# Static analysis
vendor/bin/phpstan analyse
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

# AI Configuration
AI_PRIMARY_PROVIDER=ollama
AI_FALLBACK_PROVIDER=bedrock
OLLAMA_HOST=http://localhost:11434
AWS_BEDROCK_REGION=us-east-1

# MCP Configuration
MCP_ENABLED=true
MCP_MEMORY_SERVER=true
MCP_FILESYSTEM_SERVER=true

# External APIs
UMAPYOI_API_URL=https://api.umapyoi.net
UMAMUSUMEDB_API_URL=https://umamusumedb.com/api
```

### Configuration Files

| File | Purpose |
|------|---------|
| `config/ai.php` | AI provider settings and routing |
| `config/neuron.php` | Neuron agent configuration |
| `config/mcp.php` | MCP server settings |
| `config/mcp_tools.php` | MCP tool controls |
| `config/external-apis.php` | External API configuration |

---

## Architecture Overview

```
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

```
umamusume-career-planner/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   ├── Requests/
│   │   └── Resources/
│   ├── Livewire/
│   ├── Models/
│   ├── Neuron/
│   │   └── Agents/
│   │       ├── Tools/
│   │       ├── TrainingAdvisorAgent.php
│   │       ├── RaceStrategyAgent.php
│   │       └── SkillRecommendationAgent.php
│   ├── Repositories/
│   ├── Services/
│   │   ├── AI/
│   │   │   ├── OllamaService.php
│   │   │   ├── BedrockService.php
│   │   │   └── HybridAIService.php
│   │   ├── ExternalAPI/
│   │   │   ├── UmapyoiApiClient.php
│   │   │   └── UmamusumeDBApiClient.php
│   │   ├── MCP/
│   │   │   ├── MCPMonitoringService.php
│   │   │   └── MCPHealthDashboardService.php
│   │   ├── OCR/
│   │   │   └── ScreenshotProcessingService.php
│   │   └── DataManagement/
│   │       ├── DataImportService.php
│   │       ├── DataExportService.php
│   │       └── DataMigrationService.php
│   └── Providers/
├── config/
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
├── resources/
│   ├── views/
│   ├── css/
│   └── js/
├── routes/
│   ├── web.php
│   └── api.php
├── tests/
│   ├── Feature/
│   ├── Unit/
│   └── e2e/
└── docs/
```

---

## Core Modules

### Character Management (SPEC-001)

Track character progression with comprehensive stat management:

- **Stats**: Speed, Stamina, Power, Guts, Wit (0-1200 range)
- **Aptitudes**: Distance, Surface, Running Style grades (G through SS)
- **Factors**: Blue (stat), Red (aptitude), Green (unique skill), White (normal skill)
- **Goals**: Training objectives with progress tracking
- **Conditions**: Positive/negative status effects

### Training Optimization (SPEC-002)

AI-powered training recommendations:

- Stat gain calculation with support card bonuses
- Skill hint tracking and SP cost reduction
- Training option ranking algorithm
- Scenario-specific mechanics (URA Finale, Unity Cup)

### Race Strategy (SPEC-003)

Comprehensive race preparation:

- Stat requirement analysis
- Running style optimization (Front Runner, Pace Chaser, Late Surger, End Closer)
- Weather impact calculation
- Skill recommendation engine
- Win probability prediction

### Skill Management (SPEC-004)

Complete skill lifecycle management:

- Skill catalog with categories (Normal, Rare, Unique)
- Hint-based SP cost reduction (20% per hint, 40% max)
- Evolution system (Normal → Rare)
- SP optimization strategies

### Support Card Management (SPEC-005)

Deck building and optimization:

- Six-card deck composition and validation
- Limit break multiplier system
- Meta tier rankings (SS, S, A, B)
- Synergy scoring and recommendations

---

## AI Integration

### Hybrid AI Architecture

The system uses a hybrid approach with local-first AI:

```
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

| Agent | Purpose | Tools |
|-------|---------|-------|
| TrainingAdvisorAgent | Training recommendations | CharacterStatsTool, TrainingPredictionTool |
| RaceStrategyAgent | Race preparation | RaceAnalysisTool, CompetitorTool |
| SkillRecommendationAgent | Skill build planning | SkillCatalogTool, SPOptimizationTool |

### MCP Integration

Model Context Protocol servers provide additional capabilities:

- **Memory Server**: Conversation context and session state
- **Filesystem Server**: Local file access for exports
- **Fetch Server**: External API integration

---

## Data Management

### Import/Export

Supported formats:

| Format | Import | Export | Notes |
|--------|--------|--------|-------|
| JSON | ✓ | ✓ | Full data with relationships, schema versioned |
| CSV | ✓ | ✓ | Summary data only |
| Excel (.xlsx) | - | ✓ | Formatted spreadsheet |
| Legacy JSON | ✓ | - | Migration from older versions |

### Migration Workflow

```
Source Data ──► Detect Format ──► Validate Schema ──► Transform ──► Resolve Conflicts ──► Import
```

### Backup & Restore

- Automatic backup creation before migrations
- Batch tracking with per-record status
- Rollback capability within 24 hours

---

## Testing

### Test Coverage Targets

| Type | Coverage Target |
|------|-----------------|
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

# Accessibility audit
npm run a11y:test
```

### Critical User Flows

1. Create character → upload image → save
2. Create run via quick create → add stat turns → chart renders
3. Add skill via autocomplete → keyboard navigate → set status + turn acquired
4. Export Excel/CSV/Markdown with preview
5. Import JSON → preview → confirm → verify data
6. Dark mode toggle → refresh → persists
7. Convert local run to account after login

---

## Documentation

### Core Documentation

| Document | Description |
|----------|-------------|
| [D01_System_Development_Plan](docs/D01_System_Development_Plan.md) | Development phases and timeline |
| [D02_Business_Requirements](docs/D02_Business_Requirements_Specifications.md) | Business objectives and scope |
| [D03_System_Requirements](docs/D03_System_Requirements_Specifications.md) | Functional and non-functional requirements |
| [D04_System_Design](docs/D04_System_Design_Specifications.md) | Architecture and component design |
| [D09_Database_Documentation](docs/D09_Database_Documentation.md) | Schema and relationships |

### Technical Documentation

| Document | Description |
|----------|-------------|
| [PRDs](docs/prds/) | Product requirement documents |
| [SPECs](docs/specs/) | Technical specifications |
| [Flows](docs/flows/) | System flow diagrams |
| [Wireframes](docs/wireframes/) | UI specifications |
| [Sequences](docs/sequences/) | Sequence diagrams |

### API Documentation

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/v1/characters` | GET, POST | Character management |
| `/api/v1/careers` | GET, POST | Career run management |
| `/api/v1/training/predictions` | GET | Training predictions |
| `/api/v1/skills/search` | GET | Skill autocomplete |
| `/api/v1/ai/advice` | POST | AI recommendations |

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

- [ ] Code follows PSR-12 standards
- [ ] Unit/feature tests pass with >80% coverage
- [ ] All interactive elements have `data-testid` attributes
- [ ] Playwright E2E coverage exists for key actions
- [ ] axe-core accessibility scan passes
- [ ] Works in both dark and light modes
- [ ] Responsive on mobile devices
- [ ] Documentation updated

---

## License

This project is open-sourced software licensed under the [MIT license](LICENSE).

---

## Acknowledgments

- [Laravel](https://laravel.com) - The PHP framework for web artisans
- [Livewire](https://livewire.laravel.com) - Full-stack framework for Laravel
- [Alpine.js](https://alpinejs.dev) - Lightweight JavaScript framework
- [TailwindCSS](https://tailwindcss.com) - Utility-first CSS framework
- [Neuron AI](https://github.com/inspector-apm/neuron-ai) - AI agent framework

---

## Support

For support, please:

1. Check the [documentation](docs/)
2. Search [existing issues](https://github.com/your-org/umamusume-career-planner/issues)
3. Create a new issue with detailed information

---

**Version**: 2.0.0  
**Last Updated**: January 23, 2026
