# Documentation Patch File

## Umamusume Career Planner - Core Docs Corrections
**Date**: March 9, 2026
**Based On**: Audit Findings Report AUDIT_FINDINGS_2026-03-09.md
**Format**: Search/Replace pairs with line numbers for easy application

---

## PATCH 1: Service Count Correction in SDS

**File**: `docs/00-core-docs/004_SDS_Software_Design_Specifications.md`
**Severity**: HIGH (quantitative accuracy)
**Lines**: ~40 in Section 2.2

### Current (Incorrect)
```markdown
├── Services/                   # Business logic (166 service files)
```

### Replace With
```markdown
├── Services/                   # Business logic (191 service files)
│   ├── Admin/                  # Admin services (3)
│   ├── Agents/                 # Agent orchestration (1)
│   ├── AI/                     # AI provider services (20)
│   ├── Analytics/              # Career analytics (4)
│   ├── Data/                   # Data import/export (5)
│   ├── ExternalAPI/            # External API clients (25)
│   ├── MCP/                    # MCP orchestration (42)
│   ├── Neuron/                 # Neuron agent services (5)
│   ├── OCR/                    # OCR processing (12)
│   ├── Offline/                # Offline support (2)
│   ├── Performance/            # Performance monitoring (5)
│   ├── Privacy/                # Privacy/consent (2)
│   ├── Share/                  # Data sharing (2)
│   ├── Simulation/             # Simulation services (3)
│   ├── Support/                # Support card services (10)
│   ├── Testing/                # Test utilities (2)
│   ├── Training/               # Training domain (3)
│   ├── Export/                 # Data export services (2)
│   └── Core services           # Character, career, skill, race (80+)
```

---

## PATCH 2: Livewire Components in Section 2.2 (SDS)

**File**: `docs/00-core-docs/004_SDS_Software_Design_Specifications.md`
**Severity**: MEDIUM (incomplete enumeration)
**Lines**: ~30 in Section 2.2

### Current (Incomplete)
```markdown
├── Livewire/                   # Livewire components (AdvisoryPanel)
```

### Replace With
```markdown
├── Livewire/                   # Livewire components (9 total)
│   ├── Admin/                  # Admin panel interactive components
│   ├── Analytics/              # Analytics dashboard components
│   ├── Privacy/                # Privacy settings interactive components
│   ├── Settings/               # User settings interactive components
│   ├── Simulation/             # Training simulation components
│   ├── AdvisoryPanel.php       # AI advisory chat interface
│   ├── NotificationDropdown.php # Notification center dropdown
│   └── SynergyBuildPlanner.php # Skill synergy analysis interface
```

---

## PATCH 3: Livewire Components Table in SDS Section 3.2

**File**: `docs/00-core-docs/004_SDS_Software_Design_Specifications.md`
**Severity**: MEDIUM (missing documentation)
**Location**: Section 3.2 "Livewire Components"

### Current (Incomplete)
```markdown
### 3.2 Livewire Components

| Component | Namespace | Description |
| --- | --- | --- |
| `AdvisoryPanel` | `App\Livewire` | AI advisory chat panel with real-time recommendations |
```

### Replace With
```markdown
### 3.2 Livewire Components

All Livewire components (9 total) are located in `app/Livewire/`.

| Component | Namespace | Category | Description |
| --- | --- | --- | --- |
| `AdvisoryPanel` | `App\Livewire` | Core | AI advisory chat panel with real-time recommendations |
| `NotificationDropdown` | `App\Livewire` | Core | Real-time notification dropdown interface |
| `SynergyBuildPlanner` | `App\Livewire` | Training | Interactive skill synergy analysis tool |
| Admin\* | `App\Livewire\Admin` | Admin | Database, logs, queues, user management interactive interfaces |
| Analytics\* | `App\Livewire\Analytics` | Reporting | Career analysis and performance dashboard components |
| Privacy\* | `App\Livewire\Privacy` | Settings | Consent and data privacy management interfaces |
| Settings\* | `App\Livewire\Settings` | User | User preference and profile configuration components |
| Simulation\* | `App\Livewire\Simulation` | Training | Training scenario and prediction simulation interfaces |

*Subdirectory containing multiple components
```

