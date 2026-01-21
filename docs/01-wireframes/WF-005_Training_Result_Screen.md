# WF-005: Training Result Screen

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-002], [SPEC-002], [FLOW-002], [SEQ-003]
**Source Specs**:

- `.kiro/specs/umamusume-career-planner-main/requirements.md` (Requirement 2: Training Prediction Engine)
- `.kiro/specs/umamusume-career-planner-main/design.md` (Training Results UI)

**Related Artifacts**:

- PRD: [PRD-002](../prds/PRD-002_Training_Optimization.md)
- SPEC: [SPEC-002](../specs/SPEC-002_Training_Optimization_Technical.md)
- Flow: [FLOW-002](../flows/FLOW-002_Training_Optimization_System.md)
- Tech Flow: [TECH-FLOW-002](../tech-flow/TECH-FLOW-002_Training_Optimization_Flow.md)
- Sequences: [SEQ-002](../sequences/SEQ-002_Training_Block_Resolution.md)
- User Flows: [UF-003](../user-flows/UF-003_Training_Day_Flow.md)
- Related WF: [WF-004](WF-004_Training_Selection_Interface.md)

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
