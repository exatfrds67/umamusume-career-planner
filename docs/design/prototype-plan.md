# Interactive Prototype Plan

**Document Version**: 1.0.0  
**Date**: January 28, 2026  
**Status**: Planning Document  
**Related Documents**: [component-inventory.md], [data-flow-mapping.md], [000_WIREFRAMES_INDEX.md]

---

## Overview

This document outlines the plan for building interactive prototypes of critical user flows in the Umamusume Career Planner. Prototypes will validate design decisions, test user interactions, and identify usability issues before full implementation.

---

## 1. Prototype Categories

### 1.1 Priority Levels

| Priority              | Description                             | Prototype Scope            |
| --------------------- | --------------------------------------- | -------------------------- |
| **P0 - Critical**     | Core user flows that define the product | Full interaction prototype |
| **P1 - Important**    | Secondary flows that enhance experience | Key interaction prototype  |
| **P2 - Nice to Have** | Edge cases and advanced features        | Click-through mockup       |

### 1.2 Prototype Types

| Type              | Tool                 | Purpose                       |
| ----------------- | -------------------- | ----------------------------- |
| **Click-through** | Figma/Static HTML    | Navigation flow validation    |
| **Interactive**   | Alpine.js + Blade    | Component interaction testing |
| **Functional**    | Livewire + Mock Data | Full feature validation       |

---

## 2. Critical Prototypes (P0)

### 2.1 PT-001: Training Decision Flow

**Wireframe Reference**: WF-004, WF-005

**User Story**: As a player, I want to see training predictions and select the best option for my goals.

```mermaid
flowchart LR
    A[View Training Options] --> B[Compare Predictions]
    B --> C[Check Risk Indicators]
    C --> D[Review AI Recommendation]
    D --> E[Select Training]
    E --> F[View Results]
```text

**Key Interactions to Prototype**:

| Interaction      | Description                        | Success Criteria             |
| ---------------- | ---------------------------------- | ---------------------------- |
| Prediction Cards | 6 training options with stat gains | User can compare at a glance |
| Risk Indicators  | Color-coded risk badges            | User understands risk levels |
| AI Badge         | Recommended option highlight       | User notices AI suggestion   |
| Selection        | Click/tap to select                | Responsive feedback          |
| Confirmation     | Review before commit               | Clear action buttons         |
| Result Animation | Stat gains animation               | Satisfying feedback          |

**Prototype Deliverables**:

1. Static HTML/CSS mockup of Training Selection screen
2. Alpine.js interactive version with mock data
3. Stat gain animation CSS/JS
4. Responsive variants (mobile, tablet, desktop)

**Success Metrics**:

- Time to select training < 10 seconds
- Users correctly identify AI recommendation > 90%
- Users understand risk levels > 85%

---

### 2.2 PT-002: Character Stats Dashboard

**Wireframe Reference**: WF-003

**User Story**: As a player, I want to see my character's current state and progress at a glance.

```mermaid
flowchart LR
    A[Load Dashboard] --> B[View Stats]
    B --> C[Check Goals Progress]
    C --> D[See Next Race]
    D --> E[Quick Actions]
```

**Key Interactions to Prototype**:

| Interaction     | Description                  | Success Criteria            |
| --------------- | ---------------------------- | --------------------------- |
| Stat Bars       | Animated progress bars       | Smooth animations           |
| Grade Badges    | Letter grades with tooltips  | Clear grade meanings        |
| Condition Badge | Mood indicator               | Understand current state    |
| Energy Gauge    | Energy level with trend      | Trend arrow is clear        |
| Goal Cards      | Progress toward goals        | Motivating progress display |
| Quick Actions   | Training/Race/Skills buttons | One-click access            |

**Prototype Deliverables**:

1. Desktop dashboard layout with all panels
2. Mobile responsive version
3. Dark mode variant
4. Loading states for each panel
5. Real-time update simulation

