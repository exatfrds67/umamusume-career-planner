# Design System Audit Report

**Date**: April 21, 2026  
**Design Source**: `/design/index.html` (React prototype)  
**Target Implementation**: Livewire 4 + Blade + Tailwind CSS v4  
**Status**: **CRITICAL GAPS IDENTIFIED** — Most major screens missing  

---

## Executive Summary

**Scope**: 14 design screens defined in prototype  
**Implemented**: 10 Livewire components (partially)  
**Gap Assessment**: 60% of core screens lack complete Livewire implementation  

| Status | Count | Components |
|--------|-------|------------|
| ✅ Partial Implementation | 2 | Dashboard (TrainingSuggestionPanel), Advisory Panel |
| 🟡 Minimal/Stub | 3 | NotificationDropdown, Settings (AccessibilitySettings, NotificationSettings), Simulation (SimulationWizard partial) |
| ❌ Not Implemented | 7+ | Characters, Character Wizard, Training, Races, Skills, Support Cards, Achievements, Snapshots, OCR Upload, Data Management |
| 📋 Admin/Analytics | 2 | APM Dashboard, Pattern Analytics Dashboard |

---

## Design System Reference

### Design Tokens (from `data.jsx`)

**Typography**:
- Font: Nunito (400–900 weights) from Google Fonts
- Headings: 900 weight, primary color #1E1033
- Body: 400–700 weights

**Color Palette**:

