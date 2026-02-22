# Component Inventory - Game-Aligned UI Components

**Document Version**: 1.0.0  
**Date**: January 28, 2026  
**Status**: Active Design Document  
**Related Documents**: [game-alignment-analysis.md], [000_WIREFRAMES_INDEX.md]

---

## Overview

This document provides a comprehensive inventory of all UI components required for the Umamusume Career Planner, organized by category and aligned with game UI patterns observed from 83 screenshots.

---

## 1. Layout Components

### 1.1 App Shell

| Component           | Description                               | Game Pattern Reference          | Status       |
| ------------------- | ----------------------------------------- | ------------------------------- | ------------ |
| `AppLayout`         | Main application shell with nav + content | Persistent status bar pattern   | Implemented  |
| `SidebarNavigation` | Desktop sidebar navigation                | Bottom nav converted to sidebar | Implemented  |
| `BottomNavBar`      | Mobile bottom navigation                  | Matches game's 5-tab bottom nav | Implemented  |
| `TopStatusBar`      | Persistent header with key metrics        | Game top bar: TP, RP, Currency  | Implemented  |
| `Breadcrumb`        | Hierarchical navigation path              | Progressive disclosure pattern  | Implemented  |

### 1.2 Page Layouts

| Component           | Description                | Wireframe Reference            |
| ------------------- | -------------------------- | ------------------------------ |
| `DashboardGrid`     | Multi-column widget layout | WF-001                         |
| `DetailSplitLayout` | Character art + info split | Observed in character profiles |
| `ListDetailLayout`  | Master-detail pattern      | WF-010 Card Collection         |
| `WizardLayout`      | Multi-step form container  | WF-002 Character Creation      |

---

## 2. Data Display Components

### 2.1 Stats & Progress

| Component         | Props                           | Game Pattern                      | Status       |
| ----------------- | ------------------------------- | --------------------------------- | ------------ |
| `StatBar`         | `stat`, `value`, `max`, `grade` | Horizontal bar with grade letter  | Implemented  |
| `StatRadarChart`  | `stats[]`                       | Pentagon radar on career complete | Planned      |
| `ProgressBar`     | `current`, `max`, `color`       | Energy bar pattern                | Implemented  |
| `GradeBadge`      | `grade: S\|A\|B\|C\|D\|E\|F\|G` | Letter grade indicators           | Implemented  |
| `AptitudeDisplay` | `type`, `grade`, `bonus`        | Track/Distance/Style aptitudes    | Implemented  |

**Stat Colors (Tailwind v4)**:

```css
/* @theme block */
--color-uma-speed: #3b82f6; /* blue-500 */
--color-uma-stamina: #22c55e; /* green-500 */
--color-uma-power: #f97316; /* orange-500 */
--color-uma-guts: #fbbf24; /* amber-400 */
--color-uma-wit: #0ea5e9; /* sky-500 */
```

### 2.2 Character Components

| Component           | Props                    | Game Pattern              | Status       |
| ------------------- | ------------------------ | ------------------------- | ------------ |
| `CharacterCard`     | `character`, `showStats` | Grid cards in selection   | Implemented  |
| `CharacterPortrait` | `image`, `size`, `badge` | Character art with border | Implemented  |
| `StarRating`        | `stars: 1-5`, `filled`   | ★★★☆☆ unlock/rarity       | Implemented  |
| `PotentialBadge`    | `level: 1-9`             | Level indicator on cards  | Implemented  |
| `CharacterProfile`  | `character`, `outfit`    | Split layout with art     | Implemented  |
| `MemoriesGrid`      | `items[]`                | 3x2 navigation grid       | Implemented  |

### 2.3 Career Status Components

| Component            | Props                                 | Game Pattern                | Status       |
| -------------------- | ------------------------------------- | --------------------------- | ------------ |
| `TurnCounter`        | `current`, `total`, `stage`           | "Junior Year Pre-Debut" etc | Implemented  |
| `ConditionBadge`     | `condition: GREAT\|GOOD\|NORMAL\|BAD` | Mood arrows                 | Implemented  |
| `EnergyGauge`        | `value`, `trend: up\|down\|flat`      | Green/orange energy bar     | Implemented  |
| `RaceDayBadge`       | `daysLeft`, `isRaceDay`               | Red "Race Day" indicator    | Planned      |
| `GoalProgress`       | `goal`, `current`, `target`           | G1 goal tracking            | Planned      |
| `TraineeEventBanner` | `event`, `icon`                       | Orange notification bar     | Planned      |

