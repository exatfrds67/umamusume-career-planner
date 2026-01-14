# WIREFRAMES & UI SPECIFICATIONS INDEX

**Document Version**: 1.0 | **Date**: January 14, 2026 | **Status**: Comprehensive Framework

## Overview

Wireframes provide detailed UI/UX specifications for all key screens and components. This document includes text-based wireframe representations (ASCII/structured layouts) that can be imported into Figma, Adobe XD, or other design tools.

---

## Wireframe Categories

### 1. Character Management Screens (WF-001 to WF-005)

#### WF-001: Character Creation Wizard - Step 1: Trainee Selection

```
┌────────────────────────────────────────────────────────────┐
│  Umamusume Career Planner                        [?] [≡]  │
├────────────────────────────────────────────────────────────┤
│                                                            │
│  Create New Character - Step 1 of 4                       │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│                                                            │
│  Select Trainee                                            │
│  ┌──────────────────────────────────────────────────┐    │
│  │ 🔍 Search trainees...                              │    │
│  └──────────────────────────────────────────────────┘    │
│                                                            │
│  Rarity Filter: [ ]SSR  [ ]SR  [ ]R                       │
│                                                            │
│  ┌─────────────────┬─────────────────┬─────────────────┐  │
│  │ Mejiro Ardan    │ Kitasan Black   │ Tokai Teio      │  │
│  │ SSR · Speed     │ SSR · Power     │ SSR · Stamina   │  │
│  │ [SELECT]        │ [SELECT]        │ [SELECT]        │  │
│  └─────────────────┴─────────────────┴─────────────────┘  │
│                                                            │
│  ┌─────────────────┬─────────────────┬─────────────────┐  │
│  │ ...more cards...│ ...             │ ...             │  │
│  └─────────────────┴─────────────────┴─────────────────┘  │
│                                                            │
│                          [← BACK]  [NEXT →]              │
│                                                            │
└────────────────────────────────────────────────────────────┘
```

**Components**:
- Search input with autocomplete
- Filter buttons for rarity
- Card grid (3 columns, scrollable)
- Navigation buttons

---

#### WF-002: Character Creation Wizard - Step 2: Parent Selection

```
┌────────────────────────────────────────────────────────────┐
│  Create New Character - Step 2 of 4: Select Parents        │
├────────────────────────────────────────────────────────────┤
│                                                            │
│  Main Parents (Choose 2)                                   │
│  ┌───────────────────────┬───────────────────────┐        │
│  │ Parent 1              │ Parent 2              │        │
│  │ ┌─────────────────┐   │ ┌─────────────────┐   │        │
│  │ │  [Portrait]     │   │ │  [Portrait]     │   │        │
│  │ └─────────────────┘   │ └─────────────────┘   │        │
│  │ Name: _______________│ Name: _______________│        │
│  │ Select: [CHOOSE]     │ Select: [CHOOSE]     │        │
│  │ Affinity: ◎ (100%)   │ Affinity: ◎ (100%)   │        │
│  └───────────────────────┴───────────────────────┘        │
│                                                            │
│  Inheritance Preview                                       │
│  ┌──────────────────────────────────────────────┐         │
│  │ Inherited Stat Factors:                      │         │
│  │ • Speed: ★★★ (+21)                          │         │
│  │ • Stamina: ★★☆ (+12)                        │         │
│  │                                              │         │
│  │ Growth Rates:                                │         │
│  │ • Speed: +20%                                │         │
│  │ • Stamina: +20%                              │         │
│  │ • Power: +10%                                │         │
│  │ • Guts: +20%                                 │         │
│  │ • Wit: +10%                                  │         │
│  └──────────────────────────────────────────────┘         │
│                                                            │
│                          [← BACK]  [NEXT →]              │
│                                                            │
└────────────────────────────────────────────────────────────┘
```

**Components**:
- Two-column parent selector
- Portrait images
- Affinity indicator (◎ symbol)
- Inheritance preview box
- Real-time calculation updates

