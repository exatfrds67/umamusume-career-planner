# WIREFRAMES & UI SPECIFICATIONS INDEX

**Document Version**: 2.3.0
**Date**: March 8, 2026
**Status**: Under alignment review; core storage-aware flows are aligned, and wireframes are being
refined against the current route surface and persistence boundaries

---

## Overview

This index provides a catalog of wireframe specifications for the Umamusume Pretty Derby Career
Planner application. Wireframes define the intended user interface layout, component hierarchy,
interaction patterns, and design constraints for major screens and workflows, but they should be
read alongside the aligned user-flow and tech-flow docs before being treated as implementation-
exact.

**Design Philosophy**: Mobile-first responsive design with accessibility compliance (WCAG 2.2 AA),
dark mode support, and progressive disclosure principles.

---

## Document Purpose

### Scope

Wireframe documentation covers:

- **Screen Layouts**: Detailed ASCII/structured representations of UI layouts
- **Component Specifications**: Reusable UI component definitions
- **Interaction Patterns**: User interaction flows and state transitions
- **Responsive Behavior**: Breakpoint-specific layout adaptations
- **Accessibility Guidelines**: WCAG 2.2 AA compliance requirements
- **Design Tokens**: Color schemes, typography, spacing systems

### Alignment Notes

- Wireframes are conceptual first. Route-backed screens, storage boundaries, and controller/service
ownership should be verified against the current tech-flow and user-flow documents.
- Every major screen should state whether it supports `StorageMode::LOCAL`, `StorageMode::ACCOUNT`, or both.
- Local mode should not imply account-backed reporting, history, or server-side mutation unless the
current implementation docs verify that path.
- Reporting, export, and authenticated race-entry surfaces should remain distinguished from browser-
local planning surfaces.

### Intended Audience

| Audience | Use Case |
| --- | --- |
| **UI/UX Designers** | Visual design creation, prototyping |
| **Frontend Developers** | Component implementation, responsive layouts |
| **Product Managers** | Feature validation, user flow review |
| **QA Engineers** | UI test case development, accessibility testing |
| **Stakeholders** | Feature preview, approval workflows |

---

## Wireframe Catalog

### System Architecture

```mermaid
flowchart TB
    subgraph Core[Core Application Screens]
        Dashboard[WF-001: Dashboard]
        CharCreation[WF-002: Character Creation]
        CharDetail[WF-003: Character Detail]
    end

    subgraph Training[Training System]
        TrainingSelect[WF-004: Training Selection]
        TrainingResult[WF-005: Training Result]
    end

    subgraph Racing[Race System]
        RaceCalendar[WF-006: Race Calendar]
        RacePrep[WF-007: Race Preparation]
    end

    subgraph Skills[Skill System]
        SkillShop[WF-008: Skill Shop]
        SkillLoadout[WF-009: Skill Loadout]
    end

    subgraph Support[Support Card System]
        CardCollection[WF-010: Card Collection]
        DeckBuilder[WF-011: Deck Builder]
    end

    subgraph AI[AI Advisory System]
        AIAdvisor[WF-012: AI Advisor]
    end

    Core --> Training
    Core --> Racing
    Core --> Skills
    Core --> Support
    Core --> AI
```text

---

## Wireframe Categories

### 1. Character Management Screens (WF-001 to WF-003)

| Document | Title | Status | Priority |
| --- | --- | --- | --- |
| [WF-001](WF-001_Dashboard_Overview.md) | Dashboard Overview | Alignment review in progress | P0 |
| [WF-002](WF-002_Character_Creation_Wizard.md) | Character Creation Wizard | Alignment review in progress | P0 |
| [WF-003](WF-003_Character_Detail_Management.md) | Character Detail & Management | Consistency review in progress | P0 |

**Coverage**: Dashboard, character creation flow, character detail view

**Related Artifacts**:

- PRD: [PRD-001 Character Management](../02-prds/PRD-001_Character_Management.md)
- SPEC: [SPEC-001 Character Management Technical](../02-specs/SPEC-001_Character_Management_Technical.md)
- Flow: [FLOW-001 Character Management System](../01-flows/FLOW-001_Character_Management_System.md)
- User Flows: [UF-001 Onboarding Flow](../01-user-flows/UF-001_Onboarding_Flow.md), [UF-002 Career
Setup Flow](../01-user-flows/UF-002_Career_Setup_Flow.md)

