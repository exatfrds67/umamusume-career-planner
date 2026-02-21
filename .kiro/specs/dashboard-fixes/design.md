# Design Document: Dashboard Fixes

## Overview

This design addresses 12 identified bugs on the dashboard page. The fixes span three layers: Alpine.js component JavaScript files, Blade template markup, the PHP controller, and the Character model. No new features are added — this is purely corrective work to restore the dashboard to a working, error-free state.

## Architecture

The dashboard follows a standard Laravel pattern:

```
DashboardController (PHP) → dashboard.blade.php → Blade Components → Alpine.js (via x-data)
```

Alpine.js components are registered globally in `resources/js/app.js` via `Alpine.data('name', fn)`. Blade templates invoke them with `x-data="name(args)"`. The JS functions must accept the parameters the Blade templates pass and expose the properties the templates reference.

### Fix Categories

1. **Alpine.js Scope Fixes** (Req 1, 2): Move `x-data` to a wrapper element that encloses all elements referencing Alpine properties
2. **JS Function Signature Fixes** (Req 1, 2, 4): Rewrite JS functions to accept parameters from Blade and expose expected properties
3. **Blade Markup Fix** (Req 3): Close the malformed comment
4. **Controller Data Fix** (Req 5, 9): Add analytics data to the controller's view data, remove unused variables
5. **Domain Accuracy Fixes** (Req 6, 7): Correct grade scale and turn counter max
6. **UI Fix** (Req 8): Remove or wire the dead button

## Components and Interfaces

### 1. Line Chart Component Fix (Req 1)

**Problem**: `x-data` is on the `<canvas>` element, but `x-text="lastDataPoint"`, `x-text="Math.round(averageValue)"`, and `x-text="maxValue"` are in a sibling div outside that scope.

**Solution**:

- Move `x-data="lineChart(...)"` from the `<canvas>` to the parent `<div>` that wraps both the canvas and the Data Summary Stats section
- Rewrite `lineChart(data, labels, colors, animated, responsive)` in `line-chart.js` to:
  - Accept the 5 parameters
  - Store them as component state
  - Compute `lastDataPoint`, `averageValue`, `maxValue` from the first dataset in `data`
  - Use the passed data/labels/colors to configure Chart.js

**line-chart.js interface**:

```javascript
function lineChart(data = [], labels = [], colors = ['#3B82F6'], animated = true, responsive = true) {
    return {
        chartInstance: null,
        data,
        labels,
        colors,
        animated,
        responsive,
        lastDataPoint: 0,
        averageValue: 0,
        maxValue: 0,

        init() {
            this.computeStats();
            this.$nextTick(() => this.initChart());
        },

        computeStats() {
            const dataset = Array.isArray(this.data[0]) ? this.data[0] : this.data;
            if (dataset.length === 0) return;
            this.lastDataPoint = dataset[dataset.length - 1] ?? 0;
            this.averageValue = dataset.reduce((a, b) => a + b, 0) / dataset.length;
            this.maxValue = Math.max(...dataset);
        },

        initChart() { /* Chart.js initialization using this.data, this.labels, this.colors */ },
        // ... destroy, updateChart methods
    };
}
```

**line-chart.blade.php change**: Move `x-data` and `x-init` from the `<canvas>` to the wrapping `<div>` that contains both the chart container and the Data Summary Stats grid. The `<canvas>` becomes a plain element referenced via `this.$refs.canvas` or `this.$el.querySelector('canvas')`.

### 2. Class Pyramid Component Fix (Req 2, 3)

**Problem A**: `x-text="totalFans.toLocaleString()"` is in the Total Fanbase div ABOVE the pyramid div that has `x-data="classPyramid(...)"`. The JS function accepts no parameters and has a completely different data model.

**Problem B**: `{{-- Legend {{--` is a malformed Blade comment causing raw syntax to render.

**Solution A**:

- Move `x-data="classPyramid(...)"` to the outermost card `<div>` so it wraps the Total Fanbase summary, the pyramid/bars/cards variants, and the legend
- Rewrite `classPyramid(grades)` in `class-pyramid.js` to accept the grades array and expose `sortedGrades`, `totalFans`, `hoveredLayer`, `maxFans`

**class-pyramid.js interface**:

```javascript
function classPyramid(grades = []) {
    return {
        grades,
        sortedGrades: [],
        totalFans: 0,
        maxFans: 0,
        hoveredLayer: null,

        init() {
            this.sortedGrades = [...this.grades].sort((a, b) => b.fans - a.fans);
            this.totalFans = this.grades.reduce((sum, g) => sum + g.fans, 0);
            this.maxFans = Math.max(...this.grades.map(g => g.fans), 0);
        },
    };
}
```

