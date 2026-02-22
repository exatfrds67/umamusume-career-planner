# Umamusume Game UI/UX Alignment Analysis

**Document Version**: 1.1.0  
**Date**: January 28, 2026 (Revised)  
**Purpose**: Analyze actual game UI patterns to inform Career Planner design decisions  
**Status**: Active Planning Document  
**Latest Changes**: Enhanced with Character Profile Page, In-Career Dialogue System, Condition Indicators, and Race Day States observations from comprehensive screenshot analysis (83 screenshots reviewed)

---

## Executive Summary

This document analyzes the actual Umamusume Pretty Derby game interface to extract design patterns, functional workflows, and UX conventions that should inform (but not copy) the Career Planner application design.

**Key Principle**: Align with game functions and mental models, but create a distinct web-optimized interface suitable for planning and tracking.

---

## 1. Game UI Analysis from Screenshots

### 1.1 Top Status Bar (Persistent Header)

**Observed Elements:**

- **Team Rank Badge** (B3, with rank number 153856)
- **TP (Training Points)**: 00:00 timer with 100/100 gauge (orange progress bar)
- **RP (Race Points)**: Timer (1:54:55) with 2/5 indicator (blue dots)
- **Currency Display**: 1,443,760 (coins icon)
- **Item Counter**: 12 (carrot/item icon)
- **Menu Button**: Green "NEW Menu" button (top right)

**Design Insights:**

- Persistent resource tracking at top
- Visual progress indicators (bars, dots, timers)
- Color coding: Orange (TP), Blue (RP), Yellow (currency)
- Always-visible status creates context awareness

**Career Planner Application:**

- Adapt persistent header with:
  - Current Turn / Total Turns (e.g., "Turn 15/78")
  - SP Available / Total SP
  - Current Stats Summary (compact view)
  - Storage Mode Badge (Local/Account)
  - Quick Actions Menu

---

### 1.2 Character/Trainee Management

**Observed Screens:**

#### Trainee Selection Grid

- Grid layout with character portraits
- Star rating system (1-5 stars, displayed as ★★★★☆)
- Potential Level badges (Lvl 1-5)
- Character thumbnails with clear visual hierarchy
- Filter controls: "Filters: OFF", "Stars", "Desc=" toggle
- Sorting options visible

#### Character Detail Tabs

Three-tab system observed:

1. **Potential Lvl** - Shows unlockable potential skills
2. **Hint Lvl** - Shows skill hints with SP costs
3. **Star Unlock** - Shows base stats and unique skills

**Stats Display Format:**

```
Speed:    1350/2000 [!] (Soft Cap 1200)
Stamina:  930/2000
Power:    850/2000
Guts:     1110/2000
Wit:      990/2000
```

**Design Insights:**

- Tabbed navigation for complex character data
- Clear current/max value display
- Visual icons for each stat type
- Color coding: Green for stats section headers
- Star rating as quality indicator

**Career Planner Application:**

- Character selection with similar grid layout
- Filter by aptitude grades, growth rates
- Tabbed character detail view:
  - Stats & Aptitudes
  - Factors & Inheritance
  - Skills & SP Budget
  - Training History
- Maintain stat format: current/max with progress bars

---

### 1.3 Character Profile Page

**Observed Elements:**

#### Profile Layout

- **Header**: Character name with dropdown indicator (top left, dark strip)
- **Character Art**: Full-body character artwork on left side (~50% of screen width)
- **Stats Panel** (right side):
  - **Bond Level**: Headphone icon with numeric level
  - **Total Fans**: Fan icon with count
  - **Careers Completed**: Trophy icon with count
  - **About**: Text description with character personality summary
- **Memories Section**: 3x2 icon grid navigation
  - `Profile` - Character stats
  - `Album` - Gallery/images
  - `Stories` - Story content
  - `Videos` - Cutscenes
  - `Voices` - Voice lines
  - `Epithets` - Titles/achievements

#### Outfit Variations

- Multiple outfits visible per character (school uniform, racing outfit, seasonal)
- Full character artwork with shadow and transparent background