| Component | Color | Function |
|-----------|-------|----------|
| Primary CTA buttons | linear-gradient(135°, #E879A0, #7C3AED) | Primary actions, shadow: 0 4px 12px rgba(232,121,160,.35) |
| Speed stat | #E879A0 | Pink stat bar |
| Stamina stat | #10B981 | Green stat bar |
| Power stat | #F59E0B | Gold stat bar |
| Guts stat | #EF4444 | Red stat bar |
| Wit stat | #3B82F6 | Blue stat bar |
| Grade S | #F59E0B | Stat grades |
| Grade A | #E879A0 | Stat grades |
| Grade B | #7C3AED | Stat grades |
| Grade C | #3B82F6 | Stat grades |
| Grades D–F | Greyscale | Low grades |
| Sidebar | linear-gradient(top: #150D35, bottom: #0A0620) | Sticky sidebar dark theme |
| Background | #F9F5FF | Lavender-white page bg |
| Cards | #FFFFFF | White, border #EDE9FE, shadow rgba(124,58,237,0.07) |

**Spacing & Radius**:
- Cards: `border-radius: 16px`
- Buttons: `border-radius: 10px`
- Badges: `border-radius: 6px`
- Stat bars (pill): `border-radius: 99px`
- Sidebar: `width: 240px` (expanded) / `64px` (collapsed)
- Header: `height: 64px`
- Main content: `padding: 24px`

**Layout**:
- Collapsible sidebar: 240px expanded, 64px collapsed, sticky, dark gradient
- Sticky header: 64px, frosted glass (rgba(255,255,255,0.95) + blur(12px))
- Main content: Scrollable area with 24px padding
- Stat soft cap: 1200 (diminishing returns above 1000)
- Mood modifiers: Great +4%, Good +2%, Normal 0%, Bad -2%, Awful -4%
- Facility multipliers: Lv1=1.0× to Lv5=2.0×
- Friendship Training: ≥80% bond = 1.10× multiplier; ≥3 cards = 1.35×

**Shared UI Primitives**:

```javascript
<GradeBadge grade='S|A|B|C|D|E|F' size='sm|lg' />
<StatBar stat='speed|stamina|power|guts|wit' value={n} />
<MoodChip mood='Great|Good|Normal|Bad' />
<RarityBadge rarity='SSR|SR|R' />
<Card>{children}</Card>
<Btn variant='primary|secondary|ghost|danger|gold' size='sm|md|lg'>{children}</Btn>
<Icon name='...' size={n} color='...' />
```

### Enums & Constants

```javascript
StorageMode: 'local' | 'account'
CareerPhase: 'junior' | 'classic' | 'senior' | 'ura_finale'
AffinityGrade: 'S' | 'A' | 'B' | 'C' | 'D' | 'E' | 'F'
Mood: 'Great' | 'Good' | 'Normal' | 'Bad' | 'Awful'
Condition: 'Good' | 'Normal' | 'Tired'
RunningStyle: 'Nige' | 'Senkou' | 'Sashi' | 'Oikomi'
RaceDistance: 'Sprint' | 'Mile' | 'Medium' | 'Long' | 'Dirt'
RaceGrade: 'G1' | 'G2' | 'G3'
SkillRarity: 'unique' | 'rare' | 'common'
SupportCardTier: 'S+' | 'S' | 'A+' | 'A' | 'B'
RarityBadge: 'SSR' | 'SR' | 'R'
GoalStatus: 'on_track' | 'at_risk' | 'completed'
```

---

## Screen-by-Screen Audit

### 1. Dashboard (Partial ✅)

**Design Spec** (from `Dashboard.jsx`):
- Dark gradient welcome banner with active character, rarity, scenario, mood chip, career progress bar
- 2-col stats snapshot grid + active goals + condition (energy/mood)
- 3-col grid bottom: training suggestions (3 cards), upcoming races (3 cards), AI advisor card
- All sections link to detail views
- Stats soft-cap explanation hint card

**Current Implementation**:
- ✅ `Dashboard/TrainingSuggestionPanel.php` exists
- ❌ Main dashboard page NOT FOUND
- ❌ AdvisoryPanel exists but is separate floating component, not integrated on dashboard
- ❌ No welcome banner, no stats snapshot, no goals grid

**Gaps**:
- [ ] Dashboard page/controller missing
- [ ] Blade template for dashboard layout
- [ ] Welcome banner component (with character context, progress bar)
- [ ] Stats snapshot card (2-col grid with StatBar components)
- [ ] Active goals card with progress bars and status badges
- [ ] Condition card (energy + mood display)
- [ ] Upcoming races preview (3-card carousel or grid)
- [ ] Integration of AdvisoryPanel with styling
- [ ] Soft-cap hint alert box

**Priority**: 🔴 **CRITICAL** — Core landing page missing

---

### 2. Characters (❌ Not Implemented)

**Design Spec** (from `Characters.jsx`):
- Grid of character cards (auto-fill minmax 300px)
- Card header: dark gradient, rarity badge, scenario tag, mood chip
- Card body: stats mini-grid (5 stats with icons, grades), total stats, mood, turn progress
- "+ New Character" CTA card (dashed border)
- Detail view with 6 tabs: Stats, Aptitudes, Factors, Goals, History, Snapshots
  - Stats tab: full StatBar components with soft-cap explanation
  - Aptitudes tab: grade grid for all racing styles/distances
  - Factors tab: inherited stat bonuses list
  - Goals tab: goal list with progress bars and edit controls
  - History tab: recent turn actions (turn, action, gains, SP earned)
  - Snapshots tab: named checkpoints with restore/compare buttons

**Current Implementation**:
- ❌ NO Livewire component exists

**Gaps**:
- [ ] `App\Livewire\Characters\CharacterIndex` (list view)
- [ ] `App\Livewire\Characters\CharacterDetail` (detail view with tabs)
- [ ] `resources/views/livewire/characters/index.blade.php`
- [ ] `resources/views/livewire/characters/detail.blade.php`
- [ ] Character card component (grid view)
- [ ] Aptitude grid component
- [ ] Goals list component
- [ ] History/recent turns component
- [ ] Snapshots tab with checkpoint management

**Priority**: 🔴 **CRITICAL** — Core data hub missing

---

### 3. Character Wizard (❌ Not Implemented)

**Design Spec** (from `CharacterWizard.jsx`):
- Modal with 4 steps:
  1. **Trainee Selector**: Filterable grid of character artworks with name/rarity, searchable
  2. **Parent Inheritance**: Select father/mother, show factor bonuses
  3. **Support Deck Builder**: 6-slot drag-drop UI with bond/tier indicators
  4. **Review**: Confirm all details, create button
- Progress bar showing step 1/2/3/4
- Modal header with gradient, close button
- Validation at each step

**Current Implementation**:
- ❌ NO Livewire component exists (Simulation/SimulationWizard is for race simulation, not character creation)

**Gaps**:
- [ ] `App\Livewire\Characters\CharacterWizard`
- [ ] 4-step wizard logic and step transitions
- [ ] Trainee filterable grid component
- [ ] Parent selector with factor preview
- [ ] Support deck builder (6-slot drag-drop UI)
- [ ] Review/confirmation step
- [ ] Backend creation logic

**Priority**: 🔴 **CRITICAL** — Character creation flow blocked

---

### 4. Training (❌ Not Implemented)

**Design Spec** (from `Training.jsx`):
- Header: character name, turn, stage, "Cached/Fresh" status, Facility Levels toggle
- 5 training cards (Speed, Stamina, Power, Guts, Wit):
  - Icon, label, bg color, stats gains, risk%, fatigue, SP earned
  - "BEST" badge if recommended
  - Facility level multiplier display (1×–2×)
  - Bonus notes (bond-based bonuses)
  - Click to select
- **Friendship Training banner** (if ≥80% bond): Shows ≥3 high-bond cards highlight
- **Sticky detail panel** (right side):
  - Selected training info
  - Adjusted gains calculation:
    - Base gain × facility multiplier × (1 + mood modifier) × friendship multiplier
    - Soft-cap adjustment (final formula from SEQ-002)
  - Energy/fatigue preview
  - "Confirm" button
- **Turn Result Modal**:
  - Success/failure outcome (#E879A0 gradient if success, red if failure)
  - Stat deltas table (before → after with soft-cap warning)
  - SP earned, energy after, mood after
  - Skill hint acquired notification (if applicable)
  - Energy warning (<30% fatigue alert)
  - "Continue to Turn X" button

**Current Implementation**:
- ❌ NO Livewire component exists

**Gaps**:
- [ ] `App\Livewire\Training\TrainingInterface`
- [ ] Training options card grid
- [ ] Facility levels popup/toggle
- [ ] Friendship Training banner
- [ ] Detail panel with gain calculation
- [ ] Turn result modal with stat deltas
- [ ] Soft-cap calculation logic (integrate with service)
- [ ] Blade templates for all sections

**Priority**: 🔴 **CRITICAL** — Core gameplay loop missing

---

### 5. Races (❌ Not Implemented)

**Design Spec** (from `Races.jsx`):
- Tabs: "Upcoming", "History" (switchable)
- **Upcoming Races list**:
  - Each race: name, grade (G1/G2/G3 badge), distance, surface, weather, turn
  - Readiness bar (0–100%) with color coding (green ≥80%, amber 60–79%, red <60%)
  - Readiness label (Excellent/Good/Fair/Poor)
  - Win probability %
  - [Enter Race] button
- **Race Entry Modal** (2-step):
  - Step 1: Placement selector (1–8 grid, 1st/2nd/3rd/th suffixes)
    - Reward preview: fans (by placement + grade), skill points (top 3 only), stat bonuses
  - Step 2: Result screen
    - Placement emoji (🏆 for 1st, 🎖️ for top 3, 😔 otherwise)
    - Stat bonus applied (if top 3)
    - Objective check (if race is marked required)
    - AI recovery tip
    - "Record Result" button
- **Race History tab**: Past race results table

**Current Implementation**:
- ❌ NO Livewire component exists

**Gaps**:
- [ ] `App\Livewire\Races\RaceIndex`
- [ ] `App\Livewire\Races\RaceEntryModal`
- [ ] Upcoming races list component
- [ ] Readiness bar component
- [ ] Race history table
- [ ] 2-step modal with placement selector and result screen
- [ ] Reward calculation logic
- [ ] Backend race entry recording

**Priority**: 🔴 **CRITICAL** — Race system core screens missing

---

### 6. Skills (❌ Not Implemented)

**Design Spec** (from `Skills.jsx`):
- **Left panel**: Skill catalog (searchable list)
  - Skill type badges (Unique, Distance, Style, Recovery, etc.)
  - Rarity badge (SSR/SR/R)
  - Base cost (in SP)
  - Owned indicator (✓ checkmark or × if not owned)
- **Center panel**: Skill detail view
  - Full description, type, rarity, cost, hint level (Lv0 = 0% discount, Lv5 = 40% discount)
  - **SP budget bar** at top: visual representation of total SP, current allocation, remaining
  - Cost broken down: base cost with strikethrough, discounted cost below
  - "Learn" or "Upgrade" button (if already owned)
- **Evolution modal** (if applicable):
  - Current skill → Gold skill upgrade path
  - New cost and description
  - "Upgrade" button
- **Learnable skills**: Filtered by character aptitudes and current funds

**Current Implementation**:
- ❌ NO Livewire component exists

**Gaps**:
- [ ] `App\Livewire\Skills\SkillCatalog`
- [ ] Searchable skill list component
- [ ] Skill detail panel
- [ ] SP budget bar component
- [ ] Cost calculation with hint level discounts
- [ ] Evolution modal
- [ ] Backend skill learning/upgrading

**Priority**: 🟡 **HIGH** — Core progression system missing

---

### 7. Support Cards (❌ Not Implemented)

**Design Spec** (from `SupportCards.jsx`):
- **Deck grid**: 6 slots, each showing:
  - Card artwork (if added)
  - Card name, type (icon), rarity (SSR/SR/R badge)
  - Tier badge (S+/S/A+/A/B)
  - Bond level (0–100% bar) with percentage
  - Synergy score (if multiple cards with same type/attribute)
  - Stat bonuses from card
  - Effect description
- **Add/Remove buttons** per slot
- **Card selector modal**: Paginated grid of available cards, filtered by type
- **Synergy analysis** (lower display):
  - Type distribution pie/bar chart
  - Synergy score explanation
  - "Optimize" button suggestion

**Current Implementation**:
- ✅ `SynergyBuildPlanner.php` exists (partially)
- ❌ Main deck builder UI NOT implemented

**Gaps**:
- [ ] Full Livewire component for deck view
- [ ] 6-slot grid layout
- [ ] Card selector modal
- [ ] Synergy analysis display
- [ ] Bond level bars
- [ ] Blade templates for all sections

**Priority**: 🟡 **HIGH** — Character optimization blocked

---

### 8. AI Advisor (❌ Integrated but Minimal)

**Design Spec** (from `AIAdvisor.jsx`):
- **Streaming chat interface**: Token-by-token animation as AI responds
- **Conversation history**: List of past messages
- **Model toggle**: Bedrock vs Ollama switcher (with indicator)
- **Character context sidebar**: Show active character stats, mood, goals
- **Suggested prompts**: Pre-filled examples like:
  - "Recommend training for speed"
  - "Analyze race readiness"
  - "SP allocation strategy"
- **Streaming response display**: Animated typing effect

**Current Implementation**:
- ✅ `AdvisoryPanel.php` exists (handles recommendations, not free-form chat)
- ❌ AI Advisor streaming chat interface NOT implemented

**Gaps**:
- [ ] `App\Livewire\AIAdvisor\ChatInterface`
- [ ] Streaming chat response component
- [ ] Conversation history panel
- [ ] Model toggle switch
- [ ] Character context sidebar
- [ ] Suggested prompts card grid
- [ ] Integration with backend AI endpoints

**Priority**: 🟡 **HIGH** — Advanced feature, can be deferred

---

### 9. Achievements (❌ Not Implemented)

**Design Spec** (from `Achievements.jsx`):
- **5 achievement categories**:
  1. Training Milestones (e.g., "Reach 800 Speed", "Train 50 times")
  2. Racing (e.g., "Win G1 race", "Top 3 finishes")
  3. Skills (e.g., "Learn 10 unique skills", "Max SP budget")
  4. Support Cards (e.g., "Reach 100% bond", "6-card synergy")
  5. Misc (e.g., "Complete storyline", "Export/import data")
- **Fan progression** (visual strip):
  - Debut → Bronze → Silver → Gold → Platinum → Star → Top Star → Legend
  - Current progress bar within tier
  - Fan count display
- **Achievement card**:
  - Icon, name, description
  - Lock status (🔒 if not unlocked)
  - Progress bar (if progress tracked)
  - Unlock date (if completed)
- **Toast notification** on unlock with animation

**Current Implementation**:
- ❌ NO Livewire component exists

**Gaps**:
- [ ] `App\Livewire\Achievements\AchievementCenter`
- [ ] Category tabs component
- [ ] Achievement card grid
- [ ] Fan progression strip
- [ ] Backend achievement tracking system
- [ ] Toast notification on unlock

**Priority**: 🟠 **MEDIUM** — Pure reward system, can phase in

---

### 10. Snapshots / Checkpoints (❌ Not Implemented)

**Design Spec** (from `Characters.jsx` > Snapshots tab):
- **Snapshot list**: Recent checkpoints with:
  - Name (editable)
  - Turn number
  - Total stats sum
  - Created date
  - [Restore], [Delete], [Compare] buttons
- **Create snapshot modal**:
  - Named checkpoint (e.g., "Before Race 50", "Max Speed Build")
  - Save button
- **Compare modal** (side-by-side):
  - Snapshot 1 stats vs current stats
  - Stat deltas (green if gain, red if loss)
  - Timestamp diff

**Current Implementation**:
- ❌ NO Livewire component exists

**Gaps**:
- [ ] `App\Livewire\Snapshots\SnapshotManager`
- [ ] Snapshot list component
- [ ] Create/edit modal
- [ ] Compare modal with stat deltas
- [ ] Backend snapshot persistence

**Priority**: 🟠 **MEDIUM** — QoL feature

---

### 11. OCR Upload (❌ Not Implemented)

**Design Spec** (from `OtherScreens.jsx`):
- **Drag-and-drop zone**:
  - Large drop target with 📸 icon
  - "Drop your screenshot here" text
  - File format help text (PNG, JPG, WEBP)
  - [Select File] button
  - Dashed border, lavender background, hover effects
- **Processing state** (animated):
  - ⚙️ icon, loading bar, "Processing {filename}…" text
  - Estimated ETAsetter
- **Results card**:
  - ✅ success icon
  - Extracted fields table: Field name | Value | Confidence %
  - Example fields: Speed, Stamina, Power, Guts, Wit, Energy, Turn
  - [Apply to Character] button
  - [New Upload] button

**Current Implementation**:
- ❌ NO Livewire component exists

**Gaps**:
- [ ] `App\Livewire\OCR\ScreenshotUploader`
- [ ] Drag-drop zone component
- [ ] Processing state animation
- [ ] Extracted fields display
- [ ] Backend OCR integration
- [ ] Field validation and application logic

**Priority**: 🟠 **MEDIUM** — Nice-to-have convenience feature

---

### 12. Data Management (❌ Not Implemented)

**Design Spec** (from `OtherScreens.jsx`):
- **3-step Local → Account migration modal**:
  1. **Validate**: Show local data summary, confirm format
  2. **Preview**: List of plans to migrate, duplicates detection
  3. **Migrate**: Animated progress bar, step-by-step import
  4. **Done**: Success summary, view in account button
- **Backup/Restore section**: Download JSON, import JSON
- **Export/Import buttons** per character

**Current Implementation**:
- ❌ NO Livewire component exists

**Gaps**:
- [ ] `App\Livewire\DataManagement\MigrationWizard`
- [ ] 3-step modal with progress
- [ ] Validation step display
- [ ] Preview step with duplicate detection
- [ ] Progress animation during import
- [ ] Backend migration logic
- [ ] Export/import file handling

**Priority**: 🟠 **MEDIUM** — Essential for Local users moving to Account mode

---

### 13. Settings (🟡 Minimal Implementation)

**Design Spec** (from `OtherScreens.jsx`):
- **Storage Mode**: Toggle "Local" vs "Account" (read-only after creation)
- **AI Model**: Bedrock vs Ollama selector with indicator
- **Theme**: Light/Dark/Auto toggle
- **Notifications**: Enable/disable toast alerts
- **Compact Mode**: Reduce spacing and font sizes
- **Accessibility**: High contrast mode, dyslexia-friendly font, keyboard shortcuts guide

**Current Implementation**:
- ✅ `Settings/AccessibilitySettings.php` (partial)
- ✅ `Settings/NotificationSettings.php` (partial)
- ❌ Full settings page structure missing

**Gaps**:
- [ ] Main settings page layout
- [ ] Storage mode display
- [ ] AI model selector
- [ ] Theme toggle
- [ ] Compact mode toggle
- [ ] Section-based organization (Appearance, Behavior, Privacy, etc.)

**Priority**: 🟠 **MEDIUM** — Can be completed incrementally

---

### 14. Footer / Other Screens (❌ Not Implemented)

**Design Spec**:
- **MCP Monitor**: Real-time MCP tool status
- **Performance Dashboard**: Query analytics, response times, cache hit rates
- **Admin Dashboard**: User stats, system health, debug info

**Current Implementation**:
- ✅ `Admin/ApmDashboard.php` (exists)
- ✅ `Analytics/PatternDashboard.php` (exists)

**Gaps**:
- [ ] Integrate APM/Analytics dashboards into main layout
- [ ] Admin-only permission guards

**Priority**: 🟠 **MEDIUM** — Admin tools, separate workflow

---

## Existing Implementation Assessment

### ✅ What's Already Built

| Component | File | Status | Notes |
|-----------|------|--------|-------|
| Advisory Panel | `AdvisoryPanel.php` | ⚠️ Partial | Displays recommendations; not integrated on dashboard. Styling mostly complete. |
| Notification Dropdown | `NotificationDropdown.php` | ⚠️ Minimal | Component exists but may need UI refinement per design. |
| Synergy Build Planner | `SynergyBuildPlanner.php` | ⚠️ Partial | Exists; needs full UI and deck visualization per design spec. |
| Training Suggestion Panel | `Dashboard/TrainingSuggestionPanel.php` | ⚠️ Partial | Small component; full training UI is separate screen not implemented. |
| Accessibility Settings | `Settings/AccessibilitySettings.php` | ⚠️ Minimal | Partial implementation; needs full settings page. |
| Notification Settings | `Settings/NotificationSettings.php` | ⚠️ Minimal | Partial implementation; needs full settings page. |
| Simulation Wizard | `Simulation/SimulationWizard.php` | ⚠️ Minimal | For race simulation; not same as character creation wizard. |
| APM Dashboard | `Admin/ApmDashboard.php` | 📋 Admin | Exists; needs integration. |
| Pattern Analytics | `Analytics/PatternDashboard.php` | 📋 Admin | Exists; needs integration. |

### ❌ Critical Missing Screens

| Screen | Livewire Component | Blade Template | Priority |
|--------|------------------|-----------------|----------|
| Dashboard (main) | ❌ | ❌ | 🔴 CRITICAL |
| Characters (list) | ❌ | ❌ | 🔴 CRITICAL |
| Character Detail (6 tabs) | ❌ | ❌ | 🔴 CRITICAL |
| Character Wizard | ❌ | ❌ | 🔴 CRITICAL |
| Training Interface | ❌ | ❌ | 🔴 CRITICAL |
| Races (with modal) | ❌ | ❌ | 🔴 CRITICAL |
| Skills Catalog | ❌ | ❌ | 🟡 HIGH |
| Support Cards Deck | ❌ | ❌ | 🟡 HIGH |
| AI Advisor (chat) | ❌ | ❌ | 🟡 HIGH |
| Achievements | ❌ | ❌ | 🟠 MEDIUM |
| Snapshots | ❌ | ❌ | 🟠 MEDIUM |
| OCR Upload | ❌ | ❌ | 🟠 MEDIUM |
| Data Migration | ❌ | ❌ | 🟠 MEDIUM |
| Full Settings | ⚠️ Partial | ⚠️ Partial | 🟠 MEDIUM |

---

## Visual Consistency Gaps

### Typography
- [ ] Nunito font weights (400–900) properly configured in Tailwind theme
- [ ] Font size scale matches design (10px, 11px, 12px, 13px, 14px, 15px, 16px, 18px, 20px, 22px, 24px, 32px, 36px)
- [ ] Font weight consistency (300, 400, 600, 700, 800, 900)

### Color Consistency
- [ ] Stat colors correctly applied (Speed #E879A0, Stamina #10B981, etc.)
- [ ] Grade badge colors match (S #F59E0B, A #E879A0, B #7C3AED, C #3B82F6, D–F greys)
- [ ] Card borders #EDE9FE, shadows rgba(124,58,237,0.07)
- [ ] Sidebar gradient #150D35 → #0A0620
- [ ] Primary gradient buttons (135°, #E879A0 → #7C3AED)

### Spacing & Layout
- [ ] Card border-radius consistently 16px
- [ ] Button border-radius 10px
- [ ] Badge border-radius 6px
- [ ] Main content padding 24px
- [ ] Sidebar 240px expanded / 64px collapsed
- [ ] Header 64px height with frosted glass effect

### Interactive States
- [ ] Hover brightness +6% on buttons
- [ ] Active border highlights on tabs/buttons
- [ ] Modal overlays with 75% dark blur backdrop
- [ ] Loading animations ( @keyframes pulse, @keyframes slide)
- [ ] Smooth transitions (0.15s cubic-bezier)

### Component Library Status

| Primitive | Status | Notes |
|-----------|--------|-------|
| `<Card>` | ✅ | Blade component likely exists |
| `<GradeBadge>` | ⚠️ Partial | Grade display exists; may need refinement |
| `<StatBar>` | ⚠️ Partial | Component exists; soft-cap indicator needed |
| `<MoodChip>` | ✅ | Likely component exists |
| `<RarityBadge>` | ⚠️ Partial | Display exists; design refinement needed |
| `<Btn variant>` | ⚠️ Partial | Primary/secondary variants exist; all variants needed |
| `<Icon>` | ✅ | Heroicons integration exists |

---

## Implementation Priority Roadmap

### Phase 1: Core Data Hub (Week 1–2)
1. **Dashboard** (welcome banner + 2-col stats + 3-col cards)
2. **Characters** (list view + detail view with 6 tabs)
3. **Character Wizard** (4-step modal with image selector, inheritance, deck, review)

*Enables*: Character viewing, basic run tracking

### Phase 2: Core Gameplay (Week 3–4)
4. **Training** (5 training cards + detail panel + result modal)
5. **Races** (upcoming list + 2-step entry modal + history)

*Enables*: Core turn-by-turn gameplay loop

### Phase 3: Progression Systems (Week 5–6)
6. **Skills** (catalog + detail + evolution modal)
7. **Support Cards** (deck grid + selector + synergy analysis)

*Enables*: Full character customization

### Phase 4: QoL & Advanced (Week 7–8)
8. **Achievements** (category tabs + fan progression)
9. **Snapshots** (checkpoint management + compare)
10. **OCR Upload** (screenshot extraction)
11. **Data Migration** (Local → Account conversion modal)
12. **Full Settings** (all toggles + theme + AI model)
13. **AI Advisor Chat** (streaming chat interface)

### Phase 5: Admin/Analytics (Week 9+)
14. Integrate APM Dashboard
15. Integrate Pattern Analytics

---

## Compliance Checklist

### WCAG 2.2 AA (Accessibility)
- [ ] Color contrast ratios check (min 4.5:1 for text)
- [ ] Keyboard navigation support (Tab, Enter, Escape, arrow keys)
- [ ] Focus indicators visible on all interactive elements
- [ ] ARIA labels on buttons, modals, sections
- [ ] Form fields properly labeled
- [ ] Skip navigation link (optional but recommended)

### Dual Storage Mode
- [ ] All screens detect `StorageMode` from Auth/config
- [ ] UUID routes for Local mode (`/plans/{uuid}`)
- [ ] Numeric ID routes for Account mode (`/plans/{id}`)
- [ ] LocalStorageService integration where needed
- [ ] Graceful offline degradation for Account mode

### Performance
- [ ] N+1 query prevention (eager loading)
- [ ] Caching for skill/race/character lookups
- [ ] Lazy loading for large grids
- [ ] Pagination on history/achievement lists
- [ ] Image optimization (character art, support cards)

### Testing
- [ ] Unit tests for calculation services (soft-cap, gains, rewards)
- [ ] Feature tests for each Livewire component
- [ ] Browser tests for critical user flows
- [ ] Mobile/responsive testing
- [ ] Dark mode testing

---

## Next Steps

### **Recommended Immediate Actions**:

1. **Create Session Checklist**: `Mark Phase 1 components for development`
2. **Resolve Component Library**: `Audit existing Blade components in resources/views/components/`
3. **Establish CSS Standards**: `Update app.css @theme section with all design tokens`
4. **Backend Readiness**: `Verify Character, Career, Skill, Race, Snapshot models exist + factories/seeds`
5. **Route Structure**: `Define routes for all Phase 1 screens before Livewire coding`

### **Design Source Links**:
- Prototype: `http://localhost:8000/design/index.html`
- Data shapes: `/design/components/data.jsx`
- Icons: `/design/components/icons.jsx`
- Primitives: `/design/components/data.jsx` (`GradeBadge`, `StatBar`, `MoodChip`, etc.)

---

**Report Generated**: April 21, 2026  
**Audit Performed By**: Design System Audit Tool  
**Status**: READY FOR IMPLEMENTATION ROADMAP
