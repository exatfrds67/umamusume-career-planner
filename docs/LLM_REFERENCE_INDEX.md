# LLM Documentation Reference Index

**Purpose**: Centralized reference guide for LLMs (Claude, etc.) to quickly locate essential project documentation.

**Last Updated**: March 21, 2026
**Scope**: All critical documentation for development, integration, and system understanding

---

## 🚀 Quick-Start References

### Architecture & Core Documents

| Document | Location | Purpose |
|----------|----------|---------|
| **System Design Plan** | `docs/00-core-docs/SDP.md` | High-level architecture, components, interactions |
| **Database Design** | `docs/00-core-docs/DBD.md` | Entity relationships, schema, constraints |
| **System Requirements** | `docs/00-core-docs/SRS.md` | Functional/non-functional requirements |
| **System Specification** | `docs/00-core-docs/SDS.md` | Detailed technical specifications |
| **Glossary** | `docs/00-core-docs/GLOSSARY.md` | Domain terminology and definitions |

### Feature Requirements & Specifications

| Module | PRD | SPEC |
|--------|-----|------|
| **Character Management** | `docs/02-prds/PRD_Character_Management.md` | `docs/02-specs/SPEC_Character_Management.md` |
| **Training System** | `docs/02-prds/PRD_Training_System.md` | `docs/02-specs/SPEC_Training_System.md` |
| **Skill System** | `docs/02-prds/PRD_Skill_System.md` | `docs/02-specs/SPEC_Skill_System.md` |
| **Support Cards** | `docs/02-prds/PRD_Support_Cards.md` | `docs/02-specs/SPEC_Support_Cards.md` |
| **Race System** | `docs/02-prds/PRD_Race_System.md` | `docs/02-specs/SPEC_Race_System.md` |
| **AI Advisory** | `docs/02-prds/PRD_AI_Advisory.md` | `docs/02-specs/SPEC_AI_Advisory.md` |

---

## 🔌 API & Integration References

### External APIs

| API | Documentation | Status |
|-----|---------------|--------|
| **Gametora** | `docs/external-api-integration/UMAPYOI_NET_API_STATUS.md` | Production-ready |
| **Umamusumedb** | `docs/external-api-integration/umamusumedb-api-verification.md` | Verified |
| **Character Images** | `docs/external-api-integration/SUPPORT_CARD_IMAGE_STATUS.md` | Implemented |
| **AI Training Advisory** | `docs/external-api-integration/ai-training-advisory-api.md` | Reference |

### MCP Integration

| Topic | Documentation |
|-------|---------------|
| **MCP Server Setup** | `docs/mcp-integration/MCP_SERVER_CONFIGURATION_REFERENCE.md` |
| **MCP Tools** | `docs/neuron/tools.md` |
| **Recommendations** | `docs/mcp-integration/MCP_SERVER_RECOMMENDATIONS.md` |

### AI & Neuron

| Topic | Documentation |
|-------|---------------|
| **Neuron AI Overview** | `docs/neuron/README.md` |
| **AI Providers** | `docs/neuron/ai-providers.md` |
| **Integration Guide** | `docs/neuron/integration-guide.md` |
| **RAG Implementation** | `docs/neuron/rag.md` |

---

## 🛠️ Development Workflow Guides

### By Role/Task

| Task | Documentation |
|------|---------------|
| **Starting Development** | `docs/guides/QUICK_START_CHARACTER_CREATION.md` |
| **Frontend Development** | `docs/frontend-development/README.md` |
| **Component Development** | `docs/design/component-inventory.md` |
| **Feature Implementation** | `docs/implementation-summaries/` (see latest summaries) |
| **Testing Features** | `docs/testing/BROWSER-TESTS-QUICK-REFERENCE.md` |
| **Debugging Issues** | `docs/fixes/` (categorized by issue type) |

### Feature Guides (Game Mechanics)