**Success Metrics**:

- Users locate current stats < 5 seconds
- Users understand condition status > 90%
- Users find quick actions < 3 seconds

---

### 2.3 PT-003: Support Deck Builder

**Wireframe Reference**: WF-011

**User Story**: As a player, I want to build and optimize my support deck before starting a career.

```mermaid
flowchart LR
    A[Open Deck Builder] --> B[View 6 Slots]
    B --> C[Click Slot to Add Card]
    C --> D[Browse Card Collection]
    D --> E[Select Card]
    E --> F[See Deck Score Update]
    F --> G[Save Deck]
```text

**Key Interactions to Prototype**:

| Interaction       | Description                 | Success Criteria     |
| ----------------- | --------------------------- | -------------------- |
| Empty Slots       | Placeholder with add button | Clear call to action |
| Card Picker Modal | Filterable card grid        | Easy to find cards   |
| Filter Panel      | Type/Rarity filters         | Quick filtering      |
| Card Selection    | Click to assign             | Visual feedback      |
| Deck Score        | Real-time calculation       | Immediate feedback   |
| Type Totals       | Stat type distribution      | Clear visualization  |
| Save/Reset        | Deck management actions     | Confident actions    |

**Prototype Deliverables**:

1. 6-slot grid with drag-and-drop
2. Card picker modal with filters
3. Real-time deck score calculation
4. Copy/Reset functionality
5. Mobile touch-optimized version

**Success Metrics**:

- Build complete deck < 60 seconds
- Users understand deck score > 85%
- Filter to desired card < 15 seconds

---

### 2.4 PT-004: Race Readiness & Entry

**Wireframe Reference**: WF-006, WF-007

**User Story**: As a player, I want to assess my readiness for a race and decide whether to enter.

```mermaid
flowchart LR
    A[View Race Calendar] --> B[Select Race]
    B --> C[See Readiness Score]
    C --> D[Review Requirements]
    D --> E{Ready?}
    E -->|Yes| F[Enter Race]
    E -->|No| G[Training Suggestions]
```

**Key Interactions to Prototype**:

| Interaction        | Description                    | Success Criteria        |
| ------------------ | ------------------------------ | ----------------------- |
| Race Calendar      | Monthly calendar view          | Easy date navigation    |
| Race Card          | Grade/Distance/Surface         | All info visible        |
| Readiness Gauge    | Score with breakdown           | Understand requirements |
| Win Probability    | Success estimation             | Realistic expectations  |
| Entry Button       | Conditional based on readiness | Clear enabled/disabled  |
| Confirmation Modal | For low-readiness entries      | Informed decisions      |

**Prototype Deliverables**:

1. Calendar view with race indicators
2. Race detail slide-in panel
3. Readiness breakdown visualization
4. Entry confirmation flow
5. "Not Ready" warning flow

**Success Metrics**:

- Find specific race < 10 seconds
- Understand readiness score > 90%
- Make informed entry decision > 95%

---

## 3. Important Prototypes (P1)

### 3.1 PT-005: Skill Acquisition Flow

**Wireframe Reference**: WF-008, WF-009

**Key Interactions**:

| Interaction            | Priority | Notes                                 |
| ---------------------- | -------- | ------------------------------------- |
| Skill catalog browsing | P1       | Filter by type, cost, acquired        |
| Hint discount display  | P1       | Show savings clearly                  |
| SP budget tracker      | P1       | Running total of planned purchases    |
| Bulk skill planning    | P1       | Add multiple skills before purchasing |
| Skill comparison       | P2       | Side-by-side comparison               |

**Prototype Scope**:

- Skill shop with filter panel
- SP counter with planned vs available
- Hint level discount calculator
- Skill detail modal

---

### 3.2 PT-006: AI Advisor Chat

**Wireframe Reference**: WF-012

**Key Interactions**:

