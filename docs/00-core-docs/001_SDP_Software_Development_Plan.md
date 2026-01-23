# Software Development Plan (SDP)

## Umamusume Pretty Derby Career Planner

**Document Version**: 2.1.0  
**Date**: January 23, 2026  
**Project**: UmamusumeCareerPlanner  
**Author**: Development Team  
**Status**: Current - Aligned with codebase

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Purpose and Scope](#2-purpose-and-scope)
3. [Methodology](#3-methodology)
4. [System Architecture Overview](#4-system-architecture-overview)
5. [Development Phases](#5-development-phases)
6. [Current Milestones](#6-current-milestones)
7. [Upcoming Work](#7-upcoming-work)
8. [Resource Allocation](#8-resource-allocation)
9. [Risk Management](#9-risk-management)
10. [Quality Assurance](#10-quality-assurance)
11. [Success Criteria](#11-success-criteria)
12. [Related Documents](#12-related-documents)
13. [Document Control](#13-document-control)

---

## 1. Executive Summary

This Software Development Plan (SDP) describes the development strategy, milestones, and execution status for the Umamusume Pretty Derby Career Planner application. The system is built on **Laravel 12+** with **PHP 8.2+**, **Livewire 3**, **Alpine.js**, **TailwindCSS v4**, and integrates with **AWS Bedrock Claude** models and **Ollama** for AI capabilities.

The application consolidates features from six legacy tracking applications into a unified platform, enabling players to track character progression, manage training sessions, plan race strategies, and optimize skill builds through AI-powered recommendations.

### 1.1 Project Objectives

| Objective | Status | Target |
|-----------|--------|--------|| Consolidate 6 legacy applications | Complete | Single unified platform |
| Dual storage modes (Local/Account) | Complete | Full offline support |
| AI advisory system integration | Complete | Hybrid Ollama + Bedrock |
| WCAG AA accessibility compliance | In Progress | 100% compliance |
| Performance targets (FCP < 1.5s) | In Progress | All pages optimized |

---

## 2. Purpose and Scope

### 2.1 Purpose

This SDP provides:

- Development methodology and standards
- Phase-based milestone tracking
- Risk identification and mitigation strategies
- Resource allocation guidelines
- Quality assurance requirements

### 2.2 Scope

#### In Scope

- Core gameplay tracking (characters, training, races, skills)
- AI advisory system with hybrid local/cloud architecture
- Dual storage modes (localStorage and database)
- Import/export and data migration workflows
- OCR screenshot processing pipeline
- PWA offline functionality
- Accessibility compliance (WCAG AA)

#### Out of Scope

- Native mobile applications
- Real-time multiplayer features
- Integration with game servers
- Machine learning model training

---

## 3. Methodology

### 3.1 Development Approach

The project follows an **iterative delivery model** with short milestones:

```

┌─────────────────────────────────────────────────────────────────┐
│                    ITERATIVE DELIVERY MODEL                      │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│    Plan → Develop → Test → Deploy → Review → Plan → ...        │
│                                                                  │
│    ┌──────┐    ┌──────┐    ┌──────┐    ┌──────┐    ┌──────┐    │
│    │Sprint│───▶│Sprint│───▶│Sprint│───▶│Sprint│───▶│Sprint│    │
│    │  1   │    │  2   │    │  3   │    │  4   │    │  N   │    │
│    └──────┘    └──────┘    └──────┘    └──────┘    └──────┘    │
│                                                                  │
│    2-week iterations with continuous integration                 │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘

```

### 3.2 Core Principles

| Principle | Implementation |
|-----------|----------------|| Test-First Development | Tests prioritized for new functionality |
| Continuous Integration | GitHub Actions CI/CD pipeline |
| Production Readiness | Monitoring, caching, and security checks |
| Documentation Driven | Specifications precede implementation |

### 3.3 Technology Stack

| Layer | Technology | Version |
|-------|------------|---------|| Backend Framework | Laravel | 12+ |
| PHP Runtime | PHP | 8.2+ |
| Frontend Reactivity | Livewire | 3 |
| Client Interactivity | Alpine.js | Latest |
| Styling | TailwindCSS | v4 |
| Build Tool | Vite | 7 |
| Database | MySQL/MariaDB/SQLite | - |
| AI (Local) | Ollama | Latest |
| AI (Cloud) | AWS Bedrock Claude | 4.5 |

---

## 4. System Architecture Overview

### 4.1 High-Level Architecture

```

┌─────────────────────────────────────────────────────────────────┐
│                        SYSTEM ARCHITECTURE                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌─────────────────────────────────────────────────────────┐    │
│  │                    PRESENTATION LAYER                    │    │
│  │  Blade Templates │ Livewire Components │ Alpine.js      │    │
│  └─────────────────────────────────────────────────────────┘    │
│                              ↓                                   │
│  ┌─────────────────────────────────────────────────────────┐    │
│  │                    APPLICATION LAYER                     │    │
│  │  Controllers │ Services │ AI Agents │ Actions           │    │
│  └─────────────────────────────────────────────────────────┘    │
│                              ↓                                   │
│  ┌─────────────────────────────────────────────────────────┐    │
│  │                      DOMAIN LAYER                        │    │
│  │  Eloquent Models │ Enums │ Value Objects                │    │
│  └─────────────────────────────────────────────────────────┘    │
│                              ↓                                   │
│  ┌─────────────────────────────────────────────────────────┐    │
│  │                   INFRASTRUCTURE LAYER                   │    │
│  │  Database │ Cache │ File Storage │ External APIs        │    │
│  └─────────────────────────────────────────────────────────┘    │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘

```

### 4.2 Core Modules

| Module | Description | Related Specs |
|--------|-------------|---------------|| Character Management | Character lifecycle, stats, aptitudes | SPEC-001, FLOW-001 |
| Training Optimization | Predictions, support cards, hints | SPEC-002, FLOW-002 |
| Race Strategy | Preparation, strategy, predictions | SPEC-003, FLOW-003 |
| Skill Management | Acquisition, hints, evolution | SPEC-004, FLOW-004 |
| Support Card Management | Deck composition, bonuses | SPEC-005, FLOW-005 |
| AI Advisory | Intelligent recommendations | SPEC-006, FLOW-006 |
| External Integration | APIs, OCR, WebSocket | SPEC-007, FLOW-007 |

---

## 5. Development Phases

### 5.1 Phase Overview

```

┌─────────────────────────────────────────────────────────────────┐
│                      DEVELOPMENT PHASES                          │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Phase 1: Foundation          ████████████████████ COMPLETE     │
│  Phase 2: Core Gameplay       ████████████████████ COMPLETE     │
│  Phase 3: AI Integration      ████████████████████ COMPLETE     │
│  Phase 4: Data Management     ████████████████████ COMPLETE     │
│  Phase 5: Performance         ████████████░░░░░░░░ IN PROGRESS  │
│  Phase 6: UX & Accessibility  ████████░░░░░░░░░░░░ IN PROGRESS  │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘

```

### 5.2 Phase Details

| Phase | Duration | Status | Key Deliverables |
|-------|----------|--------|------------------|| Phase 1 | Weeks 1-4 | Complete | Laravel scaffold, migrations, core models |
| Phase 2 | Weeks 5-8 | Complete | Character, training, race, skill systems |
| Phase 3 | Weeks 9-12 | Complete | AI agents, hybrid AI services, MCP |
| Phase 4 | Weeks 13-16 | Complete | Import/export, backup, OCR pipeline |
| Phase 5 | Weeks 17-20 | In Progress | APM, caching, fallback workflows |
| Phase 6 | Weeks 21-24 | In Progress | PWA, accessibility, UI polish |

---

## 6. Current Milestones

### 6.1 Phase 1: Foundation (Complete)

**Objectives:**

- Establish project foundation and development environment
- Implement core database schema and models
- Configure development tooling and CI/CD

**Deliverables:**

| Deliverable | Status | Notes |
|-------------|--------|-------|| Laravel 12 application scaffold | ✅ Complete | Full project structure |
| Environment configuration | ✅ Complete | Dev, staging, production |
| Base migrations | ✅ Complete | 18 tables per D09 |
| Core data models | ✅ Complete | Eloquent models with relationships |
| Enum definitions | ✅ Complete | StorageMode, RunStatus, etc. |

### 6.2 Phase 2: Core Gameplay (Complete)

**Objectives:**

- Implement character and career run management
- Build training session and prediction systems
- Create race, skill, and support card modules

**Deliverables:**

| Deliverable | Status | Related Spec |
|-------------|--------|--------------|
| Training sessions and predictions | ✅ Complete | SPEC-002 |
| Race strategy system | ✅ Complete | SPEC-003 |
| Skill management | ✅ Complete | SPEC-004 |
| Support card deck management | ✅ Complete | SPEC-005 |
| Career analytics and reporting | ✅ Complete | TECH-FLOW-001 |

### 6.3 Phase 3: AI and Integration (Complete)

**Objectives:**

- Implement AI advisory system with hybrid architecture
- Integrate external data sources
- Build MCP dashboards for AI management

**Deliverables:**

| Deliverable | Status | Related Spec |
|-------------|--------|--------------|
| Hybrid AI services (Ollama + Bedrock) | ✅ Complete | SPEC-006 |
| MCP integration and dashboards | ✅ Complete | FLOW-006 |
| External API integration (umapyoi.net) | ✅ Complete | SPEC-007 |
| Fallback API mechanisms | ✅ Complete | TECH-FLOW-007 |

### 6.4 Phase 4: Data Management (Complete)

**Objectives:**

- Build comprehensive import/export workflows
- Implement backup and restore functionality
- Create OCR processing pipeline

**Deliverables:**

| Deliverable | Status | Related Doc |
|-------------|--------|-------------|| Import wizard with format detection | ✅ Complete | D06 |
| Export to JSON/CSV/Excel | ✅ Complete | D06 |
| Data migration workflows | ✅ Complete | D05 |
| Backup and restore | ✅ Complete | D05 |
| OCR upload and parsing pipeline | ✅ Complete | SPEC-007 |

### 6.5 Phase 5: Performance and Reliability (In Progress)

**Objectives:**

- Implement APM and performance monitoring
- Optimize caching strategies
- Build fallback and degradation workflows

**Deliverables:**

| Deliverable | Status | Target |
|-------------|--------|--------|
| Cache monitoring and invalidation | 🔄 In Progress | Week 19 |
| Fallback and degradation workflows | 🔄 In Progress | Week 20 |
| Performance regression testing | ⏳ Pending | Week 20 |

**Performance Targets:**

| Metric | Target | Current |
|--------|--------|---------|| Page Load Time | < 2 seconds | ~2.2s |
| First Contentful Paint | < 1.5 seconds | ~1.7s |
| Time to Interactive | < 3 seconds | ~3.1s |
| API Response Time | < 200ms | ~180ms |

### 6.6 Phase 6: UX and Accessibility (In Progress)

**Objectives:**

- Complete PWA offline functionality
- Achieve WCAG AA compliance
- Polish UI and user experience

**Deliverables:**

| Deliverable | Status | Target |
|-------------|--------|--------|
| Accessibility pages and keyboard shortcuts | 🔄 In Progress | Week 23 |
| UI polish and refinement | ⏳ Pending | Week 24 |
| Dark mode optimization | ⏳ Pending | Week 24 |

---

## 7. Upcoming Work

### 7.1 Near-Term Priorities (Weeks 18-20)

| Priority | Task | Owner | Target |
|----------|------|-------|--------|
| P0 | Cache invalidation optimization | Backend Team | Week 19 |
| P1 | Fallback workflow testing | QA Team | Week 20 |
| P1 | Performance regression automation | DevOps | Week 20 |

### 7.2 Medium-Term Priorities (Weeks 21-24)

| Priority | Task | Owner | Target |
|----------|------|-------|--------|
| P0 | Background sync implementation | Frontend Team | Week 22 |
| P1 | Accessibility audits | QA Team | Week 23 |
| P1 | Accessibility remediation | Frontend Team | Week 23 |
| P2 | UI polish and refinement | Design Team | Week 24 |

### 7.3 Documentation Updates

| Document | Update Required | Target |
|----------|-----------------|--------|| SPEC-006 (AI Advisory) | Add cost monitoring section | Week 19 |
| TECH-FLOW-007 | Document OCR accuracy metrics | Week 20 |
| WF-012 (AI Advisor Interface) | Update with MCP dashboards | Week 21 |
| D17 (User Manual) | Add PWA usage instructions | Week 23 |

---

## 8. Resource Allocation

### 8.1 Team Structure

```

┌─────────────────────────────────────────────────────────────────┐
│                        TEAM STRUCTURE                            │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│                      ┌──────────────┐                           │
│                      │ Project Lead │                           │
│                      └──────┬───────┘                           │
│                             │                                    │
│         ┌───────────────────┼───────────────────┐               │
│         │                   │                   │               │
│   ┌─────┴─────┐       ┌─────┴─────┐       ┌─────┴─────┐        │
│   │ Backend   │       │ Frontend  │       │ QA        │        │
│   │ Developer │       │ Developer │       │ Engineer  │        │
│   └───────────┘       └───────────┘       └───────────┘        │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘

```

### 8.2 Role Responsibilities

| Role | Responsibilities |
|------|------------------|
| Project Lead | Overall coordination, stakeholder communication, milestone tracking |
| Backend Developer | Laravel, Livewire, database, services, AI integration |
| Frontend Developer | Alpine.js, TailwindCSS, Blade components, PWA |
| QA Engineer | Testing, accessibility verification, performance monitoring |
| DevOps | CI/CD, deployment, monitoring, infrastructure |

---

## 9. Risk Management

### 9.1 Risk Register

| Risk ID | Risk | Probability | Impact | Mitigation |
|---------|------|-------------|--------|------------|
| R-002 | External API instability | Medium | Medium | Cache + fallback services in `app/Services/ExternalAPI` |
| R-003 | OCR accuracy issues | Medium | Medium | Preprocessing, parser validation, manual correction |
| R-004 | localStorage quota limits | Low | High | Quota warnings, IndexedDB migration path |
| R-005 | Legacy data incompatibility | Low | Medium | Version exports, migration adapters |
| R-006 | Accessibility regression | Low | High | Automated axe-core tests in CI |

### 9.2 Risk Matrix

```

┌─────────────────────────────────────────────────────────────────┐
│                         RISK MATRIX                              │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Impact                                                          │
│    ▲                                                             │
│    │                                                             │
│ High │  R-004        │  R-001                                   │
│    │  R-006        │                                            │
│    │               │                                            │
│ Med │  R-005        │  R-002                                    │
│    │               │  R-003                                     │
│    │               │                                            │
│ Low │               │                                            │
│    │               │                                            │
│    └───────────────┴─────────────────────────────▶               │
│         Low         Medium         High                          │
│                   Probability                                    │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘

```

### 9.3 Contingency Plans

| Risk | Contingency |
|------|-------------|
| External API down | Serve cached data, display data freshness warnings |
| OCR failures | Provide manual data entry as fallback |
| Storage migration | Accelerate IndexedDB implementation |
| Performance issues | Implement server-side pagination, reduce bundle size |

---

## 10. Quality Assurance

### 10.1 Testing Strategy

| Test Type | Tool | Coverage Target |
|-----------|------|-----------------|
| Feature Tests | Pest | 80%+ |
| E2E Tests | Playwright | Critical paths 100% |
| Accessibility | axe-core | WCAG AA 100% |
| Visual Regression | Playwright | Key pages |

### 10.2 Critical User Flows

| Flow ID | Description | Test Status |
|---------|-------------|-------------|
| UF-002 | Career setup and creation | ✅ Covered |
| UF-003 | Training day flow | ✅ Covered |
| UF-004 | Race day flow | ✅ Covered |
| UF-005 | Skill management flow | ✅ Covered |
| UF-006 | Support deck building | ✅ Covered |
| UF-007 | AI advisor journey | 🔄 In Progress |
| UF-008 | OCR and data import | 🔄 In Progress |

### 10.3 Definition of Done

Each task is complete when:

- [ ] Code follows PSR-12 standards
- [ ] Unit/feature tests pass with >80% coverage
- [ ] All interactive elements have `data-testid` attributes
- [ ] Playwright E2E coverage exists for key actions
- [ ] axe-core accessibility scan passes
- [ ] Code follows Livewire 3 + Alpine.js best practices
- [ ] TailwindCSS classes use design tokens
- [ ] Documentation is updated
- [ ] Works in both dark and light modes
- [ ] Responsive on mobile devices (320px-2560px)

---

## 11. Success Criteria

### 11.1 MVP Launch Criteria

| Criterion | Target | Status |
|-----------|--------|--------|
| Selected P1 requirements implemented | 80%+ | ✅ Complete |
| E2E tests pass for critical flows | 100% | 🔄 In Progress |
| WCAG AA compliance verified | 100% | 🔄 In Progress |
| Performance targets met | FCP < 1.5s, TTI < 3s | 🔄 In Progress |
| Documentation complete | All SPECs, FLOWs | ✅ Complete |

### 11.2 Key Performance Indicators

| KPI | Target | Current | Status |
|-----|--------|---------|--------|
| First Contentful Paint | < 1.5 seconds | ~1.7s | 🔄 Optimizing |
| Time to Interactive | < 3 seconds | ~3.1s | 🔄 Optimizing |
| Accessibility Score | 100% WCAG AA | ~92% | 🔄 Improving |
| Test Coverage | > 80% | 85% | ✅ Met |
| Error Rate | < 1% | 0.3% | ✅ Met |

---

## 12. Related Documents

### 12.1 Specifications

| Document | Description |
|----------|-------------|
| SPEC-003 | Race Strategy Technical Specification |
| SPEC-004 | Skill Management Technical Specification |
| SPEC-005 | Support Card Management Technical Specification |
| SPEC-006 | AI Advisory Technical Specification |
| SPEC-007 | External Integration Technical Specification |

### 12.2 Design Documents

| Document | Description |
|----------|-------------|
| SEQ-001 to SEQ-015 | Sequence Diagrams |
| WF-001 to WF-011 | Wireframe Specifications |
| UF-001 to UF-008 | User Flow Diagrams |

### 12.3 Planning Documents

| Document | Description |
|----------|-------------|
| D03 | System Requirements Specifications |
| D04 | System Design Specifications |
| D05 | Data Migration Plan |
| D06 | Data Migration Specifications |
| D07 | System Integration Plan |
| D08 | System Integration Specifications |
| D09 | Database Documentation |
| D10 | Source Code Documentation |

### 12.4 PRD Documents

| Document | Description |
|----------|-------------|
| PRD-003 | Race Strategy |
| PRD-004 | Skill Management |
| PRD-005 | Support Card Management |
| PRD-006 | AI Advisory |
| PRD-007 | External Integration |

---

## 13. Document Control

### 13.1 Revision History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 2.1.0 | 2026-01-23 | Development Team | Updated phases to match current implementation |
| 2.0.0 | 2026-01-14 | Development Team | Prior plan revision |
| 1.0.0 | 2026-01-03 | Development Team | Initial draft |

### 13.2 Approval

| Role | Name | Signature | Date |
|------|------|-----------|------|
| Technical Lead | | | |
| QA Lead | | | |

### 13.3 Distribution

| Recipient | Purpose |
|-----------|---------|
| QA Team | Testing planning |
| Stakeholders | Progress tracking |
| Documentation Team | Reference documentation |

---

*This SDP reflects the current implementation status and near-term priorities as of January 23, 2026. Updates are made at each phase milestone or when significant changes occur.*
