# Product Requirement Documents Index

## Umamusume Pretty Derby Career Planner

**Document Version**: 2.2.0
**Date**: January 27, 2026
**Project**: UmamusumeCareerPlanner
**Author**: Development Team
**Status**: Current - Aligned with codebase v2.2.0

---

## Documentation Hierarchy

These PRDs define the product requirements that drive the technical specifications and implementation logic for v2.2.0:

1. **PRD (Product Requirements)**: What we are building and why (Business/User view).
2. **SPEC (Technical Specifications)**: How we build it (Architecture/Implementation view).
3. **FLOW/SEQ (Flows & Diagrams)**: Visual representation of logic and interactions.

---

## PRD Catalog

| ID | Title | Scope | Priority |
|----|-------|-------|----------|
| [PRD-001](./PRD-001_Character_Management.md) | **Character Management** | Character creation, stat tracking, inheritance, aptitudes | P0 |
| [PRD-002](./PRD-002_Training_Optimization.md) | **Training Optimization** | Training predictions, risk assessment, recommendations | P0 |
| [PRD-003](./PRD-003_Race_Strategy.md) | **Race Strategy** | Race calendar, readiness scoring, win probability | P0 |
| [PRD-004](./PRD-004_Skill_Management.md) | **Skill Management** | Skill catalog, evolution, hint tracking, loadouts | P0 |
| [PRD-005](./PRD-005_Support_Card_Management.md) | **Support Card Management** | Inventory, deck building, bond tracking, synergy | P0 |
| [PRD-006](./PRD-006_AI_Advisory.md) | **AI Advisory** | Hybrid AI integration (Ollama/Bedrock), contextual advice | P1 |
| [PRD-007](./PRD-007_External_Integration.md) | **External Integration** | External APIs, OCR pipeline, data sync, backups | P1 |

---

## Alignment with Technical Specs

Each PRD has a corresponding Technical Specification (SPEC) document detailing the implementation:

- **PRD-001** → [SPEC-001: Character Management Technical](../specs/SPEC-001_Character_Management_Technical.md)
- **PRD-002** → [SPEC-002: Training Optimization Technical](../specs/SPEC-002_Training_Optimization_Technical.md)
- **PRD-003** → [SPEC-003: Race Strategy Technical](../specs/SPEC-003_Race_Strategy_Technical.md)
- **PRD-004** → [SPEC-004: Skill Management Technical](../specs/SPEC-004_Skill_Management_Technical.md)
- **PRD-005** → [SPEC-005: Support Card Management Technical](../specs/SPEC-005_Support_Card_Management_Technical.md)
- **PRD-006** → [SPEC-006: AI Advisory Technical](../specs/SPEC-006_AI_Advisory_Technical.md)
- **PRD-007** → [SPEC-007: External Integration Technical](../specs/SPEC-007_External_Integration_Technical.md)

---

## How To Use This Folder

- **Product Owners/Stakeholders**: Start with the **PRD** files to understand feature scope and user stories.
- **Developers**: Use the PRDs to understand the "What" and "Why", then refer to the linked **SPEC** files for the "How".
- **QA/Testers**: Use User Stories in PRDs as the basis for acceptance criteria.

For a high-level view of the entire system documentation, refer to the [Software Development Plan](../001_SDP_Software_Development_Plan.md).
