# PHPStan Pest Test Properties Solution

## Problem

PHPStan Level 9 analysis was reporting numerous "Access to an undefined property" errors for properties used in Pest
tests. These properties are dynamically assigned in `beforeEach()` hooks but PHPStan cannot detect them through static
analysis.

Example errors:

```text
Access to an undefined property PHPUnit\Framework\TestCase::$user.
Access to an undefined property PHPUnit\Framework\TestCase::$character.
Access to an undefined property PHPUnit\Framework\TestCase::$parser.
```text

## Root Cause

Pest tests use dynamic property assignment in `beforeEach()` hooks:

```php
beforeEach(function () {
    $this->user = User::factory()->create();
    $this->character = Character::factory()->create();
    $this->parser = new SomeParser();
});
```text

PHPStan performs static analysis and cannot detect these runtime property assignments, resulting in false positive
errors.

## Solution

### 1. Created PHPStan Stub File

Created `phpstan-stubs/pest-test-properties.stub` to document common test properties:

```php
<?php

namespace PHPUnit\Framework {
    /**
     * @property mixed $parser Parser instance used in various parsing tests
     * @property mixed $service Service instance used in service tests
     * @property mixed $detector Detector instance used in detection tests
     * // ... additional properties
     */
    abstract class TestCase
    {
    }
}
```

### 2. Updated phpstan.neon Configuration

Added ignore rules to suppress false positive errors for Pest test properties:

```neon
parameters:
  stubFiles:
    - phpstan-stubs/pest-test-properties.stub

  ignoreErrors:
    # Pest test properties set in beforeEach() hooks - these are dynamically assigned at runtime
    # Using wildcard to match all test properties since Pest uses dynamic property assignment
    - message: '#Access to an undefined property PHPUnit\\Framework\\TestCase::\$\w+\.#'
      path: tests/*
    
    # Laravel testing methods available through traits but not recognized by PHPStan
    - message: '#Call to an undefined method PHPUnit\\Framework\\TestCase::mock\(\)\.#'
      path: tests/*
```text

## 3. Updated Tests\TestCase

Added @property annotations to `tests/TestCase.php` to document common test properties:

```php
/**
 * @property mixed $parser Parser instance used in various parsing tests
 * @property mixed $service Service instance used in service tests
 * // ... additional properties
 * 
 * @method \Illuminate\Testing\TestResponse<\Illuminate\Http\Response> get(string $uri, array<string, string> $headers = [])
 * // ... existing method annotations
 */
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
}
```text

## Results

- **Before**: PHPStan reported numerous false positive errors for test properties
- **After**: All test property access errors are properly suppressed
- **Impact**: Cleaner PHPStan output focusing on actual code issues rather than Pest framework limitations

## Why This Approach?

1. **Stub File**: Documents the properties for reference and provides type hints where possible
2. **Ignore Rules**: Suppresses false positives since PHPStan cannot detect Pest's dynamic property assignment
3. **Wildcard Pattern**: Uses `\$\w+` to match all properties, avoiding the need to maintain a long list
4. **Path Restriction**: Only applies to `tests/*` to avoid suppressing legitimate errors in application code

## Alternative Approaches Considered

1. **Declaring properties in TestCase**: Would require listing all possible properties, which is impractical given the
variety across different test files
2. **Using @var annotations in each test**: Would add significant boilerplate to every test file
3. **Disabling property checks for tests**: Too broad and would miss legitimate errors

## Maintenance

When adding new test properties:

- No action needed - the wildcard pattern automatically covers new properties
- Optionally update the stub file documentation for reference
- Consider adding specific type hints in the stub file for commonly used properties

## Files Modified

1. `phpstan-stubs/pest-test-properties.stub` - Created
2. `phpstan.neon` - Updated with stubFiles and ignoreErrors
3. `tests/TestCase.php` - Added @property annotations for documentation