---

#### WF-003: Character Dashboard - Main View

```
┌────────────────────────────────────────────────────────────┐
│  Mejiro Ardan (Turn 45)                       [Edit] [≡]   │
├────────────────────────────────────────────────────────────┤
│                                                            │
│  Status Bar                                                │
│  Energy: ████████░ 78%  | Mood: ◐ Good  | Race in: 15 d   │
│                                                            │
│  ┌────────────────────────────────────────────────────────┐│
│  │ Stats Overview         │  Goals Progress              ││
│  ├────────────────────────┼──────────────────────────────┤│
│  │ Speed      520 ★★★★★  │  Speed Goal: 800  ███░░ 65%   ││
│  │ Stamina    480 ★★★★░  │  Stamina: 600  ███░░░░ 45%    ││
│  │ Power      440 ★★★░░  │  Race Goal: G1  ◐ Upcoming    ││
│  │ Guts       460 ★★★★░  │                               ││
│  │ Wit        450 ★★★░░  │  [+ Add Goal]                ││
│  │                        │                               ││
│  │ Aptitudes              │  Recent Turns               ││
│  │ • Mile:  A+  ◎        │  ┌─────────────────────────┐  ││
│  │ • Turf:  A           │  │ Turn 44: Speed +48      │  ││
│  │ • Late Surger: S     │  │ Turn 43: Stamina +42    │  ││
│  │                        │  │ Turn 42: Power +35      │  ││
│  └────────────────────────┴──────────────────────────────┘│
│                                                            │
│  Support Deck (6 cards)                                    │
│  ┌─────────────────┬─────────────────┬─────────────────┐  │
│  │ Mejiro Dober    │ Tokai Teio      │ Kitasan Black   │  │
│  │ SSR · Power     │ SSR · Speed     │ SSR · Stamina   │  │
│  │ Bond: 90%       │ Bond: 75%       │ Bond: 85%       │  │
│  │ [⚙️ EDIT]        │ [⚙️ EDIT]        │ [⚙️ EDIT]        │  │
│  └─────────────────┴─────────────────┴─────────────────┘  │
│                                                            │
│  [NEXT RACE INFO] [TRAINING OPTIONS] [VIEW HISTORY]        │
│                                                            │
└────────────────────────────────────────────────────────────┘
```

**Components**:
- Status bar (energy, mood, countdown)
- Two-column layout (stats + goals)
- Stat bars with grade indicators
- Goal progress bars
- Support deck preview (6 cards)
- Action buttons

---

#### WF-004: Training Selection Screen

```
┌────────────────────────────────────────────────────────────┐
│  Select Training (Turn 46)                            [≡]   │
├────────────────────────────────────────────────────────────┤
│                                                            │
│  🚨 Status: Good | Energy: 78% | Mood: Good (+0%)          │
│                                                            │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ RECOMMENDED: Speed Training                  Rank: 1     ││
│  │ Alignment: High | Score: 92.5/100           [AI] (new)  ││
│  │                                                         ││
│  │ Predicted Gains:                                        ││
│  │ Speed: +45  Stamina: +5  Power: +3  Guts: +2  Wit: +1  ││
│  │ Efficiency: ★★★★★ Excellent                           ││
│  │                                                         ││
│  │ Skill Hints:                                            ││
│  │ • Lane Guidance (Guaranteed ✓ - Mejiro Dober red !)    ││
│  │ • Going Strong (25% chance)                            ││
│  │                                                         ││
│  │ Support Cards Active:                                   ││
│  │ • Mejiro Dober (Power Spec, +12) ← Red Exclamation      ││
│  │ • Tokai Teio (Speed Spec, +8)                          ││
│  │                                                         ││
│  │ Mood Prediction: Good → Normal (-1) ⚠️                 ││
│  │                                                         ││
│  │                          [SELECT THIS] [SHOW MORE]      ││
│  └─────────────────────────────────────────────────────────┘│
│                                                            │
│  Other Training Options:                                   │
│  ┌──────────────────┬──────────────────┬──────────────────┐ │
│  │ 2. Stamina       │ 3. Power         │ 4. Guts          │ │
│  │ Rank: 2 (88.3)   │ Rank: 3 (82.1)   │ Rank: 4 (71.5)   │ │
│  │ Gains: +42       │ Gains: +38       │ Gains: +35       │ │
│  │ [SELECT]         │ [SELECT]         │ [SELECT]         │ │
│  └──────────────────┴──────────────────┴──────────────────┘ │
│                                                            │
│  [BACK]  [AI ADVICE]  [VIEW HISTORY]                       │
│                                                            │
└────────────────────────────────────────────────────────────┘
```

