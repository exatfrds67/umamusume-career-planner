# Product Requirement Documents Index

## Umamusume Pretty Derby Career Planner

**Document Version**: 2.4.1
**Date**: March 11, 2026
**Project**: UmamusumeCareerPlanner
**Author**: Development Team
**Status**: Current - Link structure and index metadata aligned with the March 2026 `develop` snapshot

---

## Documentation Hierarchy

These PRDs define the product requirements that drive the technical specifications and
implementation logic for the current documentation snapshot. Individual PRDs carry module-specific
revision numbers based on when that module was last substantively updated.

1. **PRD (Product Requirements)**: What we are building and why (Business/User view).
2. **SPEC (Technical Specifications)**: How we build it (Architecture/Implementation view).
3. **FLOW/SEQ (Flows & Diagrams)**: Visual representation of logic and interactions.

---

## PRD Catalog

| ID | Title | Scope | Priority |
| --- | --- | --- | --- |
| [PRD-001](./PRD-001_Character_Management.md) | **Character Management** | Character creation, stat tracking, inheritance, aptitudes | P0 |
| [PRD-002](./PRD-002_Training_Optimization.md) | **Training Optimization** | Training predictions, risk assessment, recommendations | P0 |
| [PRD-003](./PRD-003_Race_Strategy.md) | **Race Strategy** | Race calendar, readiness scoring, win probability | P0 |
| [PRD-004](./PRD-004_Skill_Management.md) | **Skill Management** | Skill catalog, evolution, hint tracking, loadouts | P0 |
| [PRD-005](./PRD-005_Support_Card_Management.md) | **Support Card Management** | Inventory, deck building, bond tracking, synergy | P0 |
| [PRD-006](./PRD-006_AI_Advisory.md) | **AI Advisory** | Hybrid AI integration (Ollama/Bedrock), contextual advice | P1 |
| [PRD-007](./PRD-007_External_Integration.md) | **External Integration** | External APIs, OCR pipeline, data sync, backups | P1 |

---

## Current Baseline Highlights

This version includes verified game mechanics from the **Global English Server**:

### Skill Hint System (PRD-004)

- **Corrected**: 5 hint levels with 10%/10%/10%/5%/5% discounts (40% max)
- **Added**: Additional discount sources (Fast Learner +10%, Skill Sparks, Hint Books)

### Aptitude System (PRD-001, PRD-003)

- **Corrected**: Maximum grade is S (not SS)
- **Clarified**: A-rank is baseline (0%); only S-rank provides positive bonuses
- **Added**: Complete aptitude modifier table

### Stat System (PRD-001, PRD-002)

- **Updated**: Stats can exceed 1200 with diminishing returns (half value above 1200)
- **Added**: Important breakpoints (901, 1200, 1600)
- **Clarified**: 1200+ Stamina should be treated as an advanced final-spurt advisory in planner
output, not a guaranteed standalone buff label

### Track Conditions (PRD-003)

- **Added**: Complete track condition system (Firm/Good/Soft/Heavy)
- **Added**: Surface-specific penalties (Turf vs Dirt)
- **Added**: Stamina drain modifiers (+2%/sec for Soft/Heavy)

### Career Structure (PRD-002)

- **Clarified**: ~70-78 turns across 3 years (Junior, Classic, Senior)
- **Added**: Summer Training Camp mechanics (4 turns, all facilities Level 5)

---

## Alignment with Technical Specs

Each PRD has a corresponding Technical Specification (SPEC) document detailing the implementation:

- **PRD-001** → [SPEC-001: Character Management Technical](../02-specs/SPEC-001_Character_Management_Technical.md)
- **PRD-002** → [SPEC-002: Training Optimization Technical](../02-specs/SPEC-002_Training_Optimization_Technical.md)
- **PRD-003** → [SPEC-003: Race Strategy Technical](../02-specs/SPEC-003_Race_Strategy_Technical.md)
- **PRD-004** → [SPEC-004: Skill Management Technical](../02-specs/SPEC-004_Skill_Management_Technical.md)
- **PRD-005** → [SPEC-005: Support Card Management Technical](../02-specs/SPEC-005_Support_Card_Management_Technical.md)
- **PRD-006** → [SPEC-006: AI Advisory Technical](../02-specs/SPEC-006_AI_Advisory_Technical.md)
- **PRD-007** → [SPEC-007: External Integration Technical](../02-specs/SPEC-007_External_Integration_Technical.md)

---

## How To Use This Folder

- **Product Owners/Stakeholders**: Start with the **PRD** files to understand feature scope and user stories.
- **Developers**: Use the PRDs to understand the "What" and "Why", then refer to the linked **SPEC**
files for the "How".
- **QA/Testers**: Use User Stories in PRDs as the basis for acceptance criteria.

For a high-level view of the entire system documentation, refer to the [Software Development
Plan](../00-core-docs/001_SDP_Software_Development_Plan.md).

---

## Changelog

| Version | Date | Changes |
| --- | --- | --- |
| 2.4.1 | March 11, 2026 | Refined PRD index wording for the March 2026 audit by softening the high-stamina baseline note and preserving the index as navigation-first documentation. |
| 2.4.0 | March 8, 2026 | Corrected PRD-to-SPEC links to `docs/02-specs`, aligned index metadata to the current `develop` branch documentation snapshot, and clarified that module PRDs use independent revision numbers. |
| 2.2.0 | January 28, 2026 | Updated all PRDs with verified game mechanics from Global English Server. Key corrections: skill hint system (5 levels, 40% max), aptitude scale (G-S, no SS), stat system (1200+ diminishing returns), track conditions, career structure. |
| 2.1.0 | January 27, 2026 | Aligned with codebase v2.2.0, added source specs references. |
| 2.0.0 | January 2026 | Initial v2 release with dual storage architecture. |
