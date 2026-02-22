# Larastan Level 9 Fix Plan

## Summary

- **Total Errors**: 10,222
- **Analysis Date**: January 23, 2026

## Error Categories

Based on the initial analysis, the main error types are:

1. **property.notFound** - Access to undefined properties (mostly in test files using `$this->property`)
2. **missingType.iterableValue** - Array parameters/returns without type specifications
3. **argument.type** - Type mismatches in function/method calls
4. **cast.string/cast.int** - Invalid type casting
5. **offsetAccess.nonOffsetAccessible** - Accessing array offsets on mixed types
6. **property.nonObject** - Accessing properties on potentially null objects
7. **foreach.nonIterable** - Using foreach on non-iterable types
8. **return.type** - Return type mismatches
9. **property.onlyWritten** - Properties that are written but never read

## Fix Strategy

### Phase 1: Test File Property Issues (Highest Volume)

Most errors are in test files where properties like `$this->parser`, `$this->service`, etc. are accessed but not
defined. These need PHPDoc `@property` annotations or stub files.

**Files to Fix**:

- tests/Unit/Services/MCP/*.php
- tests/Unit/Services/OCR/Parsers/*.php
- tests/Unit/Services/*.php

### Phase 2: Missing Array Type Specifications

Add proper PHPDoc array type hints for parameters and return types.

**Files to Fix**:

- app/Console/Commands/*.php
- app/Http/Controllers/Api/*.php
- app/Services/*.php

### Phase 3: Type Casting and Mixed Type Issues

Fix invalid casts and mixed type handling.

**Files to Fix**:

- app/Helpers/*.php
- app/Http/Controllers/Api/*.php
- app/Services/*.php

### Phase 4: Null Safety Issues

Add null checks and proper type guards.

**Files to Fix**:

- app/Http/Controllers/Api/*.php
- app/Services/*.php

## Execution Plan

1. Create PHPStan stub file for test base classes
2. Fix test files in batches (by directory)
3. Fix application code by error type
4. Run Larastan after each batch to verify progress
5. Final verification run

## Notes

- Do not create spec files (as per user request)
- Use subagents for systematic fixes
- Prioritize test files first (largest volume)
- Focus on type safety improvements
