# PRD-005: Support Card Management System

## Umamusume Pretty Derby Career Planner

**Document Version**: 2.1.0  
**Date**: January 24, 2026  
**Project**: UmamusumeCareerPlanner  
**Author**: Development Team  
**Status**: Current - Aligned with codebase v2.0.0  
**Related Documents**: [SRS-FR-06], [SDS-4.5], [DBD-4.5], [SPEC-005]

**Source Specs**:

- `.kiro/specs/umamusume-career-planner-main/requirements.md` (Requirements)
- `.kiro/specs/umamusume-career-planner-main/design.md` (Design)
- `.kiro/specs/umamusume-career-planner-main/tasks.md` (Implementation Tasks)

**Related Artifacts**:

- SPEC: [SPEC-005](../specs/SPEC-005_Support_Card_Management_Technical.md)
- Flow: [FLOW-005](../flows/FLOW-005_Support_Card_Management_System.md)
- Wireframes: [WF-010](../wireframes/WF-010_Support_Card_Collection.md), [WF-011](../wireframes/WF-011_Support_Deck_Builder.md)
- Sequences: [SEQ-005](../sequences/SEQ-005_Support_Card_Upgrade.md)
- User Flows: [UF-006](../user-flows/UF-006_Support_Deck_Building_Flow.md)

---

## Table of Contents