| Interaction            | Priority | Notes                              |
| ---------------------- | -------- | ---------------------------------- |
| Chat interface         | P1       | Message input + history            |
| Recommendation display | P1       | Formatted advice cards             |
| Provider selection     | P2       | Switch between AI providers        |
| Context awareness      | P2       | Show what AI knows about character |
| Follow-up questions    | P2       | Suggested prompts                  |

**Prototype Scope**:

- Chat UI with message bubbles
- Typing indicator animation
- AI recommendation card format
- Quick action buttons in responses

---

### 3.3 PT-007: Character Creation Wizard

**Wireframe Reference**: WF-002

**Key Interactions**:

| Interaction             | Priority | Notes                        |
| ----------------------- | -------- | ---------------------------- |
| Step progress indicator | P1       | Visual step tracker          |
| Character selection     | P1       | Grid of available characters |
| Stats input             | P1       | Initial stats entry          |
| Scenario selection      | P1       | Choose career path           |
| Support deck selection  | P1       | Pick from saved decks        |
| Review & confirm        | P1       | Summary before creation      |

**Prototype Scope**:

- Multi-step wizard navigation
- Step validation before proceeding
- Summary view with edit capability
- Start career CTA

---

## 4. Nice to Have Prototypes (P2)

### 4.1 PT-008: Analytics Dashboard

**Key Interactions**:

- Stat trend charts (line graphs)
- Win/loss distribution (pie chart)
- Training efficiency metrics
- Historical comparison

**Scope**: Static mockup with sample charts

---

### 4.2 PT-009: OCR Import Flow

**Key Interactions**:

- Screenshot upload/capture
- OCR processing animation
- Data validation UI
- Manual correction interface
- Import confirmation

**Scope**: Click-through mockup of flow

---

### 4.3 PT-010: Settings & Preferences

**Key Interactions**:

- Theme toggle (light/dark)
- AI provider configuration
- Notification preferences
- Data export/import

**Scope**: Static mockup of settings panels

---

## 5. Prototype Development Stack

### 5.1 Technology Choices

| Layer             | Technology            | Reason                     |
| ----------------- | --------------------- | -------------------------- |
| **Markup**        | Blade templates       | Matches production stack   |
| **Styling**       | TailwindCSS v4        | Production styling system  |
| **Interactivity** | Alpine.js             | Matches production tooling |
| **Mock Data**     | JSON fixtures         | Realistic test scenarios   |
| **Hosting**       | Local Vite dev server | Fast iteration             |

### 5.2 File Structure

```text
prototypes/
├── components/
│   ├── stat-bar.blade.php
│   ├── condition-badge.blade.php
│   ├── deck-slot.blade.php
│   └── skill-card.blade.php
├── layouts/
│   ├── app.blade.php
│   └── mobile.blade.php
├── pages/
│   ├── pt-001-training/
│   ├── pt-002-dashboard/
│   ├── pt-003-deck-builder/
│   └── pt-004-race-entry/
├── data/
│   ├── characters.json
│   ├── support-cards.json
│   ├── skills.json
│   └── races.json
└── assets/
    ├── css/
    └── js/
```

### 5.3 Mock Data Requirements

| Data Set      | Records | Key Fields                        |
| ------------- | ------- | --------------------------------- |
| Characters    | 10      | Stats, current turn, career stage |
| Support Cards | 50      | Rarity, type, level, effects      |
| Skills        | 100     | Cost, hint level, requirements    |
| Races         | 30      | Grade, distance, dates            |

---

## 6. Testing Plan

### 6.1 Usability Testing

| Prototype           | Test Type        | Participants | Duration |
| ------------------- | ---------------- | ------------ | -------- |
| PT-001 Training     | Task completion  | 5-8 users    | 15 min   |
| PT-002 Dashboard    | First impression | 5-8 users    | 10 min   |
| PT-003 Deck Builder | Task completion  | 5-8 users    | 20 min   |
| PT-004 Race Entry   | Decision making  | 5-8 users    | 15 min   |

