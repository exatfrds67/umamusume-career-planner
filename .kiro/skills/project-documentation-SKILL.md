---
name: project-documentation-structure
description: Complete project documentation structure, PRDs, SPECs, flows, sequences, and technical documentation. Use when navigating documentation, understanding requirements, or implementing features based on specifications.
---

# Project Documentation Structure

## Overview

This skill provides a comprehensive guide to the Uma Musume Career Planner project documentation structure, including all PRDs, SPECs, flows, sequences, and technical documentation.

## Documentation Hierarchy

### Core Documentation (`docs/00-core-docs/`)

#### Primary Documents

1. **000_DOCUMENT_INDEX.md**
   - Master index of all documentation
   - Cross-reference guide
   - Document relationships

2. **000_MASTER_GLOSSARY.md**
   - Canonical terminology
   - Domain-specific terms
   - Technical definitions

3. **000_REQUIREMENTS_TRACEABILITY_MATRIX.md**
   - Requirements to implementation mapping
   - Verification status
   - Coverage analysis

4. **000_IMPLEMENTATION_VERIFICATION_MATRIX.md**
   - Implementation status tracking
   - Test coverage mapping
   - Verification checkpoints

#### Software Development Plan (SDP)

**File**: `001_SDP_Software_Development_Plan.md`

**Purpose**: Overall project planning and methodology

**Key Sections**:

- Project scope and objectives
- Development methodology
- Team structure and roles
- Timeline and milestones
- Risk management
- Quality assurance approach

**When to Reference**:

- Understanding project goals
- Planning new features
- Assessing project scope
- Risk assessment

#### Business Requirements Specifications (BRS)

**File**: `002_BRS_Business_Requirements_Specifications.md`

**Purpose**: High-level business requirements

**Key Sections**:

- Business objectives
- Stakeholder requirements
- Success criteria
- Constraints and assumptions
- User personas
- Business rules

**When to Reference**:

- Understanding business context
- Validating feature alignment
- Stakeholder communication
- Requirements analysis

#### Software Requirement Specifications (SRS)

**File**: `003_SRS_Software_Requirement_Specifications.md`

**Purpose**: Detailed functional and non-functional requirements

**Key Sections**:

- Functional requirements
- Non-functional requirements
- System interfaces
- Performance requirements
- Security requirements
- Accessibility requirements

**When to Reference**:

- Implementing features
- Writing tests
- Performance optimization
- Security implementation

#### Software Design Specifications (SDS)

**File**: `004_SDS_Software_Design_Specifications.md`

**Purpose**: Technical architecture and design

**Key Sections**:

- System architecture
- Component design
- Database design
- API design
- Security architecture
- Performance design

**When to Reference**:

- Architectural decisions
- Component design
- Database schema changes
- API development

#### Database Documentation (DBD)

**File**: `009_DBD_Database_Documentation.md`

**Purpose**: Complete database schema and design

**Key Sections**:

- Entity-relationship diagrams
- Table definitions
- Relationships and constraints
- Indexes and optimization
- Migration strategy
- Data integrity rules

**When to Reference**:

- Database queries
- Schema modifications
- Migration creation
- Performance optimization

#### Source Code Documentation (SCD)

**File**: `010_SCD_Source_Code_Documentation.md`

**Purpose**: Code structure and conventions

**Key Sections**:

- Directory structure
- Coding standards
- Naming conventions
- Documentation standards
- Code review guidelines

**When to Reference**:

- Writing new code
- Code reviews
- Refactoring
- Onboarding

#### Software User Manual (SUM)

**File**: `017_SUM_Software_User_Manual.md`

**Purpose**: End-user documentation

**Key Sections**:

- Getting started
- Feature guides
- Troubleshooting
- FAQ
- Best practices

**When to Reference**:

- User support
- Feature documentation
- Training materials
- Help content

### Product Requirements Documents (`docs/02-prds/`)

