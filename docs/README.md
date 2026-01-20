# Documentation Directory Structure

## Overview

This directory contains all project documentation organized by category for easy navigation and maintenance.

---

## Core Specification Documents (Root Level)

These documents form the foundation of the project and should remain in the root `docs/` directory:

### Master Index and Reference Documents (000 Series)

- `000_DOCUMENT_INDEX.md` - Master index of all documentation
- `000_IMPLEMENTATION_VERIFICATION_MATRIX.md` - Implementation verification tracking
- `000_MASTER_GLOSSARY.md` - Project terminology and definitions
- `000_REQUIREMENTS_TRACEABILITY_MATRIX.md` - Requirements tracking

### Core Specification Documents (001-017 Series)

- `001_SDP_Software_Development_Plan.md` - Software development plan
- `002_BRS_Business_Requirements_Specifications.md` - Business requirements
- `003_SRS_Software_Requirement_Specifications.md` - Software requirements
- `004_SDS_Software_Design_Specifications.md` - Software design
- `005_DMP_Data_Migration_Plan.md` - Data migration plan
- `006_DMS_Data_Migration_Specifications.md` - Data migration specifications
- `007_SIP_Software_Integration_Plan.md` - Integration plan
- `008_SIS_Software_Integration_Specifications.md` - Integration specifications
- `009_DBD_Database_Documentation.md` - Database documentation
- `010_SCD_Source_Code_Documentation.md` - Source code documentation
- `017_SUM_Software_User_Manual.md` - Software user manual

### Task 6.3 Deliverables (Root Level)

- `USER_GUIDE.md` - Comprehensive user guide
- `FAQ.md` - Frequently asked questions
- `DEVELOPER_GUIDE.md` - Developer documentation
- `API_REFERENCE.md` - API documentation
- `DEPLOYMENT_SETUP.md` - Deployment configuration guide
- `MONITORING_AND_LOGGING.md` - Monitoring and logging guide
- `LAUNCH_CHECKLIST.md` - Pre-launch verification checklist
- `PRODUCTION_TESTING_GUIDE.md` - Production testing procedures

---

## Organized Subdirectories

### `/accessibility/`

Accessibility compliance documentation and testing guides.

### `/archive/`

Archived or deprecated documentation files.

### `/audit-reports/`

Project audit reports and assessments.

- `AUDIT_REPORT_FINAL.md`
- `AUDIT_REPORT_PHASE_1.md`

### `/database-documentation/`

Database schema, alignment, and mapping documentation.

- `DATABASE_REQUIREMENT_MAPPING_TABLE.md`
- `DATABASE_SCHEMA_ALIGNMENT_VERIFICATION.md`
- `UPDATED_ENTITY_RELATIONSHIP_DIAGRAM.md`

### `/deployment/`

Deployment-related documentation and configuration files.

- `openapi.yaml` - OpenAPI specification

### `/diagrams/`

System diagrams including data flow, ERD, and process flows.

- `data-flow-diagram.md`
- `decision-tree-flow-diagrams.md`
- `entity-relationship-diagram.md`
- `system-process-flow-diagrams.md`
- `user-workflow-diagrams.md`

### `/feature-documentation/`

Feature-specific implementation documentation.

- `BEFORE_AFTER_LAZY_LOADING.md`
- `EXTERNAL_API_INTEGRATION.md`
- `IMAGE_INTEGRATION_COMPLETE.md`
- `LAZY_LOADING_IMPLEMENTATION.md`
- `LAZY_LOADING_SUMMARY.md`
- `OCR_INSTALLATION_GUIDE.md`
- `OCR_UPLOAD_SYSTEM.md`
- `SKILL_SYSTEM_DOCUMENTATION.md`
- `SUPPORT_CARDS_IMAGE_STATUS.md`
- `SUPPORT_CARDS_IMPLEMENTATION_SUMMARY.md`
- `TESTING_LAZY_LOADING.md`

### `/flows/`

System flow documentation for major features.

- `FLOW-001_Character_Management_System.md`
- `FLOW-002_Training_Optimization_System.md`
- `FLOW-003_Race_Strategy_System.md`
- `FLOW-004_Skill_Management_System.md`
- `FLOW-005_Support_Card_Management_System.md`
- `FLOW-006_AI_Advisory_System.md`
- `FLOW-007_External_Integration_System.md`

### `/future-implements/`

Future feature planning and analysis.

- `comprehensive_future_features.md`
- `remaining_5_percent_analysis.md`
- `umamusume_missing_features_analysis.md`

### `/implementation-summaries/`