**Solution B**: Fix `{{-- Legend {{--` to `{{-- Legend --}}` and add a new `{{--` before the legend content if the intent was to comment it out, or simply close the comment properly if the legend should be visible.

Based on the wireframe (WF-001), the legend section provides useful grade information, so the fix is to properly close the comment: change `{{-- Legend {{--` to a proper section header comment or remove the comment entirely and let the legend render.

### 3. Activity Timeline Component Fix (Req 4)

**Problem**: Blade references `displayedEvents` and `events` but JS defines `activities`. JS fetches from `/api/activities?page=1` which returns 404.

**Solution**:

- Rewrite `activityTimeline(events)` in `activity-timeline.js` to:
  - Accept an `events` parameter (the pre-loaded data from Blade)
  - Expose `events`, `displayedEvents` (paginated subset), and all helper methods the Blade template references
  - Remove the `fetch('/api/activities')` call entirely
  - Implement `loadMore()` to reveal more items from the pre-loaded array

**activity-timeline.js interface**:

```javascript
function activityTimeline(initialEvents = []) {
    return {
        events: initialEvents,
        displayedEvents: [],
        pageSize: 5,
        currentPage: 1,

        init() {
            this.displayedEvents = this.events.slice(0, this.pageSize);
        },

        loadMore() {
            this.currentPage++;
            this.displayedEvents = this.events.slice(0, this.currentPage * this.pageSize);
        },

        getEventColor(type) { /* return color class based on type */ },
        getEventIcon(type) { /* return emoji based on type */ },
        getEventBadgeStyle(type) { /* return badge classes */ },
        formatEventType(type) { /* capitalize type */ },
        getTimeAgo(timestamp) { /* relative time string */ },
        formatDate(dateString) { /* formatted date */ },
        formatMetadata(key, value) { /* format metadata value */ },
    };
}
```

### 4. Dashboard Controller Analytics Data (Req 5, 9)

**Problem**: Controller does not pass `$statProgression`, `$progressionLabels`, `$raceGrades`, or `$recentActivity`. Also has unused `$stats` and `$priorities` variables.

**Solution**:

- Add a `getAnalyticsData(Character $character)` private method that returns:
  - `statProgression`: Array of stat value arrays derived from character's current stats (since there's no turn-by-turn history table yet, generate a simple progression from 0 to current values)
  - `progressionLabels`: Turn labels corresponding to the data points
  - `raceGrades`: Grade distribution from the character's race schedule
  - `recentActivity`: Formatted events from recent skill acquisitions and race results
- Add these four keys to the `prepareDashboardData` return array
- Add empty arrays for these in `getEmptyDashboardData`
- Remove unused `$stats` and `$priorities` local variables from `getTrainingSuggestions` — the method already accesses `$rawStats` and `$rawPriorities` directly

### 5. Grade Scale Fix (Req 6)

**Problem**: `stats-snapshot.blade.php` returns 'SS' for ≥1200. `Character::getStatGrade()` returns 'SS+', 'SS', 'S+'. Per WF-001, S is the maximum grade.

**Solution**:

- In `stats-snapshot.blade.php`, change the `$getGrade` closure to cap at 'S' for ≥1100 (remove the SS tier)
- In `Character::getStatGrade()`, change the scale to cap at 'S' for ≥1100, removing 'SS+', 'SS', 'S+' tiers
- The corrected grade scale:

| Grade | Range    |
|-------|----------|
| S     | ≥1100    |
| A+    | ≥1000    |
| A     | ≥900     |
| B+    | ≥800     |
| B     | ≥700     |
| C+    | ≥600     |
| C     | ≥500     |
| D+    | ≥400     |
| D     | ≥300     |
| E+    | ≥200     |
| E     | ≥100     |
| F     | ≥50      |
| G     | <50      |

### 6. Turn Counter Fix (Req 7)

**Problem**: `TurnCounter.php` defaults `$total` to 78. Blade shows "Senior (49-78)". The game has 70 turns max.

**Solution**:

- Change `$total` default from 78 to 70 in `TurnCounter.php` constructor
- Change "Senior (49-78)" to "Senior (49-70)" in `turn-counter.blade.php`
- Update the class docblock from "78-turn" to "70-turn"

### 7. Recovery Options Button Fix (Req 8)

