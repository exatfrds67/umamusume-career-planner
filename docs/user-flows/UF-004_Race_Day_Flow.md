# UF-004: Race Day Flow

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-003], [SPEC-003], [FLOW-003], [SEQ-004], [SEQ-005]
**Source Specs**:

- `.kiro/specs/umamusume-career-planner-main/requirements.md` (Requirement 3: Race Preparation and Strategy)
- `.kiro/specs/umamusume-career-planner-main/design.md` (Race Flow)

**Related Artifacts**:

- PRD: [PRD-003](../prds/PRD-003_Race_Strategy.md)
- SPEC: [SPEC-003](../specs/SPEC-003_Race_Strategy_Technical.md)
- Flow: [FLOW-003](../flows/FLOW-003_Race_Strategy_System.md)
- Tech Flow: [TECH-FLOW-003](../tech-flow/TECH-FLOW-003_Race_Strategy_Flow.md)
- Wireframes: [WF-006](../wireframes/WF-006_Race_Calendar_View.md), [WF-007](../wireframes/WF-007_Race_Preparation_Screen.md)
- Sequences: [SEQ-004](../sequences/SEQ-004_Race_Registration_and_Outcome.md)

---

## Flow Diagram

```mermaid
flowchart TD
    Start([Race day arrives]) --> CheckEntry{Already entered?}
    CheckEntry -->|No| PromptEnter[Prompt to enter race]
    PromptEnter --> RacePrep[Open race prep]
    CheckEntry -->|Yes| PreCheck[Pre-race checks]

    RacePrep --> WinProb[View win probability]
    WinProb --> AdjustPlan{Need prep?}
    AdjustPlan -->|Yes| Recommendations[Follow recommendations]
    Recommendations --> ReturnPrep[Re-run preview]
    ReturnPrep --> WinProb
    AdjustPlan -->|No| LockEntry[Confirm entry]

    LockEntry --> Simulate[Run race simulation]
    Simulate --> Result[Show placement, rewards]
    Result --> UpdateState[Update stats, mood, condition, fans]
    UpdateState --> History[Record race history]
    History --> AdvanceSchedule[Advance schedule]
```

## Notes

- If not entered, user must complete prep before simulation.  
- Recommendations include training, skill buys, or rest pre-race.  
- Result includes logs for skill activations and performance.
