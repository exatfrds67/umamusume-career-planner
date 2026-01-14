# UF-008: OCR and Data Import Flow

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-007], [SPEC-007], [FLOW-007], [SEQ-010], [SEQ-011]

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
