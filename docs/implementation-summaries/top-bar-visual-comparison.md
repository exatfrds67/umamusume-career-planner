# Top Bar Visual Comparison

**Date**: 2026-02-09  
**Component**: Application Header & Status Bar  
**Related**: top-bar-enhancement-summary.md

---

## Before vs After

### Desktop View (≥1024px)

**BEFORE**:

```text
┌──────────────────────────────────────────────────────────────────────┐
│ [≡]  [Search...]                    [🌙] [🔔] [👤 Admin ▼]          │
├──────────────────────────────────────────────────────────────────────┤
│ Turn 15 / 70          SP 0          Storage [Local]                  │
└──────────────────────────────────────────────────────────────────────┘
```text

**AFTER**:

```text
┌──────────────────────────────────────────────────────────────────────┐
│ 📅 Senior 15/70 (55 left)  ⚡ 78/100  😊 Good  SP 0  Storage [Account]│
├──────────────────────────────────────────────────────────────────────┤
│ [≡] Run: Mejiro Ardan ▼  [Search...]    [?] [🌙] [🔔] [👤 Admin ▼] │
└──────────────────────────────────────────────────────────────────────┘
```

### Tablet View (640-1024px)

**BEFORE**:

```text
┌────────────────────────────────────────────────────────┐
│ [≡]  [Search...]           [🌙] [🔔] [👤 ▼]          │
├────────────────────────────────────────────────────────┤
│ Turn 15 / 70     SP 0     Storage [Local]             │
└────────────────────────────────────────────────────────┘
```text

**AFTER**:

```text
┌────────────────────────────────────────────────────────┐
│ 📅 15/70 (55 left)  ⚡ 78/100  😊  SP 0  Storage [Acc] │
├────────────────────────────────────────────────────────┤
│ [≡] Run: Ardan ▼  [Search...]  [?] [🌙] [🔔] [👤 ▼]  │
└────────────────────────────────────────────────────────┘
```

### Mobile View (<640px)

**BEFORE**:

```text
┌────────────────────────────────┐
│ [≡]  [Search...]      [👤 ▼]  │
├────────────────────────────────┤
│ Turn 15/70  SP 0  [Local]     │
└────────────────────────────────┘
```text

**AFTER**:

```text
┌────────────────────────────────┐
│ 📅 15/70  ⚡ 78  😊  SP 0  [Acc]│
├────────────────────────────────┤
│ [≡]  [Search...]  [?] [🌙] [👤]│
└────────────────────────────────┘
```

---

## Key Visual Changes

### 1. Status Bar Enhancements

| Element | Before | After | Notes |
| --- | --- | --- | --- |
| **Turn Counter** | `Turn 15 / 70` | `📅 Senior 15/70 (55 left)` | Added emoji, career stage, turns remaining |
| **Energy** | ❌ Not shown | `⚡ 78/100` | New indicator with color coding |
| **Mood** | ❌ Not shown | `😊 Good` | New indicator with emoji |
| **SP Counter** | `SP 0` | `SP 0` | Unchanged format |
| **Storage Mode** | `Storage [Local]` | `Storage [Account]` | Changed badge color (green for Account) |

### 2. Header Navigation Enhancements

| Element | Before | After | Notes |
| --- | --- | --- | --- |
| **Run Selector** | ❌ Not present | `Run: Mejiro Ardan ▼` | New dropdown for switching runs |
| **Search** | Present | Present | Unchanged |
| **Help Icon** | ❌ Not present | `[?]` | New help/documentation link |
| **Theme Toggle** | Present | Present | Unchanged |
| **Notifications** | Present | Present | Unchanged |
| **User Menu** | Present | Present | Unchanged |

---

## Color Coding

### Energy Indicator

| Range | Color | Example |
| --- | --- | --- |
| 70-100 | 🟢 Green | `⚡ 85/100` |
| 40-69 | 🟡 Yellow | `⚡ 55/100` |
| 0-39 | 🔴 Red | `⚡ 25/100` |

### Storage Mode Badge

| Mode | Color | Badge |
| --- | --- | --- |
| Local | 🟡 Warning (Amber) | `[Local]` |
| Account | 🟢 Success (Green) | `[Account]` |
| Unknown | ⚪ Secondary (Gray) | `[—]` |

### Mood Indicator

| Mood | Emoji | Modifier | Display |
| --- | --- | --- | --- |
| Great/Excellent | 😊 | +20% | `😊 Great` |
| Good | 🙂 | +10% | `🙂 Good` |
| Normal/Neutral | 😐 | 0% | `😐 Normal` |
| Bad | 🙁 | -10% | `🙁 Bad` |
| Very Bad/Terrible | 😞 | -20% | `😞 Very Bad` |

