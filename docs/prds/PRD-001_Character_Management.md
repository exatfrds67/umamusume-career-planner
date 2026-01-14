# PRD-001: Character Management System

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Project**: UmamusumeCareerPlanner  
**Author**: AI Development Team  
**Status**: Draft  
**Related Documents**: [SRS-3.1], [SDS-4.1], [DBD-009], [SPEC-001]

**Source Specs**:
- `.kiro/specs/umamusume-career-planner-main/requirements.md` (Requirements)
- `.kiro/specs/umamusume-career-planner-main/design.md` (Design)
- `.kiro/specs/umamusume-career-planner-main/tasks.md` (Implementation Tasks)

**Related Artifacts**:
- SPEC: [SPEC-001](../specs/SPEC-001_Character_Management_Technical.md)
- Flow: [FLOW-001](../flows/FLOW-001_Character_Management_System.md)
- Wireframes: [WF-002](../wireframes/WF-002_Character_Creation_Wizard.md), [WF-003](../wireframes/WF-003_Character_Detail_Management.md)
- Sequences: [SEQ-001](../sequences/SEQ-001_Character_Creation_Sequence.md)
- User Flows: [UF-002](../user-flows/UF-002_Career_Setup_Flow.md)

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
Provide a complete character lifecycle experience: creation, configuration, monitoring, and progression tracking across runs.

### 1.2 Problem Statement
Players track characters manually, causing errors in inheritance, stats, and aptitude planning; missing real-time goal alignment increases failed runs.

### 1.3 Solution Overview
- Guided wizard for new characters with inheritance factors and scenario selection.  
- Central dashboard for stats, aptitudes, goals, conditions, and support deck context.  
- Progress tracking against goals, including mood/energy state and warnings.

---

## 2. Product Overview

### 2.1 Objectives
- Ensure characters are initialized with valid scenarios, parents, and factor seeds.  
- Keep character state synchronized with training, skills, races, and support decks.  
- Surface readiness signals (goal completion, stat gaps, mood/energy risks).

### 2.2 Scope (In)
- Character creation wizard (trainee, scenario, parents/factors, starting goals).  
- Character detail dashboard (stats, aptitudes, goals, condition, mood, energy).  
- Goal management (add/edit goals, distance/ground targets).  
- Condition and mood tracking per turn with history.

### 2.3 Scope (Out)
- Training simulation (covered by PRD-002).  
- Race outcome simulation (covered by PRD-003).  
- Skill acquisition rules (covered by PRD-004).

---

## 3. User Stories
- As a player, I want to create a new character with scenario and parents so I can start a run.  
- As a player, I want to see current stats, aptitudes, and factor bonuses so I can plan training.  
- As a player, I want to track goals and see gaps to reach race requirements.  
- As a player, I want to log mood/condition changes so I can avoid risky actions.  
- As a coach, I want an audit of turns and changes for post-run review.

---

## 4. Functional Requirements
- FR1: Provide trainee list with filters (rarity, scenario availability).  
- FR2: Allow parent selection and factor resolution with preview of inherited bonuses.  
- FR3: Persist initial run state (stats, aptitudes, goals, mood, energy) with versioned snapshots.  
- FR4: Display real-time dashboard (stats, aptitudes, conditions, goals, support deck summary).  
- FR5: Support goal CRUD with validation against scenario timeline and distance/ground types.  
- FR6: Track mood/condition per turn; flag risky states (fatigue/injury warnings).  
- FR7: Expose API endpoints for creation, retrieval, updates, and snapshots (tie to SRS-3.1.x).  
- FR8: Maintain audit log of key changes (goal updates, condition changes, factor seeds).

---

## 5. User Interface Requirements
- New Run wizard with 3 steps: select trainee/scenario, choose parents/factors, confirm support deck overview.  
- Dashboard cards: stats with colored thresholds, aptitudes grid, mood/energy meter, goals timeline, support deck summary.  
- Alerts for unmet goals or risk states, with suggested next actions.  
- Accessibility: keyboard-first navigation, ARIA labels, color-contrast compliant (WCAG 2.2 AA).

---

## 6. Data and Integration
- Data entities: Character, Run, Scenario, Parent references, Inheritance Factors, Goals, Conditions, Snapshots.  
- Dependencies: SRS-3.1, SDS-4.1 domain model, DBD-009 schema (runs, goals, factors tables).  
- APIs: CRUD for runs, goals, snapshots; hooks for training (PRD-002) and races (PRD-003).  
- Inheritance factor calculation via FactorService; deck context read-only from Support Deck module (PRD-005).

---

## 7. Non-Functional Requirements
- Performance: create or load run in ≤1.5s (p95) on broadband.  
- Reliability: snapshots every N turns (configurable) with restore safety.  
- Security: enforce user ownership per run; mask shared links.  
- Compliance: follow WCAG 2.2 AA for UI, align terminology with 59 standardized requirements.

---

## 8. Success Metrics
- ≥95% completed run creations without validation errors.  
- ≤5% goal-missed incidents attributable to misconfiguration.  
- ≥80% of users use dashboard at least once per session.  
- Snapshot restore success rate ≥99%.

---

## 9. Release Plan
- Phase A: Wizard + basic run creation and dashboard read-only.  
- Phase B: Goals CRUD, mood/condition tracking, alerts.  
- Phase C: Snapshot scheduling and restore; audit log surfacing.

---

## 10. Open Questions and Assumptions
- Assumption: Factor rules provided by latest game data (see External Integration PRD-007).  
- Question: Should goals auto-import from scenario defaults or remain manual?  
- Question: Required retention window for run snapshots (default 30 days?).
