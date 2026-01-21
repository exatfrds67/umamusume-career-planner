# UF-003: Training Day Flow

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-002], [SPEC-002], [FLOW-002], [SEQ-002], [SEQ-003]
**Source Specs**:

- `.kiro/specs/umamusume-career-planner-main/requirements.md` (Requirement 2: Training Prediction Engine)
- `.kiro/specs/umamusume-career-planner-main/design.md` (Training Flow)

**Related Artifacts**:

- PRD: [PRD-002](../prds/PRD-002_Training_Optimization.md)
- SPEC: [SPEC-002](../specs/SPEC-002_Training_Optimization_Technical.md)
- Flow: [FLOW-002](../flows/FLOW-002_Training_Optimization_System.md)
- Tech Flow: [TECH-FLOW-002](../tech-flow/TECH-FLOW-002_Training_Optimization_Flow.md)
- Wireframes: [WF-004](../wireframes/WF-004_Training_Selection_Interface.md), [WF-005](../wireframes/WF-005_Training_Result_Screen.md)
- Sequences: [SEQ-002](../sequences/SEQ-002_Training_Block_Resolution.md)

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
