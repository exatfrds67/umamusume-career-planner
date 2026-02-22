# Documentation Directory Structure

## Overview

This directory contains all project documentation for the **Uma Musume Career Planner** application, organized by category for easy navigation and maintenance. The structure uses numbered prefixes for logical ordering and clear categorization.

**Tech Stack**: Laravel 12, PHP 8.2+, Livewire 4, Alpine.js 3, TailwindCSS v4, Pest v4, PHPUnit v12

---

## Directory Structure

```
docs/
├── README.md                        # This file - documentation index
├── admin-panel-quick-start.md       # Admin panel setup guide
├── ai-training-advisory-api.md      # AI advisory API documentation
├── authorization-audit-2026-01-29.md # Authorization audit report
├── authorization-fix-summary.md     # Authorization fix quick reference
├── breadcrumb-implementation-guide.md   # Breadcrumb implementation tracking
├── breadcrumb-implementation-summary.md # Breadcrumb implementation summary
├── training-prediction-fixes-summary.md # Training prediction fixes report
│
├── 00-core-docs/               # Core SDLC specification documents (28 files)
├── 01-diagrams/                # System diagrams (5 files)
├── 01-flows/                   # System flow documentation (7 files)
├── 01-sequences/               # Sequence diagrams (17 files)
├── 01-tech-flow/               # Technical flow documentation (8 files)
├── 01-user-flows/              # User flow diagrams (9 files)
├── 01-wireframes/              # UI wireframes and mockups (13 files)
├── 02-prds/                    # Product Requirements Documents (8 files)
├── 02-specs/                   # Technical specifications (9 files)
├── accessibility/              # Accessibility compliance docs (1 file)
├── archive/                    # Archived/deprecated files (1 file)
├── audit-reports/              # Project audit reports (2 files)
├── bedrock/                    # AWS Bedrock configuration (2 files)
├── components/                 # UI component documentation (1 file)
├── database-documentation/     # Database schema and mapping (3 files)
├── deployment/                 # Deployment configuration (3 files)
├── design/                     # Game alignment & UI design (13 files)
├── external-api-integration/   # External API integration docs (35 files)
├── feature-documentation/      # Feature implementation docs (12 files)
├── fixes/                      # Bug fix reports (7 files)
├── frontend-development/       # Frontend UI/UX documentation (15 files)
├── future-implements/          # Future feature planning (3 files)
├── implementation/             # Phase implementation tracking (44 files)
├── implementation-summaries/   # Task completion summaries (123 files)
├── larastan/                   # Larastan/PHPStan analysis docs (14 files)
├── mcp-integration/            # MCP server integration (3 files)
├── neuron/                     # Neuron AI framework docs (18 files)
├── performance/                # Performance optimization docs (1 file)
├── redis/                      # Redis setup and configuration (25 files)
├── reference/                  # Reference guides (7 files)
├── research/                   # Game mechanics research (6 files)
├── services/                   # Service documentation (1 file)
├── setup-guides/               # Installation and setup guides (8 files)
├── testing/                    # Testing documentation (11 files)
└── verification-reports/       # Verification and status reports (12 files)
```

---

## Core Documentation

### `/00-core-docs/` - SDLC Specification Documents (28 files)

Master reference and core specification documents:

| Document | Description |
| -------- | ----------- |
| `000_DOCUMENT_INDEX.md` | Master index of all documentation |
| `000_IMPLEMENTATION_VERIFICATION_MATRIX.md` | Implementation verification tracking |
| `000_MASTER_GLOSSARY.md` | Project terminology and definitions |
| `000_REQUIREMENTS_TRACEABILITY_MATRIX.md` | Requirements tracking |
| `001_SDP_Software_Development_Plan.md` | Software development plan |
| `002_BRS_Business_Requirements_Specifications.md` | Business requirements |
| `003_SRS_Software_Requirement_Specifications.md` | Software requirements |
| `004_SDS_Software_Design_Specifications.md` | Software design |
| `005_DMP_Data_Migration_Plan.md` | Data migration plan |
| `006_DMS_Data_Migration_Specifications.md` | Data migration specifications |
| `007_SIP_Software_Integration_Plan.md` | Integration plan |
| `008_SIS_Software_Integration_Specifications.md` | Integration specifications |
| `009_DBD_Database_Documentation.md` | Database documentation |
| `010_SCD_Source_Code_Documentation.md` | Source code documentation |
| `017_SUM_Software_User_Manual.md` | Software user manual |

Also includes a `ver.2.0.0/` subdirectory with previous version snapshots.

---

## Design Documentation

### `/01-diagrams/` - System Diagrams (5 files)

