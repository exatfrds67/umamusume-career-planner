# WF-008: Skill Shop Interface

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-004], [SPEC-004], [FLOW-004], [SEQ-006]
**Source Specs**:

- `.kiro/specs/umamusume-career-planner-main/requirements.md` (Requirement 4: Comprehensive Skill Management)
- `.kiro/specs/umamusume-career-planner-main/design.md` (Skill Shop UI)

**Related Artifacts**:

- PRD: [PRD-004](../prds/PRD-004_Skill_Management.md)
- SPEC: [SPEC-004](../specs/SPEC-004_Skill_Management_Technical.md)
- Flow: [FLOW-004](../flows/FLOW-004_Skill_Management_System.md)
- Tech Flow: [TECH-FLOW-004](../tech-flow/TECH-FLOW-004_Skill_Management_Flow.md)
- Sequences: [SEQ-003](../sequences/SEQ-003_Skill_Acquisition_and_Upgrade.md)
- User Flows: [UF-005](../user-flows/UF-005_Skill_Management_Flow.md)
- Related WF: [WF-009](WF-009_Skill_Loadout_Manager.md)

---

## Layout (Desktop)

```
+----------------------------------------------------------------------------------+
| Header: Skill Shop | SP: 620 | Filters: Type, Distance, Rarity, Cost, Hint Only  |
+----------------------------------------------------------------------------------+
| Two-Column Layout                                                                 |
| Left: Skill List                                                                  |
| - Search bar                                                                      |
| - Cards: Name, Type, Cost (after hint), Tags, Rarity                             |
| - Hint badge (0/20/40%)                                                          |
| - CTA: Buy                                                                       |
| Right: Detail Panel                                                              |
| - Skill description                                                              |
| - Prerequisites (distance/style)                                                 |
| - Evolution path (if any)                                                        |
| - Value score & recommendation flag                                              |
| - Related skills / synergies                                                     |
+----------------------------------------------------------------------------------+
| Footer: Selected skill summary, Confirm Purchase button                          |
+----------------------------------------------------------------------------------+
```

## Layout (Mobile)

- List first; detail opens as bottom sheet.  
- Sticky SP bar at top; Buy button within detail sheet.

## Notes

- Disable Buy if SP insufficient; show delta needed.  
- Evolution-capable skills highlight path; link to evolution criteria.  
- Sorting: cost asc, value desc, distance match.
