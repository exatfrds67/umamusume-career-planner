# Documentation Directory Structure

## Overview

This directory contains all project documentation organized by category for easy navigation and maintenance. The structure uses numbered prefixes for logical ordering and clear categorization.

---

## Directory Structure

```text
docs/
├── README.md                    # This file - documentation index
├── 00-core-docs/               # Core SDLC specification documents
├── 01-diagrams/                # System diagrams (ERD, data flow, etc.)
├── 01-flows/                   # System flow documentation
├── 01-sequences/               # Sequence diagrams
├── 01-tech-flow/               # Technical flow documentation
├── 01-user-flows/              # User flow diagrams
├── 01-wireframes/              # UI wireframes and mockups
├── 02-prds/                    # Product Requirements Documents
├── 02-specs/                   # Technical specifications
├── accessibility/              # Accessibility compliance docs
├── archive/                    # Archived/deprecated files
├── audit-reports/              # Project audit reports
├── database-documentation/     # Database schema and mapping
├── deployment/                 # Deployment configuration
├── external-api-integration/   # External API integration docs
├── feature-documentation/      # Feature implementation docs
├── future-implements/          # Future feature planning
├── implementation-summaries/   # Task completion summaries
├── mcp-integration/            # MCP server integration
├── neuron/                     # Neuron AI framework docs
├── redis/                      # Redis setup and configuration
├── reference/                  # Reference guides (API, User, Developer)
├── services/                   # Service documentation
├── setup-guides/               # Installation and setup guides
├── testing/                    # Testing documentation
└── verification-reports/       # Verification and status reports
```

---

## Core Documentation

### `/00-core-docs/` - SDLC Specification Documents

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

---

## Design Documentation

### `/01-diagrams/` - System Diagrams

- `data-flow-diagram.md` - Data flow visualization
- `decision-tree-flow-diagrams.md` - Decision tree flows
- `entity-relationship-diagram.md` - Database ERD
- `system-process-flow-diagrams.md` - System process flows
- `user-workflow-diagrams.md` - User workflow visualization

### `/01-flows/` - System Flows

Feature-specific system flow documentation (FLOW-001 through FLOW-007).

### `/01-sequences/` - Sequence Diagrams

System interaction sequence diagrams (SEQ-001 through SEQ-015).

### `/01-tech-flow/` - Technical Flows

Technical flow documentation for each major feature (TECH-FLOW-001 through TECH-FLOW-007).

### `/01-user-flows/` - User Flows

User journey and flow documentation (UF-001 through UF-008).

### `/01-wireframes/` - UI Wireframes

UI wireframes and mockups (WF-001 through WF-012).

---

## Requirements & Specifications

### `/02-prds/` - Product Requirements Documents

- `000_PRDS_INDEX.md` - PRD index
- `PRD-001` through `PRD-007` - Feature PRDs

### `/02-specs/` - Technical Specifications

- `000_SPECS_INDEX.md` - Specs index
- `SPEC-001` through `SPEC-007` - Technical specifications

---

## Reference Documentation

### `/reference/` - Reference Guides

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

### `/setup-guides/` - Installation Guides

| Document | Description |
| -------- | ----------- |
| `DEPLOYMENT_SETUP.md` | Deployment configuration guide |
| `INSTALL_CODE_COVERAGE.md` | Code coverage setup |
| `INSTALL_PHPREDIS_MANUALLY.md` | Manual PHP Redis installation |
| `INSTALL_PHPREDIS_NOW.md` | Quick PHP Redis setup |
| `INSTALL_TESSERACT_OCR.md` | Tesseract OCR installation |
| `TESSERACT_OCR_SETUP_COMPLETE.md` | OCR setup verification |

### `/deployment/` - Deployment Configuration

| Document | Description |
| -------- | ----------- |
| `deployment.md` | Deployment procedures |
| `LAUNCH_CHECKLIST.md` | Pre-launch verification checklist |
| `openapi.yaml` | OpenAPI specification |

### `/redis/` - Redis Configuration

Comprehensive Redis setup documentation including:

- `START_HERE.md` - Redis quick start guide
- `REDIS_SETUP_INSTRUCTIONS.md` - Detailed setup instructions
- `REDIS_WSL_SETUP_GUIDE.md` - WSL-specific setup
- `REDIS_COMMANDS_REFERENCE.md` - Command reference
- `REDIS_TESTING_GUIDE.md` - Testing procedures
- Plus troubleshooting and status documents

---

## Feature Documentation

### `/feature-documentation/` - Feature Implementation

