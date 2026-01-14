# WF-010: Support Card Collection

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-005], [SPEC-005], [FLOW-005], [SEQ-008]
**Source Specs**:

- `.kiro/specs/umamusume-career-planner-main/requirements.md` (Requirement 6: Support Card Configuration)
- `.kiro/specs/umamusume-career-planner-main/design.md` (Support Card Collection UI)

**Related Artifacts**:

- PRD: [PRD-005](../prds/PRD-005_Support_Card_Management.md)
- SPEC: [SPEC-005](../specs/SPEC-005_Support_Card_Management_Technical.md)
- Flow: [FLOW-005](../flows/FLOW-005_Support_Card_Management_System.md)
- Tech Flow: [TECH-FLOW-005](../tech-flow/TECH-FLOW-005_Support_Card_Management_Flow.md)
- Sequences: [SEQ-005](../sequences/SEQ-005_Support_Card_Upgrade.md)
- User Flows: [UF-006](../user-flows/UF-006_Support_Deck_Building_Flow.md)
- Related WF: [WF-011](WF-011_Support_Deck_Builder.md)

---

## Layout (Desktop)

```
+----------------------------------------------------------------------------------+
| Header: Support Cards | Filters: Type, Rarity, Meta Tier, Character, Bonus       |
+----------------------------------------------------------------------------------+
| Grid of Cards (3-4 per row)                                                      |
| Card tile:                                                                       |
| - Art thumbnail                                                                  |
| - Name + Rarity                                                                  |
| - Type badge (Speed/Stamina/...)                                                 |
| - Meta tier pill (S/A/B)                                                         |
| - Bond level progress bar                                                        |
| - Buttons: Add to Deck, View                                                     |
+----------------------------------------------------------------------------------+
| Right Panel: Card Detail                                                         |
| - Stats/bonuses table                                                            |
| - Events list (choices and rewards)                                              |
| - Skills/hints available                                                         |
| - Recommendation badge (if meta)                                                 |
+----------------------------------------------------------------------------------+
```

## Layout (Mobile)

- Two-column card grid; detail opens in bottom sheet.  
- Filters collapse into chips.

## Notes

- Sorting: meta tier desc, rarity desc, bond desc.  
- Quick add to active deck (with slot selection).  
- Show last sync timestamp from external meta source.
