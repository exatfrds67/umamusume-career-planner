---
applyTo: '**'
---

# Coding Preferences

- Follow Laravel 12 coding standards and best practices
- Use PHP 8.3+ strict types and type declarations
- Implement WCAG 2.2 AA accessibility compliance
- Prefer Pest testing framework over PHPUnit
- Use Laravel Pint for code formatting- **Markdownlint Compliance**: All markdown (`.md`) files MUST adhere to markdownlint standards (see section below)

## Project Architecture

- **Framework**: Laravel 12 (Released February 24, 2025)
- **Frontend**: Tailwind CSS v4 (Released January 22, 2025)
- **Database**: MySQL 8.0+ with Redis 7.0+ caching via WSL
- **AI Integration**: Hybrid Ollama local + AWS Bedrock cloud
- **External APIs**: umapyoi.net (primary), UmamusumeDB.com (verification pending)
- **Testing**: Pest v3 with PHPUnit v11
- **Code Quality**: Laravel Pint v1
- **Requirements**: 59 total requirements, 15+ database tables, 80%+ test coverage
- **Timeline**: 22-28 weeks development (6 phases)
- **Document Numbering**: 000-010, 017 (011-016 reserved/unused)

## Phase 4: Training Timeline & SP Management (✅ COMPLETE - 2026-01-29)

**5 Components Implemented (3 Alpine + 2 Blade View Components)**:

✅ **Alpine Components** (JavaScript State Management):

1. **trainingTimeline.js** (130 lines)
   - Turn navigation: nextTurn(), prevTurn(), goToTurn(number)
   - Swipe gestures: 50px threshold, left/right detection
   - State: currentTurn, totalTurns, turns[], progressPercentage
   - Computed: canGoForward, canGoBackward, currentTurnData, upcomingEventsCount
   - Events: Dispatches 'turn-changed' event on navigation
   - Color helpers: getTurnStatusColor(), getConditionColor(), getStatChangeColor()

2. **spAllocator.js** (250 lines)
   - Allocation management: allocateSP(), incrementAllocation(), clearAllocation()
   - History support: undo(), redo(), saveHistoryState() with stack
   - Auto-save: debouncedSave() with 1000ms debounce
   - Budget tracking: totalAllocated, remainingSP, isOverBudget, budgetStatus
   - Validation: validateAllocation(), Max SP enforcement
   - Distribution: distributeEvenly(), clearAllAllocations()
   - Events: allocation-changed, allocation-cleared, allocations-saved, allocation-undo/redo

3. **SkillLoadout Blade Component** (280 lines - Blade, not Alpine)
   - 3 variants: grid/list/compact
   - 3 sizes: sm/md/lg (20px/24px/32px)
   - Tier badges (S/A/B/C) with game colors
   - SP cost badges
   - Skill removal on hover (if editable)
   - Events: @skill-selected, @skill-removed, @loadout-reordered

✅ **Blade View Components**:

1. **training-timeline.blade.php** (200+ lines)
   - Header: Title, completion percentage badge
   - Progress bar with gradient
   - Navigation: Previous/Next buttons with disabled states
   - Turn details card: condition, stat gains grid, energy bar
   - Color-coded stats: green (+), red (-), gray (0)
   - Upcoming events warning
   - Swipe gesture hint for mobile
   - Full dark mode, WCAG 2.2 AA, responsive

2. **sp-allocator-interface.blade.php** (320+ lines)
   - Budget status card: Total/Allocated/Remaining with color coding
   - Quick actions: Distribute Evenly, Clear All
   - Undo/Redo buttons with history tracking
   - Allocated skills list with ±5 buttons and direct input
   - Unallocated skills grid (2-3 columns)
   - Auto-save indicator: Saving... / All saved / Unsaved changes
   - Over-budget warning with protection
   - Empty state messaging
   - Full dark mode, WCAG 2.2 AA, responsive

**Code Quality**:

- All files pass Laravel Pint formatting (PASS)
- WCAG 2.2 AA accessibility compliance
- Dark mode support with game-aligned colors
- Responsive design (mobile-first, 1-6 columns)
- Type-safe with explicit return declarations
- Semantic HTML with ARIA labels

**Git Commits** (3 total):

1. 80322c8 - Core components (trainingTimeline, spAllocator, SkillLoadout, app.js registration)
2. 750b550 - View components (training-timeline.blade.php, sp-allocator-interface.blade.php)
3. eb55584 - Documentation (PHASE_4_SUMMARY.md complete)

