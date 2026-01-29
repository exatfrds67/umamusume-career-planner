# Design Documentation Index

**Last Updated**: January 28, 2026  
**Status**: Active Design Phase

---

## Overview

This directory contains comprehensive design documentation for the Umamusume Career Planner, including game alignment analysis, component specifications, data flow mapping, and implementation planning.

---

## Core Design Documents

### 1. [IMPLEMENTATION_PLAN.md](IMPLEMENTATION_PLAN.md)

**Comprehensive 15-week implementation roadmap**

- 8 phases covering foundation to launch
- 150+ specific tasks with checkboxes
- Component specifications with code examples
- Acceptance criteria for each deliverable
- Risk management and success metrics
- Complete component and page checklists

**Key Sections**:

- Phase 1-2: Foundation & Core Display (Weeks 1-4)
- Phase 3-4: Interactive Features & Pages (Weeks 5-9)
- Phase 5-6: Advanced Features & Data Management (Weeks 10-12)
- Phase 7-8: Polish, Testing & Documentation (Weeks 13-15)

---

### 2. [IMPLEMENTATION_PLAN_UPDATES.md](IMPLEMENTATION_PLAN_UPDATES.md)

**Critical updates from research and wireframes**

- Game mechanics corrections (skill hints, aptitudes, stat caps)
- Wireframe-specific requirements
- Performance and accessibility targets
- Testing requirements
- Implementation priority matrix

**Critical Corrections**:

- Skill hint system: 5 levels (not 2), 40% max discount
- Aptitude grades: S is maximum (not SS)
- Stat range: Can exceed 1200 with diminishing returns
- Track conditions: Detailed penalty tables

---

### 3. [game-alignment-analysis.md](game-alignment-analysis.md)

**Comprehensive game UI/UX analysis from 83 screenshots**

- Top status bar patterns
- Character management UI
- Support card system
- Career & race interface
- Dialogue & event system
- Color palette and visual language

**Key Insights**:

- Persistent resource tracking at top
- Card-based UI metaphors
- 6-slot support deck grid
- Condition indicators (GREAT/GOOD/NORMAL/BAD)
- Race day states and badges

---

### 4. [component-inventory.md](component-inventory.md)

**Complete inventory of 60+ UI components**

Organized by category:

- Layout Components (9)
- Data Display Components (25)
- Input Components (12)
- Feedback Components (12)
- Navigation Components (8)
- Dialogue & Event Components (5)
- Analytics & Charts (5)

**Includes**:

- Component specifications
- Props and state management
- Responsive patterns
- Accessibility requirements
- Implementation priorities

---

### 5. [data-flow-mapping.md](data-flow-mapping.md)

**Data requirements and flow patterns**

- Component data requirements
- Real-time data flows (WebSocket)
- Service layer data providers
- External API integration
- State management patterns
- Caching strategy

**Key Sections**:

- Character management data
- Stats display data
- Career status data
- Support card data
- Skill data
- Race data

---

### 6. [game-alignment-plan.md](game-alignment-plan.md)

**Actionable implementation plan for game-aligned UI/UX**

- UI component roadmap
- Page-level implementation
- Color system implementation
- Responsive breakpoints
- Accessibility implementation
- Performance optimization
- Implementation timeline

---

### 7. [prototype-plan.md](prototype-plan.md)

**Interactive prototype specifications**

**P0 Prototypes** (Critical):

- PT-001: Training Decision Flow
- PT-002: Character Stats Dashboard
- PT-003: Support Deck Builder
- PT-004: Race Readiness & Entry

**P1 Prototypes** (Important):

- PT-005: Skill Acquisition Flow
- PT-006: AI Advisor Chat
- PT-007: Character Creation Wizard

**Includes**:

- Interaction specifications
- Success metrics
- Testing plans
- Deliverables

---

### 8. [game-ui-alignment-strategy.md](game-ui-alignment-strategy.md)

**Design philosophy and visual identity**

- Game-aligned, web-optimized approach
- Color palette (verified from game)
- Component architecture
- Layout strategy
- Implementation roadmap

**Key Principles**:

- Align with game functions and mental models
- Create distinct web-optimized interface
- Maintain accessibility and performance

---

## Related Documentation

### Research Documents

- [../research/game-mechanics-research-report.md](../research/game-mechanics-research-report.md) - Verified game mechanics from authoritative sources

### Wireframe Documents

- [../01-wireframes/000_WIREFRAMES_INDEX.md](../01-wireframes/000_WIREFRAMES_INDEX.md) - Complete wireframe catalog
- WF-001: Dashboard Overview
- WF-002: Character Creation Wizard
- WF-003: Character Detail Management
- WF-004: Training Selection Interface
- WF-005: Training Result Screen
- WF-006: Race Calendar View
- WF-007: Race Preparation Screen
- WF-008: Skill Shop Interface
- WF-009: Skill Loadout Manager
- WF-010: Support Card Collection
- WF-011: Support Deck Builder
- WF-012: AI Advisor Interface

### Core Documentation

- [../00-core-docs/004_SDS_Software_Design_Specifications.md](../00-core-docs/004_SDS_Software_Design_Specifications.md) - System architecture
- [../00-core-docs/017_SUM_Software_User_Manual.md](../00-core-docs/017_SUM_Software_User_Manual.md) - User documentation

