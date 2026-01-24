# WIREFRAMES & UI SPECIFICATIONS INDEX

**Document Version**: 2.0.0  
**Date**: January 24, 2026  
**Status**: Current - Aligned with v2.0.0 Implementation

---

## Overview

This index provides a comprehensive catalog of wireframe specifications for the Umamusume Pretty Derby Career Planner application. Wireframes define the user interface layout, component hierarchy, interaction patterns, and design specifications for all major screens and workflows.

**Design Philosophy**: Mobile-first responsive design with accessibility compliance (WCAG 2.2 AA), dark mode support, and progressive disclosure principles.

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

### Intended Audience

| Audience | Use Case |
|----------|----------|
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
```

---

## Wireframe Categories

### 1. Character Management Screens (WF-001 to WF-003)

| Document | Title | Status | Priority |
|----------|-------|--------|----------|
| [WF-001](WF-001_Dashboard_Overview.md) | Dashboard Overview | ✅ Complete | P0 |
| [WF-002](WF-002_Character_Creation_Wizard.md) | Character Creation Wizard | ✅ Complete | P0 |
| [WF-003](WF-003_Character_Detail_Management.md) | Character Detail & Management | ✅ Complete | P0 |

**Coverage**: Dashboard, character creation flow, character detail view

**Related Artifacts**:

- PRD: [PRD-001 Character Management](../prds/PRD-001_Character_Management.md)
- SPEC: [SPEC-001 Character Management Technical](../specs/SPEC-001_Character_Management_Technical.md)
- Flow: [FLOW-001 Character Management System](../flows/FLOW-001_Character_Management_System.md)
- User Flow: [UF-001 Dashboard Navigation](../user-flows/UF-001_Dashboard_Navigation_Flow.md)

---

### 2. Training Optimization Screens (WF-004 to WF-005)

| Document | Title | Status | Priority |
|----------|-------|--------|----------|
| [WF-004](WF-004_Training_Selection_Interface.md) | Training Selection Interface | ✅ Complete | P0 |
| [WF-005](WF-005_Training_Result_Screen.md) | Training Result Screen | ✅ Complete | P0 |

**Coverage**: Training prediction display, AI recommendations, result processing

**Related Artifacts**:

- PRD: [PRD-002 Training Optimization](../prds/PRD-002_Training_Optimization.md)
- SPEC: [SPEC-002 Training Optimization Technical](../specs/SPEC-002_Training_Optimization_Technical.md)
- Flow: [FLOW-002 Training Optimization System](../flows/FLOW-002_Training_Optimization_System.md)
- Tech Flow: [TECH-FLOW-002 Training Optimization](../tech-flow/TECH-FLOW-002_Training_Optimization_Flow.md)
- Sequence: [SEQ-002 Training Block Resolution](../sequences/SEQ-002_Training_Block_Resolution.md)
- User Flow: [UF-003 Training Day Flow](../user-flows/UF-003_Training_Day_Flow.md)

---

### 3. Race Strategy Screens (WF-006 to WF-007)

| Document | Title | Status | Priority |
|----------|-------|--------|----------|
| [WF-006](WF-006_Race_Calendar_View.md) | Race Calendar View | ✅ Complete | P0 |
| [WF-007](WF-007_Race_Preparation_Screen.md) | Race Preparation Screen | ✅ Complete | P0 |

**Coverage**: Race scheduling, readiness assessment, strategy recommendations

**Related Artifacts**:

- PRD: [PRD-003 Race Strategy](../prds/PRD-003_Race_Strategy.md)
- SPEC: [SPEC-003 Race Strategy Technical](../specs/SPEC-003_Race_Strategy_Technical.md)
- Flow: [FLOW-003 Race Strategy System](../flows/FLOW-003_Race_Strategy_System.md)
- Tech Flow: [TECH-FLOW-003 Race Strategy](../tech-flow/TECH-FLOW-003_Race_Strategy_Flow.md)
- Sequence: [SEQ-004 Race Registration and Outcome](../sequences/SEQ-004_Race_Registration_and_Outcome.md)
- User Flow: [UF-004 Race Day Flow](../user-flows/UF-004_Race_Day_Flow.md)

---

### 4. Skill Management Screens (WF-008 to WF-009)

| Document | Title | Status | Priority |
|----------|-------|--------|----------|
| [WF-008](WF-008_Skill_Shop_Interface.md) | Skill Shop Interface | ✅ Complete | P0 |
| [WF-009](WF-009_Skill_Loadout_Manager.md) | Skill Loadout Manager | ✅ Complete | P0 |

**Coverage**: Skill catalog, SP management, hint tracking, loadout optimization

**Related Artifacts**:

- PRD: [PRD-004 Skill Management](../prds/PRD-004_Skill_Management.md)
- SPEC: [SPEC-004 Skill Management Technical](../specs/SPEC-004_Skill_Management_Technical.md)
- Flow: [FLOW-004 Skill Management System](../flows/FLOW-004_Skill_Management_System.md)
- Tech Flow: [TECH-FLOW-004 Skill Management](../tech-flow/TECH-FLOW-004_Skill_Management_Flow.md)
- Sequence: [SEQ-003 Skill Acquisition and Upgrade](../sequences/SEQ-003_Skill_Acquisition_and_Upgrade.md)
- User Flow: [UF-005 Skill Management Flow](../user-flows/UF-005_Skill_Management_Flow.md)

---

### 5. Support Card System Screens (WF-010 to WF-011)

| Document | Title | Status | Priority |
|----------|-------|--------|----------|
| [WF-010](WF-010_Support_Card_Collection.md) | Support Card Collection | ✅ Complete | P0 |
| [WF-011](WF-011_Support_Deck_Builder.md) | Support Deck Builder | ✅ Complete | P0 |

**Coverage**: Card inventory, meta tier display, deck composition, synergy scoring

**Related Artifacts**:

- PRD: [PRD-005 Support Card Management](../prds/PRD-005_Support_Card_Management.md)
- SPEC: [SPEC-005 Support Card Management Technical](../specs/SPEC-005_Support_Card_Management_Technical.md)
- Flow: [FLOW-005 Support Card Management System](../flows/FLOW-005_Support_Card_Management_System.md)
- Tech Flow: [TECH-FLOW-005 Support Card Management](../tech-flow/TECH-FLOW-005_Support_Card_Management_Flow.md)
- Sequence: [SEQ-005 Support Card Upgrade](../sequences/SEQ-005_Support_Card_Upgrade.md)
- User Flow: [UF-006 Support Deck Building](../user-flows/UF-006_Support_Deck_Building_Flow.md)

---

### 6. AI Advisory System (WF-012)

| Document | Title | Status | Priority |
|----------|-------|--------|----------|
| [WF-012](WF-012_AI_Advisor_Interface.md) | AI Advisor Interface | ✅ Complete | P1 |

**Coverage**: AI chat interface, recommendation display, provider routing, cost tracking

**Related Artifacts**:

- PRD: [PRD-006 AI Advisory](../prds/PRD-006_AI_Advisory.md)
- SPEC: [SPEC-006 AI Advisory Technical](../specs/SPEC-006_AI_Advisory_Technical.md)
- Flow: [FLOW-006 AI Advisory System](../flows/FLOW-006_AI_Advisory_System.md)
- Tech Flow: [TECH-FLOW-006 AI Advisory](../tech-flow/TECH-FLOW-006_AI_Advisory_Flow.md)
- Sequence: [SEQ-006 AI Advice Generation](../sequences/SEQ-006_AI_Advice_Generation.md)
- User Flow: [UF-007 AI Advisor Journey](../user-flows/UF-007_AI_Advisor_Journey.md)
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
|-------|------------|-----------|-------|
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
|-------|------|-------------|-------|
| `--text-xs` | 0.75rem | 1rem | Captions, labels |
| `--text-sm` | 0.875rem | 1.25rem | Body small, secondary text |
| `--text-base` | 1rem | 1.5rem | Body text |
| `--text-lg` | 1.125rem | 1.75rem | Subheadings |
| `--text-xl` | 1.25rem | 1.75rem | Headings |
| `--text-2xl` | 1.5rem | 2rem | Page titles |
| `--text-3xl` | 1.875rem | 2.25rem | Hero headings |

#### Spacing Scale

| Token | Value | Usage |
|-------|-------|-------|
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
```