**Condition Colors**:

| Condition | Color                  | Icon         |
| --------- | ---------------------- | ------------ |
| GREAT     | Pink (`#EC4899`)       | ↑↑ Up arrow  |
| GOOD      | Light Blue (`#60A5FA`) | ↑ Up arrow   |
| NORMAL    | Orange (`#F97316`)     | → Flat arrow |
| BAD       | Red (`#EF4444`)        | ↓ Down arrow |

### 2.4 Race Components

| Component        | Props                           | Game Pattern          | Status      |
| ---------------- | ------------------------------- | --------------------- | ----------- |
| `ClassPyramid`   | `currentClass`, `fans`          | Fan count hierarchy   | Planned     |
| `RaceCard`       | `race`, `readiness`, `winProb`  | Upcoming race display | Implemented |
| `RaceGradeBadge` | `grade: G1\|G2\|G3\|OP`         | G1/G2/G3 styling      | Planned     |
| `RaceRecord`     | `wins`, `races`, `majorWins`    | "Races: 10 Wins: 7"   | Planned     |
| `MajorWinsList`  | `wins[]`                        | G1 achievement medals | Planned     |
| `RankBadge`      | `rank: S\|A\|B\|C\|D`, `rating` | "C Rank 4,656"        | Planned     |

### 2.5 Support Card Components

| Component             | Props                                            | Game Pattern              | Status       |
| --------------------- | ------------------------------------------------ | ------------------------- | ------------ |
| `SupportCard`         | `card`, `level`, `limitBreak`                    | Full card display         | Implemented  |
| `SupportCardMini`     | `card`, `showBond`                               | Deck slot display         | Planned      |
| `LimitBreakIndicator` | `current`, `max`                                 | ◇◇◇◆ diamonds             | Planned      |
| `TypeIcon`            | `type: Speed\|Stamina\|Power\|Guts\|Wit\|Friend` | Type badges               | Implemented  |
| `DeckSlot`            | `card?`, `position`                              | 6-slot grid               | Implemented  |
| `BondMeter`           | `value`, `threshold`                             | 80% friendship threshold  | Implemented  |
| `SupportEffects`      | `effects[]`                                      | Bonus percentages display | Planned      |

### 2.6 Skill Components

| Component        | Props                            | Game Pattern         | Status      |
| ---------------- | -------------------------------- | -------------------- | ----------- |
| `SkillCard`      | `skill`, `hintLevel`, `acquired` | Skill shop display   | Implemented |
| `SkillIcon`      | `type`, `rarity`                 | Skill type indicator | Planned     |
| `HintLevelBadge` | `level: 0-5`                     | Discount percentage  | Implemented |
| `SPCounter`      | `current`, `available`           | "450 pts" display    | Implemented |
| `SkillLoadout`   | `skills[]`, `limit`              | Equipped skills grid | Planned     |

---

## 3. Input Components

### 3.1 Form Elements

| Component        | Props                           | Description           |
| ---------------- | ------------------------------- | --------------------- |
| `TextInput`      | `label`, `placeholder`, `error` | Standard text field   |
| `SelectDropdown` | `options[]`, `multiple`         | Selection dropdown    |
| `Autocomplete`   | `items[]`, `filter`             | Search-enabled select |
| `RangeSlider`    | `min`, `max`, `step`            | Numeric range input   |
| `Toggle`         | `checked`, `label`              | On/off switch         |
| `Checkbox`       | `checked`, `label`              | Multi-select option   |
| `RadioGroup`     | `options[]`, `selected`         | Single-select group   |

### 3.2 Interactive Elements

| Component      | Props                     | Game Pattern           |
| -------------- | ------------------------- | ---------------------- |
| `FilterPanel`  | `filters[]`, `selected`   | Toggle filter buttons  |
| `SortDropdown` | `options[]`, `current`    | Sort criteria selector |
| `SearchBar`    | `placeholder`, `onSearch` | Top bar search         |
| `TabBar`       | `tabs[]`, `active`        | Tab navigation         |
| `Stepper`      | `steps[]`, `current`      | Wizard progress        |

---

## 4. Feedback Components

### 4.1 Notifications

