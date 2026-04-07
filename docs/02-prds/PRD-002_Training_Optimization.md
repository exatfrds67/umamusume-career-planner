# PRD-002: Training Optimization Engine

## Umamusume Pretty Derby Career Planner

**Document Version**: 2.2.0
**Date**: January 28, 2026
**Project**: UmamusumeCareerPlanner
**Author**: Development Team
**Status**: Current - Aligned with codebase v2.2.0
**Related Documents**: [SRS-FR-03], [SDS-4.2], [DBD-4.4], [SPEC-002]

**Source Specs**:

- `.kiro/specs/umamusume-career-planner-main/requirements.md` (Requirements)
- `.kiro/specs/umamusume-career-planner-main/design.md` (Design)
- `.kiro/specs/umamusume-career-planner-main/tasks.md` (Implementation Tasks)

**Related Artifacts**:

- SPEC: [SPEC-002](../02-specs/SPEC-002_Training_Optimization_Technical.md)
- Flow: [FLOW-002](../01-flows/FLOW-002_Training_Optimization_System.md)
- Wireframes: [WF-004](../01-wireframes/WF-004_Training_Selection_Interface.md),
[WF-005](../01-wireframes/WF-005_Training_Result_Screen.md)
- Sequences: [SEQ-002](../01-sequences/SEQ-002_Training_Block_Resolution.md)
- User Flows: [UF-003](../01-user-flows/UF-003_Training_Day_Flow.md)

---

## Table of Contents

