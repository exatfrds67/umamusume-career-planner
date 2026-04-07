# PRD-004: Skill Management System

## Umamusume Pretty Derby Career Planner

**Document Version**: 2.2.1
**Date**: March 11, 2026
**Project**: UmamusumeCareerPlanner
**Author**: Development Team
**Status**: Current - Aligned with codebase v2.2.0
**Related Documents**: [SRS-FR-05], [SDS-4.4], [DBD-4.3], [SPEC-004]

**Source Specs**:

- `.kiro/specs/umamusume-career-planner-main/requirements.md` (Requirements)
- `.kiro/specs/umamusume-career-planner-main/design.md` (Design)
- `.kiro/specs/umamusume-career-planner-main/tasks.md` (Implementation Tasks)

**Related Artifacts**:

- SPEC: [SPEC-004](../02-specs/SPEC-004_Skill_Management_Technical.md)
- Flow: [FLOW-004](../01-flows/FLOW-004_Skill_Management_System.md)
- Wireframes: [WF-008](../01-wireframes/WF-008_Skill_Shop_Interface.md),
[WF-009](../01-wireframes/WF-009_Skill_Loadout_Manager.md)
- Sequences: [SEQ-003](../01-sequences/SEQ-003_Skill_Acquisition_and_Upgrade.md)
- User Flows: [UF-005](../01-user-flows/UF-005_Skill_Management_Flow.md)

---

## Table of Contents

- [PRD-004: Skill Management System](#prd-004-skill-management-system)
  - [1. Executive Summary](#1-executive-summary)
  - [2. Product Overview](#2-product-overview)
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

Provide a comprehensive system for browsing, planning, and acquiring skills, managing Skill Points
(SP) budgets, and tracking skill evolution paths.

### 1.2 Problem Statement

The vast number of skills, complex prerequisite chains, and varying costs based on hint levels make
manual planning error-prone.

### 1.3 Solution Overview

- **Skill Catalog**: A searchable, filterable database of all skills with metadata.
- **Hint Tracker**: Automatic calculation of SP discounts (10%/20%/30%/35%/40%) based on hint levels (1-5).
- **AI Builder**: Integration with Skill Advisor Agent for recommendations.

---

## 2. Product Overview

### 2.1 Objectives

- Ensure accurate SP cost calculations accounting for all discount levels.
- Prevent invalid skill combinations.
- Simplify the Evolution process for upgrading skills.

### 2.2 Scope (In)

- Catalog Management, Acquisition Logic, Hint System (5 levels), Evolution, Loadouts.

### 2.3 Scope (Out)

- Real-time Activation simulation, Custom skill creation.

---

## 3. User Stories

| ID | Actor | Story | Acceptance Criteria |
| --- | --- | --- | --- |
| US-4.1 | Player | Find skills compatible with strategy | Catalog filters display relevant skills |
| US-4.2 | Player | See SP savings at each hint level | UI shows cost at all 5 hint levels |
| US-4.3 | Player | Evolve Gold skill | Evolve button active when requirements met |
| US-4.4 | Player | AI skill suggestions | AI returns optimized skill list |
| US-4.5 | Coach | Save skill loadout | Users can save and recall skill loadouts in the active storage mode. In `StorageMode::LOCAL`, loadouts are stored in browser-local state. In `StorageMode::ACCOUNT`, loadouts are stored in authenticated account-backed persistence. If loadouts are unavailable in the current mode or context, the UI must show an explicit unavailable state. |

---

## 4. Functional Requirements

### 4.1 Skill Catalog & Discovery [FR-05.1]

The catalog must display skill acquisition state explicitly as one of: `available`, `owned`,
`evolvable`, or `not eligible`. If prerequisite data is missing or unresolved, the skill must be
shown as unresolved rather than falsely marked available.

- Database with Name, Rarity, Base Cost, Cooldown, Duration, Effect Logic.
- Filtering by Name, Strategy, Distance, Surface, Effect Type.
- Prerequisite enforcement.

### 4.2 Acquisition & Cost Logic [FR-05.2, FR-05.3]

**Hint Discounts** (Game-Accurate - Global English Server):

| Hint Level | Discount | Cumulative | Cost Multiplier |
| --- | --- | --- | --- |
| 0 | 0% | 0% | 1.00x (100%) |
| 1 | 10% | 10% | 0.90x (90%) |
| 2 | 10% | 20% | 0.80x (80%) |
| 3 | 10% | 30% | 0.70x (70%) |
| 4 | 5% | 35% | 0.65x (65%) |
| 5 | 5% | 40% | 0.60x (60%) |

**Maximum Discount from Hints**: 40% at Level 5

**Additional Discount Sources**:

- Fast Learner Condition: Extra 10% discount
- Skill Sparks (Inheritance): Bonus discount based on star rating
- Hint Books: Green (white skills), Gold (rare skills)

**Hint Lifecycle**: Hint levels are tracked per skill context for the current trainee and discount
the next valid purchase of that skill acquisition. Once the purchase is confirmed, the hint discount
is treated as consumed for that acquisition and must not be presented as reusable state.

**Unique Skill Handling**: Character unique skills are typically granted by character state or
inheritance rather than purchased from the normal SP shop flow. The planner must model them as non-
standard acquisitions unless a scenario-specific rule explicitly exposes an SP purchase path.

### 4.3 Skill Evolution [FR-05.4]

- Upgrade paths from Normal to Rare/Evolved.
- Cost adjustment for owned base skills.

### 4.4 AI Recommendations [FR-05.5]

- SkillAdvisorAgent provides optimized skill lists.

### 4.5 Loadout Management [FR-05.7]

Users may toggle equipped status and save loadouts only when the resulting configuration is valid.
Invalid combinations must show validation feedback and must not appear saved or confirmed.

- Toggle equipped status, validate conflicts.

---

## 5. User Interface Requirements

### 5.1 Skill Shop Dashboard

- Header: SP Balance, Total Earned SP.
- Tabs: Recommended / Available / Owned / Evolvable.
- Skill Card with Hint Lv badge (0-5).

### 5.2 Purchase Confirmation Modal

- Cost Breakdown showing all discount sources.

### 5.3 Skill Detail View

- Description, Conditions, Synergy, Hint Progress (0-5).

---

## 6. Data and Integration

### 6.1 Data Models

- ucp_skills, ucp_skill_hints (levels 0-5), ucp_skill_acquisitions.

### 6.2 External Data

- Skill Data from umapyoi.net.

### 6.3 Internal Integration

- Training generates hints, Race provides SP.

---

## 7. Non-Functional Requirements

- Performance: < 100ms search response.
- Accuracy: Exact match with in-game values.
- Usability: Clear hint level and discount display.

---

## 8. Success Metrics

- < 50 wasted SP average.
- > 75% runs with 5+ skills logged.
- > 60% Rare skills purchased with hints.

---

## 9. Release Plan

- **v2.2.0 (Current)**: Corrected 5-level hint system, additional discount sources.

---

## 10. Open Questions and Assumptions

- Scenario event skills as 0-cost acquisitions.

---

## Changelog

| Version | Date | Changes |
| --- | --- | --- |
| 2.2.1 | March 11, 2026 | Clarified hint lifecycle/consumption semantics and documented unique skills as non-standard acquisitions rather than normal shop purchases. |
| 2.2.0 | January 28, 2026 | Updated with verified game mechanics from Global English Server: corrected hint system to 5 levels (10%/10%/10%/5%/5% = 40% max), added additional discount sources. |
| 2.1.0 | January 24, 2026 | Aligned with codebase v2.0.0. |
| 2.0.0 | January 2026 | Initial v2 release. |
