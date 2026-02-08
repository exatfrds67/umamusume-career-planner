# Sidebar Minimize Visual Reference

**Document Version**: 1.0.0  
**Date**: February 8, 2026  
**Related**: [sidebar-minimize-implementation-plan.md](./sidebar-minimize-implementation-plan.md)

---

## Overview

This document provides visual representations of the sidebar minimize feature in different states and breakpoints.

---

## Desktop Layout States

### Expanded State (Default)

```
┌─────────────────────────────────────────────────────────────────────┐
│                        Application Layout                            │
├──────────────────┬──────────────────────────────────────────────────┤
│                  │  ┌────────────────────────────────────────────┐  │
│  ┌────────────┐  │  │         Top Status Bar                     │  │
│  │ [Logo]     │  │  │  Turn: 15/78 | SP: 450 | Mode: Account    │  │
│  │ Umamusume  │◄─┤  └────────────────────────────────────────────┘  │
│  │ Career     │  │  ┌────────────────────────────────────────────┐  │
│  │ Planner    │  │  │         Header / Breadcrumb                │  │
│  └────────────┘  │  └────────────────────────────────────────────┘  │
│                  │                                                   │
│  ┌────────────┐  │  ┌────────────────────────────────────────────┐  │
│  │ 📊 Dashboard│  │  │                                            │  │
│  ├────────────┤  │  │                                            │  │
│  │ ⭐ Characters│  │  │                                            │  │
│  ├────────────┤  │  │                                            │  │
│  │ 🏋️ Training │  │  │          Main Content Area                 │  │
│  ├────────────┤  │  │                                            │  │
│  │ 🏇 Races    │  │  │                                            │  │
│  ├────────────┤  │  │                                            │  │
│  │ ⚡ Skills   │  │  │                                            │  │
│  ├────────────┤  │  │                                            │  │
│  │ 🎴 Support  │  │  │                                            │  │
│  │    Cards    │  │  │                                            │  │
│  ├────────────┤  │  └────────────────────────────────────────────┘  │
│  │ ▼ Data Mgmt│  │                                                   │
│  │   • Import │  │                                                   │
│  │   • Export │  │                                                   │
│  ├────────────┤  │                                                   │
│  │ ▼ Analytics│  │                                                   │
│  ├────────────┤  │                                                   │
│  │ ▼ AI Tools │  │                                                   │
│  └────────────┘  │                                                   │
│                  │                                                   │
│  Width: 288px    │  Width: calc(100vw - 288px)                      │
│  (w-72)          │  (lg:pl-72)                                       │
└──────────────────┴──────────────────────────────────────────────────┘
```

### Minimized State

```
┌─────────────────────────────────────────────────────────────────────┐
│                        Application Layout                            │
├────┬────────────────────────────────────────────────────────────────┤
│    │  ┌──────────────────────────────────────────────────────────┐  │
│ ┌─┐│  │         Top Status Bar                                   │  │
│ │🏇││  │  Turn: 15/78 | SP: 450 | Mode: Account                  │  │
│ └─┘│◄─┤  └──────────────────────────────────────────────────────┘  │
│    │  ┌──────────────────────────────────────────────────────────┐  │
│ 📊 │  │         Header / Breadcrumb                              │  │
│    │  └──────────────────────────────────────────────────────────┘  │
│ ⭐ │                                                                 │
│    │  ┌──────────────────────────────────────────────────────────┐  │
│ 🏋️ │  │                                                          │  │
│    │  │                                                          │  │
│ 🏇 │  │                                                          │  │
│    │  │                                                          │  │
│ ⚡ │  │          Main Content Area                               │  │
│    │  │          (More space available)                          │  │
│ 🎴 │  │                                                          │  │
│    │  │                                                          │  │
│ 💾 │  │                                                          │  │
│    │  │                                                          │  │
│ 📊 │  │                                                          │  │
│    │  │                                                          │  │
│ 🤖 │  └──────────────────────────────────────────────────────────┘  │
│    │                                                                 │
│ 80px│  Width: calc(100vw - 80px)                                    │
│(w-20)│  (lg:pl-20)                                                   │
└────┴────────────────────────────────────────────────────────────────┘
```

---

## Component States

### Toggle Button States

#### Expanded State

```
┌──────────────────────────────────────┐
│  ┌────────────────────────────────┐  │
│  │ [Logo] Umamusume Career Planner│◄─┤  ← Toggle Button
│  └────────────────────────────────┘  │     (Chevron Double Left)
└──────────────────────────────────────┘
```

#### Minimized State

```
┌──────┐
│ ┌──┐ │
│ │🏇│►│  ← Toggle Button
│ └──┘ │     (Chevron Double Right)
└──────┘
```

### Navigation Item States

#### Expanded Navigation Item

```
┌────────────────────────────────┐
│  📊  Dashboard                 │  ← Icon + Label
└────────────────────────────────┘
```

#### Minimized Navigation Item (with Tooltip)

```
┌────┐     ┌──────────────┐
│ 📊 │────►│  Dashboard   │  ← Tooltip appears on hover
└────┘     └──────────────┘
```

### Collapsible Group States

#### Expanded - Group Closed

```
┌────────────────────────────────┐
│  💾  Data Management        ▶  │  ← Chevron indicates closed
└────────────────────────────────┘
```

#### Expanded - Group Open