---

### 2. Training Optimization Screens (WF-004 to WF-005)

| Document | Title | Status | Priority |
| --- | --- | --- | --- |
| [WF-004](WF-004_Training_Selection_Interface.md) | Training Selection Interface | Alignment review in progress | P0 |
| [WF-005](WF-005_Training_Result_Screen.md) | Training Result Screen | Alignment review in progress | P0 |

**Coverage**: Training prediction display, AI recommendations, result processing

**Related Artifacts**:

- PRD: [PRD-002 Training Optimization](../02-prds/PRD-002_Training_Optimization.md)
- SPEC: [SPEC-002 Training Optimization Technical](../02-specs/SPEC-002_Training_Optimization_Technical.md)
- Flow: [FLOW-002 Training Optimization System](../01-flows/FLOW-002_Training_Optimization_System.md)
- Tech Flow: [TECH-FLOW-002 Training Optimization](../01-tech-flow/TECH-FLOW-002_Training_Optimization_Flow.md)
- Sequence: [SEQ-002 Training Block Resolution](../01-sequences/SEQ-002_Training_Block_Resolution.md)
- User Flow: [UF-003 Training Day Flow](../01-user-flows/UF-003_Training_Day_Flow.md)
- Storage Transition: [UF-009 Storage Mode Transition Flow](../01-user-flows/UF-009_Storage_Mode_Transition_Flow.md)

---

### 3. Race Strategy Screens (WF-006 to WF-007)

| Document | Title | Status | Priority |
| --- | --- | --- | --- |
| [WF-006](WF-006_Race_Calendar_View.md) | Race Calendar View | Alignment review in progress | P0 |
| [WF-007](WF-007_Race_Preparation_Screen.md) | Race Preparation Screen | Alignment review in progress | P0 |

**Coverage**: Race scheduling, readiness assessment, strategy recommendations

**Related Artifacts**:

- PRD: [PRD-003 Race Strategy](../02-prds/PRD-003_Race_Strategy.md)
- SPEC: [SPEC-003 Race Strategy Technical](../02-specs/SPEC-003_Race_Strategy_Technical.md)
- Flow: [FLOW-003 Race Strategy System](../01-flows/FLOW-003_Race_Strategy_System.md)
- Tech Flow: [TECH-FLOW-003 Race Strategy](../01-tech-flow/TECH-FLOW-003_Race_Strategy_Flow.md)
- Tech Flow: [TECH-FLOW-009 Target Race Planning](../01-tech-flow/TECH-FLOW-009_Target_Race_Planning_Flow.md)
- Tech Flow: [TECH-FLOW-010 Career Reporting](../01-tech-flow/TECH-FLOW-010_Career_Reporting_Flow.md)
- Sequence: [SEQ-004 Race Registration and Outcome](../01-sequences/SEQ-004_Race_Registration_and_Outcome.md)
- User Flows: [UF-004 Race Day Flow](../01-user-flows/UF-004_Race_Day_Flow.md), [UF-011 Target Race
Planning Flow](../01-user-flows/UF-011_Target_Race_Planning_Flow.md), [UF-010 Career Reporting and
Export Flow](../01-user-flows/UF-010_Career_Reporting_and_Export_Flow.md)

---

### 4. Skill Management Screens (WF-008 to WF-009)

| Document | Title | Status | Priority |
| --- | --- | --- | --- |
| [WF-008](WF-008_Skill_Shop_Interface.md) | Skill Shop Interface | Consistency review in progress | P0 |
| [WF-009](WF-009_Skill_Loadout_Manager.md) | Skill Loadout Manager | Consistency review in progress | P0 |

**Coverage**: Skill catalog, SP management, hint tracking, loadout optimization

**Related Artifacts**:

- PRD: [PRD-004 Skill Management](../02-prds/PRD-004_Skill_Management.md)
- SPEC: [SPEC-004 Skill Management Technical](../02-specs/SPEC-004_Skill_Management_Technical.md)
- Flow: [FLOW-004 Skill Management System](../01-flows/FLOW-004_Skill_Management_System.md)
- Tech Flow: [TECH-FLOW-004 Skill Management](../01-tech-flow/TECH-FLOW-004_Skill_Management_Flow.md)
- Sequence: [SEQ-003 Skill Acquisition and Upgrade](../01-sequences/SEQ-003_Skill_Acquisition_and_Upgrade.md)
- User Flow: [UF-005 Skill Management Flow](../01-user-flows/UF-005_Skill_Management_Flow.md)