- `data-flow-diagram.md` - Data flow visualization
- `decision-tree-flow-diagrams.md` - Decision tree flows
- `entity-relationship-diagram.md` - Database ERD
- `system-process-flow-diagrams.md` - System process flows
- `user-workflow-diagrams.md` - User workflow visualization

### `/01-flows/` - System Flows (7 files)

Feature-specific system flow documentation (FLOW-001 through FLOW-007).

### `/01-sequences/` - Sequence Diagrams (17 files)

System interaction sequence diagrams (SEQ-001 through SEQ-016) plus index.

### `/01-tech-flow/` - Technical Flows (8 files)

Technical flow documentation for each major feature (TECH-FLOW-001 through TECH-FLOW-007) plus index.

### `/01-user-flows/` - User Flows (9 files)

User journey and flow documentation (UF-001 through UF-008) plus index.

### `/01-wireframes/` - UI Wireframes (13 files)

UI wireframes and mockups (WF-001 through WF-012) plus index.

### `/design/` - Game Alignment & UI Design (13 files)

Game UI alignment strategy, component inventory, data flow mapping, prototype plans, and implementation documentation.

---

## Requirements & Specifications

### `/02-prds/` - Product Requirements Documents (8 files)

- `000_PRDS_INDEX.md` - PRD index
- `PRD-001` through `PRD-007` - Feature PRDs

### `/02-specs/` - Technical Specifications (9 files)

- `000_SPECS_INDEX.md` - Specs index
- `SPEC-001` through `SPEC-008` - Technical specifications (includes Performance Monitoring)

---

## Reference Documentation

### `/reference/` - Reference Guides (7 files)

| Document | Description |
| -------- | ----------- |
| `USER_GUIDE.md` | Comprehensive user guide |
| `DEVELOPER_GUIDE.md` | Developer documentation |
| `API_REFERENCE.md` | API documentation |
| `FAQ.md` | Frequently asked questions |
| `MONITORING_AND_LOGGING.md` | Monitoring and logging guide |
| `ai-coding-assistant-best-practices.md` | AI coding guidelines |
| `external-api-service.md` | External API service reference |

---

## Setup & Deployment

### `/setup-guides/` - Installation Guides (8 files)

| Document | Description |
| -------- | ----------- |
| `DEPLOYMENT_SETUP.md` | Deployment configuration guide |
| `INSTALL_CODE_COVERAGE.md` | Code coverage setup |
| `INSTALL_PHPREDIS_MANUALLY.md` | Manual PHP Redis installation |
| `INSTALL_PHPREDIS_NOW.md` | Quick PHP Redis setup |
| `INSTALL_TESSERACT_OCR.md` | Tesseract OCR installation |
| `TESSERACT_OCR_SETUP_COMPLETE.md` | OCR setup verification |
| `pest-browser-setup.md` | Pest browser testing setup |
| `WSL_REDIS_SETUP.md` | WSL Redis setup guide |

### `/deployment/` - Deployment Configuration (3 files)

| Document | Description |
| -------- | ----------- |
| `deployment.md` | Deployment procedures |
| `LAUNCH_CHECKLIST.md` | Pre-launch verification checklist |
| `openapi.yaml` | OpenAPI specification |

### `/redis/` - Redis Configuration (25 files)

Comprehensive Redis setup documentation including:

- `START_HERE.md` - Redis quick start guide
- `REDIS_SETUP_INSTRUCTIONS.md` - Detailed setup instructions
- `REDIS_WSL_SETUP_GUIDE.md` - WSL-specific setup
- `REDIS_COMMANDS_REFERENCE.md` - Command reference
- `REDIS_TESTING_GUIDE.md` - Testing procedures
- `REDIS_COMPLETE_GUIDE.md` - Comprehensive guide
- Plus troubleshooting, diagnosis, and status documents

### `/bedrock/` - AWS Bedrock Configuration (2 files)

- `BEDROCK_STATUS.md` - Bedrock integration status
- `BEDROCK_QUICK_CHECK.txt` - Quick verification checklist

---

## Feature Documentation

### `/feature-documentation/` - Feature Implementation (12 files)

| Document | Description |
| -------- | ----------- |
| `EXTERNAL_API_INTEGRATION.md` | External API integration overview |
| `SKILL_SYSTEM_DOCUMENTATION.md` | Skill system documentation |
| `OCR_UPLOAD_SYSTEM.md` | OCR upload system |
| `OCR_INSTALLATION_GUIDE.md` | OCR installation guide |
| `LAZY_LOADING_IMPLEMENTATION.md` | Lazy loading implementation |
| `SUPPORT_CARDS_IMPLEMENTATION_SUMMARY.md` | Support cards implementation |
| `SUPPORT_CARDS_IMAGE_STATUS.md` | Support card image status |
| `IMAGE_INTEGRATION_COMPLETE.md` | Image integration status |
| `BEFORE_AFTER_LAZY_LOADING.md` | Lazy loading before/after comparison |
| `LAZY_LOADING_SUMMARY.md` | Lazy loading summary |
| `TESTING_LAZY_LOADING.md` | Lazy loading test documentation |
| `external-api-performance-optimization.md` | External API performance optimization |