| Component           | Props                            | Description           |
| ------------------- | -------------------------------- | --------------------- |
| `Toast`             | `type`, `message`, `duration`    | Floating notification |
| `AlertBanner`       | `type`, `message`, `dismissible` | Full-width alert      |
| `BadgeNotification` | `count`, `isNew`                 | Red circular badge    |
| `InlineMessage`     | `type`, `text`                   | Form feedback         |

### 4.2 Modals & Overlays

| Component       | Props                              | Description           |
| --------------- | ---------------------------------- | --------------------- |
| `Modal`         | `title`, `content`, `actions`      | Centered dialog       |
| `ConfirmDialog` | `message`, `onConfirm`, `onCancel` | Action confirmation   |
| `SlidePanel`    | `position`, `content`              | Side drawer           |
| `Tooltip`       | `content`, `position`              | Hover information     |
| `Popover`       | `trigger`, `content`               | Click-triggered popup |

### 4.3 Loading States

| Component           | Props               | Description           |
| ------------------- | ------------------- | --------------------- |
| `Spinner`           | `size`, `color`     | Circular loading      |
| `SkeletonCard`      | `lines`, `avatar`   | Content placeholder   |
| `ProgressIndicator` | `progress`, `label` | Long operation status |
| `LoadingOverlay`    | `message`           | Full-screen block     |

---

## 5. Navigation Components

### 5.1 Primary Navigation

| Component      | Props                             | Game Pattern            |
| -------------- | --------------------------------- | ----------------------- |
| `NavItem`      | `icon`, `label`, `route`, `badge` | Nav with notifications  |
| `NavGroup`     | `title`, `items[]`                | Grouped nav items       |
| `QuickActions` | `actions[]`                       | Floating action buttons |
| `BackButton`   | `fallbackRoute`                   | Return navigation       |

### 5.2 Secondary Navigation

| Component       | Props                         | Description     |
| --------------- | ----------------------------- | --------------- |
| `Tabs`          | `items[]`, `active`           | Horizontal tabs |
| `VerticalTabs`  | `items[]`, `active`           | Sidebar tabs    |
| `Pagination`    | `current`, `total`, `perPage` | Page navigation |
| `StepIndicator` | `steps[]`, `current`          | Progress steps  |

---

## 6. Dialogue & Event Components

Based on in-game dialogue observations:

| Component          | Props                         | Game Pattern           |
| ------------------ | ----------------------------- | ---------------------- |
| `DialogueBox`      | `speaker`, `text`, `portrait` | Speech bubble styling  |
| `SpeakerTab`       | `name`, `color`               | Colored name indicator |
| `EventBanner`      | `title`, `icon`, `type`       | Trainee Event bar      |
| `DialogueControls` | `skipEnabled`, `quickEnabled` | Skip Off, Quick, Log   |
| `ConversationLog`  | `messages[]`                  | Dialogue history       |

---

## 7. Analytics & Charts

| Component    | Props                      | Description           |
| ------------ | -------------------------- | --------------------- |
| `RadarChart` | `labels[]`, `data[]`       | Pentagon stat display |
| `LineChart`  | `data[]`, `xAxis`, `yAxis` | Trend visualization   |
| `BarChart`   | `data[]`, `horizontal`     | Comparison chart      |
| `PieChart`   | `segments[]`, `showLegend` | Distribution display  |
| `Sparkline`  | `data[]`, `color`          | Inline trend          |

---

## 8. Composite Components

### 8.1 Card Layouts

| Component                | Description            | Parts                          |
| ------------------------ | ---------------------- | ------------------------------ |
| `DashboardWidget`        | Dashboard info card    | Header + Content + Actions     |
| `CharacterDetailCard`    | Full character display | Portrait + Stats + Actions     |
| `SupportDeckBuilder`     | 6-slot deck grid       | DeckSlot × 6 + Actions         |
| `TrainingPredictionCard` | Training option        | Stats + Gains + Risk           |
| `RaceReadinessCard`      | Race preparation       | Readiness + Win Prob + Actions |

### 8.2 List Components

| Component          | Description         | Parts                         |
| ------------------ | ------------------- | ----------------------------- |
| `CharacterList`    | Character grid/list | CharacterCard × N             |
| `SupportCardGrid`  | Card collection     | SupportCard × N + Filters     |
| `SkillShopList`    | Available skills    | SkillCard × N + Categories    |
| `RaceCalendar`     | Race schedule       | RaceCard × N grouped by month |
| `ActivityTimeline` | History display     | Timeline items + Pagination   |

---

## 9. Responsive Patterns

### 9.1 Breakpoint Behaviors