---

### 5. Support Card System Screens (WF-010 to WF-011)

| Document | Title | Status | Priority |
| --- | --- | --- | --- |
| [WF-010](WF-010_Support_Card_Collection.md) | Support Card Collection | Consistency review in progress | P0 |
| [WF-011](WF-011_Support_Deck_Builder.md) | Support Deck Builder | Alignment review in progress | P0 |

**Coverage**: Card inventory, meta tier display, deck composition, synergy scoring

**Related Artifacts**:

- PRD: [PRD-005 Support Card Management](../02-prds/PRD-005_Support_Card_Management.md)
- SPEC: [SPEC-005 Support Card Management Technical](../02-specs/SPEC-005_Support_Card_Management_Technical.md)
- Flow: [FLOW-005 Support Card Management System](../01-flows/FLOW-005_Support_Card_Management_System.md)
- Tech Flow: [TECH-FLOW-005 Support Card Management](../01-tech-flow/TECH-FLOW-005_Support_Card_Management_Flow.md)
- Sequence: [SEQ-005 Support Card Upgrade](../01-sequences/SEQ-005_Support_Card_Upgrade.md)
- User Flow: [UF-006 Support Deck Building](../01-user-flows/UF-006_Support_Deck_Building_Flow.md)

---

### 6. AI Advisory System (WF-012)

| Document | Title | Status | Priority |
| --- | --- | --- | --- |
| [WF-012](WF-012_AI_Advisor_Interface.md) | AI Advisor Interface | Alignment review in progress | P1 |

**Coverage**: AI chat interface, recommendation display, provider routing, cost tracking

**Related Artifacts**:

- PRD: [PRD-006 AI Advisory](../02-prds/PRD-006_AI_Advisory.md)
- SPEC: [SPEC-006 AI Advisory Technical](../02-specs/SPEC-006_AI_Advisory_Technical.md)
- Flow: [FLOW-006 AI Advisory System](../01-flows/FLOW-006_AI_Advisory_System.md)
- Tech Flow: [TECH-FLOW-006 AI Advisory](../01-tech-flow/TECH-FLOW-006_AI_Advisory_Flow.md)
- Sequence: [SEQ-006 AI Advice Generation](../01-sequences/SEQ-006_AI_Advice_Generation.md)
- User Flow: [UF-007 AI Advisor Journey](../01-user-flows/UF-007_AI_Advisor_Journey.md)
- Config: [MCP Server Configuration](../MCP_SERVER_CONFIGURATION_REFERENCE.md)

---

## Design System Specifications

### Component Library

```mermaid
mindmap
  root((Design System))
    Layout Components
      App Layout
      Sidebar Navigation
      Main Content
      Toast Container
      Modal Container
    Data Display
      Stat Bars
      Progress Bars
      Card Layouts
      Tables
      Charts
    Input Components
      Text Input
      Select Dropdown
      Autocomplete
      File Upload
      Checkbox/Radio
    Feedback Components
      Toasts
      Modals
      Tooltips
      Loading States
    Navigation
      Navbar
      Breadcrumbs
      Tabs
      Pagination
```

### Design Tokens

#### Color Palette

| Token | Light Mode | Dark Mode | Usage |
| --- | --- | --- | --- |
| `--color-primary` | `#3b82f6` | `#60a5fa` | Primary actions, links |
| `--color-stat-speed` | `#3399ff` | `#66b3ff` | Speed stat indicators |
| `--color-stat-stamina` | `#33cc99` | `#66d9b8` | Stamina stat indicators |
| `--color-stat-power` | `#ff4d4d` | `#ff7373` | Power stat indicators |
| `--color-stat-guts` | `#ffa500` | `#ffb733` | Guts stat indicators |
| `--color-stat-wit` | `#9933ff` | `#b366ff` | Wit stat indicators |
| `--color-bg-primary` | `#ffffff` | `#1a1a2e` | Primary background |
| `--color-bg-secondary` | `#f9fafb` | `#16213e` | Secondary background |
| `--color-text-primary` | `#111827` | `#eaeaea` | Primary text |
| `--color-text-secondary` | `#6b7280` | `#a0a0a0` | Secondary text |