| Breakpoint | Width | Layout Strategy |
|------------|-------|-----------------|
| **Mobile** | < 640px | Single column, bottom nav, collapsible sections |
| **Tablet** | 640-1024px | Two columns where appropriate, sidebar toggle |
| **Desktop** | 1024-1280px | Full layout with sidebar, multi-column grids |
| **Wide** | > 1280px | Maximum content width applied, generous spacing |

### Accessibility Guidelines

#### WCAG 2.2 AA Compliance

| Guideline | Requirement | Implementation |
|-----------|-------------|----------------|
| **1.1.1 Non-text Content** | Alt text for images | All `<img>` tags have descriptive `alt` attributes |
| **1.4.3 Contrast Ratio** | 4.5:1 for normal text | Design tokens enforce minimum contrast |
| **2.1.1 Keyboard Navigation** | All interactive elements focusable | `tabindex` and focus management |
| **2.4.7 Focus Visible** | Visible focus indicators | CSS focus states with high contrast |
| **4.1.2 Name, Role, Value** | ARIA labels on controls | ARIA attributes on all interactive elements |
| **2.3.3 Animation Control** | Respect `prefers-reduced-motion` | CSS media queries for animations |

#### Keyboard Shortcuts

| Action | Shortcut | Context |
|--------|----------|---------|
| Save | `Ctrl + S` | All editor screens |
| Cancel | `Esc` | Modals, dialogs |
| Search | `/` | Global navigation |
| Help | `?` | Show keyboard shortcuts |
| Navigate | `Tab` / `Shift + Tab` | Focus traversal |

