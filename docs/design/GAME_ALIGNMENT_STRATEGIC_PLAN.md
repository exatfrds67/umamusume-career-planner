# Umamusume Career Planner: Game Alignment Strategic Plan

**Document Version**: 1.0.0  
**Date**: January 29, 2026  
**Status**: Strategic Planning & Design Reference  
**Purpose**: Comprehensive game-to-app alignment planning without 1:1 copying  
**Based On**: 120+ game screenshots (July 2025 - January 2026), game mechanics research, existing design analysis

---

## Executive Summary

This document provides a strategic framework for aligning the Umamusume Career Planner with the game's UX patterns, information architecture, and mental models while creating a distinct web-optimized planning interface.

**Core Philosophy**: *Learn from the game's proven designs, adapt for web, create complementary tool.*

**Not About**: 1:1 UI copying, mobile emulation, or recreating the game  
**About**: Understanding why game UX works, applying principles to planning context

---

## Section 1: Game Interface Patterns & Principles

### 1.1 Game's Information Architecture

**Observation**: The game uses a **5-tier top-level navigation** pattern that appears consistently:

```
┌─────────────────────────────────────────────┐
│ MAIN MENU → Training → Races → Shop → Other │
└─────────────────────────────────────────────┘
```

This is further subdivided into contextual sections:
- **Training Section**: Facility selection → Trainee selection → Training execution
- **Races Section**: Upcoming races → Race history → Race predictions
- **Shop Section**: Item purchases → Skill shop → Support card management

**Implication for Career Planner**:
- Use task-oriented navigation (plan creation → execution tracking → analysis)
- Create clear entry points for different user intents
- Avoid overwhelming users with all options simultaneously
- Provide context-aware navigation breadcrumbs

### 1.2 Game's Data Display Hierarchy

**Observation**: The game consistently uses this information density pattern:

```
Level 1 (Top 20% of screen):  CRITICAL STATUS
                              ├─ Health/Stamina gauge
                              ├─ Turn number
                              └─ Action points

Level 2 (Next 30% of screen): PRIMARY CONTROLS
                              ├─ Available actions
                              ├─ Quick filters
                              └─ Mode toggles

Level 3 (Remaining 50%):      DETAILED DATA
                              ├─ Character grid/list
                              ├─ Stats breakdowns
                              └─ Historical data
```

**Implication for Career Planner**:
- Place turn tracker, SP budget, and current stats in top 20% (persistent)
- Next 30%: plan timeline, current focus areas, quick actions
- Remaining space: detailed breakdowns, comparisons, forecasts
- Defer secondary data to modals/expandable sections

### 1.3 Game's Color Language

**Observed Pattern**: Colors serve functional purposes, not decoration:

| Element | Color | Meaning | Example |
|---------|-------|---------|---------|
| **TP/Training Points** | Orange/Amber | Primary resource | 100/100 gauge |
| **RP/Race Points** | Blue | Secondary resource | 2/5 indicator |
| **Currency** | Yellow/Gold | Spending resource | 1.4M displayed |
| **Items** | Green | Collectibles | 12 in inventory |
| **Stats** | Stat-specific | Type identifier | Speed=Red, Stamina=Blue |
| **Conditions** | Traffic light | Status | GREAT=Green, GOOD=Lime, NORMAL=Gray, BAD=Red |
| **Alerts** | Red/Orange | Warnings | Low TP, race coming |
| **Links/Actions** | Blue/Purple | Interactive | Menu buttons |

**Implication for Career Planner**:
- Use stat colors consistently (Speed=Red, Stamina=Blue, Power=Yellow, Guts=Green, Wit=Purple)
- Adopt traffic-light conditions (GREAT/GOOD/NORMAL/BAD) with matching colors
- Reserve orange/amber for critical SP alerts
- Use blue for secondary metrics (targets, comparisons)
- Create a consistent color token system in Tailwind config

### 1.4 Game's Navigation Patterns