#### PRD-001: Character Management

**File**: `PRD-001_Character_Management.md`

**Scope**: Character creation, stats, aptitudes, growth rates

**Key Features**:

- Character creation wizard
- Stat management (Speed, Stamina, Power, Guts, Wit)
- Aptitude grades (G-SS)
- Growth rate configuration
- Character profiles

**Related**:

- SPEC-001, FLOW-001, TECH-FLOW-001
- SEQ-001 (Character Creation)

#### PRD-002: Training Optimization

**File**: `PRD-002_Training_Optimization.md`

**Scope**: Training system, predictions, optimization

**Key Features**:

- Training facility selection
- Stat gain predictions
- Support card integration
- Training history tracking
- Optimization recommendations

**Related**:

- SPEC-002, FLOW-002, TECH-FLOW-002
- SEQ-002 (Training Block Resolution)

#### PRD-003: Race Strategy

**File**: `PRD-003_Race_Strategy.md`

**Scope**: Race planning, strategy, outcomes

**Key Features**:

- Race calendar
- Strategy recommendations
- Weather/track conditions
- Performance predictions
- Race results tracking

**Related**:

- SPEC-003, FLOW-003, TECH-FLOW-003
- SEQ-004 (Race Registration and Outcome)

#### PRD-004: Skill Management

**File**: `PRD-004_Skill_Management.md`

**Scope**: Skill acquisition, SP management, evolution

**Key Features**:

- Skill catalog (150+ skills)
- SP budget management
- Hint system (5 levels)
- Skill evolution paths
- Loadout management

**Related**:

- SPEC-004, FLOW-004, TECH-FLOW-004
- SEQ-003 (Skill Acquisition and Upgrade)

#### PRD-005: Support Card Management

**File**: `PRD-005_Support_Card_Management.md`

**Scope**: Support cards, deck building, bonuses

**Key Features**:

- Card collection (200+ cards)
- Deck builder (6 slots)
- Bond level tracking
- Limit break system
- Meta rankings

**Related**:

- SPEC-005, FLOW-005, TECH-FLOW-005
- SEQ-005 (Support Card Upgrade)
- SEQ-016 (Support Deck Configuration)

#### PRD-006: AI Advisory

**File**: `PRD-006_AI_Advisory.md`

**Scope**: AI recommendations, hybrid system

**Key Features**:

- Local AI (Ollama)
- Cloud fallback (AWS Bedrock)
- Training recommendations
- Race strategy advice
- Skill suggestions

**Related**:

- SPEC-006, FLOW-006, TECH-FLOW-006
- SEQ-006 (AI Advice Generation)

#### PRD-007: External Integration

**File**: `PRD-007_External_Integration.md`

**Scope**: External APIs, data sync, OCR

**Key Features**:

- umapyoi.net integration
- UmamusumeDB.com integration
- OCR screenshot processing
- Data synchronization
- Real-time updates

**Related**:

- SPEC-007, FLOW-007, TECH-FLOW-007
- SEQ-007 (External Data Sync)

### Technical Specifications (`docs/02-specs/`)

Each SPEC document provides detailed technical requirements for the corresponding PRD:

- **SPEC-001**: Character Management Technical
- **SPEC-002**: Training Optimization Technical
- **SPEC-003**: Race Strategy Technical
- **SPEC-004**: Skill Management Technical
- **SPEC-005**: Support Card Management Technical
- **SPEC-006**: AI Advisory Technical
- **SPEC-007**: External Integration Technical
- **SPEC-008**: Performance Monitoring Technical

**When to Reference SPECs**:

- Implementation details
- API contracts
- Data structures
- Algorithm specifications
- Performance requirements

### System Flows (`docs/01-flows/`)

Mermaid diagrams showing system-level flows:

