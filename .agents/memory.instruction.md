---
applyTo: '**'
---

# Coding Preferences

- Follow Laravel 12 coding standards and best practices
- Use PHP 8.3+ strict types and type declarations
- Implement WCAG 2.2 AA accessibility compliance
- Prefer Pest testing framework over PHPUnit
- Use Laravel Pint for code formatting
- **CRITICAL: Never run the full test suite.** Only run the specific test file(s) relevant to the change being made, one by one.
- **Markdownlint Compliance**: All markdown (`.md`) files MUST adhere to markdownlint standards (see section below)
- Tailwind v4 focus utility conventions in this project use `focus:outline-hidden` (not `focus:outline-none`) in rendered Blade output and related assertions
- Browser tests in this repo should avoid `$page->resize(...)` (unsupported); use `$page->script('window.resizeTo(...)')` and brief `wait(...)` before initial assertions on heavy pages

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

## UI/UX Improvement Phases (✅ ALL COMPLETE - 2026-02)

### Phase 1 – Accessibility ✅

- Removed invalid `role="button"` from `<a>` anchor tags in `button.blade.php`
- Updated `ButtonComponentTest.php` assertions (18 tests, 50 assertions)
- Added Alpine.js `:aria-pressed="isDark.toString()"` to header theme toggle
- `ThemeSystem.js` dispatches `CustomEvent('theme-changed')` on theme change
- Mobile sidebar: `:aria-expanded="sidebarOpen.toString()"` + `x-trap.noscroll`

### Phase 2 – Performance & Images ✅

- Added `loading="lazy" decoding="async"` to 15+ img tags across 12 files
- LCP/above-fold images use `loading="eager" fetchpriority="high"` (e.g., welcome.blade.php logo)
- Fixed `VisualRegressionTest.php`: all `window.resizeTo` → `$page->resize(W, H)` (Dusk timeout fix)
- Fixed `TabNavigationTest.php`: removed all `echo` warning statements (→ `// skipped`)

### Phase 3 – Dark Mode & Contrast ✅

- Mass-replaced `text-gray-400 dark:text-gray-500` → `text-gray-500 dark:text-gray-400` in 21 files
  (gray-500 in dark mode = 3.14:1 contrast FAIL; gray-400 = 5.3:1 PASS)
- Fixed `search-input.blade.php`: `placeholder-gray-400 dark:placeholder-gray-500` → `placeholder-gray-500 dark:placeholder-gray-400`
- Fixed `grade-badge.blade.php`: G grade `bg-gray-400 text-white` (2.45:1) → `bg-gray-500 text-white` (4.65:1)
- Created `GradeBadgeComponentTest.php` (8 tests, 25 assertions) - all pass
- Updated `SpinnerComponentTest.php` gray color assertion

### Phase 4 – UX & Animation Polish ✅

- No `ease-linear` found — all x-transition use `ease-out`/`ease-in` ✅
- Modal overlays all have `transition-opacity`/`transition-all` base classes ✅
- Fixed `support-card-mini.blade.php`: non-standard `hover:scale-102` → `hover:scale-[1.02]`
- `deck-slot.blade.php` hover effects: already has `transition-all duration-200` base class ✅
- Focus rings: globally configured in app.css with `dark: outline-color: primary-400`
- All focus rings consistently use `focus:ring-2 focus:ring-primary-500` pattern

### WCAG Dark Mode Rule (IMPORTANT)

In Tailwind CSS utility class pairs for dark mode muted text:

- ✅ CORRECT: `text-gray-500 dark:text-gray-400` (light bg → dark-mode bg, lower = darker)
- ❌ WRONG:   `text-gray-400 dark:text-gray-500` (this makes dark mode WORSE contrast)
- Lower number = lighter color in Tailwind gray scale

### Test Summary (all clean)

- `ButtonComponentTest`: 18/18 pass, 50 assertions
- `SpinnerComponentTest`: 13/13 pass, 22 assertions
- `GradeBadgeComponentTest`: 8/8 pass, 25 assertions

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

