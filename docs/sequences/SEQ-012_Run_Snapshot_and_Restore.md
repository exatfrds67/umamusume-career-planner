# SEQ-012: Run Snapshot and Restore

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-001], [SPEC-012]

---

## Sequence Overview
- Saves periodic run snapshots and restores on request for what-if scenarios or rollback.

## Sequence Diagram

```mermaid
sequenceDiagram
    participant API as Backend
    participant CRON as Scheduler
    participant DB as Database

    CRON->>API: Trigger snapshot job
    API->>DB: Fetch active runs needing snapshot
    loop Each run
        API->>DB: Serialize state -> snapshots table
        DB-->>API: Saved snapshot_id
    end

    API->>DB: POST /api/runs/{id}/restore {snapshot_id}
    DB-->>API: Snapshot data
    API->>DB: Overwrite run state from snapshot
    DB-->>API: Restored
    API-->>API: Return restored state
```