#### Typography Scale

| Token | Size | Line Height | Usage |
| --- | --- | --- | --- |
| `--text-xs` | 0.75rem | 1rem | Captions, labels |
| `--text-sm` | 0.875rem | 1.25rem | Body small, secondary text |
| `--text-base` | 1rem | 1.5rem | Body text |
| `--text-lg` | 1.125rem | 1.75rem | Subheadings |
| `--text-xl` | 1.25rem | 1.75rem | Headings |
| `--text-2xl` | 1.5rem | 2rem | Page titles |
| `--text-3xl` | 1.875rem | 2.25rem | Hero headings |

#### Spacing Scale

| Token | Value | Usage |
| --- | --- | --- |
| `--space-1` | 0.25rem | Tight spacing |
| `--space-2` | 0.5rem | Compact spacing |
| `--space-3` | 0.75rem | Default spacing |
| `--space-4` | 1rem | Standard spacing |
| `--space-6` | 1.5rem | Comfortable spacing |
| `--space-8` | 2rem | Generous spacing |
| `--space-12` | 3rem | Section spacing |

### Responsive Breakpoints

```mermaid
flowchart LR
    Mobile[Mobile<br/>< 640px] --> Tablet[Tablet<br/>640-1024px]
    Tablet --> Desktop[Desktop<br/>1024-1280px]
    Desktop --> Wide[Wide<br/>> 1280px]
```text

| Breakpoint | Width | Layout Strategy |
| --- | --- | --- |
| **Mobile** | < 640px | Single column, bottom nav, collapsible sections |
| **Tablet** | 640-1024px | Two columns where appropriate, sidebar toggle |
| **Desktop** | 1024-1280px | Full layout with sidebar, multi-column grids |
| **Wide** | > 1280px | Maximum content width applied, generous spacing |

#### Responsive Behavior Requirements

- Desktop: multi-panel layout with persistent secondary context.
- Tablet: secondary panels may collapse into tabs or drawers; no hover-only information.
- Mobile: primary action bars stay sticky when the screen includes commit actions; secondary
analytics become collapsible; horizontally scrollable rows require visible affordances.
- Any content revealed only on hover in desktop layouts must have a tap or focus equivalent on tablet and mobile.

### Accessibility Guidelines

#### WCAG 2.2 AA Compliance

| Guideline | Requirement | Implementation |
| --- | --- | --- |
| **1.1.1 Non-text Content** | Alt text for images | All `<img>` tags have descriptive `alt` attributes |
| **1.4.3 Contrast Ratio** | 4.5:1 for normal text | Design tokens enforce minimum contrast |
| **2.1.1 Keyboard Navigation** | All interactive elements focusable | `tabindex` and focus management |
| **2.4.7 Focus Visible** | Visible focus indicators | CSS focus states with high contrast |
| **4.1.2 Name, Role, Value** | ARIA labels on controls | ARIA attributes on all interactive elements |
| **2.3.3 Animation Control** | Respect `prefers-reduced-motion` | CSS media queries for animations |

#### Accessibility Interaction Requirements

- On initial screen load, focus moves to the primary page heading or first actionable control.
- After validation failure, focus moves to an error summary container and the first invalid field.
- After modal dismissal, focus returns to the triggering control.
- Interactive card grids must support `Tab` to enter, visible focus on the active card, and arrow-
key traversal where implemented.
- Mobile primary actions and icon-only controls must meet a minimum `44x44` CSS pixel touch target.
- Collapsible sections must expose expanded or collapsed state and support keyboard activation with `Enter` and `Space`.
- Readiness, risk, warning, and success states must never rely on color alone.

#### Keyboard Shortcuts

| Action | Shortcut | Context |
| --- | --- | --- |
| Save | `Ctrl + S` | All editor screens |
| Cancel | `Esc` | Modals, dialogs |
| Search | `/` | Global navigation |
| Help | `?` | Show keyboard shortcuts |
| Navigate | `Tab` / `Shift + Tab` | Focus traversal |

---

## Implementation Guidelines

### Technology Stack