**Testing Status**:

- ✅ Code structure ready for Pest unit tests
- 🎯 Pest tests pending (allocation logic, history, validation)
- 🎯 Playwright E2E pending (swipe gestures, undo/redo, budget validation)
- ✅ Manual code review: structure and patterns validated

**Architecture Learnings**:

- Alpine.js pattern: Computed properties for derived state, event-driven communication
- Blade component pattern: @props array, x-data binding, @click handlers
- Gesture detection: 50px minimum threshold prevents accidental swipes
- Debounce strategy: 1000ms for save operations prevents API hammering
- Color patterns: Game-aligned stat colors maintained throughout (red/green/yellow/purple/blue)

---

## Game Alignment Planning (January 29, 2026)

**Strategic Planning Documents Created**:

- GAME_ALIGNMENT_STRATEGIC_PLAN.md - Comprehensive framework aligning app with game patterns (not 1:1 copy)
- GAME_VISUAL_INTERACTION_PATTERNS.md - Detailed visual design & interaction research from 120+ screenshots
- GAME_ALIGNMENT_DOCUMENTATION_INDEX.md - Master index for navigation between all alignment docs

**Key Decisions**:

- Use game-aligned stat colors (Red=Speed, Blue=Stamina, Yellow=Power, Green=Guts, Purple=Wit)
- 3 screen types: Grid/List, Detail/Tabs, Execution Flow (vs game's 2)
- Sidebar (desktop) + Bottom Nav (mobile) navigation
- 4-level component hierarchy: Pages → Sections → UI → Atomic
- 6-phase implementation roadmap (12 weeks)

**Color System Reference**:

- Stat colors: Red #EF4444, Blue #3B82F6, Yellow #EAB308, Green #22C55E, Purple #A855F7
- Condition colors: GREAT #10B981, GOOD #84CC16, NORMAL #6B7280, BAD #EF4444
- Resource colors: SP #F59E0B, Focus #06B6D4, Target #8B5CF6, Progress #10B981

## UI Component Library (Phase 2 Complete, Phase 3 In Progress)

- **Implementation Date**: 2026-01-29
- **Total Components**: 18 new components (Phase 2.1-2.3) + existing components
- **Test Coverage**: 155 tests passing, 386 assertions
- **Status**: ✅ Production-ready

### Phase 2: Component Development (Complete)

#### Phase 2.1: Stats Display Components (6 components)

- **StatBar** (existing) - Stat progress with soft cap indicators
- **GradeBadge** (existing) - S/A/B/C/D/E/F/G grade badges
- **AptitudeDisplay** - Turf/Dirt/Distance aptitude visualization (8 tests)
- **ProgressBar** - Generic progress indicator with color variants (10 tests)
- **TypeIcon** - Stat type icons with emoji support (9 tests)
- **StatRadarChart** - Pentagon radar chart for 5-stat visualization (14 tests)

#### Phase 2.2: Character Display Components (6 components)

- **CharacterPortrait** - Character image with fallback (9 tests)
- **StarRating** - 1-5 star rating display (5 tests)
- **PotentialBadge** - SS/S/A/B/C tier badges (5 tests)
- **CharacterProfile** - Comprehensive character card (7 tests)
- **MemoriesGrid** - Memory/achievement grid display (7 tests)

#### Phase 2.3: Career Status Components (6 components)

- **TurnCounter** - Turn-by-turn progress tracker (6 tests)
- **ConditionBadge** - Character condition status (8 tests)
- **EnergyGauge** - Energy level visualization (6 tests)
- **RaceDayBadge** - Race countdown/status indicator (7 tests)
- **GoalProgress** - Goal completion tracker (8 tests)
- **TraineeEventBanner** - Event/milestone announcements (6 tests)

### Phase 3: Real-World Integration (✅ COMPLETE - 2026-01-29)

**All 26 Components Implemented & Tested:**

✅ **Character Management** (4 components)

- CharacterPortrait, CharacterCard, CharacterProfile, Breadcrumb

✅ **Stats & Progress** (5 components)

- StatBar, StatRadarChart, AptitudeDisplay, ProgressBar, GoalProgress

✅ **UI Elements & Navigation** (7 components)

- TypeIcon, ConditionBadge, GradeBadge, StarRating, TurnCounter, TraineeEventBanner, RaceDayBadge

✅ **Skills & Upgrades** (3 components)

- SkillCard, HintLevelBadge, PotentialBadge

✅ **Support Cards & Inventory** (3 components)

- SupportCard, DeckSlot, MemoriesGrid

✅ **Race System** (2 components)

- RaceCard, SPCounter

✅ **Energy & Status** (2 components)

- EnergyGauge, BondMeter

✅ **Test Coverage**: 3633 tests passing, 14000+ assertions, 0 failures

**Integrated Production Views:**

1. **Character Detail Page** (`resources/views/characters/show.blade.php`)
   - ✅ CharacterPortrait, StatBar (5x), StatRadarChart, AptitudeDisplay
   - ✅ Fully tested and validated

2. **Training Show Page** (`resources/views/training/show.blade.php`)
   - ✅ TypeIcon, EnergyGauge, ConditionBadge
   - ✅ Fully tested and validated

**Phase 3 Artifacts:**

- Location: `docs/implementation/PHASE_3_FINAL_COMPLETION.md`
- Git Commit: 0816779 (develop branch)
- 314 files modified, +43,476 additions, -10,096 deletions
- Infrastructure: RaceConditionService + 5 database migrations
- **TraineeEventBanner** - Event notification banners (22 tests)

### Component Demo Integration

- **File**: `resources/views/components-demo.blade.php`
- **Sections Added**: Phase 2.1, 2.2, 2.3 showcases with all 18 components
- **Features**: Interactive examples, dark mode support, WCAG 2.2 AA compliance
- **Access**: Visit `/components-demo` to see all components in action

### Key Design Principles

- Game-aligned stat colors: Speed (blue-500), Stamina (green-500), Power (orange-500), Guts (amber-400), Wit (sky-500)
- SVG-based visualizations for StatRadarChart with pentagon math
- Emoji icons for visual clarity (🏁 race day, ⚠️ warnings, 🏆 achievements, etc.)
- Dark mode support across all components
- Keyboard navigation and WCAG compliance
- Default parameter values for flexible component usage
- Comprehensive test coverage for all logic paths

## Solutions Repository

- **Structured response mocking (NeuronAI)**: When mocking `AIProviderInterface::structured`, return an `AssistantMessage` containing JSON that matches the target schema/class instead of returning the DTO directly. The agent pipeline expects a `Message` instance; it will deserialize JSON into the response class via `processResponse()`.

## Service Worker Caching Issue

- **Problem**: Navigation to dashboard was redirected/cached, requiring hard refresh (Ctrl+Shift+R) due to missing or misconfigured service worker, especially aggressive in Chrome
- **Root Cause**: Service worker was being registered in the app but `public/sw.js` didn't exist, causing 404 errors and browser caching issues. Chrome was caching old broken service workers.
- **Solution**:
  1. Created `public/sw.js` with network-first strategy for navigation requests
  2. Added automatic old service worker cleanup in `resources/js/app.js`
  3. Created `SetCacheHeaders` middleware to prevent HTML page caching
  4. Added `updateViaCache: "none"` to service worker registration
- **Files Modified**:
  - Created: `public/sw.js`, `app/Http/Middleware/SetCacheHeaders.php`
  - Modified: `resources/js/app.js`, `bootstrap/app.php`
- **Strategy**: Network-first for HTML (always fresh when online), cache-first for static assets (images, fonts), automatic cache cleanup, no-cache headers for HTML
- **Result**: On next page load, all old service workers are unregistered, caches cleared, and fresh service worker installed

## Alpine.js Script Timing Issue

- **Problem**: Profile page failed to load because `profileManager()` Alpine.js function was defined inside `@section('content')`, making it unavailable when Alpine.js tried to initialize `x-data="profileManager()"` on line 6
- **Solution**: Move the `<script>` tag from inside `@section('content')` to use `@push('scripts')` so it executes after Alpine.js loads
- **File Fixed**: [resources/views/profile/show.blade.php](resources/views/profile/show.blade.php)
- **Result**: Profile page now loads correctly, all 18 profile tests pass

## External API Frontend Integration

- **Implementation Date**: 2026-01-25
- **APIs Connected**: umapyoi.net (characters, support cards, news)
- **Files Created**:
  - `app/Http/Controllers/Api/ExternalDataController.php` - API controller for fetching external data
  - `resources/views/external-data/browse.blade.php` - Browse page with Alpine.js
  - `tests/Feature/Api/ExternalDataControllerTest.php` - Integration tests (6/7 passing)
- **Files Modified**:
  - `routes/api.php` - Added `/api/external/*` endpoints
  - `routes/web.php` - Added `/external-data/browse` route
  - `resources/views/characters/index.blade.php` - Added "Browse External Data" button
- **Features**:
  - Real-time data fetching from umapyoi.net
  - Tabbed interface (Characters / Support Cards / News)
  - Client-side search/filtering with Alpine.js
  - API status checking and caching
  - Responsive grid layout with Tailwind CSS v4
- **API Endpoints**: All return JSON, require auth:sanctum
  - `GET /api/external/characters` - Fetch all characters
  - `GET /api/external/support-cards` - Fetch all support cards
  - `GET /api/external/news?limit=10` - Fetch latest news
  - `GET /api/external/status` - Check API availability
  - `POST /api/external/clear-cache` - Clear cached data
- **Access**: Navigate to Characters page → Click "Browse External Data" button
- **Documentation**: [docs/external-api-integration/FRONTEND_INTEGRATION_SUMMARY.md](../docs/external-api-integration/FRONTEND_INTEGRATION_SUMMARY.md)

## Dark Mode Flash/Mismatch on Page Load

- **Problem**: Page loads with light mode briefly, then switches to dark mode (or vice versa), requiring page refresh or Ctrl+Shift+R to resolve
- **Root Causes**:
  1. Theme detection and application was happening in JavaScript (app.js) which runs AFTER the page renders
  2. TWO competing theme systems (ThemeSystem.js and settings.js) causing race conditions and conflicts
  3. Theme toggle button wasn't properly synchronized across both systems
- **Solution**:
  1. Added synchronous inline `<script>` in `<head>` that runs BEFORE CSS loads:
     - Checks localStorage for user's theme preference
     - Falls back to system preference via `prefers-color-scheme` media query
     - Immediately applies `dark` class to `<html>` element
     - All CSS scoped to `html.dark` selector, so theme applies instantly
  2. Refactored ThemeSystem.js to be the single source of truth:
     - Removed `getStoredTheme()` redundancy
     - Simplified `applyTheme()` to check current state before DOM manipulation
     - Added `updateToggleButtonState()` to sync button state
     - Removed duplicate settings page update logic from keyboard shortcut
  3. Modified settings.js to delegate to ThemeSystem:
     - Theme button handlers now call `window.themeSystem.applyTheme()`
     - Kept fallback `applyTheme()` for when ThemeSystem unavailable
     - Added `updateThemeButtonUI()` helper for consistent button styling
     - Added initial sync to reflect current theme on page load
- **Files Modified**:
  - Modified: `resources/views/layouts/app.blade.php` (added inline theme script)
  - Modified: `resources/views/layouts/guest.blade.php` (added inline theme script)
  - Modified: `resources/js/core/ThemeSystem.js` (consolidated to single source of truth)
  - Modified: `resources/js/settings.js` (delegate to ThemeSystem, remove duplicates)
- **Why This Works**:
  - Inline script applies theme BEFORE CSS loads → no flash
  - Single ThemeSystem is source of truth → no race conditions
  - All UI components sync to same system → no mismatch
  - Proper initialization order ensures button state reflects actual theme
- **Result**: No more light/dark mode mismatch on page load - theme applies immediately and consistently across all components!

## Documentation Consistency

- **Problem**: Mixed date formats and inconsistent cross-references across documentation
- **Solution**: Standardize all dates to "Month DD, YYYY" format, add cross-references between planning documents
- **Result**: Achieved 10/10 (100%) documentation consistency

## Product Requirement Documents

- **Problem**: PRD folder missing from docs
- **Solution**: Recreated docs/prds with PRD-001..007 (module-level product requirements) and added to 000_DOCUMENT_INDEX with dependency links
- **Result**: PRDs visible in docs/prds and referenced in catalog/dependency matrix

## API Verification Status

- **Problem**: Inconsistent terminology for API verification status
- **Solution**: Standardize to "verification pending" for unverified APIs, "verified active" with date for verified
- **Avoid**: Don't use "requires verification" - use "verification pending" instead

## Requirements Count Standardization

- **Problem**: Diagram documentation referenced "60 comprehensive requirements" or "48 requirements" inconsistently
- **Solution**: Standardized all documentation to reference "59 requirements" consistently
- **Files Updated**: data-flow-diagram.md, decision-tree-flow-diagrams.md, entity-relationship-diagram.md, system-process-flow-diagrams.md, user-workflow-diagrams.md
- **Result**: All diagram documentation now aligned with main system documentation

## API Testing & Coverage

- **Total API Routes**: 396 registered and functional
- **Test Coverage**: 3,316 tests passing with 11,563 assertions (increased from original 3,284/11,090)
- **API Endpoint Coverage Test**: 32 test groups, all passing with 473 assertions
- **Endpoint Groups**: 15 major categories (Auth, Characters, Careers, Skills, AI/Neuron, MCP, Monitoring, Data Management, etc.)
- **Security**: 86% routes protected with `auth:sanctum` middleware, all routes have `throttle:api` rate limiting
- **Test Duration**: ~9 minutes for full suite
- **Documentation**: Created comprehensive API testing report at `docs/testing/API_TESTING_REPORT.md`
- **Status**: All endpoints verified operational and production-ready
- **Model Fix**: UmaMusume model doesn't exist - use `Character` model instead for character-related operations
- **Validation Handling**: Some routes require parameters (e.g., character_id, skill_id) and correctly return 422 validation errors when omitted

## RAG (Retrieval-Augmented Generation) Implementation

- **Implementation Date**: 2026-01-27
- **Status**: Production-ready, all tests passing (78 AI tests, 314 assertions)
- **Purpose**: Enhance AI responses with curated game knowledge from markdown documentation

### Architecture

- **VectorStoreService**: Core RAG engine with OpenAI embeddings (`text-embedding-3-small`)
- **HybridAIService**: Enhanced with knowledge retrieval before LLM routing
  - **IMPORTANT**: Constructor requires 5 parameters: MCPClient, Ollama, Bedrock, PerformanceMonitor, **VectorStoreService**
  - **Test Mocking**: Always include all 5 dependencies when mocking in tests
  - **Example**: `new HybridAIService($mcpClient, $ollamaService, $bedrockService, $performanceMonitor, $vectorStore)`
- **Knowledge Base**: Structured markdown docs in `storage/knowledge-base/`
  - `game-mechanics/stat-system.md` - Stats, breakpoints, aptitudes
  - `game-mechanics/training-system.md` - Training types, support cards
  - `game-mechanics/skill-system.md` - SP management, skill optimization
- **Frontend**: Purple "Knowledge" badge shows when RAG enhanced, lists source files

### Key Features

- Cosine similarity search with 0.7 threshold for relevance
- Keyword fallback when OpenAI API unavailable (graceful degradation)
- Smart caching: Documents 24h, embeddings 7 days (Redis)
- Source attribution: Shows which markdown files were used

## Phase 5: Race Planning & Analytics (✅ COMPLETE - 2026-01-29)

**6 Components Implemented (1 Alpine + 5 Blade View Components)**:

✅ **Blade Components**:

1. **line-chart.blade.php** (323 lines) - Chart.js integration
   - Multi-dataset support with configurable colors
   - Data summary: Current, Average, Peak values
   - Responsive canvas with animations
   - Dark mode support
   - Props: title, data[], labels[], colors[], height, animated, responsive

2. **class-pyramid.blade.php** (320 lines) - Fan hierarchy visualization
   - 3 variants: pyramid, bars, cards
   - Grade tiers: G1 (red) / G2 (orange) / G3 (yellow) / Listed (green) / Open (blue)
   - Total fanbase summary with percentages
   - Alpine data: sortedGrades, totalFans, maxFans, averageFans
   - Props: title, grades[], variant, height

3. **activity-timeline.blade.php** (380 lines) - Event timeline display
   - 3 variants: timeline (vertical), feed (social), compact (minimal)
   - Event types: race, skill, milestone, achievement
   - Time-ago calculation, pagination, metadata display
   - Alpine data: displayedEvents, itemsPerPage, currentPage, getTimeAgo(), formatMetadata()
   - Props: title, events[], variant, maxEvents

✅ **View Components** (Alpine + Blade combined):

1. **races/calendar.blade.php** (400+ lines) - Race carousel
   - Navigation: nextRace(), prevRace(), goToRace(index)
   - Filtering: filterByType(type), filterByMonth(month)
   - Swipe support: handleTouchStart/End with 50px threshold
   - States: currentRaceIndex, activeTypeFilter, activeMonthFilter
   - Computed: currentRace, filteredRaces, canGoForward, canGoBackward, raceProgress, upcomingRaces
   - Color helpers: getRaceStatusColor(), getGradeColor(), getDistanceLabel(), getRaceTypeIcon()

2. **races/targets.blade.php** (290 lines) - Race targeting interface
   - Multi-select with grade filtering
   - Race timeline with turn assignment
   - Grade distribution breakdown, fan projection
   - Alpine data: selectedRaces[], raceTurns{}, filterGrade
   - Computed: filteredRaces, totalProjectedFans, getGradeCount()

✅ **Alpine Component**:

1. **race-calendar.js** (140 lines)
   - State: races[], currentRaceIndex, filterType, selectedMonth, touchStartX/End
   - Same methods as calendar view
   - Exported as `export function raceCalendar()`

**Dashboard Enhancement**:

- Added Analytics section with 3-column grid
- Integrated line-chart, class-pyramid, activity-timeline
- Updated dashboard.blade.php (+78 lines)

**Code Quality**:

- All files pass Pint formatting (PASS)
- WCAG 2.2 AA accessibility throughout
- Full dark mode support
- 1,100+ lines of production code
- 3 comprehensive commits

**Key Patterns**:

- Alpine data functions with computed properties
- Blade components with responsive grids
- Chart.js CDN integration
- Swipe gesture detection (50px threshold)
- Color-coded grade system (G1-Open)
- Time-ago date formatting
- Event type filtering and metadata

## Phase 4 Roadmap (Next)

**Date**: 2026-01-29 onwards
**Scope**: Complete UI view integrations using Phase 3 components
**Target**: All major views using the component library

### Phase 4 High-Priority Tasks (Revised to Phase 6)

1. **Dashboard Integration** (Highest visibility, main entry point)
   - Components: CharacterCard, StatsSnapshot, MoodEnergyWidget, QuickActions
   - Views: `resources/views/dashboard.blade.php`
   - Expected: 4-6 new component integrations

2. **Career Planning Views**
   - Components: GoalProgress, TurnCounter, ProgressBar
   - Views: Career planning tabs and workflow
   - Expected: 3-4 new component integrations

3. **Race System Views**
   - Components: RaceCard, RaceDayBadge, ConditionBadge
   - Views: Race calendar, race preparation, race results
   - Expected: 3-5 new component integrations

4. **Skills Management Views**
   - Components: SkillCard, HintLevelBadge, PotentialBadge, SPCounter
   - Views: Skill shop, loadout manager, evolution tracker
   - Expected: 4-6 new component integrations

5. **Support Card System**
   - Components: SupportCard, DeckSlot, MemoriesGrid, BondMeter
   - Views: Card collection, deck builder, memories gallery
   - Expected: 4-5 new component integrations

- Cost-effective: ~$0.06/month for 30k queries (mostly cached)

### RAG Trigger Keywords

Game mechanics: stat, speed, stamina, power, guts, wit, training, skill, race, aptitude, support card
Questions: how, why, what, when, which, should
Strategy: strategy, build, optimal, best, breakpoint
Resources: sp, energy, mood, bond, hint

### Documentation

- Implementation summary: `docs/implementation-summaries/RAG_IMPLEMENTATION_SUMMARY.md`
- Neuron RAG guide: `docs/neuron/rag.md`

### Test Fix History

- **2025-01-23**: Fixed HybridAIServiceTest constructor - added VectorStoreService as 5th parameter
  - All 10 tests now passing (previously failed with ArgumentCountError)
  - Total: 78 AI tests passing, 314 assertions
  - AIChatController now derives `rag_enhanced` and `knowledge_sources` from context **or** execution response and includes them in streaming metadata and conversation logs; added reflection-based test `RAGEnhancedChatTest::propagates rag metadata from execution response`
Next time, group git commits instead of one large commit.
Commands execute in PowerShell/Command Prompt on Windows 10; use WSL2 for Linux commands.

---

## Markdownlint Standards (MANDATORY)

**CRITICAL**: All markdown files in the `docs/` directory and content updates MUST maintain 100% markdownlint compliance.

### Why Markdownlint?

- Ensures consistent markdown formatting across 477 documentation files
- Prevents table alignment issues, broken links, duplicate headings
- Maintains professional documentation quality
- Enables automated documentation validation and CI/CD integration

### Key Rules (Most Common)

**MD060/table-column-style** (Most Critical)

- **Requirement**: All markdown tables must have consistent pipe spacing
- **Fix**: Ensure spaces around ALL pipes: `| cell |` not `|cell|`
- **Separator rows**: `| --- | --- |` not `|---|---|`
- **Empty cells**: `| |` (single space between pipes)
- **All rows**: Must have same number of columns
- **Emoji handling**: For emoji cells (✅, ❌, ⚠️), use compact style to avoid alignment issues

#### MD024/no-duplicate-heading

- Two or more headings with identical text in same document
- Fix: Make each heading unique by appending context (e.g., `## Overview - Character` vs `## Overview - Training`)
- Do not remove headings; disambiguate instead

#### MD036/no-emphasis-as-heading

- Bold or italic text appearing alone on a line used as heading
- Bad: Line with just `**Bold Text**`
- Fix: Convert to proper heading: `### Bold Text` (use appropriate level)

#### MD051/link-fragments

- Links reference non-existent anchor fragments
- Bad: `[Click here](#invalid-anchor)` when no matching `## Invalid Anchor` exists
- Fix: Use correct fragment slugs matching actual headings

#### MD056/table-column-count

- Table rows have inconsistent number of columns
- Fix: Ensure all rows (header, separator, data) have same pipe/column count

#### MD003/heading-style

- Inconsistent heading style (ATX vs setext)
- Fix: Use ATX style consistently (`## Heading` not `Heading\n-------`)

#### MD031/blanks-around-fences

- Code blocks not surrounded by blank lines
- Fix: Add blank line before `\`\`\`code` and after closing `\`\`\``

#### MD047/single-trailing-newline

- File doesn't end with exactly one newline
- Fix: Ensure file ends with single `\n`

### When Updating Existing Documents

**ESPECIALLY IMPORTANT** - When modifying existing markdown files:

1. **Always run markdownlint first**

   ```bash
   npx markdownlint-cli2 path/to/file.md
   ```

2. **Fix any pre-existing issues in modified section** before adding new content

3. **Verify new content follows standards** - ALL tables, headings, links must be valid

4. **Use markdownlint auto-fix for simple issues**

   ```bash
   npx markdownlint-cli2 --fix path/to/file.md
   ```

5. **Test comprehensively after edits**

   ```bash
   npx markdownlint-cli2 "docs/**/*.md"
   ```

### Creating New Documentation

When creating new `.md` files:

1. **Start with template structure** following existing docs (e.g., `docs/02-specs/` for specs)
2. **Use only valid markdown**:
   - ATX-style headings: `## Heading Level 2`
   - Fenced code blocks (not indented): Use ` ```language`
   - Compact-style tables: `| value | value |` with `| --- |` separators
3. **No duplicate headings** in same document
4. **No emphasis-as-heading** - use proper `###` syntax
5. **All links must be valid** - test anchors exist
6. **One newline at end of file**
7. **No trailing spaces** in any line

### Common Fixes

#### Table pipes missing spaces

```markdown
# BEFORE (WRONG - 3 MD060 errors)
|Column1|Column2|
|---|---|
|val1|val2|

# AFTER (CORRECT - 0 errors)
| Column1 | Column2 |
| --- | --- |
| val1 | val2 |
```

#### Duplicate headings

```markdown
# BEFORE (WRONG - 2 MD024 errors)
## Overview
...
## Overview

# AFTER (CORRECT - 0 errors)
## Overview - Part A
...
## Overview - Part B
```

#### Emphasis as heading

```markdown
# BEFORE (WRONG - MD036 error)
**Important Section Title**
Content here...

# AFTER (CORRECT - 0 errors)
### Important Section Title
Content here...
```

### Automated Validation

- **Local**: Run `npx markdownlint-cli2 "docs/**/*.md"` before committing
- **Pre-commit**: Git hook can validate on `git commit`
- **CI/CD**: Can be integrated into GitHub Actions for automated checking
- **Results JSON**: `docs/lint-results.json` contains structured validation results

### Parser Script

- **Location**: `scripts/parse-markdownlint.cjs`
- **Purpose**: Converts raw markdownlint output to structured JSON grouped by directory
- **Usage**: `node scripts/parse-markdownlint.cjs input.txt output.json`

### Zero-Error Target

- **Current Status (2026-02-22)**: 477 files, 0 markdownlint errors ✅
- **Maintenance**: Every documentation update MUST maintain zero-error status
- **Non-negotiable**: Do not commit markdown files with markdownlint errors