- [PRD-002: Training Optimization Engine](#prd-002-training-optimization-engine)
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

Deliver a highly accurate, turn-level training recommendation engine that predicts stat gains,
assesses failure risks, and optimizes support card bonding. This system leverages **Neuron AI
Agents** to provide contextual advice beyond simple stat math.

### 1.2 Problem Statement

Players often rely on intuition for training decisions, leading to suboptimal stat distributions,
missed skill hints, or catastrophic training failures due to unmanaged energy/mood levels.

### 1.3 Solution Overview

- **Prediction Engine**: Deterministic calculation of base gains + deck bonuses + friendship multipliers.
- **Risk Assessment**: Probability modeling for training failure based on energy, mood, and negative conditions.
- **AI Advisory**: Integration with **Neuron AI (TrainingAdvisorAgent)** to evaluate trade-offs
between immediate gains and long-term goals.

---

## 2. Product Overview

### 2.1 Objectives

- Provide accurate per-turn predictions (±5% variance from game values).
- Minimize injury risk by surfacing failure probabilities clearly.
- Maximize bond progression by highlighting optimal card stacking.
- Offer strategic "Why?" explanations via AI for recommended actions.

### 2.2 Scope (In)

- Simulation and recommendation for Speed, Stamina, Power, Guts, Wit, and Rest.
- Deterministic calculation of expected gains, support effects, risk, and hint opportunity.
- Advisory recommendations for the current run context.
- Training execution in **Account Mode** for authenticated persisted runs.
- Training planning and local-state progression in **Local Mode** where supported by browser-local run state.
- **AI Integration**: Training Advisor Agent for qualitative recommendations.

The product must not imply identical persistence guarantees between Local and Account modes.

### 2.3 Scope (Out)

- Race outcome simulation (see PRD-003).
- Skill purchase logic (see PRD-004).
- Deck editing during a run (restricted by game rules).

---

## 3. User Stories

| ID | Actor | Story | Acceptance Criteria |
| --- | --- | --- | --- |
| US-2.1 | Player | I want to see exactly how much Speed/Power I will gain before clicking a button. | The UI must show predicted `+Value` outputs for all supported facilities using currently available state. If advisory services are unavailable, deterministic prediction output must still be shown without blocking the user. |
| US-2.2 | Player | I want to know the risk of failure so I don't waste a turn. | Risk % displayed (Green/Yellow/Red) based on energy. |
| US-2.3 | Player | I want the system to tell me if a support card is offering a skill hint. | "Hint" icon appears next to the facility. |
| US-2.4 | Player | I want an AI recommendation for the best training choice when I'm unsure. | "Recommended" badge appears with a reasoning tooltip. |
| US-2.5 | Coach | I want to review past training sessions to analyze my strategy. | History log available showing chosen facility vs. gains. |

---

## 4. Functional Requirements

### 4.1 Prediction Engine [FR-03.1, FR-03.2]

The prediction engine must calculate outputs only from verified current run state, support deck
state, and facility context. If required input state is incomplete or unavailable, the system must
return a recoverable error or partial-state warning and must not fabricate missing values.

**Stat Calculation Formula** (Game-Accurate):

```text
Stat Gain = (Base + StatBonus)
          × (1 + GrowthRate)
          × (1 + MoodMultiplier × (1 + MoodEffect))
          × (1 + TrainingEffect)
          × (1 + 0.05 × NumSupportCards)
          × FriendshipMultiplier
```

**Key Components**:

- **Base**: Facility Base Value (Level 1-5 specific)
- **StatBonus**: Determined by "Stat Bonus" trait on support cards
- **GrowthRate**: Character-specific innate bonus
- **MoodMultiplier**: Great (+20%), Good (+10%), Normal (0%), Bad (-10%), Worst (-20%)
- **TrainingEffect**: "Training Effect Up" trait sum
- **NumSupportCards**: Count of support cards in training (Max +30% at 6 cards)
- **FriendshipMultiplier**: Product of (1 + FriendshipBonus) for each active rainbow card

**Caps**:

- **Per Training Cap**: +100 max gain per stat (reduced to +50 if stat > 1200)

**Facility Level Multipliers**:

| Level | Multiplier |
| --- | --- |
| 1 | 1.0× (base) |
| 2 | 1.25× |
| 3 | 1.5× |
| 4 | 1.75× |
| 5 | 2.0× |

**Facility Upgrade**: Train at a facility 4 times to upgrade it by 1 level (max level 5).

### 4.2 Support Card Integration [FR-03.4]

- **Bonus Aggregation**: Sum bonuses from all cards present in a facility.
- **Friendship Training**: Apply unique multipliers when card bond ≥ 80% (Orange bar).
  - Bonus range: 10% (unupgraded) to 35% (fully uncapped)
- **Type Synergy**: Apply scenario-specific bonuses (e.g., URA link bonuses).

### 4.3 Risk & Condition Modeling [FR-03.5]

Failure probability must remain bounded between 0% and 90% and be explainable from currently modeled
inputs. If a condition effect is not yet implemented or verified, the UI must label it as simplified
or unavailable rather than presenting it as game-accurate.

**Reference: Failure Probability** (game-accurate):

- Energy > 50%: 0% Risk (usually)
- Energy < 50%: Exponential risk increase

**Reference: Condition Impact**:

- "Lazy": Chance to refuse training
- "Overweight": Speed growth penalty
- "Skinny": Stamina growth penalty

### 4.4 Career Structure [FR-03.6]

**Career Timeline** (Game-Accurate):

- **Total turns**: ~70-78 turns
- **Duration**: 3 in-game years (Junior, Classic, Senior)

**Summer Training Camp**:

- **Duration**: 4 turns
- **Timing**: Early July (both Classic and Senior years)
- **Effect**: All facilities at maximum level (Level 5)
- **Strategy**: Have maximum energy and Great mood by Early July

### 4.5 AI Recommendations [FR-03.8]

- **Agent**: `TrainingAdvisorAgent` (Neuron AI)
- **Logic**: Evaluates goal alignment, turn efficiency, and risk tolerance
- **Output**: Ranked list of actions with "Confidence Score" and natural language reasoning

---

## 5. User Interface Requirements

### 5.1 Training Selection Panel

- **Facility Grid**: 5 cards (Speed, Stamina, Power, Guts, Wit) + 1 Rest card.
- **Gain Indicators**: Colored numbers (+20 Speed) showing projected gains.
- **Support Icons**: Small avatars of support cards present in each facility.
- **Risk Meter**: Progress bar or percentage showing failure chance.
- **Recommendation Badge**: "AI Pick" icon on the optimal choice.

### 5.2 Result Screen

- **Success/Failure Animation**: Visual feedback on training outcome.
- **Stat Delta**: Pop-up showing actual gains vs. predicted.
- **Event Trigger**: Modal if a support card event or skill hint occurred.

### 5.3 Mobile Responsiveness

- **Layout**: Stacked cards on mobile, horizontal grid on desktop.
- **Touch Targets**: 44px minimum for selection buttons.

---

## 6. Data and Integration

### 6.1 Data Models

- **Inputs**: `Character` (current stats, deck), `SupportDeck` (card details), `Scenario` (constants).
- **Outputs**: `TrainingPrediction` (transient), `TrainingSession` (persisted log).
- **Reference**: `ucp_training_sessions` table (see DBD).

### 6.2 External Data

- **Card Bonuses**: Sourced from `ucp_support_cards` (synced via PRD-007).
- **Game Constants**: Base gain values sourced from game data config.

### 6.3 Internal Services

- **TrainingService**: Orchestrates the prediction and execution logic.
- **HybridAIService**: Routes complex decision requests to the configured local or cloud provider path.
- **Redis Cache**: Caches predictions for 5 minutes (`training_prediction:{run_id}`).

---

## 7. Non-Functional Requirements

- **Performance**: Prediction generation for all facilities < 200ms (p95).
- **Accuracy**: Prediction math must match game logic within ±1 stat point.
- **Availability**: AI recommendations degrade gracefully if Cloud API is unreachable.
- **Persistence**: In `StorageMode::ACCOUNT`, every executed turn must be persisted immediately. In
`StorageMode::LOCAL`, the application may update browser-local run state without implying database-
backed history or reporting parity.

---

## 8. Success Metrics

- **Usage Rate**: > 90% of turns in active runs use the training interface.
- **AI Adherence**: > 60% of users select the AI-recommended training option.
- **Failure Reduction**: Users engaging with risk warnings have 20% fewer training failures.
- **Prediction Latency**: Average API response time < 150ms.

---

## 9. Release Plan

- **v2.0.0**:
  - Full deterministic prediction engine.
  - Support card bonus integration.
  - Risk calculation.
  - Basic AI recommendations.
- **v2.1.0**:
  - Advanced AI (long-term goal alignment).
  - "What-If" scenario mode.
  - Facility level-up tracking.
- **v2.2.0 (Current)**:
  - Game-accurate training formula with all multipliers.
  - Facility level multipliers (1.0× to 2.0×).
  - Summer Training Camp mechanics (4 turns, all Level 5).
  - Career structure (~70-78 turns across 3 years).

---

## 10. Open Questions and Assumptions

- **Assumption**: Support card effect logic follows standard URA scenario formulas.
- **Open Question**: How to handle RNG-based events during training? *Current: Predict normal
success, log actual result.*
- **Open Question**: Should we track specific facility levels (Lv 1-5) per run? *Current: Yes, tracked and displayed.*

---

## Changelog

| Version | Date | Changes |
| --- | --- | --- |
| 2.2.0 | January 28, 2026 | Updated with verified game mechanics from Global English Server: added complete training formula, facility level multipliers (1.0×-2.0×), Summer Training Camp mechanics (4 turns, all Level 5), career structure (~70-78 turns), stat gain caps (+100 normal, +50 above 1200). |
| 2.1.0 | January 24, 2026 | Aligned with codebase v2.0.0, added source specs references. |
| 2.0.0 | January 2026 | Initial v2 release with prediction engine. |
