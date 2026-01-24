# PRD-004: Skill Management System

## Umamusume Pretty Derby Career Planner

**Document Version**: 2.1.0  
**Date**: January 24, 2026  
**Project**: UmamusumeCareerPlanner  
**Author**: Development Team  
**Status**: Current - Aligned with codebase v2.0.0  
**Related Documents**: [SRS-FR-05], [SDS-4.4], [DBD-4.3], [SPEC-004]

**Source Specs**:

- `.kiro/specs/umamusume-career-planner-main/requirements.md` (Requirements)
- `.kiro/specs/umamusume-career-planner-main/design.md` (Design)
- `.kiro/specs/umamusume-career-planner-main/tasks.md` (Implementation Tasks)

**Related Artifacts**:

- SPEC: [SPEC-004](../specs/SPEC-004_Skill_Management_Technical.md)
- Flow: [FLOW-004](../flows/FLOW-004_Skill_Management_System.md)
- Wireframes: [WF-008](../wireframes/WF-008_Skill_Shop_Interface.md), [WF-009](../wireframes/WF-009_Skill_Loadout_Manager.md)
- Sequences: [SEQ-003](../sequences/SEQ-003_Skill_Acquisition_and_Upgrade.md)
- User Flows: [UF-005](../user-flows/UF-005_Skill_Management_Flow.md)

---

## Table of Contents

