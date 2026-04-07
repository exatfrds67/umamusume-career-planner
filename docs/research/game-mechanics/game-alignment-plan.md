# Umamusume Career Planner - Game Alignment Implementation Plan

**Document Version**: 1.0.0  
**Date**: January 28, 2026  
**Purpose**: Actionable implementation plan for game-aligned UI/UX  
**Status**: Active Planning Document

---

## Overview

This document translates the game UI analysis into concrete implementation tasks for the Career Planner application, ensuring alignment with player mental models while optimizing for web-based planning workflows.

---

## 1. Core Design Principles

### 1.1 Alignment Without Imitation

- **DO**: Use similar color coding, terminology, and mental models
- **DON'T**: Copy exact layouts, artwork, or visual style
- **GOAL**: Familiar to players, optimized for planning

### 1.2 Web-First Optimization

- Responsive design (mobile to desktop)
- Fast load times and interactions
- Keyboard shortcuts for power users
- Progressive enhancement

### 1.3 Data-Driven Planning

- Transparency in calculations
- What-if scenario modeling
- Historical tracking and analytics
- Import/export flexibility

---

## 2. UI Component Roadmap

### 2.1 Header Component (Priority: HIGH)

**Requirements:**

- Persistent across all pages
- Display current context (character, turn, storage mode)
- Quick access to key metrics
- Responsive collapse on mobile

**Implementation:**

```blade
<x-layouts.app-header>
  <x-slot:left>
    <x-storage-mode-badge :mode="$storageMode" />
    <x-character-quick-info :character="$character" />
  </x-slot:left>

  <x-slot:center>
    <x-turn-indicator :current="$turn" :total="$totalTurns" />
  </x-slot:center>

  <x-slot:right>
    <x-sp-tracker :available="$sp" :total="$totalSp" />
    <x-menu-dropdown />
  </x-slot:right>
</x-layouts.app-header>
```text

**Tasks:**

- [ ] Create header Blade component
- [ ] Implement responsive behavior
- [ ] Add storage mode badge
- [ ] Create turn indicator component
- [ ] Build SP tracker widget

---

### 2.2 Navigation System (Priority: HIGH)

**Desktop Navigation:**

- Sidebar with collapsible sections
- Icon + label for clarity
- Active state indicators
- Keyboard shortcuts

**Mobile Navigation:**

- Bottom tab bar (5 primary sections)
- Hamburger menu for secondary
- Swipe gestures

**Implementation Tasks:**

- [ ] Create responsive nav component
- [ ] Implement active state logic
- [ ] Add keyboard navigation
- [ ] Build mobile bottom nav
- [ ] Add breadcrumb component

---

### 2.3 Stat Display Components (Priority: HIGH)

**Stat Bar Component:**

```blade
<x-stat-bar
  stat="speed"
  :current="1350"
  :max="2000"
  :target="1600"
  show-icon
  show-percentage
/>
```

**Features:**

- Color-coded by stat type (Rose/Green/Orange/Amber/Sky)
- Progress bar visualization with overflow for >1200
- **Soft Cap Indicator**: Visual marker at 1200
- Current/max/target display
- Optional percentage
- Responsive sizing

**Tasks:**

- [ ] Create stat-bar Blade component
- [ ] Add stat color configuration
- [ ] Implement progress calculation
- [ ] Add target indicator overlay
- [ ] Create stat-grid wrapper component

---

### 2.4 Character Card Component (Priority: HIGH)

**Display Modes:**

- Grid view (thumbnail + key stats)
- List view (detailed inline)
- Detail view (full information)

**Tasks:**

- [ ] Create character-card component
- [ ] Add star rating display
- [ ] Implement aptitude badges
- [ ] Add quick action buttons
- [ ] Create character-grid layout

---

### 2.5 Training Turn Component (Priority: MEDIUM)

**Features:**

- Turn number and phase display
- Training facility selection
- Predicted stat gains
- Support card presence indicators
- Actual results recording

**Tasks:**

