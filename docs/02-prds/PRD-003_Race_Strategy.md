# PRD-003: Race Strategy System

## Umamusume Pretty Derby Career Planner

**Document Version**: 2.2.0  
**Date**: January 28, 2026  
**Project**: UmamusumeCareerPlanner  
**Author**: Development Team  
**Status**: Current - Aligned with codebase v2.2.0  
**Related Documents**: [SRS-FR-04], [SDS-4.3], [DBD-4.2], [SPEC-003]

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

Provide a strategic command center for race management, enabling players to select the optimal race rotation, assess pre-race readiness, and determine the best running style (Strategy) to maximize victory probability.

### 1.2 Problem Statement

Players often enter races underprepared or with the wrong strategy, leading to unexpected losses, missed alarm clock usage, and failed scenario objectives.

### 1.3 Solution Overview

- **Race Calendar**: A filtered view of eligible races based on current turn and character aptitudes.
- **Readiness Engine**: A scoring algorithm that compares current stats against race difficulty and rival strength.
- **Strategy Advisor**: AI-driven recommendation for the optimal Running Style based on stats, skills, and track conditions.

---

## 2. Product Overview

### 2.1 Objectives

- Help players pick optimal race schedules aligned to fan count goals and skill point needs.
- Provide clear "Red Light/Green Light" readiness indicators before registration.
- Deliver detailed win probability forecasts to manage risk.

### 2.2 Scope (In)

- **Race Catalog**: Searchable database of all URA/Scenario races with filters.
- **Readiness Assessment**: Scoring system (0-100) using stats, aptitudes, and skills.
- **Strategy Selection**: Recommendation logic for Running Style.
- **Track Conditions**: Weather and ground condition impact modeling.
- **Outcome Simulation**: Probabilistic forecast of placement distribution.
- **Result Logging**: Tracking of actual race results for history and analytics.

### 2.3 Scope (Out)

- **Real-time Replay**: Visual 3D simulation of the race itself.
- **PvP Matchmaking**: Integration with the game's Team Stadium matchmaking.

---

## 3. User Stories

| ID | Actor | Story | Acceptance Criteria |
| --- | --- | --- | --- |
| US-3.1 | Player | I want to see which races are available on the current turn. | Calendar view shows G1/G2/G3/OP races eligible for entry. |
| US-3.2 | Player | I want to know if my stats are high enough to win a G1 race. | "Readiness" score displayed with specific warnings. |
| US-3.3 | Player | I want the system to tell me which running style gives the highest win chance. | Recommended strategy highlighted with reasoning. |
| US-3.4 | Player | I want to track my race history to analyze my win rate. | "Race Results" tab lists past placements and rewards. |
| US-3.5 | Player | I want to see how track conditions affect my performance. | Track condition penalties displayed in race preview. |

---

## 4. Functional Requirements

### 4.1 Race Calendar & Selection [FR-04.1]

- **Filtering**: Filter races by Grade (G1-Pre-OP), Distance (Sprint-Long), and Surface (Turf/Dirt).
- **Eligibility**: Automatically hide races where the character does not meet baseline requirements.
- **Goal Alignment**: Highlight races that satisfy specific Scenario Objectives.

### 4.2 Readiness Assessment [FR-04.5]

- **Scoring Formula**: Weighted average of:
  - Stat Sufficiency (vs. Grade baseline)
  - Distance/Surface Aptitude modifiers
  - Skill Activation Probability
  - Mood/Condition modifiers
  - Track Condition impact
- **Output**:
  - Score (0-100)
  - Classification (Excellent/Good/Fair/Poor)
  - Specific warnings (e.g., "Lack of Recovery Skills for Long Distance")

### 4.3 Aptitude System (Game-Accurate) [FR-04.3]

**Aptitude Grade Scale**: G → F → E → D → C → B → A → S (Maximum is S; SS does not exist)

**Aptitude Performance Modifiers**:

| Rank | Surface (Power) | Distance (Speed) | Style (Wit) |
| --- | --- | --- | --- |
| S | +5% | +5% | +10% |
| A | 0% (baseline) | 0% (baseline) | 0% (baseline) |
| B | -10% | -10% | -15% |
| C | -20% | -20% | -25% |
| D | -30% | -40% | -40% |
| E | -50% | -60% | -60% |
| F | -70% | -80% | -80% |
| G | -90% | -90% | -90% |

**Key Notes**:

- A-rank is the baseline (0% bonus/penalty)
- Only S-rank provides positive bonuses
- All grades below A incur penalties

### 4.4 Track Conditions (Game-Accurate) [FR-04.4]

**Weather Types**: Sunny, Cloudy, Rainy, Snowy

**Track Condition Effects**:

| Condition | Surface | Power Penalty | Speed Penalty | Stamina Drain |
| --- | --- | --- | --- | --- |
| Firm | Turf/Dirt | None | None | None |
| Good | Turf | -50 | None | None |
| Good | Dirt | -50 | None | None |
| Soft | Turf | -50 | None | +2%/sec |
| Soft | Dirt | -100 | None | +2%/sec |
| Heavy | Turf | -50 | -50 | +2%/sec |
| Heavy | Dirt | -100 | -50 | +2%/sec |