| Layer | Technology | Version | Purpose |
| --- | --- | --- | --- |
| **Framework** | Laravel | 12+ | Backend framework |
| **Frontend Reactivity** | Livewire | 4 | Server-driven UI |
| **Client Interactivity** | Alpine.js | Bundled with Livewire 4 | Client-side interactions |
| **Styling** | TailwindCSS | v4 | Utility-first CSS |
| **Build Tool** | Vite | 7 | Asset compilation |

### Component Implementation Standards

```mermaid
flowchart TD
    subgraph ComponentStructure[Component Structure]
        Blade[Blade Template]
        Livewire[Livewire Component]
        Alpine[Alpine.js Logic]
        Tailwind[TailwindCSS Classes]
    end

    subgraph BestPractices[Best Practices]
        Accessibility[ARIA Attributes]
        TestIDs[data-testid Attributes]
        Responsive[Responsive Classes]
        DarkMode[Dark Mode Support]
    end

    Blade --> Livewire
    Livewire --> Alpine
    Alpine --> Tailwind
    Tailwind --> BestPractices
```

#### Blade Component Example

```blade
{{-- resources/views/components/stat-bar.blade.php --}}
<div
    class="stat-bar"
    data-testid="stat-bar-{{ $stat }}"
    role="progressbar"
    aria-label="{{ ucfirst($stat) }} stat: {{ $value }}"
    aria-valuenow="{{ $value }}"
    aria-valuemin="0"
    aria-valuemax="1200"
>
    <div class="stat-bar__label">
        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ ucfirst($stat) }}
        </span>
        <span class="text-sm font-bold text-{{ $stat }} dark:text-{{ $stat }}-light">
            {{ $value }}
        </span>
    </div>
    <div class="stat-bar__track">
        <div
            class="stat-bar__fill bg-{{ $stat }}"
            style="width: {{ ($value / 1200) * 100 }}%"
        ></div>
    </div>
    <div class="stat-bar__grade">
        <x-grade-badge :value="$value" />
    </div>
</div>
```text

Stat display examples should treat `1200` as a contextual display ceiling or soft-cap reference, not
a universal hard cap for all mechanics, validation paths, or UI states.

#### Livewire Component Example

```php
<?php

namespace App\Livewire\Training;

use Livewire\Component;
use App\Services\TrainingPredictionService;

class TrainingSelector extends Component
{
    public $characterId;
    public $predictions = [];

    public function mount($characterId)
    {
        $this->characterId = $characterId;
        $this->loadPredictions();
    }

    public function loadPredictions()
    {
        $character = Character::findOrFail($this->characterId);
        $service = app(TrainingPredictionService::class);

        $this->predictions = $service->getPredictions($character);
    }

    public function selectTraining($facility)
    {
        $this->dispatch('training-selected', facility: $facility);
    }

    public function render()
    {
        return view('livewire.training.training-selector');
    }
}
```

### Testing Requirements

#### Visual Regression Testing

```javascript
// tests/e2e/wireframes/training-selector.spec.js
import { test, expect } from '@playwright/test';

test.describe('WF-004: Training Selection Interface', () => {
    test('renders training predictions correctly', async ({ page }) => {
        await page.goto('/characters/1/training');

        // Check header
        await expect(page.getByTestId('training-header')).toBeVisible();

        // Check prediction cards
        const predictions = page.getByTestId('prediction-card');
        await expect(predictions).toHaveCount(6);

        // Check AI recommendation badge
        await expect(page.getByTestId('ai-recommendation-badge')).toBeVisible();

        // Visual regression
        await expect(page).toHaveScreenshot('training-selector.png');
    });

    test('displays risk indicators correctly', async ({ page }) => {
        await page.goto('/characters/1/training');

        const riskBadge = page.getByTestId('risk-badge-high');
        await expect(riskBadge).toHaveClass(/bg-red/);
    });
});
```text

#### Accessibility Testing

```javascript
// tests/e2e/accessibility/training-selector.spec.js
import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

test.describe('WF-004: Accessibility', () => {
    test('should not have any automatically detectable accessibility issues', async ({ page }) => {
        await page.goto('/characters/1/training');

        const accessibilityScanResults = await new AxeBuilder({ page }).analyze();

        expect(accessibilityScanResults.violations).toEqual([]);
    });

    test('supports keyboard navigation', async ({ page }) => {
        await page.goto('/characters/1/training');

        await page.keyboard.press('Tab');
        await expect(page.getByTestId('prediction-card-speed')).toBeFocused();

        await page.keyboard.press('Enter');
        await expect(page).toHaveURL(/.*training-result/);
    });
});
```