- **FLOW-001**: Character Management System
- **FLOW-002**: Training Optimization System
- **FLOW-003**: Race Strategy System
- **FLOW-004**: Skill Management System
- **FLOW-005**: Support Card Management System
- **FLOW-006**: AI Advisory System
- **FLOW-007**: External Integration System

**When to Reference**:

- Understanding system architecture
- Component interactions
- Data flow analysis
- Integration points

### Sequence Diagrams (`docs/01-sequences/`)

Detailed interaction sequences:

- **SEQ-001**: Character Creation Sequence
- **SEQ-002**: Training Block Resolution
- **SEQ-003**: Skill Acquisition and Upgrade
- **SEQ-004**: Race Registration and Outcome
- **SEQ-005**: Support Card Upgrade
- **SEQ-006**: AI Advice Generation
- **SEQ-007**: External Data Sync
- **SEQ-008**: Notification Delivery
- **SEQ-009**: User Profile Update
- **SEQ-010**: Inventory Transaction
- **SEQ-011**: Telemetry Event Capture
- **SEQ-012**: Run Snapshot and Restore
- **SEQ-013**: Achievement Unlock
- **SEQ-014**: Error Reporting and Retry
- **SEQ-015**: Data Migration Snapshot to Live
- **SEQ-016**: Support Deck Configuration

**When to Reference**:

- Implementing features
- Understanding workflows
- Debugging issues
- Integration testing

### Technical Flows (`docs/01-tech-flow/`)

Technical implementation flows:

- **TECH-FLOW-001**: Character Management Flow
- **TECH-FLOW-002**: Training Optimization Flow
- **TECH-FLOW-003**: Race Strategy Flow
- **TECH-FLOW-004**: Skill Management Flow
- **TECH-FLOW-005**: Support Card Management Flow
- **TECH-FLOW-006**: AI Advisory Flow
- **TECH-FLOW-007**: External Integration Flow

**When to Reference**:

- Implementation planning
- Task breakdown
- Technical decisions
- Code organization

### User Flows (`docs/01-user-flows/`)

User journey documentation:

- **UF-001**: Onboarding Flow
- **UF-002**: Career Setup Flow
- **UF-003**: Training Day Flow
- **UF-004**: Race Day Flow
- **UF-005**: Skill Management Flow
- **UF-006**: Support Deck Building Flow
- **UF-007**: AI Advisor Journey
- **UF-008**: OCR and Data Import Flow

**When to Reference**:

- UX design
- Feature planning
- User testing
- Documentation writing

### Wireframes (`docs/01-wireframes/`)

UI/UX wireframes:

- **WF-001**: Dashboard Overview
- **WF-002**: Character Creation Wizard
- **WF-003**: Character Detail Management
- **WF-004**: Training Selection Interface
- **WF-005**: Training Result Screen
- **WF-006**: Race Calendar View
- **WF-007**: Race Preparation Screen
- **WF-008**: Skill Shop Interface
- **WF-009**: Skill Loadout Manager
- **WF-010**: Support Card Collection
- **WF-011**: Support Deck Builder
- **WF-012**: AI Advisor Interface

**When to Reference**:

- UI implementation
- Component design
- Layout planning
- Accessibility implementation

## Implementation Documentation

### External API Integration (`docs/external-api-integration/`)

**Key Documents**:

- `README.md`: Integration overview
- `API_TESTING_QUICK_REFERENCE.md`: Testing guide
- `IMPLEMENTATION_SUMMARY.md`: Implementation status
- `SKILLS_DATA_IMPLEMENTATION.md`: Skills API integration
- `SUPPORT_CARD_IMPLEMENTATION_ROADMAP.md`: Support cards roadmap

**When to Reference**:

- External API integration
- Data synchronization
- API testing
- Error handling

### Feature Documentation (`docs/feature-documentation/`)

**Key Documents**:

- `SKILL_SYSTEM_DOCUMENTATION.md`: Skill system details
- `OCR_UPLOAD_SYSTEM.md`: OCR implementation
- `EXTERNAL_API_INTEGRATION.md`: API integration guide
- `LAZY_LOADING_IMPLEMENTATION.md`: Performance optimization

