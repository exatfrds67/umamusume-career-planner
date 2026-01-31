# Implementation Plan: Character Stats Display Fix

## Overview

This implementation plan breaks down the Character Stats Display Fix into discrete, testable tasks. The approach follows a layered strategy: Model → Validation → Migration → Components → Testing. Each task builds on previous work and includes validation through automated tests.

## Tasks

- [x] 1. Enhance Character Model with Type Normalization
  - [x] 1.1 Add accessor for current_stats attribute
    - Implement `getCurrentStatsAttribute()` method that casts all stat values to integers
    - Ensure accessor returns array with exactly five keys: speed, stamina, power, guts, wit
    - Handle missing stats by defaulting to 0
    - _Requirements: 1.2, 2.1, 8.1_
  
  - [x] 1.2 Add mutator for current_stats attribute
    - Implement `setCurrentStatsAttribute()` method that normalizes input to integers
    - Convert string values to integers before JSON encoding
    - Validate input is an array before processing
    - _Requirements: 1.1, 2.2_
  
  - [x] 1.3 Update getStat() method for type safety
    - Ensure method always returns integer type
    - Add explicit type casting in return statement
    - Handle missing stat keys gracefully
    - _Requirements: 2.3, 8.2_
  
  - [x]* 1.4 Write unit tests for Character model normalization
    - **Property 1: Database Storage Type Consistency** - Test that stats are stored as integers
    - **Property 4: Backward Compatibility Normalization** - Test string stats are normalized on access
    - **Property 5: Mutator Type Conversion** - Test mutator converts strings to integers
    - **Property 6: getStat() Return Type** - Test getStat() returns integers for all stat types
    - Test edge cases: missing stats, null values, invalid keys
    - _Requirements: 9.1_

- [x] 2. Enhance Form Request Validation
  - [x] 2.1 Update StoreCharacterRequest validation rules
    - Change max validation from 1200 to 2000 for all stats
    - Ensure 'integer' rule is present for all stat fields
    - Add custom error messages for integer type validation
    - _Requirements: 1.5, 3.1_
  
  - [x] 2.2 Update UpdateCharacterRequest validation rules
    - Change max validation from 1200 to 2000 for all stats
    - Ensure 'integer' rule is present for all stat fields
    - Add custom error messages for integer type validation
    - _Requirements: 1.5, 3.2_
  
  - [x] 2.3 Add input sanitization to Form Requests
    - Implement `prepareForValidation()` method in both Form Requests
    - Strip non-numeric characters from stat inputs using regex
    - Convert sanitized values to integers before validation
    - _Requirements: 3.5_
  
  - [x]* 2.4 Write feature tests for form validation
    - **Property 2: Stat Value Boundary Validation** - Test range enforcement (0-2000)
    - **Property 3: Invalid Input Rejection** - Test non-numeric inputs are rejected
    - **Property 7: Form Validation Type Enforcement** - Test integer enforcement
    - **Property 8: Input Sanitization** - Test non-numeric character stripping
    - Test validation error messages are clear and helpful
    - _Requirements: 9.2, 9.3_

- [x] 3. Create Database Migration for Existing Records
  - [x] 3.1 Generate migration file
    - Create migration: `normalize_character_stats_to_integers`
    - Add descriptive comment explaining purpose
    - _Requirements: 6.1_
  
  - [x] 3.2 Implement up() method
    - Query all characters from ucp_characters table
    - Decode current_stats JSON for each character
    - Check if any stat value is a string type
    - Normalize string stats to integers
    - Handle unconvertible values by setting to 0 and logging warning
    - Update database with normalized stats
    - Log summary: records updated, errors encountered
    - _Requirements: 6.1, 6.2, 6.3, 6.4_
  
  - [x] 3.3 Implement down() method
    - Add log message indicating no reversal needed
    - Integer stats are valid in both states (no-op rollback)
    - _Requirements: 6.5_
  
  - [x]* 3.4 Write migration tests
    - **Property 14: Migration String-to-Integer Conversion** - Test string-to-integer conversion
    - Test migration handles unconvertible values gracefully
    - Test migration reports correct count of updated records
    - Test migration can be rolled back without errors
    - _Requirements: 9.2_

- [x] 4. Checkpoint - Verify Model and Validation Layer
  - Ensure all tests pass for Character model and Form Requests
  - Run migration on test database to verify it works
  - Ask the user if questions arise

