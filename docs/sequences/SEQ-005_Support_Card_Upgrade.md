# SEQ-005: Support Card Upgrade

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-001], [SPEC-005], [FLOW-005]

---

## Sequence Overview
- User enhances support card level/rarity; system checks materials/caps, applies upgrade, updates deck bonuses.

## Sequence Diagram

```mermaid
sequenceDiagram
    participant U as User
    participant UI as Frontend
    participant API as Backend
    participant SC as SupportCardService
    participant DB as Database

    U->>UI: Open Support Card
    UI->>API: GET /api/support-cards/{id}
    API-->>UI: 200 Card details + mats

    U->>UI: Upgrade request
    UI->>API: POST /api/support-cards/{id}/upgrade
    API->>SC: ValidateUpgrade(card, mats, caps)
    SC-->>API: OK or error
    API-->>UI: 422 on error

    alt Success
        API->>SC: ApplyUpgrade
        SC-->>API: New stats/rarity + cost
        API->>DB: Persist card + inventory
        DB-->>API: Saved
        API-->>UI: 200 Updated card + deck bonuses
    end
    UI-->>U: Show upgraded card
```