| Document | Description |
| -------- | ----------- |
| `EXTERNAL_API_INTEGRATION.md` | External API integration overview |
| `SKILL_SYSTEM_DOCUMENTATION.md` | Skill system documentation |
| `OCR_UPLOAD_SYSTEM.md` | OCR upload system |
| `OCR_INSTALLATION_GUIDE.md` | OCR installation guide |
| `LAZY_LOADING_IMPLEMENTATION.md` | Lazy loading implementation |
| `SUPPORT_CARDS_IMPLEMENTATION_SUMMARY.md` | Support cards implementation |
| `IMAGE_INTEGRATION_COMPLETE.md` | Image integration status |

### `/external-api-integration/` - External API Integration

| Document | Description |
| -------- | ----------- |
| `IMPLEMENTATION_SUMMARY.md` | Integration implementation summary |
| `CACHE_WARMING_IMPLEMENTATION.md` | Cache warming documentation |
| `mcp-fetch-test-results.md` | MCP fetch test results |
| `umamusumedb-api-verification.md` | UmamusumeDB API verification |
| `secondary-apis-verification.md` | Secondary APIs verification |
| `TASK-1.1.1-SUMMARY.md` | Task 1.1.1 completion summary |
| `TASK_1.1.2_SUMMARY.md` | Task 1.1.2 completion summary |

### `/mcp-integration/` - MCP Server Integration

| Document | Description |
| -------- | ----------- |
| `MCP_SERVER_CONFIGURATION_REFERENCE.md` | MCP configuration reference |
| `MCP_SERVER_RECOMMENDATIONS.md` | MCP server recommendations |
| `mcp-memory-test-results.md` | Memory test results |

### `/neuron/` - Neuron AI Framework

AI framework documentation including agents, providers, RAG, streaming, tools, and workflows.

### `/services/` - Service Documentation

- `CacheManagerService.md` - Cache manager service documentation

---

## Testing & Quality

### `/testing/` - Testing Documentation

| Document | Description |
| -------- | ----------- |
| `PRODUCTION_TESTING_GUIDE.md` | Production testing procedures |
| `CODE_COVERAGE_WARNING_RESOLVED.md` | Code coverage resolution |
| `TEST_ERRORS_RESOLVED.md` | Test error resolutions |
| `character-creation-flow-test.md` | Character creation flow tests |
| `offline-page-accessibility.md` | Offline page accessibility |
| `offline-page-test.md` | Offline page testing |

### `/accessibility/` - Accessibility Compliance

- `focus-management.md` - Focus management documentation

---

## Project Management

### `/audit-reports/` - Audit Reports

- `AUDIT_REPORT_FINAL.md` - Final audit report
- `AUDIT_REPORT_PHASE_1.md` - Phase 1 audit report

### `/implementation-summaries/` - Task Summaries

Task implementation summaries (TASK_3_x through TASK_6_x series).

### `/verification-reports/` - Verification Reports

Documentation verification, status reports, and standards including:

- `FINAL_STATUS_REPORT.md`
- `DOCUMENTATION_COMPLETION_SUMMARY.md`
- `EXECUTIVE_SUMMARY_DOCUMENTATION_COMPLETE.md`
- Various gap analysis and verification documents

### `/future-implements/` - Future Planning

- `comprehensive_future_features.md`
- `remaining_5_percent_analysis.md`
- `umamusume_missing_features_analysis.md`

---

## Database Documentation

### `/database-documentation/`

| Document | Description |
| -------- | ----------- |
| `DATABASE_REQUIREMENT_MAPPING_TABLE.md` | Requirements to database mapping |
| `DATABASE_SCHEMA_ALIGNMENT_VERIFICATION.md` | Schema alignment verification |
| `UPDATED_ENTITY_RELATIONSHIP_DIAGRAM.md` | Current ERD |

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

### For DevOps

- Deployment: `/setup-guides/DEPLOYMENT_SETUP.md`
- Launch: `/deployment/LAUNCH_CHECKLIST.md`
- Monitoring: `/reference/MONITORING_AND_LOGGING.md`
- Testing: `/testing/PRODUCTION_TESTING_GUIDE.md`
- Redis: `/redis/START_HERE.md`

### For Project Management

- Requirements: `/00-core-docs/003_SRS_Software_Requirement_Specifications.md`
- Traceability: `/00-core-docs/000_REQUIREMENTS_TRACEABILITY_MATRIX.md`
- Status: `/verification-reports/FINAL_STATUS_REPORT.md`

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

---

## Version

**Last Updated**: January 21, 2026  
**Organization Version**: 3.0