Task implementation summaries and completion reports.

- `TASK_3_1_3_IMPLEMENTATION_SUMMARY.md`
- `TASK_3_1_5_IMPLEMENTATION_SUMMARY.md`
- `TASK_3_2_1_IMPLEMENTATION_SUMMARY.md`
- `TASK_3_2_2_IMPLEMENTATION_SUMMARY.md`
- `TASK_3_2_3_IMPLEMENTATION_SUMMARY.md`
- `TASK_3_2_5_IMPLEMENTATION_SUMMARY.md`
- `TASK_4_1_1_MCP_INTEGRATION_SUMMARY.md`
- `TASK_4_1_2_HYBRID_AI_IMPLEMENTATION_SUMMARY.md`
- `TASK_4_1_3_MCP_SUBAGENT_SYSTEM_SUMMARY.md`
- `TASK_4_1_4_MCP_TOOL_INTEGRATION_SUMMARY.md`
- `TASK_4_1_5_AI_DASHBOARD_SUMMARY.md`
- `TASK_4_2_1_BEDROCK_AGENTCORE_SUMMARY.md`
- `TASK_4_2_2_AGENT_ORCHESTRATION_SUMMARY.md`
- `TASK_4_3_2_IMPLEMENTATION_SUMMARY.md`
- `TASK_4_3_3_IMPLEMENTATION_SUMMARY.md`
- `TASK_4_3_4_IMPLEMENTATION_SUMMARY.md`
- `TASK_4_3_5_IMPLEMENTATION_SUMMARY.md`
- `TASK_4_4_1_IMPLEMENTATION_SUMMARY.md`
- `TASK_4_4_2_IMPLEMENTATION_SUMMARY.md`
- `TASK_4_4_3_IMPLEMENTATION_SUMMARY.md`
- `TASK_4_4_4_IMPLEMENTATION_SUMMARY.md`
- `TASK_4_4_5_IMPLEMENTATION_SUMMARY.md`
- `TASK_5_1_1_COMPLETION_SUMMARY.md`
- `TASK_6_3_COMPLETION_SUMMARY.md`

### `/mcp-integration/`

MCP (Model Context Protocol) server integration documentation.

- `MCP_SERVER_CONFIGURATION_REFERENCE.md`
- `MCP_SERVER_RECOMMENDATIONS.md`
- `mcp-memory-test-results.md`

### `/neuron/`

Neuron AI framework documentation.

- `agents.md`
- `ai-providers.md`
- `installation.md`
- `rag.md`
- `README.md`
- `streaming.md`
- `structured-output.md`
- `tools.md`
- `workflows.md`

### `/prds/`

Product Requirements Documents.

- `000_PRDS_INDEX.md`
- `PRD-001_Character_Management.md`
- `PRD-002_Training_Optimization.md`
- `PRD-003_Race_Strategy.md`
- `PRD-004_Skill_Management.md`
- `PRD-005_Support_Card_Management.md`
- `PRD-006_AI_Advisory.md`
- `PRD-007_External_Integration.md`

### `/sequences/`

Sequence diagrams for system interactions.

- `000_SEQUENCE_DIAGRAMS_INDEX.md`
- `SEQ-001_Character_Creation_Sequence.md`
- `SEQ-002_Training_Block_Resolution.md`
- `SEQ-003_Skill_Acquisition_and_Upgrade.md`
- `SEQ-004_Race_Registration_and_Outcome.md`
- `SEQ-005_Support_Card_Upgrade.md`
- `SEQ-006_AI_Advice_Generation.md`
- `SEQ-007_External_Data_Sync.md`
- `SEQ-008_Notification_Delivery.md`
- `SEQ-009_User_Profile_Update.md`
- `SEQ-010_Inventory_Transaction.md`
- `SEQ-011_Telemetry_Event_Capture.md`
- `SEQ-012_Run_Snapshot_and_Restore.md`
- `SEQ-013_Achievement_Unlock.md`
- `SEQ-014_Error_Reporting_and_Retry.md`
- `SEQ-015_Data_Migration_Snapshot_to_Live.md`

### `/specs/`

Technical specifications for major features.

- `000_SPECS_INDEX.md`
- `SPEC-001_Character_Management_Technical.md`
- `SPEC-002_Training_Optimization_Technical.md`
- `SPEC-003_Race_Strategy_Technical.md`
- `SPEC-004_Skill_Management_Technical.md`
- `SPEC-005_Support_Card_Management_Technical.md`
- `SPEC-006_AI_Advisory_Technical.md`
- `SPEC-007_External_Integration_Technical.md`