**Components**:
- Recommended training (prominent top)
- AI badge indicator
- Predicted stat gains visualization
- Skill hint display with guarantees
- Support card bonuses shown
- Mood prediction warning
- Expandable alternatives

---

#### WF-005: Race Preparation Screen

```
┌────────────────────────────────────────────────────────────┐
│  Upcoming Race: Kanto Okami Cup                       [≡]   │
├────────────────────────────────────────────────────────────┤
│                                                            │
│  Grade: G1 | Track: Tokyo | Distance: Medium (2400m)      │
│  Surface: Turf | Weather: Sunny | Turn: 85 | Days: 8      │
│                                                            │
│  Stat Requirements                 Running Style           │
│  ┌─────────────────────────┐       ┌──────────────────────┐│
│  │ Speed      520 ○ ADEQUATE       │ RECOMMENDED:        ││
│  │ Stamina    480 × INADEQUATE     │ Late Surger         ││
│  │ Power      440 ⦾ BORDERLINE    │ Score: 85.3/100     ││
│  │ Guts       460 ○ ADEQUATE       │                      ││
│  │ Wit        450 ⦾ BORDERLINE    │ Reasoning:          ││
│  │                                 │ • High Power aptitude││
│  │ Readiness: ⦾ BORDERLINE        │ • Strong in Guts    ││
│  └─────────────────────────┘       │ • Medium distance   ││
│                                    │   suits late push   ││
│  Stat Gaps to Address:             │                      ││
│  • Stamina: -120 to target        │ Alternatives:       ││
│  • Wit: -80 to optimal            │ • End Closer (76.5) ││
│                                    │ • Front Runner (62.1)│
│  [TRAINING FOCUS] [AI STRATEGY]    └──────────────────────┘│
│                                                            │
│  Skill Recommendations                                     │
│  ┌────────────────────────────────────────────────────────┐│
│  │ Priority: High                                         ││
│  │ Essential Skills:                                      ││
│  │ ✓ Predator's Instinct (Late Surger enhancement) - OWN ││
│  │ ○ Turf Runner (Surface specific) - NEED 120 SP        ││
│  │                                                        ││
│  │ Priority: Medium                                       ││
│  │ Recommended:                                           ││
│  │ ○ Cool Breeze (Weather bonus) - HAVE HINT (-20%)      ││
│  │ □ Withstand - Consider for durability                 ││
│  └────────────────────────────────────────────────────────┘│
│                                                            │
│  Performance Forecast                                      │
│  Placement Probability:                                    │
│  1st Place: 35% | 2nd: 40% | 3rd: 20% | 4th+: 5%         │
│  Expected: 2nd Place                                       │
│                                                            │
│  Win Condition: Build Stamina 220+ points before race     │
│                                                            │
│  [PREPARE] [RUN SIMULATION] [AI DETAILED ANALYSIS]        │
│                                                            │
└────────────────────────────────────────────────────────────┘
```

---

### 2. Skill Management Screens (WF-006 to WF-008)

#### WF-006: Skill Inventory & SP Management