### Character Database Fix (2026-01)

- **Problem**: Character Database on `/characters/create` showed "No trainees match your filters" — empty list
- **Root Cause**: `CharacterController::create()` queried `ExternalData` model for `data_type='trainee'|'character'` but those records don't exist. Actual character data is in `ucp_game_characters` table (61 records) via `GameCharacter` model.
- **Solution**: Replaced `ExternalData` query with `GameCharacter::query()->orderBy('name_en')->get()->map(...)`, mapping fields like `name_en`, `title_en`, `primary_distance`, `preferred_style`, `image_path`, growth rates.
- **JS Filter Fix**: `filteredTrainees()` in Alpine uses `trainee.strategy`, but controller originally mapped as `style`. Fixed by providing both `style` and `strategy` keys.
- **Filter UI Fix**: Replaced non-functional Rarity filter (all characters are SSR) with Style/Strategy filter (Escape/Leader/Betweener/Chaser).

### Alpine formData.aptitudes Undefined Fix (2026-01)

- **Problem**: 10+ Alpine errors on `/characters/create`: `Cannot read properties of undefined (reading 'sprint')` on `formData.aptitudes.distance.sprint` etc.
- **Root Cause**: Step 3 (Aptitudes) uses `x-show` (not `x-if`), so bindings fire on page load. `loadDraft()` replaced entire `formData` with `this.formData = parsed.formData`, potentially loading a draft with corrupted/missing aptitudes structure. Also `selectTrainee()` did `this.formData.aptitudes = { ...trainee.aptitudes }` where aptitudes was `[]` (empty array), spreading to `{}`.
- **Solution**:
  1. Added `deepMerge()` helper method to characterWizard Alpine component
  2. `loadDraft()` now does `this.formData = this.deepMerge(defaults, parsed.formData)` preserving nested structure
  3. `selectTrainee()` now checks `typeof === 'object' && !Array.isArray()` before merging aptitudes
  4. `isStep3Valid()` now uses optional chaining (`this.formData.aptitudes?.distance`) with null-safety fallback

### Help Page Layout Fix (2026-01)

- **Problem**: Help page used `layouts.guest` (no sidebar for logged-in users), minimal content
- **Solution**: Full rewrite with `layouts.app`, breadcrumbs, Quick Start Guide (3 steps), Resource Cards (Getting Started, Accessibility, Privacy & Data, Feedback), FAQ accordion (5 questions with Alpine.js)

### Profile Page Spacing Fix (2026-01)

- **Problem**: Profile content pushed below fold due to excessive spacing
- **Solution**: Reduced `space-y-6` to `space-y-4`, `pb-5` to `pb-3`, heading `text-3xl` to `text-2xl sm:text-3xl`
- **Keyboard Nav Fix**: Added missing `tabs[]`, `focusNextTab()`, `focusPrevTab()`, `focusFirstTab()`, `focusLastTab()` to Alpine `profileManager` component

### Settings Delete Account Button Fix (2026-01)

- **Problem**: Delete Account button text invisible — used `border-error-300` / `dark:text-error-300` but `error-300` doesn't exist in the theme
- **Solution**: Changed to `border-error-200` / `dark:text-error-200`
- **Note**: Theme error colors defined: 50, 100, 200, 500, 600, 700 — NO 300 or 400

## Curated Skills Data Architecture

- **File**: `database/seeders/data/curated_skills.php` — 176 real game skills from uma.guide Global server
- **67 character-exclusive unique skills** total:
  - 48 card 01 primary unique skills (unique_001 through unique_048)
  - 19 card 02 alternate unique skills (unique_049 through unique_067)