**When to Reference**:

- Feature implementation
- Performance optimization
- User documentation
- Testing features

### Testing Documentation (`docs/testing/`)

**Key Documents**:

- `PRODUCTION_TESTING_GUIDE.md`: Testing procedures
- `API_TESTING_REPORT.md`: API test results
- `character-creation-flow-test.md`: Flow testing
- `offline-page-accessibility.md`: Accessibility testing

**When to Reference**:

- Writing tests
- Test planning
- Quality assurance
- Accessibility compliance

### Setup Guides (`docs/setup-guides/`)

**Key Documents**:

- `DEPLOYMENT_SETUP.md`: Deployment procedures
- `WSL_REDIS_SETUP.md`: Redis configuration
- `INSTALL_TESSERACT_OCR.md`: OCR setup
- `INSTALL_CODE_COVERAGE.md`: Coverage tools

**When to Reference**:

- Environment setup
- Deployment
- Tool installation
- Configuration

## Documentation Best Practices

### Finding Documentation

1. **Start with Index**: Check `000_DOCUMENT_INDEX.md`
2. **Use Glossary**: Reference `000_MASTER_GLOSSARY.md` for terms
3. **Follow Traceability**: Use RTM for requirement mapping
4. **Check Sequences**: Review sequence diagrams for workflows

### Reading Documentation

1. **PRD First**: Understand business requirements
2. **SPEC Next**: Review technical specifications
3. **Flow Diagrams**: Visualize system interactions
4. **Sequences**: Understand detailed workflows
5. **Implementation**: Check implementation summaries

### Updating Documentation

1. **Maintain Consistency**: Update related documents
2. **Update Traceability**: Keep RTM current
3. **Version Control**: Document version changes
4. **Cross-Reference**: Update links and references

### Documentation Hierarchy

```
Business Requirements (PRD)
    ↓
Technical Specifications (SPEC)
    ↓
System Flows (FLOW)
    ↓
Technical Flows (TECH-FLOW)
    ↓
Sequence Diagrams (SEQ)
    ↓
Implementation Summaries
```

## Quick Reference

### By Feature Area

**Character Management**:

- PRD-001, SPEC-001, FLOW-001, TECH-FLOW-001, SEQ-001

**Training System**:

- PRD-002, SPEC-002, FLOW-002, TECH-FLOW-002, SEQ-002

**Race System**:

- PRD-003, SPEC-003, FLOW-003, TECH-FLOW-003, SEQ-004

**Skill System**:

- PRD-004, SPEC-004, FLOW-004, TECH-FLOW-004, SEQ-003

**Support Cards**:

- PRD-005, SPEC-005, FLOW-005, TECH-FLOW-005, SEQ-005, SEQ-016

**AI Advisory**:

- PRD-006, SPEC-006, FLOW-006, TECH-FLOW-006, SEQ-006

**External Integration**:

- PRD-007, SPEC-007, FLOW-007, TECH-FLOW-007, SEQ-007

### By Document Type

**Requirements**: PRD-001 through PRD-007
**Specifications**: SPEC-001 through SPEC-008
**Flows**: FLOW-001 through FLOW-007
**Sequences**: SEQ-001 through SEQ-016
**Tech Flows**: TECH-FLOW-001 through TECH-FLOW-007
**User Flows**: UF-001 through UF-008
**Wireframes**: WF-001 through WF-012

## Related Skills

- `uma-musume-domain-SKILL.md`: Game mechanics and domain knowledge
- `laravel-architecture-SKILL.md`: Technical architecture patterns
- `laravel-testing-SKILL.md`: Testing approaches and patterns

## Version Information

- Documentation Version: v2.0.0
- Last Major Update: 2026-01-23
- Last Updated: 2026-01-29
