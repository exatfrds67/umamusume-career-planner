# UF-003: Training Day Flow

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-002], [SPEC-002], [FLOW-002], [SEQ-002], [SEQ-003]

---

## Flow Diagram
```mermaid
flowchart TD
    Start([Turn begins]) --> OpenTraining[Open training screen]
    OpenTraining --> ViewPredictions[View predictions (gains, risk, bond)]
    ViewPredictions --> DecideAction{Choose action}
    DecideAction -->|Train| ExecuteTraining[Execute training]
    DecideAction -->|Rest| RestAction[Rest]
    DecideAction -->|Race| JumpRace[Go to race prep]
    DecideAction -->|Shop| SkillShop[Open skill shop]

    ExecuteTraining --> Outcome{Success?}
    Outcome -->|Yes| ApplyGains[Apply full gains]
    Outcome -->|No| ApplyPartial[Apply partial gains]
    ApplyGains --> UpdateState[Update stats, energy, mood]
    ApplyPartial --> UpdateState

    RestAction --> Restore[Restore energy/mood]
    Restore --> UpdateState

    SkillShop --> PurchaseFlow[Buy skills]
    PurchaseFlow --> UpdateState

    JumpRace --> RacePrep

    UpdateState --> Events[Process events/hints]
    Events --> History[Record history]
    History --> AdvanceTurn[Advance to next turn]
```

## Notes
- Predicted risk informs confirmation modal for high-risk actions.  
- Events may add hints, alter condition; processed post-update.  
- Turn advances after action completion.