**Design Insights:**

- Clean split layout with art emphasis
- Icon + label navigation pattern
- Numeric counters for progression metrics
- Character About text for personality context

**Career Planner Application:**

- Adopt similar split layout for character detail pages
- Use icon + label for navigation within character profiles
- Include character personality/description from game data
- Track Bond Level, Fans, and Career count per character

---

### 1.4 Support Card System

**Observed Elements:**

#### Support Card Display

- Card rarity: SSR (highest visible)
- Limit break indicators: Diamond symbols (◇◇◇◇)
- Card artwork with character portraits
- Type icons (Speed, Stamina, Power, Guts, Wit, Friend)

#### Support Formation (Deck Builder)

- **Layout**: 6-slot grid (upper row for main, lower for sub/friends)
- **Card Slots**: Show Rarity (SSR/SR), Level (Lvl 30-50), Type Icon, Limit Break diamonds
- **Controls**:
  - `Copy` button (top right)
  - `Reset` and `Auto-Fill` buttons (bottom)
  - `Start Career!` and `Perks` buttons
- **Bonuses Display**: Small icons below cards showing Type totals (e.g., Speed x3, Power x1)

#### Support Card Details

- **Unique Perk**: Named ability
- **Support Effects**:
  - Friendship Bonus: 25%
  - Mood Effect: 30%
  - Initial Friendship Gauge: 20
  - Race Bonus: 5%
- **Support Bonus**: +4.93% (aggregate)

**Design Insights:**

- Visual card metaphor with rich artwork
- Clear stat bonuses and effects
- Limit break progression visible
- Rarity tiers clearly marked

**Career Planner Application:**

- Support deck builder with 6 slots
- Card database with filtering:
  - By type (Speed/Stamina/Power/Guts/Wit/Friend)
  - By rarity (SSR/SR/R)
  - By limit break level
- Show aggregate bonuses for deck
- Synergy calculator
- Meta tier rankings (SS/S/A/B)

- Meta tier rankings (SS/S/A/B)

---

### 1.4 Career & Race Interface

**Observed Screens:**

#### Career Main Screen

- **Calendar/Turn**: "Junior Year Pre-Debut", "Senior Year Early Apr"
- **Turn Counter**: "7 turn(s) left"
- **Goal**: "Place top 3 in Victoria Mile" with "Entry criteria met!" status
- **Energy Bar**: Color-coded gauge with trend indicator (orange/green)
- **Mood**: "NORMAL" (orange arrow), "GREAT" (pink up arrow), "GOOD"
- **Trainee Event**: Banner notification (e.g., "Valentine's Day")

#### Race Results & History

- **Class Pyramid**: Hierarchy displaying Fan counts for each grade:
  - Legend: 320,000
  - Top Star: 240,000
  - Star: 160,000
  - Platinum (KEEP!): 100,000
  - Gold: 50,000
  - Silver: 20,000
  - Bronze: 5,000
  - Beginner: 1st place
  - Debut/Maiden
- **Career Record**: "Races: 10 Wins: 7"
- **Major Wins**: List with G1 badges (blue/silver/gold styling)
- **Rank Badge**: "C Rank" with Rating (e.g., 4,656)

#### Complete Career

- **Attributes Tab**: 5-stat pentagon radar chart (Speed, Stamina, Power, Guts, Wit)
- **Grades**: Letters (S, A, B, C, D, E) for stats
- **Aptitudes**:
  - Track (Turf/Dirt)
  - Distance (Sprint/Mile/Medium/Long)
  - Style (Front/Pace/Late/End)
- **Skill Pts**: Available points displayed (e.g., "7 pts")

#### In-Career Dialogue System

- **Dialogue Box Structure**:
  - Character name tab (rounded, colored background matching character theme)
  - White speech bubble with black text
  - Character portrait (small circular thumbnail) next to name
- **Trainee Event Banner**:
  - Orange/amber notification bar with star icon
  - Event title text (e.g., "Valentine's Day", "Fan Letter", "Sleep Deprived")
  - Positioned at top of character interaction area
