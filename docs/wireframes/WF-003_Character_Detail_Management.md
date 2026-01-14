# WF-003: Character Detail & Management

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-001], [SPEC-001], [FLOW-001], [SEQ-001]

---

## Layout (Desktop)
```
+----------------------------------------------------------------------------------+
| Header: Run Selector | Character: [Name] | Actions: Export | Duplicate | Delete   |
+----------------------------------------------------------------------------------+
| Two-Column Content                                                                |
| Left (wider):                                                                     |
| +-------------------------------+                                                 |
| | Stats Panel                   |                                                 |
| | Speed  A (980)   bar          |                                                 |
| | Stamina B+ (820) bar          |                                                 |
| | Power  B (780)  bar           |                                                 |
| | Guts   B (760)  bar           |                                                 |
| | Wiz.   A- (890) bar           |                                                 |
| | Aptitudes chips               |                                                 |
| +-------------------------------+                                                 |
| | Mood/Energy/Condition strip   | Recovery CTA                                    |
| +-------------------------------+                                                 |
| | Goals Timeline                | Milestones with status icons                    |
| +-------------------------------+                                                 |
| | Race Schedule                 | Table: Race, Grade, Date, Readiness             |
| +-------------------------------+                                                 |
|
| Right (narrow):                                                                     |
| +-------------------------------+                                                 |
| | Support Deck summary (6 slots)|                                                 |
| | Bond progress mini-bars       |                                                 |
| +-------------------------------+                                                 |
| | Skills summary                | Count, SP, key rare skills                      |
| +-------------------------------+                                                 |
| | AI Advisor quick ask          | Textbox + send                                  |
| +-------------------------------+                                                 |
```

## Layout (Mobile)
- Stack panels; actions in overflow menu.  
- Race schedule collapsible accordion.  
- Deck summary uses horizontal scroll chips.

## Components
- Stats panel with grade badges and numeric values.  
- Goals timeline with goal type and due turn.  
- Race schedule table with readiness badge and entry link.  
- Deck summary with card avatars and bond bars.  
- Skills summary highlighting rare/evolved skills and SP available.

## Notes
- Provide edit buttons inline for goals and schedule items.  
- Dark mode friendly backgrounds; cards use subtle borders.  
- Keyboard focus indicators for table rows and buttons.
