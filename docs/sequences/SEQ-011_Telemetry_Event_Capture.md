# SEQ-011: Telemetry Event Capture

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-001], [SPEC-011]
**Source Specs**:

- `.kiro/specs/umamusume-career-planner-main/requirements.md` (Telemetry Requirements)
- `.kiro/specs/umamusume-career-planner-main/design.md` (Telemetry System)

**Related Artifacts**:

- PRD: [PRD-007](../prds/PRD-007_External_Integration.md)
- SPEC: [SPEC-007](../specs/SPEC-007_External_Integration_Technical.md)
- Tech Flow: [TECH-FLOW-007](../tech-flow/TECH-FLOW-007_External_Integration_Flow.md)

---

## Sequence Overview

- Captures client events, validates schema, batches, and stores for analytics.

## Sequence Diagram

```mermaid
sequenceDiagram
    participant UI as Frontend
    participant API as Backend
    participant VAL as EventValidator
    participant Q as Queue
    participant W as Worker
    participant DB as Warehouse

    UI->>API: POST /api/events {batch}
    API->>VAL: Validate schema + size limits
    VAL-->>API: OK or reject
    API-->>UI: 202 Accepted or 422

    alt Accepted
        API->>Q: Enqueue batch
        Q-->>W: Deliver job
        W->>DB: Insert events (compressed)
        DB-->>W: Ack
    end
```