### `/tech-flow/`

Technical flow documentation.

- `000_TECH_FLOW_INDEX.md`
- `TECH-FLOW-001_Character_Management_Flow.md`
- `TECH-FLOW-002_Training_Optimization_Flow.md`
- `TECH-FLOW-003_Race_Strategy_Flow.md`
- `TECH-FLOW-004_Skill_Management_Flow.md`
- `TECH-FLOW-005_Support_Card_Management_Flow.md`
- `TECH-FLOW-006_AI_Advisory_Flow.md`
- `TECH-FLOW-007_External_Integration_Flow.md`

### `/testing/`

Testing documentation and test results.

- `character-creation-flow-test.md`
- `offline-page-accessibility.md`
- `offline-page-test.md`

### `/user-flows/`

User flow diagrams and documentation.

- `000_USER_FLOW_DIAGRAMS_INDEX.md`
- `UF-001_Onboarding_Flow.md`
- `UF-002_Career_Setup_Flow.md`
- `UF-003_Training_Day_Flow.md`
- `UF-004_Race_Day_Flow.md`
- `UF-005_Skill_Management_Flow.md`
- `UF-006_Support_Deck_Building_Flow.md`
- `UF-007_AI_Advisor_Journey.md`
- `UF-008_OCR_and_Data_Import_Flow.md`

### `/verification-reports/`

Documentation verification, status reports, and standards.

- `DOCUMENTATION_ARTIFACTS_DIRECTORY_STRUCTURE.md`
- `DOCUMENTATION_COMPLETION_SUMMARY.md`
- `DOCUMENTATION_DISCREPANCY_REPORT.md`
- `DOCUMENTATION_GAP_ANALYSIS.md`
- `DOCUMENTATION_MANIFEST_AND_DELIVERY_CHECKLIST.md`
- `EXECUTIVE_SUMMARY_DOCUMENTATION_COMPLETE.md`
- `FINAL_STATUS_REPORT.md`
- `OFFICIAL_DOCUMENTATION_UPDATES.md`
- `requirements.md`
- `tasks.md`
- `TECHNOLOGY_VERIFICATION_TABLE.md`
- `TEMPLATE_STANDARD.md`

### `/wireframes/`

UI wireframes and mockups.

- `000_WIREFRAMES_INDEX.md`
- `WF-001_Dashboard_Overview.md`
- `WF-002_Character_Creation_Wizard.md`
- `WF-003_Character_Detail_Management.md`
- `WF-004_Training_Selection_Interface.md`
- `WF-005_Training_Result_Screen.md`
- `WF-006_Race_Calendar_View.md`
- `WF-007_Race_Preparation_Screen.md`
- `WF-008_Skill_Shop_Interface.md`
- `WF-009_Skill_Loadout_Manager.md`
- `WF-010_Support_Card_Collection.md`
- `WF-011_Support_Deck_Builder.md`
- `WF-012_AI_Advisor_Interface.md`

---

## Quick Reference

### For Users

- Start with: `USER_GUIDE.md`
- Questions: `FAQ.md`

### For Developers

- Start with: `DEVELOPER_GUIDE.md`
- API: `API_REFERENCE.md`
- Database: `/database-documentation/`
- Features: `/feature-documentation/`

### For DevOps

- Deployment: `DEPLOYMENT_SETUP.md`
- Monitoring: `MONITORING_AND_LOGGING.md`
- Testing: `PRODUCTION_TESTING_GUIDE.md`
- Launch: `LAUNCH_CHECKLIST.md`

### For Project Management

- Requirements: `003_SRS_Software_Requirement_Specifications.md`
- Traceability: `000_REQUIREMENTS_TRACEABILITY_MATRIX.md`
- Status: `/verification-reports/FINAL_STATUS_REPORT.md`

---

## Document Naming Conventions

- **000 Series**: Master reference documents
- **001-017 Series**: Core specification documents
- **Uppercase with underscores**: General documentation (e.g., `USER_GUIDE.md`)
- **Prefixed with category**: Organized documents (e.g., `TASK_`, `FLOW-`, `PRD-`)
- **Lowercase with hyphens**: Subdirectory files (e.g., `data-flow-diagram.md`)

---

## Maintenance

- Core specification documents (000-017) should not be moved or renamed
- Task 6.3 deliverables should remain in root for easy access
- Implementation summaries go to `/implementation-summaries/`
- Feature-specific docs go to `/feature-documentation/`
- Verification and status reports go to `/verification-reports/`

---

## Version

**Last Updated**: January 20, 2026  
**Organization Version**: 2.0