---

## Wireframe Usage Workflow

### Design to Development Workflow

```mermaid
flowchart LR
    subgraph Design[Design Phase]
        Wireframe[Create Wireframe]
        Review[Review & Approve]
        Document[Document Specs]
    end

    subgraph Development[Development Phase]
        ComponentDev[Develop Component]
        Integration[Integrate Component]
        Testing[Test & Validate]
    end

    subgraph Delivery[Delivery Phase]
        QA[QA Validation]
        UAT[User Acceptance]
        Deploy[Deploy to Production]
    end

    Wireframe --> Review --> Document
    Document --> ComponentDev --> Integration --> Testing
    Testing --> QA --> UAT --> Deploy
```text

### Wireframe Import Process

1. **Designers**: Create wireframes using ASCII representations or design tools
2. **Documentation**: Convert designs to markdown format following template
3. **Review**: Submit for technical and product review
4. **Approval**: Obtain sign-off from stakeholders
5. **Development**: Developers implement based on specifications
6. **Validation**: QA validates against wireframe specifications
7. **Deployment**: Deploy to staging/production environments

---

## Related Documentation

### Core Documentation

| Document | Description |
| --- | --- |
| [SDP - Software Development Plan](../00-core-docs/001_SDP_Software_Development_Plan.md) | Project timeline and milestones |
| [BRS - Business Requirements](../00-core-docs/002_BRS_Business_Requirements_Specifications.md) | Business objectives and scope |
| [SRS - Software Requirements](../00-core-docs/003_SRS_Software_Requirement_Specifications.md) | Functional and non-functional requirements |
| [SDS - Software Design](../00-core-docs/004_SDS_Software_Design_Specifications.md) | System architecture and design |
| [SUM - Software User Manual](../00-core-docs/017_SUM_Software_User_Manual.md) | End-user documentation |

### Supplementary Documentation

| Document Set | Description |
| --- | --- |
| [PRDs (001-007)](../02-prds/000_PRDS_INDEX.md) | Product Requirement Documents |
| [SPECs (001-007)](../02-specs/000_SPECS_INDEX.md) | Technical Specifications |
| [Flows (001-007)](../01-flows/000_FLOWS_INDEX.md) | System Flow Diagrams |
| [Tech Flows (001-010)](../01-tech-flow/000_TECH_FLOW_INDEX.md) | Technical Flow Diagrams |
| [Sequences (001-017)](../01-sequences/000_SEQUENCE_DIAGRAMS_INDEX.md) | Sequence Diagrams |
| [User Flows (001-011)](../01-user-flows/000_USER_FLOW_DIAGRAMS_INDEX.md) | User Flow Diagrams |

---

## Maintenance and Updates

### Update Procedures

1. **Wireframe Changes**: Document changes with version history
2. **Component Updates**: Reflect changes in implementation notes
3. **Design System Changes**: Update design tokens and guidelines
4. **Accessibility Updates**: Document new WCAG requirements

### Version Control

| Version | Date | Author | Changes |
| --- | --- | --- | --- |
| 2.3.0 | 2026-03-08 | Development Team | Marked wireframes as under alignment review, added storage-aware guidance, updated Livewire version references, expanded accessibility and responsive interaction requirements, and refreshed related document coverage |
| 2.0.0 | 2026-01-24 | Development Team | Comprehensive update aligned with v2.0.0 implementation, added design system specifications, accessibility guidelines, and testing requirements |
| 1.0.0 | 2026-01-14 | Development Team | Initial wireframe specifications |

---

## Support and Resources

### Design Resources

- Review the aligned user-flow, tech-flow, and sequence docs before treating a wireframe as implementation-exact.
- Prefer repository-local component, route, and interaction references over raw external links when
documenting current behavior.
- Keep storage-mode caveats close to the affected screen rather than assuming Local and Account parity.

### Contact

For wireframe-related questions or contributions, update the affected wireframe alongside the
corresponding user-flow or tech-flow document so drift is visible in one review pass.

---

*This index reflects the wireframe specification set under active alignment review. Treat individual
wireframes as conceptual UI contracts unless the current route surface and persistence behavior are
verified in the aligned implementation docs.*