---

## Responsive Behavior

### Desktop (≥1024px)

- ✅ All indicators visible
- ✅ Full labels shown
- ✅ Run selector visible
- ✅ Turns remaining shown
- ✅ Career stage shown

### Tablet (640-1024px)

- ✅ All indicators visible
- ⚠️ Condensed labels
- ✅ Run selector visible (abbreviated)
- ✅ Turns remaining shown
- ⚠️ Career stage hidden

### Mobile (<640px)

- ✅ Core indicators visible
- ⚠️ Emoji-only for mood
- ❌ Run selector hidden
- ❌ Turns remaining hidden
- ❌ Career stage hidden
- ⚠️ Abbreviated storage label

---

## Run Selector Dropdown

**Closed State**:

```text
┌─────────────────────────┐
│ Run: Mejiro Ardan ▼     │
└─────────────────────────┘
```text

**Open State**:

```text
┌─────────────────────────────────┐
│ Run: Mejiro Ardan ▼             │
├─────────────────────────────────┤
│ ACTIVE RUNS                     │
│                                 │
│ Mejiro Ardan                    │
│ URA Finals • Turn 15/70         │
│                                 │
│ Special Week                    │
│ Aoharu • Turn 42/70             │
│ ─────────────────────────────── │
│ + Create New Run                │
└─────────────────────────────────┘
```

---

## Accessibility Improvements

### Screen Reader Announcements

**Before**:

- "Turn 15 of 70"
- "SP 0"
- "Storage Local"

**After**:

- "Current turn 15 of 70, 55 turns remaining, Senior year"
- "Energy 78 out of 100"
- "Mood Good"
- "SP 0"
- "Storage Account mode"
- "Select career run"
- "Help and documentation"

### Keyboard Navigation

**New Shortcuts**:

- `Tab` → Navigate to run selector
- `Enter` → Open run selector dropdown
- `↑/↓` → Navigate run list
- `Enter` → Select run
- `Esc` → Close dropdown
- `Tab` → Navigate to help icon

---

## Implementation Notes

### Data Flow

```text
Controller/Livewire
    ↓
$topStatus = [
    'currentTurn' => 15,
    'maxTurns' => 70,
    'spAvailable' => 0,
    'storageMode' => 'account',
    'energy' => 78,           // NEW
    'mood' => 'good',         // NEW
    'careerStage' => 'senior' // NEW
]
    ↓
layouts/app.blade.php
    ↓
components/top-status-bar.blade.php
    ↓
Rendered Status Bar
```text

### Component Structure

```text
<header> (sticky)
  ├── <top-status-bar> (status indicators)
  │   ├── Turn Counter (with stage & remaining)
  │   ├── Energy Indicator (color-coded)
  │   ├── Mood Indicator (emoji + label)
  │   ├── SP Counter
  │   └── Storage Mode Badge
  │
  └── <app-header> (navigation)
      ├── Run Selector Dropdown (NEW)
      ├── Search Field
      ├── Help Icon (NEW)
      ├── Theme Toggle
      ├── Notifications
      └── User Menu
```

---

## Testing Scenarios

### Visual Testing

- [ ] Status bar displays correctly at all breakpoints
- [ ] Energy color changes based on value
- [ ] Mood emoji displays correctly
- [ ] Storage badge shows correct color
- [ ] Run selector dropdown opens/closes smoothly
- [ ] Help icon is visible and clickable
- [ ] Dark mode styling is correct

### Functional Testing

- [ ] Turn counter calculates remaining turns correctly
- [ ] Energy indicator updates when energy changes
- [ ] Mood indicator updates when mood changes
- [ ] Storage mode badge reflects current mode
- [ ] Run selector allows switching between runs
- [ ] Help icon navigates to help page
- [ ] All dropdowns close on click-away
- [ ] Keyboard navigation works for all elements

### Accessibility Testing

- [ ] Screen readers announce all status changes
- [ ] Keyboard navigation reaches all interactive elements
- [ ] Focus indicators are visible
- [ ] ARIA labels are present and correct
- [ ] Color contrast meets WCAG 2.2 AA
- [ ] Touch targets are at least 44x44px

---

**Summary**: The enhanced top bar provides significantly more information at a glance while maintaining excellent UX and
accessibility. The responsive design ensures optimal display across all device sizes.
