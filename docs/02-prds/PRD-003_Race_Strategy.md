# PRD-003: Race Strategy System

## Umamusume Pretty Derby Career Planner

**Document Version**: 2.4.1
**Date**: March 11, 2026
**Project**: UmamusumeCareerPlanner
**Author**: Development Team
**Status**: Current - Aligned with codebase v2.4.0
**Related Documents**: [SRS-FR-04], [SDS-4.3], [DBD-4.2], [SPEC-003]

**Source Specs**:

- `.kiro/specs/umamusume-career-planner-main/requirements.md` (Requirements)
- `.kiro/specs/umamusume-career-planner-main/design.md` (Design)
- `.kiro/specs/umamusume-career-planner-main/tasks.md` (Implementation Tasks)

**Related Artifacts**:

- SPEC: [SPEC-003](../02-specs/SPEC-003_Race_Strategy_Technical.md)
- Flow: [FLOW-003](../01-flows/FLOW-003_Race_Strategy_System.md)
- Wireframes: [WF-006](../01-wireframes/WF-006_Race_Calendar_View.md),
[WF-007](../01-wireframes/WF-007_Race_Preparation_Screen.md)
- Sequences: [SEQ-004](../01-sequences/SEQ-004_Race_Registration_and_Outcome.md)
- User Flows: [UF-004](../01-user-flows/UF-004_Race_Day_Flow.md)

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

Provide a strategic command center for race management, enabling players to select the optimal race
rotation, assess pre-race readiness, and determine the best running style (Strategy) to maximize
victory probability.

### 1.2 Problem Statement

Players often enter races underprepared or with the wrong strategy, leading to unexpected losses,
missed alarm clock usage, and failed scenario objectives.

### 1.3 Solution Overview

- **Race Calendar**: A filtered view of eligible races based on current turn and character aptitudes.
- **Readiness Engine**: A scoring algorithm that compares current stats against race difficulty and rival strength.
- **Strategy Advisor**: AI-driven recommendation for the optimal Running Style based on stats,
skills, and track conditions.

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
| US-3.1 | Player | I want to see which races are available on the current turn. | The calendar must show available races for the current turn and storage context. If no races are available, the UI must show an explicit empty state rather than an empty grid. In Local mode, planning-only race views must not imply account-backed entry persistence. |
| US-3.2 | Player | I want to know if my stats are high enough to win a G1 race. | "Readiness" score displayed with specific warnings. |
| US-3.3 | Player | I want the system to tell me which running style gives the highest win chance. | Recommended strategy highlighted with reasoning. |
| US-3.4 | Player | I want to track my race history to analyze my win rate. | "Race Results" tab lists past placements and rewards. |
| US-3.5 | Player | I want to see how track conditions affect my performance. | Track condition penalties displayed in race preview. |

---

## 4. Functional Requirements

### 4.1 Race Calendar & Selection [FR-04.1]

The race calendar must distinguish among `available`, `not eligible`, and `future target` race
states. Filtering must not silently hide eligibility failures without explanation. A race hidden by
a user-selected filter and a race unavailable due to eligibility must remain distinguishable in the
user experience.

- **Filtering**: Filter races by Grade (G1-Pre-OP), Distance (Sprint-Long), and Surface (Turf/Dirt).
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

**Planner Display Note**: These penalties are planner-facing approximations used to communicate
likely performance impact. The game resolves track-condition effects through race simulation
variables rather than literal permanent stat subtraction.

**Weather Impact**:

- Weather determines track condition probability
- Drier weather = lower chance of wet conditions
- Snow = highest chance of Heavy condition
- Weather revealed on race day only

### 4.5 Strategy Optimization [FR-04.7]

The system must return: (1) a recommended running style, (2) ranked alternatives when available, and
(3) explainable contributing factors such as stats, aptitudes, mood, track conditions, and skill
fit. Recommendations must be derived from concrete current inputs and must not rely on opaque,
unverifiable reasoning.

### 4.6 Outcome Simulation [FR-04.6]

- **Rival Generation**: Generate synthetic rivals based on race grade difficulty curves.
- **Simulation**: Run statistical trials (Monte Carlo method) to determine win % probability.
- **Confidence**: Display confidence interval for the prediction.

### 4.7 Result Management [FR-04.2]

When a race result is recorded, the system must either persist the full result and rewards or return
a recoverable error state. The UI must not imply a successful save if the result could not be
persisted.

- **Rewards**: Auto-calculate fan/SP gains based on placement and race modifiers.
- **History**: Persist result to race log linked to `CareerRun` in Account Mode.

---

## 5. User Interface Requirements

### 5.1 Race Calendar View

