# PRD-007: External Integration System

## Umamusume Pretty Derby Career Planner

**Document Version**: 2.1.0  
**Date**: January 24, 2026  
**Project**: UmamusumeCareerPlanner  
**Author**: Development Team  
**Status**: Current - Aligned with codebase v2.0.0  
**Related Documents**: [SRS-FR-08], [SDS-4.7], [DBD-2.1], [SPEC-007]

**Source Specs**:

- `.kiro/specs/umamusume-career-planner-main/requirements.md` (Requirements)
- `.kiro/specs/umamusume-career-planner-main/design.md` (Design)
- `.kiro/specs/umamusume-career-planner-main/tasks.md` (Implementation Tasks)

**Related Artifacts**:

- SPEC: [SPEC-007](../specs/SPEC-007_External_Integration_Technical.md)
- Flow: [FLOW-007](../flows/FLOW-007_External_Integration_System.md)
- Sequence: [SEQ-007](../sequences/SEQ-007_External_Data_Sync.md)
- User Flows: [UF-008](../user-flows/UF-008_OCR_and_Data_Import_Flow.md)

---

## Table of Contents

- [PRD-007: External Integration System](#prd-007-external-integration-system)
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

Ensure the application maintains up-to-date game data and provides robust data interchange capabilities. This includes syncing reference data (Characters, Cards, Skills) from external community APIs and enabling user data import via OCR and file upload.

### 1.2 Problem Statement

Game data changes frequently (new banners, balance patches). Manual updates are unsustainable. Additionally, manually inputting character stats for planning is tedious and error-prone.

### 1.3 Solution Overview

- **API Sync**: Automated background jobs to fetch data from `umapyoi.net` (Primary) and `umamusumedb.com` (Fallback).
- **OCR Pipeline**: Automated extraction of game stats from screenshots using **Tesseract** with **GD** image preprocessing.
- **Resilience**: Implementation of **Circuit Breaker** patterns to handle external API downtime gracefully.
- **Real-time**: **WebSocket (Laravel Reverb)** integration to push updates to connected clients.

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
- **Real-time**: Broadcasting sync completion events via WebSockets.

### 2.3 Scope (Out)

- **Game Server Interaction**: No direct packet sniffing or connection to Cygames servers.
- **Video Processing**: OCR is limited to static screenshots, not video streams.

---

## 3. User Stories

| ID | Actor | Story | Acceptance Criteria |
|----|-------|-------|---------------------|
| US-7.1 | Admin | I want the card database to update automatically when a new banner drops. | Scheduled job runs daily; fetches new cards; updates DB. |
| US-7.2 | Player | I want to upload a screenshot of my character's end-of-run stats to save time. | Upload image -> System fills in Speed/Stamina/etc. fields. |
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

### 4.2 OCR Pipeline [FR-08.4]

- **Preprocessing**: Resize to max 2000px, Grayscale, Adaptive Thresholding (GD Library).
- **Extraction**: Tesseract OCR engine (v5+) with Japanese/English language packs.
- **Parsing**: Regex-based extraction for Stats (S/S/P/G/W), Skill names, and Race results.
- **Validation**: Confidence scoring. Flag low-confidence (<80%) fields for manual user review.

### 4.3 Data Management [FR-09]

- **Import**:
  - Detect format (JSON v1/v2, CSV).
  - Validate schema.
  - Conflict resolution (Skip/Overwrite/Copy).
- **Export**:
  - JSON (Full backup).
  - Excel/CSV (Tabular data for analysis).

### 4.4 Real-time Updates [FR-08.5]

- **Technology**: Laravel Reverb.
- **Events**: `DataSyncCompleted`, `OCRProcessingFinished`.
- **UX**: Show "New Data Available" toast to active users without page reload.

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

- **Export**: Checkboxes for data types (Characters, Decks, History). Format dropdown.
- **Import**: File picker. Conflict resolution radio buttons.

---

## 6. Data and Integration

### 6.1 Data Models

- **Cache**: `ucp_external_api_cache` (stores raw JSON responses).
- **OCR Logs**: `ucp_ocr_extractions` (audit trail of uploads and confidence scores).
- **Reference Tables**: `ucp_game_data` (versioning metadata).

### 6.2 Service Architecture

- **ExternalAPIService**: Facade for API clients.
- **CircuitBreaker**: Middleware state machine (Closed -> Open -> Half-Open).
- **OCRService**: Orchestrator for ImageProc -> Tesseract -> Parser.

---

## 7. Non-Functional Requirements

- **Resilience**: System must not crash if external APIs return 500 or 404.
- **Performance**:
  - API Sync: Background job, zero impact on frontend latency.
  - OCR: < 5 seconds processing time per image.
- **Security**:
  - Validate all uploaded files (MIME type, magic bytes) to prevent malware.
  - Sanitize all external strings before DB insertion (XSS prevention).

---

## 8. Success Metrics

- **Data Freshness**: Database reflects game updates within 24 hours.
- **OCR Accuracy**: > 90% field recognition rate on standard UI screenshots.
- **Resilience**: 100% uptime of the planner even during external API outages.

---

## 9. Release Plan

- **v2.0.0 (Current)**:
  - Connectors for primary/fallback APIs.
  - Circuit Breaker logic.
  - Basic OCR (Stats only).
  - JSON Import/Export.
- **v2.1.0 (Next)**:
  - Advanced OCR (Skill icons, Race results).
  - Community-shared deck imports via URL.
  - Auto-repair of corrupted local data.

---

## 10. Open Questions and Assumptions

- **Assumption**: `umapyoi.net` API remains free and public.
- **Assumption**: Tesseract language data files are installed on the server environment.
- **Open Question**: How to handle copyright on card images fetched from external APIs? *Current: Proxy/Cache images locally.*