| Feature | Documentation |
|---------|---------------|
| **Career Mode Fundamentals** | `docs/guides/TURN_BASED_CALENDAR_CAREER_MODE_UMA_MUSUME.md` |
| **Race Calendar System** | `docs/guides/RACE_CALENDAR_GUIDE.md` |
| **Skill System** | `docs/guides/SKILL_SYSTEM_GUIDE.md` |
| **Support Deck Setup** | `docs/guides/SUPPORT_DECK_SETUP_GUIDE.md` |
| **Inheritance System** | `docs/guides/INHERITANCE_SYSTEM_LEGACIES_SPARKS_UMA_MUSUME.md` |
| **Synergy Building** | `docs/guides/SYNERGY_BUILD_MECHANIC_UMA_MUSUME.md` |

---

## 🔧 Configuration & Setup

### Environment Setup

| Topic | Documentation |
|-------|---------------|
| **Quick Start - Admin Panel** | `docs/setup-guides/admin-panel-quick-start.md` |
| **Deployment Checklist** | `docs/deployment/LAUNCH_CHECKLIST.md` |
| **Deployment Setup** | `docs/deployment/deployment.md` |

### System Configuration

| System | Documentation |
|--------|---------------|
| **Redis Setup** | `docs/redis/START_HERE.md` → `docs/redis/REDIS_COMPLETE_GUIDE.md` |
| **Tesseract OCR** | `docs/setup-guides/INSTALL_TESSERACT_OCR.md` |
| **Pest Browser Tests** | `docs/setup-guides/pest-browser-setup.md` |
| **Code Coverage** | `docs/setup-guides/INSTALL_CODE_COVERAGE.md` |

---

## 🧪 Testing & Quality

### Testing Guides

| Type | Documentation |
|------|---------------|
| **Browser Testing Overview** | `docs/testing/BROWSER-TESTS-QUICK-REFERENCE.md` |
| **API Testing** | `docs/testing/API_TESTING_REPORT.md` |
| **Character Creation Flow** | `docs/testing/character-creation-flow-test.md` |
| **External Data Browser** | `docs/testing/external-data-browser-manual-testing-guide.md` |
| **Production Testing** | `docs/testing/PRODUCTION_TESTING_GUIDE.md` |

### Code Quality

| Topic | Documentation |
|-------|---------------|
| **Larastan Static Analysis** | `docs/larastan/larastan-level9-fixes-summary.md` |
| **Audit Findings** | `docs/audits/AUDIT_FINDINGS_SAFE_2026-03-09.md` |
| **Authorization Audit** | `docs/audits/authorization-audit-2026-01-29.md` |

---

## 📊 System Design & Flows

### Architecture Diagrams

| Diagram | Location |
|---------|----------|
| **Entity Relationships** | `docs/01-diagrams/` |
| **Data Flows** | `docs/01-diagrams/` |
| **Component Interactions** | `docs/01-flows/` |

### Detailed Flows

| Flow | Location |
|------|----------|
| **System Flows** | `docs/01-flows/` |
| **User Interactions** | `docs/01-user-flows/` |
| **Sequence Diagrams** | `docs/01-sequences/` |
| **Technical Flows** | `docs/01-tech-flow/` |

---

## 🎮 Game Mechanics Research

| Topic | Documentation |
|-------|---------------|
| **Game Alignment Analysis** | `docs/research/game-alignment-analysis.md` |
| **Game UI Alignment Strategy** | `docs/research/GAME_VISUAL_INTERACTION_PATTERNS.md` |
| **URA Finale Guide** | `docs/research/umamusume-ura-finale-comprehensive-guide.md` |
| **Mechanics Research** | `docs/research/game-mechanics-research-report.md` |

---

## 🔍 Reference Materials

### API Reference

| Topic | Documentation |
|-------|---------------|
| **REST API** | `docs/reference/API_REFERENCE.md` |
| **User Guide** | `docs/reference/USER_GUIDE.md` |
| **Developer Guide** | `docs/reference/DEVELOPER_GUIDE.md` |
| **Best Practices** | `docs/reference/ai-coding-assistant-best-practices.md` |

### Troubleshooting

| Issue | Documentation |
|-------|---------------|
| **AI Troubleshooting** | `docs/guides/AI-Troubleshooting-Guide.md` |
| **Common Fixes** | `docs/fixes/` (browse by issue type) |
| **MongoDB Access** | `docs/reference/external-api-service.md` |

