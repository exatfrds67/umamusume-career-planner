# Documentation Index

## Umamusume Pretty Derby Career Planner

**Document Version**: 3.1
**Date**: January 23, 2026
**Project**: UmamusumeCareerPlanner
**Author**: Development Team
**Status**: Current - Aligned to codebase

---

## Table of Contents

1. [Document Overview](#1-document-overview)
2. [Document Catalog](#2-document-catalog)
3. [Document Dependencies](#3-document-dependencies)
4. [Quick Reference Guide](#4-quick-reference-guide)
5. [Archive Structure](#5-archive-structure)

---

## 1. Document Overview

This index provides a current reference to core documentation in `docs/00-core-docs`, aligned with the present codebase implementation.

### 1.1 Documentation Structure

- **Core Docs**: Authoritative, implementation-aligned documents in `docs/00-core-docs/`
- **Product/Specs**: Detailed PRDs and specs in `docs/02-prds/` and `docs/02-specs/`
- **Flows/Diagrams**: Supporting diagrams in `docs/01-*` folders
- **Implementation Summaries**: Status and changes in `docs/implementation-summaries/`

### 1.2 Documentation Suite Summary

| Category | Documents | Purpose |
| --- | --- | --- |
| Reference | 000_MASTER_GLOSSARY, 000_DOCUMENT_INDEX | Terminology and navigation |
| Planning | 001_SDP | Current development plan and milestones |
| Requirements | 002_BRS, 003_SRS | Business and software requirements (current scope) |
| Design | 004_SDS | Current technical architecture and design |
| Migration | 005_DMP, 006_DMS | Data migration plan and technical specs |
| Integration | 007_SIP, 008_SIS | Integration plan and specifications |
| Technical | 009_DBD, 010_SCD | Database and source code documentation |
| Verification | 000_IMPLEMENTATION_VERIFICATION_MATRIX, 000_REQUIREMENTS_TRACEABILITY_MATRIX | Implementation status and traceability |
| User | 017_SUM | End-user manual |

---

## 2. Document Catalog

### 2.1 Reference Documents

- **000_MASTER_GLOSSARY.md**: Standardized terminology for the system
- **000_DOCUMENT_INDEX.md**: This index

### 2.2 Planning Documents

- **001_SDP_Software_Development_Plan.md**: Current roadmap, phases, and milestones

### 2.3 Requirements Documents

- **002_BRS_Business_Requirements_Specifications.md**: Business goals and current scope
- **003_SRS_Software_Requirement_Specifications.md**: Functional and non-functional requirements aligned to the implemented system

### 2.4 Design Documents

- **004_SDS_Software_Design_Specifications.md**: System architecture and implementation design

### 2.5 Migration Documents

- **005_DMP_Data_Migration_Plan.md**: Migration strategy and procedures
- **006_DMS_Data_Migration_Specifications.md**: Migration technical specifications

### 2.6 Integration Documents

- **007_SIP_Software_Integration_Plan.md**: Integration plan
- **008_SIS_Software_Integration_Specifications.md**: Integration technical details

### 2.7 Technical Documents

- **009_DBD_Database_Documentation.md**: Database schema and relationships
- **010_SCD_Source_Code_Documentation.md**: Codebase structure and key components

### 2.8 Verification Documents

- **000_IMPLEMENTATION_VERIFICATION_MATRIX.md**: Implementation status snapshot
- **000_REQUIREMENTS_TRACEABILITY_MATRIX.md**: Requirements to implementation traceability

### 2.9 User Documents

- **017_SUM_Software_User_Manual.md**: User manual and feature walkthroughs

---

## 3. Document Dependencies

```text
000_MASTER_GLOSSARY
    |
    +-- 002_BRS
    |     |
    |     +-- 003_SRS
    |           |
    |           +-- 004_SDS
    |           |     |
    |           |     +-- 007_SIP --> 008_SIS
    |           |     +-- 005_DMP --> 006_DMS
    |           |     +-- 009_DBD
    |           |     +-- 010_SCD
    |           |
    |           +-- 000_REQUIREMENTS_TRACEABILITY_MATRIX
    |           +-- 000_IMPLEMENTATION_VERIFICATION_MATRIX
    |
    +-- 017_SUM
```

---

## 4. Quick Reference Guide

### 4.1 By Topic

| Topic | Primary Document | Supporting Documents |
| --- | --- | --- |
| Architecture | 004_SDS | 010_SCD, 009_DBD |
| Database | 009_DBD | 004_SDS |
| API & Routes | 010_SCD | 004_SDS |
| AI & MCP Integration | 007_SIP, 008_SIS | 004_SDS |
| Data Migration | 005_DMP | 006_DMS |
| Implementation Status | 000_IMPLEMENTATION_VERIFICATION_MATRIX | 000_REQUIREMENTS_TRACEABILITY_MATRIX |
| User Guide | 017_SUM | 000_MASTER_GLOSSARY |

### 4.2 By Role

| Role | Primary Documents |
| --- | --- |
| Project Manager | 001_SDP, 002_BRS |
| Architect | 004_SDS, 007_SIP |
| Backend Developer | 010_SCD, 009_DBD, 003_SRS |
| Frontend Developer | 010_SCD, 017_SUM |
| QA Engineer | 003_SRS, 000_REQUIREMENTS_TRACEABILITY_MATRIX |
| Support | 017_SUM |

---

## 5. Archive Structure

Archived documents live in `docs/archive/`:

- `versions/`: Superseded document versions
- `task-summaries/`: Completed task summaries
- `superseded/`: Retired documents

---

## Document Control

| Version | Date | Author | Changes |
| --- | --- | --- | --- |
| 3.1 | 2026-01-23 | Development Team | Updated catalog and dependency map to match current codebase |
| 3.0 | 2026-01-12 | Development Team | Documentation consolidation and archive structure |

---

*This index is the authoritative navigation guide for the Umamusume Career Planner core documentation suite.*
