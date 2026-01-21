# PRD-007: External Integration System

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Project**: UmamusumeCareerPlanner  
**Author**: AI Development Team  
**Status**: Draft  
**Related Documents**: [SRS-3.7], [SDS-4.7], [DBD-009], [SPEC-007], [PRD-001], [PRD-006]

**Source Specs**:

- `.kiro/specs/umamusume-career-planner-main/requirements.md` (Requirements)
- `.kiro/specs/umamusume-career-planner-main/design.md` (Design)
- `.kiro/specs/umamusume-career-planner-main/tasks.md` (Implementation Tasks)

**Related Artifacts**:

- SPEC: [SPEC-007](../specs/SPEC-007_External_Integration_Technical.md)
- Flow: [FLOW-007](../flows/FLOW-007_External_Integration_System.md)
- Wireframes: [WF-001](../wireframes/WF-001_Dashboard_Overview.md)
- Sequences: [SEQ-007](../sequences/SEQ-007_External_Data_Sync.md), [SEQ-015](../sequences/SEQ-015_Data_Migration_Snapshot_to_Live.md)
- User Flows: [UF-008](../user-flows/UF-008_OCR_and_Data_Import_Flow.md)

---

## Table of Contents

- [PRD-007: External Integration System](#prd-007-external-integration-system)
  - [Umamusume Pretty Derby Career Planner](#umamusume-pretty-derby-career-planner)
  - [Table of Contents](#table-of-contents)
  - [1. Executive Summary](#1-executive-summary)
    - [1.1 Purpose](#11-purpose)
    - [1.2 Problem Statement](#12-problem-statement)
    - [1.3 Solution Overview](#13-solution-overview)
  - [2. Product Overview](#2-product-overview)
    - [2.1 Objectives](#21-objectives)
    - [2.2 Scope (In)](#22-scope-in)
    - [2.3 Scope (Out)](#23-scope-out)
  - [3. User Stories](#3-user-stories)
  - [4. Functional Requirements](#4-functional-requirements)
  - [5. User Interface Requirements](#5-user-interface-requirements)
  - [6. Data and Integration](#6-data-and-integration)
  - [7. Non-Functional Requirements](#7-non-functional-requirements)
  - [8. Success Metrics](#8-success-metrics)
  - [9. Release Plan](#9-release-plan)
  - [10. Open Questions and Assumptions](#10-open-questions-and-assumptions)

---

## 1. Executive Summary

### 1.1 Purpose

Integrate external data sources (umapyoi.net, optional UmamusumeDB.com) to keep characters, races, skills, and support cards current.

### 1.2 Problem Statement

Stale data leads to incorrect recommendations, invalid validations, and poor AI advice.

### 1.3 Solution Overview

- Scheduled ingests for characters, races, skills, support cards, and events.  
- Normalization/mapping to internal schema with versioning.  
- Health checks, retries, and audit logs for data quality.

---

## 2. Product Overview

### 2.1 Objectives

- Maintain up-to-date reference data with provenance and timestamps.  
- Provide rollback and diff visibility for each sync.  
- Supply validated data to PRDs 001–006 without breaking changes.

### 2.2 Scope (In)

- API clients for primary/secondary sources; rate limit handling.  
- Normalization of races, skills, support cards, factors, scenarios.  
- Validation rules (schema, referential integrity, required fields).  
- Audit logging and monitoring dashboards.

### 2.3 Scope (Out)

- User-provided custom data ingestion (future consideration).  
- Real-time webhooks from external providers (pull-only for now).

---

## 3. User Stories

- As an operator, I want scheduled syncs with alerts on failures.  
- As a developer, I want schema-stable data with version tags to avoid breaking consumers.  
- As a player, I want accurate race requirements and skill data in recommendations.

---

## 4. Functional Requirements

- FR1: Implement connectors for primary source (umapyoi.net) and optional backup (UmamusumeDB.com).  
- FR2: Normalize payloads to internal schema with mapping tables and enums.  
- FR3: Validate data (required fields, referential integrity, value ranges).  
- FR4: Upsert reference tables with versioning and changelog.  
- FR5: Provide health endpoints and sync status dashboard.  
- FR6: Retry with backoff on transient errors; alert on persistent failures.  
- FR7: Expose data access APIs to consuming services (training, race, skills, decks, AI advisory).

---

## 5. User Interface Requirements

- Operator dashboard with last sync time, record counts, and diff summary.  
- Error log viewer with sample payloads and validation issues.  
- Manual re-run button for a specific feed and dry-run mode.  
- Permission-gated access for operators only.

---

## 6. Data and Integration

- Data: reference tables for characters, races, skills, support cards, factors, scenarios.  
- Services: SyncScheduler, SourceClient(s), Normalizer, Validator, AuditLogger.  
- Dependencies: SRS-3.7, SDS-4.7, DBD-009 reference schema.  
- Downstream consumers: PRD-001..006, MCP configuration reference.

---

## 7. Non-Functional Requirements

- Reliability: failed sync auto-retries 3 times with backoff; alert within 5 minutes.  
- Performance: nightly full sync completes within 10 minutes; incremental sync within 90 seconds.  
- Observability: metrics for success/fail counts, records processed, and validation errors.  
- Security: API keys stored in secrets; encrypted at rest; least privilege access.

---

## 8. Success Metrics

- Sync success rate ≥99%.  
- Data freshness: ≥95% of records updated within 24 hours of source change.  
- Validation error rate <1% of records per sync.  
- Rollback time <5 minutes using versioned snapshots.

---

## 9. Release Plan

- Phase A: Primary source connector + normalization + audit logs.  
- Phase B: Validation, retries, and operator dashboard.  
- Phase C: Backup source failover and rollback tooling.

---

## 10. Open Questions and Assumptions

- Assumption: Source APIs remain stable; rate limits documented.  
- Question: How to handle license/attribution requirements for external data?  
- Question: Should we expose public API endpoints for third parties?
