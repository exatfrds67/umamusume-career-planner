# WF-002: Character Creation Wizard

## Umamusume Pretty Derby Career Planner

**Document Version**: 1.0  
**Date**: January 14, 2026  
**Related Documents**: [PRD-001], [SPEC-001], [FLOW-001], [SEQ-001]

---

## Layout (Desktop Wizard - Stepper)
```
+----------------------------------------------------------------------------------+
| Header: New Run Wizard | Step 1 of 4: Trainee & Scenario                         |
+----------------------------------------------------------------------------------+
| Steps Sidebar: 1) Trainee 2) Parents/Factors 3) Support Deck 4) Review           |
+----------------------------------------------------------------------------------+
| Content Row (2 cols)                                                             |
| [Left] Trainee List w/ Filters        [Right] Selected Preview                   |
|                                                                              |
| Trainee List: search, rarity filter, distance aptitudes, season filter.          |
| Cards: Name, rarity, aptitudes chart, growth rates.                              |
|                                                                              |
| Preview Panel:                                                                  |
| - Portrait + Name                                                               |
| - Scenario dropdown                                                             |
| - Base stats + growth bars                                                      |
| - Aptitude chips (A/B/C)                                                        |
| - CTA: Next                                                                     |
+----------------------------------------------------------------------------------+
| Footer: Back | Next/Continue (disabled until selection)                         |
+----------------------------------------------------------------------------------+
```

## Key Steps
1) Trainee & Scenario selection.  
2) Parents & Factors: parent pickers, factor summary, inheritance seed note.  
3) Support Deck: 6-slot grid, validation badges, auto-fill option.  
4) Review & Confirm: summary of trainee, scenario, parents, deck, projected stats.

## Layout (Mobile)
- Top progress bar instead of sidebar; steps as tabs.  
- Lists become vertical cards; preview collapses below selection.  
- Footer has sticky Next/Back buttons.

## Notes
- Validation: disable Next until required fields selected per step.  
- Show factor bonuses preview in Step 2; deck type counts warning in Step 3.  
- Accessibility: Stepper has aria-current; ensure keyboard navigation between steps.
