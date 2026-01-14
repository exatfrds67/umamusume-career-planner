# WF-009: Skill Loadout Manager

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-004], [SPEC-004], [FLOW-004], [SEQ-006], [SEQ-007]

**Source Specs**:

- `.kiro/specs/umamusume-career-planner-main/requirements.md` (Requirement 4: Comprehensive Skill Management)
- `.kiro/specs/umamusume-career-planner-main/design.md` (Skill Loadout UI)

**Related Artifacts**:

- PRD: [PRD-004](../prds/PRD-004_Skill_Management.md)
- SPEC: [SPEC-004](../specs/SPEC-004_Skill_Management_Technical.md)
- Flow: [FLOW-004](../flows/FLOW-004_Skill_Management_System.md)
- Tech Flow: [TECH-FLOW-004](../tech-flow/TECH-FLOW-004_Skill_Management_Flow.md)
- User Flows: [UF-005](../user-flows/UF-005_Skill_Management_Flow.md)
- Related WF: [WF-008](WF-008_Skill_Shop_Interface.md)

---

## Layout (Desktop)

```
+----------------------------------------------------------------------------------+
| Header: Skill Loadout | Slots: 8/10 used | SP Remaining: 220 | Auto-Optimize     |
+----------------------------------------------------------------------------------+
| Left: Skill List (owned)                  | Right: Loadout Grid                   |
|                                                                                |
| List: filter by type, distance, rarity; search.                                 |
| Rows: Skill name, tags, rarity, cost, evolved badge.                            |
|                                                                                |
| Loadout Grid:                                                                   |
| [Slot 1] Skill A (rare) [x]                                                     |
| [Slot 2] Skill B (evolved) [x]                                                  |
| ...                                                                            |
| [Empty slots placeholders]                                                     |
| Drag/drop between list and grid.                                               |
+----------------------------------------------------------------------------------+
| Bottom Bar:                                                                     |
| - Coverage summary (distance/style)                                            |
| - Value score (with sparkline)                                                 |
| - Buttons: Save | Revert | Auto-Optimize                                      |
+----------------------------------------------------------------------------------+
```

## Layout (Mobile)

- Tabs: Loadout | Owned.  
- Drag/drop simplified to add/remove buttons; loadout shows list view.  
- Bottom sticky bar with Save/Revert/Auto.

## Notes

- Auto-optimize suggests best combination by value score within SP budget.  
- Show conflicts/duplicates warnings.  
- Evolution chains: tapping shows path and requirements.