### 6.2 Test Tasks

#### PT-001: Training Decision

1. Find the training most likely to improve Speed
2. Identify which training the AI recommends
3. Find the training with highest risk
4. Select a training and confirm

#### PT-002: Stats Dashboard

1. Find your character's current Speed stat
2. Identify your character's condition/mood
3. Locate the next scheduled race
4. Navigate to the training screen

#### PT-003: Deck Builder

1. Add a Speed-type support card to slot 1
2. Find all SSR cards in your collection
3. Build a complete 6-card deck
4. Check the deck's total score

#### PT-004: Race Entry

1. Find the next G1 race on the calendar
2. Check your readiness score for that race
3. Decide whether to enter (explain reasoning)
4. Complete the entry process

### 6.3 Success Metrics

| Metric               | Target      | Measurement                |
| -------------------- | ----------- | -------------------------- |
| Task completion rate | > 90%       | Users complete main tasks  |
| Time on task         | < benchmark | Users complete efficiently |
| Error rate           | < 10%       | Users make few mistakes    |
| Satisfaction rating  | > 4/5       | Post-test survey           |
| SUS score            | > 80        | System Usability Scale     |

---

## 7. Implementation Timeline

### 7.1 Phase 1: P0 Prototypes (Week 1-2)

| Day  | Deliverable                          |
| ---- | ------------------------------------ |
| 1-2  | PT-001 Training Decision prototype   |
| 3-4  | PT-002 Dashboard prototype           |
| 5-6  | PT-003 Deck Builder prototype        |
| 7-8  | PT-004 Race Entry prototype          |
| 9-10 | Testing & iteration on P0 prototypes |

### 7.2 Phase 2: P1 Prototypes (Week 3)

| Day | Deliverable                         |
| --- | ----------------------------------- |
| 1-2 | PT-005 Skill Acquisition prototype  |
| 3-4 | PT-006 AI Advisor prototype         |
| 5   | PT-007 Character Creation prototype |

### 7.3 Phase 3: Polish & Documentation (Week 4)

| Day | Deliverable                   |
| --- | ----------------------------- |
| 1-2 | P2 static mockups             |
| 3-4 | Refinement based on testing   |
| 5   | Final documentation & handoff |

---

## 8. Handoff Deliverables

### 8.1 Per Prototype

1. **Interactive prototype** (Blade + Alpine.js)
2. **Responsive variants** (mobile, tablet, desktop)
3. **Interaction documentation** (state changes, animations)
4. **Accessibility notes** (keyboard nav, screen reader)
5. **Test results summary** (usability findings)

### 8.2 Design System Artifacts

1. **Component snippets** (reusable code)
2. **Animation specifications** (timing, easing)
3. **Responsive breakpoint behaviors**
4. **Dark mode color mappings**
5. **ARIA pattern documentation**

---

## 9. Risks & Mitigations

| Risk                                            | Impact                      | Mitigation                          |
| ----------------------------------------------- | --------------------------- | ----------------------------------- |
| Mock data doesn't reflect real scenarios        | Low prototype validity      | Create diverse, realistic test data |
| Users unfamiliar with game mechanics            | Poor test results           | Include brief onboarding            |
| Prototype too polished, sets wrong expectations | Feature creep               | Communicate "this is a prototype"   |
| Technical limitations vs production             | Prototype doesn't translate | Use same tech stack                 |

---

## Document Control

**Version History**:

| Version | Date       | Changes                |
| ------- | ---------- | ---------------------- |
| 1.0.0   | 2026-01-28 | Initial prototype plan |

**Related Documents**:

- [component-inventory.md](component-inventory.md)
- [data-flow-mapping.md](data-flow-mapping.md)
- [000_WIREFRAMES_INDEX.md](../01-wireframes/000_WIREFRAMES_INDEX.md)
