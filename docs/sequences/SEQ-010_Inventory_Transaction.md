# SEQ-010: Inventory Transaction

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-001], [SPEC-010]

---

## Sequence Overview
- Processes inventory adds/removals (items, mats, currencies) with atomicity and logging.

## Sequence Diagram

```mermaid
sequenceDiagram
    participant API as Backend
    participant DB as Database
    participant LOG as AuditLog

    API->>DB: Begin transaction
    API->>DB: Lock user inventory row
    API->>DB: Apply deltas (+/- items, currency)
    DB-->>API: Balance check OK/Fail
    alt Fail
        API->>DB: Rollback
        API-->>API: Return error
    else Success
        API->>LOG: Write inventory change log
        API->>DB: Commit
        API-->>API: Return updated balances
    end
```