---

## Quick Reference

### Color Palette (Verified)

| Stat    | Tailwind Class | Hex Code  | Usage                       |
| ------- | -------------- | --------- | --------------------------- |
| Speed   | `rose-500`     | `#FB7185` | Speed stat, icons           |
| Stamina | `green-500`    | `#22C55E` | Stamina stat, healing       |
| Power   | `orange-500`   | `#F97316` | Power stat, physical effort |
| Guts    | `amber-400`    | `#FBBF24` | Guts stat, burning spirit   |
| Wit     | `sky-500`      | `#0EA5E9` | Wit stat, mental skills     |

### Condition Colors

| Condition | Color                  | Icon         |
| --------- | ---------------------- | ------------ |
| GREAT     | Pink (`#EC4899`)       | ↑↑ Up arrow  |
| GOOD      | Light Blue (`#60A5FA`) | ↑ Up arrow   |
| NORMAL    | Orange (`#F97316`)     | → Flat arrow |
| BAD       | Red (`#EF4444`)        | ↓ Down arrow |

### Grade Scale

**Aptitude Grades**: G → F → E → D → C → B → A → S (S is maximum)

**Stat Grades**:

| Grade | Range     | Color      |
| ----- | --------- | ---------- |
| S     | 950-1099  | Purple     |
| A     | 850-949   | Red        |
| B+    | 750-849   | Orange     |
| B     | 650-749   | Yellow     |
| C+    | 550-649   | Green      |
| C     | 450-549   | Blue       |
| D+    | 350-449   | Gray       |
| D     | 250-349   | Light Gray |
| E     | 150-249   | Light Gray |
| F     | 0-149     | Light Gray |

### Skill Hint Discounts (Verified)

| Hint Level | Discount | Example (120 SP) |
| ---------- | -------- | ---------------- |
| 0 hints    | 0%       | 120 SP           |
| 1 hint     | 10%      | 108 SP (-12)     |
| 2 hints    | 20%      | 96 SP (-24)      |
| 3 hints    | 30%      | 84 SP (-36)      |
| 4 hints    | 35%      | 78 SP (-42)      |
| 5 hints    | 40% max  | 72 SP (-48)      |

---

## Implementation Workflow

### 1. Review Phase

- Read IMPLEMENTATION_PLAN.md for overall roadmap
- Review IMPLEMENTATION_PLAN_UPDATES.md for critical corrections
- Study game-alignment-analysis.md for UI patterns
- Review relevant wireframes for specific screens

### 2. Design Phase

- Reference component-inventory.md for component specs
- Check data-flow-mapping.md for data requirements
- Review game-ui-alignment-strategy.md for design principles

### 3. Development Phase

- Follow phase-by-phase implementation plan
- Use component specifications as reference
- Implement data flows as documented
- Follow accessibility guidelines

### 4. Testing Phase

- Use prototype-plan.md for interaction testing
- Follow testing requirements from IMPLEMENTATION_PLAN_UPDATES.md
- Validate against wireframe specifications

### 5. Validation Phase

- Check against game-alignment-analysis.md for accuracy
- Verify color palette matches game
- Validate game mechanics (hints, aptitudes, stats)
- Test accessibility compliance

---

## Key Decisions & Rationale

### Design Philosophy

**Game-Aligned, Web-Optimized**: Use familiar game patterns but optimize for web-based planning workflows.

### Color System

**Game-Accurate**: Colors extracted from 83 game screenshots to ensure instant recognition.

### Component Architecture

**Card-Based**: Mirrors game's card metaphor for characters, support cards, and skills.

### Accessibility

**WCAG 2.2 AA**: Built-in from the start, not bolted-on later.

### Performance

**Core Web Vitals**: Target <2s load time, <100ms interaction delay.

### Testing

**Comprehensive**: 80%+ code coverage, E2E tests for all critical flows.

---

## Success Metrics

### Technical Metrics

- Lighthouse Performance: ≥90
- WCAG 2.2 AA Compliance: 100%
- Test Coverage: ≥80%
- Browser Support: 95%+

### User Metrics

- Task Completion: ≥90%
- Time on Task: ≤baseline
- Error Rate: <5%
- Satisfaction: ≥4.5/5

### Business Metrics

- Adoption: 1000+ MAU (6 months post-launch)
- Migration Success: >95% successful imports
- Feature Usage: ≥70% use core features

---

## Contributing

When adding new design documentation:

1. Follow existing document structure
2. Include version history and related documents
3. Add entry to this README
4. Update IMPLEMENTATION_PLAN.md if needed
5. Cross-reference with wireframes and research

---

## Document Control

**Maintained By**: Development Team  
**Review Cycle**: Weekly during active development  
**Next Review**: 2026-02-15 (after Phase 1 completion)

**Version History**:

| Version | Date       | Changes                                  |
| ------- | ---------- | ---------------------------------------- |
| 1.0.0   | 2026-01-28 | Initial design documentation index       |

---

*This index reflects the current design documentation for Umamusume Career Planner v2.0.0. All documents are aligned with implemented features and design system guidelines.*