```
┌────────────────────────────────────────────────────────────┐
│  Skill Management                                     [≡]   │
├────────────────────────────────────────────────────────────┤
│                                                            │
│  Current SP: 450 | Total SP Earned: 2400 | Used: 1950      │
│                                                            │
│  Filter: [Owned] [Available] [All]  |  Sort: [Cost] [Name] │
│                                                            │
│  ┌────────────────────────────────────────────────────────┐│
│  │ OWNED SKILLS (22)                                      ││
│  ├────────────────────────────────────────────────────────┤│
│  │ ✓ Predator's Instinct     [Unique] SP: 180            ││
│  │   Late Surger enhancement, inherited from legacy       ││
│  │                                                        ││
│  │ ✓ Go with the Flow → Lane Legerdemain [EVOLVED]      ││
│  │   Base: 120 SP | Evolved to: 180 SP | Hint: 1 (-20%) ││
│  │                                                        ││
│  │ ✓ Nimble                  [Normal] SP: 120            ││
│  │   Speed +2, acquired Turn 35                          ││
│  │                                                        ││
│  │ ✓ Turf Runner             [Normal] SP: 120            ││
│  │   Turf surface bonus +5%                              ││
│  └────────────────────────────────────────────────────────┘│
│                                                            │
│  ┌────────────────────────────────────────────────────────┐│
│  │ AVAILABLE SKILLS TO ACQUIRE (128)                      ││
│  ├────────────────────────────────────────────────────────┤│
│  │ ⚡ Going Strong [Normal] 120 SP (10 hints available)  ││
│  │   Recommended for build | Cost with hints: 96 SP      ││
│  │   [ACQUIRE] [MORE INFO]                               ││
│  │                                                        ││
│  │ ⚡ Cool Breeze [Normal] 120 SP (Owned as Rare)        ││
│  │   Weather specific, consider evolution              ││
│  │   [VIEW EVOLUTION] [MORE INFO]                        ││
│  │                                                        ││
│  │ ○ Withstand [Normal] 150 SP (Available)              ││
│  │   Durability boost, hint from support card (pending)  ││
│  │   [QUEUE FOR HINT] [ACQUIRE]                         ││
│  └────────────────────────────────────────────────────────┘│
│                                                            │
│  Skill Build Recommendations (from AI)                     │
│  ┌────────────────────────────────────────────────────────┐│
│  │ Priority Queue:                                        ││
│  │ 1. Going Strong (96 SP with hints)                    ││
│  │ 2. Withstand (120 SP baseline)                        ││
│  │ 3. Weather specific skills (120 SP each)              ││
│  │                                                        ││
│  │ Total SP needed: 336 | Current SP: 450 | ✓ AFFORDABLE ││
│  │                                                        ││
│  │                 [BUILD THIS] [CUSTOMIZE]              ││
│  └────────────────────────────────────────────────────────┘│
│                                                            │
└────────────────────────────────────────────────────────────┘
```

---

### 3. Support Card Deck Configuration (WF-009)

#### WF-009: Support Deck Manager

