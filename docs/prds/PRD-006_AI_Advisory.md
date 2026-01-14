# PRD-006: AI Advisory System

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Project**: UmamusumeCareerPlanner  
**Author**: AI Development Team  
**Status**: Draft  
**Related Documents**: [SRS-3.6], [SDS-4.6], [MCP], [SPEC-006], [PRD-001], [PRD-002]

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
Provide AI-driven recommendations for training, skills, races, and deck tweaks with transparent rationale.

### 1.2 Problem Statement
Players need guidance tuned to their run state and strategy; generic advice misses context and increases failure risk.

### 1.3 Solution Overview
- Context builder aggregates run, deck, race plan, and goals.  
- AI advisor (hybrid local + Bedrock) outputs recommended action and rationale.  
- Confidence scoring, risk notes, and alternatives.

---

## 2. Product Overview

### 2.1 Objectives
- Deliver actionable, explainable advice in under 1.5s.  
- Respect safety/guardrails to avoid risky suggestions.  
- Learn from outcomes to adjust future recommendations.

### 2.2 Scope (In)
- Advice endpoints for training choice, skill purchase, race registration, deck tweak.  
- Rationale and risk sections; confidence score.  
- Feedback loop: user accept/override for model evaluation.  
- Prompt templates and safety filters.

### 2.3 Scope (Out)
- Full automation of gameplay; user approval required.  
- Chat-style long conversations (defer to future UX).

---

## 3. User Stories
- As a player, I want the AI to suggest my next action with reasons.  
- As a player, I want to see alternatives when risk is high.  
- As a player, I want to rate the advice to improve future suggestions.  
- As a coach, I want a log of advice vs actual actions.

---

## 4. Functional Requirements
- FR1: Build context payload from run state, deck, skills, goals, schedule (PRD-001/002/003/004/005).  
- FR2: Generate advice using hybrid AI pipeline (local + Bedrock) with prompt templates.  
- FR3: Return action, confidence, rationale, risks, and top alternatives.  
- FR4: Enforce safety filters: no contradictory or high-risk advice without warnings.  
- FR5: Collect feedback (accept/decline/reason) and log for retraining.  
- FR6: Provide API endpoints and telemetry hooks with trace IDs.  
- FR7: Fail gracefully with fallback heuristics if AI unavailable.

---

## 5. User Interface Requirements
- Advice panel with recommended action, confidence bar, rationale, risks, and alternatives.  
- Quick actions to accept/decline and apply to current turn.  
- Feedback buttons with reasons (helpful, risky, irrelevant).  
- Loading/degenerate states with fallback tips.  
- Accessible cards and buttons; keyboard shortcuts for accept/decline.

---

## 6. Data and Integration
- Inputs: consolidated context from PRD-001..005, race schedule, support events.  
- Services: ContextBuilder, AdvisoryModel (local + Bedrock), SafetyFilter, FeedbackStore.  
- Dependencies: MCP server configuration reference, SRS-3.6, SDS-4.6.  
- Telemetry: advice requests/responses, feedback, downstream action chosen.

---

## 7. Non-Functional Requirements
- Performance: advice response ≤1.5s (p95) with warm model; fallback ≤800ms.  
- Availability: degrade to heuristics when AI offline; clear UI state.  
- Safety: apply guardrails to block harmful or nonsensical advice; log filters.  
- Privacy: redact PII; store minimal run identifiers.

---

## 8. Success Metrics
- Advice acceptance rate ≥60%.  
- Reduction in risky turns (injury) by ≥15%.  
- CSAT on advice relevance ≥4.3/5.  
- Feedback coverage ≥50% of advice calls.

---

## 9. Release Plan
- Phase A: Context builder + prompt templates + deterministic fallback.  
- Phase B: Hybrid AI pipeline with confidence and alternatives; feedback capture.  
- Phase C: Continuous evaluation dashboard and auto-tuning.

---

## 10. Open Questions and Assumptions
- Assumption: Bedrock access keys available and rate limits sufficient.  
- Question: What offline mode experience is acceptable if both AI providers fail?  
- Question: Should advice history persist per run or globally?
