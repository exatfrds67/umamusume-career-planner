# UF-006: Support Deck Building Flow

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-005], [SPEC-005], [FLOW-005], [SEQ-008], [WF-011]
**Source Specs**:

- `.kiro/specs/umamusume-career-planner-main/requirements.md` (Requirement 6: Support Card Configuration)
- `.kiro/specs/umamusume-career-planner-main/design.md` (Support Deck Building Flow)

**Related Artifacts**:

- PRD: [PRD-005](../prds/PRD-005_Support_Card_Management.md)
- SPEC: [SPEC-005](../specs/SPEC-005_Support_Card_Management_Technical.md)
- Flow: [FLOW-005](../flows/FLOW-005_Support_Card_Management_System.md)
- Tech Flow: [TECH-FLOW-005](../tech-flow/TECH-FLOW-005_Support_Card_Management_Flow.md)
- Wireframes: [WF-010](../wireframes/WF-010_Support_Card_Collection.md), [WF-011](../wireframes/WF-011_Support_Deck_Builder.md)
- Sequences: [SEQ-005](../sequences/SEQ-005_Support_Card_Upgrade.md)

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
