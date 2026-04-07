# PRD-007: External Integration System

## Umamusume Pretty Derby Career Planner

**Document Version**: 2.2.0
**Date**: January 28, 2026
**Project**: UmamusumeCareerPlanner
**Author**: Development Team
**Status**: Current - Aligned with codebase v2.2.0
**Related Documents**: [SRS-FR-08], [SDS-4.7], [DBD-2.1], [SPEC-007]

**Source Specs**:

- `.kiro/specs/umamusume-career-planner-main/requirements.md` (Requirements)
- `.kiro/specs/umamusume-career-planner-main/design.md` (Design)
- `.kiro/specs/umamusume-career-planner-main/tasks.md` (Implementation Tasks)

**Related Artifacts**:

- SPEC: [SPEC-007](../02-specs/SPEC-007_External_Integration_Technical.md)
- Flow: [FLOW-007](../01-flows/FLOW-007_External_Integration_System.md)
- Sequence: [SEQ-007](../01-sequences/SEQ-007_External_Data_Sync.md)
- User Flows: [UF-008](../01-user-flows/UF-008_OCR_and_Data_Import_Flow.md)

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
    - [4.1 External Data Sync \[FR-08.1, FR-08.3\]](#41-external-data-sync-fr-081-fr-083)
    - [4.2 Game Mechanics Data Sync \[FR-08.2\]](#42-game-mechanics-data-sync-fr-082)
    - [4.3 OCR Pipeline \[FR-08.4\]](#43-ocr-pipeline-fr-084)
    - [4.4 Data Management \[FR-09\]](#44-data-management-fr-09)
    - [4.5 Sync Status Updates \[FR-08.5\]](#45-sync-status-updates-fr-085)
  - [5. User Interface Requirements](#5-user-interface-requirements)
    - [5.1 OCR Upload Modal](#51-ocr-upload-modal)
    - [5.2 Sync Status Dashboard (Admin)](#52-sync-status-dashboard-admin)
    - [5.3 Import/Export Panel](#53-importexport-panel)
  - [6. Data and Integration](#6-data-and-integration)
    - [6.1 Data Models](#61-data-models)
    - [6.2 Service Architecture](#62-service-architecture)
  - [7. Non-Functional Requirements](#7-non-functional-requirements)
  - [8. Success Metrics](#8-success-metrics)
  - [9. Release Plan](#9-release-plan)
  - [10. Open Questions and Assumptions](#10-open-questions-and-assumptions)
  - [Changelog](#changelog)

---

## 1. Executive Summary

### 1.1 Purpose

Ensure the application maintains up-to-date game data and provides robust data interchange capabilities.

### 1.2 Problem Statement

Game data changes frequently (new banners, balance patches). Manual updates are unsustainable.
Additionally, manually inputting character stats for planning is tedious and error-prone.

### 1.3 Solution Overview

- **API Sync**: Automated background jobs to fetch data from `umapyoi.net` (Primary) and `umamusumedb.com` (Fallback).
- **OCR Pipeline**: Automated extraction of game stats from screenshots using **Tesseract** with
**GD** image preprocessing.
- **Resilience**: Implementation of **Circuit Breaker** patterns to handle external API downtime gracefully.
- **Status Delivery**: Queue-backed sync completion, cached status, and polling-friendly UI refresh
flows for connected clients.

---

## 2. Product Overview

### 2.1 Objectives

- Maintain >99% data accuracy compared to the live game.
- Reduce user data entry time by >80% via OCR.
- Ensure system stability even when external data providers are offline.

### 2.2 Scope (In)

- **External APIs**: Connectors for `umapyoi.net` and `umamusumedb.com`.
- **OCR Service**: Image upload, preprocessing, text extraction, and parsing logic.
- **Data Management**: Import/Export of user plans (JSON/CSV/Excel).
- **Caching**: Redis-backed caching for external responses (24h TTL).
- **Status Delivery**: Surface sync completion through persisted status records, cache state, and
next-refresh UI indicators.

### 2.3 Scope (Out)

- **Game Server Interaction**: No direct packet sniffing or connection to Cygames servers.
- **Video Processing**: OCR is limited to static screenshots, not video streams.

---

## 3. User Stories

| ID | Actor | Story | Acceptance Criteria |
| --- | --- | --- | --- |
| US-7.1 | Admin | I want the card database to update automatically when a new banner drops. | Scheduled job runs daily; fetches new cards; updates DB. |
| US-7.2 | Player | I want to upload a screenshot of my character's end-of-run stats to save time. | Upload image → system extracts candidate values → low-confidence fields are flagged for manual review before apply. Applying OCR-derived data must respect the active storage mode. The system must not auto-persist extracted values without a confirmed apply step. |
| US-7.3 | Player | I want to export my race plans to Excel to share with my circle. | "Export" button generates a valid .xlsx file. |
| US-7.4 | System | I want to stop calling an API if it keeps timing out to prevent app lag. | Circuit breaker opens after 5 failures; returns cached/stale data. |

---

## 4. Functional Requirements

### 4.1 External Data Sync [FR-08.1, FR-08.3]

- **Sources**: Support multiple upstream providers.
- **Strategy**:
  1. Attempt Primary (`umapyoi.net`).
  2. If Fail/Timeout -> Log Error -> Attempt Fallback (`umamusumedb.com`).
  3. If All Fail -> Use Stale Cache -> Notify Admin.
- **Normalization**: Map external JSON schemas to internal `ucp_` database schema.

### 4.2 Game Mechanics Data Sync [FR-08.2]

Ensure synced data reflects accurate game mechanics:

**Skill Hint System**:

- 5 hint levels with 10%/10%/10%/5%/5% discounts (40% max)
- Additional sources: Fast Learner (+10%), Skill Sparks, Hint Books

**Aptitude System**:

- Grade scale: G → F → E → D → C → B → A → S (no SS)
- A-rank baseline; only S-rank provides positive bonuses

**Stat System**:

- Soft cap at 1200 with diminishing returns above
- Important breakpoints: 901, 1200, 1600

**Track Conditions**:

- Firm: No penalties
- Good: -50 Power
- Soft: -50/-100 Power, +2%/sec stamina drain
- Heavy: -50/-100 Power, -50 Speed, +2%/sec stamina drain

### 4.3 OCR Pipeline [FR-08.4]

The OCR pipeline must: (1) validate MIME type and file signature before processing, (2) preprocess
images for OCR, (3) extract candidate values, (4) score confidence per field, and (5) require manual
review for low-confidence outputs. If OCR fails, the system must return a recoverable error state
and preserve the original upload context for retry or manual entry.

**Implementation Reference**:

- **Preprocessing**: Resize to max 2000px, Grayscale, Adaptive Thresholding (GD Library).
- **Extraction**: Tesseract OCR engine (v5+) with Japanese/English language packs.
- **Parsing**: Regex-based extraction for Stats (S/S/P/G/W), Skill names, and Race results.
- **Confidence Threshold**: 80% (flag below for manual review).

### 4.4 Data Management [FR-09]

Import and export actions must identify whether they are review-only, local-apply, or account-
persist actions. After import, the system must show a summary of created, updated, skipped, and
errored items. Export options must state whether the output reflects browser-local data, account-
backed data, or the current visible report scope.

### 4.5 Sync Status Updates [FR-08.5]

- **Technology**: Laravel jobs, cache-backed status tracking, and HTTP refresh endpoints.
- **Events/Signals**: Persist sync and OCR completion state for admin dashboards and polling clients.
- **UX**: Show updated sync status and "new data available" messaging on the next refresh or poll cycle.

---

## 5. User Interface Requirements

### 5.1 OCR Upload Modal

- **Dropzone**: Drag & drop area for images.
- **Preview**: Thumbnail of uploaded image.
- **Processing State**: Progress bar / Spinner.
- **Review Screen**: Side-by-side view of Image Crop vs Extracted Value for manual correction.

### 5.2 Sync Status Dashboard (Admin)

- **Status Indicators**: Green/Red badges for each API provider.
- **Logs**: Recent sync attempts, duration, and record counts.
- **Controls**: "Force Sync Now" button.

### 5.3 Import/Export Panel

The panel must include empty-state guidance, format guidance, and conflict controls only when
conflicts are detected. After an import or export action, the user must receive a clear post-action
summary.

---

## 6. Data and Integration

### 6.1 Data Models

- **Cache**: `ucp_external_api_cache` (stores raw JSON responses).
- **OCR Logs**: `ucp_ocr_extractions` (audit trail of uploads and confidence scores).
- **Reference Tables**: `ucp_game_data` (versioning metadata).

### 6.2 Service Architecture

- **ExternalAPIService**: Facade for API clients.
- **CircuitBreaker**: Middleware state machine (Closed -> Open -> Half-Open).
- **TesseractService**: Orchestrator for image preprocessing, OCR extraction, and parsing workflows.

---

## 7. Non-Functional Requirements

- **Resilience**: System must not crash if external APIs return 500 or 404.
- **Performance**:
  - API Sync: Background job, zero impact on frontend latency.
  - OCR: < 5 seconds processing time per image.
- **Security**: Validate uploaded files by MIME type and signature, sanitize external strings before
persistence, and do not partially and silently persist rejected or invalid payloads.

---

## 8. Success Metrics

- **Data Freshness**: Database reflects game updates within 24 hours.
- **OCR Accuracy**: > 90% field recognition rate on standard UI screenshots.
- **Resilience**: 100% uptime of the planner even during external API outages.

---

## 9. Release Plan

- **v2.0.0**:
  - Connectors for primary/fallback APIs.
  - Circuit Breaker logic.
  - Basic OCR (Stats only).
  - JSON Import/Export.
- **v2.1.0**:
  - Advanced OCR (Skill icons, Race results).
  - Community-shared deck imports via URL.
  - Auto-repair of corrupted local data.
- **v2.2.0 (Current)**:
  - Game mechanics data validation against verified sources.
  - Updated schema to support accurate game mechanics.

---

## 10. Open Questions and Assumptions

- **Assumption**: `umapyoi.net` API remains free and public.
- **Assumption**: Tesseract language data files are installed on the server environment.
- **Open Question**: How to handle copyright on card images fetched from external APIs? *Current:
Proxy/Cache images locally.*

---

## Changelog

| Version | Date | Changes |
| --- | --- | --- |
| 2.2.0 | January 28, 2026 | Updated with verified game mechanics from Global English Server: added game mechanics data sync requirements ensuring accurate skill hint system (5 levels, 40% max), aptitude system (G-S, no SS), stat system (1200+ diminishing returns), and track conditions. |
| 2.1.0 | January 24, 2026 | Aligned with codebase v2.0.0, added source specs references. |
| 2.0.0 | January 2026 | Initial v2 release with API sync and OCR. |