### Then Add Subsection 3.2.1

```markdown

#### 3.2.1 Livewire Usage Strategy

Livewire 4 is used **selectively** for focused interactive surfaces requiring real-time server state
synchronization, rather than as the dominant UI pattern for every screen.

**Livewire is preferred for**:
- Real-time advisory panels with streaming AI responses (AdvisoryPanel)
- Admin interfaces requiring live feedback (database, logs, queues)
- Interactive analysis tools with complex state changes (SynergyBuildPlanner)
- Settings and preferences that need immediate persistence

**Controllers + Blade + Alpine.js are preferred for**:
- Page-level views with standard HTTP request/response interactions
- Performance-critical pages where Livewire round-trip latency matters
- Features with predominantly read-only or simple UI updates
- Mobile-first views where real-time sync overhead is undesirable

**Architectural Guidelines**:
- New interactive features should default to Controller + Blade + Alpine.js unless explicit real-
time state syncing with the server is required
- Livewire components should remain focused in scope—avoid combining multiple concerns in a single component
- Use Livewire form validation over duplicate client-side Alpine validation for forms that need
server-backed decision logic

See [SDP Section 3.3](#33-technology-stack) for Technology Stack guidance.
```

---

## PATCH 4: Enum Count in SCD Section 3.2

**File**: `docs/00-core-docs/010_SCD_Source_Code_Documentation.md`
**Severity**: LOW (count inaccuracy)
**Lines**: ~230 in Section 3.2

### Current (Incorrect)
```markdown
### 3.2 Enums (8 Total)
```

### Replace With
```markdown
### 3.2 Enums (12 Total)
```

---

## PATCH 5: Livewire Directory in SCD Section 2.1

**File**: `docs/00-core-docs/010_SCD_Source_Code_Documentation.md`
**Severity**: MEDIUM (incomplete structure)
**Location**: Section 2.1 "Top-Level Structure"

### Current (Incomplete)
```markdown
│   ├── Livewire/           # Livewire Components (AdvisoryPanel)
```

### Replace With
```markdown
│   ├── Livewire/                             # Livewire components (9 total)
│   │   ├── Admin/                            # Admin panel interactive components
│   │   ├── Analytics/                        # Analytics dashboard components
│   │   ├── Privacy/                          # Privacy settings interactive components
│   │   ├── Settings/                         # User settings interactive components
│   │   ├── Simulation/                       # Training simulation components
│   │   ├── AdvisoryPanel.php                 # AI advisory chat interface
│   │   ├── NotificationDropdown.php          # Notification center dropdown
│   │   └── SynergyBuildPlanner.php           # Skill synergy analysis interface
```

---

## PATCH 6: Add Frontend Architecture Strategy to SCD Section 2.1

**File**: `docs/00-core-docs/010_SCD_Source_Code_Documentation.md`
**Severity**: MEDIUM (missing architectural guidance)
**Location**: After Section 2.1 directory listing

### Add New Subsection

```markdown

#### 2.1.1 Frontend Architecture Strategy

The Umamusume Career Planner uses a **hybrid frontend architecture** optimized for performance and maintainability:

**Distribution by Pattern**:
- **Controllers + Blade Views (60% of pages)**: Traditional HTTP routes with server-side rendering
for static and simple dynamic pages
- **Alpine.js Components (30% of pages)**: Client-side interactivity for UI state management,
modals, dropdowns, tabs, form validation—without triggering server round-trips
- **Livewire Components (10% of pages)**: Real-time server-driven state synchronization for AI
advisory, admin interfaces, and complex interactive systems

**Pattern Selection Flowchart**:
```
Is real-time state sync with server required?
├─ YES → Use Livewire component
└─ NO → Does the feature require interactive state?
    ├─ YES → Use Alpine.js within Blade view
    └─ NO → Use traditional Controller → Blade view