```
┌────────────────────────────────┐
│  💾  Data Management        ▼  │  ← Chevron indicates open
├────────────────────────────────┤
│      • Data Hub                │
│      • Import Data             │
│      • Export Data             │
│      • Migration               │
│      • Backup & Restore        │
└────────────────────────────────┘
```

#### Minimized - Group Hidden

```
┌────┐     ┌──────────────────┐
│ 💾 │────►│ Data Management  │  ← Tooltip shows group name
└────┘     │  • Data Hub      │     (no submenu in tooltip)
           │  • Import        │
           │  • Export        │
           └──────────────────┘
```

---

## Responsive Breakpoints

### Mobile (<640px) - Bottom Navigation

```
┌─────────────────────────────────┐
│                                 │
│                                 │
│                                 │
│       Main Content              │
│       (Full Width)              │
│                                 │
│                                 │
├─────────────────────────────────┤
│ 📊 │ ⭐ │ 🏋️ │ 🏇 │ ⚡ │  ← Bottom Nav
└─────────────────────────────────┘
```

### Tablet (640-1024px) - Overlay Sidebar

```
┌─────────────────────────────────┐
│  ☰  Header                      │  ← Hamburger menu
├─────────────────────────────────┤
│                                 │
│       Main Content              │
│       (Full Width)              │
│                                 │
└─────────────────────────────────┘

When menu opened:
┌──────────────┬──────────────────┐
│              │  ☰  Header       │
│  Sidebar     ├──────────────────┤
│  (Overlay)   │                  │
│              │  Main Content    │
│  📊 Dashboard│  (Dimmed)        │
│  ⭐ Characters│                  │
│  ...         │                  │
└──────────────┴──────────────────┘
```

### Desktop (≥1024px) - Fixed Sidebar with Minimize

```
See "Desktop Layout States" section above
```

---

## Transition Animation

### Collapse Animation (300ms)

```
Frame 1 (0ms):     Frame 2 (150ms):   Frame 3 (300ms):
┌──────────┐       ┌────────┐         ┌────┐
│ 📊 Dash  │  ───► │ 📊 Da  │  ───►   │ 📊 │
└──────────┘       └────────┘         └────┘
  288px              154px              80px
```

### Expand Animation (300ms)

```
Frame 1 (0ms):     Frame 2 (150ms):   Frame 3 (300ms):
┌────┐             ┌────────┐         ┌──────────┐
│ 📊 │  ───►       │ 📊 Da  │  ───►   │ 📊 Dash  │
└────┘             └────────┘         └──────────┘
  80px               154px              288px
```

---

## Tooltip Positioning

### Right-Positioned Tooltip (Default)

```
┌────┐
│ 📊 │────►┌──────────────┐
└────┘     │  Dashboard   │
           └──────────────┘
```

### Tooltip with Arrow

```
┌────┐
│ 📊 │◄───┌──────────────┐
└────┘    │  Dashboard   │
          └──────────────┘
```

---

## Color Scheme

### Light Mode

```
┌────────────────────────────────┐
│  Background: #FFFFFF           │
│  Text: #111827                 │
│  Border: #E5E7EB               │
│  Hover: #F9FAFB                │
│  Active: #EFF6FF (blue-50)     │
└────────────────────────────────┘
```

### Dark Mode

```
┌────────────────────────────────┐
│  Background: #1F2937           │
│  Text: #F9FAFB                 │
│  Border: #374151               │
│  Hover: #374151                │
│  Active: #1E3A8A (blue-900/50) │
└────────────────────────────────┘
```

---

## Accessibility Features

### Focus Indicators

```
┌────────────────────────────────┐
│  ┌──────────────────────────┐  │
│  │ 📊  Dashboard            │  │  ← 2px blue outline
│  └──────────────────────────┘  │     + 4px shadow
└────────────────────────────────┘
```

### Screen Reader Announcements

```
User Action:          Screen Reader Says:
─────────────────────────────────────────
Click minimize    →   "Sidebar minimized"
Click expand      →   "Sidebar expanded"
Hover icon        →   "Dashboard, link"
Press Alt+B       →   "Sidebar toggled"
```

---

## Implementation Notes

### CSS Classes

#### Expanded State

- Sidebar: `lg:w-72` (288px)
- Content: `lg:pl-72` (288px padding-left)
- Logo: Full text visible
- Labels: All visible

#### Minimized State

- Sidebar: `lg:w-20` (80px)
- Content: `lg:pl-20` (80px padding-left)
- Logo: Icon only
- Labels: Hidden (except in tooltips)

### Alpine.js Bindings

```html
<!-- Dynamic width -->
:class="$store.sidebar.minimized ? 'lg:w-20' : 'lg:w-72'"

<!-- Show/hide labels -->
x-show="!$store.sidebar.minimized"

<!-- Toggle button icon -->
x-show="$store.sidebar.minimized"  <!-- Expand icon -->
x-show="!$store.sidebar.minimized" <!-- Collapse icon -->
```

---

## Related Documentation

- [Implementation Plan](./sidebar-minimize-implementation-plan.md)
- [Component Inventory](../design/component-inventory.md)
- [Wireframes Index](../01-wireframes/000_WIREFRAMES_INDEX.md)

---

*This visual reference complements the implementation plan and provides clear visual guidance for developers and designers.*
