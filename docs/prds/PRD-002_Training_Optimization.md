# PRD-002: Training Optimization Engine

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Project**: UmamusumeCareerPlanner  
**Author**: AI Development Team  
**Status**: Draft  
**Related Documents**: [SRS-3.2], [SDS-4.2], [DBD-009], [SPEC-002], [PRD-001]

**Source Specs**:
- `.kiro/specs/umamusume-career-planner-main/requirements.md` (Requirements)
- `.kiro/specs/umamusume-career-planner-main/design.md` (Design)
- `.kiro/specs/umamusume-career-planner-main/tasks.md` (Implementation Tasks)

**Related Artifacts**:
- SPEC: [SPEC-002](../specs/SPEC-002_Training_Optimization_Technical.md)
- Flow: [FLOW-002](../flows/FLOW-002_Training_Optimization_System.md)
- Wireframes: [WF-004](../wireframes/WF-004_Training_Selection_Interface.md), [WF-005](../wireframes/WF-005_Training_Result_Screen.md)
- Sequences: [SEQ-002](../sequences/SEQ-002_Training_Block_Resolution.md)
- User Flows: [UF-003](../user-flows/UF-003_Training_Day_Flow.md)

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
Deliver turn-level training recommendations with predicted stat gains, risk, and bond growth to maximize run success.

### 1.2 Problem Statement
Players guess optimal training each turn; this leads to suboptimal stat distribution, missed goals, and injuries.

### 1.3 Solution Overview
- Turn context ingestion (stats, mood, energy, deck, goals, upcoming races).  
- Prediction of gains, risks, bond progress, and hint opportunities.  
- Ranked recommendations with rationale and what-if comparisons.

---

## 2. Product Overview

### 2.1 Objectives
- Provide accurate per-turn predictions using deck, facility levels, conditions, and support events.  
- Minimize injury risk by surfacing failure probabilities and fatigue impact.  
- Align training choices with mid/long-term goals (race readiness, stat caps).

### 2.2 Scope (In)
- Training action simulation (speed, stamina, power, guts, wisdom, rest).  
- Risk modeling (fatigue, failure rate, mood impact).  
- Bond gain projection and hint drop probabilities.  
- REST API for predictions and decision logs.

### 2.3 Scope (Out)
- Race outcome simulation (PRD-003).  
- Skill purchasing (PRD-004).  
- Deck editing (PRD-005).

---

## 3. User Stories
- As a player, I want to see stat/bond gains for each training option so I can pick the best action.  
- As a player, I want risk warnings when injury chance is high so I can avoid failed turns.  
- As a player, I want to compare top 3 options with rationale.  
- As a coach, I want to log chosen actions to review run quality.

---

## 4. Functional Requirements
- FR1: Load run context (stats, mood, energy, deck, support bonuses, facilities, goals, schedule).  
- FR2: Simulate each available action and compute gains, bond increases, hint probabilities, and energy deltas.  
- FR3: Compute risk score including injury chance, failure probability, and condition degradation.  
- FR4: Rank options by configurable strategy (max score, balanced, safety-first).  
- FR5: Return API response with top N options, full breakdown, and rationale (tie to SRS-3.2.x).  
- FR6: Persist chosen action and outcome deltas for analytics (telemetry).  
- FR7: Provide what-if endpoint for alternate decks or facility levels (optional after MVP).

---

## 5. User Interface Requirements
- Training panel shows each option with: predicted stat gains, bond gain, hint chance, risk meter, energy cost.  
- Filters for strategy style (balanced/speed/safety).  
- Tooltip with rationale and contributing factors.  
- Warnings when risk exceeds threshold; suggest rest if safest.  
- Keyboard navigation and screen reader labels for all buttons.

---

## 6. Data and Integration
- Inputs: run state from PRD-001, deck data from PRD-005, facility levels, upcoming race schedule.  
- Outputs: prediction payload stored in telemetry (see SEQ-011) and run diff (stats/mood/energy).  
- Services: TrainingSimulator, RiskModel, RecommendationEngine.  
- Dependencies: SRS-3.2, SDS-4.2, DBD-009 training tables, FactorService, Support bonuses.

---

## 7. Non-Functional Requirements
- Performance: prediction response ≤1.2s (p95) with warm cache.  
- Availability: degradation mode returns baseline heuristics if model unavailable.  
- Observability: log inputs/outputs with trace IDs; redact PII.  
- Accuracy: model error margin within ±5% vs validated dataset; track drift.

---

## 8. Success Metrics
- Reduction in failed turns due to injury by ≥20%.  
- ≥80% of players use recommendations on ≥50% of turns.  
- Mean stat target attainment improves by ≥10% vs baseline runs.  
- Prediction latency meets SLA in 95th percentile.

---

## 9. Release Plan
- Phase A: Deterministic simulator (static formulas) + risk meter.  
- Phase B: ML-driven scoring with strategy filters; decision logging.  
- Phase C: What-if comparisons and facility progression modeling.

---

## 10. Open Questions and Assumptions
- Assumption: Facility level data is available per turn.  
- Question: Should players pin preferred strategy per run or per turn?  
- Question: Model retrain cadence (weekly vs per event) and source-of-truth dataset.