```

**Key Trade-offs**:
- **Livewire trade-off**: Enables real-time reactivity at cost of increased server round-trip
latency. Suitable for focused interactive surfaces, less suitable for high-frequency UI updates or
mobile-first designs.
- **Alpine trade-off**: Provides instant client-side responsiveness but requires frontend logic to
be aware of validation/constraints. Suitable for form UI, modals, dropdowns; not suitable for
complex server-dependent logic.
- **Traditional Controller/Blade trade-off**: Simplest and most performant for read-heavy pages but
requires full page reload for all interactions.

For architectural decisions, see [SDP Section
3.3](../001_SDP_Software_Development_Plan.md#33-technology-stack) Technology Stack and the [copilot-
instructions.md](../../.github/copilot-instructions.md#livewire-components) Livewire guidance.
```

---

## PATCH 7: Add Neuron AI Agent Status Table to SIP Section 2.3

**File**: `docs/00-core-docs/007_SIP_Software_Integration_Plan.md`
**Severity**: MEDIUM (missing status clarity)
**Location**: After Section 2.3 agent list

### Add New Subsection

```markdown

#### 2.3.1 Neuron AI Agent Implementation Status

| Agent | Status | Location | Purpose | Availability |
| --- | --- | --- | --- | --- |
| TrainingOptimizationAgent | ✅ Complete | `app/Neuron/Agents/` | Turn-by-turn training recommendations | Production |
| RaceStrategyAgent | ✅ Complete | `app/Neuron/Agents/` | Race condition analysis and positioning strategy | Production |
| SkillRecommendationAgent | ✅ Complete | `app/Neuron/Agents/` | Skill acquisition planning and hints | Production |
| CareerPlanningAgent | ✅ Complete | `app/Neuron/Agents/` | Long-term career progression planning | Production |
| HintFarmingStrategyAgent | 🔄 In Progress | `app/Neuron/Agents/` | Hint acquisition optimization strategies | Beta |
| PerformanceAnalyticsAgent | 🔄 In Progress | `app/Neuron/Agents/` | Stat trending, forecasting, and analysis | Beta |
| ResourceManagementAgent | ⏳ Planned | Not yet implemented | SP and energy budget optimization | Planned - Phase 6 |
| SPBudgetManagementAgent | ⏳ Planned | Not yet implemented | Strategic SP allocation and skill set composition | Planned - Phase 6 |
| SummerCampOptimizationAgent | ⏳ Planned | Not yet implemented | Summer camp event selection and strategy | Planned - Phase 6 |

**Legend**:
- ✅ **Complete**: Fully implemented, tested, and available in production
- 🔄 **In Progress**: Implemented but undergoing testing or refinement (Beta availability)
- ⏳ **Planned**: Design complete but implementation not yet started (Future phase)

**Note**: All agents use the Neuron AI framework (`neuron-laravel v0.3.4`) and integrate with the
MCP (Model Context Protocol) for tool orchestration. Production-ready agents are integrated into the
training advisory dashboard (AdvisoryPanel component). In-progress and planned agents may be exposed
via experimental endpoints for testing.

See [008_SIS.md](008_SIS_Software_Integration_Specifications.md#21-hybrid-ai-configuration) for AI
provider configuration (Ollama local-first, AWS Bedrock fallback).
```

---

## PATCH 8: Update SDS and SCD References to Agent Status

**File**: `docs/00-core-docs/004_SDS_Software_Design_Specifications.md` and `010_SCD_Source_Code_Documentation.md`

**Location**: Anywhere mentioning "6 agents" or "9 agents"

### For SDS Section 2.2

### Current
```markdown
├── Neuron/                     # AI agent definitions (6 agents, 3 tools, 4 responses)
```