- [ ] Create training-turn component
- [ ] Build facility selector
- [ ] Add prediction display
- [ ] Implement actual vs predicted comparison
- [ ] Create turn timeline view

---

### 2.6 Skill Management Interface (Priority: MEDIUM)

**Components:**

- Skill catalog browser
- Skill acquisition planner
- SP budget calculator
- Hint tracker
- Evolution path visualizer

**Tasks:**

- [ ] Create skill-card component
- [ ] Build skill search/filter
- [ ] Implement SP calculator (with 10%/5% hint discount logic)
- [ ] Add hint level tracker (Max Level 5)
- [ ] Create skill evolution tree

---

### 2.7 Support Deck Builder (Priority: MEDIUM)

**Features:**

- 6-slot deck configuration
- Card database browser
- Synergy calculator
- Bonus aggregation
- Meta tier display

**Tasks:**

- [ ] Create support-card component
- [ ] Build 6-slot deck interface
- [ ] Implement card search/filter
- [ ] Add synergy calculation
- [ ] Create bonus summary panel

---

## 3. Page-Level Implementation

### 3.1 Dashboard (Priority: HIGH)

**Layout:**

```text
┌─────────────────────────────────────┐
│ Header (persistent)                 │
├─────────┬───────────────────────────┤
│ Nav     │ Active Career Widget      │
│ (side)  ├───────────────────────────┤
│         │ SP Budget │ Next Training │
│         ├───────────┴───────────────┤
│         │ Recent Activity           │
└─────────┴───────────────────────────┘
```

**Tasks:**

- [ ] Create dashboard layout
- [ ] Build active career widget
- [ ] Implement SP budget widget
- [ ] Add training recommendation widget
- [ ] Create recent activity list

---

### 3.2 Character Management (Priority: HIGH)

**Views:**

- Character list/grid
- Character detail (tabbed)
- Character creation wizard
- Character comparison

**Tasks:**

- [ ] Create character index page
- [ ] Build character detail tabs
- [ ] Implement character form
- [ ] Add comparison view
- [ ] Create character import UI

---

### 3.3 Training Planner (Priority: HIGH)

**Features:**

- Turn-by-turn planning
- Timeline visualization
- Bulk edit capabilities
- Template system
- AI recommendations

**Tasks:**

- [ ] Create training planner layout
- [ ] Build turn timeline component
- [ ] Implement bulk edit modal
- [ ] Add template selector
- [ ] Integrate AI recommendation display

---

### 3.4 Race Calendar (Priority: MEDIUM)

**Features:**

- Race schedule display
- Race requirements checker
- Performance predictor
- Results tracker

**Tasks:**

- [ ] Create race calendar view
- [ ] Build race detail modal
- [ ] Implement requirements checker
- [ ] Add prediction display
- [ ] Create results form

---

### 3.5 Skills & SP Management (Priority: MEDIUM)

**Features:**

- Skill catalog browser
- Acquisition planner
- SP budget tracker
- Hint management
- Evolution paths

**Tasks:**

- [ ] Create skill catalog page
- [ ] Build skill detail modal
- [ ] Implement acquisition planner
- [ ] Add SP budget visualization
- [ ] Create evolution path diagram

---

### 3.6 Support Deck Builder (Priority: MEDIUM)

**Features:**

- Deck configuration
- Card database
- Synergy analysis
- Meta rankings
- Deck templates

**Tasks:**

- [ ] Create deck builder page
- [ ] Build card database browser
- [ ] Implement synergy calculator
- [ ] Add meta tier display
- [ ] Create deck template system

---

## 4. Color System Implementation

### 4.1 Tailwind Configuration

**File:** `tailwind.config.js`

