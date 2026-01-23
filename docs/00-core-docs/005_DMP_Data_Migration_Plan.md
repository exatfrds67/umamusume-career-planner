# Data Migration Plan (DMP)

## Umamusume Pretty Derby Career Planner

**Document Version**: 2.0.0
**Date**: January 23, 2026
**Project**: UmamusumeCareerPlanner
**Author**: Development Team
**Status**: Current - Aligned with codebase

---

## Table of Contents

1. [Purpose](#1-purpose)
2. [Migration Sources](#2-migration-sources)
3. [Migration Targets](#3-migration-targets)
4. [Migration Strategy](#4-migration-strategy)
5. [Data Transformation Rules](#5-data-transformation-rules)
6. [Conflict Resolution](#6-conflict-resolution)
7. [Validation Procedures](#7-validation-procedures)
8. [Rollback Strategy](#8-rollback-strategy)
9. [Operational Considerations](#9-operational-considerations)

---

## 1. Purpose

This Data Migration Plan defines the strategy for migrating data from legacy sources and external imports into the current Umamusume Career Planner schema. It covers detection, conversion, validation, conflict resolution, and rollback procedures implemented in the `DataMigrationService` and `DataImportService`.

---

## 2. Migration Sources

### 2.1 Supported Source Formats

| Source Type | Format | Detection Method | Priority |
|-------------|--------|------------------|----------|
| Legacy JSON exports (v1) | JSON | `detectLegacyFormat()` | High |
| Google Sheets exports | CSV | File extension + header detection | Medium |
| Excel exports | XLSX | MIME type detection | Medium |
| OCR extraction results | JSON | Schema validation | High |
| External API data (umapyoi.net) | JSON | API response structure | High |
| External API data (umamusumedb) | JSON | API response structure | Medium |

### 2.2 Source System Analysis

```

Legacy Applications
├── uma_musume_race_planner (PHP + MySQL)
│   └── Export: MySQL dump or JSON
├── umamusume-tracker (Laravel + React)
│   └── Export: JSON API format
├── uma-tracker (Laravel + Blade)
│   └── Export: MySQL dump
├── uma-run-tracker (Static HTML + JS)
│   └── Export: localStorage JSON
└── uma-tracker-form (Native PHP)
    └── Export: CSV or JSON

```

---

## 3. Migration Targets

### 3.1 Target Tables

| Target Table | Description | Key Constraints |
|--------------|-------------|-----------------|
| `ucp_characters` | Character records with stats and scenarios | Stat values 0-1200 |
| `ucp_careers` | Career run tracking | Valid scenario types |
| `ucp_training_sessions` | Training session history | Foreign key to careers |
| `ucp_skills` | Skill catalog | Unique skill identifiers |
| `ucp_skill_hints` | Hint tracking for SP reduction | Max 3 hints per skill |
| `ucp_skill_acquisitions` | Skills acquired per career | Turn number validation |
| `ucp_support_cards` | Support card inventory | Valid rarity and type |

### 3.2 Schema Version

Current target schema version: `2.0`

All imports include `schema_version` field for forward compatibility and migration tracking.

---

## 4. Migration Strategy

### 4.1 Migration Workflow

```

┌─────────────────────────────────────────────────────────────┐
│                    MIGRATION WORKFLOW                        │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  1. DETECT                                                   │
│     └─ DataMigrationService::detectLegacyFormat()           │
│        ├─ Identify source format                            │
│        ├─ Determine schema version                          │
│        └─ Select appropriate adapter                        │
│                                                              │
│  2. CONVERT                                                  │
│     └─ Transform to current schema fields                   │
│        ├─ Field mapping (legacy → canonical)                │
│        ├─ Type conversion                                   │
│        └─ Enum normalization                                │
│                                                              │
│  3. VALIDATE                                                 │
│     └─ DataImportService::validateRecord()                  │
│        ├─ Schema validation                                 │
│        ├─ Business rule checks                              │
│        └─ Integrity verification                            │
│                                                              │
│  4. RESOLVE CONFLICTS                                        │
│     └─ Handle duplicates and conflicts                      │
│        ├─ Skip existing                                     │
│        ├─ Overwrite                                         │
│        ├─ Merge data                                        │
│        └─ Rename duplicates                                 │
│                                                              │
│  5. EXECUTE                                                  │
│     └─ Batch import with progress tracking                  │
│        ├─ Transaction management                            │
│        ├─ Progress callbacks                                │
│        └─ Error collection                                  │
│                                                              │
│  6. REPORT                                                   │
│     └─ Generate migration results                           │
│        ├─ Success count                                     │
│        ├─ Error details                                     │
│        └─ Store in history                                  │
│                                                              │
└─────────────────────────────────────────────────────────────┘

```

### 4.2 Service Layer Integration

```

app/Services/DataManagement/
├── DataMigrationService.php
│   ├─ detectLegacyFormat(array $data): string
│   ├─ migrateFromLegacy(array $data): array
│   ├─ transformField(string $field, mixed $value): mixed
│   └─ getMigrationHistory(): array
│
├── DataImportService.php
│   ├─ import(UploadedFile $file, array $options): ImportResult
│   ├─ validateRecord(array $record): ValidationResult
│   ├─ resolveConflict(array $record, string $strategy): array
│   └─ executeImport(array $records, callable $progress): void
│
└── DataExportService.php
    ├─ export(array $ids, string $format): string
    ├─ generateBackup(): string
    └─ getExportFormats(): array

```

---

## 5. Data Transformation Rules

### 5.1 Field Mapping (Legacy to Canonical)

| Legacy Field | Canonical Field | Transformation |
|--------------|-----------------|----------------|
| `spd`, `speed` | `speed` | Direct mapping |
| `sta`, `stam` | `stamina` | Direct mapping |
| `pow`, `power` | `power` | Direct mapping |
| `gut`, `guts` | `guts` | Direct mapping |
| `int`, `wis`, `wit` | `wit` | Direct mapping |
| `sp`, `skill_points` | `total_sp_available` | Direct mapping |
| `hp`, `health` | `stamina_percentage` | Normalize to 0-100 |
| `turn`, `current_turn` | `turn_number` | Direct mapping |
| `run_id`, `plan_id` | `career_id` | Generate new if importing |

### 5.2 Enum Normalization

| Field | Valid Values | Default |
|-------|--------------|---------|
| `scenario_type` | `ura_finale`, `unity_cup` | `ura_finale` |
| `career_stage` | `junior`, `classic`, `senior` | `junior` |
| `mood` | `very_bad`, `bad`, `normal`, `good`, `very_good` | `normal` |
| `aptitude_grade` | `SS`, `S`, `A`, `B`, `C`, `D`, `E`, `F`, `G` | `G` |
| `skill_status` | `acquired`, `skipped`, `suggested` | `suggested` |

### 5.3 Value Constraints

| Field | Constraint | Handling |
|-------|------------|----------|
| Stat values | 0-1200 | Clamp to valid range |
| Turn number | 1-78 | Validate against career stage |
| SP cost | > 0 | Reject invalid values |
| Hint count | 0-3 | Cap at maximum |

---

## 6. Conflict Resolution

### 6.1 Conflict Detection

Duplicates are detected by:

1. Same character name + scenario + created date
2. Same UUID (for migrated records)
3. Same source ID reference (for external API data)

### 6.2 Resolution Strategies

| Strategy | Behavior | Use Case |
|----------|----------|----------|
| `skip` | Keep existing, ignore imported | Preserve user modifications |
| `overwrite` | Replace existing with imported | Fresh data sync |
| `merge` | Combine data, prefer newer values | Incremental updates |
| `rename` | Create new with modified identifier | Keep both versions |

### 6.3 User Interface for Conflict Resolution

```

┌─────────────────────────────────────────────────┐
│ Conflict Detected                               │
├─────────────────────────────────────────────────┤
│                                                 │
│ "Speed Build - Special Week" already exists     │
│                                                 │
│ Existing:                    Importing:         │
│ Updated: 2026-01-20         Updated: 2026-01-15│
│ Turn: 45                    Turn: 72            │
│ Status: In Progress         Status: Completed   │
│                                                 │
│ [Skip] [Overwrite] [Merge] [Rename & Import]    │
│                                                 │
└─────────────────────────────────────────────────┘

```

---

## 7. Validation Procedures

### 7.1 Validation Layers

```

Input Data
    │
    ▼
┌───────────────────────────────────┐
│ Layer 1: Schema Validation        │
│ ├─ Required fields present        │
│ ├─ Data types correct             │
│ └─ Format compliance              │
└───────────────┬───────────────────┘
                │
                ▼
┌───────────────────────────────────┐
│ Layer 2: Business Rules           │
│ ├─ Value ranges valid             │
│ ├─ Enum values valid              │
│ └─ Relationships valid            │
└───────────────┬───────────────────┘
                │
                ▼
┌───────────────────────────────────┐
│ Layer 3: Integrity Checks         │
│ ├─ No orphan records              │
│ ├─ References exist               │
│ └─ Consistency verified           │
└───────────────┬───────────────────┘
                │
                ▼
           Valid Data

```

### 7.2 Validation Rules

| Field | Rule | Error Message |
|-------|------|---------------|
| `character_name` | Required | "Character name is required" |
| `scenario_type` | Valid enum | "Invalid scenario type" |
| `speed`, `stamina`, etc. | 0-1200 | "Stat must be between 0 and 1200" |
| `turn_number` | 1-78 | "Turn must be between 1 and 78" |
| `skill_id` | Exists in catalog | "Unknown skill reference" |

---

## 8. Rollback Strategy

### 8.1 Rollback Mechanisms

| Mechanism | Description | Scope |
|-----------|-------------|-------|
| Batch tracking | Each import batch assigned unique ID | Per-import |
| Transaction wrapping | Database transactions for atomicity | Per-batch |
| Soft deletes | Mark records for deletion, not hard delete | Per-record |
| Backup creation | Full backup before migration via UI | Full database |

### 8.2 Rollback Procedure

```

Rollback Triggered
        │
        ▼
┌─────────────────────────────────┐
│ 1. Stop migration process       │
└───────────────┬─────────────────┘
                │
                ▼
┌─────────────────────────────────┐
│ 2. Retrieve batch ID from cache │
└───────────────┬─────────────────┘
                │
                ▼
┌─────────────────────────────────┐
│ 3. Delete imported records      │
│    by batch ID                  │
└───────────────┬─────────────────┘
                │
                ▼
┌─────────────────────────────────┐
│ 4. Restore from pre-migration   │
│    backup if available          │
└───────────────┬─────────────────┘
                │
                ▼
┌─────────────────────────────────┐
│ 5. Verify data integrity        │
└───────────────┬─────────────────┘
                │
                ▼
┌─────────────────────────────────┐
│ 6. Notify stakeholders          │
└─────────────────────────────────┘

```

### 8.3 Rollback Windows

| Window | Duration | Capability |
|--------|----------|------------|
| Immediate | During migration | Full rollback via transaction |
| Short-term | Within 24 hours | Batch-based rollback |
| Long-term | Within 7 days | Backup restoration |

---

## 9. Operational Considerations

### 9.1 Performance Settings

| Setting | Default Value | Description |
|---------|---------------|-------------|
| Batch size | 50 records | Records processed per batch |
| Transaction timeout | 300 seconds | Maximum transaction duration |
| Memory limit | 256MB | PHP memory limit during import |
| Progress interval | 10 records | UI update frequency |

### 9.2 Monitoring and Logging

| Event | Log Level | Details Captured |
|-------|-----------|------------------|
| Migration started | INFO | Source, target, record count |
| Batch completed | DEBUG | Batch ID, success count, duration |
| Validation error | WARNING | Record ID, field, error message |
| Migration failed | ERROR | Exception, stack trace, partial results |
| Migration completed | INFO | Total processed, success, failures |

### 9.3 OCR Pipeline Integration

For OCR-based data intake:

1. Image uploaded via `OcrUploadController`
2. Processed by `OcrProcessingService`
3. Parsed results validated by `DataImportService::validateRecord()`
4. Manual correction UI for ambiguous values
5. Final import via standard migration workflow

---

## Document Control

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 2.0.0 | 2026-01-23 | Development Team | Complete rewrite aligned with current implementation |
| 1.0.0 | 2026-01-14 | Development Team | Initial draft |

---

## Related Documents

- [FLOW-001: Character Management System](flows/FLOW-001_Character_Management_System.md)
- [SEQ-015: Data Migration Snapshot to Live](sequences/SEQ-015_Data_Migration_Snapshot_to_Live.md)
- [TECH-FLOW-007: External Integration Flow](tech-flow/TECH-FLOW-007_External_Integration_Flow.md)
- [D06: Data Migration Specifications](D06_Data_Migration_Specifications.md)
- [D15: Data Migration Report Template](D15_Data_Migration_Report.md)

---

*This plan reflects current migration capabilities implemented in the codebase.*
