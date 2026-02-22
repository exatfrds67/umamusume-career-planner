# Implementation Plan Updates from Research & Wireframes

**Document Version**: 1.0.0  
**Date**: February 22, 2026  
**Purpose**: Key updates to incorporate from research and wireframe documentation

---

## Critical Game Mechanics Corrections

### 1. Skill Hint System (VERIFIED)

**Source**: `docs/research/game-mechanics-research-report.md`

**Correction Required**:

- Levels 1-3: 10% discount each (cumulative: 10%, 20%, 30%)
- Levels 4-5: 5% discount each (cumulative: 35%, 40%)
- Maximum 5 hint levels (not 2 as previously documented)
- Maximum discount: 40% (5 hint levels, not 2 as previously documented)

**Additional Discount Sources**:

- "Fast Learner" Condition: +10% discount
- Skill Sparks (Inheritance): Bonus discount based on star rating
- Hint Books: Green (white skills), Gold (rare skills)

**Implementation Impact**:

- Update `SkillHintService::calculateDiscount()` method
- Update UI to show hint levels 0-5 (not 0-2)
- Add Fast Learner condition check
- Add skill spark discount calculation

---

### 2. Aptitude System (VERIFIED)

**Source**: `docs/research/game-mechanics-research-report.md`

**Correction Required**:

- Maximum aptitude grade is **S** (not SS)
- A-rank is baseline (0% bonus/penalty)
- Only S-rank provides positive bonuses
- All grades below A incur penalties

**Aptitude Performance Modifiers**:

| Rank | Surface (Power) | Distance (Speed) | Style (Wit)   |
| ---- | --------------- | ---------------- | ------------- |
| S    | +5%             | +5%              | +10%          |
| A    | 0% (baseline)   | 0% (baseline)    | 0% (baseline) |
| B    | -10%            | -10%             | -15%          |
| C    | -20%            | -20%             | -25%          |
| D    | -30%            | -40%             | -40%          |
| E    | -50%            | -60%             | -60%          |
| F    | -70%            | -80%             | -80%          |
| G    | -90%            | -90%             | -90%          |

**Implementation Impact**:

- Remove SS grade from all aptitude displays
- Update grade calculation logic
- Update aptitude modifier calculations
- Update UI to show correct grade scale (G-S)

---

### 3. Stat Range & Soft Cap (VERIFIED)

**Source**: `docs/research/game-mechanics-research-report.md`

**Correction Required**:

- Stats can exceed 1200 (not capped at 1200)
- Diminishing returns: Stats >1200 count for half value
- Important breakpoints: 901, 1200, 1600
- Special mechanics at 1200+: "Stamina Contest" buff

**Implementation Impact**:

- Update stat bar component to support values >1200
- Add soft cap indicator at 1200 mark
- Add visual indicator for diminishing returns zone
- Update stat calculation formulas

---

### 4. Weather & Track Conditions (VERIFIED)

**Source**: `docs/research/game-mechanics-research-report.md`

**Track Condition Mechanics**:

| Condition | Surface   | Power Penalty | Speed Penalty | Stamina Drain |
| --------- | --------- | ------------- | ------------- | ------------- |
| Firm      | Turf/Dirt | None          | None          | None          |
| Good      | Turf      | -50           | None          | None          |
| Good      | Dirt      | -50           | None          | None          |
| Soft      | Turf      | -50           | None          | +2%/sec       |
| Soft      | Dirt      | -100          | None          | +2%/sec       |
| Heavy     | Turf      | -50           | -50           | +2%/sec       |
| Heavy     | Dirt      | -100          | -50           | +2%/sec       |

**Implementation Impact**:

- Update race readiness calculator with correct penalties
- Add track condition impact to race predictions
- Update weather-related skill recommendations

---

## Wireframe-Specific Requirements

### 5. Dashboard Components (WF-001)

**Required Components**:

