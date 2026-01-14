# UF-002: Career Setup Flow

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-001], [SPEC-001], [SEQ-001], [FLOW-001]

---

## Flow Diagram
```mermaid
flowchart TD
    Start([Start New Run]) --> SelectTrainee[Select Trainee]
    SelectTrainee --> SelectScenario[Select Scenario]
    SelectScenario --> PickParents[Pick Parents]
    PickParents --> Factors[View factor bonuses]
    Factors --> BuildDeck[Build support deck (6 slots)]
    BuildDeck --> ValidateDeck{Deck valid?}
    ValidateDeck -->|No| FixDeck[Adjust cards]
    FixDeck --> ValidateDeck
    ValidateDeck -->|Yes| Review[Review summary]
    Review --> Confirm[Confirm creation]
    Confirm --> CreateRun[Persist run]
    CreateRun --> Ready[Run ready (Day 1)]
    Ready --> NextSteps[Guided next steps: training, skill shop]
```

## Notes
- Validation: deck type constraints, scenario compatibility, parent selection required.  
- Factor bonuses previewed before confirm; inheritance seed stored.  
- Post-create CTA to start training prediction or set schedule.