- [PRD-004: Skill Management System](#prd-004-skill-management-system)
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

---

## 1. Executive Summary

### 1.1 Purpose

Provide a comprehensive system for browsing, planning, and acquiring skills, managing Skill Points (SP) budgets, and tracking skill evolution paths. This system ensures players maximize their character's performance by selecting the optimal skills for specific race conditions.

### 1.2 Problem Statement

The vast number of skills, complex prerequisite chains, and varying costs based on hint levels make manual planning error-prone. Players often overspend SP on low-impact skills or fail to save enough for crucial Rare/Gold skills for the URA Finals.

### 1.3 Solution Overview

- **Skill Catalog**: A searchable, filterable database of all skills with metadata (cost, rarity, conditions).
- **Hint Tracker**: Automatic calculation of SP discounts (20%/40%) based on acquired hints.
- **AI Builder**: Integration with **Skill Advisor Agent** (Neuron AI) to recommend skill loadouts tailored to specific race targets.

---

## 2. Product Overview

### 2.1 Objectives

- Ensure accurate SP cost calculations accounting for all discount levels.
- prevent invalid skill combinations (e.g., conflicting Unique skills or unmet prerequisites).
- Simplify the "Evolution" process for upgrading Normal skills to Rare/Unique versions.

### 2.2 Scope (In)

- **Catalog Management**: Database of Normal, Rare, and Unique skills.
- **Acquisition Logic**: Purchase validation, SP deduction, and inventory tracking.
- **Hint System**: Tracking hint levels (1-3) and applying cost reductions.
- **Evolution**: Managing logic for upgrading skills (e.g., *Go with the Flow* → *Lane Legerdemain*).
- **Loadouts**: Creating and saving skill sets for specific race scenarios.

### 2.3 Scope (Out)

- **Real-time Activation**: Visual simulation of skill triggers during a race (handled statistically in PRD-003).
- **Skill Creation**: Users cannot define custom skills; data must come from external sources.

---

## 3. User Stories

| ID | Actor | Story | Acceptance Criteria |
|----|-------|-------|---------------------|
| US-4.1 | Player | I want to find skills compatible with "Long Distance" and "Betweener" strategy. | Catalog filters display only relevant skills. |
| US-4.2 | Player | I want to see how much SP I save if I wait for another hint. | UI shows "Current Cost" vs "Cost at Next Hint Level". |
| US-4.3 | Player | I want to evolve my Gold skill after meeting the success conditions. | "Evolve" button becomes active; evolution requirements are checked. |
| US-4.4 | Player | I want the AI to suggest skills for the Japan Cup (2400m Turf). | AI returns a list of recommended skills prioritizing recovery and speed. |
| US-4.5 | Coach | I want to save a "PvP Dirt" skill loadout for quick reference. | Ability to save, name, and recall a set of acquired skills. |

---

## 4. Functional Requirements

### 4.1 Skill Catalog & Discovery [FR-05.1]

- **Database**: Maintain a repository of skills with attributes: Name (EN/JP), Rarity, Base Cost, Cooldown, Duration, and Effect Logic.
- **Filtering**: Allow search by Name, Strategy (Nige/Senkou...), Distance, Surface, and Effect Type (Heal/Buff/Debuff).
- **Prerequisites**: Enforce logic where Skill B requires possession of Skill A.

### 4.2 Acquisition & Cost Logic [FR-05.2, FR-05.3]

- **Base Cost**: Define standard SP cost per skill.
- **Hint Discounts**:
  - Level 0: 100% Cost
  - Level 1: 80% Cost (-20%)
  - Level 2: 70% Cost (-30%)
  - Level 3+: 60% Cost (-40%)
- **Validation**: Prevent purchase if SP balance is insufficient or prerequisites are unmet.

### 4.3 Skill Evolution [FR-05.4]

- **Upgrade Path**: Map Normal skills to their Rare/Evolved counterparts.
- **Cost Adjustment**: Deduct the difference in SP if upgrading from an owned base skill.
- **Status Tracking**: Mark base skill as "Upgraded" (effectively replaced) in the inventory.

### 4.4 AI Recommendations [FR-05.5]

- **Agent**: `SkillAdvisorAgent` (Neuron AI).
- **Context**: Input current stats, available SP, hints, and target race.
- **Output**: Optimized list of skills to purchase within budget to maximize win probability.

### 4.5 Loadout Management [FR-05.7]

- **Configurations**: Allow users to toggle "Equipped" status for acquired skills (limited by game slots/points if applicable).
- **Validation**: Alert on conflicting skills (e.g., two versions of the same unique skill family).

---

## 5. User Interface Requirements

### 5.1 Skill Shop Dashboard

- **Header**: Current SP Balance, Total Earned SP.
- **Tabs**: Recommended / Available / Owned / Evolvable.
- **Skill Card**:
  - Icon/Color indicating type (Blue=Heal, Orange=Buff, Red=Debuff, Green=Passive).
  - Cost display with strikethrough for discounted prices.
  - "Hint Lv X" badge.
- **Filter Bar**: Dropdowns for Rarity, Type, and Aptitude compatibility.

### 5.2 Purchase Confirmation Modal

- **Summary**: Skill Name, Effect Description.
- **Cost Breakdown**: Base Cost - Hint Discount = Final Price.
- **Balance Update**: Old SP → New SP.
- **Evolution Warning**: If purchasing a base skill that has an available evolution.

### 5.3 Skill Detail View

- **Description**: Full text of skill effect.
- **Conditions**: Activation triggers (e.g., "Middle leg", "Behind leader").
- **Synergy**: List of other skills that work well with this one.

---

## 6. Data and Integration

### 6.1 Data Models

- **Inputs**: `CareerRun` (SP, Hints), `SkillDefinitions`.
- **Outputs**: `SkillAcquisition` records, `SkillLoadout`.
- **Entities**:
  - `ucp_skills` (Reference data)
  - `ucp_skill_hints` (Run-specific state)
  - `ucp_skill_acquisitions` (History)

### 6.2 External Data

- **Skill Data**: Synced from `umapyoi.net` (names, icons, effects).
- **Meta Data**: Tier lists/ratings fetched from community sources (optional).

### 6.3 Internal Integration

- **Training (PRD-002)**: Training events generate hints (`ucp_skill_hints`).
- **Race (PRD-003)**: Race results provide SP; Race Strategy uses equipped skills for simulation.
- **Support Cards (PRD-005)**: Support cards determine available hints during training.

---

## 7. Non-Functional Requirements

- **Performance**: Skill search and filtering must respond in < 100ms.
- **Accuracy**: Cost calculations must exactly match in-game values (integer rounding).
- **Usability**: Hint levels and discounts must be visually distinct to prevent wasted SP.
- **Data Integrity**: Cannot acquire the same skill twice; cannot acquire evolved skill without base.

---

## 8. Success Metrics

- **Optimization**: Users utilizing the planner finish runs with < 50 wasted SP on average.
- **Adoption**: > 75% of active runs have at least 5 skills logged.
- **Hint Usage**: Users wait for Hint Lv 1+ for > 60% of Rare skill purchases.

---

## 9. Release Plan

- **v2.0.0 (Current)**:
  - Full catalog browsing and search.
  - Basic acquisition tracking.
  - Hint logic (manual entry or auto from training).
  - SP budget tracking.
- **v2.1.0 (Next)**:
  - Skill Evolution wizard.
  - "Chain" visualization (viewing dependency trees).
  - Loadout sharing via QR/Link.

---

## 10. Open Questions and Assumptions

- **Assumption**: Unique skills are treated as "Normal" rarity for cost purposes unless evolved.
- **Open Question**: How to handle "Steel Will" (Hagane no Ishi) and other skills given via specific scenario events? *Current: Treat as 0-cost acquisition events.*
- **Open Question**: Should we track skill specific levels (Lv 1-5)? *Current: Yes, but simplified to "Acquired" vs "Maxed" for simulation.*
