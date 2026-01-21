# WF-007: Race Preparation Screen

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-003], [SPEC-003], [FLOW-003], [SEQ-004]
**Source Specs**:

- `.kiro/specs/umamusume-career-planner-main/requirements.md` (Requirement 3: Race Preparation and Strategy)
- `.kiro/specs/umamusume-career-planner-main/design.md` (Race Preparation UI)

**Related Artifacts**:

- PRD: [PRD-003](../prds/PRD-003_Race_Strategy.md)
- SPEC: [SPEC-003](../specs/SPEC-003_Race_Strategy_Technical.md)
- Flow: [FLOW-003](../flows/FLOW-003_Race_Strategy_System.md)
- Tech Flow: [TECH-FLOW-003](../tech-flow/TECH-FLOW-003_Race_Strategy_Flow.md)
- Sequences: [SEQ-004](../sequences/SEQ-004_Race_Registration_and_Outcome.md)
- User Flows: [UF-004](../user-flows/UF-004_Race_Day_Flow.md)
- Related WF: [WF-006](WF-006_Race_Calendar_View.md)

---

## Layout (Desktop)

```
+----------------------------------------------------------------------------------+
| Header: Race Preparation | Race: Kyoto G2 | Actions: Preview Again | Enter Race   |
+----------------------------------------------------------------------------------+
| Two-Column Layout                                                                   |
| Left:                                                                              |
| +-------------------------------+                                                   |
| | Win Probability Card          |                                                   |
| | Win % gauge, readiness score  |                                                   |
| | Factors: stats, skills, mood  |                                                   |
| +-------------------------------+                                                   |
| | Competitors List              |                                                   |
| | Rows: Name, Strength, Style   |                                                   |
| | Toggle to view detail panel   |                                                   |
| +-------------------------------+                                                   |
|
| Right:                                                                             |
| +-------------------------------+                                                   |
| | Prep Recommendations          |                                                   |
| | - Train Speed 1x              |                                                   |
| | - Buy Skill X (SP 180)        |                                                   |
| | - Rest if energy < 40         |                                                   |
| +-------------------------------+                                                   |
| | Loadout Check                 |                                                   |
| | - Distance aptitude fit       |                                                   |
| | - Skills coverage             |                                                   |
| +-------------------------------+                                                   |
| | Upcoming Schedule             |                                                   |
| | Next races, deadlines         |                                                   |
| +-------------------------------+                                                   |
+----------------------------------------------------------------------------------+
| Footer: Buttons [Enter Race] [Save Plan] [Ask AI]                                  |
+----------------------------------------------------------------------------------+
```

## Layout (Mobile)

- Stack: Win prob, recommendations, competitors accordion, loadout check.  
- Sticky CTA bar for Enter Race.

## Notes

- Competitor detail drawer shows stats/skills on selection.  
- Recommendations clickable to auto-navigate (to training/skill shop).  
- Show warning if energy low or key skill missing.
