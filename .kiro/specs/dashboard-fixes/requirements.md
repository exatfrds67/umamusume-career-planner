# Requirements Document

## Introduction

The dashboard page (`/dashboard`) has accumulated numerous bugs from previous development iterations. These include Alpine.js scope errors causing 9+ console errors, JavaScript function signature mismatches between Blade templates and JS files, a malformed Blade comment rendering raw syntax on the page, missing controller data for the analytics section, an incorrect grade scale, a wrong turn counter range, a non-functional UI button, and unused code. This spec addresses all identified issues to restore the dashboard to a correct, error-free state.

## Glossary

- **Dashboard_Controller**: The `DashboardController` PHP class that prepares and passes data to the dashboard view
- **Line_Chart_Component**: The `line-chart.blade.php` Blade component and its companion `line-chart.js` Alpine.js function
- **Class_Pyramid_Component**: The `class-pyramid.blade.php` Blade component and its companion `class-pyramid.js` Alpine.js function
- **Activity_Timeline_Component**: The `activity-timeline.blade.php` Blade component and its companion `activity-timeline.js` Alpine.js function
- **Stats_Snapshot_Component**: The `stats-snapshot.blade.php` Blade component displaying character stat values and grades
- **Turn_Counter_Component**: The `turn-counter.blade.php` Blade component and its `TurnCounter.php` class displaying career turn progress
- **Mood_Energy_Widget**: The `mood-energy-widget.blade.php` Blade component displaying mood and energy status
- **Alpine_Scope**: The DOM subtree within which Alpine.js `x-data` properties are accessible via `x-text`, `x-bind`, etc.
- **EARS_Pattern**: Easy Approach to Requirements Syntax, a structured pattern for writing requirements

## Requirements

### Requirement 1: Fix Line Chart Alpine.js Scope and JS Function

**User Story:** As a developer, I want the line chart component to have correct Alpine.js scoping and a JS function that accepts the passed parameters, so that the stat progression chart renders without console errors.

#### Acceptance Criteria

1. WHEN the Line_Chart_Component renders, THE Line_Chart_Component SHALL place the `x-data` directive on a wrapper element that encloses both the canvas and the Data Summary Stats section
2. WHEN the Line_Chart_Component is initialized with data, labels, colors, animated, and responsive parameters, THE `lineChart` JS function SHALL accept those five parameters and use them to configure the chart
3. WHEN the Line_Chart_Component has data points, THE Line_Chart_Component SHALL compute and display `lastDataPoint`, `averageValue`, and `maxValue` from the provided data without Alpine.js reference errors
4. WHEN the Line_Chart_Component receives an empty data array, THE Line_Chart_Component SHALL display zero or dash values for the summary stats

### Requirement 2: Fix Class Pyramid Alpine.js Scope and JS Function

**User Story:** As a developer, I want the class pyramid component to have correct Alpine.js scoping and a JS function that accepts grade data, so that the fan distribution pyramid renders without console errors.

#### Acceptance Criteria

1. WHEN the Class_Pyramid_Component renders, THE Class_Pyramid_Component SHALL place the `x-data` directive on a wrapper element that encloses both the Total Fanbase summary and the pyramid visualization
2. WHEN the Class_Pyramid_Component is initialized with a grades array, THE `classPyramid` JS function SHALL accept that grades parameter and expose `sortedGrades`, `totalFans`, `hoveredLayer`, and `maxFans` properties
3. WHEN the Class_Pyramid_Component receives grade data, THE Class_Pyramid_Component SHALL compute `totalFans` as the sum of all grade fan counts and `sortedGrades` as the grades sorted by fan count descending

### Requirement 3: Fix Class Pyramid Malformed Blade Comment

**User Story:** As a user, I want the class pyramid component to render cleanly without visible raw Blade syntax, so that the dashboard looks correct.

#### Acceptance Criteria

1. THE Class_Pyramid_Component SHALL have properly formed Blade comments with matching opening `{{--` and closing `--}}` delimiters
2. WHEN the Class_Pyramid_Component renders, THE Class_Pyramid_Component SHALL display the legend section as styled HTML without any visible Blade comment syntax

