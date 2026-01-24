# PRD-003: Race Strategy System

## Umamusume Pretty Derby Career Planner

**Document Version**: 2.1.0  
**Date**: January 24, 2026  
**Project**: UmamusumeCareerPlanner  
**Author**: Development Team  
**Status**: Current - Aligned with codebase v2.0.0  
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

Provide a strategic command center for race management, enabling players to select the optimal race rotation, assess pre-race readiness, and determine the best running style (Strategy) to maximize victory probability.

### 1.2 Problem Statement

Players often enter races underprepared or with the wrong strategy (e.g., using "Front Runner" with poor Stamina), leading to unexpected losses, missed alarm clock usage, and failed scenario objectives.

### 1.3 Solution Overview

- **Race Calendar**: A filtered view of eligible races based on current turn and character aptitudes.
- **Readiness Engine**: A scoring algorithm that compares current stats against race difficulty (Grade) and rival strength.
- **Strategy Advisor**: AI-driven recommendation for the optimal Running Style (Nige/Senkou/Sashi/Oikomi) based on stats and skills.

---

## 2. Product Overview

### 2.1 Objectives

- Help players pick optimal race schedules aligned to fan count goals and skill point needs.
- Provide clear "Red Light/Green Light" readiness indicators before registration.
- Deliver detailed win probability forecasts to manage risk.

### 2.2 Scope (In)

- **Race Catalog**: Searchable database of all URA/Scenario races with filters (Grade, Distance, Surface).
- **Readiness Assessment**: Scoring system (0-100) using stats, aptitudes, and skills.
- **Strategy Selection**: Recommendation logic for Running Style.
- **Outcome Simulation**: Probabilistic forecast of placement distribution (1st, 2nd-5th, 6th+).
- **Result Logging**: Tracking of actual race results for history and analytics.

### 2.3 Scope (Out)

- **Real-time Replay**: Visual 3D simulation of the race itself.
- **PvP Matchmaking**: Integration with the game's Team Stadium (Champions Meeting) matchmaking.

---

## 3. User Stories

| ID | Actor | Story | Acceptance Criteria |
|----|-------|-------|---------------------|
| US-3.1 | Player | I want to see which races are available on the current turn. | Calendar view shows G1/G2/G3/OP races eligible for entry. |
| US-3.2 | Player | I want to know if my stats are high enough to win a G1 race. | "Readiness" score displayed with specific warnings (e.g., "Stamina too low"). |
| US-3.3 | Player | I want the system to tell me which running style gives the highest win chance. | Recommended strategy (e.g., "Late Surger") is highlighted with reasoning. |
| US-3.4 | Player | I want to track my race history to analyze my win rate. | "Race Results" tab lists past placements and rewards. |
| US-3.5 | Coach | I want to simulate a race against typical rivals to test my build. | "Simulation" button generates a predicted placement distribution. |

---

## 4. Functional Requirements

### 4.1 Race Calendar & Selection [FR-04.1]

- **Filtering**: Filter races by Grade (G1-Pre-OP), Distance (Sprint-Long), and Surface (Turf/Dirt).
- **Eligibility**: Automatically hide races where the character does not meet baseline requirements (e.g., fan count).
- **Goal Alignment**: Highlight races that satisfy specific Scenario Objectives (e.g., "Win the Japan Cup").

### 4.2 Readiness Assessment [FR-04.5]

- **Scoring Formula**: Weighted average of:
  - Stat Sufficiency (vs. Grade baseline)
  - Distance/Surface Aptitude modifiers
  - Skill Activation Probability
  - Mood/Condition modifiers
- **Output**:
  - Score (0-100)
  - Classification (Excellent/Good/Fair/Poor)
  - Specific warnings (e.g., "Lack of Recovery Skills for Long Distance").

### 4.3 Strategy Optimization [FR-04.7]