- [ ] Run selector dropdown in header
- [ ] Turn counter with career stage display
- [ ] Energy gauge with trend indicator
- [ ] Mood/condition badge with color coding
- [ ] Goals panel with progress tracking
- [ ] Training suggestions with AI badge
- [ ] Upcoming races panel (3 races)
- [ ] Recent activity timeline
- [ ] AI advisor card with confidence %

**Key Features**:

- Real-time WebSocket updates for stats
- Responsive grid (1/2/3/4 columns based on viewport)
- Widget customization (show/hide)
- Keyboard shortcuts (Alt+R for run selector, Alt+A for AI)

---

### 6. Training Selection Interface (WF-004)

**Required Components**:

- [ ] Training prediction cards (6 facilities)
- [ ] AI recommendation badge on best option
- [ ] Risk level badges (Low/Medium/High with colors)
- [ ] Support card indicators with red exclamation (🔴)
- [ ] Friendship training indicator (💛)
- [ ] Skill hint display with guaranteed indicator
- [ ] Stat gains breakdown
- [ ] Energy cost display
- [ ] Efficiency rating (★★★★★)

**Key Features**:

- High-risk training confirmation modal
- Expandable/collapsible prediction cards
- Real-time prediction updates
- Support card presence tracking

---

### 7. Skill Shop Interface (WF-008)

**Required Components**:

- [ ] SP balance widget (current/earned/spent/planned)
- [ ] Skill search with English/Japanese support
- [ ] Filter panel (type, rarity, distance, style)
- [ ] Skill cards with hint level display (0-5)
- [ ] Final cost calculation with discount breakdown
- [ ] AI recommendations panel
- [ ] Skill evolution path display
- [ ] Skill detail modal

**Key Features**:

- Hint discount visualization (5 levels: 10%/20%/30%/35%/40% indicators)
- SP budget status (healthy/moderate/tight/deficit)
- Skill planning (mark as planned before acquiring)
- Related skills display

---

### 8. Support Deck Builder (WF-011)

**Required Components**:

- [ ] 6-slot deck grid
- [ ] Deck overview widget (score, meta tier avg, bond avg)
- [ ] Deck validation panel
- [ ] Card library browser with filters
- [ ] Deck analysis panel
- [ ] Auto-optimize button
- [ ] Synergy score calculator
- [ ] Type distribution display

**Key Features**:

- Drag-and-drop card placement
- Real-time validation feedback
- Meta tier badges (SS/S/A/B)
- Limit break indicators (◇◇◇◆)
- Bond level progress bars
- Type distribution warnings

---

## Additional UI Patterns from Game Analysis

### 9. Character Profile Components

**From game-alignment-analysis.md**:

- [ ] Character profile split layout (art left, info right)
- [ ] Memories grid navigation (3x2 icon grid)
- [ ] Bond level display with headphone icon
- [ ] Total fans counter
- [ ] Careers completed counter
- [ ] Character "About" text section
- [ ] Outfit variations display

---

### 10. Dialogue & Event System

**From game-alignment-analysis.md**:

- [ ] Dialogue box with speech bubble styling
- [ ] Speaker tab with colored name indicator
- [ ] Trainee event banner (orange notification bar)
- [ ] Dialogue controls (Skip Off, Quick, Log buttons)
- [ ] Conversation log component

---

### 11. Race System Components

**From game-alignment-analysis.md**:

- [ ] Class pyramid display (fan count hierarchy)
- [ ] Race grade badges (G1 gold, G2 silver, G3 bronze)
- [ ] Race record display ("Races: 10 Wins: 7")
- [ ] Major wins list with G1 achievement medals
- [ ] Rank badge (S/A/B/C/D with rating number)
- [ ] Race day badge (red indicator when turn(s) left = 0)
- [ ] Finished badge (green for completed career)

---

## Performance & Accessibility Requirements

### 12. Performance Targets (from WF-001)

| Metric                       | Target | Measurement         |
| ---------------------------- | ------ | ------------------- |
| **Initial Load**             | < 2.0s | Time to Interactive |
| **First Contentful Paint**   | < 1.5s | Lighthouse          |
| **Largest Contentful Paint** | < 2.5s | Lighthouse          |
| **Time to Interactive**      | < 3.0s | Lighthouse          |
| **Cumulative Layout Shift**  | < 0.1  | Lighthouse          |