---

## Implementation Guidelines

### Technology Stack

| Layer | Technology | Version | Purpose |
|-------|------------|---------|---------|
| **Framework** | Laravel | 12+ | Backend framework |
| **Frontend Reactivity** | Livewire | 3 | Server-driven UI |
| **Client Interactivity** | Alpine.js | Latest | Client-side interactions |
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
```

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
```

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
```

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
|----------|-------------|
| [SDP - Software Development Plan](../001_SDP_Software_Development_Plan.md) | Project timeline and milestones |
| [BRS - Business Requirements](../002_BRS_Business_Requirements_Specifications.md) | Business objectives and scope |
| [SRS - Software Requirements](../003_SRS_Software_Requirement_Specifications.md) | Functional and non-functional requirements |
| [SDS - Software Design](../004_SDS_Software_Design_Specifications.md) | System architecture and design |
| [SUM - Software User Manual](../017_SUM_Software_User_Manual.md) | End-user documentation |

### Supplementary Documentation

| Document Set | Description |
|--------------|-------------|
| [PRDs (001-007)](../prds/000_PRDS_INDEX.md) | Product Requirement Documents |
| [SPECs (001-007)](../specs/000_SPECS_INDEX.md) | Technical Specifications |
| [Flows (001-007)](../flows/000_FLOWS_INDEX.md) | System Flow Diagrams |
| [Tech Flows (001-007)](../tech-flow/000_TECH_FLOW_INDEX.md) | Technical Flow Diagrams |
| [Sequences (001-015)](../sequences/000_SEQUENCE_DIAGRAMS_INDEX.md) | Sequence Diagrams |
| [User Flows (001-008)](../user-flows/000_USER_FLOW_DIAGRAMS_INDEX.md) | User Flow Diagrams |

---

## Maintenance and Updates

### Update Procedures

1. **Wireframe Changes**: Document changes with version history
2. **Component Updates**: Reflect changes in implementation notes
3. **Design System Changes**: Update design tokens and guidelines
4. **Accessibility Updates**: Document new WCAG requirements

### Version Control

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 2.0.0 | 2026-01-24 | Development Team | Comprehensive update aligned with v2.0.0 implementation, added design system specifications, accessibility guidelines, and testing requirements |
| 1.0.0 | 2026-01-14 | Development Team | Initial wireframe specifications |

---

## Support and Resources

### Design Resources

- **Figma Community**: [Umamusume Career Planner Components](https://figma.com)
- **TailwindCSS v4**: [Official Documentation](https://tailwindcss.com)
- **WCAG 2.2**: [Web Content Accessibility Guidelines](https://www.w3.org/WAI/WCAG22/quickref/)

### Contact

For wireframe-related questions or contributions:

- **Documentation Team**: <documentation@umacareerplanner.dev>
- **GitHub Issues**: [Report Issues](https://github.com/org/repo/issues)
- **Design Discussions**: [Community Forum](https://community.umacareerplanner.dev)

---

*This index reflects the current wireframe specifications for Umamusume Career Planner v2.0.0. All wireframes are aligned with implemented features and design system guidelines.*
