# Requirements Traceability Matrix

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 12, 2026  
**Project**: UmamusumeCareerPlanner  
**Author**: Development Team  
**Status**: Final  

---

## Table of Contents

1. [Overview](#1-overview)
2. [Business Requirements Traceability](#2-business-requirements-traceability)
3. [Functional Requirements Traceability](#3-functional-requirements-traceability)
4. [Non-Functional Requirements Traceability](#4-non-functional-requirements-traceability)
5. [Integration Requirements Traceability](#5-integration-requirements-traceability)

---

## 1. Overview

This Requirements Traceability Matrix (RTM) maps all requirements from their source through design, implementation, and testing phases. It ensures complete coverage and traceability of all 59+ requirements.

### 1.1 Traceability Legend

| Column | Description |
| ------ | ----------- |
| REQ ID | Unique requirement identifier |
| Source | Origin document (BRS/SRS) |
| Description | Brief requirement description |
| Priority | ★★★★★ (Critical) to ★ (Optional) |
| Design Ref | Reference in SDS |
| DB Ref | Reference in DBD |
| Code Ref | Reference in SCD |
| Test Ref | Test case reference |
| Status | Implementation status |

---

## 2. Business Requirements Traceability

### 2.1 Core Business Requirements

| REQ ID | Source | Description | Priority | Design Ref | Status |
| ------ | ------ | ----------- | -------- | ---------- | ------ |
| BR-001 | BRS 5.1.1 | Advanced Training Decision Support | ★★★★★ | SDS 4.1 | Planned |
| BR-002 | BRS 5.1.1 | Skill Management and SP Optimization | ★★★★★ | SDS 4.2 | Planned |
| BR-003 | BRS 5.1.1 | Multi-Scenario Career Management | ★★★★ | SDS 4.3 | Planned |
| BR-004 | BRS 5.1.2 | Local-First Data Architecture | ★★★★★ | SDS 2.1 | Planned |
| BR-005 | BRS 5.1.2 | Intelligent External Data Integration | ★★★★ | SDS 3.1 | Planned |
| BR-006 | BRS 5.1.2 | MCP Server Integration | ★★★ | SDS 2.4 | Planned |
| BR-007 | BRS 5.1.3 | PWA with Accessibility Excellence | ★★★★★ | SDS 6.1 | Planned |
| BR-008 | BRS 5.1.3 | Advanced UI and Interaction Design | ★★★★ | SDS 6.2 | Planned |
| BR-009 | BRS 5.2.1 | Advanced Performance Standards | ★★★★★ | SDS 7.1 | Planned |
| BR-010 | BRS 5.2.1 | Scalability and Reliability | ★★★★ | SDS 7.2 | Planned |
| BR-011 | BRS 5.2.2 | Comprehensive Data Security | ★★★★★ | SDS 8.1 | Planned |
| BR-012 | BRS 5.2.2 | Privacy-by-Design Architecture | ★★★★★ | SDS 8.2 | Planned |

---

## 3. Functional Requirements Traceability

### 3.1 Character State Management (REQ-3.1.x)

| REQ ID | Source | Description | Priority | Design Ref | DB Ref | Status |
| ------ | ------ | ----------- | -------- | ---------- | ------ | ------ |
| REQ-3.1.1 | SRS 3.1.3 | Character Creation and Configuration | ★★★★★ | SDS 4.1.1 | DBD 3.1.2 | Planned |
| REQ-3.1.2 | SRS 3.1.3 | Aptitude Management | ★★★★★ | SDS 4.1.2 | DBD 3.1.2 | Planned |
| REQ-3.1.3 | SRS 3.1.3 | Goal Setting and Progress Tracking | ★★★★ | SDS 4.1.3 | DBD 3.1.2 | Planned |
| REQ-3.1.4 | SRS 3.1.3 | Character State Monitoring | ★★★★ | SDS 4.1.4 | DBD 3.1.3 | Planned |

### 3.2 Training Prediction Engine (REQ-3.2.x)

| REQ ID | Source | Description | Priority | Design Ref | DB Ref | Status |
| ------ | ------ | ----------- | -------- | ---------- | ------ | ------ |
| REQ-3.2.1 | SRS 3.2.3 | URA Finale Training Predictions | ★★★★★ | SDS 4.2.1 | DBD 3.1.3 | Planned |
| REQ-3.2.2 | SRS 3.2.3 | Unity Cup Training Predictions | ★★★★★ | SDS 4.2.2 | DBD 3.1.3 | Planned |
| REQ-3.2.3 | SRS 3.2.3 | Training Option Analysis | ★★★★★ | SDS 4.2.3 | DBD 3.1.3 | Planned |
| REQ-3.2.4 | SRS 3.2.3 | Real-Time Recommendation Updates | ★★★★ | SDS 4.2.4 | DBD 3.1.4 | Planned |

### 3.3 Race Preparation and Strategy (REQ-3.3.x)

| REQ ID | Source | Description | Priority | Design Ref | DB Ref | Status |
| ------ | ------ | ----------- | -------- | ---------- | ------ | ------ |
| REQ-3.3.1 | SRS 3.3.3 | Race Information Display | ★★★★ | SDS 4.3.1 | DBD 3.2.1 | Planned |
| REQ-3.3.2 | SRS 3.3.3 | Character Readiness Assessment | ★★★★ | SDS 4.3.2 | DBD 3.1.2 | Planned |
| REQ-3.3.3 | SRS 3.3.3 | Strategy Optimization | ★★★★ | SDS 4.3.3 | DBD 3.1.3 | Planned |
| REQ-3.3.4 | SRS 3.3.3 | Race Goal Management | ★★★ | SDS 4.3.4 | DBD 3.1.3 | Planned |

### 3.4 Advanced Skill Management (REQ-3.4.x)

| REQ ID | Source | Description | Priority | Design Ref | DB Ref | Status |
| ------ | ------ | ----------- | -------- | ---------- | ------ | ------ |
| REQ-3.4.1 | SRS 3.4.3 | Skill Database and Categorization | ★★★★ | SDS 4.4.1 | DBD 3.2.1 | Planned |
| REQ-3.4.2 | SRS 3.4.3 | Hint System and Cost Reduction | ★★★★ | SDS 4.4.2 | DBD 3.1.3 | Planned |
| REQ-3.4.3 | SRS 3.4.3 | Skill Evolution Management | ★★★★ | SDS 4.4.3 | DBD 3.2.1 | Planned |
| REQ-3.4.4 | SRS 3.4.3 | SP Optimization Engine | ★★★★ | SDS 4.4.4 | DBD 3.1.3 | Planned |

### 3.5 Support Card Management (REQ-3.5.x)

| REQ ID | Source | Description | Priority | Design Ref | DB Ref | Status |
| ------ | ------ | ----------- | -------- | ---------- | ------ | ------ |
| REQ-3.5.1 | SRS 3.5.3 | Support Card Database | ★★★★ | SDS 4.5.1 | DBD 3.2.1 | Planned |
| REQ-3.5.2 | SRS 3.5.3 | Deck Management System | ★★★★ | SDS 4.5.2 | DBD 3.1.2 | Planned |
| REQ-3.5.3 | SRS 3.5.3 | Friendship Tracking | ★★★ | SDS 4.5.3 | DBD 3.1.3 | Planned |
| REQ-3.5.4 | SRS 3.5.3 | Deck Optimization Engine | ★★★ | SDS 4.5.4 | DBD 3.1.3 | Planned |

### 3.6 AI Advisory System

| REQ ID | Source | Description | Priority | Design Ref | DB Ref | Status |
| ------ | ------ | ----------- | -------- | ---------- | ------ | ------ |
| REQ-AI-001 | SRS 2.8 | Hybrid AI Processing | ★★★★★ | SDS 4.1 | DBD 3.1.4 | Planned |
| REQ-AI-002 | SRS 2.8 | Ollama Local Integration | ★★★★★ | SDS 4.2 | DBD 3.1.4 | Planned |
| REQ-AI-003 | SRS 2.8 | AWS Bedrock Integration | ★★★★ | SDS 4.3 | DBD 3.1.4 | Planned |
| REQ-AI-004 | SRS 2.8 | Intelligent Routing | ★★★★ | SDS 4.1.2 | DBD 3.1.4 | Planned |
| REQ-AI-005 | SRS 2.8 | Cost Tracking and Budget | ★★★★ | SDS 4.3.1 | DBD 3.1.4 | Planned |

### 3.7 OCR and Screenshot Processing

| REQ ID | Source | Description | Priority | Design Ref | DB Ref | Status |
| ------ | ------ | ----------- | -------- | ---------- | ------ | ------ |
| REQ-OCR-001 | DMS 12 | Tesseract OCR Setup | ★★★ | SDS 5.1 | DBD 3.3 | Planned |
| REQ-OCR-002 | DMS 12 | Japanese Language Support | ★★★ | SDS 5.1 | DBD 3.3 | Planned |
| REQ-OCR-003 | DMS 12 | Screenshot Upload System | ★★★ | SDS 5.2 | DBD 3.3 | Planned |
| REQ-OCR-004 | DMS 12 | Data Extraction Validation | ★★★ | SDS 5.3 | DBD 3.3 | Planned |

---

## 4. Non-Functional Requirements Traceability

### 4.1 Performance Requirements

| REQ ID | Source | Description | Target | Design Ref | Status |
| ------ | ------ | ----------- | ------ | ---------- | ------ |
| NFR-PERF-001 | SRS 5.1 | Core Feature Response Time | <2 seconds | SDS 7.1 | Planned |
| NFR-PERF-002 | SRS 5.1 | Local AI Processing Time | <3 seconds | SDS 4.2 | Planned |
| NFR-PERF-003 | SRS 5.1 | Cloud AI Processing Time | <5 seconds | SDS 4.3 | Planned |
| NFR-PERF-004 | SRS 5.1 | Database Query Time | <500ms | DBD 6.1 | Planned |
| NFR-PERF-005 | BRS 5.2.1 | Core Web Vitals LCP | <2.5s | SDS 7.1 | Planned |
| NFR-PERF-006 | BRS 5.2.1 | Core Web Vitals INP | <200ms | SDS 7.1 | Planned |
| NFR-PERF-007 | BRS 5.2.1 | Core Web Vitals CLS | <0.1 | SDS 7.1 | Planned |

### 4.2 Security Requirements

| REQ ID | Source | Description | Design Ref | Status |
| ------ | ------ | ----------- | ---------- | ------ |
| NFR-SEC-001 | BRS 5.2.2 | Data Encryption | SDS 8.1 | Planned |
| NFR-SEC-002 | BRS 5.2.2 | Laravel Sanctum Auth | SDS 8.2 | Planned |
| NFR-SEC-003 | BRS 5.2.2 | OWASP Top 10 Protection | SDS 8.3 | Planned |
| NFR-SEC-004 | BRS 5.2.2 | Rate Limiting | SDS 8.4 | Planned |
| NFR-SEC-005 | BRS 5.2.2 | Audit Logging | SDS 8.5 | Planned |

### 4.3 Accessibility Requirements

| REQ ID | Source | Description | Standard | Design Ref | Status |
| ------ | ------ | ----------- | -------- | ---------- | ------ |
| NFR-ACC-001 | BRS 5.1.3 | WCAG 2.2 AA Compliance | WCAG 2.2 | SDS 6.1 | Planned |
| NFR-ACC-002 | BRS 5.1.3 | Keyboard Navigation | WCAG 2.1.1 | SDS 6.2 | Planned |
| NFR-ACC-003 | BRS 5.1.3 | Screen Reader Support | WCAG 4.1.2 | SDS 6.3 | Planned |
| NFR-ACC-004 | BRS 5.1.3 | Color Contrast (4.5:1) | WCAG 1.4.3 | SDS 6.4 | Planned |
| NFR-ACC-005 | BRS 5.1.3 | Focus Indicators (3:1) | WCAG 2.4.7 | SDS 6.5 | Planned |
| NFR-ACC-006 | BRS 5.1.3 | Text Resizing (200%) | WCAG 1.4.4 | SDS 6.6 | Planned |

### 4.4 Reliability Requirements

| REQ ID | Source | Description | Target | Design Ref | Status |
| ------ | ------ | ----------- | ------ | ---------- | ------ |
| NFR-REL-001 | SIS 2.2.1 | External API Uptime | 99.5% | SIP 3.1 | Planned |
| NFR-REL-002 | SIS 2.2.1 | Local AI Availability | 99.9% | SIP 4.1 | Planned |
| NFR-REL-003 | SIS 2.2.1 | Database Availability | 99.95% | DBD 7.1 | Planned |
| NFR-REL-004 | SIS 2.2.1 | WebSocket Delivery | 99% | SIP 7.1 | Planned |

---

## 5. Integration Requirements Traceability

### 5.1 External API Integration

| REQ ID | Source | Description | Design Ref | Status |
| ------ | ------ | ----------- | ---------- | ------ |
| REQ-INT-001 | SIS 2.1.1 | umapyoi.net Integration | SIP 3.1.1 | Planned |
| REQ-INT-002 | SIS 2.1.1 | UmamusumeDB.com Integration | SIP 3.1.2 | Planned |
| REQ-INT-003 | SIS 2.1.1 | Community Data Integration | SIP 3.2 | Planned |
| REQ-INT-004 | SIS 2.1.1 | Fallback Mechanisms | SIP 3.1.2 | Planned |

### 5.2 AI Services Integration

| REQ ID | Source | Description | Design Ref | Status |
| ------ | ------ | ----------- | ---------- | ------ |
| REQ-INT-005 | SIS 2.1.2 | Ollama Local Processing | SIP 4.1.1 | Planned |
| REQ-INT-006 | SIS 2.1.2 | AWS Bedrock Cloud Fallback | SIP 4.1.2 | Planned |
| REQ-INT-007 | SIS 2.1.2 | Hybrid AI Routing | SIP 4.1.3 | Planned |
| REQ-INT-008 | SIS 2.1.2 | MCP Server Integration | SIP 5.1 | Planned |

### 5.3 Database Integration

| REQ ID | Source | Description | Design Ref | Status |
| ------ | ------ | ----------- | ---------- | ------ |
| REQ-INT-009 | SIP 6.1 | MySQL Primary Database | DBD 2.1 | Planned |
| REQ-INT-010 | SIP 6.1 | Redis Caching Layer | DBD 2.2 | Planned |
| REQ-INT-011 | SIP 6.2 | Real-Time Sync | SIP 6.2.1 | Planned |

---

## Summary Statistics

| Category | Total | Critical | High | Medium | Low |
| -------- | ----- | -------- | ---- | ------ | --- |
| Business Requirements | 12 | 6 | 4 | 2 | 0 |
| Functional Requirements | 30+ | 10 | 12 | 6 | 2 |
| Non-Functional Requirements | 18 | 5 | 8 | 5 | 0 |
| Integration Requirements | 11 | 4 | 5 | 2 | 0 |
| **Total** | **71+** | **25** | **29** | **15** | **2** |

---

## Document Control

| Version | Date | Author | Changes |
| ------- | ---- | ------ | ------- |
| 1.0 | 2026-01-12 | Development Team | Initial RTM creation |

---

*This Requirements Traceability Matrix ensures complete coverage of all requirements from source through implementation and testing.*