### 13. Accessibility Requirements (from WF-001, WF-004, WF-008, WF-011)

**WCAG 2.2 AA Compliance**:

- [ ] All icons have `aria-label` attributes
- [ ] Color contrast ratio ≥ 4.5:1 for text
- [ ] All interactive elements keyboard accessible
- [ ] Logical focus order (Tab/Shift+Tab)
- [ ] Clear focus indicators
- [ ] Screen reader announcements for dynamic updates
- [ ] ARIA live regions for stat updates
- [ ] Proper ARIA attributes on all controls

**Keyboard Shortcuts** (from WF-001):

- `Alt+R` - Open run selector
- `Alt+U` - Open user menu
- `Alt+S` - Focus sidebar
- `Alt+A` - Open AI advisor
- `F5` or `Ctrl+R` - Refresh dashboard
- `Tab` / `Shift+Tab` - Navigate panels
- `Esc` - Close modals/dropdowns

---

## Testing Requirements

### 14. Test Coverage (from wireframes)

**Unit Tests**:

- [ ] Hint discount calculation (5 levels)
- [ ] Aptitude modifier calculation (G-S grades)
- [ ] Stat soft cap logic (>1200 handling)
- [ ] Track condition penalties
- [ ] Synergy score calculation
- [ ] Deck validation rules

**Feature Tests**:

- [ ] Training selection flow
- [ ] Skill acquisition with hints
- [ ] Deck building and validation
- [ ] Character stat updates
- [ ] Race readiness calculation

**E2E Tests (Playwright)**:

- [ ] Dashboard widget interactions
- [ ] Training prediction and execution
- [ ] Skill shop browsing and acquisition
- [ ] Deck builder card selection
- [ ] AI recommendation acceptance

**Accessibility Tests**:

- [ ] axe-core automated scans
- [ ] Keyboard-only navigation
- [ ] Screen reader compatibility
- [ ] Color contrast validation

---

## Implementation Priority Matrix

### Phase 1 (Weeks 1-2) - Foundation

1. Tailwind color system with game-accurate colors
2. Base layout components (header, nav, breadcrumbs)
3. Icon system with all game indicators
4. Typography scale

### Phase 2 (Weeks 3-4) - Core Display

1. Stat bar with soft cap indicator
2. Character card components
3. Condition/mood badges
4. Grade badges (S/A/B/C/D/E/F/G)
5. Turn counter with stage display

### Phase 3 (Weeks 5-6) - Interactive Features

1. Training prediction cards with AI badges
2. Skill cards with 5-level hint display
3. Support card components with limit break
4. Deck builder 6-slot grid

### Phase 4 (Weeks 7-9) - Pages

1. Dashboard with all widgets
2. Training selection interface
3. Skill shop with SP tracking
4. Deck builder with validation

### Phase 5 (Weeks 10-11) - Advanced

1. Dialogue & event system
2. Analytics dashboard
3. AI advisor integration

### Phase 6 (Week 12) - Data Management

1. Import/export with validation
2. OCR integration

### Phase 7 (Weeks 13-14) - Polish

1. Accessibility audit
2. Performance optimization
3. Cross-browser testing

### Phase 8 (Week 15) - Testing & Docs

1. Comprehensive test suite
2. Documentation updates

---

## Document Control

**Version History**:

| Version | Date       | Changes                                                |
| ------- | ---------- | ------------------------------------------------------ |
| 1.0.0   | 2026-01-28 | Initial updates from research and wireframe documents |

**Related Documents**:

- [IMPLEMENTATION_PLAN.md](IMPLEMENTATION_PLAN.md)
- [game-mechanics-research-report.md](../research/game-mechanics-research-report.md)
- [game-alignment-analysis.md](game-alignment-analysis.md)
- [000_WIREFRAMES_INDEX.md](../01-wireframes/000_WIREFRAMES_INDEX.md)