- **Card variant architecture**: 48 unique Global characters; 19 have a second training card with a different unique skill. 48 + 19 = 67 total cards, matching uma.guide's "67 of 67" count exactly.
- **Naming corrections applied**:
  - (2026-03-01) Special Week: "All-Seeing Eyes" → "Shooting Star"
  - (2026-03-01) Daiwa Scarlet: "The View from the Lead Is Mine!" → "Resplendent Red Ace"
  - (2026-03-02) 10 card 01 names corrected to match uma.guide exactly (Vodka, Silence Suzuka, Tokai Teio, Gold Ship, Mejiro McQueen, El Condor Pasa, Narita Brian, T.M. Opera O, Mihono Bourbon, Haru Urara)
  - (2026-03-02) Inherited skill "Cut and Drive!" renamed to "Cut and Drive! (Inherited)" to avoid UNIQUE constraint collision with Vodka's card 01 skill
- **meta_tier ENUM**: Valid values are `['S+', 'S', 'A', 'B', 'C']` — do NOT use `A+`
- **Authoritative source**: uma.guide for EN Global skill names; GameTora is JS-heavy and unreliable for web scraping
- **uma.guide URL pattern**: `https://uma.guide/characters/detail.html?card=XXXXXX` (first 4 = character ID, last 2 = card variant 01/02)
- **Characters in ucp_characters** (141+ unique) greatly exceed Global server trainable cards (67); many are JP-only
- **Seeder**: `UcpSkillsSeeder` with upsert logic — matches by `internal_id`, only updates NULL/empty fields (never overwrites populated names), checks name uniqueness before creating
- **DB constraint**: `ucp_skills.name` has UNIQUE constraint — watch for collisions when renaming

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

## Phase 5 – WCAG 2.1 AA Skills Page ✅ (2026-02-28)

### Skills Page Accessibility Fixes (`resources/views/skills/`)

**`index.blade.php` changes:**

- Tab buttons: Added `:tabindex="activeTab === 'tab' ? '0' : '-1'"` + `:aria-selected` string binding
- Tab keyboard nav: `@keydown.arrow-right/arrow-left/home/end.prevent` on `[role="tablist"]` nav using `$el.querySelectorAll('[role=tab]')`
- Modal close button: Added `aria-label="Close skill details"` and `aria-hidden="true"` on SVG
- Modal `aria-modal`: Changed from static `aria-modal="true"` to `:aria-modal="showSkillModal ? 'true' : 'false'"`
- Added `id="modal-title"` to modal `<h3>` inside `<template x-if="selectedSkill">` (fixes `aria-labelledby` reference)
- Loading state: Added `role="status"` on wrapper, `aria-hidden="true"` on spinner
- Admin badge emoji: `<span aria-hidden="true">🔓</span>` pattern
- Acquire button emoji: `<span ... aria-hidden="true">🔓</span>`

**`inventory.blade.php` changes:**

- Filters section: Wrapped filter grid in `<fieldset>` + `<legend class="sr-only">Filter skills</legend>`
- Results count: Added `role="status" aria-live="polite" aria-atomic="true"` on results summary div
- Skill cards: Added `role="button"`, `tabindex="0"`, `@keydown.enter/space.prevent`, `:aria-label` on clickable divs
- Search input: Wrapped in `<div class="relative">`, added clear button with `aria-label="Clear search"`
- Pagination buttons: Added `aria-label="Go to first/previous/next/last page"` on all 4 pagination buttons

**`planner.blade.php` changes:**

- Build template cards: Added `role="button"`, `tabindex="0"`, `@keydown.enter/space.prevent`, `:aria-label`, `:aria-pressed`
- Delete build button: Added `:aria-label="\`Delete build ${build.name}\`"` and `aria-hidden="true"` on SVG icon

### Pattern: Alpine Tab Keyboard Navigation (WCAG 2.1.1)

