# SEQ-015: Data Migration (Snapshot to Live)

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-001], [SPEC-015], [005_DMP_Data_Migration_Plan.md]

**Source Specs**:

- `.kiro/specs/umamusume-career-planner-main/requirements.md` (Data Migration Requirements)
- `.kiro/specs/umamusume-career-planner-main/design.md` (Migration Architecture)
- `docs/005_DMP_Data_Migration_Plan.md`

**Related Artifacts**:

- PRD: [PRD-001](../prds/PRD-001_Character_Management.md)
- SPEC: [SPEC-001](../specs/SPEC-001_Character_Management_Technical.md)
- Flow: [FLOW-001](../flows/FLOW-001_Character_Management_System.md)
- Wireframes: [WF-001](../wireframes/WF-001_Dashboard_Overview.md)

---

## Sequence Overview

- Migrates snapshot data into live tables with validation, chunking, and audit logging.

## Sequence Diagram

```mermaid
sequenceDiagram
    participant OP as Operator
    participant API as MigrationTool
    participant SRC as SnapshotDB
    participant DB as LiveDB
    participant LOG as Audit

    OP->>API: Start migration (batch size, dry-run?)
    API->>SRC: Stream snapshot batch
    SRC-->>API: Batch rows
    API->>API: Validate schema + refs
    alt Validation fail
        API->>LOG: Record failure
        API-->>OP: Halt with errors
    else Valid
        API->>DB: Upsert batch with transaction
        DB-->>API: Commit
        API->>LOG: Record success
        API-->>OP: Progress update
    end
    loop Until complete
        API->>SRC: Next batch
    end
    API-->>OP: Migration summary
```
