# Browser Test Suites Quick Reference

## Quick Start Commands

### 1. Page Traversal (All Routes)

```bash
# Full traversal with report
php artisan test:traversal --open-report

# Specific scope
php artisan test:traversal --scope=public
php artisan test:traversal --scope=auth
php artisan test:traversal --scope=admin
```

**Runtime:** 5-10 minutes | **Output:** HTML report in `storage/app/test-reports/`

### 2. Tab Navigation

```bash
# All tab tests
php artisan test tests/Browser/TabNavigationTest.php

# Keyboard navigation only
php artisan test --group=tabs --group=keyboard

# Accessibility verification
php artisan test --group=tabs --group=accessibility
```

**Runtime:** 2-3 minutes | **Tests:** All tab interfaces

### 3. User Journey

```bash
# All workflows
php artisan test tests/Browser/UserJourneyTest.php

# Specific journey
php artisan test --filter="registration flow"
php artisan test --filter="character creation"
php artisan test --filter="E2E journey"
```

**Runtime:** 3-5 minutes | **Tests:** 8 complete user workflows

### 4. Smoke Tests (Pre-Deployment)

```bash
# Quick health check (recommended before deploy)
php artisan test tests/Browser/SmokeTestSuite.php --group=smoke

# Critical paths only
php artisan test --group=smoke --group=critical
```

**Runtime:** 2-3 minutes | **Use:** Quick verification before deployment

### 5. Visual Regression

```bash
# All visual tests
php artisan test tests/Browser/VisualRegressionTest.php

# Specific viewport
php artisan test --group=mobile
php artisan test --group=tablet
php artisan test --group=desktop

# Screenshot capture
php artisan test --group=screenshots

# Cross-browser testing
php artisan test --group=cross-browser
```

**Runtime:** 5 minutes | **Output:** Screenshots in `storage/app/screenshots/`

### 6. Accessibility Audit (WCAG)

```bash
# Full WCAG audit
php artisan test tests/Browser/AccessibilityAuditTest.php

# Specific compliance areas
php artisan test --group=aria
php artisan test --group=keyboard
php artisan test --group=forms
php artisan test --group=wcag
```

**Runtime:** 3-4 minutes | **Standard:** WCAG 2.1 Level AA

## Common Workflows

### Before Deployment

```bash
# 1. Run smoke tests (fastest)
php artisan test --group=smoke

# 2. Run accessibility audit
php artisan test --group=wcag

# 3. Quick traversal
php artisan test:traversal --scope=public
```

**Total Time:** ~5-8 minutes

### Weekly QA Check

```bash
# Run all browser tests
php artisan test --group=browser
```

**Total Time:** ~20-30 minutes

### After Major UI Changes

```bash
# 1. Visual regression with screenshots
php artisan test --group=screenshots

# 2. Tab navigation tests
php artisan test --group=tabs

# 3. Accessibility audit
php artisan test --group=accessibility
```

**Total Time:** ~10-15 minutes

## Group Tags

| Group | Description |
|-------|-------------|
| `browser` | All browser tests |
| `smoke` | Quick critical path tests |
| `tabs` | Tab navigation tests |
| `keyboard` | Keyboard navigation tests |
| `accessibility` | Accessibility/WCAG tests |
| `wcag` | WCAG compliance only |
| `screenshots` | Visual regression screenshots |
| `cross-browser` | Multi-browser tests |
| `e2e` | End-to-end user journeys |
| `critical` | Critical paths only |

## Filtering Tests

### By Name

```bash
php artisan test --filter="user can login"
php artisan test --filter="character creation"
```

### By File

```bash
php artisan test tests/Browser/TabNavigationTest.php
```

### By Multiple Groups

```bash
php artisan test --group=browser --group=critical
php artisan test --group=accessibility --group=keyboard
```

## Output Options

### Compact Output

```bash
php artisan test --group=smoke --compact
```

### With Coverage

```bash
php artisan test --group=smoke --coverage
```

### Parallel Execution (Faster)

```bash
php artisan test --group=browser --parallel
```

## Troubleshooting

### Playwright Not Installed

```bash
npm install playwright@latest
npx playwright install
```

### Tests Timeout

```bash
# Increase timeout in phpunit.xml or use:
php artisan test --group=smoke --stop-on-failure
```

### View Browser (Debug)

```bash
php artisan test:traversal --headless=false
```

### Clear Test Database

```bash
php artisan migrate:fresh --env=testing
```

## Report Locations

- **Traversal Reports:** `storage/app/test-reports/traversal-{timestamp}.html`
- **Screenshots:** `storage/app/screenshots/`
- **Latest Report:** `storage/app/test-reports/traversal-latest.html`

## Recommended Schedule

| When | What to Run | Why |
|------|-------------|-----|
| **Before Commit** | Smoke tests | Quick validation (2-3 min) |
| **Before Deployment** | Smoke + Accessibility | Critical checks (5-8 min) |
| **After UI Changes** | Visual + Tabs | Visual consistency (10-15 min) |
| **Weekly** | All browser tests | Full coverage (20-30 min) |
| **Nightly (CI/CD)** | Full suite | Continuous monitoring |

## Quick Reference Card

```text
┌─────────────────────────────────────────────────┐
│ BROWSER TEST SUITE QUICK COMMANDS              │
├─────────────────────────────────────────────────┤
│ Pre-Deploy:  php artisan test --group=smoke    │
│ All Routes:  php artisan test:traversal        │
│ All Tests:   php artisan test --group=browser  │
│ WCAG Check:  php artisan test --group=wcag     │
│ Visual:      php artisan test --group=screenshots
│ Tabs:        php artisan test --group=tabs     │
└─────────────────────────────────────────────────┘
```

---

**For detailed documentation, see:**

- `tests/Browser/README.md` - Complete browser testing guide
- `TESTING.md` - General testing documentation
- `docs/implementation-summaries/browser-test-suites-implementation.md` -
  Full implementation details