- **Grid Layout**: Monthly view showing turns (Early/Late) and available races.
- **Readiness Badges**: Small colored dots (Green/Yellow/Red) on calendar slots.
- **Details Panel**: Slide-out panel showing race specifics when clicked.
- **Phase Filter**: Filter strip for Junior / Classic / Senior / All career phases.
- **Surface Filter**: Separate filter for Turf / Dirt (must not be combined with distance).
- **Distance Filter**: Separate filter for Sprint / Mile / Medium / Long / Super Long.
- **Month Display**: Month labels must use `month_label` strings from game data (e.g. "April",
"Early Summer"), grouped by `year_in_scenario`; not raw calendar month numbers.
- **Fan Requirement**: Each race card must display the minimum fan count required to enter.
- **SP Reward**: Each race card must display the SP points awarded upon winning.
- **URA Finale Badge**: Races with `is_ura_finale = true` must receive a distinct visual badge labelled "URA Finale".
- **Phase Badge**: Each race card must display a coloured phase badge (Junior / Classic / Senior / All).

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

### 5.4 Race Targets Interface

- **Grade Filter**: Filter buttons must use actual game grades in priority order: G1 / G2 / G3 / OP
/ Pre-OP / Debut. Legacy values "Listed" and "Open" are incorrect and must not appear.
- **Phase Filter**: Filter strip for Junior / Classic / Senior / All career phases.
- **Fan Requirement Per Race**: Each race row must display the minimum fans required to enter that race.
- **SP Reward Per Race**: Each race row must display the SP reward alongside the fans reward.
- **URA Finale Flag**: Races with `is_ura_finale = true` must be highlighted with a distinct badge.
- **Save Plan**: The save-plan action must preserve selected race targets in browser-local state
when only local planning is available. If an authenticated account persistence path exists, the UI
may offer a server-backed save action. If only browser-local planning is available, the interface
must state that clearly and must not imply account-backed synchronization.

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
- **v2.2.0**:
  - Game-accurate aptitude modifiers (G-S scale, no SS).
  - Track condition system with stat penalties.
  - Weather impact on track conditions.
- **v2.3.0**:
  - Race catalog expanded to **49 races** across Junior / Classic / Senior / All phases.
  - Removed 4 duplicate entries; added 22 new G1/G2/G3/Pre-Open races.
  - Game character catalog links each character to their target races via
  `ucp_game_character_target_races` with `is_goal` and `is_required` flags.
  - `GameRace` model updated with `gameCharacters()` reverse BelongsToMany relationship.
- **v2.4.0 (Current)**:
  - Calendar page: split combined race-type filter into separate Surface (Turf/Dirt) and Distance
  Category (Sprint/Mile/Medium/Long/Super Long) filters.
  - Calendar page: add Phase filter (Junior/Classic/Senior/All), URA Finale badge, fan-requirement and
  SP-reward display on each race card.
  - Calendar page: fix month display to use `month_label` string values grouped by `year_in_scenario`
  instead of raw numeric calendar months.
  - Targets page: correct grade filter values from incorrect `Listed/Open` to actual game grades `OP/Pre-OP/Debut`.
  - Targets page: add Phase filter, fan-requirement per race, SP-reward per race, URA Finale badge.
  - Both pages: `RaceController` enriched with `spReward`, `fanRequirement`, `statRequirements`,
  `distanceCategory`, and `surface` fields for targets.

---

## 10. Open Questions and Assumptions

- **Assumption**: Rival stats scale with race grade (simplified model).
- **Open Question**: How to model "blocked" states? *Current: Abstracted into Power/Guts check.*
- **Open Question**: Should we support "Rotation" planning? *Yes, implemented as "Race Goals".*

---

## Changelog

| Version | Date | Changes |
| --- | --- | --- |
| 2.4.1 | March 11, 2026 | Clarified that displayed track-condition penalties are planner-readable approximations of race impact, not literal permanent stat subtraction in the game engine. |
| 2.4.0 | March 4, 2026 | Calendar page: separate surface/distance filters, phase filter, URA Finale badge, fan-requirement and SP-reward display, corrected month-label display. Targets page: corrected grade filter values (OP/Pre-OP/Debut), phase filter, per-race fan-requirement and SP-reward, URA Finale badge. RaceController data contract enriched. |
| 2.3.0 | February 27, 2026 | Race catalog expanded to 49 races (22 new races added, 4 duplicates removed). Added `ucp_game_character_target_races` pivot linking characters to their canonical race targets. `GameRace` model updated with `gameCharacters()` reverse relationship. |
| 2.2.0 | January 28, 2026 | Updated with verified game mechanics from Global English Server: corrected aptitude scale (G-S, no SS), added aptitude modifier table, added track condition system with stat penalties (Firm/Good/Soft/Heavy), weather impact on track conditions. |
| 2.1.0 | January 24, 2026 | Aligned with codebase v2.0.0, added source specs references. |
| 2.0.0 | January 2026 | Initial v2 release with race calendar and readiness scoring. |
