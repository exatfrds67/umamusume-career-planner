# WF-006: Race Calendar View

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-003], [SPEC-003], [FLOW-003], [SEQ-004]

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
