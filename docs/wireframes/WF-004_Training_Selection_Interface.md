# WF-004: Training Selection Interface

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-002], [SPEC-002], [FLOW-002], [SEQ-002], [SEQ-003]

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
