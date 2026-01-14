# PRD-004: Skill Management System

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Project**: UmamusumeCareerPlanner  
**Author**: AI Development Team  
**Status**: Draft  
**Related Documents**: [SRS-3.4], [SDS-4.4], [DBD-009], [SPEC-004], [PRD-001], [PRD-002]

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
1. [Executive Summary](#1-executive-summary)
2. [Product Overview](#2-product-overview)
3. [User Stories](#3-user-stories)
4. [Functional Requirements](#4-functional-requirements)
5. [User Interface Requirements](#5-user-interface-requirements)
6. [Data and Integration](#6-data-and-integration)
7. [Non-Functional Requirements](#7-non-functional-requirements)
8. [Success Metrics](#8-success-metrics)
9. [Release Plan](#9-release-plan)
10. [Open Questions and Assumptions](#10-open-questions-and-assumptions)

---

## 1. Executive Summary

### 1.1 Purpose
Enable players to acquire, upgrade, and manage skills efficiently with clear costs, prerequisites, and synergy guidance.

### 1.2 Problem Statement
Players often overspend skill points on low-impact skills or miss prerequisites; lack of visibility causes wasted turns and poor race readiness.

### 1.3 Solution Overview
- Structured skill board with prerequisites, costs, conflicts, and synergies.  
- Validation and projection of skill point usage.  
- Recommendations based on race plan and current stats.

---

## 2. Product Overview

### 2.1 Objectives
- Provide accurate skill costs and prerequisite checks.  
- Prevent conflicting skills and highlight synergies for targeted races.  
- Track skill point balance and forecast after planned purchases.

### 2.2 Scope (In)
- Skill catalog with tags (distance/ground/strategy).  
- Purchase, upgrade, and refund (if supported by game rules).  
- Recommendation engine aligned to race targets (PRD-003).  
- Validation APIs and UI board.

### 2.3 Scope (Out)
- Training prediction logic (PRD-002).  
- Support deck management (PRD-005).

---

## 3. User Stories
- As a player, I want to see prerequisites and conflicts before buying a skill.  
- As a player, I want recommendations for skills that best fit my next race.  
- As a player, I want to know my remaining skill points after a set of purchases.  
- As a coach, I want a history of purchased skills per run.

---

## 4. Functional Requirements
- FR1: Provide skill catalog with metadata (cost, tags, prerequisites, conflicts, rarity).  
- FR2: Validate purchases against prerequisites, conflicts, and available points.  
- FR3: Support upgrades with increasing cost and effect tiers.  
- FR4: Compute synergy score versus target race(s) and recommend top options.  
- FR5: Track skill point balance and log purchase history.  
- FR6: Expose APIs for listing skills, validating, purchasing, upgrading, and undo (if allowed).  
- FR7: Integrate with readiness scoring (PRD-003) and training suggestions (PRD-002) for coordinated guidance.

---

## 5. User Interface Requirements
- Skill board with search, filters (tags, cost, synergy score), and sorting.  
- Detail drawer: description, cost, prerequisites/conflicts, synergy badge, recommendation reason.  
- Purchase/upgrade confirmation with point delta preview and remaining balance.  
- Error states for unmet prerequisites or conflicts.  
- Accessible components: focus order, screen reader labels, keyboard shortcuts for purchase/close.

---

## 6. Data and Integration
- Data: skills table, prerequisites map, conflicts map, synergy model weights.  
- Inputs: run stats (PRD-001), race targets (PRD-003), training plan (PRD-002).  
- Services: SkillService, SynergyScorer, Validation engine.  
- Dependencies: SRS-3.4, SDS-4.4, DBD-009 skill tables; external updates via PRD-007.

---

## 7. Non-Functional Requirements
- Performance: validation and purchase response ≤800ms (p95).  
- Consistency: atomic updates of skill set and point balance.  
- Observability: audit trail of purchases; anomaly detection for unexpected costs.  
- Accessibility: WCAG 2.2 AA for board interactions.

---

## 8. Success Metrics
- Validation failure rate <3% after guidance.  
- ≥85% of recommended skills purchased lead to readiness improvement vs baseline.  
- Support-related skill conflicts reduced by ≥50%.  
- User satisfaction (CSAT) ≥4.5/5 for skill board UX.

---

## 9. Release Plan
- Phase A: Catalog + validation + purchase flow.  
- Phase B: Synergy scoring and recommendations + upgrade path.  
- Phase C: History/audit export and optional refund support (if rules permit).

---

## 10. Open Questions and Assumptions
- Assumption: Skill costs and effects refreshed regularly from game data.  
- Question: Should partial refunds exist for misclicks?  
- Question: How to handle limited-time skills in catalog visibility?
