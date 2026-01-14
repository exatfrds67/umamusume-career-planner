# SEQ-002: Training Block Resolution

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-001], [SPEC-002], [FLOW-002]

---

## Sequence Overview
- Resolves a single turn: fetch context, simulate training/race/rest, apply gains, update mood/energy, persist.

## Sequence Diagram

```mermaid
sequenceDiagram
    participant U as User
    participant UI as Frontend
    participant API as Backend
    participant SIM as TrainingSimulator
    participant ST as StatService
    participant DB as Database

    U->>UI: Choose action (train/rest/race)
    UI->>API: POST /api/runs/{id}/turn {action}
    API->>DB: Load run state + deck + buffs
    DB-->>API: State snapshot

    API->>SIM: SimulateTurn(state, action)
    SIM->>ST: ComputeGains
    ST-->>SIM: Gains + events
    SIM-->>API: Result {gains, mood, events, log}

    API->>DB: Persist new stats/energy/mood
    API-->>UI: 200 Turn resolved {diffs, log}
    UI-->>U: Show updated dashboard
```
