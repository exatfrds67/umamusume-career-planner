# Requirements Document: Umamusume Career Planner v2.1.0

**Document Version**: 2.1.0  
**Date**: January 25, 2026  
**Project**: UmamusumeCareerPlanner  
**Status**: Active - Requirements Definition  
**Standard**: IEEE 29148-2018  
**Related Documents**: SDP v2.1, BRS v2.1, SRS v2.1, SDS v2.1, SPEC-001 to SPEC-007, PRD-001 to PRD-007

---

## Table of Contents

1. [Introduction](#1-introduction)
2. [System Overview](#2-system-overview)
3. [Glossary](#3-glossary)
4. [Character Management Requirements](#4-character-management-requirements)
5. [Training Optimization Requirements](#5-training-optimization-requirements)
6. [Race Strategy Requirements](#6-race-strategy-requirements)
7. [Skill Management Requirements](#7-skill-management-requirements)
8. [Support Card Management Requirements](#8-support-card-management-requirements)
9. [AI Advisory Requirements](#9-ai-advisory-requirements)
10. [External Integration Requirements](#10-external-integration-requirements)
11. [Data Management Requirements](#11-data-management-requirements)
12. [Storage Mode Requirements](#12-storage-mode-requirements)
13. [Performance Requirements](#13-performance-requirements)
14. [Accessibility Requirements](#14-accessibility-requirements)
15. [Progressive Web App Requirements](#15-progressive-web-app-requirements)
16. [Security Requirements](#16-security-requirements)
17. [Testing Requirements](#17-testing-requirements)
18. [Non-Functional Requirements](#18-non-functional-requirements)
19. [Traceability Matrix](#19-traceability-matrix)
20. [Verification and Validation](#20-verification-and-validation)

---

## 1. Introduction

### 1.1 Purpose

This Requirements Document specifies the functional and non-functional requirements for the Umamusume Career Planner v2.1.0 release. This version builds upon the v2.0.0 foundation with enhanced performance optimization, complete accessibility compliance, improved PWA capabilities, and advanced analytics features.

### 1.2 Scope

**v2.0.0 Implemented Features (Complete):**

- ✅ Complete character management system with stats, aptitudes, and factors
- ✅ Training optimization with Neuron AI framework
- ✅ Race strategy system with performance predictions
- ✅ Comprehensive skill management with SP optimization
- ✅ Support card deck management with synergy scoring
- ✅ Hybrid AI routing (Ollama + AWS Bedrock)
- ✅ OCR pipeline with Tesseract and GD preprocessing
- ✅ Complete data management (import/export/migration/backup)
- ✅ PWA foundation with service workers and offline capabilities
- ✅ Laravel Sanctum authentication
- ✅ Comprehensive testing framework (Pest PHP, Playwright)

**v2.1.0 Focus Areas (In Progress):**

- 🔄 Performance optimization and APM monitoring
- 🔄 Complete WCAG AA accessibility compliance
- 🔄 Enhanced PWA offline functionality
- 🔄 Advanced analytics and reporting
- 🔄 Cache optimization and invalidation strategies

### 1.3 Definitions and Acronyms

| Term | Definition |
|------|------------|
| Career Run | A complete training progression from Junior to URA Finals (78 turns) |
| Uma Musume | A horse girl character from the Uma Musume: Pretty Derby game |
| SP (Skill Points) | Points earned from races and events, spent to purchase skills |
| Stat Max | Maximum stat value (1200) - hard cap, no values above allowed |
| URA Finale | The final race series at the end of Senior Year |
| OCR | Optical Character Recognition for screenshot data extraction |
| MCP | Model Context Protocol for AI tool integration |
| Neuron AI | Agent orchestration framework for AI capabilities |
| PWA | Progressive Web Application for offline functionality |
| APM | Application Performance Monitoring |

### 1.4 Technology Stack

| Layer | Technology | Version | Purpose |
|-------|------------|---------|---------|
| Backend Framework | Laravel | 12+ | Application foundation |
| PHP Runtime | PHP | 8.2+ | Server-side logic |
| Frontend Reactivity | Livewire | 3 | Server-driven UI |
| Client Interactivity | Alpine.js | Latest | Client-side interactions |
| Styling | TailwindCSS | v4 | Utility-first CSS |
| Build Tool | Vite | 7 | Asset compilation |
| Database | MySQL/MariaDB | 8.0+ | Primary data store |
| Cache | Redis | 7+ | Caching and queues |
| AI (Local) | Ollama | Latest | Local AI inference |
| AI (Cloud) | AWS Bedrock | Claude 4.5 | Cloud AI fallback |
| WebSocket | Laravel Reverb | Latest | Real-time updates |

---

## 2. System Overview

### 2.1 System Context

The Umamusume Career Planner consolidates features from six legacy tracking applications into a unified platform, enabling players to track character progression, manage training sessions, plan race strategies, and optimize skill builds through AI-powered recommendations.

### 2.2 Core Modules

| Module | Description | PRD Reference | SPEC Reference |
|--------|-------------|---------------|----------------|
| Character Management | Character lifecycle, stats, aptitudes, factors | PRD-001 | SPEC-001 |
| Training Optimization | Predictions, support cards, hints, AI recommendations | PRD-002 | SPEC-002 |
| Race Strategy | Preparation, strategy, win probability | PRD-003 | SPEC-003 |
| Skill Management | Acquisition, hints, evolution, SP optimization | PRD-004 | SPEC-004 |
| Support Card Management | Deck composition, bonuses, synergy | PRD-005 | SPEC-005 |
| AI Advisory | Intelligent recommendations, hybrid AI | PRD-006 | SPEC-006 |
| External Integration | APIs, OCR, WebSocket, data sync | PRD-007 | SPEC-007 |

### 2.3 System Architecture

```

┌─────────────────────────────────────────────────────────────────┐
│                        SYSTEM ARCHITECTURE                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌─────────────────────────────────────────────────────────┐    │
│  │                    PRESENTATION LAYER                    │    │
│  │  Blade Templates │ Livewire Components │ Alpine.js      │    │
│  │  TailwindCSS v4  │ PWA Service Worker                   │    │
│  └─────────────────────────────────────────────────────────┘    │
│                              ↓                                   │
│  ┌─────────────────────────────────────────────────────────┐    │
│  │                    APPLICATION LAYER                     │    │
│  │  Controllers │ Services │ AI Agents │ Form Requests     │    │
│  └─────────────────────────────────────────────────────────┘    │
│                              ↓                                   │
│  ┌─────────────────────────────────────────────────────────┐    │
│  │                      DOMAIN LAYER                        │    │
│  │  Eloquent Models │ Enums │ Value Objects │ Repositories │    │
│  └─────────────────────────────────────────────────────────┘    │
│                              ↓                                   │
│  ┌─────────────────────────────────────────────────────────┐    │
│  │                   INFRASTRUCTURE LAYER                   │    │
│  │  MySQL │ Redis │ File Storage │ External APIs │ AI      │    │
│  └─────────────────────────────────────────────────────────┘    │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘

```

---

## 3. Glossary

### 3.1 Game Terms

| Term | Japanese | Definition |
|------|----------|------------|
| Speed | スピード | Determines maximum running speed (0-1200) |
| Stamina | スタミナ | Determines HP/effective stamina (0-1200) |
| Power | パワー | Affects acceleration and lane-changing (0-1200) |
| Guts | 根性 | Affects last spurt and stamina consumption (0-1200) |
| Wit | 賢さ | Affects skill activation rate (0-1200) |
| Nige | 逃げ | Front Runner running style |
| Senkou | 先行 | Pace Chaser running style |
| Sashi | 差し | Late Surger running style |
| Oikomi | 追込 | End Closer running style |
| Factor | 因子 | Inherited trait providing stat/skill bonuses |

### 3.2 Aptitude Grades

| Grade | Effectiveness | Value Range |
|-------|---------------|-------------|
| SS | 120% | Exceptional |
| S | 110% | Excellent |
| A | 100% | Standard |
| B | 90% | Above Average |
| C | 80% | Average |
| D | 70% | Below Average |
| E | 60% | Poor |
| F | 50% | Very Poor |
| G | 40% | Minimal |

### 3.3 Stat Grade Thresholds

| Grade | Value Range |
|-------|-------------|
| SS | 1100+ |
| S | 950-1099 |
| A | 850-949 |
| B+ | 750-849 |
| B | 650-749 |
| C+ | 550-649 |
| C | 450-549 |
| D+ | 350-449 |
| D | 250-349 |
| E | 150-249 |
| F | 0-149 |

---

## 4. Character Management Requirements

**Reference Documents**: PRD-001, SPEC-001, FLOW-001, WF-002, WF-003, SEQ-001, UF-002

### 4.1 Character Creation [REQ-CM-001]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-CM-001.1 | System SHALL provide a 4-step creation wizard (Trainee, Scenario, Parents, Deck) | P0 | Complete |
| REQ-CM-001.2 | System SHALL support trainee selection with searchable grid interface | P0 | Complete |
| REQ-CM-001.3 | System SHALL support scenario selection (URA Finals, Unity Cup, Grand Masters) | P0 | Complete |
| REQ-CM-001.4 | System SHALL calculate factor inheritance from 2 parents and 4 grandparents | P0 | Complete |
| REQ-CM-001.5 | System SHALL validate support deck composition (exactly 6 cards: 5 owned + 1 borrowed) | P0 | Complete |
| REQ-CM-001.6 | System SHALL preview factor bonuses before character creation | P1 | Complete |
| REQ-CM-001.7 | System SHALL generate UUID for Local mode characters | P0 | Complete |

### 4.2 Stat Tracking [REQ-CM-002]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-CM-002.1 | System SHALL track five core stats: Speed, Stamina, Power, Guts, Wit | P0 | Complete |
| REQ-CM-002.2 | System SHALL enforce stat range 0-1200 (hard cap) | P0 | Complete |
| REQ-CM-002.3 | System SHALL display stat grades based on value thresholds | P0 | Complete |
| REQ-CM-002.4 | System SHALL track energy level (0-100 scale) | P0 | Complete |
| REQ-CM-002.5 | System SHALL track mood status (Very Bad, Bad, Normal, Good, Great) | P0 | Complete |
| REQ-CM-002.6 | System SHALL track active conditions (buffs/debuffs) | P1 | Complete |
| REQ-CM-002.7 | System SHALL apply mood modifiers: Great +4%, Good +2%, Normal 0%, Bad -2%, Awful -4% | P0 | Complete |

### 4.3 Aptitude Management [REQ-CM-003]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-CM-003.1 | System SHALL track aptitude grades for Surface (Turf, Dirt) | P0 | Complete |
| REQ-CM-003.2 | System SHALL track aptitude grades for Distance (Sprint, Mile, Medium, Long) | P0 | Complete |
| REQ-CM-003.3 | System SHALL track aptitude grades for Running Style (Nige, Senkou, Sashi, Oikomi) | P0 | Complete |
| REQ-CM-003.4 | System SHALL apply aptitude effectiveness multipliers in calculations | P0 | Complete |
| REQ-CM-003.5 | System SHALL support aptitude modification via inheritance factors | P1 | Complete |

### 4.4 Factor Inheritance [REQ-CM-004]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-CM-004.1 | System SHALL calculate factor bonuses: ★☆☆=+5, ★★☆=+12, ★★★=+21 | P0 | Complete |
| REQ-CM-004.2 | System SHALL aggregate factors from 6 total sources (2 parents, 4 grandparents) | P0 | Complete |
| REQ-CM-004.3 | System SHALL support stat factors (Speed, Stamina, Power, Guts, Wit) | P0 | Complete |
| REQ-CM-004.4 | System SHALL support aptitude factors for grade bonuses | P1 | Complete |
| REQ-CM-004.5 | System SHALL store factor inheritance history for reference | P1 | Complete |

### 4.5 Goal Management [REQ-CM-005]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-CM-005.1 | System SHALL support stat target goals (e.g., Speed ≥ 1000) | P1 | Complete |
| REQ-CM-005.2 | System SHALL support race placement goals (e.g., Win G1 Race) | P1 | Complete |
| REQ-CM-005.3 | System SHALL track goal progress with visual indicators | P1 | Complete |
| REQ-CM-005.4 | System SHALL mark goals as Completed, On Track, or At Risk | P1 | Complete |
| REQ-CM-005.5 | System SHALL trigger AI alerts for at-risk goals | P2 | Complete |

### 4.6 Character Snapshots [REQ-CM-006]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-CM-006.1 | System SHALL support character state snapshots for versioning | P1 | Complete |
| REQ-CM-006.2 | System SHALL store snapshot metadata (turn number, timestamp) | P1 | Complete |
| REQ-CM-006.3 | System SHALL allow comparison between snapshots | P2 | In Progress |

---

## 5. Training Optimization Requirements

**Reference Documents**: PRD-002, SPEC-002, FLOW-002, WF-004, WF-005, SEQ-002, UF-003, TECH-FLOW-002

### 5.1 Training Prediction [REQ-TO-001]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-TO-001.1 | System SHALL calculate base stat gains for all 5 training facilities | P0 | Complete |
| REQ-TO-001.2 | System SHALL apply support card bonus multipliers | P0 | Complete |
| REQ-TO-001.3 | System SHALL apply friendship training multipliers (bond ≥ 80%) | P0 | Complete |
| REQ-TO-001.4 | System SHALL apply mood modifiers to training gains | P0 | Complete |
| REQ-TO-001.5 | System SHALL calculate predictions for all facilities simultaneously | P0 | Complete |
| REQ-TO-001.6 | System SHALL cache prediction results (5-minute TTL) | P1 | Complete |
| REQ-TO-001.7 | System SHALL invalidate cache on character state change | P1 | Complete |

### 5.2 Risk Assessment [REQ-TO-002]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-TO-002.1 | System SHALL calculate failure risk based on energy level | P0 | Complete |
| REQ-TO-002.2 | System SHALL apply condition modifiers to risk (Overweight +10%, Lazy +5%) | P0 | Complete |
| REQ-TO-002.3 | System SHALL display risk indicators (Green <15%, Amber 15-40%, Red >40%) | P0 | Complete |
| REQ-TO-002.4 | System SHALL factor mood status into risk calculation | P1 | Complete |
| REQ-TO-002.5 | System SHALL warn users when risk exceeds 40% | P1 | Complete |

### 5.3 Skill Hint Tracking [REQ-TO-003]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-TO-003.1 | System SHALL calculate hint probability per training facility | P0 | Complete |
| REQ-TO-003.2 | System SHALL identify support cards with "Hint Lv Up" ability | P0 | Complete |
| REQ-TO-003.3 | System SHALL display hint icons for facilities with hint chances | P1 | Complete |
| REQ-TO-003.4 | System SHALL track hint acquisition per skill | P1 | Complete |
| REQ-TO-003.5 | System SHALL cap hint levels at 2 (40% maximum discount) | P0 | Complete |

### 5.4 Training Recommendations [REQ-TO-004]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-TO-004.1 | System SHALL rank training options by recommendation score | P0 | Complete |
| REQ-TO-004.2 | System SHALL highlight AI-recommended training with badge | P1 | Complete |
| REQ-TO-004.3 | System SHALL provide reasoning for recommendations | P1 | Complete |
| REQ-TO-004.4 | System SHALL consider goal progress in recommendations | P1 | Complete |
| REQ-TO-004.5 | System SHALL integrate Neuron TrainingAdvisorAgent | P1 | Complete |

### 5.5 Training Execution [REQ-TO-005]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-TO-005.1 | System SHALL record training sessions with stat gains | P0 | Complete |
| REQ-TO-005.2 | System SHALL update character stats atomically (transaction) | P0 | Complete |
| REQ-TO-005.3 | System SHALL record support card bonuses applied | P1 | Complete |
| REQ-TO-005.4 | System SHALL advance turn counter after training | P0 | Complete |
| REQ-TO-005.5 | System SHALL dispatch TrainingCompleted event | P1 | Complete |
| REQ-TO-005.6 | System SHALL process skill hint acquisitions | P1 | Complete |

---

## 6. Race Strategy Requirements

**Reference Documents**: PRD-003, SPEC-003, FLOW-003, WF-006, WF-007, SEQ-004, UF-004, TECH-FLOW-003

### 6.1 Race Calendar [REQ-RS-001]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-RS-001.1 | System SHALL display race calendar with filtering by grade/distance | P0 | Complete |
| REQ-RS-001.2 | System SHALL show upcoming races with days remaining | P0 | Complete |
| REQ-RS-001.3 | System SHALL display race requirements (stats, aptitudes) | P0 | Complete |
| REQ-RS-001.4 | System SHALL highlight races matching character aptitudes | P1 | Complete |
| REQ-RS-001.5 | System SHALL detect scheduling conflicts | P2 | Complete |

### 6.2 Readiness Assessment [REQ-RS-002]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-RS-002.1 | System SHALL calculate readiness score (0-100%) | P0 | Complete |
| REQ-RS-002.2 | System SHALL classify readiness: Excellent (≥85%), Good (70-84%), Fair (55-69%), Poor (<55%) | P0 | Complete |
| REQ-RS-002.3 | System SHALL evaluate stat fit vs race requirements | P0 | Complete |
| REQ-RS-002.4 | System SHALL apply aptitude modifiers to readiness | P0 | Complete |
| REQ-RS-002.5 | System SHALL consider active skills in readiness | P1 | Complete |
| REQ-RS-002.6 | System SHALL factor mood/condition into readiness | P1 | Complete |

### 6.3 Win Probability [REQ-RS-003]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-RS-003.1 | System SHALL calculate win probability percentage | P1 | Complete |
| REQ-RS-003.2 | System SHALL compare stats against generated competitors | P1 | Complete |
| REQ-RS-003.3 | System SHALL apply aptitude effectiveness to probability | P1 | Complete |
| REQ-RS-003.4 | System SHALL factor skill synergy into probability | P1 | Complete |
| REQ-RS-003.5 | System SHALL apply RNG variance (±5%) to probability | P2 | Complete |

### 6.4 Running Style Optimization [REQ-RS-004]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-RS-004.1 | System SHALL recommend optimal running style per race | P1 | Complete |
| REQ-RS-004.2 | System SHALL analyze character aptitudes for style recommendation | P1 | Complete |
| REQ-RS-004.3 | System SHALL display reasoning for style recommendation | P1 | Complete |
| REQ-RS-004.4 | System SHALL integrate RaceStrategyAgent for AI recommendations | P1 | Complete |

### 6.5 Race Result Recording [REQ-RS-005]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-RS-005.1 | System SHALL record race results with placement | P0 | Complete |
| REQ-RS-005.2 | System SHALL calculate and award rewards (fans, SP, stats) | P0 | Complete |
| REQ-RS-005.3 | System SHALL update character grade based on results | P1 | Complete |
| REQ-RS-005.4 | System SHALL check scenario objectives on race completion | P1 | Complete |
| REQ-RS-005.5 | System SHALL dispatch RaceCompleted event | P1 | Complete |

---

## 7. Skill Management Requirements

**Reference Documents**: PRD-004, SPEC-004, FLOW-004, WF-008, WF-009, SEQ-003, UF-005, TECH-FLOW-004

### 7.1 Skill Catalog [REQ-SM-001]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-SM-001.1 | System SHALL maintain skill catalog with 500+ skills | P0 | Complete |
| REQ-SM-001.2 | System SHALL support skill search by English and Japanese names | P0 | Complete |
| REQ-SM-001.3 | System SHALL categorize skills: Normal, Rare, Unique | P0 | Complete |
| REQ-SM-001.4 | System SHALL display skill effects and activation conditions | P1 | Complete |
| REQ-SM-001.5 | System SHALL filter skills by type (Speed, Stamina, Power, Guts, Wit, Recovery) | P1 | Complete |

### 7.2 Skill Acquisition [REQ-SM-002]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-SM-002.1 | System SHALL track skill acquisitions per character | P0 | Complete |
| REQ-SM-002.2 | System SHALL enforce SP budget validation before acquisition | P0 | Complete |
| REQ-SM-002.3 | System SHALL apply hint discounts to SP cost (20% per hint, 40% max) | P0 | Complete |
| REQ-SM-002.4 | System SHALL record acquisition turn number | P1 | Complete |
| REQ-SM-002.5 | System SHALL mark hint records as used after acquisition | P1 | Complete |

### 7.3 Skill Evolution [REQ-SM-003]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-SM-003.1 | System SHALL support skill evolution (Normal → Rare) | P1 | Complete |
| REQ-SM-003.2 | System SHALL validate evolution requirements before allowing | P1 | Complete |
| REQ-SM-003.3 | System SHALL atomically replace base skill with evolved version | P1 | Complete |
| REQ-SM-003.4 | System SHALL log evolution history | P2 | Complete |

### 7.4 SP Optimization [REQ-SM-004]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-SM-004.1 | System SHALL track total SP available | P0 | Complete |
| REQ-SM-004.2 | System SHALL track total SP spent | P0 | Complete |
| REQ-SM-004.3 | System SHALL provide SP budget optimization recommendations | P1 | Complete |
| REQ-SM-004.4 | System SHALL warn when SP insufficient for planned acquisitions | P1 | Complete |

### 7.5 Skill Recommendations [REQ-SM-005]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-SM-005.1 | System SHALL recommend skills based on race targets | P1 | Complete |
| REQ-SM-005.2 | System SHALL integrate SkillRecommendationAgent | P1 | Complete |
| REQ-SM-005.3 | System SHALL prioritize skills with available hints | P2 | Complete |
| REQ-SM-005.4 | System SHALL consider running style in recommendations | P2 | Complete |

---

## 8. Support Card Management Requirements

**Reference Documents**: PRD-005, SPEC-005, FLOW-005, WF-010, WF-011, SEQ-005, UF-006, TECH-FLOW-005

### 8.1 Card Inventory [REQ-SC-001]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-SC-001.1 | System SHALL maintain support card inventory (200+ cards) | P0 | Complete |
| REQ-SC-001.2 | System SHALL track card rarity (R, SR, SSR) | P0 | Complete |
| REQ-SC-001.3 | System SHALL track limit break level (0-4 stars) | P0 | Complete |
| REQ-SC-001.4 | System SHALL track card specialization (Speed, Stamina, Power, Guts, Wit, Friend, Group) | P0 | Complete |
| REQ-SC-001.5 | System SHALL display meta tier rankings (SS, S, A, B) | P1 | Complete |

### 8.2 Deck Composition [REQ-SC-002]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-SC-002.1 | System SHALL validate deck size (exactly 6 cards) | P0 | Complete |
| REQ-SC-002.2 | System SHALL enforce ownership rule (5 owned + 1 borrowed) | P0 | Complete |
| REQ-SC-002.3 | System SHALL prevent duplicate cards in deck | P0 | Complete |
| REQ-SC-002.4 | System SHALL warn on type imbalance | P1 | Complete |
| REQ-SC-002.5 | System SHALL calculate deck synergy score | P1 | Complete |

### 8.3 Bond Tracking [REQ-SC-003]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-SC-003.1 | System SHALL track bond level per support card (0-100%) | P0 | Complete |
| REQ-SC-003.2 | System SHALL apply friendship training at bond ≥ 80% | P0 | Complete |
| REQ-SC-003.3 | System SHALL track bond milestones (20%, 40%, 60%, 80%) | P1 | Complete |
| REQ-SC-003.4 | System SHALL award milestone rewards (hints, stats, events) | P1 | Complete |

### 8.4 Card Bonuses [REQ-SC-004]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-SC-004.1 | System SHALL calculate stat bonuses per card | P0 | Complete |
| REQ-SC-004.2 | System SHALL aggregate bonuses for training predictions | P0 | Complete |
| REQ-SC-004.3 | System SHALL apply unique card abilities | P1 | Complete |
| REQ-SC-004.4 | System SHALL factor limit break level into bonus calculations | P1 | Complete |

### 8.5 Meta Tier Sync [REQ-SC-005]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-SC-005.1 | System SHALL sync meta tier rankings from external sources | P1 | Complete |
| REQ-SC-005.2 | System SHALL cache tier data (24-hour TTL) | P1 | Complete |
| REQ-SC-005.3 | System SHALL display tier badges on cards | P1 | Complete |
| REQ-SC-005.4 | System SHALL allow manual tier override | P2 | In Progress |

---

## 9. AI Advisory Requirements

**Reference Documents**: PRD-006, SPEC-006, FLOW-006, WF-012, SEQ-006, UF-007, TECH-FLOW-006

### 9.1 Hybrid AI Architecture [REQ-AI-001]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-AI-001.1 | System SHALL implement hybrid AI with Ollama (local) primary | P0 | Complete |
| REQ-AI-001.2 | System SHALL implement AWS Bedrock (cloud) fallback | P0 | Complete |
| REQ-AI-001.3 | System SHALL route requests based on complexity analysis | P1 | Complete |
| REQ-AI-001.4 | System SHALL fallback to Bedrock when Ollama unavailable | P0 | Complete |
| REQ-AI-001.5 | System SHALL support configurable AI provider selection | P1 | Complete |

### 9.2 Neuron AI Agents [REQ-AI-002]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-AI-002.1 | System SHALL implement TrainingAdvisorAgent | P0 | Complete |
| REQ-AI-002.2 | System SHALL implement RaceStrategyAgent | P0 | Complete |
| REQ-AI-002.3 | System SHALL implement SkillRecommendationAgent | P1 | Complete |
| REQ-AI-002.4 | System SHALL implement CareerPlanningAgent | P1 | Complete |
| REQ-AI-002.5 | System SHALL provide agent tools (GetCharacterStats, GetTrainingPredictions, GetRaceRequirements, GetSkillCatalog) | P1 | Complete |

### 9.3 MCP Integration [REQ-AI-003]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-AI-003.1 | System SHALL integrate Memory MCP server for conversation context | P1 | Complete |
| REQ-AI-003.2 | System SHALL integrate Filesystem MCP server for file operations | P1 | Complete |
| REQ-AI-003.3 | System SHALL integrate Fetch MCP server for HTTP requests | P1 | Complete |
| REQ-AI-003.4 | System SHALL monitor MCP tool usage metrics | P2 | Complete |
| REQ-AI-003.5 | System SHALL provide MCP health dashboard | P2 | Complete |

### 9.4 AI Conversation [REQ-AI-004]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-AI-004.1 | System SHALL maintain conversation history per session | P1 | Complete |
| REQ-AI-004.2 | System SHALL provide context-aware responses | P0 | Complete |
| REQ-AI-004.3 | System SHALL support quick topic buttons (Training, Race, Skills, Career) | P1 | Complete |
| REQ-AI-004.4 | System SHALL display confidence scores for recommendations | P1 | Complete |
| REQ-AI-004.5 | System SHALL persist conversation history (90-day retention) | P2 | Complete |

### 9.5 AI Cost Management [REQ-AI-005]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-AI-005.1 | System SHALL track token usage per request | P1 | Complete |
| REQ-AI-005.2 | System SHALL estimate costs for cloud AI usage | P1 | Complete |
| REQ-AI-005.3 | System SHALL enforce configurable cost thresholds | P2 | Complete |
| REQ-AI-005.4 | System SHALL provide usage dashboards | P2 | In Progress |
| REQ-AI-005.5 | System SHALL alert on budget threshold exceeded | P2 | In Progress |

---

## 10. External Integration Requirements

**Reference Documents**: PRD-007, SPEC-007, FLOW-007, SEQ-007, UF-008, TECH-FLOW-007

### 10.1 External API Integration [REQ-EI-001]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-EI-001.1 | System SHALL integrate with umapyoi.net API (primary source) | P0 | Complete |
| REQ-EI-001.2 | System SHALL integrate with umamusumedb.com API (fallback) | P1 | Complete |
| REQ-EI-001.3 | System SHALL implement circuit breaker pattern | P0 | Complete |
| REQ-EI-001.4 | System SHALL cache API responses (24-hour TTL) | P1 | Complete |
| REQ-EI-001.5 | System SHALL handle API timeouts gracefully | P0 | Complete |

### 10.2 Circuit Breaker [REQ-EI-002]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-EI-002.1 | System SHALL implement CLOSED state (normal operation) | P0 | Complete |
| REQ-EI-002.2 | System SHALL implement OPEN state after failure threshold (5 failures) | P0 | Complete |
| REQ-EI-002.3 | System SHALL implement HALF_OPEN state for recovery checks | P0 | Complete |
| REQ-EI-002.4 | System SHALL return cached data when circuit is OPEN | P0 | Complete |
| REQ-EI-002.5 | System SHALL configure recovery timeout (60 seconds) | P1 | Complete |

### 10.3 OCR Processing [REQ-EI-003]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-EI-003.1 | System SHALL accept screenshot uploads (JPEG, PNG) | P1 | Complete |
| REQ-EI-003.2 | System SHALL preprocess images (resize, grayscale, threshold) | P1 | Complete |
| REQ-EI-003.3 | System SHALL extract text using Tesseract OCR | P1 | Complete |
| REQ-EI-003.4 | System SHALL parse extracted text into structured data | P1 | Complete |
| REQ-EI-003.5 | System SHALL validate parsed data with confidence scoring | P1 | Complete |
| REQ-EI-003.6 | System SHALL provide manual correction UI for low confidence (<85%) | P1 | Complete |

### 10.4 WebSocket Integration [REQ-EI-004]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-EI-004.1 | System SHALL implement real-time updates via Laravel Reverb | P1 | Complete |
| REQ-EI-004.2 | System SHALL broadcast character state changes | P1 | Complete |
| REQ-EI-004.3 | System SHALL broadcast external data sync notifications | P2 | Complete |
| REQ-EI-004.4 | System SHALL support channel-based subscriptions | P1 | Complete |

---

## 11. Data Management Requirements

**Reference Documents**: 005_DMP, 006_DMS, FLOW-001, SEQ-015

### 11.1 Data Import [REQ-DM-001]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-DM-001.1 | System SHALL support JSON import with schema versioning | P0 | Complete |
| REQ-DM-001.2 | System SHALL support CSV import | P1 | Complete |
| REQ-DM-001.3 | System SHALL support Excel import (.xlsx) | P1 | Complete |
| REQ-DM-001.4 | System SHALL detect and migrate legacy formats | P1 | Complete |
| REQ-DM-001.5 | System SHALL provide import preview before commit | P1 | Complete |
| REQ-DM-001.6 | System SHALL validate records against schema and business rules | P0 | Complete |

### 11.2 Data Export [REQ-DM-002]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-DM-002.1 | System SHALL export to JSON format with schema version | P0 | Complete |
| REQ-DM-002.2 | System SHALL export to Excel format (.xlsx) | P1 | Complete |
| REQ-DM-002.3 | System SHALL export to CSV format | P1 | Complete |
| REQ-DM-002.4 | System SHALL support selective export (filter by criteria) | P1 | Complete |
| REQ-DM-002.5 | System SHALL export 50k rows within 60 seconds | P1 | Complete |

### 11.3 Conflict Resolution [REQ-DM-003]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-DM-003.1 | System SHALL detect duplicate records during import | P0 | Complete |
| REQ-DM-003.2 | System SHALL support Skip resolution strategy | P0 | Complete |
| REQ-DM-003.3 | System SHALL support Overwrite resolution strategy | P0 | Complete |
| REQ-DM-003.4 | System SHALL support Merge resolution strategy | P1 | Complete |
| REQ-DM-003.5 | System SHALL support Rename resolution strategy | P1 | Complete |
| REQ-DM-003.6 | System SHALL provide UI for manual conflict resolution | P1 | Complete |

### 11.4 Backup and Restore [REQ-DM-004]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-DM-004.1 | System SHALL support manual backup creation | P0 | Complete |
| REQ-DM-004.2 | System SHALL support full restore from backup | P0 | Complete |
| REQ-DM-004.3 | System SHALL validate backup version compatibility | P1 | Complete |
| REQ-DM-004.4 | System SHALL include metadata in backups (version, timestamp, schema) | P1 | Complete |
| REQ-DM-004.5 | System SHALL compress backup files | P1 | Complete |

### 11.5 Data Migration [REQ-DM-005]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-DM-005.1 | System SHALL detect legacy data formats | P1 | Complete |
| REQ-DM-005.2 | System SHALL transform legacy fields to canonical names | P1 | Complete |
| REQ-DM-005.3 | System SHALL normalize enum values | P1 | Complete |
| REQ-DM-005.4 | System SHALL validate and clamp stat ranges | P1 | Complete |
| REQ-DM-005.5 | System SHALL maintain migration history | P2 | Complete |

---

## 12. Storage Mode Requirements

**Reference Documents**: BRS Section 4.9, SRS FR-10, UF-001

### 12.1 Local Storage Mode [REQ-ST-001]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-ST-001.1 | System SHALL support Local mode using browser localStorage | P0 | Complete |
| REQ-ST-001.2 | System SHALL generate UUID identifiers for Local runs | P0 | Complete |
| REQ-ST-001.3 | System SHALL function fully offline in Local mode | P0 | Complete |
| REQ-ST-001.4 | System SHALL display "Local Mode" badge indicator | P0 | Complete |
| REQ-ST-001.5 | System SHALL warn on localStorage quota limits (5-10MB) | P1 | Complete |

### 12.2 Account Storage Mode [REQ-ST-002]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-ST-002.1 | System SHALL support Account mode using MySQL database | P0 | Complete |
| REQ-ST-002.2 | System SHALL use integer IDs for Account runs | P0 | Complete |
| REQ-ST-002.3 | System SHALL require authentication for Account mode | P0 | Complete |
| REQ-ST-002.4 | System SHALL display "Account Mode" badge indicator | P0 | Complete |
| REQ-ST-002.5 | System SHALL sync data to cloud on save | P0 | Complete |

### 12.3 Storage Conversion [REQ-ST-003]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-ST-003.1 | System SHALL support conversion from Local to Account mode | P0 | Complete |
| REQ-ST-003.2 | System SHALL preserve all data during conversion | P0 | Complete |
| REQ-ST-003.3 | System SHALL optionally remove Local copy after conversion | P1 | Complete |
| REQ-ST-003.4 | System SHALL redirect to Account URL after conversion | P1 | Complete |

### 12.4 Draft Auto-Save [REQ-ST-004]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-ST-004.1 | System SHALL auto-save drafts every 30 seconds | P0 | Complete |
| REQ-ST-004.2 | System SHALL store drafts in localStorage regardless of mode | P0 | Complete |
| REQ-ST-004.3 | System SHALL retain last 3 draft versions | P1 | Complete |
| REQ-ST-004.4 | System SHALL prompt for draft recovery after 7 days | P1 | Complete |
| REQ-ST-004.5 | System SHALL clear drafts after successful save | P0 | Complete |

---

## 13. Performance Requirements

**Reference Documents**: SRS NFR-01, SDS Section 2

### 13.1 Page Load Performance [REQ-PF-001]

| ID | Requirement | Target | Status |
|----|-------------|--------|--------|
| REQ-PF-001.1 | Page load time SHALL be under 2 seconds | <2s | In Progress |
| REQ-PF-001.2 | First Contentful Paint SHALL be under 1.5 seconds | <1.5s | In Progress |
| REQ-PF-001.3 | Time to Interactive SHALL be under 3 seconds | <3s | In Progress |
| REQ-PF-001.4 | Largest Contentful Paint SHALL be under 2.5 seconds | <2.5s | In Progress |

### 13.2 API Performance [REQ-PF-002]

| ID | Requirement | Target | Status |
|----|-------------|--------|--------|
| REQ-PF-002.1 | API response time SHALL be under 200ms (p95) | <200ms | Complete |
| REQ-PF-002.2 | Training prediction response SHALL be under 1.2 seconds (p95) | <1.2s | Complete |
| REQ-PF-002.3 | AI advisory response SHALL be under 2.5 seconds (local) | <2.5s | Complete |
| REQ-PF-002.4 | AI advisory response SHALL be under 4 seconds (cloud fallback) | <4s | Complete |
| REQ-PF-002.5 | Autocomplete response SHALL be under 200ms | <200ms | Complete |

### 13.3 Database Performance [REQ-PF-003]

| ID | Requirement | Target | Status |
|----|-------------|--------|--------|
| REQ-PF-003.1 | Individual queries SHALL complete under 50ms | <50ms | Complete |
| REQ-PF-003.2 | Character list (paginated) SHALL load under 100ms | <100ms | Complete |
| REQ-PF-003.3 | Export operations SHALL process 50k rows under 60s | <60s | Complete |

### 13.4 Caching Strategy [REQ-PF-004]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-PF-004.1 | System SHALL cache training predictions (5-minute TTL) | P1 | Complete |
| REQ-PF-004.2 | System SHALL cache external API responses (24-hour TTL) | P1 | Complete |
| REQ-PF-004.3 | System SHALL cache skill metadata (7-day TTL) | P1 | Complete |
| REQ-PF-004.4 | System SHALL invalidate cache on relevant data changes | P1 | Complete |
| REQ-PF-004.5 | System SHALL provide cache monitoring dashboard | P2 | In Progress |

---

## 14. Accessibility Requirements

**Reference Documents**: SRS NFR-03, SUM Section 13

### 14.1 WCAG 2.2 AA Compliance [REQ-AC-001]

| ID | Requirement | WCAG Reference | Status |
|----|-------------|----------------|--------|
| REQ-AC-001.1 | All images SHALL have descriptive alt text | 1.1.1 | Complete |
| REQ-AC-001.2 | Color information SHALL be available via text | 1.4.1 | Complete |
| REQ-AC-001.3 | Text contrast ratio SHALL be minimum 4.5:1 | 1.4.3 | Complete |
| REQ-AC-001.4 | Page SHALL support 400% zoom reflow | 1.4.10 | In Progress |
| REQ-AC-001.5 | All interactive elements SHALL be keyboard accessible | 2.1.1 | Complete |
| REQ-AC-001.6 | Skip-to-main link SHALL be provided | 2.4.1 | Complete |
| REQ-AC-001.7 | Focus SHALL always be visible | 2.4.7 | Complete |
| REQ-AC-001.8 | Semantic HTML elements SHALL be used | 4.1.2 | Complete |

### 14.2 Keyboard Navigation [REQ-AC-002]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-AC-002.1 | All interactive elements SHALL be focusable via Tab | P0 | Complete |
| REQ-AC-002.2 | Modals SHALL implement focus trap | P0 | Complete |
| REQ-AC-002.3 | Escape key SHALL close modals/dialogs | P0 | Complete |
| REQ-AC-002.4 | Keyboard shortcuts SHALL be documented | P1 | Complete |
| REQ-AC-002.5 | Shortcuts SHALL not conflict with screen reader commands | P1 | Complete |

### 14.3 Screen Reader Support [REQ-AC-003]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-AC-003.1 | All forms SHALL have associated labels | P0 | Complete |
| REQ-AC-003.2 | Dynamic content SHALL announce via ARIA live regions | P1 | Complete |
| REQ-AC-003.3 | Complex widgets SHALL have appropriate ARIA roles | P1 | Complete |
| REQ-AC-003.4 | Loading states SHALL be announced | P1 | In Progress |

### 14.4 Motion and Animation [REQ-AC-004]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-AC-004.1 | System SHALL respect prefers-reduced-motion setting | P1 | Complete |
| REQ-AC-004.2 | Essential animations SHALL have alternatives | P1 | Complete |
| REQ-AC-004.3 | Animations SHALL be pausable where applicable | P2 | In Progress |

---

## 15. Progressive Web App Requirements

**Reference Documents**: BRS Section 4.11, SRS NFR-04

### 15.1 Service Worker [REQ-PWA-001]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-PWA-001.1 | System SHALL register service worker for caching | P1 | Complete |
| REQ-PWA-001.2 | System SHALL cache static assets for offline use | P1 | Complete |
| REQ-PWA-001.3 | System SHALL implement cache-first strategy for assets | P1 | Complete |
| REQ-PWA-001.4 | System SHALL implement network-first strategy for API calls | P1 | Complete |

### 15.2 Offline Functionality [REQ-PWA-002]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-PWA-002.1 | Local mode SHALL function fully offline | P0 | Complete |
| REQ-PWA-002.2 | System SHALL display offline indicator when disconnected | P1 | Complete |
| REQ-PWA-002.3 | System SHALL queue Account mode saves when offline | P1 | In Progress |
| REQ-PWA-002.4 | System SHALL sync queued saves when connection restored | P1 | In Progress |
| REQ-PWA-002.5 | System SHALL provide offline fallback pages | P1 | In Progress |

### 15.3 Installation [REQ-PWA-003]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-PWA-003.1 | System SHALL provide valid web manifest | P1 | Complete |
| REQ-PWA-003.2 | System SHALL support "Add to Home Screen" | P1 | Complete |
| REQ-PWA-003.3 | System SHALL provide appropriate icons (192x192, 512x512) | P1 | Complete |
| REQ-PWA-003.4 | System SHALL define app theme colors | P1 | Complete |

---

## 16. Security Requirements

**Reference Documents**: SRS NFR-02, SIS Section 6

### 16.1 Authentication [REQ-SE-001]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-SE-001.1 | System SHALL implement Laravel Sanctum for authentication | P0 | Complete |
| REQ-SE-001.2 | Web routes SHALL use session-based authentication | P0 | Complete |
| REQ-SE-001.3 | API routes SHALL use token-based authentication | P0 | Complete |
| REQ-SE-001.4 | System SHALL support "Remember Me" functionality | P1 | Complete |
| REQ-SE-001.5 | System SHALL support email verification | P2 | Complete |

### 16.2 Authorization [REQ-SE-002]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-SE-002.1 | System SHALL isolate user data (per-user access) | P0 | Complete |
| REQ-SE-002.2 | System SHALL implement policy-based access control | P0 | Complete |
| REQ-SE-002.3 | System SHALL validate ownership before data access | P0 | Complete |

### 16.3 Input Validation [REQ-SE-003]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-SE-003.1 | System SHALL validate all form inputs via Form Requests | P0 | Complete |
| REQ-SE-003.2 | System SHALL sanitize inputs to prevent XSS | P0 | Complete |
| REQ-SE-003.3 | System SHALL use parameterized queries (Eloquent) | P0 | Complete |
| REQ-SE-003.4 | System SHALL validate uploaded file MIME types | P0 | Complete |
| REQ-SE-003.5 | System SHALL enforce file size limits (2MB max for images) | P0 | Complete |

### 16.4 Rate Limiting [REQ-SE-004]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-SE-004.1 | Web routes SHALL be limited to 60 requests/minute | P0 | Complete |
| REQ-SE-004.2 | API routes SHALL be limited to 100 requests/minute | P0 | Complete |
| REQ-SE-004.3 | AI endpoints SHALL be limited to 30 requests/minute | P1 | Complete |
| REQ-SE-004.4 | OCR endpoints SHALL be limited to 10 requests/minute | P1 | Complete |

### 16.5 CSRF Protection [REQ-SE-005]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-SE-005.1 | All form submissions SHALL include CSRF tokens | P0 | Complete |
| REQ-SE-005.2 | API endpoints SHALL validate CSRF for session-based requests | P0 | Complete |

---

## 17. Testing Requirements

**Reference Documents**: SDP Section 10, 000_IMPLEMENTATION_VERIFICATION_MATRIX

### 17.1 Unit Testing [REQ-TS-001]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-TS-001.1 | System SHALL achieve >80% code coverage for services | P0 | In Progress |
| REQ-TS-001.2 | All calculators SHALL have comprehensive unit tests | P0 | Complete |
| REQ-TS-001.3 | AI service mocks SHALL provide deterministic responses | P1 | Complete |

### 17.2 Feature Testing [REQ-TS-002]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-TS-002.1 | All API endpoints SHALL have feature tests | P0 | In Progress |
| REQ-TS-002.2 | Livewire components SHALL have interaction tests | P1 | In Progress |
| REQ-TS-002.3 | Form validations SHALL be tested | P0 | Complete |

### 17.3 End-to-End Testing [REQ-TS-003]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-TS-003.1 | Critical user flows SHALL have Playwright tests | P0 | In Progress |
| REQ-TS-003.2 | E2E tests SHALL cover Career Setup flow (UF-002) | P0 | Complete |
| REQ-TS-003.3 | E2E tests SHALL cover Training Day flow (UF-003) | P0 | Complete |
| REQ-TS-003.4 | E2E tests SHALL cover Race Day flow (UF-004) | P0 | Complete |
| REQ-TS-003.5 | E2E tests SHALL cover Skill Management flow (UF-005) | P0 | Complete |
| REQ-TS-003.6 | E2E tests SHALL cover AI Advisor flow (UF-007) | P1 | In Progress |
| REQ-TS-003.7 | E2E tests SHALL cover Data Import flow (UF-008) | P1 | In Progress |

### 17.4 Accessibility Testing [REQ-TS-004]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-TS-004.1 | All pages SHALL pass axe-core accessibility scans | P0 | In Progress |
| REQ-TS-004.2 | Keyboard navigation SHALL be tested programmatically | P1 | Complete |
| REQ-TS-004.3 | Screen reader compatibility SHALL be verified | P1 | In Progress |

---

## 18. Non-Functional Requirements

### 18.1 Maintainability [REQ-NF-001]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-NF-001.1 | Code SHALL follow PSR-12 coding standards | P0 | Complete |
| REQ-NF-001.2 | All interactive elements SHALL have data-testid attributes | P0 | Complete |
| REQ-NF-001.3 | Business logic SHALL be encapsulated in service layer | P0 | Complete |
| REQ-NF-001.4 | Documentation SHALL be maintained for public APIs | P1 | Complete |

### 18.2 Compatibility [REQ-NF-002]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-NF-002.1 | System SHALL support Chrome (last 2 versions) | P0 | Complete |
| REQ-NF-002.2 | System SHALL support Firefox (last 2 versions) | P0 | Complete |
| REQ-NF-002.3 | System SHALL support Safari (last 2 versions) | P0 | Complete |
| REQ-NF-002.4 | System SHALL support Edge (last 2 versions) | P0 | Complete |
| REQ-NF-002.5 | System SHALL support iOS Safari | P0 | Complete |
| REQ-NF-002.6 | System SHALL support Chrome Android | P0 | Complete |

### 18.3 Responsive Design [REQ-NF-003]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-NF-003.1 | System SHALL support viewport range 320px-2560px | P0 | Complete |
| REQ-NF-003.2 | Mobile layout SHALL apply below 640px | P0 | Complete |
| REQ-NF-003.3 | Tablet layout SHALL apply 640px-1024px | P0 | Complete |
| REQ-NF-003.4 | Desktop layout SHALL apply above 1024px | P0 | Complete |
| REQ-NF-003.5 | Touch targets SHALL be minimum 44px | P0 | Complete |

### 18.4 Observability [REQ-NF-004]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-NF-004.1 | System SHALL integrate APM for performance monitoring | P1 | In Progress |
| REQ-NF-004.2 | System SHALL log errors with trace IDs | P1 | Complete |
| REQ-NF-004.3 | System SHALL track AI costs and performance | P1 | Complete |
| REQ-NF-004.4 | System SHALL monitor cache hit/miss rates | P1 | In Progress |
| REQ-NF-004.5 | System SHALL monitor external API health | P1 | Complete |

### 18.5 Internationalization [REQ-NF-005]

| ID | Requirement | Priority | Status |
|----|-------------|----------|--------|
| REQ-NF-005.1 | Primary interface SHALL be in English | P0 | Complete |
| REQ-NF-005.2 | System SHALL support Japanese skill names | P0 | Complete |
| REQ-NF-005.3 | System SHALL support Japanese character names | P1 | Complete |
| REQ-NF-005.4 | System SHALL store text in UTF-8 (utf8mb4) | P0 | Complete |

---

## 19. Traceability Matrix

### 19.1 Business Requirements to Functional Requirements

| Business Req | Functional Req | Technical Spec | PRD | Priority |
|--------------|----------------|----------------|-----|----------|
| BR-1 Character Management | REQ-CM-* | SPEC-001 | PRD-001 | P0 |
| BR-2 Training Optimization | REQ-TO-* | SPEC-002 | PRD-002 | P0 |
| BR-3 Race Strategy | REQ-RS-* | SPEC-003 | PRD-003 | P0 |
| BR-4 Skill Management | REQ-SM-* | SPEC-004 | PRD-004 | P0 |
| BR-5 Support Card Management | REQ-SC-* | SPEC-005 | PRD-005 | P0 |
| BR-6 AI Advisory | REQ-AI-* | SPEC-006 | PRD-006 | P1 |
| BR-7 External Integration | REQ-EI-* | SPEC-007 | PRD-007 | P1 |
| BR-8 Data Management | REQ-DM-* | D05, D06 | - | P0 |
| BR-9 Storage Modes |
