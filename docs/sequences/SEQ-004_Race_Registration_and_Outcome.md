# SEQ-004: Race Registration and Outcome

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-001], [SPEC-004], [FLOW-003]

---

## Sequence Overview
- User registers for a race; system validates schedule/stats, simulates outcome, applies rewards/penalties.

## Sequence Diagram

```mermaid
sequenceDiagram
    participant U as User
    participant UI as Frontend
    participant API as Backend
    participant RS as RaceService
    participant SIM as RaceSimulator
    participant DB as Database

    U->>UI: Pick race
    UI->>API: POST /api/runs/{id}/races/register {raceId}
    API->>RS: Validate(raceId, run state)
    RS-->>API: OK or error (date, fatigue, requirements)
    API-->>UI: 422 on validation fail

    alt Success
        API->>SIM: Simulate(race, stats, skills, deck)
        SIM-->>API: Outcome {placement, rewards, condition}
        API->>DB: Persist race result, fame, stats deltas
        DB-->>API: Saved
        API-->>UI: 200 Outcome + rewards
    end
    UI-->>U: Show race result
```
