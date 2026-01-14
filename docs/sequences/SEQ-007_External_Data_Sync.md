# SEQ-007: External Data Sync (Training Buddy)

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-001], [SPEC-007], [FLOW-007]

---

## Sequence Overview
- Syncs external training buddy stats; system pulls data, normalizes, applies buffs.

## Sequence Diagram

```mermaid
sequenceDiagram
    participant CRON as Scheduler
    participant API as Backend
    participant EXT as ExternalAPI
    participant MAP as Mapper
    participant DB as Database

    CRON->>API: Trigger sync (buddyId)
    API->>EXT: GET /buddies/{id}
    EXT-->>API: 200 Buddy payload

    API->>MAP: Normalize(payload)
    MAP-->>API: Mapped stats/buffs
    API->>DB: Upsert buddy record + buffs
    DB-->>API: Saved
    API-->>CRON: Sync complete
```
