# Skills Required for Uma Musume Career Planner (v2.0.0)

**Document Version**: 2.0.0  
**Date**: 2026-01-23  
**Project**: UmamusumeCareerPlanner  
**Audience**: Contributors, Maintainers, and AI Coding Assistants  
**Status**: Current (Aligned with v2.0.0 scope and architecture)

---

## 1. Purpose

This document defines the technical and domain skills required to effectively contribute to and maintain the **Umamusume Career Planner** v2.0.0.

It reflects the current architecture and documentation set, including:

- PRDs (PRD-001..007), SPECS (SPEC-001..007)
- Sequence diagrams (SEQ-001..015)
- Tech-flow documents (TECH-FLOW-001..007)
- User flows and wireframes
- Core system docs (SDP/BRS/SRS/SDS/DMP/DMS/SIP/SIS/DBD/SCD/SUM)

---

## 2. Product & Architecture Context (v2.0.0)

### 2.1 Application Goal (Functional Scope)

The application supports end-to-end planning and tracking for Uma Musume career runs, including:

- Character/run lifecycle (creation, state, goals, snapshots)
- Training prediction and resolution
- Race registration, simulation, and outcome recording
- Skill acquisition, hint tracking, and evolution
- Support card inventory, deck building, and upgrades
- AI advisory (local-first with cloud fallback)
- External integrations (API sync, OCR data intake, real-time updates where applicable)

### 2.2 Core Architectural Patterns

Contributors should be comfortable with:

- Layered architecture: **UI → Controllers → Services → Repositories → Database**
- Event-driven patterns: domain events and listeners
- Caching strategies: short/medium/long TTL tiers
- Resilience patterns: circuit breaker + fallback for external APIs
- Real-time updates: WebSocket broadcasting (per docs, e.g., SD-007 / SPEC-007)
- Testing as a first-class deliverable

> Note: Some “tracker” variants also reference dual-storage (local vs account). Contributors should be able to work with local-first storage and synchronization patterns where present in the codebase.

---

## 3. Required Skills (Engineering)

### 3.1 Backend Engineering (PHP + Laravel 12)

**Required**

- **PHP 8.2+**: typed properties, enums, attributes, union types, strict typing
- **Laravel 12** fundamentals:
  - Routing (web + API), middleware, validation (Form Requests)
  - Eloquent ORM (relationships, eager loading, query optimization)
  - Database migrations and seeding
  - Events/listeners, queues/jobs (where used), scheduled tasks
  - Service container & dependency injection
  - Policy/authorization patterns (user-owned resources)

**Highly Recommended**

- API design: consistent REST contracts, status codes, and error shapes
- Performance tuning: N+1 avoidance, indexes, caching, pagination
- Observability: structured logs, traces, metrics hooks

### 3.2 Domain Modeling & Business Logic Implementation

Contributors must understand how to translate specs/flows into robust implementations:

- Character state modeling: stats, mood, energy, conditions, goals, snapshots
- Training computation engines and ranking logic (SPEC-002, TECH-FLOW-002)
- Race readiness scoring and simulation workflow (SPEC-003, SEQ-004)
- Skill rules:
  - SP validation
  - hint-based discount (20% per hint, max 40%)
  - evolution paths (Normal → Rare)
- Support card systems: deck rules, bond/limit break effects, bonuses

### 3.3 Data Layer & Persistence (MySQL + Redis)

**Required**

- MySQL schema design: normalization, constraints, and migrations
- Indexing strategy and query profiling
- Transactionality (atomic operations for inventory/awards/migrations)
- Redis usage patterns:
  - caching
  - rate limiting (if enabled)
  - queues (if enabled)

### 3.4 Frontend (Laravel UI stack)

Contributors should be comfortable with the UI approach used in v2.0.0 documentation:

- Blade templating and component design
- TailwindCSS v4 (responsive and accessible layouts)
- Lightweight interactivity (Alpine.js or equivalent)
- If the codebase includes Livewire: stateful components, hydration/dehydration, validation UX

---

## 4. Required Skills (Integrations)

### 4.1 AI Advisory & Agent Systems

Contributors working on AI features should understand:

