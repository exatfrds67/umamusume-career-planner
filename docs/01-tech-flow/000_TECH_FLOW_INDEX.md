# TECH-FLOW DOCUMENTS: Quick Reference Index

**Status**: Broadly aligned at the architecture and mid-implementation layers; remaining work is
mainly template consistency and legacy-example cleanup.

## Overview

Tech Flow documents describe architecture, component boundaries, data movement, and implementation
concerns for the core modules in the application. They should be read as implementation guidance
tied to the current Laravel repository, not as a source of truth for speculative or future-only
service names.

All technical flows should explicitly identify whether they support `StorageMode::LOCAL`,
`StorageMode::ACCOUNT`, or both.

**Document Version**: 2.3.0
**Date**: March 8, 2026
**Project**: UmamusumeCareerPlanner
**Status**: Broadly aligned; template consistency pass in progress

---

## Document Index

### TECH-FLOW-001: Character Management

**Status**: Updated for storage-aware character versus career boundaries and snapshot delegation

- Related sequence: [SEQ-001](../01-sequences/SEQ-001_Character_Creation_Sequence.md)
- File: [TECH-FLOW-001_Character_Management_Flow.md](TECH-FLOW-001_Character_Management_Flow.md)

### TECH-FLOW-002: Training Optimization

**Status**: Updated for storage-aware prediction and execution guidance, with current character-
centric limitations documented

- Related sequence: [SEQ-002](../01-sequences/SEQ-002_Training_Block_Resolution.md)
- File: [TECH-FLOW-002_Training_Optimization_Flow.md](TECH-FLOW-002_Training_Optimization_Flow.md)

### TECH-FLOW-003: Race Strategy

**Status**: Updated for current route surface and service boundaries

- Related sequences: [SEQ-004](../01-sequences/SEQ-004_Race_Registration_and_Outcome.md),
[SEQ-017](../01-sequences/SEQ-017_Storage_Mode_Transition.md)
- File: [TECH-FLOW-003_Race_Strategy_Flow.md](TECH-FLOW-003_Race_Strategy_Flow.md)

### TECH-FLOW-004: Skill Management

**Status**: Template-aligned; storage-mode, eager-loading, related-documents, and route-surface guidance added

- Related sequence: [SEQ-003](../01-sequences/SEQ-003_Skill_Acquisition_and_Upgrade.md)
- File: [TECH-FLOW-004_Skill_Management_Flow.md](TECH-FLOW-004_Skill_Management_Flow.md)

### TECH-FLOW-005: Support Card Management

**Status**: Template-aligned; storage-mode, eager-loading, related-documents, and active deck naming guidance added

- Related sequences: [SEQ-005](../01-sequences/SEQ-005_Support_Card_Upgrade.md),
[SEQ-016](../01-sequences/SEQ-016_Support_Deck_Configuration.md)
- File: [TECH-FLOW-005_Support_Card_Management_Flow.md](TECH-FLOW-005_Support_Card_Management_Flow.md)

### TECH-FLOW-006: AI Advisory

**Status**: Updated for config-driven providers, current chat routes, and storage-aware context

- Related sequences: [SEQ-006](../01-sequences/SEQ-006_AI_Advice_Generation.md),
[SEQ-017](../01-sequences/SEQ-017_Storage_Mode_Transition.md)
- File: [TECH-FLOW-006_AI_Advisory_Flow.md](TECH-FLOW-006_AI_Advisory_Flow.md)

### TECH-FLOW-007: External Integration

**Status**: Template-aligned; storage-mode, eager-loading, related-documents, and OCR validation wording tightened

- Related sequences: [SEQ-007](../01-sequences/SEQ-007_External_Data_Sync.md),
[SEQ-015](../01-sequences/SEQ-015_Data_Migration_Snapshot_to_Live.md)
- File: [TECH-FLOW-007_External_Integration_Flow.md](TECH-FLOW-007_External_Integration_Flow.md)

### TECH-FLOW-008: Storage Mode and Conversion

**Status**: Added to document dual storage detection, browser-backed payload handling, and local-to-account conversion

- Related sequence: [SEQ-017](../01-sequences/SEQ-017_Storage_Mode_Transition.md)
- File: [TECH-FLOW-008_Storage_Mode_and_Local_Account_Conversion.md](TECH-
FLOW-008_Storage_Mode_and_Local_Account_Conversion.md)

### TECH-FLOW-009: Target Race Planning

**Status**: Added to decompose target selection, readiness refresh, and storage-aware planning from
general race strategy

- Related sequence: [SEQ-004](../01-sequences/SEQ-004_Race_Registration_and_Outcome.md)
- File: [TECH-FLOW-009_Target_Race_Planning_Flow.md](TECH-FLOW-009_Target_Race_Planning_Flow.md)

### TECH-FLOW-010: Career Reporting

**Status**: Added to decompose reporting, export, and cached aggregation from general race and character flows

- Related sequence: [SEQ-004](../01-sequences/SEQ-004_Race_Registration_and_Outcome.md)
- File: [TECH-FLOW-010_Career_Reporting_Flow.md](TECH-FLOW-010_Career_Reporting_Flow.md)

---

## Cross-Cutting Guidance

- All read and write flows must state whether persistence is browser-backed local storage,
authenticated database storage, or a storage-mode abstraction that selects between them.
- Where related collections are rendered or analyzed, required eager-loaded relationships should be
documented explicitly and lazy loading in loops should be avoided.
- Every technical flow should include a `Related Documents` section so readers can move across
planning, execution, conversion, and reporting without relying on the index alone.
- AI provider endpoints, model identifiers, and pricing are configuration-driven and environment-
dependent. Documentation examples are illustrative only.
- Route references should favor the currently registered Laravel route surface over speculative REST endpoints.
- When any technical flow is substantially rewritten, update the corresponding status line in this
index in the same change set.

---

## Current Review Focus

- Storage-mode support across character, training, race, and AI flows
- Race-planning alignment with `/races`, `/races/calendar`, `/races/targets`,
`/characters/{character}/races/{gameRace}/enter`, and `/reports/career/{career}`
- Configuration-backed AI provider and MCP optionality notes
- Eager-loading expectations for reporting, race history, and deck analysis
