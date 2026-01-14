# WF-001: Dashboard Overview

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-001], [SPEC-001], [FLOW-001], [SEQ-015]

---

## Layout (Desktop)
```
+----------------------------------------------------------------------------------+
| App Header: Logo | Run Selector [Current Run ▼] | Notifications 🔔 | User Menu    |
+----------------------------------------------------------------------------------+
| Sidebar                                                                                |
| - Dashboard (active)                                                                   |
| - Character                                                                            |
| - Training                                                                             |
| - Races                                                                                |
| - Skills                                                                               |
| - Support Cards                                                                        |
| - AI Advisor                                                                           |
| - Settings                                                                             |
+----------------------------------------------------------------------------------+
| Main Grid (2 cols)                                                                     |
| [Left: Progress & Schedule]      [Right: Stats & Advisories]                           |
|                                                                                       |
| Left Column:                                                                            |
| +-------------------------------+   Right Column:                                      |
| | Current Goals                 |   +-------------------------------+                  |
| | - Short-term: ...             |   | Stat Snapshot                 |                  |
| | - Long-term: ...              |   | Speed  A  (980)               |                  |
| | Progress bars                 |   | Stamina B+ (820)              |                  |
| +-------------------------------+   | Power  B  (780)               |                  |
| | Upcoming Races (next 3)       |   | Guts   B  (760)               |                  |
| | [Date] [Race] [Grade] [Prep%] |   | Wiz.   A- (890)               |                  |
| +-------------------------------+   +-------------------------------+                  |
| | Training Suggestions (list)   |   | Mood/Energy Widget            |                  |
| | [Action] [Gains] [Risk]       |   | Mood: Good  | Energy: 72/100  |                  |
| +-------------------------------+   +-------------------------------+                  |
| | Recent Results (race/training)|   | AI Advisor Card               |                  |
| | timeline w/ icons             |   | Last tip + CTA: "Ask again"   |                  |
| +-------------------------------+   +-------------------------------+                  |
```

## Layout (Mobile)
- Collapsible sidebar into bottom nav (Dashboard, Train, Race, Skills, AI, Settings).  
- Stack cards vertically; stat snapshot collapsible.  
- Race list uses horizontal scroll chips.

## Components
- Header: run switcher, notifications, user menu.  
- Goals card: progress bars, CTA to edit goals.  
- Upcoming races: list with readiness badge and enter/preview buttons.  
- Training suggestions: top 3 with gains/risk.  
- Stat snapshot: grades, numeric values, aptitudes tooltip.  
- Mood/Energy widget: emoji + bar; button to open recovery options.  
- AI Advisor card: last response snippet, quick actions (regenerate, ask follow-up).

## Notes
- Tailwind v4 implied: grid-cols-2 on desktop, gap-4, p-4 cards.  
- Dark mode: use bg-surface/dark equivalents; badges invert colors.  
- Accessibility: keyboard focus for sidebar links; provide aria-live on AI updates.