- [x] 5. Create Blade Component Classes
  - [x] 5.1 Create StatBar component class
    - Create `app/View/Components/StatBar.php`
    - Add typed properties for all component parameters
    - Cast string props to integers in constructor
    - Implement helper methods: getPercentage(), isAboveSoftCap(), getStatColor(), etc.
    - _Requirements: 4.2, 4.5_
  
  - [x] 5.2 Create StatRadarChart component class
    - Create `app/View/Components/StatRadarChart.php`
    - Add typed property for stats array
    - Normalize all stat values to integers in constructor
    - Implement helper methods: calculatePoints(), getStatPercentages(), etc.
    - _Requirements: 5.2, 5.3, 5.5_
  
  - [x]* 5.3 Write component unit tests
    - **Property 9: Stat Bar Percentage Calculation** - Test percentage calculation
    - **Property 10: Soft Cap Indicator Display** - Test soft cap indicator for stats > 1200
    - **Property 11: Target Marker Display** - Test target marker display
    - **Property 12: Radar Chart Point Calculation** - Test polygon point calculation
    - **Property 13: Radar Chart Percentage Normalization** - Test percentage normalization
    - **Property 15: Missing Stat Handling** - Test handling of missing/null stats
    - Test components accept both integer and string props
    - _Requirements: 9.4, 9.5_

- [x] 6. Update Blade Component Views
  - [x] 6.1 Update stat-bar.blade.php view
    - Update component to use class-based component
    - Ensure all prop references use typed properties
    - Remove inline type casting (handled by component class)
    - Test rendering with various stat values
    - _Requirements: 4.1, 4.3, 4.4_
  
  - [x] 6.2 Update stat-radar-chart.blade.php view
    - Update component to use class-based component
    - Ensure all prop references use typed properties
    - Remove inline type casting (handled by component class)
    - Test rendering with various stat combinations
    - _Requirements: 5.1, 5.4_
  
  - [x]* 6.3 Write component rendering tests
    - Test StatBar renders all five stats correctly
    - Test StatRadarChart renders pentagon with five axes
    - Test components display numeric values without errors
    - Test components handle edge cases (0 stats, max stats)
    - _Requirements: 9.4, 9.5_

- [x] 7. Update Character Controller
  - [x] 7.1 Verify store() method uses validated data
    - Ensure stats are passed directly from validated request
    - No manual type conversion needed (handled by model mutator)
    - _Requirements: 1.1, 8.5_
  
  - [x] 7.2 Verify update() method uses validated data
    - Ensure stats are passed directly from validated request
    - No manual type conversion needed (handled by model mutator)
    - _Requirements: 1.1, 8.5_
  
  - [x]* 7.3 Write integration tests for controller actions
    - **Property 1: Database Storage Type Consistency** - Test stats stored as integers
    - Test character creation stores integer stats
    - Test character update stores integer stats
    - Test API responses maintain correct JSON structure
    - _Requirements: 9.2, 9.3_

- [x] 8. Checkpoint - Verify Component Layer
  - Ensure all component tests pass
  - Manually test character detail page in browser
  - Verify no console errors appear
  - Ask the user if questions arise

- [x] 9. Run Data Migration
  - [x] 9.1 Backup production database (if applicable)
    - Create database backup before running migration
    - Document backup location and timestamp
    - _Requirements: 6.5_
  
  - [x] 9.2 Run migration on staging/development
    - Execute: `php artisan migrate`
    - Review migration logs for errors
    - Verify updated record count matches expectations
    - _Requirements: 6.4_
  
  - [x] 9.3 Verify migrated data
    - Query sample characters and check stat types
    - Verify all stats are integers in database
    - Check application renders correctly with migrated data
    - _Requirements: 6.2_

- [x] 10. Add Documentation
  - [x] 10.1 Document Character model stat handling
    - Add PHPDoc comments to accessor/mutator methods
    - Document expected data types in class docblock
    - Add inline comments explaining normalization logic
    - _Requirements: 10.1, 10.5_
  
  - [x] 10.2 Document Form Request validation rules
    - Add comments explaining stat validation rules
    - Document valid range (0-2000) in class docblock
    - Add examples of valid/invalid inputs
    - _Requirements: 10.2_
  
  - [x] 10.3 Document Blade component prop types
    - Add PHPDoc comments to component class properties
    - Document expected prop types in component docblock
    - Add usage examples in component class comments
    - _Requirements: 10.3_
  
  - [x] 10.4 Update database schema documentation
    - Update DBD with current_stats field type information
    - Document expected JSON structure with integer values
    - Add migration notes to changelog
    - _Requirements: 10.4_

