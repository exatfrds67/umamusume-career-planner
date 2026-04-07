# USER FLOW DIAGRAMS: Journey Map Index

**Document Version**: 2.3.0
**Date**: March 8, 2026
**Project**: UmamusumeCareerPlanner
**Status**: Needs continued alignment review for storage-mode support, current navigation surfaces,
and reporting/export journey coverage. Core user-journey coverage exists, but some flows still
reflect earlier account-centric assumptions.

---

## Overview

User flow documents describe the user-facing journeys through the application: onboarding, run
setup, day-to-day actions, advisory usage, deck building, import workflows, and storage transition.
They should reflect the current storage-aware architecture and distinguish clearly between:

- browser-local actions in `StorageMode::LOCAL`
- authenticated persisted actions in `StorageMode::ACCOUNT`
- conceptual UI labels versus currently registered routes or entry points

Where a user flow mixes conceptual UX language with implementation references, the navigation path
should be treated as conceptual unless the document explicitly names a current route or screen entry
surface.

---

## Document Index

### UF-001: Onboarding Flow

**Status**: Refined for navigation-surface clarity and more precise Local-to-Account wording

- File: [UF-001_Onboarding_Flow.md](UF-001_Onboarding_Flow.md)

### UF-002: Career Setup Flow

**Status**: Refined for clearer local versus account persistence semantics and recovery wording

- File: [UF-002_Career_Setup_Flow.md](UF-002_Career_Setup_Flow.md)

### UF-003: Training Day Flow

**Status**: Updated for storage-aware execution boundaries, degraded advisory behavior, and no-run handling

- File: [UF-003_Training_Day_Flow.md](UF-003_Training_Day_Flow.md)

### UF-004: Race Day Flow

**Status**: Updated for storage-aware race planning versus account-backed race execution and reporting

- File: [UF-004_Race_Day_Flow.md](UF-004_Race_Day_Flow.md)

### UF-005: Skill Management Flow

**Status**: Refined for storage-aware planning versus acquisition semantics and empty-state handling

- File: [UF-005_Skill_Management_Flow.md](UF-005_Skill_Management_Flow.md)

### UF-006: Support Deck Building Flow

**Status**: Updated for local draft versus authenticated deck persistence and empty-state handling

- File: [UF-006_Support_Deck_Building_Flow.md](UF-006_Support_Deck_Building_Flow.md)

### UF-007: AI Advisor Journey

**Status**: Updated for config-driven providers, chat versus advisory surfaces, and storage-aware advisory payloads

- File: [UF-007_AI_Advisor_Journey.md](UF-007_AI_Advisor_Journey.md)

### UF-008: OCR and Data Import Flow

**Status**: Updated for storage-aware ingestion, review-only outcomes, and partial-save handling

- File: [UF-008_OCR_and_Data_Import_Flow.md](UF-008_OCR_and_Data_Import_Flow.md)

### UF-009: Storage Mode Transition Flow

**Status**: Added to cover local-to-account validation, duplicate handling, and cleanup choice

- File: [UF-009_Storage_Mode_Transition_Flow.md](UF-009_Storage_Mode_Transition_Flow.md)

### UF-010: Career Reporting and Export Flow

**Status**: Added to document account-backed reporting, comparisons, and exports without implying local-mode parity

- File: [UF-010_Career_Reporting_and_Export_Flow.md](UF-010_Career_Reporting_and_Export_Flow.md)

### UF-011: Target Race Planning Flow

**Status**: Added to separate target planning from race entry and reporting

- File: [UF-011_Target_Race_Planning_Flow.md](UF-011_Target_Race_Planning_Flow.md)

---

## Cross-Cutting Guidance

- Every user flow should state whether an action is browser-local, authenticated and persisted, or advisory-only.
- Replace vague references to "all users with active career runs" with storage-aware wording that
distinguishes browser-local runs from authenticated persisted runs.
- Add a navigation-surface note when a document uses conceptual UI labels such as dashboard, shop,
wizard, or advisor interface.
- Offline behavior should be described per action. Local planning can remain available offline,
while account-backed saves, exports, and server-side mutations require connectivity.
- When a new user flow is added or materially rewritten, update this index in the same change set.

---

## Current Review Focus

- Cross-document consistency and future drift prevention rather than major structural correction
- Any remaining user flows that need stronger offline or degraded-state wording during future feature changes
- Keeping navigation-surface notes aligned when route surfaces or entry points change