---

## 📦 Feature Documentation by Module

| Module | Documentation |
|--------|---------------|
| **Support Cards** | `docs/feature-documentation/SUPPORT_CARD_EXTERNAL_API_INTEGRATION.md` |
| **Skills** | `docs/feature-documentation/SKILL_SYSTEM_DOCUMENTATION.md` |
| **External API Integration** | `docs/feature-documentation/EXTERNAL_API_INTEGRATION.md` |
| **Lazy Loading** | `docs/feature-documentation/LAZY_LOADING_IMPLEMENTATION.md` |
| **Image Integration** | `docs/feature-documentation/IMAGE_INTEGRATION_COMPLETE.md` |

---

## 📋 Audit & Verification

| Report | Location | Purpose |
|--------|----------|---------|
| **AI Subsystem Audit** | `docs/audits/ai-subsystem-audit-2026-02-28.md` | AI integration verification |
| **Authorization Audit** | `docs/audits/authorization-audit-2026-01-29.md` | Security review |
| **Telescope/Horizon Audit** | `docs/audits/telescope-horizon-audit-2026-01-29.md` | Monitoring setup |
| **Technical Findings** | `docs/audits/AUDIT_FINDINGS_SAFE_2026-03-09.md` | Latest findings |
| **Diagram Patches** | `docs/audits/DIAGRAM_PATCH_APPLICATION_GUIDE_REVISED.md` | Documentation fixes |

---

## 📚 Future References

| Topic | Documentation |
|-------|---------------|
| **Future Features** | `docs/future-implements/comprehensive_future_features.md` |
| **Missing Features** | `docs/future-implements/umamusume_missing_features_analysis.md` |
| **5% Analysis** | `docs/future-implements/remaining_5_percent_analysis.md` |

---

## 🗂️ Directory Navigation

### By Subdirectory

- **`00-core-docs/`** - SDLC specification documents (SDP, DBD, SRS, SDS, SCD, SUM, GLOSSARY)
- **`01-diagrams/`** - System diagrams (ERD, data flow, component diagrams)
- **`01-flows/`** - System flow documentation
- **`01-sequences/`** - Sequence diagrams for interactions
- **`01-tech-flow/`** - Technical implementation flows
- **`01-user-flows/`** - User journey flows
- **`01-wireframes/`** - UI mockups and wireframes
- **`02-prds/`** - Product Requirements Documents by feature
- **`02-specs/`** - Technical Specifications by feature
- **`audits/`** - Project audit reports and findings
- **`external-api-integration/`** - External API documentation and integration guides
- **`feature-documentation/`** - Feature-specific implementation docs
- **`guides/`** - User and game mechanics guides
- **`implementation-summaries/`** - Task completion and phase summaries
- **`larastan/`** - Static analysis documentation
- **`mcp-integration/`** - MCP server integration
- **`neuron/`** - Neuron AI framework documentation
- **`redis/`** - Redis setup and configuration
- **`research/`** - Game mechanics and design research
- **`testing/`** - Testing guides and results
- **`setup-guides/`** - Installation and setup
- **`deployment/`** - Deployment configuration

---

## 💡 Usage Tips for LLMs

1. **Start with core docs**: SDP → DBD → SRS → relevant SPEC for context
2. **Understand feature requirements**: Check PRD before diving into implementation
3. **Reference existing patterns**: Browse implementation-summaries for similar features
4. **Use game guides**: Understand Uma Musume mechanics from guides/ before implementation
5. **Check external APIs**: Verify API docs before integration work
6. **Review latest audits**: Keep aware of known issues and fixes
7. **Test thoroughly**: Follow testing guides in docs/testing/

---

## 🔄 Updates & Maintenance

This index is maintained to reflect current documentation structure. When adding new documentation:

1. Add entry to appropriate section above
2. Update the relevant subdirectory's README.md
3. Link from related documentation
4. Keep entries alphabetically sorted within sections

---

**Questions?** Refer to specific subdirectory README.md files for context-specific navigation.