```javascript
export default {
    theme: {
        extend: {
            colors: {
                // Verified Stat Colors (from game-ui-alignment-strategy.md)
                "stat-speed": {
                    DEFAULT: "#FB7185", // rose-500
                    light: "#FDA4AF",
                    dark: "#E11D48",
                },
                "stat-stamina": {
                    DEFAULT: "#22C55E", // green-500
                    light: "#4ADE80",
                    dark: "#16A34A",
                },
                "stat-power": {
                    DEFAULT: "#F97316", // orange-500
                    light: "#FB923C",
                    dark: "#EA580C",
                },
                "stat-guts": {
                    DEFAULT: "#FBBF24", // amber-400
                    light: "#FCD34D",
                    dark: "#D97706",
                },
                "stat-wit": {
                    DEFAULT: "#0EA5E9", // sky-500
                    light: "#38BDF8",
                    dark: "#0284C7",
                },

                // UI colors
                "uma-primary": "#84CC16", // lime-500 (Success)
                "uma-secondary": "#FFFFFF",
                "uma-accent": "#FB7185",
            },
        },
    },
};
```text

**Tasks:**

- [ ] Update Tailwind config
- [ ] Create stat color utility classes
- [ ] Document color usage guidelines
- [ ] Test dark mode variants
- [ ] Verify WCAG AA compliance

---

## 5. Responsive Breakpoints

### 5.1 Breakpoint Strategy

```javascript
// Tailwind breakpoints
sm: '640px',   // Mobile landscape
md: '768px',   // Tablet portrait
lg: '1024px',  // Tablet landscape / Small desktop
xl: '1280px',  // Desktop
2xl: '1536px', // Large desktop
```

### 5.2 Layout Adaptations

**Mobile (< 768px):**

- Single column
- Bottom navigation
- Stacked widgets
- Simplified tables
- Swipeable tabs

**Tablet (768px - 1023px):**

- Two columns where appropriate
- Collapsible sidebar
- Grid widgets (2 columns)
- Responsive tables
- Tabbed navigation

**Desktop (1024px+):**

- Multi-column layouts
- Persistent sidebar
- Grid widgets (3-4 columns)
- Full data tables
- Side-by-side comparisons

**Tasks:**

- [ ] Define responsive component variants
- [ ] Test on target devices
- [ ] Optimize touch targets (44px min)
- [ ] Implement responsive images
- [ ] Add viewport meta tags

---

## 6. Accessibility Implementation

### 6.1 WCAG 2.2 AA Compliance

**Requirements:**

- Color contrast ratio ≥ 4.5:1 (text)
- Color contrast ratio ≥ 3:1 (UI components)
- Keyboard navigation for all functions
- Screen reader support
- Focus indicators
- Skip navigation links

**Tasks:**

- [ ] Audit color contrast
- [ ] Implement keyboard shortcuts
- [ ] Add ARIA labels
- [ ] Test with screen readers
- [ ] Add focus indicators
- [ ] Create skip links

---

### 6.2 Keyboard Navigation

**Global Shortcuts:**

- `?` - Show keyboard shortcuts
- `/` - Focus search
- `Esc` - Close modals/dropdowns
- `Tab` - Navigate forward
- `Shift+Tab` - Navigate backward

**Page-Specific:**

- `n` - New character/plan
- `e` - Edit current item
- `s` - Save changes
- `←/→` - Navigate turns
- `1-9` - Quick navigation

**Tasks:**

- [ ] Implement keyboard event handlers
- [ ] Create shortcuts modal
- [ ] Add visual indicators
- [ ] Document shortcuts
- [ ] Test keyboard-only navigation

---

## 7. Performance Optimization

### 7.1 Loading Strategy

**Critical Path:**

1. HTML shell
2. Critical CSS (inline)
3. Core JavaScript
4. Deferred assets

**Lazy Loading:**

- Images below fold
- Secondary navigation
- Analytics widgets
- Non-critical features

**Tasks:**

- [ ] Implement code splitting
- [ ] Add lazy loading for images
- [ ] Optimize Livewire payloads
- [ ] Enable browser caching
- [ ] Minimize render-blocking resources

---

### 7.2 Performance Targets

**Core Web Vitals:**

- LCP (Largest Contentful Paint): < 2.5s
- FID (First Input Delay): < 100ms
- CLS (Cumulative Layout Shift): < 0.1

**Additional Metrics:**

