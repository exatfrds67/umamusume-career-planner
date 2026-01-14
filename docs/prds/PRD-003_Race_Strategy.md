# PRD-003: Race Strategy System

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Project**: UmamusumeCareerPlanner  
**Author**: AI Development Team  
**Status**: Draft  
**Related Documents**: [SRS-3.3], [SDS-4.3], [DBD-009], [SPEC-003], [PRD-001], [PRD-002]

**Source Specs**:

- `.kiro/specs/umamusume-career-planner-main/requirements.md` (Requirements)
- `.kiro/specs/umamusume-career-planner-main/design.md` (Design)
- `.kiro/specs/umamusume-career-planner-main/tasks.md` (Implementation Tasks)

**Related Artifacts**:

- SPEC: [SPEC-003](../specs/SPEC-003_Race_Strategy_Technical.md)
- Flow: [FLOW-003](../flows/FLOW-003_Race_Strategy_System.md)
- Wireframes: [WF-006](../wireframes/WF-006_Race_Calendar_View.md), [WF-007](../wireframes/WF-007_Race_Preparation_Screen.md)
- Sequences: [SEQ-004](../sequences/SEQ-004_Race_Registration_and_Outcome.md)
- User Flows: [UF-004](../user-flows/UF-004_Race_Day_Flow.md)

---

## Table of Contents

- [PRD-003: Race Strategy System](#prd-003-race-strategy-system)
  - [Umamusume Pretty Derby Career Planner](#umamusume-pretty-derby-career-planner)
  - [Table of Contents](#table-of-contents)
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

Support race selection, preparation, and outcome simulation to maximize placements and rewards.

### 1.2 Problem Statement

Players over/under-train or choose poor race schedules; missing required stats and skills results in poor placements.

### 1.3 Solution Overview

- Race calendar with requirements and recommended readiness.  
- Pre-race readiness checks (stats, skills, condition).  
- Race outcome simulation and result logging with rewards.

---

## 2. Product Overview

### 2.1 Objectives

- Help players pick optimal race schedule aligned to goals and stat growth.  
- Provide readiness scoring and risk flags before registration.  
- Deliver post-race analytics to improve future decisions.

### 2.2 Scope (In)

- Race catalog search with filters (grade, distance, ground, date).  
- Readiness assessment using stats, aptitudes, skills, condition, support effects.  
- Outcome simulation (placement, rewards, condition changes).  
- Logging of rewards, fame, and condition deltas.

### 2.3 Scope (Out)

- Real-time race replay visualizations.  
- PvP matches; covered by external game client.

---

## 3. User Stories

- As a player, I want to know if my stats meet race requirements before registering.  
- As a player, I want the tool to suggest the next best race slots.  
- As a player, I want to see expected rewards and risks before committing.  
- As a coach, I want race history to analyze training quality.

---

## 4. Functional Requirements

- FR1: Provide race catalog with filters and scenario alignment.  
- FR2: Compute readiness score using stats, aptitudes, skills, condition, deck bonuses.  
- FR3: Validate registration (date conflicts, fatigue thresholds).  
- FR4: Simulate race outcome with probability distribution for placements.  
- FR5: Persist results (placement, rewards, fame, condition changes, injuries).  
- FR6: Expose API endpoints for listing races, registering, simulating, and retrieving history.  
- FR7: Generate recommendations for next races based on goals and season timeline.

---

## 5. User Interface Requirements

- Race finder table with filters and readiness badge.  
- Pre-race modal showing requirements, readiness score, risk meter, expected rewards.  
- Post-race summary card with placement, gains, and condition changes.  
- Alerting for over-scheduling or fatigue.  
- Accessible table sorting and keyboard navigation.

---

## 6. Data and Integration

- Inputs: character state (PRD-001), training outputs (PRD-002), skill set (PRD-004), deck buffs (PRD-005).  
- Data: race catalog, readiness model coefficients, reward tables.  
- Services: RaceService, ReadinessScorer, RaceSimulator.  
- Dependencies: SRS-3.3, SDS-4.3, DBD-009 race tables; external data sync via PRD-007.

---

## 7. Non-Functional Requirements

- Performance: readiness check and registration ≤1.0s (p95).  
- Reliability: prevent double booking; idempotent registration.  
- Accuracy: readiness score calibration within ±7% vs benchmark data.  
- Observability: log simulations with seeds for replay.

---

## 8. Success Metrics

- ≥90% of registered races meet readiness threshold.  
- Placement improvement ≥8% vs baseline runs without guidance.  
- Registration error rate <2% (e.g., date conflicts).  
- Simulation latency within SLA.

---

## 9. Release Plan

- Phase A: Race catalog + readiness check + registration validation.  
- Phase B: Outcome simulation with probability bands and rewards logging.  
- Phase C: Recommendation engine for schedules and post-race analytics.

---

## 10. Open Questions and Assumptions

- Assumption: Race catalog kept current via PRD-007 sync.  
- Question: How to handle event-limited races (cutoff logic)?  
- Question: Should readiness weights adapt per scenario or remain global?
