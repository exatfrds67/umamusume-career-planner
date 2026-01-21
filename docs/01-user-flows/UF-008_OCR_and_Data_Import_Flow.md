# UF-008: OCR and Data Import Flow

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-007], [SPEC-007], [FLOW-007], [SEQ-010], [SEQ-011]

**Source Specs**:

- `.kiro/specs/umamusume-career-planner-main/requirements.md` (External Integration Requirements)
- `.kiro/specs/umamusume-career-planner-main/design.md` (OCR and Data Import Flow)

**Related Artifacts**:

- PRD: [PRD-007](../prds/PRD-007_External_Integration.md)
- SPEC: [SPEC-007](../specs/SPEC-007_External_Integration_Technical.md)
- Flow: [FLOW-007](../flows/FLOW-007_External_Integration_System.md)
- Tech Flow: [TECH-FLOW-007](../tech-flow/TECH-FLOW-007_External_Integration_Flow.md)
- Wireframes: [WF-001](../wireframes/WF-001_Dashboard_Overview.md)
- Sequences: [SEQ-007](../sequences/SEQ-007_External_Data_Sync.md), [SEQ-010](../sequences/SEQ-010_Inventory_Transaction.md), [SEQ-011](../sequences/SEQ-011_Telemetry_Event_Capture.md), [SEQ-015](../sequences/SEQ-015_Data_Migration_Snapshot_to_Live.md)

---

## Flow Diagram

```mermaid
flowchart TD
    Start([User has screenshot]) --> Upload[Upload image (PNG/JPG)]
    Upload --> Validate{Valid size/format?}
    Validate -->|No| Reject[Reject with error]
    Validate -->|Yes| Preprocess[Preprocess (OpenCV)]
    Preprocess --> OCR[Tesseract OCR]
    OCR --> PostProcess[Post-process per field]
    PostProcess --> Confidence{Confidence < 70%?}
    Confidence -->|Yes| QueueReview[Queue manual review]
    Confidence -->|No| BuildResult[Build result]
    QueueReview --> BuildResult
    BuildResult --> SaveDB[Persist OCR result]
    SaveDB --> ReturnUser[Return text + confidence]
    ReturnUser --> ApplyToRun{Apply to active run?}
    ApplyToRun -->|Yes| MapFields[Map stats/skills to run]
    MapFields --> UpdateRun[Update run state]
    UpdateRun --> Done
    ApplyToRun -->|No| Done[Finish]
```

## Notes

- Max size 10MB; language jpn, psm 6.  
- Low confidence auto-queued for review; admins correct and reapply.  
- Mapping step optional; user can discard or re-run OCR.
