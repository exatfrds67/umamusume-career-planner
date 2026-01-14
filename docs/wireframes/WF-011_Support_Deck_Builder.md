# WF-011: Support Deck Builder

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-005], [SPEC-005], [FLOW-005], [SEQ-008]
**Source Specs**:

- `.kiro/specs/umamusume-career-planner-main/requirements.md` (Requirement 6: Support Card Configuration)
- `.kiro/specs/umamusume-career-planner-main/design.md` (Support Deck Builder UI)

**Related Artifacts**:

- PRD: [PRD-005](../prds/PRD-005_Support_Card_Management.md)
- SPEC: [SPEC-005](../specs/SPEC-005_Support_Card_Management_Technical.md)
- Flow: [FLOW-005](../flows/FLOW-005_Support_Card_Management_System.md)
- Tech Flow: [TECH-FLOW-005](../tech-flow/TECH-FLOW-005_Support_Card_Management_Flow.md)
- Sequences: [SEQ-005](../sequences/SEQ-005_Support_Card_Upgrade.md)
- User Flows: [UF-006](../user-flows/UF-006_Support_Deck_Building_Flow.md)
- Related WF: [WF-010](WF-010_Support_Card_Collection.md)

---

## Layout (Desktop)

```
+----------------------------------------------------------------------------------+
| Header: Deck Builder | Deck: "Speed Focus" | Slots 6/6 | Actions: Save | Auto   |
+----------------------------------------------------------------------------------+
| Left: Card Library (filters, search)       | Right: Deck Slots (6)               |
|                                                                                |
| Library rows: thumbnail, name, type, rarity, meta tier, bond.                   |
| Add button.                                                                    |
|                                                                                |
| Deck slots:                                                                    |
| [1] Card A (Speed)   [Swap] [Remove]                                           |
| [2] Card B (Stamina) ...                                                       |
| ...                                                                            |
| Friend slot indicator.                                                         |
|                                                                                |
| Under slots: Scoring summary: meta tier avg, type distribution, bond avg.      |
+----------------------------------------------------------------------------------+
| Footer: Buttons Save | Revert | Optimize | Export                               |
+----------------------------------------------------------------------------------+
```

## Layout (Mobile)

- Tabs: Deck | Library.  
- Slots list vertically; swap via select target.  
- Optimize button sticky.

## Notes

- Auto-optimize uses scoring weights: meta tier, rarity, level/LB, bond, bonuses vs desired training.  
- Warnings for type over-cap (e.g., >2 of same type excluding friend).  
- Export gives shareable code/string.