**Problem**: The button has no action — no `href`, `wire:click`, or Alpine handler.

**Solution**: Remove the Recovery Options button. The mood/energy widget already shows the relevant status information, and there is no recovery modal or route implemented. Adding a non-functional button violates the principle of not having dead UI elements. If recovery functionality is added later, the button can be reintroduced with proper wiring.

## Data Models

No database schema changes are required. All fixes are in the presentation and controller layers.

The analytics data will be derived from existing model data:

```php
// statProgression: derived from character current_stats, interpolated
// progressionLabels: generated turn labels
// raceGrades: derived from character race_schedule JSON field
// recentActivity: derived from skillAcquisitions relationship
```

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system — essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Line chart computes correct summary statistics

*For any* non-empty array of numeric data points passed to `lineChart()`, `lastDataPoint` should equal the last element of the first dataset, `averageValue` should equal the arithmetic mean of the first dataset, and `maxValue` should equal the maximum value of the first dataset.

**Validates: Requirements 1.2, 1.3**

### Property 2: Class pyramid computes correct fan totals and sorting

*For any* array of grade objects (each with a numeric `fans` property), after `classPyramid(grades)` initializes, `totalFans` should equal the sum of all `fans` values, `sortedGrades` should contain the same elements as the input sorted by `fans` descending, and `maxFans` should equal the maximum `fans` value.

**Validates: Requirements 2.2, 2.3**

### Property 3: Activity timeline initializes with correct subset and working helpers

*For any* array of event objects passed to `activityTimeline(events)`, `events` should equal the input array, `displayedEvents` should be a prefix slice of `events` with length at most `pageSize`, and helper methods (`getEventColor`, `getEventIcon`, `formatEventType`, `getTimeAgo`) should return non-empty strings for any valid event type.

**Validates: Requirements 4.1, 4.3**

### Property 4: Activity timeline pagination reveals more events

*For any* array of events with length > pageSize, each call to `loadMore()` should increase `displayedEvents.length` until it equals `events.length`, and `displayedEvents` should always be a prefix of `events`.

**Validates: Requirements 4.4**

### Property 5: Stats snapshot grade scale caps at S (Blade closure)

*For any* integer stat value, the `$getGrade` closure in `stats-snapshot.blade.php` should return 'S' for values ≥ 1100, and should never return 'SS' or any grade above 'S'.

**Validates: Requirements 6.1, 6.2**

### Property 6: Character model grade scale caps at S

*For any* integer stat value, `Character::getStatGrade()` should return 'S' for values ≥ 1100, and should never return 'SS', 'SS+', or 'S+'.

**Validates: Requirements 6.3, 6.4**

## Error Handling

- **Empty data arrays**: Line chart and class pyramid must handle empty arrays gracefully (display zeros/dashes, no JS errors)
- **Missing event properties**: Activity timeline helpers should handle events with missing `type`, `timestamp`, or `metadata` fields without throwing
- **No character selected**: Controller already handles this via `getEmptyDashboardData()` — analytics fields will be added there too
- **Invalid stat values**: Grade functions should handle negative numbers and very large numbers without error

## Testing Strategy

### Dual Testing Approach

- **Unit tests (Pest)**: Verify specific examples, edge cases, and controller data passing
- **Property tests (Pest with `pestphp/pest` datasets or a PBT library)**: Verify universal properties across generated inputs

Since this is a PHP + JS project, testing is split:

**PHP (Pest v4)**:

- Feature tests for DashboardController verifying analytics data is passed
- Unit tests for `Character::getStatGrade()` grade scale correctness
- Unit tests for `TurnCounter` default values
- Blade rendering tests for the malformed comment fix and grade closure

**JavaScript**:

- Unit tests for `lineChart()`, `classPyramid()`, `activityTimeline()` functions
- Property tests using fast-check (or similar JS PBT library) for the 6 correctness properties

### Property-Based Testing Configuration

- Minimum 100 iterations per property test
- Each property test must reference its design document property
- Tag format: **Feature: dashboard-fixes, Property {number}: {property_text}**
- Each correctness property is implemented by a single property-based test

### Test Organization

- PHP tests: `tests/Feature/DashboardControllerTest.php`, `tests/Unit/Models/CharacterGradeTest.php`, `tests/Unit/Components/TurnCounterTest.php`
- JS tests: `tests/js/components/line-chart.test.js`, `tests/js/components/class-pyramid.test.js`, `tests/js/components/activity-timeline.test.js`