### `/external-api-integration/` - External API Integration (35 files)

Comprehensive external API integration documentation including implementation summaries, testing guides, browser testing reports, cache warming, pagination analysis, performance testing, and API verification reports.

### `/mcp-integration/` - MCP Server Integration (3 files)

| Document | Description |
| -------- | ----------- |
| `MCP_SERVER_CONFIGURATION_REFERENCE.md` | MCP configuration reference |
| `MCP_SERVER_RECOMMENDATIONS.md` | MCP server recommendations |
| `mcp-memory-test-results.md` | Memory test results |

### `/neuron/` - Neuron AI Framework (18 files)

AI framework documentation including:

- `README.md` - Neuron overview
- `agents.md` - Agent configuration
- `ai-providers.md` - AI provider setup
- `installation.md` - Installation guide
- `integration-guide.md` - Integration guide
- `mcp-connector-guide.md` - MCP connector guide
- `mcp-tool-integration.md` - MCP tool integration
- `rag.md` - RAG implementation
- `streaming.md` - Streaming support
- `structured-output.md` - Structured output
- `tools.md` - Tool definitions
- `workflows.md` - Workflow configuration
- `recommendation-parser.md` - Recommendation parsing
- Plus Larastan fixes, performance reports, and research documents

### `/components/` - UI Component Documentation (1 file)

- `ai/critical-alert-badge.md` - Critical alert badge component docs

### `/services/` - Service Documentation (1 file)

- `CacheManagerService.md` - Cache manager service documentation

### `/performance/` - Performance Optimization (1 file)

- `critical-detection-optimization.md` - Critical detection performance optimization

---

## Testing & Quality

### `/testing/` - Testing Documentation (11 files)

| Document | Description |
| -------- | ----------- |
| `PRODUCTION_TESTING_GUIDE.md` | Production testing procedures |
| `API_TESTING_REPORT.md` | API testing report |
| `CODE_COVERAGE_WARNING_RESOLVED.md` | Code coverage resolution |
| `TEST_ERRORS_RESOLVED.md` | Test error resolutions |
| `character-creation-flow-test.md` | Character creation flow tests |
| `external-api-retry-test-checklist.md` | External API retry test checklist |
| `external-api-retry-test-results.md` | External API retry test results |
| `external-data-browse-performance-results.md` | External data browse performance |
| `external-data-browser-manual-testing-guide.md` | Manual testing guide |
| `offline-page-accessibility.md` | Offline page accessibility |
| `offline-page-test.md` | Offline page testing |

### `/accessibility/` - Accessibility Compliance (1 file)

- `focus-management.md` - Focus management documentation

### `/larastan/` - Static Analysis Documentation (14 files)

Larastan/PHPStan level 9 analysis documentation including fix plans, strategies, batch summaries, progress reports, and model fixes.

---

## Project Management

### `/audit-reports/` - Audit Reports (2 files)

- `AUDIT_REPORT_FINAL.md` - Final audit report
- `AUDIT_REPORT_PHASE_1.md` - Phase 1 audit report

### `/implementation/` - Phase Implementation (44 files)

Phase-by-phase implementation tracking (Phases 1-7), game mechanics alignment, UI component integration, and external API frontend integration documentation.

### `/implementation-summaries/` - Task Summaries (123 files)

Detailed task implementation summaries covering all project tasks across phases, including admin panel, AI advisory, blade refactoring, browser tests, character features, MCP integration, Neuron RAG, Redis setup, skills page, training predictions, and UI enhancements.

### `/verification-reports/` - Verification Reports (12 files)

Documentation verification, status reports, and standards including:

- `FINAL_STATUS_REPORT.md` - Final project status report
- `DOCUMENTATION_COMPLETION_SUMMARY.md` - Documentation completion summary
- `EXECUTIVE_SUMMARY_DOCUMENTATION_COMPLETE.md` - Executive summary
- `DOCUMENTATION_GAP_ANALYSIS.md` - Gap analysis
- `DOCUMENTATION_MANIFEST_AND_DELIVERY_CHECKLIST.md` - Delivery checklist
- `TECHNOLOGY_VERIFICATION_TABLE.md` - Technology verification
- `TEMPLATE_STANDARD.md` - Documentation template standard
- Plus discrepancy reports and requirements/tasks tracking

