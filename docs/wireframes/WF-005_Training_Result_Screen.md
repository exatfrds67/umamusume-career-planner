# WF-005: Training Result Screen

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-002], [SPEC-002], [FLOW-002], [SEQ-003]

---

## Layout (Desktop)
```
+----------------------------------------------------------------------------------+
| Header: Training Result | Turn N                                                 |
+----------------------------------------------------------------------------------+
| Result Banner: Success / Failure badge | Gains summary                           |
+----------------------------------------------------------------------------------+
| Two-Column Content                                                             |
| Left:                                                                           |
| +-------------------------------+                                               |
| | Stat Gains                    |                                               |
| | Speed +20 (+5 friend bonus)   |                                               |
| | Stamina +10                   |                                               |
| | ...                           |                                               |
| +-------------------------------+                                               |
| | Bond Changes                  |                                               |
| | Card A +5 (milestone @80)     |                                               |
| | Card B +3                     |                                               |
| +-------------------------------+                                               |
| | Events Triggered              |                                               |
| | - Story event choice list     |                                               |
| +-------------------------------+                                               |
|
| Right:                                                                          |
| +-------------------------------+                                               |
| | Energy/Mood/Condition         |                                               |
| | Energy 62/100, Mood: Good     |                                               |
| +-------------------------------+                                               |
| | Hint Drops                    |                                               |
| | Skill X Hint Lv+1             |                                               |
| +-------------------------------+                                               |
| | History Timeline (mini)       |                                               |
| | Last 3 actions                |                                               |
| +-------------------------------+                                               |
+----------------------------------------------------------------------------------+
| Footer: Buttons [Next Training], [Return to Dashboard], [Ask AI]                 |
+----------------------------------------------------------------------------------+
```

## Layout (Mobile)
- Stack cards; result banner full width.  
- History timeline collapsible.  
- Footer buttons as bottom sheet.

## Notes
- Show RNG seed if reproducibility mode on.  
- If failure, display penalty details and encouragement tip.  
- AI quick action suggests next safest training or rest.