- [PRD-005: Support Card Management System](#prd-005-support-card-management-system)
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

Provide a centralized system for managing support card inventories, constructing optimal decks, and tracking meta-relevance to maximize training efficiency and skill acquisition.

### 1.2 Problem Statement

Players struggle to select the best combination of 6 cards from hundreds of options. Without tools to calculate synergy or track bond milestones, players often build suboptimal decks that fail to support their training goals or provide necessary skill hints.

### 1.3 Solution Overview

- **Inventory Manager**: Track owned cards, limit break (LB) levels, and levels.
- **Deck Builder**: Interactive 6-slot builder (5 Owned + 1 Friend) with real-time synergy scoring.
- **Meta Integration**: Automatic synchronization of tier list rankings from external community sources.

---

## 2. Product Overview

### 2.1 Objectives

- Simplify deck composition by highlighting cards that complement the character's growth rates and target race.
- Provide visibility into "Bond" progression mechanics to optimize Friendship Training timing.
- Ensure card data (effects, events) remains current via external API sync.

### 2.2 Scope (In)

- **Collection**: CRUD operations for user's card inventory.
- **Deck Management**: Creation, validation, and storage of `SupportDeck` configurations.
- **Analysis**: Calculation of total deck bonuses (e.g., "Total Speed Bonus +5").
- **Bond Tracking**: Monitoring bond gauges (0-100) and milestone rewards during training.
- **External Sync**: Fetching card metadata (Images, Rarity, Type) from `umapyoi.net`.

### 2.3 Scope (Out)

- **Gacha Simulation**: No "pull" mechanics or probability simulators.
- **Trade System**: No card trading between users (cards are account-bound).

---

## 3. User Stories

| ID | Actor | Story | Acceptance Criteria |
|----|-------|-------|---------------------|
| US-5.1 | Player | I want to register which SSR cards I own and their limit break level. | Inventory view allows adding cards and setting LB (0-4). |
| US-5.2 | Player | I want to build a deck with 3 Speed and 2 Intelligence cards. | Deck builder validates types and counts; warns if unbalanced. |
| US-5.3 | Player | I want to borrow a "Friend" card that I don't own. | The 6th slot allows selection from the global database, not just inventory. |
| US-5.4 | Player | I want to see which cards are currently "S-Tier" in the meta. | Cards display a "Meta Tier" badge synced from external sources. |
| US-5.5 | Coach | I want to know total "Race Bonus" provided by my deck. | Summary panel sums up specific effect values across all 6 cards. |

---

## 4. Functional Requirements

### 4.1 Inventory Management [FR-06.1]

- **Card Database**: Maintain a local cache of all available game cards (`ucp_support_cards`) synced via PRD-007.
- **User Ownership**: Track specific instances of cards owned by the user, including Level and Limit Break (LB) status.
- **Filtering**: Filter by Type (Speed/Stamina/etc.), Rarity (R/SR/SSR), and Meta Tier.

### 4.2 Deck Building Logic [FR-06.2]

- **Composition Rules**:
  - Max 6 cards total.
  - Max 5 cards from User Inventory.
  - Max 1 card from Friend/Global pool.
  - No duplicate character names allowed (e.g., cannot have SSR Special Week and R Special Week).
- **Synergy Scoring**: Calculate a score (0-100) based on:
  - Alignment with Character Growth Rates (e.g., Speed cards for Speed growth char).
  - Coverage of needed Skills.
  - Rarity/Level power.

### 4.3 Bonus Calculation [FR-06.4]

- **Effect Aggregation**: Sum effects like `training_effect_up`, `race_bonus`, `fan_bonus`, `skill_pt_bonus`.
- **Training Integration**: Expose these aggregates to the Training Optimization Engine (PRD-002) to adjust gain predictions.

### 4.4 Bond & Event Tracking [FR-06.3]

- **Bond Gauge**: Track bond points (0-100) per card during a run.
- **Thresholds**:
  - 80+: Enable Rainbow/Friendship Training.
  - 60+: Enable Card Events.
- **Event Lookup**: Provide quick access to event choices and outcomes (e.g., "Top choice gives Speed +10").

### 4.5 Meta Synchronization [FR-06.5]

- **Sync Job**: Periodically fetch tier list data from configured external sources.
- **Visual Indicators**: Display tier badges (SS, S, A, B) on card faces.

---

## 5. User Interface Requirements

### 5.1 Card Collection View

- **Grid Layout**: Responsive grid of card thumbnails.
- **Status Indicators**: Badges for "Owned", "LB Level" (e.g., 3★), and "Meta Tier".
- **Quick Edit**: Click on a card to toggle ownership or adjust LB level without leaving the grid.

### 5.2 Deck Builder Interface

- **Slot View**: 6 clear slots. Slot 6 visually distinct (Friend slot).
- **Drag & Drop**: Ability to drag cards from inventory sidebar into slots.
- **Stats Radar**: Real-time radar chart showing the deck's bias (e.g., heavy Speed, low Guts).
- **Warnings**: Visual alerts for "Duplicate Character" or "Empty Slot".

### 5.3 Card Detail Modal

- **Header**: Large artwork, Name, Title.
- **Bonuses Table**: List of all unique bonuses at current level.
- **Skills**: List of skills this card can teach (Hints) or give (Events).
- **Event Cheatsheet**: Collapsible list of events and the best answers.

---

## 6. Data and Integration

### 6.1 Data Models

- **Entities**:
  - `SupportCard` (Reference data)
  - `SupportDeck` (User configuration)
  - `CareerRun` (Links to used deck)
- **Relationships**: A `CareerRun` belongs to a `SupportDeck`. A `SupportDeck` has many `SupportCards`.

### 6.2 External Data (via PRD-007)

- **Source**: `umapyoi.net` API.
- **Fields**: Name, Rarity, Type, Image URL, Max Stats, Unique Effect logic.

### 6.3 Internal Integration

- **Training (PRD-002)**: Deck bonuses directly modify training gain formulas.
- **Skills (PRD-004)**: Cards determine the pool of available skill hints.
- **Character (PRD-001)**: Decks are assigned during the creation wizard.

---

## 7. Non-Functional Requirements

- **Responsiveness**: Drag-and-drop actions must be jank-free (60fps).
- **Data Freshness**: Meta tiers update within 24 hours of external source changes.
- **Validation**: Server-side validation of decks prevents illegal configurations even if client-side checks fail.
- **Storage**: Decks are persisted to `ucp_support_decks` and linked to User ID.

---

## 8. Success Metrics

- **Builder Usage**: > 95% of Career Runs have a valid 6-card deck assigned.
- **Optimization**: Users interacting with the "Synergy Score" build decks with 15% higher average output.
- **Inventory Tracking**: Active users track an average of 30+ cards in their inventory.

---

## 9. Release Plan

- **v2.0.0 (Current)**:
  - Inventory management (Owned/LB).
  - Basic Deck Builder (5+1 slots).
  - Bonus aggregation logic.
  - External data sync for card list.
- **v2.1.0 (Next)**:
  - "Auto-Fill" deck based on strategy (e.g., "Max Speed").
  - Event choice helper during training.
  - Deck sharing via shortlink.

---

## 10. Open Questions and Assumptions

- **Assumption**: The "Friend" card pool allows selecting any card in the database, regardless of ownership.
- **Open Question**: How to handle "Group" type cards which have different event mechanics? *Current: Treated as normal cards with specific event triggers.*
- **Open Question**: Should we track specific card levels (1-50)? *Current: Yes, inferred from Rarity/LB, but editable.*