**Screen Type A: Grid/List Views** (Character selection, Race list)
```
┌─ Search/Filter bar (sticky) ─────────────────┐
├─ Sorting/View toggle options                   │
├─────────────────────────────────────────────┤
│ [Card] [Card] [Card]                        │
│ [Card] [Card] [Card]  ← Grid responsive     │
│ [Card] [Card] [Card]                        │
└─────────────────────────────────────────────┘
```

**Screen Type B: Detail/Tabbed Views** (Character profile, Stat details)
```
┌─ Tab Navigation (Speed/Stamina/Power...) ───┐
├─ Detail header (back button, share) ───────┤
├─────────────────────────────────────────────┤
│ Primary content area                        │
│ With nested hierarchies                     │
│ And collapsible sections                    │
└─────────────────────────────────────────────┘
```

**Screen Type C: Execution Flow** (Training screen, Race prediction)
```
┌─ Progress indicator (Turn 15/78) ──────────┐
├─ Primary decision area (6-9 options) ──────┤
├─ Effect preview area ──────────────────────┤
├─────────────────────────────────────────────┤
│ Historical context (previous turns) ────┤
│ With swipe/scroll capability for history│
└─────────────────────────────────────────────┘
```

**Implication for Career Planner**:
- Adopt these 3 screen types for plan interaction
- Type A for: Plan list, Character list, Skill list, Training history
- Type B for: Plan detail, Character detail, Skill detail, Run analysis
- Type C for: SP allocation, Training turn execution, Race prediction

### 1.5 Game's Gesture & Interaction Patterns

**Observed from Screenshots**:

1. **Horizontal Scrolling**: Used for historical data (previous turns, race history)
2. **Tab Navigation**: Used for related breakdowns (Stats tabs, Skill tabs)
3. **Modal Dialogs**: Used for confirmations and skill selection
4. **Pull-to-refresh**: Used for race schedule updates (implied)
5. **Swipe Navigation**: Mobile assumes left/right swipe for tab switching
6. **Long Press**: Implied for character selection and skill tooltips

**Implication for Career Planner**:
- Use horizontal scroll for turn-by-turn history view
- Tab interface for stat/skill/aptitude breakdowns
- Modals for confirmations (stat allocation, skill purchase)
- Implement swipe navigation on mobile
- Add touch targets minimum 44px (WCAG guideline)

---

## Section 2: Feature Mapping: Game Mechanics → App Workflows

### 2.1 Training System Alignment

**Game Flow**:
```
[Select Facility] → [Select Trainee] → [Show Stats Gained] → [Confirm] → [Update Status]
```

**Game Characteristics**:
- Single decision per turn
- Immediate visual feedback (stats gained shown)
- Limited time (turn counter visible)
- Support cards provide multiplicative bonus

**Career Planner Adaptation**:
```
[Select Training Focus] → [Show Recommended Actions] → [Preview SP Impact] → [Allocate] → [Confirm]
```

**Key Differences**:
- Plan view instead of live execution
- Multi-turn lookahead instead of single turn
- SP budget management instead of facility selection
- Offline-capable (no live timers needed)

**Design Elements to Use**:
- Training progress bar (like TP/RP gauges)
- Stat gain preview with color-coded improvements
- Grid-based facility selection → convert to "Training Focus Cards" (list/grid view)
- Confirmation dialog with full impact summary

### 2.2 Character Management Alignment

**Game Flow**:
```
[Character Grid] → [Select Character] → [View Details with Tabs] → [Manage Skills/Gear]
```

**Game Characteristics**:
- Visual character portraits with star ratings
- Multi-tab breakdown (Potential/Hints/Star Unlock)
- Skill list with SP costs
- Support card assignment/gear
- Clear potential level indicators (Lvl 1-9)

**Career Planner Adaptation**:
```
[Character Grid/List] → [Select for Plan] → [View with Tabs] → [Configure Training Path]
```