| Component      | Mobile (<640px)     | Tablet (640-1024px) | Desktop (≥1024px) |
| -------------- | ------------------- | ------------------- | ----------------- |
| Navigation     | Bottom nav bar      | Collapsible sidebar | Fixed sidebar     |
| Stats Panel    | Collapsed accordion | 2-column grid       | Horizontal bars   |
| Support Deck   | Horizontal scroll   | 2×3 grid            | 3×2 grid          |
| Character Grid | 2 columns           | 3 columns           | 4-5 columns       |
| Skill Shop     | Simple list         | Card grid           | Detailed cards    |

### 9.2 Progressive Disclosure

| Screen Size | Show Initially             | Available on Demand            |
| ----------- | -------------------------- | ------------------------------ |
| Mobile      | Essential stats, next race | Full stats, history, analytics |
| Tablet      | Primary panels             | Secondary panels in tabs       |
| Desktop     | All panels                 | Expanded details in modals     |

---

## 10. Accessibility Specifications

### 10.1 ARIA Requirements

| Component Type | Required ARIA                         | Notes                         |
| -------------- | ------------------------------------- | ----------------------------- |
| Progress bars  | `role="progressbar"`, `aria-valuenow` | Include text alternative      |
| Stat bars      | `role="meter"`, `aria-valuetext`      | Read as "Speed: A grade, 980" |
| Modals         | `role="dialog"`, `aria-modal`         | Focus trap required           |
| Tabs           | `role="tablist"`, `role="tab"`        | Arrow key navigation          |
| Dropdowns      | `role="listbox"`, `role="option"`     | Type-ahead support            |

### 10.2 Focus Management

| Scenario      | Focus Behavior                  |
| ------------- | ------------------------------- |
| Modal opens   | Focus first interactive element |
| Modal closes  | Return to trigger element       |
| Tab change    | Focus new tab content           |
| Toast appears | Announce via `aria-live`        |
| Navigation    | Skip link to main content       |

### 10.3 Color Independence

All components that use color coding (stats, conditions, grades) must also include:

- Text labels
- Icons or shapes
- High contrast alternatives

---

## 11. Component Implementation Priority

### Phase 1 (Core - P0)

| Component       | Wireframe | Status   |
| --------------- | --------- | -------- |
| StatBar         | WF-003    | Required |
| CharacterCard   | WF-001    | Required |
| ConditionBadge  | WF-003    | Required |
| EnergyGauge     | WF-003    | Required |
| TurnCounter     | WF-003    | Required |
| SupportCardMini | WF-011    | Required |

### Phase 2 (Training & Race - P0)

| Component              | Wireframe | Status   |
| ---------------------- | --------- | -------- |
| TrainingPredictionCard | WF-004    | Required |
| RaceReadinessCard      | WF-007    | Required |
| RaceGradeBadge         | WF-006    | Required |
| GradeBadge             | WF-003    | Required |

### Phase 3 (Skills & Deck - P0)

| Component      | Wireframe | Status   |
| -------------- | --------- | -------- |
| SkillCard      | WF-008    | Required |
| DeckBuilder    | WF-011    | Required |
| HintLevelBadge | WF-008    | Required |
| SPCounter      | WF-008    | Required |

### Phase 4 (Polish - P1)

| Component        | Wireframe | Status       |
| ---------------- | --------- | ------------ |
| DialogueBox      | (New)     | Game-aligned |
| EventBanner      | (New)     | Game-aligned |
| RadarChart       | WF-003    | Required     |
| ActivityTimeline | WF-003    | Required     |

---

## 12. Testing Requirements

### 12.1 Unit Tests

Each component requires:

- Props validation tests
- Render tests for all states
- Accessibility tests (axe-core)
- Snapshot tests for visual regression

### 12.2 Integration Tests

- Component composition tests
- Livewire interaction tests
- Real-time update tests
- Responsive behavior tests

---

## Document Control

**Version History**:

| Version | Date       | Changes                                                     |
| ------- | ---------- | ----------------------------------------------------------- |
| 1.0.0   | 2026-01-28 | Initial component inventory based on 83-screenshot analysis |

**Related Documents**:

- [game-alignment-analysis.md](game-alignment-analysis.md)
- [000_WIREFRAMES_INDEX.md](../01-wireframes/000_WIREFRAMES_INDEX.md)
- [game-ui-alignment-strategy.md](game-ui-alignment-strategy.md)
