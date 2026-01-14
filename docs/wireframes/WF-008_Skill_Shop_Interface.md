# WF-008: Skill Shop Interface

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-004], [SPEC-004], [FLOW-004], [SEQ-006]

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
