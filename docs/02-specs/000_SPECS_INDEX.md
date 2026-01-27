# Technical Specifications Index

**Document Version**: 2.2.0  
**Last Updated**: 2026-01-27  
**Status**: Active  
**Project**: Umamusume Pretty Derby Career Planner  
**Architecture Version**: v2.2.0

---

## 1. Document Purpose

This index provides a comprehensive catalog of all Technical Specifications (SPEC) for the Umamusume Career Planner system. Each SPEC document translates Product Requirements Documents (PRDs) into detailed technical implementations aligned with Laravel 12 and the Hybrid AI architecture.

---

## 2. Specifications Overview

| SPEC ID | Module | Status | PRD Reference | Last Updated |
|---------|--------|--------|---------------|--------------|
| [SPEC-001](#spec-001) | Character Management | Active | PRD-001 | 2026-01-24 |
| [SPEC-002](#spec-002) | Training Optimization | Active | PRD-002 | 2026-01-24 |
| [SPEC-003](#spec-003) | Race Strategy | Active | PRD-003 | 2026-01-24 |
| [SPEC-004](#spec-004) | Skill Management | Active | PRD-004 | 2026-01-27 |
| [SPEC-005](#spec-005) | Support Card Management | Active | PRD-005 | 2026-01-27 |
| [SPEC-006](#spec-006) | AI Advisory System | Active | PRD-006 | 2026-01-24 |
| [SPEC-007](#spec-007) | External Integration | Active | PRD-007 | 2026-01-24 |
| [SPEC-008](#spec-008) | Performance Monitoring & APM | Active | SRS §3.9 | 2026-01-27 |

---

## 3. Specifications Catalog

### SPEC-001: Character Management System

**File**: [SPEC-001_Character_Management_Technical.md](SPEC-001_Character_Management_Technical.md)  
**Covers**: PRD-001  
**Focus**: Character lifecycle, stat tracking, aptitude management, inheritance optimization

**Key Components**:

- Character entity with stats (Speed/Stamina/Power/Guts/Wit), Energy, Mood
- Aptitude system with grade-based ratings (G-SS) for Distance, Surface, Strategy
- Factor inheritance with parent selection and stat/aptitude bonuses
- Goal engine for dynamic objective tracking
- Condition system for status effect management

**Technology Stack**:

- Laravel 12 Eloquent Models
- Service Layer: `CharacterService`, `FactorInheritanceService`
- Database: MySQL 8.0+ with `ucp_characters` table
- Cache: Redis for character state snapshots

---

### SPEC-002: Training Optimization System

**File**: [SPEC-002_Training_Optimization_Technical.md](SPEC-002_Training_Optimization_Technical.md)  
**Covers**: PRD-002  
**Focus**: Training predictions, support card integration, failure risk assessment

**Key Components**:

- Prediction engine with deterministic stat gain calculations
- Support card bonus system with friendship training multipliers
- Risk calculator for failure probability based on energy/mood
- Recommendation engine for training option ranking
- Neuron AI integration for intelligent training advisories

**Technology Stack**:

- Service Layer: `TrainingService`, `TrainingPredictionService`
- AI: `NeuronAI` with `TrainingAdvisorAgent`
- Cache: Redis predictions with 5-minute TTL
- Database: `ucp_training_sessions` for execution history

---

### SPEC-003: Race Strategy System

**File**: [SPEC-003_Race_Strategy_Technical.md](SPEC-003_Race_Strategy_Technical.md)  
**Covers**: PRD-003  
**Focus**: Race preparation, strategy optimization, performance prediction

**Key Components**:

- Race analysis engine for stat requirement validation
- Weather impact system with track condition modifiers
- Running style optimizer based on character aptitudes
- Win probability calculator using weighted scoring
- Race calendar with scheduling and conflict detection

**Technology Stack**:

- Service Layer: `RaceService`, `RaceStrategyService`
- AI: `RaceStrategyAgent` via Neuron framework
- External: API integration with `umapyoi.net`
- Database: `ucp_race_definitions`, `ucp_race_results`

---

### SPEC-004: Skill Management System

**File**: [SPEC-004_Skill_Management_Technical.md](SPEC-004_Skill_Management_Technical.md)  
**Covers**: PRD-004  
**Focus**: Skill acquisition, hint tracking, evolution mechanics, SP optimization

**Key Components**:

- Skill catalog database with Normal, Rare, and Unique classifications
- Hint system with level-based SP discounts (10%-40%)
- Evolution logic for skill upgrades with prerequisite validation
- SP optimization algorithms for budget management
- Skill recommendation engine powered by AI

**Technology Stack**:

- Service Layer: `SkillService`, `SPCostCalculator`
- AI: `SkillRecommendationAgent`
- Database: `ucp_skills`, `ucp_skill_hints`, `ucp_skill_acquisitions`
- Cache: Skill metadata and cost calculations

---

### SPEC-005: Support Card Management System

**File**: [SPEC-005_Support_Card_Management_Technical.md](SPEC-005_Support_Card_Management_Technical.md)  
**Covers**: PRD-005  
**Focus**: Deck composition, card bonuses, bond tracking, meta rankings

**Key Components**:

- Card inventory with limit break (0-4 stars) and level tracking
- Deck builder supporting 6-card composition (5 owned + 1 borrowed)
- Synergy analysis for stat distribution and event coverage
- Meta integration with tier list synchronization
- Bond level system affecting friendship training bonuses

**Technology Stack**:

- Service Layer: `SupportDeckService`, `DeckCompositionValidator`
- External: Sync from community databases
- Database: `ucp_support_cards`, `ucp_support_decks`
- Cache: Card metadata and deck analysis results

---

### SPEC-006: AI Advisory System

**File**: [SPEC-006_AI_Advisory_Technical.md](SPEC-006_AI_Advisory_Technical.md)  
**Covers**: PRD-006  
**Focus**: Intelligent recommendations using hybrid local/cloud AI

**Key Components**:

- Hybrid architecture routing between Ollama (local) and AWS Bedrock (cloud)
- Neuron agent framework with specialized agents (Training, Racing, Skills)
- Context builder for dynamic prompt generation from game state
- MCP (Model Context Protocol) integration for tool use
- Cost management with token tracking and budget enforcement

**Technology Stack**:

- AI Services: `NeuronAI`, `OllamaService`, `BedrockService`
- MCP Servers: Database query, file system, calculation tools
- Database: `ucp_ai_conversations`, `ucp_ai_recommendations`
- Models: Claude 4.5, Mistral Large 2, Ollama local models

---

### SPEC-007: External Integration System

**File**: [SPEC-007_External_Integration_Technical.md](SPEC-007_External_Integration_Technical.md)  
**Covers**: PRD-007  
**Focus**: API integration, OCR processing, WebSocket updates

**Key Components**:

- API clients with circuit breaker pattern for resilience
- Fallback strategy across multiple data sources
- OCR pipeline using Tesseract + OpenCV for screenshot processing
- Data sync scheduler for daily game data updates
- Real-time updates via Laravel Reverb WebSocket broadcasting

**Technology Stack**:

- Service Layer: `ExternalAPIService`, `OCRService`
- OCR: Tesseract 5.x, OpenCV 4.x
- WebSocket: Laravel Reverb
- Database: `ucp_external_api_cache`, `ucp_ocr_extractions`

---

### SPEC-008: Performance Monitoring & APM System

**File**: [SPEC-008_Performance_Monitoring_Technical.md](SPEC-008_Performance_Monitoring_Technical.md)  
**Covers**: SRS §3.9 (Performance Requirements)  
**Focus**: Application performance monitoring, query optimization, alerting

**Key Components**:

- Real-time metrics collection via middleware instrumentation
- API endpoint performance tracking with percentile analysis
- Database query analysis with automatic optimization suggestions
- Redis cache hit/miss analysis and TTL optimization
- Performance alerting with configurable thresholds
- Regression detection across deployments
- Historical metrics storage with tiered retention

**Technology Stack**:

- Service Layer: 8 specialized services (ApmService, QueryOptimizationService, etc.)
- Real-time Storage: Redis (1-hour hot metrics)
- Historical Storage: MySQL with tiered retention (7 days raw → 1 year aggregated)
- Alerting: Laravel Events + Notifications (Slack, Email, Dashboard)
- Database: `ucp_apm_metrics`, `ucp_apm_alerts`, `ucp_apm_aggregates`

---

## 4. Architecture Alignment

All specifications adhere to the **v2.2.0 Architecture** defined in the Software Development Plan:

**Backend Framework**:

- Laravel 12.x (PHP 8.3+)
- Domain-Driven Design with Service Layer pattern

**Frontend Stack**:

- Livewire 3 for reactive components
- Alpine.js 3 for client-side interactivity
- Tailwind CSS v4 for styling

**Database**:

- MySQL 8.0+ with InnoDB engine
- Table prefix: `ucp_`
- Schema versioning via Laravel migrations

**AI Integration**:

- Hybrid: Ollama (local) + AWS Bedrock Claude 4.5 (cloud)
- Neuron framework for agent orchestration
- MCP protocol for tool integration

**Caching & Performance**:

- Redis 7.x for application cache
- Query optimization with eager loading
- Response caching for external API calls

---

## 5. Cross-Cutting Concerns

### 5.1 Performance Standards

- API response time: < 200ms (p95)
- Database queries: < 50ms (individual)
- AI response time: < 2s (local), < 5s (cloud)
- Page load time: < 1.5s (initial), < 500ms (navigation)

### 5.2 Security Requirements

- Authentication: Laravel Sanctum with SPA tokens
- Authorization: Policy-based access control
- CSRF Protection: Enabled for all state-changing operations
- Input Validation: Form Request validation classes
- SQL Injection Prevention: Eloquent ORM with parameterized queries

### 5.3 Testing Strategy

- **Unit Tests**: Pest PHP for business logic (80% coverage target)
- **Feature Tests**: Laravel HTTP tests for endpoints
- **Integration Tests**: Database transactions with RefreshDatabase
- **E2E Tests**: Playwright for critical user journeys
- **AI Tests**: Mock responses for deterministic validation

### 5.4 Accessibility Compliance

- WCAG 2.1 Level AA conformance
- Semantic HTML structure
- ARIA labels for dynamic content
- Keyboard navigation support
- Screen reader compatibility

---

## 6. Document Change Control

### Version History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 2.0.0 | 2026-01-24 | Development Team | Full alignment with v2.0.0 architecture |
| 1.0.0 | 2026-01-14 | Development Team | Initial specification drafts |

### Review Schedule

- **Technical Review**: Weekly during active development
- **Architecture Review**: Bi-weekly with lead architects
- **Stakeholder Review**: Monthly for PRD alignment validation

---

## 7. Related Documentation

### Planning Documents

- [001_SDP_Software_Development_Plan.md](../001_SDP_Software_Development_Plan.md)
- [002_PMP_Project_Management_Plan.md](../002_PMP_Project_Management_Plan.md)

### Requirements & Design

- [003_SRS_Software_Requirement_Specifications.md](../003_SRS_Software_Requirement_Specifications.md)
- [004_SDS_Software_Design_Specifications.md](../004_SDS_Software_Design_Specifications.md)

### Database & API

- [009_DBD_Database_Documentation.md](../009_DBD_Database_Documentation.md)
- [010_API_API_Documentation.md](../010_API_API_Documentation.md)

### Product Requirements

- [PRD Index](../prds/000_PRDS_INDEX.md)
- Individual PRD documents (PRD-001 through PRD-007)

### Visual Documentation

- [Wireframes Index](../wireframes/000_WIREFRAMES_INDEX.md)
- [Sequence Diagrams Index](../sequences/000_SEQUENCES_INDEX.md)
- [User Flow Index](../user-flows/000_USER_FLOWS_INDEX.md)

---

## 8. Glossary

| Term | Definition |
|------|------------|
| **Aptitude** | Character rating (G-SS) for distance, surface, or running style |
| **Factor** | Inherited trait from parent characters providing stat/skill bonuses |
| **Neuron** | AI agent framework for providing strategic recommendations |
| **SP** | Skill Points - currency for acquiring skills during training |
| **Support Deck** | Configuration of 6 support cards providing training bonuses |
| **Training Session** | Single turn of training in a career run |
| **URA Finale** | Primary scenario mode in Umamusume Pretty Derby |

---

**Document Control**  
**Maintained By**: Technical Architecture Team  
**Next Review**: 2026-02-07  
**Distribution**: Development Team, Product Managers, QA Team

---

**End of Document**