### `/future-implements/` - Future Planning (3 files)

- `comprehensive_future_features.md` - Comprehensive future features list
- `remaining_5_percent_analysis.md` - Remaining work analysis
- `umamusume_missing_features_analysis.md` - Missing features analysis

### `/fixes/` - Bug Fix Reports (7 files)

Historical bug fix reports including form field fixes, HTTP layer fixes, Larastan view component fixes, race factory enum fixes, test fixes, and training prediction console error fixes.

### `/frontend-development/` - Frontend UI/UX (15 files)

Frontend development documentation including sidebar implementation, UI/UX fix summaries, phase planning, and browser test results.

### `/research/` - Game Mechanics Research (6 files)

Game mechanics research including screenshot analysis, visual interaction patterns, URA Finale mechanics guides, and game mechanics research reports.

---

## Database Documentation

### `/database-documentation/` (3 files)

| Document | Description |
| -------- | ----------- |
| `DATABASE_REQUIREMENT_MAPPING_TABLE.md` | Requirements to database mapping |
| `DATABASE_SCHEMA_ALIGNMENT_VERIFICATION.md` | Schema alignment verification |
| `UPDATED_ENTITY_RELATIONSHIP_DIAGRAM.md` | Current ERD |

---

## Root-Level Documentation Files

| Document | Description |
| -------- | ----------- |
| `admin-panel-quick-start.md` | Admin panel setup and access guide |
| `ai-training-advisory-api.md` | AI Training Advisory System API documentation |
| `authorization-audit-2026-01-29.md` | Comprehensive authorization audit report |
| `authorization-fix-summary.md` | Authorization fix quick reference |
| `breadcrumb-implementation-guide.md` | Breadcrumb navigation implementation tracking |
| `breadcrumb-implementation-summary.md` | Breadcrumb implementation summary and status |
| `training-prediction-fixes-summary.md` | Training prediction system fixes report |

---

## Quick Reference

### For Users

- Start with: `/reference/USER_GUIDE.md`
- Questions: `/reference/FAQ.md`

### For Developers

- Start with: `/reference/DEVELOPER_GUIDE.md`
- API: `/reference/API_REFERENCE.md`
- Database: `/database-documentation/`
- Features: `/feature-documentation/`
- Neuron AI: `/neuron/README.md`

### For DevOps

- Deployment: `/setup-guides/DEPLOYMENT_SETUP.md`
- Launch: `/deployment/LAUNCH_CHECKLIST.md`
- Monitoring: `/reference/MONITORING_AND_LOGGING.md`
- Testing: `/testing/PRODUCTION_TESTING_GUIDE.md`
- Redis: `/redis/START_HERE.md`
- AWS Bedrock: `/bedrock/BEDROCK_STATUS.md`

### For Project Management

- Requirements: `/00-core-docs/003_SRS_Software_Requirement_Specifications.md`
- Traceability: `/00-core-docs/000_REQUIREMENTS_TRACEABILITY_MATRIX.md`
- Status: `/verification-reports/FINAL_STATUS_REPORT.md`
- Future: `/future-implements/comprehensive_future_features.md`

---

## Document Naming Conventions

| Pattern | Usage |
| ------- | ----- |
| `000-017` prefix | Core SDLC specification documents |
| `FLOW-XXX` | System flow documents |
| `SEQ-XXX` | Sequence diagrams |
| `TECH-FLOW-XXX` | Technical flow documents |
| `UF-XXX` | User flow documents |
| `WF-XXX` | Wireframe documents |
| `PRD-XXX` | Product requirement documents |
| `SPEC-XXX` | Technical specifications |
| `TASK_X_X_X` | Task implementation summaries |
| `UPPERCASE_WITH_UNDERSCORES` | General documentation |
| `lowercase-with-hyphens` | Subdirectory files |

---

## Maintenance Guidelines

1. **Core documents** (`/00-core-docs/`) should not be moved or renamed
2. **New task summaries** go to `/implementation-summaries/`
3. **Feature-specific docs** go to `/feature-documentation/`
4. **Setup/installation guides** go to `/setup-guides/`
5. **Reference materials** go to `/reference/`
6. **Test documentation** goes to `/testing/`
7. **Verification reports** go to `/verification-reports/`
8. **Bug fix reports** go to `/fixes/`
9. **Static analysis docs** go to `/larastan/`
10. **Neuron AI docs** go to `/neuron/`
11. **Frontend UI/UX docs** go to `/frontend-development/`
12. **Game research** goes to `/research/`
13. **Phase implementation tracking** goes to `/implementation/`

---

## Version

**Last Updated**: February 22, 2026  
**Organization Version**: 4.0
