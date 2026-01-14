# PRD-005: Support Card Management

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Project**: UmamusumeCareerPlanner  
**Author**: AI Development Team  
**Status**: Draft  
**Related Documents**: [SRS-3.5], [SDS-4.5], [DBD-009], [SPEC-005], [PRD-001], [PRD-002], [PRD-004]

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
Manage support card inventory, decks, and upgrades to maximize training outcomes and bond gains.

### 1.2 Problem Statement
Players struggle to pick optimal decks and track upgrade materials; suboptimal decks reduce training gains and hint availability.

### 1.3 Solution Overview
- Deck builder with synergy scores and facility coverage checks.  
- Inventory management with upgrade/limit-break material tracking.  
- Validation to prevent illegal decks and highlight gaps.

---

## 2. Product Overview

### 2.1 Objectives
- Optimize deck composition for target strategy and race plan.  
- Surface coverage gaps (training types, friend/support effects).  
- Track upgrade progress and material requirements.

### 2.2 Scope (In)
- Deck creation/editing (six cards) with validation rules.  
- Synergy scoring vs training plan and race targets.  
- Inventory and upgrade flow with material/rarity tracking.  
- Export/import deck templates.

### 2.3 Scope (Out)
- Training simulation (PRD-002) except for providing bonuses and events.  
- Real-money transactions for card acquisition.

---

## 3. User Stories
- As a player, I want to build a deck that boosts my target stats.  
- As a player, I want to know which cards conflict or overlap excessively.  
- As a player, I want to see material needs to upgrade a card to the next limit break.  
- As a coach, I want deck usage analytics across runs.

---

## 4. Functional Requirements
- FR1: Manage inventory with rarity, level, bond bonus, hint bonus, event list.  
- FR2: Validate deck composition (slot count, duplicates, scenario restrictions).  
- FR3: Compute deck synergy score vs training plan and race goals; flag coverage gaps.  
- FR4: Provide recommendations for replacements based on desired stat focus.  
- FR5: Track upgrade/limit-break materials and costs; update card stats on upgrade.  
- FR6: Expose APIs for deck CRUD, validation, scoring, and upgrade operations.  
- FR7: Integrate deck bonuses into training predictions (PRD-002) and readiness (PRD-003).

---

## 5. User Interface Requirements
- Deck builder grid with card slots, synergy score, and gap badges.  
- Card detail drawer: stats, skills, events, bonuses, upgrade path, material needs.  
- Validation badges and warnings on deck save.  
- Import/export buttons for templates; QR/URL share if allowed.  
- Accessible drag/drop alternatives; keyboard slot assignment.

---

## 6. Data and Integration
- Data: support cards, events, bonuses, material tables, synergy weights.  
- Inputs: training plan (PRD-002), race targets (PRD-003), skill plan (PRD-004).  
- Services: SupportDeckService, SynergyScorer, UpgradeService.  
- Dependencies: SRS-3.5, SDS-4.5, DBD-009 support tables; external data updates via PRD-007.

---

## 7. Non-Functional Requirements
- Performance: deck validation/scoring ≤900ms (p95).  
- Consistency: atomic deck saves; prevent partial updates.  
- Observability: deck change audits; upgrade outcome logs.  
- Accessibility: WCAG 2.2 AA for deck builder interactions.

---

## 8. Success Metrics
- Deck save success rate ≥98%.  
- Synergy score improvement ≥12% vs user’s baseline deck.  
- Upgrade completion tracking accuracy ≥99%.  
- User satisfaction ≥4.4/5 for deck builder.

---

## 9. Release Plan
- Phase A: Inventory + deck CRUD + validation.  
- Phase B: Synergy scoring and recommendations; upgrade flow.  
- Phase C: Template sharing and analytics.

---

## 10. Open Questions and Assumptions
- Assumption: Card event data stays up to date via PRD-007 sync.  
- Question: Allow duplicate friend cards if game rules change?  
- Question: Should synergy scoring be scenario-specific or global?