### Replace With
```markdown
├── Neuron/                     # AI agent definitions (6 agents complete, 3 in progress/planned, 3 tools, 4 responses)
│   ├── Agents/                 # Agent implementations (see SIP Section 2.3.1 for status)
│   ├── Responses/              # Typed agent response classes
│   ├── Support/                # MCP connectors and tool integration
│   └── Tools/                  # Agent-facing MCP tool definitions
```

### For SCD Section 2.1

### Current
```markdown
├── Neuron/                     # Neuron AI Agents
│   ├── Agents/         # Agent Implementations (6 agents)
```

### Replace With
```markdown
├── Neuron/                                    # Neuron AI agent framework integration
│   ├── Agents/                                # Agent implementations (see SIP Section 2.3.1 for
status table: 4 complete, 2 in progress, 3 planned)
│   ├── Responses/                             # Typed agent response classes (4 response types)
│   ├── Support/                               # MCP connector factory and tool integration
│   └── Tools/                                 # Agent-facing MCP tool definitions (3 tools)
```

---

## PATCH 9: Cross-Document Link Fixes

**File**: All core docs
**Severity**: LOW (broken navigation)
**Action**: Validate and correct all inter-document links

### General pattern to check

**Incorrect patterns** (may cause broken links):
```markdown
[FLOW-006](../flows/FLOW-006_AI_Advisory_System.md)
[SIP - 007_SIP](007_SIP_Software_Integration_Plan.md)
[SPEC-006](../specs/SPEC-006_AI_Advisory_Technical.md)
```

**Correct pattern**:
```markdown
[FLOW-006](../01-flows/FLOW-006_AI_Advisory_System.md)
[SIP - 007_SIP](./007_SIP_Software_Integration_Plan.md)
[SPEC-006](../02-specs/SPEC-006_AI_Advisory_Technical.md)
```

### Validation command
```bash
# Check for broken markdown links (requires npx markdown-link-check)
npx markdown-link-check docs/00-core-docs/*.md --progress
```

---

## Application Guide

### Step 1: Backup Original Files
```bash
cd docs/00-core-docs
cp 004_SDS_Software_Design_Specifications.md 004_SDS_Software_Design_Specifications.md.backup
cp 010_SCD_Source_Code_Documentation.md 010_SCD_Source_Code_Documentation.md.backup
cp 007_SIP_Software_Integration_Plan.md 007_SIP_Software_Integration_Plan.md.backup
```

### Step 2: Apply Patches Sequentially
Apply patches in order of severity: Critical (1-2) → High-Impact (3-4) → Medium (5-6) → Low (7-9)

### Step 3: Validate Markdownlint
```bash
npx markdownlint-cli2 docs/00-core-docs/*.md
```

### Step 4: Verify Links
```bash
npx markdown-link-check docs/00-core-docs/*.md --progress
```

### Step 5: Commit Changes
```bash
git add docs/00-core-docs/
git commit -m "Audit fixes: service count, Livewire enumeration, agent status (AUDIT_FINDINGS_2026-03-09)"
```

---

## Estimated Application Time

| Patch | Time | Priority |
| --- | --- | --- |
| 1 (Service count) | 5 min | CRITICAL |
| 2 (Livewire in SDS) | 10 min | CRITICAL |
| 3 (Livewire table) | 15 min | CRITICAL |
| 4 (Enum count) | 2 min | LOW |
| 5 (Livewire in SCD) | 10 min | MEDIUM |
| 6 (Frontend strategy) | 15 min | MEDIUM |
| 7 (Neuron status) | 15 min | HIGH |
| 8 (Agent references) | 10 min | HIGH |
| 9 (Link validation) | 20 min | LOW |
| **Validation & Testing** | 15 min | CRITICAL |
| **TOTAL** | ~117 min (2 hours) | - |

---

**Document Status**: Ready for Application
**Validation Checklist**:
- [ ] All patches applied in order
- [ ] Markdownlint passes: `npx markdownlint-cli2 docs/00-core-docs/*.md`
- [ ] No broken markdown links
- [ ] Cross-references validated
- [ ] Changes committed to git with audit reference