- **Local-first AI** integration via **Ollama**
- Cloud fallback via **AWS Bedrock (Claude models)** as documented in SPEC-006
- Prompt/context building from current run state (SEQ-006)
- Latency and timeout handling; graceful degradation modes
- Cost and usage tracking strategies (token-based accounting)

### 4.2 External Data Integration & Resilience

- HTTP client usage in Laravel (timeouts, retries)
- Circuit breaker concepts (open/half-open/closed state)
- Data normalization/mapping from third-party schemas (SEQ-007)
- Caching external data with TTL
- Rate limiting and backoff

### 4.3 OCR / Image Pipelines

- Image preprocessing basics (cropping, thresholding, normalization)
- OCR engines (e.g., Tesseract) integration considerations
- Post-OCR parsing, validation, and manual correction workflows
- Handling low-confidence extraction results safely

### 4.4 Real-time / WebSocket Messaging (if applicable)

- Publish/subscribe model (channels like `character.{id}`)
- Event serialization and payload schemas
- Connection scaling and fan-out considerations

---

## 5. Quality, Security, and Compliance Skills

### 5.1 Testing & QA

**Required**

- Unit tests for engines/services and domain models
- Integration tests for critical flows (create run, training turn, skill purchase, race complete)
- API contract tests (validation errors, auth, response schema)
- Performance tests for hotspots (prediction, ranking, caching)

**Recommended**

- Test data management: factories, seeders, fixtures
- Regression discipline around calculations and stat caps

### 5.2 Security Practices

- Input validation, request authorization, and user ownership checks
- File upload security (type/size validation; safe storage)
- Secrets management (env/config discipline)
- Safe error reporting (no sensitive data leakage)

### 5.3 Accessibility & UX Quality

- WCAG 2.2 AA basics:
  - keyboard navigation
  - focus management
  - contrast and semantic structure
- Mobile-first responsive design implementation

---

## 6. Operational & Documentation Skills

### 6.1 Documentation-Driven Development

Contributors must be able to:

- Implement features directly from PRDs/SPECS/TECH-FLOW/SEQ documents
- Keep docs updated when behavior/architecture changes
- Maintain cross-links and traceability between artifacts

### 6.2 DevOps Basics (Local & CI)

- Composer/NPM workflows; Vite bundling
- Running migrations and seeders safely
- Understanding environment configuration differences (dev/staging/prod)
- CI pipeline discipline: tests + formatting gates

---

## 7. Suggested Skill Levels by Contribution Area

| Area | Minimum | Recommended |
|---|---:|---:|
| Core Laravel development | Intermediate | Advanced |
| Calculation engines (training/race/skills) | Intermediate | Advanced (math + testing) |
| Database design & migrations | Intermediate | Advanced |
| Caching & performance | Intermediate | Advanced |
| AI integrations (Ollama/Bedrock) | Intermediate | Advanced |
| External integrations & resilience | Intermediate | Advanced |
| OCR pipeline | Beginner-Intermediate | Intermediate |
| WebSocket realtime | Beginner-Intermediate | Intermediate |
| Accessibility | Beginner-Intermediate | Intermediate |

---

## 8. Reference Pointers (Primary Docs)

- **Character Management**: PRD-001, SPEC-001, TECH-FLOW-001, SEQ-001  
- **Training Optimization**: PRD-002, SPEC-002, TECH-FLOW-002, SEQ-002  
- **Race Strategy**: PRD-003, SPEC-003, TECH-FLOW-003, SEQ-004  
- **Skill Management**: PRD-004, SPEC-004, TECH-FLOW-004, SEQ-003  
- **Support Cards**: PRD-005, SPEC-005, TECH-FLOW-005, SEQ-005  
- **AI Advisory**: PRD-006, SPEC-006, TECH-FLOW-006, SEQ-006  
- **External Integration**: PRD-007, SPEC-007, TECH-FLOW-007, SEQ-007 / SD-006  
- **Cross-cutting**: Telemetry (SEQ-011), Error handling (SEQ-014), Snapshot/Restore (SEQ-012), Migration (SEQ-015)

---

## Document Control

| Version | Date | Author | Changes |
|---|---|---|---|
| 2.0.0 | 2026-01-23 | Development Team | Rewritten to align with v2.0.0 docs: services/events/caching, AI+external+OCR integrations, testing and accessibility expectations |