**Key Differences**:
- Plans are per-character, not global management
- Tabs organize: Overview / Stats & Aptitudes / Factors / Skills & SP / Training History
- Support card integration is for stat bonus reference only
- Skill tree shown as "Available Skills" filtered by cost/level

**Design Elements to Use**:
- Character portrait grid (responsive 2-4 columns)
- Star rating system for character tier (same as game)
- Potential level badge (shows growth potential)
- Tab interface for detail organization
- Skill list with SP cost and activation percentage
- Split layout: character portrait left, details right (on desktop)

### 2.3 Stat Progression Tracking Alignment

**Game Characteristics Observed**:
- Live stat display with current/max values
- Soft cap indicator at 1200 (shown with icon on some stats)
- Percentage display (rarely, but implied in calculations)
- Target stat tracking (races show target speed/stamina)
- Factor bonuses from inheritance (shown in details)

**Career Planner Adaptation**:
```
Current Turn 1:
├─ Speed: 750/2000 (50 from factor)
├─ Stamina: 620/2000 (40 from factor)
├─ Power: 680/2000
├─ Guts: 900/2000
└─ Wit: 710/2000

Turn 20 Projection:
├─ Speed: 1300/2000 [Soft Cap]
├─ Stamina: 950/2000
├─ Power: 1100/2000
├─ Guts: 1200/2000 [Soft Cap]
└─ Wit: 1050/2000
```

**Design Elements to Use**:
- Stat bar with soft cap indicator (visual line at 1200)
- Current/max display format
- Factor bonus annotation in parentheses
- Progress bars with stat-specific colors
- Radar chart for quick visual comparison (pentagon shape)
- Comparison view: current vs target vs projected
- Historical tracking with turn-by-turn breakdown

### 2.4 Skill & SP Management Alignment

**Game Flow Observed**:
```
[Character Detail] → [Skills Tab] → [Available Skills List] → [Select Skill] → [Preview Cost] → [Purchase]
```

**Game Characteristics**:
- Organized skill list by type/tier
- SP cost clearly displayed
- Rarity/type indicators (color-coded borders)
- Locked skills shown as unavailable (greyed out)
- Skill effect preview on selection
- Quick purchase button

**Career Planner Adaptation**:
```
[Plan Detail] → [Skills Tab] → [Skills Grid/List] → [Drag to Allocate] → [SP Impact Preview] → [Save]
```

**Key Differences**:
- Plan-view instead of action execution
- Budget planning instead of single purchase
- Drag-and-drop SP allocation (visual planning)
- Multi-skill comparison

