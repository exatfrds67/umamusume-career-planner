# SEQ-003: Skill Acquisition and Upgrade

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-001], [SPEC-003], [FLOW-004]

---

## Sequence Overview
- User buys or upgrades skills; system validates prerequisites, costs, conflicts, then applies updates.

## Sequence Diagram

```mermaid
sequenceDiagram
    participant U as User
    participant UI as Frontend
    participant API as Backend
    participant SK as SkillService
    participant DB as Database

    U->>UI: Open Skill Board
    UI->>API: GET /api/runs/{id}/skills
    API-->>UI: 200 Current skills + points

    U->>UI: Select skill (buy/upgrade)
    UI->>API: POST /api/runs/{id}/skills {skillId, action}
    API->>SK: Validate(skillId, action, run)
    SK-->>API: OK or error (prereq/cost/conflict)
    API-->>UI: 422 on validation fail

    alt Success
        API->>SK: Apply(skillId, action)
        SK-->>API: Updated skills, points delta
        API->>DB: Persist skills + points
        DB-->>API: Saved
        API-->>UI: 200 Updated board + log
    end
    UI-->>U: Render new skill state
```