```blade
<nav role="tablist"
    @keydown.arrow-right.prevent="const tabs = [...$el.querySelectorAll('[role=tab]')]; const idx = tabs.indexOf(document.activeElement); if (idx >= 0) { const next = tabs[(idx + 1) % tabs.length]; next.focus(); next.click(); }"
    @keydown.arrow-left.prevent="const tabs = [...$el.querySelectorAll('[role=tab]')]; const idx = tabs.indexOf(document.activeElement); if (idx >= 0) { const prev = tabs[(idx - 1 + tabs.length) % tabs.length]; prev.focus(); prev.click(); }"
    @keydown.home.prevent="const tabs = [...$el.querySelectorAll('[role=tab]')]; tabs[0]?.focus(); tabs[0]?.click();"
    @keydown.end.prevent="const tabs = [...$el.querySelectorAll('[role=tab]')]; tabs[tabs.length - 1]?.focus(); tabs[tabs.length - 1]?.click();">
```

### Pattern: Keyboard-Accessible Non-Button Clickable Cards

```blade
<div @click="action()"
     @keydown.enter.prevent="action()"
     @keydown.space.prevent="action()"
     role="button"
     tabindex="0"
     :aria-label="`Descriptive label for ${item.name}`">
```

### Pattern: Modal x-if + aria-labelledby Fix

- `aria-labelledby="modal-title"` on dialog div
- `id="modal-title"` on h3 INSIDE `<template x-if="selectedSkill">` — works because x-if renders to DOM when condition is true

## Phase 6 – WCAG 2.1 AA Support Cards Page ✅ (2026-07-23)

### Support Cards Page Accessibility Fixes (`resources/views/support-cards/`)

**`index.blade.php` changes:**

- Page title: Added `@section('title', 'Support Cards - ' . config('app.name'))` for descriptive `<title>`
- aria-expanded: Changed `aria-expanded="showExternalImport"` to `:aria-expanded="showExternalImport.toString()"` + added `aria-controls="external-import-panel"`
- Import panel: Added `id="external-import-panel"`, `role="region"`, `aria-label="External API Import"`
- Card grid: Changed `md:grid-cols-2` to `sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4` for better responsive layout
- SVG aria-hidden: Added `aria-hidden="true"` to filter button, search icon, clear filters icon, external import toggle icon
- Clear filters: Added `aria-label="Clear all filters"` to clear button

**`show.blade.php` changes:**

- Page title: Added `@section('title', $supportCard->name . ' - Support Cards - ' . config('app.name'))`

**`partials/external-import.blade.php` changes:**

- Heading hierarchy: Changed `<h3>` to `<h2>` (was breaking H1→H3 skip)
- SVG aria-hidden: Added `aria-hidden="true"` to globe icon, refresh button SVG, loading spinner, error icon, dismiss button SVG, two empty state SVGs (7 total)
- Dismiss error button: Added `aria-label="Dismiss error"`

**`components/support-card-tile.blade.php` changes (full rewrite):**

- Outer element: Changed `<div>` to `<a>` tag wrapping entire card for keyboard accessibility
- Focus styles: `focus:outline-hidden focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900`
- aria-label: Comprehensive label with card name, rarity, type, and tier
- CLS fix: Changed `h-48` to `aspect-video` for intrinsic aspect ratio (fixed CLS 0.47)
- Image `alt=""`: Decorative within labeled link
- Placeholder SVG: Added `aria-hidden="true"`
- Stat bonuses: Added `role="list"` / `role="listitem"` semantics
- Removed redundant "View Details" button (entire card is now the link)

**Layout change (`layouts/app.blade.php`):**

- Title: Changed from `{{ config('app.name') }}` to `@yield('title', config('app.name', 'Umamusume Career Planner'))`

### Tests Created

- `tests/Feature/SupportCardAccessibilityTest.php` — 10 Pest tests, 37 assertions
- Tests cover: page titles, aria-expanded, import panel, heading hierarchy, grid layout, keyboard-accessible links, aria-hidden SVGs, clear filters label, semantic landmarks

### Pattern: Dynamic Page Titles

```blade
{{-- In layout: --}}
<title>@yield('title', config('app.name', 'Default'))</title>

{{-- In child views: --}}
@section('title', 'Page Name - ' . config('app.name'))
```

