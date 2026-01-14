# UF-006: Support Deck Building Flow

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-005], [SPEC-005], [FLOW-005], [SEQ-008], [WF-011]

---

## Flow Diagram
```mermaid
flowchart TD
    Start([Need deck]) --> OpenDeck[Open deck builder]
    OpenDeck --> SelectBase[Select deck template or empty]
    SelectBase --> AddCards[Add cards from library]
    AddCards --> ValidateTypes{Type constraints OK?}
    ValidateTypes -->|No| WarnTypes[Warn: too many of same type]
    WarnTypes --> AdjustDeck[Adjust selection]
    AdjustDeck --> ValidateTypes
    ValidateTypes -->|Yes| ScoreDeck[Score deck]
    ScoreDeck --> Optimize{Auto-optimize?}
    Optimize -->|Yes| RunOptimize[Apply optimization]
    RunOptimize --> Review[Review slots]
    Optimize -->|No| Review
    Review --> SaveDeck[Save deck]
    SaveDeck --> Export[Optional: Export/share]
    Export --> Done[Deck ready]
    SaveDeck --> Done
```

## Notes
- Scoring factors: meta tier, rarity, level/LB, bond, training bonuses.  
- Friend slot indicated separately; cannot exceed slot limit.  
- Export provides share code; import option mirrors flow.
