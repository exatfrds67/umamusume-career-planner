# PRD-001: Character Management System

## Umamusume Pretty Derby Career Planner

**Document Version**: 2.3.1
**Date**: March 11, 2026
**Project**: UmamusumeCareerPlanner
**Author**: Development Team
**Status**: Current - Aligned with codebase v2.3.0
**Related Documents**: [SRS-FR-02], [SRS-FR-10], [SDS-4.2], [DBD-4.2], [SPEC-001]

**Source Specs**:

- `.kiro/specs/umamusume-career-planner-main/requirements.md` (Requirements)
- `.kiro/specs/umamusume-career-planner-main/design.md` (Design)
- `.kiro/specs/umamusume-career-planner-main/tasks.md` (Implementation Tasks)

**Related Artifacts**:

- SPEC: [SPEC-001](../02-specs/SPEC-001_Character_Management_Technical.md)
- Flow: [FLOW-001](../01-flows/FLOW-001_Character_Management_System.md)
- Wireframes: [WF-002](../01-wireframes/WF-002_Character_Creation_Wizard.md),
[WF-003](../01-wireframes/WF-003_Character_Detail_Management.md)
- Sequences: [SEQ-001](../01-sequences/SEQ-001_Character_Creation_Sequence.md)
- User Flows: [UF-002](../01-user-flows/UF-002_Career_Setup_Flow.md)

---

## Table of Contents