- **Bottom Controls**:
  - `Skip Off` toggle button
  - `Quick` action button (amber highlight)
  - `Log` button with hamburger menu icon

#### Condition Indicators (Detailed)

- **GREAT** (pink): Up arrow, best condition
- **GOOD** (light blue): Slight up arrow
- **NORMAL** (orange): Flat arrow
- **BAD** (visibility varies): Down arrow indicator
- Badge positioned next to energy bar with trend arrow

#### Race Day States

- **Race Day**: Special red badge replaces turn counter when turn(s) left = 0
- **Finished**: Green badge for completed career

**Career Planner Application:**

- Implement condition indicator component with color + icon + label
- Create event notification system for training events
- Support all mood/condition states in planning
- Track race day scheduling in turn timeline

---

### 1.5 Scout / Gacha System

**Observed Elements:**

- **Scout Results**: Grid of 10 characters
- **Rarity**: 1-3 stars visible
- **Bonuses**: Item multipliers (x1, x5, x10, x60, x90)
- **Exchange Pts**: Progress bar (e.g., "50 > 60")
- **New Character**: Full-screen splash art with Epithet (e.g., "[Formula R] Maruzensky")

---

### 1.6 Enhancement/Upgrade Flow

**Observed Screens:**

#### Main Enhancement Menu

Three primary options:

1. **Trainee** - Character selection
2. **Support Cards** - Card management
3. **Veteran Umamusume** - Legacy character features

#### Scenario Selection

- **URA Finale**: "The Beginning" (Standard scenario)
- **Unity Cup**: "Shine On, Team Spirit!" (Modern scenario, confirms advanced stat potential)
- **Scenario Record**: Displays high scores (e.g., 11,303)

**Design Insights:**

- Hierarchical menu structure
- Clear categorization of upgrade types
- Visual character guide/mascot
- Dialogue boxes for context

**Career Planner Application:**

- Character management dashboard
- Skill acquisition planning interface
- Support deck configuration
- Import/Export data tools
- Settings and preferences

---

### 1.5 Navigation Pattern

**Bottom Navigation Bar (Persistent):**

- **Enhance** (pink highlight when active)
- **Story** (with "Limited Time NEW" badge)
- **Home** (central, larger icon)
- **Race** (with "Event Underway" badge)
- **Scout** (gacha/recruitment)

**Additional UI Elements:**

- **Club** button (top left area)
- **Concert Theater** (with notification badge)
- **Shop** (with "Daily" badge)
- **Career Event** (large promotional banner)
- **Special Missions** (side panel)

**Design Insights:**

- Bottom navigation for primary sections
- Central "Home" as anchor point
- Notification badges for time-sensitive content
- Event promotions prominently displayed

**Career Planner Application:**

- Simplified navigation:
  - Dashboard (Home)
  - Characters
  - Training Planner
  - Race Calendar
  - Skills & SP
  - Support Decks
- Responsive sidebar for desktop
- Bottom nav for mobile
- Breadcrumb navigation for deep pages

---

## 2. Color Palette & Visual Language

### 2.1 Stat Color Coding

From game screenshots:

