# Requirements Document: Character Stats Display Fix

## Introduction

This specification addresses critical data type issues in the Character Stats Display system that prevent the Current Statistics and Stats Overview components from rendering correctly. The root cause is that stat values are being stored as strings in the `current_stats` JSON field instead of integers, causing component rendering failures and data type mismatches throughout the application.

## Glossary

- **Character**: An Umamusume trainee entity with stats, aptitudes, and career progression data
- **Current_Stats**: JSON field storing five stat values (speed, stamina, power, guts, wit)
- **Stat_Bar**: UI component displaying individual stat progress with visual indicators
- **Stat_Radar_Chart**: UI component displaying all five stats in a pentagon radar visualization
- **Soft_Cap**: The 1200 stat value threshold where diminishing returns begin (50% effectiveness)
- **JSON_Casting**: Laravel's automatic conversion of JSON database fields to PHP arrays
- **Type_Coercion**: Automatic conversion between data types (string to integer)
- **Component_Props**: Data passed to Blade/Livewire components with expected types

## Requirements

### Requirement 1: Data Storage Integrity

**User Story:** As a developer, I want character stats to be stored as integers in the database, so that data type consistency is maintained throughout the application.

#### Acceptance Criteria

1. WHEN a character is created or updated, THE System SHALL store stat values as integers in the `current_stats` JSON field
2. WHEN stat values are retrieved from the database, THE System SHALL ensure they are cast to integers
3. WHEN invalid stat data is submitted (non-numeric), THE System SHALL reject it with a validation error
4. WHEN existing characters with string stats are loaded, THE System SHALL normalize them to integers
5. THE System SHALL validate that all stat values are between 0 and 2000

### Requirement 2: Model Data Normalization

**User Story:** As a developer, I want the Character model to automatically normalize stat data, so that components always receive correctly typed values.

#### Acceptance Criteria

1. WHEN the `current_stats` attribute is accessed, THE Character_Model SHALL return an array with integer values
2. WHEN the `current_stats` attribute is set, THE Character_Model SHALL convert string values to integers
3. WHEN the `getStat()` method is called, THE Character_Model SHALL return an integer value
4. THE Character_Model SHALL provide a mutator that normalizes stats before database storage
5. THE Character_Model SHALL provide an accessor that normalizes stats after database retrieval

### Requirement 3: Form Input Validation

**User Story:** As a user, I want the character creation and edit forms to only accept valid numeric stat values, so that data integrity is maintained from the point of entry.

#### Acceptance Criteria

1. WHEN submitting the character creation form, THE System SHALL validate that all stat values are integers
2. WHEN submitting the character edit form, THE System SHALL validate that all stat values are integers
3. WHEN a non-numeric stat value is submitted, THE System SHALL return a clear validation error message
4. WHEN stat values are outside the valid range (0-2000), THE System SHALL reject them with an error
5. THE System SHALL strip any non-numeric characters from stat inputs before validation

### Requirement 4: Component Rendering Correctness

**User Story:** As a user, I want the Current Statistics section to display all five stat bars correctly, so that I can see my character's progress at a glance.

#### Acceptance Criteria

1. WHEN viewing a character detail page, THE Stat_Bar_Component SHALL render all five stats (speed, stamina, power, guts, wit)
2. WHEN a stat value is displayed, THE Stat_Bar_Component SHALL show the numeric value without type errors
3. WHEN a stat exceeds 1200, THE Stat_Bar_Component SHALL display the soft cap indicator
4. WHEN a target stat is set in goals, THE Stat_Bar_Component SHALL display the target marker
5. THE Stat_Bar_Component SHALL calculate percentages correctly using integer values

### Requirement 5: Radar Chart Visualization

**User Story:** As a user, I want the Stats Overview radar chart to render properly, so that I can visualize my character's stat distribution.

#### Acceptance Criteria

1. WHEN viewing a character detail page, THE Stat_Radar_Chart_Component SHALL render a pentagon with five stat axes
2. WHEN stat values are passed to the component, THE Stat_Radar_Chart_Component SHALL accept integer values
3. WHEN calculating polygon points, THE Stat_Radar_Chart_Component SHALL use integer math without type errors
4. WHEN displaying stat labels, THE Stat_Radar_Chart_Component SHALL show correct numeric values
5. THE Stat_Radar_Chart_Component SHALL normalize stat values to percentages for visualization

### Requirement 6: Data Migration for Existing Records

**User Story:** As a system administrator, I want existing characters with string stats to be automatically fixed, so that all users benefit from the fix without manual intervention.

#### Acceptance Criteria

1. WHEN the migration runs, THE System SHALL identify all characters with string stat values
2. WHEN processing each character, THE System SHALL convert string stats to integers
3. WHEN a stat value cannot be converted, THE System SHALL log a warning and set it to 0
4. WHEN the migration completes, THE System SHALL report the number of records updated
5. THE Migration SHALL be reversible in case of issues

### Requirement 7: Console Error Prevention

**User Story:** As a user, I want the character detail page to load without JavaScript errors, so that I have a smooth browsing experience.

#### Acceptance Criteria

1. WHEN the character detail page loads, THE System SHALL not produce any console errors related to stat display
2. WHEN components receive stat data, THE System SHALL not produce type mismatch warnings
3. WHEN calculations are performed on stats, THE System SHALL not produce NaN (Not a Number) errors
4. WHEN the page renders, THE System SHALL not show undefined or null values in stat displays
5. THE System SHALL handle edge cases (missing stats, null values) gracefully

### Requirement 8: Backward Compatibility

**User Story:** As a developer, I want the fix to be backward compatible with existing code, so that other parts of the application continue to work without modification.

#### Acceptance Criteria

1. WHEN existing code accesses `current_stats`, THE System SHALL return the expected array structure
2. WHEN existing code calls `getStat()`, THE System SHALL return integer values as before
3. WHEN existing tests run, THE System SHALL pass all existing test cases
4. WHEN API endpoints return character data, THE System SHALL maintain the same JSON structure
5. THE System SHALL not break any existing Livewire components or Blade views

### Requirement 9: Testing Coverage

**User Story:** As a developer, I want comprehensive tests for stat data handling, so that regressions are prevented in the future.

#### Acceptance Criteria

1. WHEN running unit tests, THE System SHALL verify stat normalization in the Character model
2. WHEN running feature tests, THE System SHALL verify character creation with integer stats
3. WHEN running feature tests, THE System SHALL verify character updates with integer stats
4. WHEN running component tests, THE System SHALL verify Stat_Bar renders with integer props
5. WHEN running component tests, THE System SHALL verify Stat_Radar_Chart renders with integer props

### Requirement 10: Documentation Updates

**User Story:** As a developer, I want clear documentation on stat data handling, so that future development maintains data type consistency.

#### Acceptance Criteria

1. THE System SHALL document the expected data types for `current_stats` in the Character model
2. THE System SHALL document the stat validation rules in the form request classes
3. THE System SHALL document the component prop types in the Blade component classes
4. THE System SHALL update the database schema documentation with stat field types
5. THE System SHALL provide inline code comments explaining the normalization logic

---

## Document Information

**Document Type**: Requirements Document  
**Version**: 1.0.0  
**Date**: January 29, 2026  
**Status**: Draft  
**Feature**: Character Stats Display Fix  
**Related**: PRD-001_Character_Management.md, SPEC-001_Character_Management_Technical.md, DBD_Database_Documentation.md