```
┌────────────────────────────────────────────────────────────┐
│  Configure Support Deck (6 Cards)                     [≡]   │
├────────────────────────────────────────────────────────────┤
│                                                            │
│  Current Deck Power: S Tier | Meta Score: 92/100           │
│                                                            │
│  DECK COMPOSITION (6 of 6)                                 │
│  ┌─────────────────┬─────────────────┬─────────────────┐  │
│  │ 1. Mejiro Dober│ 2. Tokai Teio   │ 3. Kitasan Black│  │
│  │ SSR · Power    │ SSR · Speed     │ SSR · Stamina   │  │
│  │ LB: 4★★★★★    │ LB: 2★★☆☆☆     │ LB: 4★★★★★     │  │
│  │ Bond: 90% +3B  │ Bond: 75%       │ Bond: 85% +2B   │  │
│  │ [EDIT]         │ [EDIT]          │ [EDIT]          │  │
│  └─────────────────┴─────────────────┴─────────────────┘  │
│                                                            │
│  ┌─────────────────┬─────────────────┬─────────────────┐  │
│  │ 4. Narita Brian│ 5. Symboli Rudolf│ 6. [BORROWED]   │  │
│  │ SSR · Wit      │ SSR · Guts      │ SR · Pal        │  │
│  │ LB: 3★★★☆☆    │ LB: 2★★☆☆☆     │ LB: 1★☆☆☆☆     │  │
│  │ Bond: 80% +2B  │ Bond: 70%       │ Bond: 60%       │  │
│  │ [EDIT]         │ [EDIT]          │ [EDIT]          │  │
│  └─────────────────┴─────────────────┴─────────────────┘  │
│                                                            │
│  Deck Analysis                     Recommendations        │
│  ┌─────────────────────────┐      ┌──────────────────┐   │
│  │ Distribution:           │      │ Alternative:     │   │
│  │ • Speed: 2 cards       │      │ • Swap Narita    │   │
│  │ • Power: 1 card        │      │   Brian for      │   │
│  │ • Stamina: 1 card      │      │   another Speed  │   │
│  │ • Guts: 1 card         │      │   (less Wit)     │   │
│  │ • Wit: 1 card          │      │                  │   │
│  │                         │      │ • Consider pal   │   │
│  │ ✓ Good diversity        │      │   synergy cards  │   │
│  │ ✓ Meta cards included   │      │                  │   │
│  │ ✓ Friendship unlocked   │      │ [TRY ALTERNATIVE]│   │
│  └─────────────────────────┘      └──────────────────┘   │
│                                                            │
│  Support Card Skills Provided                              │
│  ┌──────────────────────────────────────────────────────┐ │
│  │ Speed Facility:                                      │ │
│  │ • Lane Guidance (Mejiro Dober - guaranteed!)        │ │
│  │ • Cool Breeze (Tokai Teio, Kitasan Black)           │ │
│  │                                                      │ │
│  │ Stamina Facility:                                    │ │
│  │ • Stamina Boost (Kitasan Black, Symboli Rudolf)    │ │
│  │ • Perseverance (Narita Brian)                       │ │
│  │                                                      │ │
│  │ ... [more facilities]                               │ │
│  └──────────────────────────────────────────────────────┘ │
│                                                            │
│                          [SAVE DECK] [RECOMMEND OPTIMAL]   │
│                                                            │
└────────────────────────────────────────────────────────────┘
```

---

## Wireframe Summary

**Total Screens**: 20+
- Character Management: 5 screens
- Training & Race: 4 screens
- Skill Management: 3 screens
- Support Cards: 2 screens
- AI & Settings: 3 screens
- Additional: Historical data, reports, settings

**Design Principles**:
- Mobile-first responsive design
- Dark mode support (WCAG 2.2 AA contrast)
- Accessibility: Keyboard navigation, screen reader support
- Information hierarchy: Critical data prominent
- Real-time updates via WebSocket
- Progressive disclosure (expandable sections)

**Component Library**:
- Stat bars (visual + numeric)
- Progress bars (goals, training)
- Card layouts (characters, support cards, skills)
- Tables (history, predictions)
- Modal dialogs (confirmations, details)
- Tooltips & info icons
- Status indicators (symbols, colors, badges)

---

## Next Steps

1. **Import into Design Tool**: Use coordinates/spacing for Figma/XD
2. **Create Component Library**: Reusable design tokens
3. **Prototype Interactions**: Transitions and animations
4. **Accessibility Testing**: Screen reader testing, keyboard nav
5. **Mobile Responsive**: Test on 320px, 768px, 1200px breakpoints
6. **Dark Mode**: Verify contrast ratios and color schemes

---

**Related Documents**: [SPEC Index](../specs/000_SPECS_INDEX.md), [TECH-FLOW Index](../tech-flow/000_TECH_FLOW_INDEX.md)