### Pattern: Alpine aria-expanded String Binding

```blade
<button :aria-expanded="showPanel.toString()" aria-controls="panel-id">
<div id="panel-id" x-show="showPanel" role="region" aria-label="Panel description">
```

### Pattern: Full-Card Accessible Link (Card Tile)

```blade
<a href="{{ route('...') }}"
   class="block ... focus:outline-hidden focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
   aria-label="{{ $card->name }} - {{ $card->rarity }} {{ $card->card_type }}">
   {{-- Card content with decorative images (alt="") and aria-hidden SVGs --}}
</a>
```

## Phase 7 – WCAG 2.1 AA AI & Tools + External Resources Pages ✅ (2026-07-24)

### AI Dashboard (`resources/views/ai/dashboard.blade.php`)

- Converted from `<x-app-layout>` to `@extends('layouts.app')` for title support
- Added `@section('title', 'AI Management Dashboard')`
- Added `<x-breadcrumb>` (Home > AI & Tools)
- Added `<h1>` page header with subtitle
- Added `scope="col"` to all 5 `<th>` elements in Performance Comparison table
- Removed duplicate `<!-- Summary Cards -->` comment
- Replaced `@push('scripts')` with inline `@vite`

### AI Chat (`resources/views/ai/chat.blade.php`)

- Added `aria-hidden="true"` to 4 decorative SVGs (person icon, 3 quick action icons)
- Added `role="region" aria-label="Chat conversation"` to chat container
- Converted Quick Actions wrapper from `<div>` to `<section aria-label="Quick Actions">`

### MCP Dashboard (`resources/views/mcp/dashboard.blade.php`)

- Converted from `<x-app-layout>` to `@extends('layouts.app')` with title
- Added breadcrumb (Home > AI & Tools > MCP Dashboard)
- Added `<h1>` with descriptive subtitle
- Full ARIA tab pattern: `role="tablist"`, 6 `role="tab"` with `aria-selected`/`aria-controls`/`id`, 6 `role="tabpanel"` with `aria-labelledby`
- `aria-hidden="true"` on 7 decorative SVGs
- Replaced `@push('scripts')` with inline `@vite`

### OCR Upload (`resources/views/ocr/upload.blade.php`)

- Spinner: Added `role="status"`, `aria-label`, sr-only loading text
- Drop zone SVG: Added `aria-hidden="true"`
- Drop zone: Added `aria-describedby="upload-format-info"` linking to format text
- Format info: Added `id="upload-format-info"`

### OCR Results (`resources/views/ocr/results.blade.php`)

- Added breadcrumb (Home > AI & Tools > OCR Upload > OCR Results)
- Fixed confidence score dark mode colors (`dark:text-green-400`, `dark:text-yellow-400`, `dark:text-red-400`)
- Wrapped warning emoji in `<span aria-hidden="true">`
- Added `role="alert"` to low-confidence warning span
- Added `aria-label="Back to OCR Upload page"` on back link

### OCR Partials (4 files)

- `character-stats-form.blade.php`: 2 emoji `aria-hidden` wraps
- `training-session-form.blade.php`: 1 emoji `aria-hidden` wrap
- `skill-list-form.blade.php`: 1 emoji `aria-hidden` wrap
- `race-result-form.blade.php`: 1 emoji `aria-hidden` wrap

### Test Fix

- `OCRUIWorkflowTest.php`: Updated `assertSee('⚠️ Review recommended')` → `assertSee('Review recommended')` (emoji now in aria-hidden span)

### Pattern: Layout Conversion for Title Support

When converting from `<x-app-layout>` to `@extends('layouts.app')`:

```blade
{{-- Replace component wrapper --}}
@extends('layouts.app')
@section('title', 'Page Title')
@section('content')
  {{-- page content --}}
@endsection

{{-- Replace @push('scripts') with --}}
@vite('resources/js/pages/module/page.js')
```

### Verified via Chrome DevTools (all 5 pages)