### Requirement 4: Fix Activity Timeline Property Names and Data Source

**User Story:** As a developer, I want the activity timeline component to use consistent property names between Blade and JS and receive data from the controller rather than a non-existent API, so that the timeline renders without errors.

#### Acceptance Criteria

1. WHEN the Activity_Timeline_Component is initialized with events data, THE `activityTimeline` JS function SHALL accept that events parameter and expose `events`, `displayedEvents`, and helper methods matching the Blade template references
2. THE Activity_Timeline_Component SHALL NOT fetch data from `/api/activities` and SHALL instead use the events data passed from the Blade template
3. WHEN the Activity_Timeline_Component has events, THE Activity_Timeline_Component SHALL provide `getEventColor`, `getEventIcon`, `getEventBadgeStyle`, `formatEventType`, `getTimeAgo`, `formatDate`, and `formatMetadata` helper methods
4. WHEN the Activity_Timeline_Component `loadMore` is called, THE Activity_Timeline_Component SHALL reveal additional events from the pre-loaded events array

### Requirement 5: Pass Analytics Data from Dashboard Controller

**User Story:** As a user, I want the analytics section of the dashboard to display real character data for stat progression, race grades, and recent activity, so that the charts and timeline are meaningful.

#### Acceptance Criteria

1. WHEN a character is selected, THE Dashboard_Controller SHALL pass `statProgression` data derived from the character's stat history to the dashboard view
2. WHEN a character is selected, THE Dashboard_Controller SHALL pass `progressionLabels` corresponding to the stat progression data points to the dashboard view
3. WHEN a character is selected, THE Dashboard_Controller SHALL pass `raceGrades` data derived from the character's race schedule or results to the dashboard view
4. WHEN a character is selected, THE Dashboard_Controller SHALL pass `recentActivity` data derived from the character's recent actions to the dashboard view
5. WHEN no character is selected, THE Dashboard_Controller SHALL pass empty arrays for `statProgression`, `progressionLabels`, `raceGrades`, and `recentActivity`

### Requirement 6: Fix Grade Scale to Match Game Accuracy

**User Story:** As a player, I want the grade scale to cap at S (not SS) to match the actual Umamusume game, so that stat grades are accurate.

#### Acceptance Criteria

1. THE Stats_Snapshot_Component `$getGrade` function SHALL return 'S' as the maximum grade for stat values of 1100 or above
2. THE Stats_Snapshot_Component `$getGrade` function SHALL NOT return 'SS' for any stat value
3. THE Character model `getStatGrade` method SHALL return 'S' as the maximum grade for stat values of 1100 or above
4. THE Character model `getStatGrade` method SHALL NOT return 'SS', 'SS+', or 'S+' for any stat value

### Requirement 7: Fix Turn Counter Maximum Turn Value

**User Story:** As a player, I want the turn counter to show the correct maximum of 70 turns (not 78), so that career progress is accurately represented.

#### Acceptance Criteria

1. THE Turn_Counter_Component class SHALL default the `$total` parameter to 70
2. WHEN the Turn_Counter_Component renders the progress bar stage labels, THE Turn_Counter_Component SHALL display "Senior (49-70)" instead of "Senior (49-78)"
3. THE Turn_Counter_Component class docblock SHALL reference a 70-turn career instead of 78-turn

### Requirement 8: Fix Non-Functional Recovery Options Button

**User Story:** As a user, I want the Recovery Options button on the mood/energy widget to either perform an action or be removed, so that the UI does not contain dead interactive elements.

#### Acceptance Criteria

1. WHEN the Mood_Energy_Widget renders, THE Mood_Energy_Widget SHALL either remove the Recovery Options button or wire it to a meaningful action
2. IF the Recovery Options button is retained, THEN THE Mood_Energy_Widget SHALL navigate the user to the character detail page or open a recovery modal

### Requirement 9: Remove Unused Variables in Dashboard Controller

**User Story:** As a developer, I want the dashboard controller to be free of unused variables, so that the code is clean and maintainable.

#### Acceptance Criteria

1. THE Dashboard_Controller `getTrainingSuggestions` method SHALL NOT declare unused local variables `$stats` and `$priorities`
