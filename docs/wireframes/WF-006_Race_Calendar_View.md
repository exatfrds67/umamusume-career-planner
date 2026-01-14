# WF-006: Race Calendar View

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-003], [SPEC-003], [FLOW-003], [SEQ-004]
**Source Specs**:

- `.kiro/specs/umamusume-career-planner-main/requirements.md` (Requirement 3: Race Preparation and Strategy)
- `.kiro/specs/umamusume-career-planner-main/design.md` (Race Calendar UI)

**Related Artifacts**:

- PRD: [PRD-003](../prds/PRD-003_Race_Strategy.md)
- SPEC: [SPEC-003](../specs/SPEC-003_Race_Strategy_Technical.md)
- Flow: [FLOW-003](../flows/FLOW-003_Race_Strategy_System.md)
- Tech Flow: [TECH-FLOW-003](../tech-flow/TECH-FLOW-003_Race_Strategy_Flow.md)
- Sequences: [SEQ-004](../sequences/SEQ-004_Race_Registration_and_Outcome.md)
- User Flows: [UF-004](../user-flows/UF-004_Race_Day_Flow.md)
- Related WF: [WF-007](WF-007_Race_Preparation_Screen.md)

---

## Layout (Desktop)

```
+----------------------------------------------------------------------------------+
| Header: Race Calendar | Filter: Grade [All/G1/G2/G3] | Distance | Surface | Month |
+----------------------------------------------------------------------------------+
| Month Grid (calendar)                                                                 |
| Each cell: [Date] [Race Name] [Grade Pill] [Readiness% badge]                         |
| Tooltip on hover: distance, surface, rewards, entry deadline                         |
+----------------------------------------------------------------------------------+
| Right Sidebar:                                                                        |
| - Selected Race Detail                                                                |
|   Name, Grade, Distance/Surface, Prize, Entry Deadline                               |
|   Competitor strength estimate                                                        |
|   Buttons: Preview (win prob), Enter                                                 |
| - Prep Checklist                                                                      |
|   - Required stats vs current                                                         |
|   - Suggested trainings/skills                                                        |
+----------------------------------------------------------------------------------+
```

## Layout (Mobile)

- Calendar switches to list by week; filters as chips.  
- Selected race detail becomes bottom sheet.

## Components

- Filters: grade, distance, surface, month; clear-all button.  
- Readiness badge: color-coded; tap to view factors.  
- Preview CTA opens modal with win probability and recommendations.

## Notes

- Deadlines marked with warning icon if close; highlight current week.  
- Supports jump-to-turn for simulation mode.  
- Accessible focus order across cells; ensure keyboard navigation of calendar.