- [PRD-001: Character Management System](#prd-001-character-management-system)
  - [1. Executive Summary](#1-executive-summary)
    - [1.1 Purpose](#11-purpose)
    - [1.2 Problem Statement](#12-problem-statement)
    - [1.3 Solution Overview](#13-solution-overview)
  - [2. Product Overview](#2-product-overview)
    - [2.1 Objectives](#21-objectives)
    - [2.2 Scope (In)](#22-scope-in)
    - [2.3 Scope (Out)](#23-scope-out)
  - [3. User Stories](#3-user-stories)
  - [4. Functional Requirements](#4-functional-requirements)
  - [5. User Interface Requirements](#5-user-interface-requirements)
  - [6. Data and Integration](#6-data-and-integration)
  - [7. Non-Functional Requirements](#7-non-functional-requirements)
  - [8. Success Metrics](#8-success-metrics)
  - [9. Release Plan](#9-release-plan)
  - [10. Open Questions and Assumptions](#10-open-questions-and-assumptions)
  - [Changelog](#changelog)

---

## 1. Executive Summary

### 1.1 Purpose

Provide a comprehensive system for managing the lifecycle of *Uma Musume* characters within the
application, facilitating creation, configuration, stat tracking, and persistence across both local
(offline) and account-based (cloud) storage modes.

### 1.2 Problem Statement

Players struggle to manually track complex character states—including factor inheritance, stat caps,
diminishing returns above 1200, and scenario-specific aptitudes—leading to suboptimal build planning
and data loss when switching devices or clearing browser cache.

### 1.3 Solution Overview

- **Unified Wizard**: A guided 4-step process for character initialization (Trainee, Scenario,
Parents/Inheritance, Support Deck).
- **Dual Storage Architecture**: Seamless support for Local Mode (browser localStorage) and Account
Mode (MySQL database) with conversion capabilities.
- **Real-time Dashboard**: Centralized view of stats, aptitudes, goals, and conditions powered by Livewire 3 reactivity.

---

## 2. Product Overview

### 2.1 Objectives

- **Accuracy**: Ensure stat tracking respects the 0-1200 soft cap with diminishing returns above
1200 and scenario-specific logic.
- **Accessibility**: Provide full offline functionality for guest users via Local Mode.
- **Integrity**: Validate factor inheritance rules and support deck composition constraints.
- **Synchronization**: Allow seamless migration of character data from Local to Account storage.

### 2.2 Scope (In)

- **Character Creation**: Wizard-based setup including Trainee selection, Scenario choice (URA,
etc.), Parent selection for inheritance, and initial Support Deck configuration.
- **State Management**: Tracking of 5 Core Stats (Speed, Stamina, Power, Guts, Wit), Energy, Mood, and Conditions.
- **Aptitude Management**: Tracking grades (G to S) for Surface, Distance, and Running Style.
- **Storage Management**: CRUD operations for both Local (UUID-based) and Account (ID-based) modes.
- **Goal Tracking**: Definition and progress monitoring of short-term and long-term objectives.

### 2.3 Scope (Out)

- **Gameplay Simulation**: Actual training execution (handled by PRD-002).
- **Race Simulation**: Race outcomes (handled by PRD-003).
- **External Game Sync**: Direct connection to game servers (uses external APIs via PRD-007 instead).

---

## 3. User Stories

| ID | Actor | Story | Acceptance Criteria |
| --- | --- | --- | --- |
| US-1.1 | Guest User | I want to create a character without logging in so I can test the app quickly. | Character saved to localStorage; "Local Mode" badge visible. |
| US-1.2 | Player | I want to select parent characters to automatically calculate inheritance bonuses. | Initial stats must reflect the selected parent factor mapping exactly: 1★ = +5, 2★ = +12, 3★ = +21. The review screen must show the per-stat inherited bonus source and total projected starting stats before confirmation. Stats with no inherited factor contribution must display as `+0` rather than remaining implicit. |
| US-1.3 | Player | I want to see my current stats and aptitude grades on a dashboard to plan my next move. | Stats shown with 0-1200+ bars (with diminishing returns indicator); Aptitudes shown as letter grades (G-S). |
| US-1.4 | Registered User | I want to convert my local character to my account so I don't lose data. | "Convert" button available; data persists to DB; Local copy optional removal. |
| US-1.5 | Player | I want to define target stats for the URA Finals to track my progress. | Goals appear on dashboard; progress bars update with stats. |
| US-1.6 | Player | I want to edit my support deck after creation if I change my strategy. | Deck edit interface available; changes reflect in training predictions. |

---

## 4. Functional Requirements

### 4.1 Character Creation Wizard [FR-02.1]

The system shall provide a guided setup flow for creating a character/run configuration that includes:

- trainee selection,
- scenario selection,
- parent selection for inheritance,
- support deck assignment.

**Storage-Aware Creation Requirement**: Character creation must support both storage modes. In
`StorageMode::LOCAL`, creation saves a browser-local payload with UUID-based identity. In
`StorageMode::ACCOUNT`, creation saves an authenticated database-backed record. The review step must
display the active storage mode clearly before final confirmation.

**Trainee Selection**: Searchable list of available Uma Musume sourced from `ucp_game_data`.

**Scenario Selection**: Choice of scenario (e.g., URA Finals, Unity Cup) affecting base stats.

**Inheritance**: Selection of **2 required Parents** must be supported in the base flow. If
grandparent data is available in a later or extended flow, it must be documented as an enhancement
and must not be implied as required for successful character creation in the current implementation.
The system must calculate initial stat bonuses based on Factor stars using the canonical mapping: 1★
= +5, 2★ = +12, 3★ = +21.

**Deck Building**: Integration with Support Card module (PRD-005) to assign 6 cards (5 owned + 1 borrowed).

### 4.2 Stat & State Tracking [FR-02.2, FR-02.3]

- **Core Stats**: Track Speed, Stamina, Power, Guts, Wit.
  - **Soft Cap**: 1200 (stats can exceed this value)
  - **Diminishing Returns**: Stats above 1200 count for half value in race calculations
  - **Important Breakpoints**: 901, 1200, 1600
  - **High-Stamina Advisory**: At 1200+ Stamina, the planner may surface advanced final-spurt
  guidance, but it must not present this as a guaranteed standalone in-game buff label.
- **Status Attributes**:
  - Energy: 0-100 scale.
  - Mood: 5 levels (Very Bad to Very Good).
  - Conditions: List of active buffs/debuffs (e.g., "Good Practice", "Lazy").

### 4.3 Aptitude Management [FR-02.6]

- **Grades**: Track grades for:
  - Surface (Turf, Dirt)
  - Distance (Sprint, Mile, Medium, Long)
  - Style (Nige, Senkou, Sashi, Oikomi)
- **Scale**: G, F, E, D, C, B, A, S (maximum grade is S; SS does not exist).
- **Baseline**: A-rank is the baseline (0% bonus/penalty). Only S-rank provides positive bonuses.
- **Aptitude Modifiers**:

| Rank | Surface (Power) | Distance (Speed) | Style (Wit) |
| --- | --- | --- | --- |
| S | +5% | +5% | +10% |
| A | 0% (baseline) | 0% (baseline) | 0% (baseline) |
| B | -10% | -10% | -15% |
| C | -20% | -20% | -25% |
| D | -30% | -40% | -40% |
| E | -50% | -60% | -60% |
| F | -70% | -80% | -80% |
| G | -90% | -90% | -90% |

- **Inheritance Modifiers**: Ability to modify initial grades based on parent factors (3★ aptitude
sparks raise grade by 1 level).

### 4.4 Storage & Persistence [FR-10]

**Local Mode**: Uses browser `localStorage` with a versioned JSON schema and UUID identifiers.

**Account Mode**: Uses authenticated MySQL persistence with numeric primary keys and owner-scoped access.

**Mode Transition**: Local-to-account conversion must provide a per-item summary of converted,
skipped, and errored items and must not imply full migration of unsupported entity types.

**Drafts**: Draft state must autosave at most every 30 seconds using the active storage mode; if
autosave fails, the UI must show a non-silent warning and preserve the current editable state when
possible.

### 4.5 Goal System [FR-02.9]

Users may create numeric stat goals and race-placement goals. The system must reject goals that
reference invalid or unavailable stats, races, or malformed target values. Completed goals must
record a completion timestamp. If a referenced race is later unavailable or deleted, the goal must
display a recoverable warning state rather than failing silently. In `StorageMode::LOCAL`, goals
persist in browser-local state; in `StorageMode::ACCOUNT`, goals persist in authenticated storage.

---

## 5. User Interface Requirements

### 5.1 Creation Wizard

- **Step 1: Trainee**: Grid view of character icons with search filter.
- **Step 2: Inheritance**: Tree view for Parent/Grandparent selection with "Calculate Bonus" summary panel.
- **Step 3: Deck**: Slot-based interface for support cards.
- **Step 4: Review**: Summary card showing projected starting state.

### 5.2 Character Dashboard

- **Header**: Character Name, Title, Current Turn, Scenario, Storage Mode Badge.
- **Stat Panel**: Circular or Bar charts for 5 core stats with color coding (Speed=Blue, Stamina=Green, etc.).
  - Display diminishing returns indicator for stats above 1200
  - Highlight important breakpoints (901, 1200, 1600)
- **Aptitude Grid**: Matrix display of current grades (G through S scale).
- **Status Widget**: Energy bar and Mood icon.
- **Goals Widget**: List of active goals with progress bars.

### 5.3 Responsive Design

- Mobile: Single column layout; tabbed navigation for dashboard sections.
- Desktop: Multi-column layout; sidebar navigation; always-visible stat summary.

---

## 6. Data and Integration

### 6.1 Data Models

- **Character**: `id` (UUID/Int), `name`, `scenario`, `stats` (JSON), `aptitudes` (JSON).
- **Run**: Linked to Character, tracks `current_turn`, `status`, `history`.
- **Factor**: `type`, `stars`, `parent_id`.

### 6.2 External Integration (via PRD-007)

- **Trainee Data**: Synced from `umapyoi.net` (names, images, base stats).
- **OCR Import**: Ability to populate creation wizard fields via screenshot upload.

### 6.3 Internal Integration

- **Training (PRD-002)**: Character stats feed into training prediction engine.
- **Race (PRD-003)**: Aptitudes and stats feed into race readiness scoring.
- **Skills (PRD-004)**: Acquired skills linked to character profile.
- **Decks (PRD-005)**: Active deck linked to character for training bonuses.

---

## 7. Non-Functional Requirements

- **Performance**: Dashboard load time < 200ms (p95). Stat updates reflect immediately (reactive).
- **Reliability**: No data loss during Local->Account conversion.
- **Accessibility**: WCAG 2.1 AA Compliance. All form inputs must have labels. Keyboard navigation support for wizard.
- **Storage Limits**: Local mode warnings when approaching browser storage quota (5MB).

---

## 8. Success Metrics

- **Completion Rate**: > 90% of started wizards result in a created character.
- **Conversion Rate**: > 20% of Local Mode characters converted to Account Mode.
- **Data Integrity**: < 1% of characters with invalid stats (e.g., negative values).
- **User Retention**: > 50% of users return to update character state within 7 days.

---

## 9. Release Plan

- **v2.0.0**:
  - Full creation wizard with inheritance calculation.
  - Dual storage mode (Local/Account) implementation.
  - Core stat and aptitude tracking.
  - Dashboard UI with Livewire reactivity.
- **v2.1.0**:
  - Advanced goal tracking alerts.
  - Batch import of characters via JSON/OCR.
  - Character comparison tool.
- **v2.2.0**:
  - Updated stat system with diminishing returns above 1200.
  - Corrected aptitude scale (G-S, no SS).
  - Game-accurate aptitude modifiers.
- **v2.3.0 (Current)**:
  - Game character catalog (`ucp_game_characters`) with 61 characters seeded.
  - Per-character race target associations (`ucp_game_character_target_races`) with 244 associations.
  - `GameCharacter` model with `targetRaces()`, `goalRaces()`, `requiredRaces()` relationships.
  - Silence Suzuka avatar fix — seeder now handles non-standard hash-only image filenames via manual
  overrides and self-healing update path.
  - Duplicate character record removed (DB integrity restored, 169 unique characters).

---

## 10. Open Questions and Assumptions

- **Assumption**: Base stats and growth rates are accurately provided by the external API.
- **Assumption**: Users understand the difference between Local and Account modes via UI cues.
- **Open Question**: Should we implement a "Archive" state for characters to save storage space without deletion?
- **Open Question**: How to handle schema updates for Local Mode data stored in user browsers?
(Current strategy: versioned JSON schema parser).

---

## Changelog

| Version | Date | Changes |
| --- | --- | --- |
| 2.3.1 | March 11, 2026 | Softened the 1200+ Stamina note so the planner treats it as advanced final-spurt guidance rather than a guaranteed named buff. |
| 2.3.0 | February 27, 2026 | Added game character catalog (`ucp_game_characters`, 61 chars seeded), per-character race target associations (`ucp_game_character_target_races`, 244 associations), `GameCharacter` model with relationship scopes. Fixed Silence Suzuka avatar (non-standard hash-only filename). Removed duplicate character DB record. |
| 2.2.0 | January 28, 2026 | Updated with verified game mechanics from Global English Server: corrected aptitude scale (G-S, no SS), added aptitude modifier table, updated stat system to reflect 1200+ capability with diminishing returns, added important stat breakpoints (901, 1200, 1600). |
| 2.1.0 | January 24, 2026 | Aligned with codebase v2.0.0, added source specs references. |
| 2.0.0 | January 2026 | Initial v2 release with dual storage architecture. |