**Classification**: Good, Soft, and Heavy are all classified as "Wet" conditions.

**Weather Impact**:

- Weather determines track condition probability
- Drier weather = lower chance of wet conditions
- Snow = highest chance of Heavy condition
- Weather revealed on race day only

### 4.5 Strategy Optimization [FR-04.7]

- **Style Analysis**: Evaluate all 4 running styles against character Aptitudes and Stats.
- **Recommendations**: Suggest the style with the highest win probability.
- **AI Integration**: Use **Race Strategy Agent** to explain *why* a strategy is preferred.

### 4.6 Outcome Simulation [FR-04.6]

- **Rival Generation**: Generate synthetic rivals based on race grade difficulty curves.
- **Simulation**: Run statistical trials (Monte Carlo method) to determine win % probability.
- **Confidence**: Display confidence interval for the prediction.

### 4.7 Result Management [FR-04.2]

- **Input**: User records actual placement (1st-18th).
- **Rewards**: Auto-calculate fan/SP gains based on placement and race modifiers.
- **History**: Persist result to race log linked to `CareerRun`.

---

## 5. User Interface Requirements

### 5.1 Race Calendar View

- **Grid Layout**: Monthly view showing turns (Early/Late) and available races.
- **Readiness Badges**: Small colored dots (Green/Yellow/Red) on calendar slots.
- **Details Panel**: Slide-out panel showing race specifics when clicked.

### 5.2 Preparation Screen

- **Header**: Race Name, Grade, Track info.
- **Readiness Gauge**: Circular gauge showing overall score.
- **Strategy Selector**: 4 cards for Running Styles, highlighting the recommended one.
- **Stat Comparison**: Radar chart comparing User vs. Average Rival.
- **Track Conditions**: Display current/predicted conditions with stat penalties.
- **Skill List**: List of equipped skills, dimming those unlikely to activate.

### 5.3 Post-Race Modal

- **Result Input**: Simple number input for placement.
- **Reward Confirmation**: Display of Fans/Stat/SP gained.
- **Analysis**: If loss, provide AI analysis of potential causes.

---

## 6. Data and Integration

### 6.1 Data Models

- **Inputs**:
  - `CareerRun` (Stats, Aptitudes, Skills)
  - `RaceDefinition` (Distance, Surface, Rivals)
  - `TrackCondition` (Weather, Ground state)
- **Outputs**:
  - `RacePrediction` (Win %, Recommended Strategy)
  - `RaceResult` (Placement, Rewards)

### 6.2 External Integration

- **Race Data**: Sourced from `ucp_game_data` (synced via PRD-007).
- **Rival Data**: Rival stats/skills templates sourced from external game databases.

### 6.3 AI Services

- **Race Strategy Agent**: Neuron AI agent providing qualitative advice.
- **Win Probability Model**: Statistical model for complex scenarios.

---

## 7. Non-Functional Requirements

- **Performance**: Readiness check must complete in < 500ms.
- **Accuracy**: Win probability should correlate with actual outcomes.
- **Usability**: Calendar filters must persist across navigation.
- **Resilience**: If external race data is missing, fall back to generic templates.

---

## 8. Success Metrics

- **Win Rate**: Users following recommendations achieve > 10% higher win rates in G1 races.
- **Adoption**: > 80% of race entries are logged through the planner.
- **Prediction Accuracy**: > 70% alignment between predicted outcome bucket and actual result.

---

## 9. Release Plan

- **v2.0.0**:
  - Race Calendar with basic filtering.
  - Deterministic Readiness Scoring.
  - Strategy recommendation based on Aptitude/Stats.
- **v2.1.0**:
  - Rival generation and specific rival analysis.
  - Advanced simulation (Monte Carlo).
- **v2.2.0 (Current)**:
  - Game-accurate aptitude modifiers (G-S scale, no SS).
  - Track condition system with stat penalties.
  - Weather impact on track conditions.

---

## 10. Open Questions and Assumptions

- **Assumption**: Rival stats scale with race grade (simplified model).
- **Open Question**: How to model "blocked" states? *Current: Abstracted into Power/Guts check.*
- **Open Question**: Should we support "Rotation" planning? *Yes, implemented as "Race Goals".*

---

## Changelog

| Version | Date | Changes |
| --- | --- | --- |
| 2.2.0 | January 28, 2026 | Updated with verified game mechanics from Global English Server: corrected aptitude scale (G-S, no SS), added aptitude modifier table, added track condition system with stat penalties (Firm/Good/Soft/Heavy), weather impact on track conditions. |
| 2.1.0 | January 24, 2026 | Aligned with codebase v2.0.0, added source specs references. |
| 2.0.0 | January 2026 | Initial v2 release with race calendar and readiness scoring. |