- AI Dashboard: title, breadcrumb, h1, named regions, table headers with scope
- AI Chat: breadcrumb, h1, chat conversation region, quick actions section, no decorative SVGs in a11y tree
- MCP Dashboard: breadcrumb, h1, ARIA tabs with selected state switching, panel visibility
- OCR Upload: breadcrumb, h1, status spinner with live region, combobox labels, drop zone description
- OCR Results: breadcrumb (4-level), h1, back link with aria-label
- External Data Browse: confirmed still working from prior session fixes
- No JS errors on any page

## Phase 8 – WCAG 2.1 AA Admin Panel Pages ✅ (2026-07-24)

### performance/dashboard.blade.php (488 lines)

- Added `aria-hidden="true"` to 9 decorative SVGs (metric icons, alert severity icons, regression icon)
- Changed 4 `<div class="mt-5 grid grid-cols-2 gap-4">` → `<dl>` for Database/Cache/API/System metric sections
- Added `role="list"` to alerts container + `role="listitem"` on each alert item
- Added `role="list"` to regressions container + `role="listitem"` on each regression item

### admin/queue/index.blade.php (404 lines)

- Added `role="progressbar"`, `aria-valuenow`, `aria-valuemin="0"`, `aria-valuemax="100"`, `aria-label="Job batch progress"` to batch progress bars
- Added `role="status"` to Redis Connected/Disconnected badges
- Added `role="status"` to Horizon Active/Error/Inactive badges
- Fixed keyspace hit rate contrast: `text-gray-400 dark:text-gray-500` → `text-gray-500 dark:text-gray-400`

### admin/users/edit.blade.php (90 lines)

- Added `aria-describedby="bio-error"` and `aria-invalid="true"` on bio textarea (on error)
- Added `id="bio-error"` to bio error `<p>` element
- Added `aria-describedby="password-error"` on password input (on error)
- Added `id="password-error"` to password error `<p>` element

### admin-layout.blade.php (117 lines)

- Added `aria-current` attributes to all 6 mobile nav links (desktop links already had them)
- Added `@keydown.escape.window="mobileOpen = false"` to mobile menu for Escape key dismissal

### Pages already WCAG compliant (no changes needed)

- `admin/users/index.blade.php` — full compliance (search role, sr-only labels, table caption/scope, aria-labels)
- `admin/system-settings/index.blade.php` — full compliance (dl, role="status", heading hierarchy)
- `admin/logs/index.blade.php` — full compliance (search role, sr-only labels, time elements, level text labels)
- `admin/database/maintenance.blade.php` — full compliance (table caption/scope, confirm actions)
- `admin/database/seeders.blade.php` — full compliance (heading hierarchy, aria-labels)
- `admin-confirm-action.blade.php` — full compliance (role="dialog", aria-modal, aria-labelledby, focus-visible)
- `livewire/admin/apm-dashboard.blade.php` — full compliance (aria-pressed, aria-live, role="list", sr-only, caption/scope)

### Verified via Chrome DevTools (all admin pages)

- Users index: navigation landmark, search role, table caption, descriptive action buttons/links
- Users edit: labeled form fields, password describedby hint, proper checkbox
- System Settings: status roles on health indicators, heading hierarchy, confirm action buttons
- Database Maintenance: heading hierarchy, pre-formatted migration output, table with caption/scope
- Queue Monitor: `status` role on Redis/Horizon badges, heading hierarchy, labeled buttons
- Performance Dashboard: heading hierarchy (h1>h2>h3), labeled controls, breadcrumb, switch role
- Logs: search role, aria-labels, level badges with text (not color-only)
- No JS errors on performance dashboard page

## Phase 9 – WCAG 2.1 AA Profile, Settings & Help Pages ✅ (2026-07-25)

### profile/show.blade.php (189 lines)

