# WF-004: Training Selection Interface

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-002], [SPEC-002], [FLOW-002], [SEQ-002], [SEQ-003]

**Source Specs**:

- `.kiro/specs/umamusume-career-planner-main/requirements.md` (Requirement 2: Training Prediction Engine)
- `.kiro/specs/umamusume-career-planner-main/design.md` (Training Selection UI)

**Related Artifacts**:

- PRD: [PRD-002](../prds/PRD-002_Training_Optimization.md)
- SPEC: [SPEC-002](../specs/SPEC-002_Training_Optimization_Technical.md)
- Flow: [FLOW-002](../flows/FLOW-002_Training_Optimization_System.md)
- Tech Flow: [TECH-FLOW-002](../tech-flow/TECH-FLOW-002_Training_Optimization_Flow.md)
- Sequences: [SEQ-002](../sequences/SEQ-002_Training_Block_Resolution.md), [SEQ-003](../sequences/SEQ-003_Skill_Acquisition_and_Upgrade.md)
- User Flows: [UF-003](../user-flows/UF-003_Training_Day_Flow.md)
- Related WF: [WF-005](WF-005_Training_Result_Screen.md)

---

## Layout (Desktop)

```
+----------------------------------------------------------------------------------+
| Header: Training - Turn N | Actions: Skip | Rest | AI Advise                      |
+----------------------------------------------------------------------------------+
| Left Column (Predictions)                | Right Column (Run Snapshot)           |
|                                                                                |
| +-----------------------------------+   +-----------------------------------+   |
| | Training Spots (grid/list)        |   | Mood/Energy Widget                |   |
| | [Speed] Gains: +20,+10 Risk: 12%  |   | Condition, Debuffs               |   |
| | [Stamina] Gains: ...              |   +-----------------------------------+   |
| | [Power] ...                       |   | Support Bond Mini-bars            |   |
| | [Guts] ...                        |   +-----------------------------------+   |
| | [Wisdom] ...                      |   | Skills & SP Summary               |   |
| | [Race] ...                        |   +-----------------------------------+   |
| +-----------------------------------+   | Upcoming Race Reminder             |   |
|                                         +-----------------------------------+   |
+----------------------------------------------------------------------------------+
| Footer: CTA buttons per spot (Train) with confirmation modal if high risk        |
+----------------------------------------------------------------------------------+
```

## Layout (Mobile)

- Single-column list of training spots; snapshot collapses below the active card.  
- Sticky footer with "Rest" and "Train" actions.  
- Risk badge shown inline.

## Components

- Training cards: stat gains, bond gains, hint chance, risk badge, participating supports.  
- Risk badge colors: green <15%, amber 15-40%, red >40%.  
- Snapshot: energy bar, mood chip, condition icons, SP remaining, upcoming race card.

## Notes

- Sorting toggle: by highest gain, by lowest risk, by friendship presence.  
- Friendship glow around cards when bond ≥80 for any participant.  
- High-risk confirmation modal before execution.
