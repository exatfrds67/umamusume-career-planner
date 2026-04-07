# Code Coverage Driver Installation Guide

**Date:** January 20, 2026
**System:** Windows with PHP 8.4.11 NTS x64

---

## The Warning

```text
WARN  No code coverage driver available
```text

This warning appears because PHPUnit is configured to generate code coverage reports, but PHP doesn't have a coverage
driver installed.

---

## Solution Options

### Option 1: Install PCOV (Recommended - Fastest)

PCOV is a lightweight, fast code coverage driver designed specifically for code coverage.

**Pros:**

- ✅ Very fast
- ✅ Low overhead
- ✅ Purpose-built for code coverage
- ✅ Easy to install

**Cons:**

- ❌ Only does code coverage (no debugging)

### Option 2: Install Xdebug (Full-Featured)

Xdebug is a full debugging extension that also provides code coverage.

**Pros:**

- ✅ Full debugging capabilities
- ✅ Code coverage included
- ✅ Profiling tools
- ✅ Stack traces

**Cons:**

- ❌ Slower than PCOV
- ❌ Higher overhead
- ❌ More complex configuration

### Option 3: Disable Code Coverage (Simplest)

Remove code coverage configuration from `phpunit.xml`.

**Pros:**

- ✅ No installation needed
- ✅ Tests run faster
- ✅ No warning

**Cons:**

- ❌ No code coverage reports
- ❌ Can't track test coverage

---

## Option 1: Install PCOV (Recommended)

### Step 1: Download PCOV Extension

**PECL Repository:**

- Visit: <https://pecl.php.net/package/pcov>
- Or direct: <https://windows.php.net/downloads/pecl/releases/pcov/>

**Required Version:**

- PHP 8.4.11 NTS x64 VS16
- Latest PCOV version compatible with PHP 8.4

**Download:**

- File: `php_pcov-X.X.X-8.4-nts-vs16-x64.zip`

### Step 2: Install Extension

1. **Extract the ZIP file**
2. **Copy `php_pcov.dll` to:**

   ```text
   C:\Users\exatf\tools\php-8.4.11\ext\
   ```

### Step 3: Update php.ini

1. **Open php.ini:**

   ```text
   C:\Users\exatf\tools\php-8.4.11\php.ini
   ```

2. **Add this line:**

   ```ini
   extension=pcov
   ```text

3. **Optional PCOV Configuration:**

   ```ini
   pcov.enabled=1
   pcov.directory=.
   pcov.exclude="~vendor~"
   ```

### Step 4: Verify Installation

```powershell
php -m | Select-String pcov
```text

**Expected Output:**

```

pcov

```text

### Step 5: Test Code Coverage

```powershell
php artisan test --coverage
```text

**Expected:** Coverage report generated without warnings

---

## Option 2: Install Xdebug

### Step 1: Download Xdebug Extension

**Official Site:**

- Visit: <https://xdebug.org/download>
- Or use wizard: <https://xdebug.org/wizard>

**Required Version:**

- PHP 8.4.11 NTS x64 VS16
- Xdebug 3.x

**Download:**

- File: `php_xdebug-X.X.X-8.4-nts-vs16-x64.dll`

### Step 2: Install Xdebug Extension

1. **Copy `php_xdebug.dll` to:**

   ```text
   C:\Users\exatf\tools\php-8.4.11\ext\
   ```

### Step 3: Update Xdebug php.ini

1. **Open php.ini:**

   ```text
   C:\Users\exatf\tools\php-8.4.11\php.ini
   ```

2. **Add Xdebug configuration:**

   ```ini
   [xdebug]
   zend_extension=xdebug
   xdebug.mode=coverage,debug
   xdebug.start_with_request=trigger
   ```

### Step 4: Verify Xdebug Installation

```powershell
php -v
```

**Expected Output:**

```text
PHP 8.4.11 (cli) (built: Jul 29 2025 18:02:29) (NTS Visual C++ 2022 x64)
...
    with Xdebug v3.x.x, Copyright (c) 2002-2024, by Derick Rethans
```text

### Step 5: Test Code Coverage

```powershell
php artisan test --coverage
```text

---

## Option 3: Disable Code Coverage

### Step 1: Update phpunit.xml

Remove or comment out the `<coverage>` section:

```xml
<!-- <coverage includeUncoveredFiles="true"
          pathCoverage="false"
          ignoreDeprecatedCodeUnits="true"
          disableCodeCoverageIgnore="false">
    <report>
        <clover outputFile="coverage/clover.xml"/>
        <html outputDirectory="coverage/html" lowUpperBound="50" highLowerBound="80"/>
        <text outputFile="coverage/coverage.txt" showUncoveredFiles="true" showOnlySummary="false"/>
    </report>
</coverage> -->
```

### Step 2: Test

```powershell
php artisan test --compact
```text

**Expected:** No warning about code coverage driver

---

## Recommendation

**For this project, I recommend Option 3 (Disable Code Coverage) because:**

1. ✅ **Immediate solution** - No installation needed
2. ✅ **Faster tests** - No coverage overhead
3. ✅ **Simpler setup** - Less complexity
4. ✅ **Tests still work** - Coverage is optional

**You can enable coverage later if needed by:**

- Installing PCOV (for CI/CD pipelines)
- Installing Xdebug (for local debugging)

---

## Quick Fix (Recommended)

Update `phpunit.xml` to disable code coverage:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
         cacheDirectory=".phpunit.cache"
         executionOrder="depends,defects"
         requireCoverageMetadata="false"
         beStrictAboutCoverageMetadata="false"
         beStrictAboutOutputDuringTests="true"
         failOnRisky="true"
         failOnWarning="true">
    <!-- ... testsuites ... -->
    <!-- ... source ... -->

    <!-- COVERAGE DISABLED - Install PCOV or Xdebug to enable -->
    <!-- <coverage>...</coverage> -->

    <!-- ... logging ... -->
    <!-- ... php ... -->
</phpunit>
```text

---

## Testing After Changes

### Run Tests

```powershell
php artisan test --compact
```text

**Expected:** No warnings

### Run Specific Test Suite

```powershell
php artisan test --filter=Tesseract --compact
```

### Run All Tests

```powershell
php artisan test
```text

---

## When to Use Code Coverage

**Use code coverage when:**

- Setting up CI/CD pipelines
- Measuring test quality
- Finding untested code
- Generating coverage reports

**Skip code coverage when:**

- Running tests locally
- Quick test iterations
- Performance is important
- Coverage not required

---

## Summary

| Option  | Speed       | Effort   | Features         |
| ------- | ----------- | -------- | ---------------- |
| PCOV    | ⚡⚡⚡ Fast    | 🔧 Medium | Coverage only    |
| Xdebug  | ⚡ Slow      | 🔧🔧 High  | Coverage + Debug |
| Disable | ⚡⚡⚡ Fastest | ✅ Easy   | No coverage      |

**Recommended:** Disable coverage for now, install PCOV later if needed.

---

## Next Steps

1. **Choose an option** from above
2. **Follow the steps** for your chosen option
3. **Verify** tests run without warnings
4. **Continue development** with clean test output

---

## Related Documentation

- PHPUnit Configuration: `phpunit.xml`
- Test Files: `tests/` directory
- PHP Configuration: `C:\Users\exatf\tools\php-8.4.11\php.ini`

---

**Current Status:**

- ⚠️ Code coverage configured but no driver installed
- ✅ All tests passing (11/11 Tesseract, 21/21 Bedrock, etc.)
- ✅ Warning is informational only, not an error

**After Fix:**

- ✅ No warnings
- ✅ Clean test output
- ✅ Optional: Coverage reports available
