# Game UI Alignment Strategy

**Version**: 1.0.0
**Date**: February 22, 2026
**Status**: Initial Draft
**Based On**: `docs/design/game-alignment-analysis.md`

---

## 1. Design Philosophy

The Umamusume Career Planner will adopt a **"Game-Aligned, Web-Optimized"** design strategy.

- **Game-Aligned**: Use familiar colors, icons, layout metaphors (cards, grids), and terminology so players instantly understand the interface.
- **Web-Optimized**: Leverage the strengths of desktop/web interfaces (mouse/keyboard, larger screen real estate) for complex planning tasks that are tedious in-game (e.g., bulk comparison, deck optimization).

## 2. Visual Identity System

### 2.1 Color Palette (Verified)

Derived from game screenshot analysis to ensure stronger visual recognition.

| Category        | Primary Color   | Tailwind Class                                   | Hex Code (Approx) | Usage                       |
| :-------------- | :-------------- | :----------------------------------------------- | :---------------- | :-------------------------- |
| **Speed**       | Blue            | `bg-blue-500`                                    | `#3B82F6`         | Speed stat, icons           |
| **Stamina**     | Vivid Green     | `bg-green-500`                                   | `#22C55E`         | Stamina stat, healing       |
| **Power**       | Orange          | `bg-orange-500`                                  | `#F97316`         | Power stat, physical effort |
| **Guts**        | Amber/Yellow    | `bg-amber-400`                                   | `#FBBF24`         | Guts stat, burning spirit   |
| **Wit**         | Azure Blue      | `bg-sky-500`                                     | `#0EA5E9`         | Wit stat, mental skills     |
| **TP (Energy)** | Orange Gradient | `bg-gradient-to-r from-orange-400 to-yellow-400` | -                 | Action points               |
| **Success**     | Lime Green      | `bg-lime-500`                                    | `#84CC16`         | Success states, bonuses     |
| **Action**      | White/Green     | `bg-white` / `bg-green-600`                      | -                 | Primary buttons             |

### 2.2 Typography

- **Headings**: Sans-serif, Bold (700+), High contrast.
- **Data**: Monospaced or Tabular nums for stats blocks to ensure alignment.
- **Body**: High legibility sans-serif (Inter/Roboto).

## 3. Component Architecture

### 3.1 Card System

The "Card" is the fundamental unit of the UI, mirroring the game's Trainee and Support Cards.

- **Trainee Card**:
  - **Portrait**: Top/Left focus.
  - **Rarity**: Star rating (1-5★) prominently displayed.
  - **Stats**: Radar chart or Bar chart summary.
  - **Badges**: Distance/Surface aptitudes (A/B/C/etc.) as quick-read pills.

- **Support Card**:
  - **Visual**: Vertical aspect ratio (2:3).
  - **Type Icon**: Top Right corner ( Speed, Stamina, etc.).
  - **Level**: Bottom overlay (e.g., "Lvl 50").
  - **Limit Break**: Diamond indicators on frame.

### 3.2 Deck Builder (Support Formation)

Replicate the **6-slot Grid Layout** observed in the game:

- **Upper Row**: 3 Main Cards.
- **Lower Row**: 2 Sub Cards + 1 Friend Card.
- **Interactions**: Click slot to open card selector modal.
- **Summary**: Total bonuses displayed below the grid.

### 3.3 Career Timeline

Visualize the 3-year career structure horizontally or vertically:

- **Segments**: Junior Year -> Classic Year -> Senior Year -> URA.
- **Turn Indicators**: Distinct markers for "Summer Camp" (July) and "Goal Races".
- **Events**: Pop-up or accordion details for specific turn events (e.g., Valentine's).

## 4. Layout Strategy

### 4.1 Desktop (Planner View)

- **Sidebar**: Quick navigation (mimics the bottom nav of mobile but vertical).
- **Main Stage**:
  - **Top**: Persistent resource/status bar (Turn counter, SP budget).
  - **Center**: The active planning workspace (Deck construction, Training grid).
  - **Right Panel (Collapsible)**: Simulation results, predicted stats, calculations.

### 4.2 Mobile (Companion View)

- **Bottom Navigation**: Enhance | Story | Home | Race | Scout (matches game).
- **View**: Stacked cards, simplified lists.
- **Interactions**: Tap-to-edit, simpler forms.

## 5. Implementation Roadmap

1. **Foundation**: Set up Tailwind config with Uma-specific colors.
2. **Components**: Build `TraineeCard`, `SupportCard`, and `StatBar` Blade components.
3. **Layouts**: Create `DeckBuilder` grid and `CareerTimeline` view.
4. **Integration**: Connect components to `CareerPlanner` logic.