- Added `aria-hidden="true"` to 7 decorative SVGs (5 tab button icons + 2 alert message icons)
- Added roving `tabindex` (`:tabindex="activeTab === 'TABNAME' ? 0 : -1"`) on all 5 tab buttons
- Added arrow key navigation (`@keydown.arrow-right/left/home/end`) on `<nav role="tablist">` element
- Already had: `role="tablist"`, `role="tab"`, `role="tabpanel"`, `aria-selected`, `aria-controls`, `aria-labelledby`

### profile/partials/account-tab-content.blade.php

- Added `@class` with conditional error borders on name/email inputs
- Added `aria-describedby="name-error"` + `aria-invalid="true"` for name field error state
- Added `aria-describedby="email-error"` + `aria-invalid="true"` for email field error state
- Added `aria-describedby="bio-help"` + `id="bio-help"` on bio textarea help text
- Added `aria-hidden="true"` on 4 decorative SVGs (spinner, verified badge, change/remove avatar icons)
- Added `id="name-error" role="alert"` and `id="email-error" role="alert"` on error paragraphs

### profile/partials/notifications-tab-content.blade.php

- Changed 4 `<h3>` elements to `<label for="notif_XXXX">` elements with cursor-pointer
- Added `id="notif_email"`, `id="notif_training"`, `id="notif_race"`, `id="notif_ai"` to corresponding checkboxes

### profile/partials/security-tab.blade.php

- Added `aria-hidden="true"` to 2 decorative SVGs (monitor icon, warning triangle)
- Added `role="dialog"` + `aria-modal="true"` + `aria-labelledby="delete-account-title"` to delete modal
- Added `id="delete-account-title"` to modal heading
- Added `aria-required="true"` on delete password field
- Added `aria-describedby="delete-confirm-help"` on confirmation input + `id="delete-confirm-help"` on error paragraph

### settings/index.blade.php (1265 lines)

- Added `@section('title', 'Settings')` (was missing)
- Added `role="tablist"` to nav container
- Added `id="tab-{section}"`, `role="tab"`, `:aria-selected`, `aria-controls` to all 8 nav links
- Added `role="tabpanel"` + `aria-labelledby="tab-{section}"` to all 8 content panels
- Password modal: `role="dialog"` + `aria-modal="true"` + `aria-labelledby="password-modal-title"` + `@keydown.escape`
- Delete modal: `role="dialog"` + `aria-modal="true"` + `aria-labelledby="delete-modal-title"` + `@keydown.escape`
- Delete confirmation: `aria-describedby="delete-confirm-instructions"` on input + `id` on instruction paragraph
- Theme selector: `role="radiogroup" aria-label="Theme selection"` on grid + `role="radio"` + `:aria-checked` on 3 buttons
- Font size slider: `:aria-valuetext="fontSize + '% font size'"`

### help/index.blade.php

- Added `aria-label="Help and support resources"` to `<section>` element
- Already had: `@section('title', 'Help & Support')`, proper h1/h2 heading hierarchy

### Pages already WCAG compliant (no changes needed)

- `profile/partials/privacy-tab-content.blade.php` — proper `<label for>` associations
- `profile/partials/preferences-tab-content.blade.php` — proper label associations
- `settings/accessibility.blade.php` — wrapper with Livewire component, structurally fine
- `settings/notifications.blade.php` — wrapper with Livewire component, structurally fine

### Verified via Chrome DevTools

- Profile: `tablist "Profile sections"` with 5 tab elements (all selectable), `tabpanel "Account"`, form labels, `textbox "Bio" description="Brief description..."`, SVGs hidden
- Settings: `tablist "Settings navigation"` with 8 tab elements, `tabpanel "Account"`/`"Appearance"`, `radiogroup "Theme selection"` with 3 radio elements, `slider "Font Size: 100%" valuetext="100% font size"`, `switch` roles on toggles
- Help: `region "Help and support resources"`, heading hierarchy h1>h2, all links labeled, skip link present

### Tests (40 passed, 103 assertions)

- ProfileTest.php + SettingsControllerTest.php — all green