- Time to Interactive: < 3.5s
- Total Blocking Time: < 300ms
- Speed Index: < 3.4s

**Tasks:**

- [ ] Set up Lighthouse CI
- [ ] Monitor Core Web Vitals
- [ ] Optimize critical rendering path
- [ ] Reduce JavaScript bundle size
- [ ] Implement performance budgets

---

## 8. Implementation Timeline

### Phase 1: Foundation (Weeks 1-2)

- [ ] Set up Tailwind configuration
- [ ] Create base layout components
- [ ] Implement header and navigation
- [ ] Build stat display components
- [ ] Create character card component

### Phase 2: Core Pages (Weeks 3-4)

- [ ] Dashboard implementation
- [ ] Character management pages
- [ ] Training planner interface
- [ ] Basic skill management
- [ ] Support deck builder

### Phase 3: Advanced Features (Weeks 5-6)

- [ ] AI recommendation integration
- [ ] Analytics dashboard
- [ ] Comparison views
- [ ] Import/export UI
- [ ] Template system

### Phase 4: Polish & Testing (Weeks 7-8)

- [ ] Accessibility audit
- [ ] Performance optimization
- [ ] Cross-browser testing
- [ ] Mobile device testing
- [ ] User acceptance testing

---

## 9. Testing Strategy

### 9.1 Component Testing

**Tools:** Pest + Browser Testing

```php
it('displays stat bar correctly', function () {
    $page = visit('/characters/1');

    $page->assertSee('Speed')
        ->assertSee('755 / 1200')
        ->assertElementExists('.stat-bar-speed')
        ->assertNoJavascriptErrors();
});
```text

**Tasks:**

- [ ] Write component tests
- [ ] Test responsive behavior
- [ ] Verify accessibility
- [ ] Test keyboard navigation
- [ ] Validate color contrast

---

### 9.2 Integration Testing

**Scenarios:**

- Character creation flow
- Training plan editing
- Skill acquisition planning
- Support deck building
- Data import/export

**Tasks:**

- [ ] Write integration tests
- [ ] Test dual storage modes
- [ ] Verify data persistence
- [ ] Test error handling
- [ ] Validate edge cases

---

### 9.3 User Acceptance Testing

**Test Groups:**

- Experienced players
- New players
- Accessibility users
- Mobile-only users
- Power users

**Tasks:**

- [ ] Recruit test users
- [ ] Create test scenarios
- [ ] Conduct usability sessions
- [ ] Gather feedback
- [ ] Iterate on findings

---

## 10. Documentation Requirements

### 10.1 Developer Documentation

**Topics:**

- Component API reference
- Styling guidelines
- Accessibility standards
- Performance best practices
- Testing procedures

**Tasks:**

- [ ] Document all components
- [ ] Create style guide
- [ ] Write contribution guidelines
- [ ] Document build process
- [ ] Create troubleshooting guide

---

### 10.2 User Documentation

**Topics:**

- Getting started guide
- Feature tutorials
- Keyboard shortcuts
- Import/export guide
- FAQ

**Tasks:**

- [ ] Write user manual
- [ ] Create video tutorials
- [ ] Build interactive help
- [ ] Document workflows
- [ ] Create FAQ section

---

## 11. Success Metrics

### 11.1 Technical Metrics

- Lighthouse score: ≥ 90
- Core Web Vitals: All green
- Test coverage: ≥ 80%
- Accessibility score: 100%
- Browser compatibility: 95%+

### 11.2 User Metrics

- Task completion rate: ≥ 90%
- Time to complete key tasks: < baseline
- User satisfaction: ≥ 4.5/5
- Error rate: < 5%
- Return user rate: ≥ 60%

---

## Document Control

**Version History:**

- v1.0.0 (2026-01-28): Initial implementation plan

**Related Documents:**

- `docs/design/game-alignment-analysis.md` - UI analysis
- `docs/01-wireframes/` - Detailed wireframes
- `docs/00-core-docs/004_SDS_Software_Design_Specifications.md`

**Next Review Date:** 2026-02-15