- **Style Analysis**: Evaluate all 4 running styles against character Aptitudes and Stats.
- **Recommendations**: Suggest the style with the highest win probability.
- **AI Integration**: Use **Race Strategy Agent** (Neuron AI) to explain *why* a strategy is preferred (e.g., "Your high Power supports Late Surger acceleration").

### 4.4 Outcome Simulation [FR-04.6]

- **Rival Generation**: Generate synthetic rivals based on race grade difficulty curves.
- **Simulation**: Run statistical trials (Monte Carlo method) to determine win % probability.
- **Confidence**: Display confidence interval for the prediction.

### 4.5 Result Management [FR-04.2]

- **Input**: User records actual placement (1st-18th).
- **Rewards**: Auto-calculate fan/SP gains based on placement and race modifiers.
- **History**: Persist result to `ucp_training_sessions` (or dedicated race log) linked to `CareerRun`.

---

## 5. User Interface Requirements

### 5.1 Race Calendar View

- **Grid Layout**: Monthly view showing turns (Early/Late) and available races.
- **Readiness Badges**: Small colored dots (Green/Yellow/Red) on calendar slots indicating readiness for the best available race.
- **Details Panel**: Slide-out panel showing race specifics (Track, Weather, Rivals) when a race is clicked.

### 5.2 Preparation Screen

- **Header**: Race Name, Grade, Track info.
- **Readiness Gauge**: Circular gauge showing overall score.
- **Strategy Selector**: 4 cards for Running Styles, highlighting the recommended one.
- **Stat Comparison**: Radar chart comparing User vs. Average Rival.
- **Skill List**: List of equipped skills, dimming those unlikely to activate (e.g., wrong distance).

### 5.3 Post-Race Modal

- **Result Input**: Simple number input for placement.
- **Reward Confirmation**: Display of Fans/Stat/SP gained.
- **Analysis**: "Did you win?" check. If loss, provide AI analysis of potential causes (e.g., "Stamina depletion detected").

---

## 6. Data and Integration

### 6.1 Data Models

- **Inputs**:
  - `CareerRun` (Stats, Aptitudes, Skills)
  - `RaceDefinition` (Distance, Surface, Rivals)
- **Outputs**:
  - `RacePrediction` (Win %, Recommended Strategy)
  - `RaceResult` (Placement, Rewards)

### 6.2 External Integration

- **Race Data**: Sourced from `ucp_game_data` (synced via PRD-007 from umapyoi.net).
- **Rival Data**: Rival stats/skills templates sourced from external game databases.

### 6.3 AI Services

- **Race Strategy Agent**: Neuron AI agent providing qualitative advice.
- **Win Probability Model**: Statistical model (internal logic) or potentially AI-assisted for complex scenarios.

---

## 7. Non-Functional Requirements

- **Performance**: Readiness check must complete in < 500ms.
- **Accuracy**: Win probability should correlate with actual outcomes (within statistical variance).
- **Usability**: Calendar filters must persist across navigation.
- **Resilience**: If external race data is missing, fall back to generic templates based on Grade/Distance.

---

## 8. Success Metrics

- **Win Rate**: Users following recommendations achieve > 10% higher win rates in G1 races compared to baseline.
- **Adoption**: > 80% of race entries are logged through the planner.
- **Prediction Accuracy**: > 70% alignment between predicted outcome bucket and actual result.

---

## 9. Release Plan

- **v2.0.0 (Current)**:
  - Race Calendar with basic filtering.
  - Deterministic Readiness Scoring.
  - Strategy recommendation based on Aptitude/Stats.
- **v2.1.0 (Next)**:
  - Rival generation and specific rival analysis (named characters).
  - Advanced simulation (Monte Carlo).
  - Weather/Track Condition support.

---

## 10. Open Questions and Assumptions

- **Assumption**: Rival stats scale linearly with race grade (this is a simplification; actual game logic is complex).
- **Open Question**: How to model "blocked" states (where a character gets stuck behind others)? *Current Approach: Abstracted into Power/Guts check.*
- **Open Question**: Should we support "Rotation" planning (booking races in advance)? *Yes, implemented as "Race Goals".*
