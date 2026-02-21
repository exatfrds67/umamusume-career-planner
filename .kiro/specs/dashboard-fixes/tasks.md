# Implementation Plan: Dashboard Fixes

## Overview

Fix 12 identified bugs on the dashboard page spanning Alpine.js scope errors, JS function signature mismatches, a malformed Blade comment, missing controller data, incorrect grade scale, wrong turn counter max, a dead UI button, and unused variables. All fixes are in the presentation and controller layers — no database changes.

## Tasks

- [x] 1. Fix line chart component (Alpine scope + JS function)
  - [x] 1.1 Rewrite `resources/js/components/line-chart.js` to accept `(data, labels, colors, animated, responsive)` parameters, store them as state, compute `lastDataPoint`, `averageValue`, `maxValue` from the first dataset, and use the passed data to configure Chart.js
    - _Requirements: 1.2, 1.3, 1.4_
  - [x] 1.2 Update `resources/views/components/line-chart.blade.php` to move `x-data="lineChart(...)"` from the `<canvas>` element to the outer card `<div>` that wraps both the chart container and the Data Summary Stats grid. The `<canvas>` should use `x-ref="canvas"` instead
    - _Requirements: 1.1_
  - [ ]* 1.3 Write property test for line chart stat computation
    - **Property 1: Line chart computes correct summary statistics**
    - **Validates: Requirements 1.2, 1.3**

- [x] 2. Fix class pyramid component (Alpine scope + JS function + malformed comment)
  - [x] 2.1 Rewrite `resources/js/components/class-pyramid.js` to accept a `(grades)` parameter and expose `sortedGrades` (sorted by fans descending), `totalFans` (sum), `maxFans` (max), and `hoveredLayer` properties
    - _Requirements: 2.2, 2.3_
  - [x] 2.2 Update `resources/views/components/class-pyramid.blade.php` to move `x-data="classPyramid(...)"` to the outermost card `<div>` so it wraps the Total Fanbase summary, all variant sections, and the legend. Remove the duplicate `x-data` from the variant-specific divs
    - _Requirements: 2.1_
  - [x] 2.3 Fix the malformed Blade comment on line ~140 of `class-pyramid.blade.php`: change `{{-- Legend {{--` to a proper closing `--}}` so the legend section renders as styled HTML
    - _Requirements: 3.1, 3.2_
  - [ ]* 2.4 Write property test for class pyramid fan computation
    - **Property 2: Class pyramid computes correct fan totals and sorting**
    - **Validates: Requirements 2.2, 2.3**

- [x] 3. Fix activity timeline component (property names + data source)
  - [x] 3.1 Rewrite `resources/js/components/activity-timeline.js` to accept an `(events)` parameter, expose `events`, `displayedEvents` (paginated subset), and implement all helper methods referenced in the Blade template: `getEventColor`, `getEventIcon`, `getEventBadgeStyle`, `formatEventType`, `getTimeAgo`, `formatDate`, `formatMetadata`, and `loadMore`
    - Remove the `fetch('/api/activities')` call entirely
    - _Requirements: 4.1, 4.2, 4.3, 4.4_
  - [ ]* 3.2 Write property tests for activity timeline initialization and pagination
    - **Property 3: Activity timeline initializes with correct subset and working helpers**
    - **Property 4: Activity timeline pagination reveals more events**
    - **Validates: Requirements 4.1, 4.3, 4.4**

- [x] 4. Checkpoint - Verify JS component fixes
  - Ensure all Alpine.js console errors are resolved by reviewing the three fixed components. Ask the user if questions arise.

- [x] 5. Fix grade scale to cap at S
  - [x] 5.1 Update the `$getGrade` closure in `resources/views/components/dashboard/stats-snapshot.blade.php` to return 'S' as the maximum grade for values ≥ 1100, removing the 'SS' tier
    - _Requirements: 6.1, 6.2_
  - [x] 5.2 Update `Character::getStatGrade()` in `app/Models/Character.php` to return 'S' as the maximum grade for values ≥ 1100, removing 'SS+', 'SS', and 'S+' tiers
    - _Requirements: 6.3, 6.4_
  - [ ]* 5.3 Write property tests for grade scale
    - **Property 5: Stats snapshot grade scale caps at S (Blade closure)**
    - **Property 6: Character model grade scale caps at S**
    - **Validates: Requirements 6.1, 6.2, 6.3, 6.4**

- [x] 6. Fix turn counter maximum and stage label
  - [x] 6.1 In `app/View/Components/TurnCounter.php`, change the `$total` default from 78 to 70 and update the class docblock to reference "70-turn career"
    - _Requirements: 7.1, 7.3_
  - [x] 6.2 In `resources/views/components/turn-counter.blade.php`, change "Senior (49-78)" to "Senior (49-70)" in the stage labels
    - _Requirements: 7.2_
  - [ ]* 6.3 Write unit test for TurnCounter default total value
    - Test that `new TurnCounter()` has `$total === 70`
    - _Requirements: 7.1_

- [x] 7. Fix mood/energy widget and controller cleanup
  - [x] 7.1 Remove the non-functional "Recovery Options" button from `resources/views/components/dashboard/mood-energy-widget.blade.php`
    - _Requirements: 8.1_
  - [x] 7.2 In `app/Http/Controllers/DashboardController.php`, remove the unused `$stats` and `$priorities` local variables from `getTrainingSuggestions()` (keep `$rawStats` and `$rawPriorities` which are used)
    - _Requirements: 9.1_

- [x] 8. Pass analytics data from Dashboard Controller
  - [x] 8.1 Add a private `getAnalyticsData(Character $character)` method to `DashboardController` that returns `statProgression`, `progressionLabels`, `raceGrades`, and `recentActivity` arrays derived from the character's current stats, race schedule, and skill acquisitions
    - _Requirements: 5.1, 5.2, 5.3, 5.4_
  - [x] 8.2 Add the analytics data keys to `prepareDashboardData()` return array and add empty arrays for them in `getEmptyDashboardData()`
    - _Requirements: 5.5_
  - [ ]* 8.3 Write feature test for DashboardController analytics data
    - Test that the dashboard view receives `statProgression`, `progressionLabels`, `raceGrades`, and `recentActivity` when a character exists
    - Test that empty arrays are passed when no character exists
    - _Requirements: 5.1, 5.2, 5.3, 5.4, 5.5_

- [x] 9. Final checkpoint - Ensure all tests pass
  - Run `php artisan test --compact` to verify all PHP tests pass. Ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- JS property tests require a JS testing framework (Vitest + fast-check recommended)
- PHP tests use Pest v4
- No database migrations are needed — all fixes are in views, JS, and controller
