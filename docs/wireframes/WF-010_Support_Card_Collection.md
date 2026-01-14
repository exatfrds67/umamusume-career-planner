# WF-010: Support Card Collection

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-005], [SPEC-005], [FLOW-005], [SEQ-008]

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