- **Speed**: Blue (#3B82F6)
- **Stamina**: Green (#4CAF50 approximate)
- **Power**: Orange/Red (#FF5722 approximate)
- **Guts**: Orange (#FF9800 approximate)
- **Wit**: Blue (#2196F3 approximate)

### 2.2 UI Element Colors

- **Primary Actions**: Bright green (#8BC34A)
- **Secondary Actions**: White/cream backgrounds
- **Warnings/Alerts**: Pink/magenta
- **Information**: Blue tones
- **Success States**: Lime green
- **Headers**: Purple/navy gradients

### 2.3 Typography

- **Headers**: Bold, clear sans-serif
- **Body Text**: Brown/dark brown for readability
- **Labels**: Smaller, uppercase for categories
- **Numbers**: Prominent, easy to scan

**Career Planner Application:**

- Adopt similar stat color coding for consistency
- Use Tailwind's color system (Updated Jan 2026):
  - Speed: `blue-500` (#3B82F6)
  - Stamina: `green-500` (#22C55E)
  - Power: `orange-500` (#F97316)
  - Guts: `amber-400` (#FBBF24)
  - Wit: `sky-500` (#0EA5E9)
- Maintain high contrast for accessibility
- Support dark mode with adjusted palette

---

## 3. Functional Patterns to Adopt

### 3.1 Progressive Disclosure

**Game Pattern:**

- Main menu → Sub-menu → Detail view
- Tabs for related information
- Expandable sections

**Career Planner:**

- Dashboard overview → Detailed planning
- Tabbed interfaces for complex data
- Collapsible sections for optional details

### 3.2 Visual Feedback

**Game Pattern:**

- Progress bars for resources
- Badges for notifications
- Timers for time-sensitive actions
- Color changes for state

**Career Planner:**

- Progress indicators for turn completion
- Badges for unsaved changes
- Visual diff for prediction vs actual
- State indicators (draft, saved, completed)

### 3.3 Filtering & Sorting

**Game Pattern:**

- Toggle filters on/off
- Sort by multiple criteria
- Visual filter indicators

**Career Planner:**

- Filter characters by aptitude, status
- Sort skills by SP cost, type, acquisition
- Filter support cards by type, rarity
- Save filter presets

### 3.4 Resource Management Display

**Game Pattern:**

- Always-visible resource counters
- Visual gauges and progress bars
- Clear current/max displays

**Career Planner:**

- SP budget tracker (used/available)
- Turn counter (current/total)
- Stat progress bars (current/target)
- Training facility usage tracking

---

## 4. Key Differences (Planner vs Game)

### 4.1 Planning vs Real-Time

**Game**: Real-time decisions, immediate feedback
**Planner**: Pre-planning, prediction, analysis

**Implications:**

- Planner needs "what-if" scenarios
- Comparison views (planned vs actual)
- Historical tracking and analytics
- Export/import for data portability

### 4.2 Information Density

**Game**: Simplified, action-focused
**Planner**: Data-rich, analytical

**Implications:**

- More detailed stat breakdowns
- Calculation transparency
- Multiple view modes (simple/advanced)
- Customizable dashboard widgets

### 4.3 Workflow Optimization

**Game**: Linear progression through turns
**Planner**: Non-linear planning and editing

**Implications:**

- Jump to any turn
- Bulk edit capabilities
- Template system for common patterns
- Undo/redo functionality

---

## 5. Recommended UI Components

### 5.1 Dashboard Widgets

1. **Active Career Overview**
    - Character portrait
    - Current turn / total
    - Current stats with progress bars
    - Next race countdown
    - Quick actions

2. **SP Budget Tracker**
    - Available SP
    - Planned skill acquisitions
    - SP allocation chart

3. **Training Recommendations**
    - AI-suggested next training
    - Facility synergy indicators
    - Stat gap analysis

4. **Recent Activity**
    - Last edited plans
    - Import history
    - Completed careers

### 5.2 Character Card Component

```
┌─────────────────────────────┐
│ [Portrait]  Character Name  │
│             ★★★★☆           │
│                             │
│ Speed:    1350 ▓▓▓▓▓▓▓▓▓▓░ 2000 [!]│
│ Stamina:  493 ▓▓▓▓░░░░░░ 2000    │
│ Power:    777 ▓▓▓▓▓▓░░░░ 2000    │
│ Guts:     303 ▓▓░░░░░░░░ 2000    │
│ Wit:      398 ▓▓▓░░░░░░░ 2000    │
│                             │
│ [View Details] [Edit Plan]  │
└─────────────────────────────┘
```

### 5.3 Training Turn Component

```
┌─────────────────────────────┐
│ Turn 15 │ Junior - December │
│─────────────────────────────│
│ Training: Speed              │
│ Predicted Gains:             │
│   Speed: +12 (±2)           │
│   Power: +3                  │
│                             │
│ Support Cards Present:       │
│ [Card1] [Card2] [Card3]     │
│                             │
│ Actual Results: [Record]    │
└─────────────────────────────┘
```

### 5.4 Skill Acquisition Planner

```
┌─────────────────────────────┐
│ Skill Shop                  │
│─────────────────────────────│
│ Available SP: 450 / 800     │
│                             │
│ ☑ Speed Star (180 SP)       │
│   Level: 5/5 (-40%)         │
│   Final Cost: 108 SP        │
│                             │
│ ☐ Stamina Keeper (180 SP)   │
│   Level: 0/5                │
│   Final Cost: 180 SP        │
│                             │
│ [Add to Plan] [Clear All]   │
└─────────────────────────────┘
```

---

## 6. Responsive Design Strategy

### 6.1 Desktop (1280px+)

- Three-column layout
- Sidebar navigation
- Dashboard widgets in grid
- Detailed data tables
- Side-by-side comparisons

### 6.2 Tablet (768px - 1279px)

- Two-column layout
- Collapsible sidebar
- Stacked widgets
- Simplified tables
- Tabbed comparisons

### 6.3 Mobile (320px - 767px)

- Single column
- Bottom navigation
- Card-based layout
- Swipeable tabs
- Simplified data views

---

## 7. Accessibility Considerations

### 7.1 Color Independence

- Don't rely solely on color for stat differentiation
- Use icons + color + labels
- Ensure sufficient contrast ratios (WCAG AA)

### 7.2 Keyboard Navigation

- All actions accessible via keyboard
- Logical tab order
- Skip navigation links
- Focus indicators

### 7.3 Screen Reader Support

- Semantic HTML
- ARIA labels for complex widgets
- Status announcements for dynamic updates
- Alternative text for images

---

## 8. Implementation Priorities

### Phase 1: Core Layout

- [ ] Persistent header with status indicators
- [ ] Responsive navigation system
- [ ] Dashboard grid layout
- [ ] Character card component

### Phase 2: Data Display

- [ ] Stat visualization components
- [ ] Training turn timeline
- [ ] Skill acquisition interface
- [ ] Support deck builder

### Phase 3: Advanced Features

- [ ] Filtering and sorting
- [ ] Comparison views
- [ ] Analytics dashboard
- [ ] Export/import UI

### Phase 4: Polish

- [ ] Animations and transitions
- [ ] Loading states
- [ ] Error handling UI
- [ ] Dark mode refinement

---

## 9. Design System Alignment

### 9.1 Tailwind Configuration

// tailwind.config.js additions (Verified)
theme: {
extend: {
colors: {
'uma-speed': '#3B82F6', // Blue
'uma-stamina': '#22C55E', // Green
'uma-power': '#F97316', // Orange
'uma-guts': '#FBBF24', // Amber
'uma-wit': '#0EA5E9', // Sky
'uma-primary': '#84CC16', // Lime (Success)
'uma-secondary': '#FFFFFF',
}
}
}

```

### 9.2 Component Library

- Leverage existing Blade components
- Create Livewire components for interactive elements
- Alpine.js for client-side interactivity
- Reusable stat bar component
- Card grid component
- Filter panel component

---

## 10. Next Steps

1. **Create Wireframes**: Based on this analysis, create detailed wireframes for key screens
2. **Component Inventory**: List all reusable components needed
3. **Data Flow Mapping**: Map how data flows through the UI
4. **Prototype Key Interactions**: Build interactive prototypes for complex workflows
5. **User Testing**: Validate design decisions with target users

---

## Document Control

**Version History:**

- v1.1.0 (2026-01-28): Enhanced with Character Profile Page, In-Career Dialogue System, Condition Indicators, and Race Day States observations from comprehensive 83-screenshot analysis
- v1.0.0 (2026-01-28): Initial analysis based on game screenshots

**Related Documents:**

- `docs/01-wireframes/` - UI wireframes
- `docs/01-user-flows/` - User journey maps
- `docs/00-core-docs/004_SDS_Software_Design_Specifications.md`
- `docs/00-core-docs/017_SUM_Software_User_Manual.md`

**Approval Status**: Draft - Pending Review
```