**Design Elements to Use**:
- Skill grid with 3-4 columns, responsive layout
- Skill cards: icon, name, SP cost, type badge, current status
- Color-coded borders by skill type (reference game's color scheme)
- SP budget bar (like the TP gauge) showing allocated vs available
- Allocation area with drag targets
- Impact summary: "Allocating this skill will use 120 SP, leaving 180 available"

### 2.5 Race Planning & Prediction Alignment

**Game Characteristics Observed**:
- Upcoming race list with:
  - Race name, distance, terrain, weather condition
  - Entry deadline
  - Prize breakdown
  - Race type (tournament, league, etc.)
- Race history with:
  - Placement, time, prize amount
  - Performance rating (star-based)
  - Stat requirements display

**Career Planner Adaptation**:
```
Race Planning Tab:
├─ Upcoming races (calendar view)
├─ Race targets (select races for plan)
├─ Stat requirements to win
├─ Current vs required stats comparison
└─ Training path to meet targets
```

**Design Elements to Use**:
- Race card: name, distance, type, date
- Stat requirement breakdown (table: stat, required, current, delta)
- Calendar grid for race schedule
- Race history list with sorting/filtering
- Target selection modal with race preview
- Training path recommendation based on targets

---

## Section 3: App Layout & Component Architecture

### 3.1 Proposed App Layout Structure

```
┌─────────────────────────────────────────────────────────┐
│ TOP STATUS BAR                                          │
│ ├─ Turn: 15/78  │  SP Available: 300/500  │ Mode: Account
│ ├─ Current Focus: Speed  │  Next Race: In 12 turns    │
└─────────────────────────────────────────────────────────┘

┌───────────────────────┬─────────────────────────────────┐
│                       │                                 │
│   SIDEBAR NAV         │  MAIN CONTENT AREA              │
│  (Desktop only)       │                                 │
│                       │  ┌─ BREADCRUMB ─────────────┐  │
│  • Dashboard          │  │                           │  │
│  • Plans              │  ├─ PAGE HEADER              │  │
│  • Characters         │  │  ├─ Title                 │  │
│  • Training           │  │  ├─ Quick Actions         │  │
│  • Races              │  │  └─ Filter/Sort           │  │
│  • Analysis           │  │                           │  │
│  • Settings           │  ├─ CONTENT SECTION          │  │
│                       │  │  (Grid, Tabs, Cards)      │  │
│                       │  │                           │  │
│                       │  └─────────────────────────┘  │
└───────────────────────┴─────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ MOBILE BOTTOM NAV                                       │
│ [Dashboard] [Plans] [Characters] [Training] [Analyze]  │
└─────────────────────────────────────────────────────────┘
```

### 3.2 Component Hierarchy

**Level 1: Page Templates** (Container layouts)
- `DashboardPage` - Overview & quick actions
- `PlanDetailPage` - Plan execution interface
- `CharacterDetailPage` - Character profile
- `TrainingPage` - SP/skill allocation
- `AnalysisPage` - Charts & comparisons

**Level 2: Section Components** (Content areas)
- `StatsSummary` - Current stats display
- `SkillAllocator` - Drag-drop SP budget
- `TrainingTimeline` - Turn-by-turn breakdown
- `RaceSchedule` - Upcoming/completed races
- `CharacterGrid` - Character selection

**Level 3: UI Components** (Reusable elements)
- `StatBar` - Single stat with progress
- `GradeBadge` - A/B/C grade display
- `ConditionIndicator` - GREAT/GOOD/NORMAL/BAD
- `SkillCard` - Skill with SP cost
- `RaceCard` - Race with key info
- `Tab` - Tab navigation
- `Modal` - Dialog/confirmation

**Level 4: Atomic Components** (Design system)
- `Button` - CTA buttons
- `Card` - Content container
- `Badge` - Small labels
- `Gauge` - Progress indicator
- `Chart` - Data visualization

### 3.3 Responsive Breakpoints

Align with Tailwind default breakpoints:

| Breakpoint | Width | Device | Layout |
|-----------|-------|--------|--------|
| sm | 640px | Phablet | 1-column, bottom nav |
| md | 768px | Tablet | 1-column, side nav toggle |
| lg | 1024px | Desktop | 2-column, side nav |
| xl | 1280px | Desktop+ | 2-column optimized |

---

## Section 4: Design System & Visual Language

### 4.1 Color Palette (Game-Aligned)

**Stat Colors** (Primary palette):
```
Speed:    #EF4444 (Red-500)      [Game: Crimson]
Stamina:  #3B82F6 (Blue-500)     [Game: Azure]
Power:    #EAB308 (Yellow-500)   [Game: Amber]
Guts:     #22C55E (Green-500)    [Game: Emerald]
Wit:      #A855F7 (Purple-500)   [Game: Violet]
```

**Condition Colors** (Status):
```
GREAT:    #10B981 (Emerald-500)  [Upward arrow, strong]
GOOD:     #84CC16 (Lime-500)     [Slightly up, positive]
NORMAL:   #6B7280 (Gray-500)     [Neutral, baseline]
BAD:      #EF4444 (Red-500)      [Downward arrow, warning]
```

**Resource Colors**:
```
SP (Training Points):    #F59E0B (Amber-500)    [Primary resource]
Training Focus Color:    #06B6D4 (Cyan-500)     [Current focus]
Target Color:            #8B5CF6 (Violet-500)   [Goals/targets]
Progress:                #10B981 (Green-500)    [Completed/achieved]
```

**Semantic Colors**:
```
Success:  #10B981 (Green)
Warning:  #F59E0B (Amber)
Danger:   #EF4444 (Red)
Info:     #3B82F6 (Blue)
Neutral:  #6B7280 (Gray)
```

### 4.2 Typography Scale

```
Heading 1 (h1):  32px / 2rem   Bold      Page titles
Heading 2 (h2):  24px / 1.5rem Bold      Section headers
Heading 3 (h3):  20px / 1.25rem SemiBold  Card titles
Heading 4 (h4):  16px / 1rem   SemiBold  Stat labels
Body Text:       14px / 0.875rem Regular  Default text
Small Text:      12px / 0.75rem Regular  Captions, hints
Mono (code):     12px / 0.75rem Monospace Data values
```

### 4.3 Spacing System

Use Tailwind's default spacing scale (4px base unit):

```
xs: 0.25rem (4px)
sm: 0.5rem (8px)
md: 1rem (16px)
lg: 1.5rem (24px)
xl: 2rem (32px)
2xl: 2.5rem (40px)
3xl: 3rem (48px)
```

### 4.4 Component Sizing

**Buttons**:
- Large: 48px height (touch target)
- Default: 40px height
- Small: 32px height
- Minimum width: 44px (WCAG touch requirement)

**Cards**:
- Padding: 16px (md)
- Border radius: 8px
- Box shadow: subtle (0 1px 3px rgba)
- On hover: slight elevation

**Inputs**:
- Height: 40px (default)
- Padding: 8px 12px
- Border: 1px solid
- Focus ring: 2px offset, stat-color

---

## Section 5: User Workflows & Information Flows

### 5.1 Plan Creation Flow

**Current App Implementation** → **Improved Alignment**:

```
User wants to plan a character run:

CURRENT:
1. Click "Create Plan"
2. Select character (grid)
3. Enter career details
4. Set stat targets
5. Confirm

IMPROVED:
1. Click "New Plan" (FAB button, prominent)
2. Character selection grid with filters
   ├─ Filter by race level (Easy/Normal/Hard)
   ├─ Sort by growth rate or potential
   └─ Show tier star rating
3. Plan setup wizard (card-based):
   ├─ Card 1: Career info (type: NuramaRace/TrainingEvent)
   ├─ Card 2: Career goal (win/place/consistent)
   ├─ Card 3: Stat targets (6 input fields with sliders)
   ├─ Card 4: Race schedule (select 3-5 target races)
   └─ Card 5: Review & confirm
4. Plan created, automatically navigate to training tab
5. Show "Training timeline" with first turn highlighted
```

**Design Pattern**: Multi-step wizard using card progression (similar to game's facility selection flow)

### 5.2 Plan Execution Flow

**Game Training Loop** → **App Planning Loop**:

```
GAME:
[Facility Selection] → [Trainee Selection] → [Training Executed]
        ↓                     ↓                      ↓
    6 facilities       Select 1 trainee      Immediate stat gain
                       (+ support cards)      Resource consumption

APP:
[Plan View] → [Training Tab] → [SP Allocation] → [Skill Planning] → [Save]
      ↓              ↓               ↓                    ↓            ↓
  Turn display   Timeline      Allocate to       Select skills      Update
  Current stats   view         facility groups   within budget       projections
```

**Design Elements**:
- Timeline view showing turns 1-78 (horizontal scroll)
- Current turn highlighted and sticky
- SP budget gauge (orange, like TP) at top
- Facility group buttons (Speed/Stamina/Power/Guts/Wit training)
- SP allocation sliders or drag targets
- Impact preview: "This allocation will give +45 Speed, +20 Stamina"

### 5.3 Race Planning Flow

**Observation**: Game shows races in a list with upcoming race highlighted.

```
USER FLOW:
1. User navigates to "Races" tab
2. Sees calendar or list of upcoming races
3. Selects a race for planning
4. Modal shows:
   ├─ Race name, distance, terrain
   ├─ Stat requirements (table)
   ├─ Current stats vs required
   ├─ Turns until race
   └─ "Use as target" button
5. Target race added to plan
6. Training recommendations updated

VISUAL DESIGN:
Race cards in horizontal scroll or grid:
┌─────────────────────────┐
│ Race Name               │ ← Larger text
│ Distance: 2000m         │ ← Gray secondary
│ 🏃 Turf  ☀️  Clear     │ ← Icon format
│                         │
│ Speed: 1200 (need 1400) │ ← Color coded
│ Stamina: 900 (need 1100)│    (red = gap)
│ In 12 turns             │ ← Timer
│                         │
│ [Analyze] [Plan for it] │ ← Actions
└─────────────────────────┘
```

---

## Section 6: Data Visualization & Analytics

### 6.1 Stat Progression Chart

**Game doesn't show this**, but app should (planning advantage):

```
Multi-turn projection (radar chart):

     Wit (1000)
         ▲
      /  │  \
    /    │    \
  Guts (1100)─────Speed (1350)
    \    │    /
      \  │  /
Stamina ─┼─ Power
(950)    │  (1100)

Color coding: bars by turn or by stat (consistent with stat colors)
Display options:
  • Current (turn 1) - solid
  • Projected (turn 78) - dashed/outlined
  • Target race needs - dotted/colored differently
```

### 6.2 Training Progress Tracker

Similar to game's turn counter, but expanded:

```
Turn 15 / 78 (19% complete)

Progress bars per facility group:
├─ Speed Training: ████░░░░ (45% total SP allocated)
├─ Stamina Training: ██░░░░░░ (20% total SP allocated)
├─ Power Training: ███░░░░░ (30% total SP allocated)
├─ Guts Training: ██░░░░░░ (20% total SP allocated)
└─ Wit Training: █░░░░░░░ (10% total SP allocated)

Total SP committed: 300/500 (60%)
Remaining SP: 200 (40%)
```

### 6.3 Skill Impact Analysis

Show skill acquisition timeline:

```
Turn   Skill Acquired         SP Cost   New Total Stats
────────────────────────────────────────────────────────
15     Speed Boost (Lvl 1)    50 SP    Speed +50 → 850
22     Stamina Recovery       60 SP    Stamina +40 → 960
35     Power Acceleration     75 SP    Power +60 → 1100
48     Wit Enhancement        50 SP    Wit +50 → 1050
65     Final Sprint           100 SP   Speed +100 → 1350
```

---

## Section 7: Accessibility & Performance Considerations

### 7.1 Accessibility (WCAG 2.2 AA)

**Color & Contrast**:
- Stat colors must be distinguishable without color alone
  - Use icons + text labels (e.g., 🐎 Speed, 🚴 Stamina)
  - Ensure 4.5:1 contrast minimum on text
  - Provide pattern/texture variations for stat areas

**Interactive Elements**:
- Minimum touch target: 44×44px
- Focus visible: 2px outline, color-coded
- Tab order: logical flow (top to bottom, left to right)
- ARIA labels: semantic HTML + aria-label where needed

**Motion**:
- Respect prefers-reduced-motion
- Animations optional (can be disabled)
- Transitions: < 300ms for responsiveness

**Forms**:
- Labels associated with inputs
- Error messages linked to fields
- Required fields marked clearly (*) and announced
- Form validation happens on blur, not on type

### 7.2 Performance Targets

**Core Web Vitals (mobile)**:
- LCP (Largest Contentful Paint): < 2.5s
- FID (First Input Delay): < 100ms
- CLS (Cumulative Layout Shift): < 0.1

**Implementation**:
- Lazy load character portraits (use `loading="lazy"`)
- Code split by page (Vite dynamic imports)
- Compress images (WebP, 80-90% quality for screenshots)
- Cache static data: character list, skill data, race schedule
- Defer non-critical JS (Alpine components load on interaction)

**Mobile Optimization**:
- Responsive images (srcset for 2x/3x density)
- No horizontal scroll on small screens
- Bottom nav must be 44px minimum height
- Font sizes: min 16px on inputs (prevent iOS zoom)

---

## Section 8: Implementation Priorities & Roadmap

### 8.1 Phase 1: Foundation (Weeks 1-2)

**Priority**: Critical
- [ ] Update Tailwind config with game-aligned color tokens
- [ ] Create color token documentation (stat/condition/resource colors)
- [ ] Build base layout components (AppLayout, TopStatusBar, SidebarNav)
- [ ] Set up responsive breakpoints and mobile nav

**Deliverables**: Color system, layout foundation, navigation scaffold

### 8.2 Phase 2: Core Components (Weeks 3-4)

**Priority**: Critical
- [ ] Stat display components (StatBar, ProgressBar, RadarChart)
- [ ] Character selection grid with filtering
- [ ] Plan card component for lists
- [ ] Tab navigation for detail views

**Deliverables**: Component library, character grid, plan list

### 8.3 Phase 3: Plan Management (Weeks 5-6)

**Priority**: High
- [ ] Plan creation wizard (multi-step card flow)
- [ ] Plan detail page with tabs (Overview/Stats/Skills/Races)
- [ ] Training timeline view (turn-by-turn)
- [ ] SP budget allocation UI (slider or drag-drop)

**Deliverables**: Full plan CRUD, training interface

### 8.4 Phase 4: Advanced Features (Weeks 7-8)

**Priority**: Medium
- [ ] Race planning interface with stat requirement matching
- [ ] Skill allocation & SP tracking
- [ ] Multi-plan comparison
- [ ] Import/export with game data alignment

**Deliverables**: Race planning, skill management, data import

### 8.5 Phase 5: Analytics & Visualization (Weeks 9-10)

**Priority**: Medium
- [ ] Stat progression charts (radar, line graphs)
- [ ] Training progress dashboard
- [ ] Skill acquisition timeline
- [ ] Plan success prediction

**Deliverables**: Analytics dashboard, prediction models

### 8.6 Phase 6: Polish & Optimization (Weeks 11-12)

**Priority**: High
- [ ] Accessibility testing & fixes (WCAG 2.2 AA)
- [ ] Performance optimization (Core Web Vitals)
- [ ] Mobile responsiveness testing
- [ ] Browser compatibility
- [ ] User testing with actual players

**Deliverables**: Accessible, performant, tested app

---

## Section 9: Design Patterns to Avoid

### 9.1 Anti-Patterns (Don't do these)

❌ **Exact Game UI Copy**
- Don't recreate the mobile game interface in web
- Do create complementary interface optimized for planning

❌ **Feature Bloat**
- Don't try to include every game system (gacha, guild wars, etc.)
- Do focus on training/career planning core loop

❌ **Over-Animation**
- Don't use animations for their own sake
- Do use purposeful transitions (< 300ms, meaningful)

❌ **Accessibility Afterthought**
- Don't rely on color alone to convey information
- Do design accessible from the start (icons, labels, contrast)

❌ **Mobile Unfriendly**
- Don't force desktop layouts on mobile
- Do adopt bottom navigation and responsive cards

❌ **Offline Incompatible**
- Don't require constant server communication
- Do cache critical data (character list, skills, races)

### 9.2 Design Pattern Go-Tos (Do these)

✅ **Task-Oriented Navigation**
- Organize by what users do (create plan, execute training, analyze results)
- Not by data model (characters, stats, runs)

✅ **Progressive Disclosure**
- Show critical info upfront (current turn, SP available)
- Hide advanced options until needed
- Use modals/collapsibles for secondary info

✅ **Consistent Mental Models**
- If turning red means "bad" in conditions, apply consistently
- Stat colors always represent same stat type
- Actions have expected outcomes

✅ **Responsive by Default**
- Design mobile-first
- Enhance for larger screens
- Test at actual breakpoints (not just resize)

✅ **Accessibility First**
- Test with keyboard only
- Check color contrast before shipping
- Use semantic HTML (buttons, links, headings)
- Add ARIA labels where needed

---

## Section 10: Reference Integration Points

This plan references and aligns with:

- **game-mechanics-research-report.md**: Stat formulas, training system, skill mechanics
- **game-alignment-analysis.md**: Detailed UI pattern observations from 83+ screenshots
- **component-inventory.md**: Existing Blade components and their status
- **data-flow-mapping.md**: How data moves through the application
- **IMPLEMENTATION_PLAN.md**: Actionable development tasks and phases

**Related Documentation**:
- docs/design/game-ui-alignment-strategy.md (visual design specifications)
- docs/research/game-mechanics-research-report.md (mechanics validation)
- AGENTS.md (coding standards and conventions)

---

## Section 11: Design Decision Log

**Decision 1**: Use stat-specific colors (Red=Speed, Blue=Stamina, etc.)
- **Rationale**: Game uses color coding consistently; users recognize patterns
- **Implementation**: Tailwind color tokens for each stat type
- **Status**: ✅ Planned for Phase 1

**Decision 2**: Multi-step wizard for plan creation
- **Rationale**: Game uses sequential facility → trainee selection; app mirrors with plan → character → targets
- **Implementation**: Card-based progression, 5 steps with preview
- **Status**: ✅ Planned for Phase 3

**Decision 3**: Bottom navigation for mobile instead of sidebar
- **Rationale**: Game uses bottom nav (implied from mobile screenshots); suits touch navigation
- **Implementation**: 5-tab navigation bar, Livewire routing
- **Status**: ✅ Planned for Phase 1

**Decision 4**: Tabbed detail views (not stacked single columns)
- **Rationale**: Game uses tabs for related breakdowns; cleaner than scrolling long pages
- **Implementation**: Tab component with lazy loading
- **Status**: ✅ Planned for Phase 2

**Decision 5**: Persistent top status bar
- **Rationale**: Game's top bar is always visible; users need context on turn/SP/focus
- **Implementation**: Sticky header with turn counter, SP gauge, current stats
- **Status**: ✅ Planned for Phase 1

---

## Appendix A: Game Screenshots Referenced

This plan analyzes screenshots from:
- **July 2025**: Game tutorial & basic flows (20 screenshots)
- **October-November 2025**: Character management & training UI (35 screenshots)
- **December 2025**: Race mechanics & predictions (25 screenshots)
- **January 2026**: Recent career flows & detailed stat views (40 screenshots)

**Total Analyzed**: 120+ screenshots covering:
- Main menu & navigation
- Character selection & profile
- Training facility interface
- Race schedule & results
- Skill shop & equipment
- Career progression flows
- Prediction/strategy screens

---

## Appendix B: Color Reference Guide

Use this in code/config:

```javascript
// Stat Colors (from game)
const statColors = {
  speed: '#EF4444',    // red-500
  stamina: '#3B82F6',  // blue-500
  power: '#EAB308',    // yellow-500
  guts: '#22C55E',     // green-500
  wit: '#A855F7',      // purple-500
};

// Condition Colors
const conditionColors = {
  great: '#10B981',    // emerald-500
  good: '#84CC16',     // lime-500
  normal: '#6B7280',   // gray-500
  bad: '#EF4444',      // red-500
};

// Resource Colors
const resourceColors = {
  sp: '#F59E0B',       // amber-500
  focus: '#06B6D4',    // cyan-500
  target: '#8B5CF6',   // violet-500
  progress: '#10B981', // green-500
};
```

---

**Document Status**: Ready for design team review  
**Next Step**: Assign design components to development sprints  
**Review Date**: February 5, 2026