- [x] 11. Write Property-Based Tests
  - [ ]* 11.1 Create property test suite
    - Create `tests/Property/CharacterStatsPropertyTest.php`
    - Configure minimum 100 iterations per property test
    - Tag each test with feature name and property number
    - _Requirements: 9.1, 9.2_
  
  - [ ]* 11.2 Implement property tests for model layer
    - **Property 1: Database Storage Type Consistency** - Test any character operation stores integers
    - **Property 4: Backward Compatibility Normalization** - Test any string stat is normalized
    - **Property 5: Mutator Type Conversion** - Test any stat assignment converts to integer
    - **Property 6: getStat() Return Type** - Test any stat name returns integer
    - _Requirements: 9.1_
  
  - [ ]* 11.3 Implement property tests for validation layer
    - **Property 2: Stat Value Boundary Validation** - Test any value outside 0-2000 is rejected
    - **Property 3: Invalid Input Rejection** - Test any non-numeric input is rejected
    - **Property 7: Form Validation Type Enforcement** - Test any form submission enforces integers
    - **Property 8: Input Sanitization** - Test any input with non-numeric chars is sanitized
    - _Requirements: 9.2, 9.3_
  
  - [ ]* 11.4 Implement property tests for component layer
    - **Property 9: Stat Bar Percentage Calculation** - Test any stat/max combination calculates correctly
    - **Property 12: Radar Chart Point Calculation** - Test any stat set calculates points correctly
    - **Property 13: Radar Chart Percentage Normalization** - Test any stat normalizes to percentage
    - **Property 15: Missing Stat Handling** - Test any missing stat defaults to 0
    - _Requirements: 9.4, 9.5_

- [x] 12. Final Checkpoint - Complete System Verification
  - Run full test suite: `php artisan test --compact`
  - Run property tests with 100+ iterations
  - Verify all tests pass (unit, feature, property)
  - Manually test character creation, editing, and viewing
  - Verify no console errors in browser
  - Check database contains integer stats for all characters
  - Ensure all tests pass, ask the user if questions arise

- [ ] 13. Manual Radar Chart Verification
  - [ ] 13.1 Navigate to test character page
    - URL: <http://127.0.0.1:8000/characters/162>
    - Ensure logged in to application
    - Frontend assets built: `npm run build` ✅
    - _Requirements: 5.1, 5.4_
  
  - [ ] 13.2 Verify radar chart rendering
    - Pentagon shape clearly visible
    - Stats distributed across pentagon (not clustered)
    - Grid labels visible (200, 400, 600, 800, 1000)
    - Legend shows all 5 stats without cutoff
    - No console errors in browser DevTools
    - _Requirements: 5.2, 5.3, 5.5_
  
  - [ ] 13.3 Test responsive behavior
    - Test on mobile viewport (320px-768px)
    - Test on tablet viewport (768px-1024px)
    - Test on desktop viewport (1024px+)
    - Verify chart scales appropriately
    - _Requirements: 5.4_
  
  - [ ] 13.4 Test dark mode
    - Toggle dark mode
    - Verify colors and contrast
    - Verify grid labels are visible
    - Verify legend is readable
    - _Requirements: 5.4_
  
  - [ ] 13.5 Document verification results
    - Take screenshots of working radar chart
    - Note any issues or improvements needed
    - Update verification guide with results
    - _Requirements: 10.5_

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties
- Unit tests validate specific examples and edge cases
- Migration should be tested thoroughly before production deployment
- All code changes should follow PSR-12 and Laravel conventions
- Run `vendor/bin/pint --dirty` after each code change to ensure formatting

## Testing Commands

```bash
# Run all tests
php artisan test --compact

# Run specific test file
php artisan test --compact tests/Unit/Models/CharacterTest.php

# Run tests with filter
php artisan test --compact --filter="normalizes string stats"

# Run property tests only
php artisan test --compact tests/Property/

# Check code formatting
vendor/bin/pint --dirty

# Run static analysis
vendor/bin/phpstan analyse
```

## Rollback Plan

If issues arise after deployment:

1. **Immediate**: Rollback migration using `php artisan migrate:rollback`
2. **Code**: Revert model accessor/mutator changes
3. **Validation**: Revert Form Request changes
4. **Components**: Revert component class changes
5. **Verify**: Run test suite to ensure system stability
6. **Investigate**: Review logs to identify root cause
7. **Fix**: Address issues and redeploy with additional tests

---

## Document Information

**Document Type**: Implementation Plan  
**Version**: 1.0.0  
**Date**: January 29, 2026  
**Status**: Draft  
**Feature**: Character Stats Display Fix  
**Related**: requirements.md, design.md
